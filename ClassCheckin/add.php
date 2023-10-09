<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>计师2班打卡</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        h1 {
            background-color: #007BFF;
            color: #fff;
            padding: 20px;
        }

        h2 {
            color: #333;
        }

        form {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            padding: 20px;
            width: 300px;
            margin: 0 auto;
        }

        form input[type="text"],
        form input[type="number"] {
            width: 40%; 
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        form input[type="submit"],button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        form input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <h1>后台 - 计师2班打卡</h1>
    <form action="add_task.php" method="post">
        格式：日期+课程（如10.9历史）<br><br>
        任务名称：<input type="text" name="task_name" required><br>
        时间限制（分钟）：<input type="number" name="ddl" min="1" step="1" required><br>
        <input type="submit" value="添加任务">
    </form>
    <br>
    <button onclick="location.href='backend.php'">返回</button>
    <div class="footer">平台开发：hyx<br>2023.10</div>
</body>
</html>
