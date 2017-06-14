<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/6/6
 * Time: 21:47
 */

if(!function_exists('httpPost')){
    function httpPost($url, $data = null)
    {
        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1;SV1)");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $temp=curl_exec ($ch);
        curl_close ($ch);
        return $temp;
    }
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

/**
 * 用户状态
 * @param $status
 * @param bool|true $is_color
 */
function admin_user_status($status, $is_color = true){
    if($status == 1){
        return $is_color?"<span color='green'>正常</span>":"正常";
    }elseif($status == 2){
        return $is_color?"<span color='red'>锁定</span>":"锁定";
    }elseif($status == 4){
        return $is_color?"<span color='black'>删除</span>":"删除";
    }
}

/**
 * 用户信息
 * @param $user_id
 * @param bool|true $username
 */
function admin_user($user_id, $username = true){
    $detail = M('admin')->where(['id'=>$user_id])->find();
    if($detail){
        if($username){
            return $detail['contact_name'];
        }else{
            return $detail;
        }
    }
    return '';
}

function admin_user_role($role_id){
    if($role_id == 1){
        return '系统';
    }elseif($role_id == 2){
        return '运营';
    }elseif($role_id == 3){
        return '渠道';
    }elseif($role_id == 4){
        return '魔座';
    }elseif($role_id == 5){
        return '推广';
    }
    return '';
}

function package_info($package_id=0, $show=true){
    $info = M('package')->where(['id'=>$package_id])->find();
    if($show){
        return $info['package_name'];
    }else{
        return $info;
    }
}

/**
 * 获取城市信息~
 * @param $city_id
 * @param bool|false $is_show
 * @return mixed|string
 */
function get_city($city_id, $is_show=false){
    $detail = M('area')->where(['id'=>$city_id])->find();
    if($detail){
        if($is_show){
            return $detail['city_name'];
        }else{
            return $detail;
        }
    }
    return '';
}

if(!function_exists('send_msgs')){
    /**
     * @param $mobile
     * @param $content
     */
    function  send_msgs($mobile, $message, $code='',$openid = '') {
        $uid = "200117"; $pwd = strtoupper(md5('634131')); $encode = "utf8";$content = base64_encode($message);

        $data = "uid={$uid}&password={$pwd}&encode={$encode}&encodeType=base64&content={$content}&mobile=$mobile";
        $res = httpPost('http://119.90.36.56:8090/jtdsms/smsSend.do',$data);

        return($res);
    }

}

/**
 * 短信下发接口
 * @param $mobile
 * @param $message
 */
/**
 * 短信下发接口
 * @param $mobile
 * @param $message
 */
function send_msg($mobile, $message, $code='',$openid = '') {
    $openid = strval($openid);
    // 校验 //每个手机30十分钟内只能发3次
    // 校验 // 每个用户30分钟内只能发5次
    $bool = D('Sms')->check_mobile($mobile);
    if (!$bool) {
        return false;
    }

    $bool = D('Sms')->check_user($openid);
    if (!$bool) {
        return false;
    }

    $data = array(
        'mobile' => $mobile,
        'msg' => $message,
        'code'=>$code,
        'openid' => strval($openid),
        'send_time' => time()
    );

    $res = D('Sms')->add($data);
    if($res){
//        $token = md5($message . 'mlmk1234');
        try {
//            $url = "http://sms.weiyingjia.cn:8080/dog3/httpUTF8SMSToken.jsp?username=mlmk&token={$token}&mobile=$mobile&msg=$message";
//            $data = [];
//            $data['log'] = file_get_contents($url);
            $uid = "200117"; $pwd = strtoupper(md5('634131')); $encode = "utf8";$content = base64_encode($message);
            $data = "uid={$uid}&password={$pwd}&encode={$encode}&encodeType=base64&content={$content}&mobile=$mobile";
            $res2 = httpPost('http://119.90.36.56:8090/jtdsms/smsSend.do',$data);
            $data = [];
            if (  $res2 > 0) {
                $data['send_status'] = 1;
            } else {
                $data['send_status'] = 2;
            }
            $data['log'] = $res2;
            D('Sms')->where(array('id'=>$res))->save($data);
        } catch (\Exception $e) {

        }
    }else{
        return false;
    }
    return true;
}

/**
 *
 * @param $password
 * @param $salt
 */
function encrypt_password($password, $salt = '')
{
    return md5($password . md5($salt));
}

/**
 * 获取自动分表名
 *
 * @param $table
 * @param $userid
 * @param int $n
 * @return string
 */
function get_hash_table($table, $userid, $n = 9) {
    $str = abs(crc32($userid));
    $hash = intval($str / $n);
    $hash = intval(fmod($hash, $n));

    return $table . "_" . ($hash + 1);
}

/**
 * 遍历获取目录下的指定类型的文件
 * @param $path
 * @param array $files
 * @return array
 */
function getfiles($path, $allowFiles, &$files = array())
{
    if (!is_dir($path)) return null;
    if(substr($path, strlen($path) - 1) != '/') $path .= '/';
    $handle = opendir($path);
    while (false !== ($file = readdir($handle))) {
        if ($file != '.' && $file != '..') {
            $file = iconv("gb2312","utf-8",$file);
            $path2 = $path . $file;
            if (is_dir($path2)) {
                getfiles($path2, $allowFiles, $files);
            } else {
                if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
                    $files[] = array(
                        'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                        'mtime'=> filemtime($path2)
                    );
                }
            }
        }
    }
    return $files;
}

/**
 * 保存图片
 * @param $url
 * @param $path
 */
function save_img($url,$path) {
    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt ( $ch, CURLOPT_URL, $url );
    ob_start ();
    curl_exec ( $ch );
    $return_content = ob_get_contents ();
    ob_end_clean ();
    $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );

    $fp= fopen($path,"w"); //将文件绑定到流 
    fwrite($fp,$return_content); //写入文件
    fclose($fp);
}


