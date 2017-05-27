<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="renderer" content="webkit">
<title>美联美客后台管理系统</title>
<meta name="keywords" content="美联美客后台管理系统">
<meta name="description" content="美联美客后台管理系统">
<!--[if lt IE 8]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->
<link rel="shortcut icon" href="favicon.ico">
<link href="/Public/css/bootstrap.min.css?v=3.3.5" rel="stylesheet">
<link href="/Public/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
<link href="/Public/css/animate.css" rel="stylesheet">
<link href="/Public/css/style.css?v=4.0.0" rel="stylesheet">
</head>
<body class="fixed-sidebar full-height-layout gray-bg" style="overflow:hidden">
<div id="wrapper">
  <!--左侧导航开始-->
  <nav class="navbar-default navbar-static-side" role="navigation">
    <div class="nav-close"><i class="fa fa-times-circle"></i> </div>
    <div class="sidebar-collapse">
      <ul class="nav" id="side-menu">
        <li class="nav-header">
          <div class="dropdown profile-element"> <span><img alt="image" class="img-circle" src="/Public/img/profile_small.jpg" /></span> <a data-toggle="dropdown" class="dropdown-toggle" href="#"> <span class="clear"> <span class="block m-t-xs"><strong class="font-bold"><?php echo ($admin["uname"]); ?></strong></span> <span class="text-muted text-xs block">超级管理员<b class="caret"></b></span> </span> </a>
            <ul class="dropdown-menu animated fadeInRight m-t-xs">
              <li><a class="J_menuItem" href="#">修改头像</a> </li>
              <li><a class="J_menuItem" href="#">个人资料</a> </li>
              <li><a class="J_menuItem" href="#">联系我们</a> </li>
              <li><a class="J_menuItem" href="#">信箱</a> </li>
              <li class="divider"></li>
              <li><a href="<?php echo U('/index/logout');?>">安全退出</a> </li>
            </ul>
          </div>
          <div class="logo-element">联</div>
        </li>
        <li style=" border-bottom: 2px solid #ccc!important;"> <a href="#"> <i class="fa fa-desktop"></i> <span class="nav-label">公众号管理</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/city/index');?>">公众号列表</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/city/reply');?>">消息回复管理</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/city/media');?>">永久素材管理</a> </li>
          </ul>
        </li>

        <li> <a href="#"> <i class="fa fa-home"></i> <span class="nav-label">首页管理</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/home/sign');?>">签到配置</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/home/index');?>">首页配置</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/home/set');?>">文案配置</a> </li>
			<li> <a class="J_menuItem" href="<?php echo U('/home/redSet');?>">红包文案配置</a> </li>
          </ul>
        </li>

        <li> <a href="#"> <i class="fa fa-object-group"></i> <span class="nav-label">商品管理</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/goods/index');?>">商品列表</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/goods/tag');?>">商品标签</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/exchange/index');?>">兑换记录</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/lottery/index');?>">抽奖记录</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/express/index');?>">发货记录</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/exchange_rules/edit');?>">兑换规则</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/wxlog/index/rtype/1');?>">微信支付记录</a> </li>
          </ul>
        </li>

        <li> <a href="#"> <i class="fa fa-credit-card"></i> <span class="nav-label">刮刮卡管理</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/card/index');?>">中奖规则</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/card/history');?>">刮卡记录</a> </li>
          </ul>
        </li>

        <li> <a href="#"> <i class="fa  fa-balance-scale"></i> <span class="nav-label">任务大厅</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/taskmall/index');?>">超级任务</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/milestone/index');?>">里程碑</a> </li>
          </ul>
        </li>

        <li style=" border-bottom: 2px solid #ccc!important;"> <a href="#"> <i class="fa fa-gamepad"></i> <span class="nav-label">游戏管理</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/game/index');?>">游戏列表</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/game/navigation');?>">航海寻宝设置</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/game/bugbear');?>">无穷小怪兽设置</a> </li>
          </ul>
        </li>

        <li> <a href="#"> <i class="fa fa-gears"></i> <span class="nav-label">营销工具</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/signed/index');?>">签到礼包(线上)</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/signed/index/t/1');?>">签到礼包(线下)</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/signed/packstatistics');?>">签到统计</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/signed/dituistatistics');?>">地推统计</a> </li>
          </ul>
        </li>
        
        <li> <a href="#"> <i class="fa fa-group"></i> <span class="nav-label">用户管理</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/users/index');?>">微信用户</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/users/union');?>">Union用户</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/users/member');?>">手机用户</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/users/address');?>">用户地址</a> </li>
          </ul>
        </li>

        <li> <a href="#"> <i class="fa fa-money"></i> <span class="nav-label">资金管理</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/users/bank');?>">用户资金</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/users/case_record');?>">提现记录</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/city/wechat_pay_record');?>">微信支付记录</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/users/integral_record');?>">M币流水</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/users/money_record');?>">资金流水</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/users/integral_exchange');?>">积分兑换</a></li>
            <li> <a class="J_menuItem" href="<?php echo U('/wxlog/index');?>">微信充值M币</a> </li>
          </ul>
        </li>

        <li style=" border-bottom: 2px solid #ccc!important;"> <a href="#"> <i class="fa fa-money"></i> <span class="nav-label">系统设置</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/sys/msg');?>">监控消息</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/sys/signed');?>">签到红包</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/sys/sms');?>">发送短信</a> </li>
            <!--
            <li> <a class="J_menuItem" href="<?php echo U('/sys/user_tongji');?>">用户数据</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/sys/msg');?>">红包数据</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/sys/msg');?>">M币数据</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/sys/msg');?>">提现数据</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/sys/msg');?>">微信数据</a> </li>-->
          </ul>
        </li>

        <li>
          <a href="#"> <i class="fa fa-desktop"></i> <span class="nav-label">HeHa活动管理</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/active_heha/prize');?>">奖池设置</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/active_heha/join');?>">活动参与</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/active_heha/invite');?>">用户助力</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/active_heha/record');?>">抽奖流水</a> </li>
          </ul>
        </li>
        
        <li>
          <a href="#"> <i class="fa fa-desktop"></i> <span class="nav-label">美食地图活动管理</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/active_msdt/prize');?>">奖池设置</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/active_msdt/join');?>">活动参与</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/active_msdt/record');?>">抽奖流水</a> </li>
			<li> <a class="J_menuItem" href="<?php echo U('/active_msdt/notdraw_record');?>">未刮奖用户</a> </li>
          </ul>
        </li>
        <li>
          <a href="#"> <i class="fa fa-desktop"></i> <span class="nav-label">机场电视-美食地图</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/active_airport/prize');?>">奖池设置</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/active_airport/join');?>">活动参与</a> </li>
            <li> <a class="J_menuItem" href="<?php echo U('/active_airport/record');?>">抽奖流水</a> </li>
			<li> <a class="J_menuItem" href="<?php echo U('/active_airport/notdraw_record');?>">未刮奖用户</a> </li>
          </ul>
        </li>

        <li>
          <a href="#"> <i class="fa fa-desktop"></i> <span class="nav-label">二维码跳链管理</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/jump/index');?>">二维码列表</a> </li>
          </ul>
        </li>
        
         <li>
          <a href="#"> <i class="fa fa-desktop"></i> <span class="nav-label">图片转链接管理</span> <span class="fa arrow"></span> </a>
          <ul class="nav nav-second-level">
            <li> <a class="J_menuItem" href="<?php echo U('/imgshare/index');?>">链接列表</a> </li>
          </ul>
        </li>
        
        <!--
        <li> <a href="mailbox.html"><i class="fa fa-envelope"></i> <span class="nav-label">信箱 </span><span class="label label-warning pull-right">16</span></a>
          <ul class="nav nav-second-level">
            <li><a class="J_menuItem" href="mailbox.html">收件箱</a> </li>
            <li><a class="J_menuItem" href="mail_detail.html">查看邮件</a> </li>
            <li><a class="J_menuItem" href="mail_compose.html">写信</a> </li>
          </ul>
        </li>-->
        <!--
        <li> <a href="#"><i class="fa fa-edit"></i> <span class="nav-label">表单</span><span class="fa arrow"></span></a>
          <ul class="nav nav-second-level">

            <li><a class="J_menuItem" href="form_wizard.html">表单向导</a> </li>
            <li> <a href="#">文件上传 <span class="fa arrow"></span></a>
              <ul class="nav nav-third-level">
                <li><a class="J_menuItem" href="form_webuploader.html">百度WebUploader</a> </li>
                <li><a class="J_menuItem" href="form_file_upload.html">DropzoneJS</a> </li>
                <li><a class="J_menuItem" href="form_avatar.html">头像裁剪上传</a> </li>
              </ul>
            </li>
          </ul>
        </li>-->
        <!--
        <li> <a class="J_menuItem" href="css_animation.html"><i class="fa fa-magic"></i> <span class="nav-label">CSS动画</span></a> </li>
        <li> <a href="#"><i class="fa fa-cutlery"></i> <span class="nav-label">工具 </span><span class="fa arrow"></span></a>
          <ul class="nav nav-second-level">
            <li><a class="J_menuItem" href="form_builder.html">表单构建器</a> </li>
          </ul>
        </li>-->
      </ul>
    </div>
  </nav>
  <!--左侧导航结束-->
  <!--右侧部分开始-->
  <div id="page-wrapper" class="gray-bg dashbard-1">
    <div class="row border-bottom">
      <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header"><a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
          <form role="search" class="navbar-form-custom" method="post" action="search_results.html">
            <div class="form-group">
              <input type="text" placeholder="" class="form-control" name="top-search" id="top-search">
            </div>
          </form>
        </div>
        <ul class="nav navbar-top-links navbar-right">
          <li class="dropdown"> <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#"> <i class="fa fa-envelope"></i> <span class="label label-warning">0</span> </a>
            <ul class="dropdown-menu dropdown-messages">
              <!--
              <li class="m-t-xs">
                <div class="dropdown-messages-box"> <a href="profile.html" class="pull-left"> <img alt="image" class="img-circle" src="/Public/img/a7.jpg"> </a>
                  <div class="media-body"> <small class="pull-right">46小时前</small> <strong>小四</strong> 这个在日本投降书上签字的军官，建国后一定是个不小的干部吧？ <br>
                    <small class="text-muted">3天前 2014.11.8</small> </div>
                </div>
              </li>-->
              <li class="divider"></li>
              <li>
                <div class="text-center link-block"> <a class="J_menuItem" href="<?php echo U('/index/index');?>"> <i class="fa fa-envelope"></i> <strong> 查看所有消息</strong> </a> </div>
              </li>
            </ul>
          </li>
          <li class="dropdown"> <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#"> <i class="fa fa-bell"></i> <span class="label label-primary">0</span> </a>
            <ul class="dropdown-menu dropdown-alerts">
              <li> <a href="#">
                <div> <i class="fa fa-envelope fa-fw"></i> 您有0条未读消息 <span class="pull-right text-muted small">4分钟前</span> </div>
                </a> </li>
              <li class="divider"></li>
              <li>
                <div class="text-center link-block"> <a class="J_menuItem" href="<?php echo U('/index/index');?>"> <strong>查看所有 </strong> <i class="fa fa-angle-right"></i> </a> </div>
              </li>
            </ul>
          </li>
          <li class="dropdown hidden-xs"> <a class="right-sidebar-toggle" aria-expanded="false"> <i class="fa fa-tasks"></i> 主题 </a> </li>
        </ul>
      </nav>
    </div>
    <div class="row content-tabs">
      <button class="roll-nav roll-left J_tabLeft"><i class="fa fa-backward"></i> </button>
      <nav class="page-tabs J_menuTabs">
        <div class="page-tabs-content"> <a href="javascript:;" class="active J_menuTab" data-id="<?php echo U('index/index1');?>">首页</a> </div>
      </nav>
      <button class="roll-nav roll-right J_tabRight"><i class="fa fa-forward"></i> </button>
      <div class="btn-group roll-nav roll-right">
        <button class="dropdown J_tabClose" data-toggle="dropdown">关闭操作<span class="caret"></span> </button>
        <ul role="menu" class="dropdown-menu dropdown-menu-right">
          <li class="J_tabShowActive"><a>定位当前选项卡</a> </li>
          <li class="divider"></li>
          <li class="J_tabCloseAll"><a>关闭全部选项卡</a> </li>
          <li class="J_tabCloseOther"><a>关闭其他选项卡</a> </li>
        </ul>
      </div>
      <a href="<?php echo U('index/logout');?>" class="roll-nav roll-right J_tabExit"><i class="fa fa fa-sign-out"></i> 退出</a> </div>
    <div class="row J_mainContent" id="content-main">
      <iframe class="J_iframe" name="iframe0" width="100%" height="100%" src="<?php echo U('index/index1');?>" frameborder="0" data-id="<?php echo U('index/index1');?>" seamless></iframe>
    </div>
    <div class="footer">
      <div class="pull-right">&copy; 2015-2016 <a href="http://www.millionmake.com/" target="_blank">美联美客</a> </div>
    </div>
  </div>
  <!--右侧部分结束-->
  <!--右侧边栏开始-->
  <div id="right-sidebar">
    <div class="sidebar-container">
      <ul class="nav nav-tabs navs-3">
        <li class="active"> <a data-toggle="tab" href="#tab-1"> <i class="fa fa-gear"></i> 主题 </a> </li>
      </ul>
      <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
          <div class="sidebar-title">
            <h3> <i class="fa fa-comments-o"></i> 主题设置</h3>
            <small><i class="fa fa-tim"></i> 你可以从这里选择和预览主题的布局和样式，这些设置会被保存在本地，下次打开的时候会直接应用这些设置。</small> </div>
          <div class="skin-setttings">
            <div class="title">主题设置</div>
            <div class="setings-item"> <span>收起左侧菜单</span>
              <div class="switch">
                <div class="onoffswitch">
                  <input type="checkbox" name="collapsemenu" class="onoffswitch-checkbox" id="collapsemenu">
                  <label class="onoffswitch-label" for="collapsemenu"> <span class="onoffswitch-inner"></span> <span class="onoffswitch-switch"></span> </label>
                </div>
              </div>
            </div>
            <div class="setings-item"> <span>固定顶部</span>
              <div class="switch">
                <div class="onoffswitch">
                  <input type="checkbox" name="fixednavbar" class="onoffswitch-checkbox" id="fixednavbar">
                  <label class="onoffswitch-label" for="fixednavbar"> <span class="onoffswitch-inner"></span> <span class="onoffswitch-switch"></span> </label>
                </div>
              </div>
            </div>
            <div class="setings-item"> <span> 固定宽度 </span>
              <div class="switch">
                <div class="onoffswitch">
                  <input type="checkbox" name="boxedlayout" class="onoffswitch-checkbox" id="boxedlayout">
                  <label class="onoffswitch-label" for="boxedlayout"> <span class="onoffswitch-inner"></span> <span class="onoffswitch-switch"></span> </label>
                </div>
              </div>
            </div>
            <div class="title">皮肤选择</div>
            <div class="setings-item default-skin nb"> <span class="skin-name "> <a href="#" class="s-skin-0"> 默认皮肤 </a> </span> </div>
            <div class="setings-item blue-skin nb"> <span class="skin-name "> <a href="#" class="s-skin-1"> 蓝色主题 </a> </span> </div>
            <div class="setings-item yellow-skin nb"> <span class="skin-name "> <a href="#" class="s-skin-3"> 黄色/紫色主题 </a> </span> </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--右侧边栏结束-->
  <!--mini聊天窗口开始-->
  <div class="small-chat-box fadeInRight animated" >
    <div class="heading" draggable="true"> <small class="chat-date pull-right"> 2015.9.1 </small> 与 Beau-zihan 聊天中 </div>
    <div class="content">
      <div class="left">
        <div class="author-name"> Beau-zihan <small class="chat-date"> 10:02 </small> </div>
        <div class="chat-message active"> 你好 </div>
      </div>

    </div>
    <div class="form-chat">
      <div class="input-group input-group-sm">
        <input type="text" class="form-control">
        <span class="input-group-btn">
        <button
                        class="btn btn-primary" type="button">发送 </button>
        </span> </div>
    </div>
  </div>
  <div id="small-chat" style="display: none"> <span class="badge badge-warning pull-right">5</span> <a class="open-small-chat"> <i class="fa fa-comments"></i> </a> </div>
  <!--mini聊天窗口结束-->
</div>
<!-- 全局js -->
<script src="/Public/js//jquery.min.js?v=2.1.4"></script>
<script src="/Public/js//bootstrap.min.js?v=3.3.5"></script>
<script src="/Public/js//plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/Public/js//plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/Public/js//plugins/layer/layer.min.js"></script>
<!-- 自定义js -->
<script src="/Public/js//hplus.js?v=4.0.0"></script>
<script type="text/javascript" src="/Public/js//contabs.js"></script>
<!-- 第三方插件 -->
<script src="/Public/js//plugins/pace/pace.min.js"></script>
</body>
</html>