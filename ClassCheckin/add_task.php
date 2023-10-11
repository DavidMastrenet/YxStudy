<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_name = $_POST["task_name"];
    $task_deadline = time() + $_POST["ddl"]*60;
    $task_filename = "tasks/$task_name.txt";
    $record_filename = "records/$task_name.txt";
    file_put_contents($task_filename, $task_deadline);
    file_put_contents($record_filename, "开始登记打卡\n");
    echo "任务已添加成功！";
}
?>