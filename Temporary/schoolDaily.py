import re
import time
from datetime import datetime

import requests
from bs4 import BeautifulSoup

from schoolDaily_des_util import raw_str_enc

from flask import Flask, jsonify, render_template

app = Flask(__name__)

session = requests.session()
session.headers = {
    "user-agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) "
                  "Chrome/91.0.4472.124 Safari/537.36",
}


@app.route('/current_day')
def get_current_day():
    today = datetime.now().date()
    day_week = today.weekday()
    return f"今天是 {today} 星期 {'一二三四五六日'[day_week]}"


@app.route('/jwc_notice/<int:num_of_notice>')
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


def cas_login(username, password):
    login_url = "https://cas.shnu.edu.cn/cas/login?service=http%3A%2F%2Fcourse.shnu.edu.cn%2Feams%2Flogin.action"
    response = session.get(login_url)
    soup = BeautifulSoup(response.text, "html.parser")
    lt_value = soup.find("input", {"id": "lt"})["value"]
    data = {
        "rsa": raw_str_enc(username + password + lt_value),
        "ul": len(username),
        "pl": len(password),
        "lt": lt_value,
        "execution": soup.find("input", {"name": "execution"})["value"],
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

    return jsonify(courses)


@app.route('/')
def index():
    return render_template('schoolDaily_frontend.html')


if __name__ == '__main__':
    usr = ""
    pwd = ""
    cas_login(usr, pwd)
    app.run(debug=True)
