import time
import datetime

import requests
from bs4 import BeautifulSoup

from schoolDaily_des_util import raw_str_enc

session = requests.session()
session.headers = {
    "user-agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) "
                  "Chrome/91.0.4472.124 Safari/537.36",
}


def cas_login_xgxt(username, password):
    login_url = "https://cas.shnu.edu.cn/cas/login?service=http%3a%2f%2fxgxt.shnu.edu.cn%2fdefault.aspx"
    # response = session.get(login_url)
    # soup = BeautifulSoup(response.text, "html.parser")
    # lt_value = soup.find("input", {"id": "lt"})["value"]
    lt_value = "test"
    data = {
        "rsa": raw_str_enc(username + password + lt_value),
        "ul": len(username),
        "pl": len(password),
        "lt": lt_value,  # fuck value
        "execution": "e1s1",
        "_eventId": "submit",
    }
    post_headers = {
        "content-type": "application/x-www-form-urlencoded",
        "referer": login_url,
    }
    session.post(login_url, data=data, headers=post_headers)


def get_accommodation_info():
    global done
    done = 0
    url = "https://xgxt.shnu.edu.cn/YXXT/SSDYXHome.aspx"
    response = session.get(url)
    if response.status_code == 200:
        soup = BeautifulSoup(response.text, 'html.parser')
        dorm_element = soup.find(string="宿舍：")
        if dorm_element:
            parent_li = dorm_element.find_parent("li")
            if parent_li:
                dorm_info = parent_li.get_text(strip=True).replace("宿舍：", "")
                print("现在是", datetime.datetime.now(), "宿舍信息:", dorm_info)
                if dorm_info != "暂未开放":
                    done = 1
            else:
                print("现在是", datetime.datetime.now(), "未找到宿舍信息")
        else:
            print("现在是", datetime.datetime.now(), "请检查是否登录成功。")
    else:
        print("现在是", datetime.datetime.now(), "请求失败，状态码:", response.status_code)

usr = ""
pwd = ""

cas_login_xgxt(usr, pwd)
while True:
    cas_login_xgxt(usr, pwd)
    get_accommodation_info()
    if done == 1:
        break
    time.sleep(300)
