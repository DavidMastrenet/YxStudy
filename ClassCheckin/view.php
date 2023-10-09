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
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        h1 {
            background-color: #007BFF;
            color: #fff;
            padding: 20px;
        }

        form {
            margin: 20px auto;
            max-width: 400px;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }

        select, input[type="submit"],button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        h2 {
            margin-top: 20px;
        }

        pre {
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            overflow: auto;
            text-align: left; /* 左对齐 */
        }

        h2, p, pre { /* 统一字体大小 */
            font-size: 16px;
            font-family: Arial, sans-serif;
        }

        p {
            margin: 5px 0;
            text-align: left; /* 左对齐 */
        }
        
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <h1>计师2班打卡</h1>

    <form action="view.php" method="post">
        <select name="selected_task">
            <?php
            $task_files = glob("tasks/*.txt");
            foreach ($task_files as $task_file) {
                $task_name = basename($task_file, ".txt");
                echo "<option value=\"$task_name\">$task_name</option>";
            }
            ?>
        </select>
        <input type="submit" value="查看记录">
    </form>
    <button onclick="location.href='backend.php'">返回</button>
    <div class="footer">平台开发：hyx<br>2023.10</div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $selected_task = $_POST["selected_task"];
        $record_filename = "records/$selected_task.txt";

        // Read the user list from user.list file
        $user_list = file("user.list", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Read the username list from username.list file
        $username_list = file("username.list", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $username_map = array();
        foreach ($username_list as $line) {
            list($user, $username) = explode(" - ", $line);
            $username_map[$user] = $username;
        }

        if (file_exists($record_filename)) {
            $records = file_get_contents($record_filename);
            echo "<h2>$selected_task 的打卡记录</h2>";
            echo "<pre>$records</pre>";

            // Extract usernames from the records
            preg_match_all('/用户：(\d+)/', $records, $matches);
            $recorded_users = $matches[1];

            // Find users who didn't record
            $missing_users = array_diff($user_list, $recorded_users);

            if (!empty($missing_users)) {
                echo "<h2>未打卡名单</h2>";
                foreach ($missing_users as $user) {
                    $username = isset($username_map[$user]) ? $username_map[$user] : "未知用户";
                    echo "<p>$user - $username</p>";
                }
            }
        } else {
            echo "<p>未找到任务的打卡记录。</p>";
        }
    }
    ?>
</body>
</html>
