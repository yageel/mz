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
function send_msg($mobile, $message, $code='',$openid = '') {
    $openid = strval($openid);
    // 校验 //每个手机30十分钟内只能发3次
    // 校验 // 每个用户30分钟内只能发5次
        $token = md5($message . 'mlmk1234');
        try {
            $url = "http://sms.weiyingjia.cn:8080/dog3/httpUTF8SMSToken.jsp?username=mlmk&token={$token}&mobile=$mobile&msg=$message";
            $res = file_get_contents($url);
            var_dump($res);

        } catch (\Exception $e) {

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


