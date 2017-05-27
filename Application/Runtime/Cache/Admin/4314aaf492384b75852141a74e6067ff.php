<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--360浏览器优先以webkit内核解析-->
<title>美联美客后台系统</title>
<link rel="shortcut icon" href="favicon.ico">
<link href="/Public/css/bootstrap.min.css?v=3.3.5" rel="stylesheet">
<link href="/Public/css/font-awesome.css?v=4.4.0" rel="stylesheet">
<link href="/Public/css/animate.css" rel="stylesheet">
<link href="/Public/css/style.css?v=4.0.0" rel="stylesheet">
<base target="_self">
</head>
<body class="gray-bg">
<div class="row  border-bottom white-bg dashboard-header">
  <div class="col-sm-12">
    <blockquote class="text-warning" style="font-size:14px">
      <?php if($admin['uname'] == 'system'): ?><h4 class="text-danger">欢迎使用FM105.7深圳优悦广播后台管理系统</h4>
      <?php elseif($admin['uname'] == 'helens'): ?>
        <h4 class="text-danger">欢迎使用Helens-美女开台后台管理系统</h4>
      <?php else: ?>
        <h4 class="text-danger">欢迎使用美联美客后台系统</h4>
          <?php if($total_msg): ?><div style="color: #000">短信余额：截止<?php echo ($total_msg); ?>】条</div><?php endif; endif; ?>

    </blockquote>
    <hr>
  </div>

</div>


<!-- 全局js -->
<script src="/Public/js/jquery.min.js?v=2.1.4"></script>
<script src="/Public/js/bootstrap.min.js?v=3.3.5"></script>
<script src="/Public/js/plugins/layer/layer.min.js"></script>
<!-- 自定义js -->
<script src="/Public/js/content.js"></script>
<!-- 欢迎信息 -->
<script src="/Public/js/welcome.js"></script>
</body>
</html>