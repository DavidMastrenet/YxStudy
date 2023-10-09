<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_task = $_POST["selected_task"];
    $username = $_POST["username"];
    $timestamp = date("Y-m-d H:i:s");
    $ip = $_SERVER["REMOTE_ADDR"];

    // 检查用户是否存在
    if (!isUserValid($username)) {
        echo "用户不存在！";
        exit;
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
    if ($current_time > $task_deadline) {
        echo "任务已经过期，无法打卡！";
        exit;
    }

    // 任务的打卡记录文件
    $record_filename = "records/$selected_task.txt";
    
    $lat = $result['lat'];
    $lon = $result['lon'];
    // 打卡记录
    $record_data = "用户：$username  时间：$timestamp  位置信息：经度$lon 纬度$lat  $location_mod  IP：$ip\n";

    // 保存打卡记录到任务文件
    file_put_contents($record_filename, $record_data, FILE_APPEND);
    echo "打卡成功！";
}

function isUserValid($username) {
    $users = file("user.list", FILE_IGNORE_NEW_LINES);
    return in_array($username, $users);
}

?>
