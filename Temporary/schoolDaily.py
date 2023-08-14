import re
import time
from datetime import datetime

import requests
from bs4 import BeautifulSoup

from schoolDaily_des_util import raw_str_enc

from flask import Flask, jsonify, render_template, redirect, url_for, request
from flask_login import LoginManager, UserMixin, login_user, login_required, logout_user, current_user

session = requests.session()
session.headers = {
    "user-agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) "
                  "Chrome/91.0.4472.124 Safari/537.36",
}

app = Flask(__name__)
app.secret_key = 'test_key'

login_manager = LoginManager()
login_manager.init_app(app)


class User(UserMixin):
    def __init__(self, id):
        self.id = id


@app.route('/logout')
@login_required
def logout():
    logout_user()
    return redirect(url_for('login'))


@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        global username, password
        username = request.form['user_id']
        password = request.form['password']
        cas_login(username, password)
        get_course_info()
        if login_status == 0:
            return render_template('login.html', message="登录失败")
        else:
            user = User(username)
            login_user(user)
            return redirect(url_for('index'))
    return render_template('login.html')


@login_manager.user_loader
def load_user(user_id):
    return User(user_id)


@login_manager.unauthorized_handler
def unauthorized():
    return redirect(url_for('login'))


@app.route('/current_day')
@login_required
def get_current_day():
    today = datetime.now().date()
    day_week = today.weekday()
    return f"今天是{today} 星期{'一二三四五六日'[day_week]}"


@app.route('/jwc_notice/<int:num_of_notice>')
@login_required
def get_jwc_notice(num_of_notice):
    url = "https://jwc.shnu.edu.cn/"
    response = requests.get(url)
    content = response.text
    soup = BeautifulSoup(content, "html.parser")
    article_list = soup.find("table", class_="ArticleList")
    title_date_rows = article_list.find_all("tr")
    processed_titles = set()
    list_id = 0
    notices = []
    for row in title_date_rows:
        title_element = row.find("a", class_="Normal")
        date_element = row.find("span", class_="PublishDate")
        title = title_element.text.strip()
        date = date_element.text.strip()
        if title not in processed_titles:
            processed_titles.add(title)
            list_id += 1
            notices.append({
                "id": list_id,
                "date": date,
                "title": title
            })
        if list_id >= num_of_notice:
            break
    return jsonify(notices)


@app.route('/comp_notice/<int:num_of_notice>')
@login_required
def get_comp_notice(num_of_notice):
    url = "http://xxjd.shnu.edu.cn/27065/list.htm"
    response = requests.get(url)
    soup = BeautifulSoup(response.text, "html.parser")
    list_id = 0
    news_list = soup.find_all("li", class_="news")
    notices = []
    for news in news_list:
        list_id += 1
        title = news.find("span", class_="news_title").find("a").text
        date = news.find("span", class_="news_meta").text
        notices.append({
            "id": list_id,
            "date": date,
            "title": title
        })
        if list_id >= num_of_notice:
            break
    return jsonify(notices)


def cas_login(usr, pwd):
    login_url = "https://cas.shnu.edu.cn/cas/login?service=http%3A%2F%2Fcourse.shnu.edu.cn%2Feams%2Flogin.action"
    response = session.get(login_url)
    if 'id="lt"' in response.text:
        soup = BeautifulSoup(response.text, "html.parser")
        lt_value = soup.find("input", {"id": "lt"})["value"]
    else:
        lt_value = ''
    if 'name="execution"' in response.text:
        execution = soup.find("input", {"name": "execution"})["value"]
    else:
        execution = "e1s1"
    data = {
        "rsa": raw_str_enc(usr + pwd + lt_value),
        "ul": len(usr),
        "pl": len(pwd),
        "lt": lt_value,
        "execution": execution,
        "_eventId": "submit",
    }
    post_headers = {
        "content-type": "application/x-www-form-urlencoded",
        "referer": login_url,
    }
    session.post(login_url, data=data, headers=post_headers)

    eams_url = "http://course.shnu.edu.cn/eams/login.action"
    eams_headers = {
        "accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,"
                  "application/signed-exchange;v=b3;q=0.7",
        "accept-language": "zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6",
        "cache-control": "no-cache",
        "pragma": "no-cache",
    }
    session.get(eams_url, headers=eams_headers)
    print(f"Logged as {username}")


@app.route('/course_info')
def get_course_info():
    global login_status
    search_url = "https://course.shnu.edu.cn/eams/stdSyllabus!search.action"
    search_headers = {
        "accept": "*/*",
        "accept-language": "zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6",
        "cache-control": "no-cache",
        "content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "pragma": "no-cache",
        "sec-ch-ua": "\"Not/A)Brand\";v=\"99\", \"Microsoft Edge\";v=\"115\", \"Chromium\";v=\"115\"",
        "sec-ch-ua-mobile": "?0",
        "sec-ch-ua-platform": "\"macOS\"",
        "sec-fetch-dest": "empty",
        "sec-fetch-mode": "cors",
        "sec-fetch-site": "same-origin",
        "x-requested-with": "XMLHttpRequest"
    }

    search_data = {
        "lesson.no": "",
        "lesson.course.name": "",
        "lesson.courseType.name": "",
        "lesson.teachClass.name": "2023计算机科学与技术(师范)本科2班",  # 你的班级
        "teacher.name": "",
        "lesson.teachClass.stdCount": "",
        "lesson.teachClass.limitCount": "",
        "lesson.course.credits": "",
        "lesson.coursePeriod": "",
        "lesson.project.id": "1",
        "lesson.semester.id": "342",
        "_": time.time(),
    }

    search_response = session.post(search_url, data=search_data, headers=search_headers)

    pattern_contents1 = r"contents\['(\d+)'\]='([^']*)'"
    pattern_contents2 = r"<td class=\"gridselect\"><input class=\"box\" name=\"lesson.id\" value=\"(\d+)\" " \
                        r"type=\"checkbox\"/></td><td>[\s\S]*?<a " \
                        r"href=\"/eams/stdSyllabus!info.action\?lesson.id=\d+\"[^>]*?>([^<]*)</a>"
    contents1_data = re.findall(pattern_contents1, search_response.text)
    contents2_data = re.findall(pattern_contents2, search_response.text)

    course_info = {course_id: info.strip() for course_id, info in contents1_data}

    course_names = {course_id: course_name for course_id, course_name in contents2_data}

    list_id = 0

    courses = []
    for course_id, info in course_info.items():
        list_id += 1
        course_name = course_names.get(course_id)
        info = info.replace('<br>', '')
        courses.append({
            "id": list_id,
            "course_name": course_name,
            "info": info
        })

    if str(courses) == "[]":
        courses.append({
            "id": "错误",
            "course_name": "无法获取课程信息",
            "info": "请检查学号、密码及班级名称是否正确"
        })
        login_status = 0
    else:
        login_status = 1

    return jsonify(courses)


@app.route('/')
@login_required
def index():
    return render_template('schoolDaily_frontend.html', username=current_user.id)


if __name__ == '__main__':
    app.run(debug=True)
