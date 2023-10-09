<?php
session_start();
if (!isset($_SESSION['checkuser'])) {
    header("Location: index.php");
    exit;
}
$checkuser = $_SESSION['checkuser'];
if (isset($_SESSION['haschecked'])){
    if ($_SESSION['haschecked'] != $_SESSION['checkuser']){
        $haschecked = $_SESSION['haschecked'];
        $notice = '警告：请勿帮他人打卡！(后台已记录)';
        $selected_task = $_SESSION['selected_task'];
        $record_filename = "records/$selected_task.txt";
        $record_data = "用户：$checkuser 与 $haschecked 使用同一设备进行打卡，请注意！\n";
        file_put_contents($record_filename, $record_data, FILE_APPEND);
    }
}
$_SESSION['haschecked'] = $_SESSION['checkuser'];
unset($_SESSION['checkuser']);
unset($_SESSION['selected_task']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>计师2班打卡</title>
    <style>
        body {
            background-color: white;
            color: black;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background-color: black;
                color: white;
            }
        }

        .success-box {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 18px;
            border-radius: 5px;
        }

        .checkmark {
            font-size: 24px;
            margin-bottom: 10px;
            color: white;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
            text-align: center;
        }

        h2 {
          color: red;
          font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="success-box">
        <div class="checkmark">&#10004;</div>
        <?php
        echo "$checkuser 打卡成功！";
        if (isset($notice)) {
            echo '<br>';
            echo "<h2>$notice</h2>";
        }
        ?>
    </div>
    <div class="footer">平台开发：hyx<br>2023.10</div>
</body>
</html>
