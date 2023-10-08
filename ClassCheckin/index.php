<!DOCTYPE html>
<html>
<head>
    <title>计师2班打卡</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            text-align: center;
        }

        h1 {
            color: #333;
        }

        form {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            max-width: 400px; /* 最大宽度 */
            width: 90%; /* 宽度设置为百分比 */
            margin: 0 auto;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }

        select, input[type="text"], input[type="button"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="text"] {
            width: 80%; /* 缩小用户名输入框 */
        }

        input[type="button"] {
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }

        input[type="button"]:hover {
            background-color: #0056b3;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: #fff;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #1e7e34;
        }

        /* 底部声明样式 */
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
        }

        /* 响应式设计 */
        @media (max-width: 600px) {
            form {
                padding: 10px; /* 在小屏幕上减小表单内边距 */
            }
        }
    </style>
    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                alert("浏览器不支持地理位置获取。");
            }
        }

        function showPosition(position) {
            var latitude = position.coords.latitude;
            var longitude = position.coords.longitude;
            document.getElementById("latitude").value = latitude;
            document.getElementById("longitude").value = longitude;
        }
    </script>
</head>
<body>
    <h1>计师2班打卡</h1>
    <form action="record.php" method="post">
        <label for="selected_task">课程</label>
        <select name="selected_task" id="selected_task">
            <?php
            $task_files = glob("tasks/*.txt");
            foreach ($task_files as $task_file) {
                $task_name = basename($task_file, ".txt");
                $task_deadline =file_get_contents($task_file);
                $task_deadline_mod = date("Y-m-d H:i:s", file_get_contents($task_file));
                $current_time = time();
                if ($current_time <= $task_deadline) {
                    echo "<option value=\"$task_name\">$task_name - 截止时间：$task_deadline_mod</option>";
                }
            }
            ?>
        </select><br>
        <label for="username">学号</label>
        <input type="text" name="username" id="username" required>
        <!-- 用于存储地理位置信息 -->
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">
        <input type="button" value="获取地理位置" onclick="getLocation()">
        <input type="submit" value="打卡（务必先获取位置）">
    </form>
    <!-- 底部声明 -->
    <div class="footer">平台开发：hyx <br>温馨提示：有用户特征记录，请勿帮他人打卡</div>
</body>
</html>
