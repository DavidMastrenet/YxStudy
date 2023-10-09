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
            color: #333;
        }

        button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            margin: 10px;
            border-radius: 5px;
        }

        button:hover {
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
    <button onclick="location.href='add.php'">添加任务</button>
    <button onclick="location.href='view.php'">查看记录</button>
    <div class="footer">平台开发：hyx<br>2023.10</div>
</body>
</html>
