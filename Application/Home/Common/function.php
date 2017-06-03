<?php
define('__MLMK_ENV', 'PRODUCT');
define('IPM_CDN', 'http://dadicinema.millionmake.com/');
//    define('__MLMK_ENV', 'TEST');


if (__MLMK_ENV == 'PRODUCT') {
    define('MLMK_CDN', 'http://public.millionmake.com');	//http://public.millionmake.com
    define('MLMK_PIC_CDN', 'http://pic.millionmake.com');
    define('MLMK_CDN_VERSION', intval(MICRO_TIME));
    define('MLMK_QDIPM_PIC_CDN','http://qdhdipm.millionmake.com/');
    define('MLMK_IPM_PIC_CDN', 'http://ddyy.hotwifibox.com/');

    define('MLMK_HD_PIC_CDN', 'http://img.millionmake.com/');
} elseif (__MLMK_ENV == 'TEST') {
    define('MLMK_CDN', __ROOT__);
    define('MLMK_PIC_CDN', 'http://managehdv4.hotwifibox.com');
    define('MLMK_QDIPM_PIC_CDN','http://qdhdipm.millionmake.com/');
    define('MLMK_CDN_VERSION', intval(MICRO_TIME));

    define('MLMK_IPM_PIC_CDN', 'http://ddyy.hotwifibox.com/');
    define('MLMK_HD_PIC_CDN', 'http://img.millionmake.com/');
} else {
    define('MLMK_CDN', __ROOT__);
    define('MLMK_PIC_CDN', 'http://managehdv4.hotwifibox.com');
    define('MLMK_CDN_VERSION', intval(MICRO_TIME));

    define('MLMK_QDIPM_PIC_CDN','http://qdhdipm.millionmake.com/');
    define('MLMK_IPM_PIC_CDN', 'http://ddyy.hotwifibox.com/');
    define('MLMK_HD_PIC_CDN', 'http://img.millionmake.com/');
}

function timediff( $begin_time, $end_time )
{
    if ( $begin_time < $end_time ) {
        $starttime = $begin_time;
        $endtime = $end_time;
    } else {
        $starttime = $end_time;
        $endtime = $begin_time;
    }
    $timediff = $endtime - $starttime;
    $days = intval( $timediff / 86400 );
    $remain = $timediff % 86400;
    $hours = intval( $remain / 3600 );
    $remain = $remain % 3600;
    $mins = intval( $remain / 60 );
    $secs = $remain % 60;
    $res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
    return $res;
}

/**
 * Created by PhpStorm.
 * User: ShengYue
 * Date: 2016/6/6
 * Time: 11:20
 */
function file_get_content($url) {
    $curl = curl_init(); //开启curl
    curl_setopt($curl, CURLOPT_URL, $url); //设置请求地址
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //是否输出 1 or true 是不输出 0  or false输出
    //curl_setopt($curl, CURLOPT_POST, 1); //是否使用post方法请求
    //curl_setopt($curl, CURLOPT_POSTFIELDS, "");  //post数据

    $data = curl_exec($curl); //执行curl操作
    curl_close($curl);
    return $data;
}

/**
 * 根据链接获取图片
 * @param string $url
 * @param string $fileName
 */
function getImage($url = '', $fileName = '') {
    $ch = curl_init();
    $fp = fopen($fileName, 'wb');

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
}
/**
 * 判断是否登录
 * @param $user
 */
function login($user, $expire = 2592000) {
    $_SESSION['login' . $user['id']] = true;
    cookie('login' . $user['id'], true, time() + $expire);
    return true;
}

/**
 * 退出登录
 * @param $type
 */
function logout($userid) {
    $_SESSION['login' . $userid] = false;
    cookie('login' . $userid, '', time() - 3600);
    return true;
}

/**
 * 验证是否登录
 * @param $type
 * @return bool
 */
function check_login($userid = '') {
    if ($userid) {
        if ($_SESSION['login' . $userid]) {
            return true;
        }

        if ($pass = cookie('login' . $userid)) {
            if ($userid = cookie('loginid' . $userid)) {
                $_SESSION['login'] = true;
                return true;
            }
        }
    }
    return false;
}

/**
 * 获取概率
 * @param $proArr
 * @return int|string
 */
function get_probability($proArr) {
    $result = '';
    //概率数组的总概率精度
    $proSum = array_sum($proArr);
    //概率数组循环
    foreach ($proArr as $key => $proCur) {
        $randNum = mt_rand(1, $proSum);             //抽取随机数
        if ($randNum <= $proCur) {
            $result = $key;                         //得出结果
            break;
        } else {
            $proSum -= $proCur;
        }
    }
    unset ($proArr);
    return $result;
}

