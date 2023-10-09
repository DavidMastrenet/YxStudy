<?php
session_start();
// 检查用户是否已登录
if (isset($_SESSION['user'])) {
    header("Location: backend.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($username === "USERNAME"  && $password === "PASSWORD") {
        $_SESSION['user'] = $username;
        header("Location: backend.php");
    }

    $error = "用户名或密码不正确";
}

?>

<!DOCTYPE html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>登录</title>
  <style>
    body {
      background: url('https://cdn.pixabay.com/photo/2018/08/14/13/23/ocean-3605547_1280.jpg') no-repeat;
      background-size: 100% 230%;
    }

    #login_box {
      width: 300px;
      height: 250px;
      background-color: #00000060;
      margin: auto;
      margin-top: 10%;
      text-align: center;
      border-radius: 10px;
      padding: 50px 50px;
    }

    h2 {
      color: #ffffff90;
      margin-top: 5%;
    }

    #input-box {
      margin-top: 5%;
    }

    span {
      color: #fff;
    }

    input {
      border: 0;
      width: 60%;
      font-size: 15px;
      color: #fff;
      background: transparent;
      border-bottom: 2px solid #fff;
      padding: 5px 10px;
      outline: none;
      margin-top: 10px;
    }

    button {
      margin-top: 50px;
      width: 60%;
      height: 30px;
      border-radius: 10px;
      border: 0;
      color: #fff;
      text-align: center;
      line-height: 30px;
      font-size: 15px;
      background-image: linear-gradient(to right, #30cfd0, #330867);
    }

    #sign_up {
      margin-top: 45%;
      margin-left: 60%;
    }

    a {
      color: #b94648;
    }
    
    .msg {
        text-align: center;
        line-height: 88px;
        color: #ffffff90;
    }
    
    .footer {
        margin-top: 20px;
        font-size: 12px;
        color: #888;
    }

  </style>
</head>

<body>
  <div id="login_box">
    <h2>登录</h2>
    
    <form method="POST">
        <div class="input_box">
            <input type="text" class="input-item" id="username" name="username" placeholder="用户名" required>
        </div>
        <div class="input_box">
            <input type="password" class="input-item" id="password" name="password" placeholder="密码" required>
        </div>
        <button type="submit" class="btn" name="login">登录</button>
    </form>
    <?php
    if (isset($error)) {
        echo "<p style='color: red;'>{$error}</p>";
        }
    ?>
    <div class="footer">平台开发：hyx<br>2023.10</div>
  </div>
</body>
</html>



