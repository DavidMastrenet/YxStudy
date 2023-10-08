<!DOCTYPE html>
<html>
<head>
    <title>计师2班打卡</title>
</head>
<body>
    <h1>查看打卡记录</h1>

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
            echo "<h2>任务：$selected_task 的打卡记录</h2>";
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
