<?php
$config = array(
    //'配置项'=>'配置值'
    'URL_MODEL'            =>3,    //2是去除index.php
    'DB_FIELDTYPE_CHECK'   =>true,
    'TMPL_STRIP_SPACE'     =>true,
    'OUTPUT_ENCODE'        =>true, // 页面压缩输出
    'USE_REDIS' => false,
    'MODULE_ALLOW_LIST'    =>    array('Admin','Home'),
    'DEFAULT_MODULE'       =>    'Admin',  // 默认模块
    'BASE_URL' => 'http://mz.hotwifibox.com/',
    //加密混合值
    'AUTH_CODE' => 'MoZuo=#=#',
    //数据库配置
    'URL_CASE_INSENSITIVE' => true,
    'URL_HTML_SUFFIX' => 'html',

//    'SESSION_OPTIONS'=>array(
//        'type'=> 'db',//session采用数据库保存
//        'expire'=>604800,//session过期时间，如果不设就是php.ini中设置的默认值
//        ),

    //
    'AD_REDIS' => array('127.0.0.1', 6379, 5, ''), //6380
    'PRO_REDIS' => array('127.0.0.1', 6379, 5, ''), //产品
    'TOKEN_REDIS' => array('127.0.0.1', 6379, 5, ''), //微信token 及相关
    'TOKEN_GAME' => array('127.0.0.1', 6379, 5, ''), //游戏

    'SESSION_TABLE'=>'hd_sess', //必须设置成这样，如果不加前缀就找不到数据表，这个需要注意
    'TAGLIB_BUILD_IN' => 'cx',//标签库
    //'TAGLIB_PRE_LOAD' => '',//命名范围

);


$db = dirname(__FILE__).'/db_config.php';
$db_config = file_exists($db) ? include "$db" : array();

$other = dirname(__FILE__).'/other.php';
$other_config = file_exists($other) ? include "$other" : array();

return array_merge($db_config,$config,$other_config);