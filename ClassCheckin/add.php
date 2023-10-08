<!DOCTYPE html>
<html>
<head>
    <title>计师2班打卡</title>
</head>
<body>
    <h2>添加打卡任务</h2>
    <form action="add_task.php" method="post">
        任务名称：<input type="text" name="task_name" required><br>
        时间限制（分钟）：<input type="number" name="ddl" required><br>
        <input type="submit" value="添加任务">
    </form>

</body>
</html>
