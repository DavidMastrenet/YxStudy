import requests
from bs4 import BeautifulSoup
from datetime import datetime


def get_current_day():
    today = datetime.now().date()
    day_week = today.weekday()
    print("今天是", today, "星期", "一二三四五六日"[day_week])


def get_jwc_notice(num_of_notice):
    url = "https://jwc.shnu.edu.cn/"
    response = requests.get(url)
    content = response.text
    soup = BeautifulSoup(content, "html.parser")
    article_list = soup.find("table", class_="ArticleList")
    title_date_rows = article_list.find_all("tr")
    processed_titles = set()
    list_id = 0
    print("教务处学生公告：")
    for row in title_date_rows:
        title_element = row.find("a", class_="Normal")
        date_element = row.find("span", class_="PublishDate")
        title = title_element.text.strip()
        date = date_element.text.strip()
        if title not in processed_titles:
            processed_titles.add(title)
            list_id += 1
            print(f"{list_id}. [{date}] {title}")
        if list_id >= num_of_notice:
            break


def get_comp_notice(num_of_notice):
    url = "http://xxjd.shnu.edu.cn/27065/list.htm"
    response = requests.get(url)
    soup = BeautifulSoup(response.text, "html.parser")
    list_id = 0
    news_list = soup.find_all("li", class_="news")
    print("信息与机电工程学院公告：")
    for news in news_list:
        list_id += 1
        title = news.find("span", class_="news_title").find("a").text
        date = news.find("span", class_="news_meta").text
        print(f"{list_id}. [{date}] {title}")
        if list_id >= num_of_notice:
            break


def cas_login():
    global session
    session = requests.Session()
    login_url = "https://cas.shnu.edu.cn/cas/login?service=http%3A%2F%2Fcourse.shnu.edu.cn%2Feams%2Flogin.action"
    headers = {
        "user-agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
    }
    response = session.get(login_url, headers=headers)
    soup = BeautifulSoup(response.text, "html.parser")
    lt_value = soup.find("input", {"id": "lt"})["value"]
    data = {
        "rsa": "",  # 你的RSA
        "ul": "10",
        "pl": "15",
        "lt": lt_value,
        "execution": "e1s1",
        "_eventId": "submit",
    }
    post_headers = {
        "content-type": "application/x-www-form-urlencoded",
        "user-agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "referer": login_url,
    }

    post_response = session.post(login_url, data=data, headers=post_headers)

    eams_url = "http://course.shnu.edu.cn/eams/login.action"
    eams_headers = {
        "accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
        "accept-language": "zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6",
        "cache-control": "no-cache",
        "pragma": "no-cache",
    }
    session.get(eams_url, headers=eams_headers)
    global cas_login_done
    cas_login_done = True


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
        "_": "1691897459074",
    }

    search_response = session.post(search_url, data=search_data, headers=search_headers)

    if search_response.status_code == 200:
        soup = BeautifulSoup(search_response.text, "html.parser")
        course_info = {}
        tbody = soup.find("tbody", id="grid665029811_data")
        if tbody:
            print("本学期公共课：")
            for row in tbody.find_all("tr"):
                cols = row.find_all("td")
                course_id = cols[1].text.strip()
                course_name = cols[2].find("a").text.strip()
                teacher = cols[5].text.strip()
                schedule = cols[10].text.strip()
                course_info[course_id] = f"{course_name} - {teacher} - {schedule}"
            for course_id, info in course_info.items():
                print(f"课程编号：{course_id}, 课程信息：{info}")
    else:
        print("课程搜索失败")


get_current_day()
get_jwc_notice(5)
get_comp_notice(5)
cas_login()
get_course_info()
