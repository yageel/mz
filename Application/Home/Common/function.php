<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/6/6
 * Time: 17:03
 */

use Helpers\Helper;
use Helpers\Presenter;
use Redis\MyRedis;


function getAccessToken($city_id)
{
    $appId = D('city')->where(['city_id' => $city_id])->getField('appid');
    $key = 'wechat_access_token' . $appId;
    $access_token = MyRedis::getTokenInstance()->new_get($key);
    return $access_token;
}

/**
 * 时间戳转日期
 *
 * created by 胡倍玮
 *
 * @param int $timestamp 时间戳
 * @param string $format 默认Y-m-d H:i:s
 * @return null|string 时间戳为空或0返回null，不填返回当前日期
 */
function timestampToDate($timestamp = -1, $format = 'Y-m-d H:i:s')
{
    if (!$timestamp) {
        return null;
    }
    if ($timestamp == -1) {
        return date($format, time());
    } else {
        return date($format, $timestamp);
    }
}

/**
 * http请求
 *
 * @param $url
 * @param mixed $data
 * @return mixed
 */
function https_request($url, $data = null)
{
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
 * 展示临时素材
 *
 * @param $type
 * @param $fileName
 * @return string
 */
function getMediaHtml($type, $fileName)
{
    $html = '';
    $filePath = '/upload/' . Helper::MEDIA_PATH . $fileName;
    if ($type == Presenter::MEDIA_TYPE_IMAGE || $type == Presenter::MEDIA_TYPE_THUMB) {
        $html = "<img src=\"{$filePath}\" height=\"200\">";
    } else if ($type == Presenter::MEDIA_TYPE_VOICE) {
        $html = "<audio src=\"{$filePath}\" controls>你的浏览器版本太旧，不支持audio</audio>";
    } else if ($type == Presenter::MEDIA_TYPE_VIDEO) {
        $html = "<video src=\"{$filePath}\" controls style=\"width: 400px !important;\">你的浏览器版本太旧，不支持video</video>";
    }
    return $html;
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