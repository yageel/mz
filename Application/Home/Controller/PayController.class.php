<?php
/**
 * Created by PhpStorm.
 * User: Qinmj
 * Date: 2017/2/28
 * Time: 17:12
 */

namespace Home\Controller;
use Think\Controller;
use Weixin\MyWechat;

require_once LIB_PATH .'Wxpay/weixin.class.php';
class PayController extends Controller
{

    public function __construct()
    {
        $request_uri = $_SERVER['REQUEST_URI'];
        $type =substr($request_uri,-7,2);
        $str = strstr($type,"/");
        if($str){
            $type=str_replace('/','',$str);
        }
        $type = intval($type);

        //初始化wxpayconfig配置
        $cityInfo = D('City')->get_city($type);
        C('WXPAY.APPID', $cityInfo['appid']);
        C('WXPAY.APPSECRET',$cityInfo['appsecret']);
        C('WXPAY.MCHID', $cityInfo['mchid']);
        C('WXPAY.KEY', $cityInfo['zhifu']);

        $path = LIB_PATH . 'Weixin/zhengshu_'.$type;
        C('WXPAY.SSLCERT_PATH', $path . '/apiclient_cert.pem');
        C('WXPAY.SSLKEY_PATH', $path . '/apiclient_key.pem');
        $config = new \WxPayConfig();

    }

    public function notify(){
        notify();
    }


    public function testmodel(){
        testmodel();
    }
}

?>