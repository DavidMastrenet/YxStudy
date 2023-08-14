import os
import yaml
from datetime import datetime
from flask import Flask, render_template, request, redirect, url_for, send_from_directory
import uuid

app = Flask(__name__)

DATA_FILE = "data.yaml"

def load_data():
    if not os.path.exists(DATA_FILE):
        return []
    with open(DATA_FILE, "r") as file:
        return yaml.load(file, Loader=yaml.FullLoader)

def save_data(data):
    with open(DATA_FILE, "w") as file:
        yaml.dump(data, file)

@app.route("/", methods=["GET", "POST"])
def submit():
    if request.method == "POST":
        name = request.form.get("name")
        book = request.form.get("book")
        duration = request.form.get("duration")
        image = request.files.get("image")
        if name and book and duration and image:
            filename = uuid.uuid4()
            data = load_data()
            data.append({
                "name": name,
                "book": book,
                "duration": duration,
                "date": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                "image_filename": str(filename) + ".jpg"
            })
            save_data(data)
            if not os.path.exists("img"):
                os.makedirs("img")
            image.save(os.path.join("img", str(filename) + ".jpg"))
            return redirect(url_for("submit"))
    return render_template("index.html")

@app.route("/admin")
def admin():
    data = load_data()
    return render_template("admin.html", data=data)


@app.route('/img/<path:filename>')
def img(filename):
    file_path = os.path.join('img', filename)
    return send_from_directory('img', filename)


if __name__ == "__main__":
    app.run(debug=False, port=9099, host="0.0.0.0")
