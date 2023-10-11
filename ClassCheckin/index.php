<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_task = $_POST["selected_task"];
    $username = $_POST["username"];
    $timestamp = date("Y-m-d H:i:s");
    $ip = $_SERVER["REMOTE_ADDR"];

    // 检查用户是否存在
    if (!isUserValid($username)) {
        $error = "用户不存在！";
        $err = "1";
    }
    
    function wgs84_to_gcj02($lat, $lon) {
        $a = 6378245.0; // 长半轴
        $ee = 0.00669342162296594323; // 扁率
    
        if (out_of_china($lat, $lon)) {
            return array('lat' => $lat, 'lon' => $lon);
        }
    
        $dLat = transformLat($lon - 105.0, $lat - 35.0);
        $dLon = transformLon($lon - 105.0, $lat - 35.0);
        $radLat = $lat / 180.0 * M_PI;
        $magic = sin($radLat);
        $magic = 1 - $ee * $magic * $magic;
        $sqrtMagic = sqrt($magic);
        $dLat = ($dLat * 180.0) / (($a * (1 - $ee)) / ($magic * $sqrtMagic) * M_PI);
        $dLon = ($dLon * 180.0) / ($a / $sqrtMagic * cos($radLat) * M_PI);
    
        $mgLat = $lat + $dLat;
        $mgLon = $lon + $dLon;
    
        return array('lat' => $mgLat, 'lon' => $mgLon);
    }
    
    function out_of_china($lat, $lon) {
        return !($lon > 73.66 && $lon < 135.05 && $lat > 3.86 && $lat < 53.55);
    }
    
    function transformLat($x, $y) {
        $ret = -100.0 + 2.0 * $x + 3.0 * $y + 0.2 * $y * $y + 0.1 * $x * $y + 0.2 * sqrt(abs($x));
        $ret += (20.0 * sin(6.0 * $x * M_PI) + 20.0 * sin(2.0 * $x * M_PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($y * M_PI) + 40.0 * sin($y / 3.0 * M_PI)) * 2.0 / 3.0;
        $ret += (160.0 * sin($y / 12.0 * M_PI) + 320 * sin($y * M_PI / 30.0)) * 2.0 / 3.0;
        return $ret;
    }
    
    function transformLon($x, $y) {
        $ret = 300.0 + $x + 2.0 * $y + 0.1 * $x * $x + 0.1 * $x * $y + 0.1 * sqrt(abs($x));
        $ret += (20.0 * sin(6.0 * $x * M_PI) + 20.0 * sin(2.0 * $x * M_PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($x * M_PI) + 40.0 * sin($x / 3.0 * M_PI)) * 2.0 / 3.0;
        $ret += (150.0 * sin($x / 12.0 * M_PI) + 300.0 * sin($x / 30.0 * M_PI)) * 2.0 / 3.0;
        return $ret;
    }

    if (isset($_POST["latitude"]) && isset($_POST["longitude"])) {
        // 获取经度和纬度
        $result = wgs84_to_gcj02($_POST["latitude"], $_POST["longitude"]);
        
        $latitude = number_format($result['lat'], 6);
        $longitude = number_format($result['lon'] , 6);
            // 构建高德地图API的逆地理编码请求URL
        $api_url = "https://restapi.amap.com/v3/geocode/regeo?key=APIKEY&radius=10&extensions=0&roadlevel=0&location={$longitude},{$latitude}";
        // 发送HTTP请求
        $response = file_get_contents($api_url);
        // 解析JSON响应
        $data = json_decode($response, true);
        if ($data["status"] === "1") {
            // 提取出地点名称
            $location = $data["regeocode"]["formatted_address"];
            $location_mod = "位置：{$location}";
        } else {
            $location_mod = "无法获取地点信息";
        }
    } else {
        $location_mod = "未收到经度和纬度信息";
    }

    // 获取任务截止时间
    $task_deadline = file_get_contents("tasks/$selected_task.txt");
    $current_time = time();

    // 检查是否超过任务截止时间
    if ($current_time > $task_deadline && (!isset($err))) {
        $error = "任务已经过期，无法打卡！";
        $err = "1";
    }

    // 任务的打卡记录文件
    $record_filename = "records/$selected_task.txt";
    
    $lat = $result['lat'];
    $lon = $result['lon'];
    // 打卡记录
    $record_data = "用户：$username  时间：$timestamp  位置信息：经度$lon 纬度$lat  $location_mod  IP：$ip\n";
    
    $records = file_get_contents($record_filename);
    
    if (strpos($records, $username) !== false) {
        $error = "请勿重复打卡！";
        $err = "1";
    } else {
        if (!isset($err)) {
            file_put_contents($record_filename, $record_data, FILE_APPEND);
        }
    }

    if (!isset($err)) {
        session_start();
        $_SESSION['checkuser'] = $username;
        $_SESSION['selected_task'] = $selected_task;
        header("Location: success.php");
    }
}

function isUserValid($username) {
    $users = file("user.list", FILE_IGNORE_NEW_LINES);
    return in_array($username, $users);
}
?>

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
            background-color: #007BFF;
            color: #fff;
            padding: 20px;
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
        
        notice {
          color: red;
          font-size: 20px;
          font-weight: bold;
        }
    </style>
    <script>
        window.onload = getLocation;
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
    <form action="index.php" method="post">
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
        <input type="submit" value="打卡">
        <?php
        if (isset($error)) {
            echo "<notice>{$error}</notice><br>";
            }
        ?>
        <button onclick="location.href='backend.php'">后台管理</button>
    </form>
    <!-- 底部声明 -->
    <div class="footer">平台开发：hyx<br>2023.10<br>温馨提示：有用户特征记录，请勿帮他人打卡</div>
</body>
</html>