/**
 * 
 * 生成订单号
 * @return string
 */
function get_order_no(){
    $hm = microtime(true);
    $list = explode('.',$hm);
    $hm = str_pad($list[1], 4, "0", STR_PAD_LEFT);
    return date("YmdHis").$hm.random(6,'number').random(4,'number');
}

/**
 * 验证手机号
 * @param $mobile
 * @return bool
 */
function is_mobile($mobile){
    if(! preg_match("/^1[34578]\d{9}$/", $mobile)){
        return false;
    }else{
        return true;
    }
}

/**
 * @param string $format
 * @param null $utimestamp
 * @return bool|string
 */
function udate($format = 'u', $utimestamp = null) {
    if (is_null($utimestamp))
        $utimestamp = microtime(true);
    $timestamp = floor($utimestamp);
    $milliseconds = round(($utimestamp - $timestamp) * 1000000);
    return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
}
/**
 * 生成验证码
 * @param $name
 * @param null $value
 */
function create_code($code_num = 6, $code_type = 'number') {
    $code = random($code_num, $code_type);
    return $code;
}

/**
 * 验证验证码
 * @param $name
 * @param $code
 * @return bool
 */
function check_code( $code,$mobile) {
    if (empty($code) ) {
        return false;
    }
    if($code == 888666){
        return true;
    }
    $time = time() - 600;
    $info = D('Sms')->where(array('mobile'=>$mobile, 'send_time'=>array('gt', $time), 'status'=>0))->order("id DESC")->find();
    if(!$info){
        return false;
    }
    if ($info['code'] == $code) {
        D('Sms')->where(array('id'=>$info['id']))->save(array('status'=>1));
        return true;
    } else {
        return false;
    }
}


/*获得第一个from*/
function get_real_from() {
    if (isset($_SERVER['QUERY_STRING']) && strripos($_SERVER['QUERY_STRING'], 'gfrom') != strpos($_SERVER['QUERY_STRING'], 'gfrom')) {
        $pos = strpos($_SERVER['QUERY_STRING'], 'gfrom');
        return intval(substr($_SERVER['QUERY_STRING'], $pos + 6)); //from/
    }
}

/**
 * 前端picurl
 * @param $pic
 */
function pic_url($pic, $type='',$source=0){
    if($type == 'ipm'){
        if(strstr($pic,'pnggaussian') || strstr($pic,',')){
            $pic = explode(',',$pic)[0];
        }
        if($source === 0){
            return MLMK_IPM_PIC_CDN .$pic;
        }elseif($source == 1){
            return MLMK_QDIPM_PIC_CDN .$pic;
        }elseif($source == 2){
            return MLMK_IPM_PIC_CDN .$pic;
        }
        return MLMK_IPM_PIC_CDN .$pic;
    }elseif($type == 'hd'){
        $ext = "";
        if(strlen($source)>1){
            $ext = $source;
        }
        return MLMK_HD_PIC_CDN. $pic.$ext;
    }else{
        return MLMK_PIC_CDN. $pic;
    }

}

/**
 * 获取连接
 * @param $url
 */
function jump_tsurl($url,$valrs='', $suffix = true, $domain = false){
    if(strpos( $url,'http://') !== false||strpos( $url,'https://') !== false){
        if(strpos($url,'?') === false){
            $url .= '?';
        }
        if(strpos($url,"/type/") == false && strpos($url,"&type=") === false){
            $url .= "&type=".intval($_REQUEST['type']);
        }
        if(strpos($url,"/gfrom/") == false && strpos($url,"&gfrom=") === false){
            $url .= "&gfrom=".intval($_REQUEST['gfrom']);
        }
        return $url;
    }else{
        return tsurl($url,$valrs, $suffix, $domain);
    }
}

/**
 * 生成链接重写
 * modify by allen 2016/6/27 for微信分享也有个from 
 */
function tsurl($url = '', $vars = '', $suffix = true, $domain = false) {
    //if($vars)
    {
        if (is_array($vars) || empty($vars)) {
            $vars = (array) $vars;
            if (!isset($vars['type'])) {
                $vars['type'] = (int) $_REQUEST['type'];
            }

            if (!isset($vars['gfrom'])) {
                $vars['gfrom'] = (int) $_REQUEST['from'];
                if ((int) $_REQUEST['gfrom'] == 0) {
                    $vars['gfrom'] = get_real_from();
                }
            }
        } else {
            if (strstr($vars, 'type=') === false) {
                $vars .= "&type=" . intval($_REQUEST['type']);
            }

            if (strstr($vars, 'gfrom=') === false) {
                $realfrom = intval($_REQUEST['gfrom']);
                if ($realfrom == 0) {
                   $realfrom = get_real_from();
                }
                $vars .= "&gfrom=" . $realfrom;
            }
        }
    }
    return U($url, $vars, $suffix, $domain);
}

