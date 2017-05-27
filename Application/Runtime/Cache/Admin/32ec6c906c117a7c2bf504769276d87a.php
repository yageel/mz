<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    

    <title>美联美客后台管理系统 - 登录</title>
    <meta name="keywords" content="美联美客后台管理系统">
    <meta name="description" content="美联美客后台管理系统">

    <link rel="shortcut icon" href="favicon.ico"> <link href="/Public/css/bootstrap.min.css?v=3.3.5" rel="stylesheet">
    <link href="/Public/css/font-awesome.css?v=4.4.0" rel="stylesheet">

    <link href="/Public/css/animate.css" rel="stylesheet">
    <link href="/Public/css/style.css?v=4.0.0" rel="stylesheet"><base target="_self">
    <!--[if lt IE 8]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->
    <script>if(window.top !== window.self){ window.top.location = window.location;}</script>
</head>

<body class="gray-bg signin">

    <div class="middle-box text-center loginscreen  animated fadeInDown">
        <div>
            <div>
                <h1 class="logo-name">联</h1>
            </div>
            <h3>欢迎使用 美联美客</h3>

            <form class="m-t" target="_self" role="form" method="post" action="<?php echo U('/login/post');?>">
                <div class="form-group">
                    <input type="text" name="uname" class="form-control" placeholder="用户名" required="">
                </div>
                <div class="form-group">
                    <input type="password" name="pwd" class="form-control" placeholder="密码" required="">
                </div>
                <button type="submit" class="btn btn-primary block full-width m-b">登 录</button>


                <p class="text-muted text-center">
                </p>

            </form>
        </div>
    </div>

    <!-- 全局js -->
    <script src="/Public/js/jquery.min.js?v=2.1.4"></script>
    <script src="/Public/js/bootstrap.min.js?v=3.3.5"></script>
    <!--统计代码，可删除-->
</body>

</html>