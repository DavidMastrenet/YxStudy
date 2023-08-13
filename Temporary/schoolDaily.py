import requests
from bs4 import BeautifulSoup
from datetime import datetime


def get_current_day():
    today = datetime.now().date()
    day_week = today.weekday()
    print("今天是", today, "星期", "一二三四五六日"[day_week])


def get_notice(num_of_notice):
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


get_current_day()
get_notice(5)