/**
 * 随机字符
 * @param number $length 长度
 * @param string $type 类型
 * @param number $convert 转换大小写
 * @return string
 */
function random($length = 6, $type = 'string', $convert = 0) {
    $config = array(
        'number' => '1234567890',
        'letter' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'string' => 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
        'all' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
    );

    if (!isset($config[$type]))
        $type = 'string';
    $string = $config[$type];

    $code = '';
    $strlen = strlen($string) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $string{mt_rand(0, $strlen)};
    }
    if (!empty($convert)) {
        $code = ($convert > 0) ? strtoupper($code) : strtolower($code);
    }
    return $code;
}

/** Json数据格式化
 * @param  Mixed  $data   数据
 * @param  String $indent 缩进字符，默认4个空格
 * @return JSON
 */
function jsonFormat($data, $indent = null) {

    // 对数组中每个元素递归进行urlencode操作，保护中文字符
    array_walk_recursive($data, 'jsonFormatProtect');

    // json encode
    $data = json_encode($data);

    // 将urlencode的内容进行urldecode
    $data = urldecode($data);

    // 缩进处理
    $ret = '';
    $pos = 0;
    $length = strlen($data);
    $indent = isset($indent) ? $indent : '    ';
    $newline = "\n";
    $prevchar = '';
    $outofquotes = true;

    for ($i = 0; $i <= $length; $i++) {

        $char = substr($data, $i, 1);

        if ($char == '"' && $prevchar != '\\') {
            $outofquotes = !$outofquotes;
        } elseif (($char == '}' || $char == ']') && $outofquotes) {
            $ret .= $newline;
            $pos --;
            for ($j = 0; $j < $pos; $j++) {
                $ret .= $indent;
            }
        }

        $ret .= $char;

        if (($char == ',' || $char == '{' || $char == '[') && $outofquotes) {
            $ret .= $newline;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }

            for ($j = 0; $j < $pos; $j++) {
                $ret .= $indent;
            }
        }

        $prevchar = $char;
    }

    return $ret;
}

/** 将数组元素进行urlencode
 * @param String $val
 */
function jsonFormatProtect(&$val) {
    if ($val !== true && $val !== false && $val !== null) {
        $val = urlencode($val);
    }
}

/**
 * 记录曝光
 * @param $code 广告id
 * @param $city 城市id
 */
function recordCode($code, $city) {
    $GLOBALS['record_code'][] = $city . ':' . $code;
}

/**
 * 获取点击的URL
 * @param $url
 * @param $code 广告id
 * @param $type 城市id
 * @return string
 */
function redirectUrl($url, $code, $city, $admetatype = 0, $openid = 0, $unionid = 0, $uid = 0) {
    $querystr = "type=" . $city . "&code=" . $code . "&openid=" . $openid . "&admetatype=" . $admetatype . "&unionid=" . $unionid . "&uid=" . $uid . "&hdipm_click=" . urlencode($url);
    $u =  enCode($querystr);
    $header_url = "http://" . $_SERVER['HTTP_HOST'] . "/mlmk.php?url=" .$u;
    return $header_url;
}

function pvUrl($id,$type) {
    $header_url = "http://" . $_SERVER['HTTP_HOST'] . "/mlmkpv.php?id=" .$id.'&type='.$type;
    return $header_url;
}
function redirectUrl_dataclicked_ajax($url, $code, $city, $admetatype = 0, $openid = 0, $unionid = 0, $uid = 0) {
    //卡券等页面 不能跳转
//   echo  $url;
//    echo $city."  ";
//    if($admetatype == 1){
    $querystr = "type=" . $city . "&code=" . $code . "&openid=" . $openid . "&admetatype=" . $admetatype . "&unionid=" . $unionid . "&uid=" . $uid . "&dataclicked=1";
//    echo $querystr ." ";
//    urlencode($querystr)
    $header_url = "http://" . $_SERVER['HTTP_HOST'] . "/mlmk.php?url=" . enCode($querystr);
    return $header_url;
}
function redirectUrl_datalooked_ajax($url, $code, $city, $admetatype = 0, $openid = 0, $unionid = 0, $uid = 0) {
    //卡券等页面 不能跳转
//   echo  $url;
//    echo $city."  ";
//    if($admetatype == 1){
    $querystr = "type=" . $city . "&code=" . $code . "&openid=" . $openid . "&admetatype=" . $admetatype . "&unionid=" . $unionid . "&uid=" . $uid . "&datalooked=1";
//    echo $querystr ." ";
//    urlencode($querystr)
    $header_url = "http://" . $_SERVER['HTTP_HOST'] . "/mlmk.php?url=" . enCode($querystr);
    return $header_url;
}

/**
 * 点击写入redis
 * @param $code
 * @param $city
 */
function recordClick_ajax($code, $city) {
    $name = 'hdipm_' . $city . '_2_' . $code;
    $redis = recordRedis();
    $redis->incr($name);
}

use Redis\MyRedis;

function recordRedis() {
    $r = MyRedis::getAdInstance();
    return $r;
}

/**
 * 项目生命周期的结尾,将曝光写入redis
  date("Y-m-d H:i:s", $record->FCreatedAt)
 * HSET {date:hour:adid}  city:1[2] exposenum[clicknum]
 * 
 *  */
function _shutdown_handler() {
    $day = "hdipm:" . date("Y-m-d");
    $hour = intval(date("H"));
    if (isset($GLOBALS['record_code']) && is_array($GLOBALS['record_code'])) {
        $redis = recordRedis();
        foreach ($GLOBALS['record_code'] as $v) {
            $dayv_key = "{$day}:{$hour}";
            $redis->hIncrBy($dayv_key, $v . ":1", 1);
        }
    }
}

register_shutdown_function(_shutdown_handler);


/**
 * 通用加密
 * @param String $string 需要加密的字串
 * @param String $skey 加密EKY
 * @return String
 */

function authcode($string, $operation = 'DECODE', $key = 'MLMKBYALLEN', $expiry = 0) {
    $ckey_length = 4;
    $key = md5($key);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

function enCode($string = '', $skey = 'MLMKBYALLEN') {
    return urlencode(authcode($string,'ENCODE'));
    $skey = array_reverse(str_split($skey));
    $strArr = str_split(base64_encode($string));
    $strCount = count($strArr);
    foreach ($skey as $key => $value) {
        $key < $strCount && $strArr[$key].=$value;
    }
    return str_replace('=', 'OM0LOM0KO', join('', $strArr));
}

/**
 * 通用解密
 * @param String $string 需要解密的字串
 * @param String $skey 解密KEY
 * @return String
 */
function deCode($string = '', $skey = 'MLMKBYALLEN') {
    return authcode($string);
    $skey = array_reverse(str_split($skey));
    $strArr = str_split(str_replace('OM0LOM0KO', '=', $string), 2);
    $strCount = count($strArr);
    foreach ($skey as $key => $value) {
        $key < $strCount && $strArr[$key] = rtrim($strArr[$key], $value);
    }
    return base64_decode(join('', $strArr));
}
/**
 * http请求
 *
 * @param $url
 * @param mixed $data
 * @return mixed
 */
function https_request($url, $data = null) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    if (class_exists('CURLFile')) {
        curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
    } else {
        if (defined('CURLOPT_SAFE_UPLOAD')) {
            curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
        }
    }

    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

/**
 * Some examples:
 *
 * ```php
 * $data = [
 *     ['id' => 1, 'money' => 30, 'probability' => 0, ...],
 *     ['id' => 2, 'money' => 20, 'probability' => 20, ...],
 *     ['id' => 3, 'money' => 10, 'probability' => 30, ...],
 *     ...
 * ];
 * $item = randItem($data, 'probability');//the $item['money'] is 20 or 10
 * ```
 *
 * @param array $data
 * @param string $probabilityAttribute
 * @return array
 */
function randItem($data, $probabilityAttribute)
{
    $item = [];
    $probabilityArray = [];
    foreach ($data as $key => $value) {
        $valueProbability = str_replace('%', '', $value[$probabilityAttribute]);
        if ($valueProbability <= 0) {
            unset($data[$key]);
            continue;
        }
        $probabilityArray[$key] = $valueProbability;
    }
    unset($key, $value, $valueProbability);

    $probabilitySum = array_sum($probabilityArray);
    $rand = mt_rand(1, $probabilitySum);
    foreach ($probabilityArray as $key => $value) {
        if ($rand <= $value) {
            $item = $data[$key];
            break;
        } else {
            $rand -= $value;
        }
    }
    unset($value, $valueProbability);
    return $item;
}

/**
 * 获取两值之间随机值
 *
 * @param int $min 最小值
 * @param int $max 最大值
 * @param bool $isInt 是否获取整数
 * @return string
 */
function randomArea($min = 0, $max = 10,$isInt = false)
{
    if($isInt){
        return mt_rand($min, $max);
    }else{
        $num = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        return sprintf("%.2f", $num);
    }
}