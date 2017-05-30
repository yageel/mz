<?php
namespace Home\Controller;

use Think\Exception;
use Weixin\MyWechat;
use Org\Util\File;
use Redis\MyRedis;
class AccessController extends BaseController
{
   function index(){
       try {
           ob_clean();
           $weObj = $this->initWechat($this->type);

           $weObj->valid();
           $type = $weObj->getRev()->getRevType();

           switch ($type) {
               case MyWechat::MSGTYPE_TEXT:
                   $this->msgReply($weObj, "text",$type);
                   break;

               case MyWechat::MSGTYPE_EVENT:
                   $event = $weObj->getRevEvent();
                   if (isset($event['event'])) {
                       switch ($event['event']) {
                           case MyWechat::EVENT_SUBSCRIBE:
                               $this->msgReply($weObj, "subscribe",$type);
                               break;

                           case MyWechat::EVENT_UNSUBSCRIBE:
                               $this->msgReply($weObj, "unsubscribe",$type);
                               break;

                           case MyWechat::EVENT_MENU_VIEW:
                               //$userInfo = $weObj->getRevData();
                               break;

                           case MyWechat::EVENT_MENU_CLICK:
                               $this->msgReply($weObj, MyWechat::EVENT_MENU_CLICK,$type);
                               break;

                           default:
                               # code...
                               break;
                       }
                   }
                   break;
               default:
                   break;
           }
           echo 'success';
       }catch (\Exception $e){
           File::write_file(APP_PATH . 'log/test.log', $e->getMessage() . "\r\n", 'a+');
       }
   }

    /**
     * 获取token接口
     */
    public function getAccessToken0_0()
    {
        $verify = isset($_GET['verify']) ? $_GET['verify'] : "";
        $appid = isset($_GET['appid']) ? $_GET['appid'] : "";


        if ($verify && $verify === md5("millionmake_0o0_") && $appid) {
            // $city = M('city')->where(array('appid' => $appid))->find();

            $cachename = 'wechat_access_token'.$appid;
            $data = MyRedis::getTokenInstance()->new_get($cachename);
            $return = ["access_token" => strval($data['access_token']), "expires_in" => intval($data['expires_in'])];
            echo json_encode($return);
            exit;
        } else {
            $errmsg = "the verify is not valid";
        }
        $return = ["errcode" => "selfdefined", "errmsg" => $errmsg];
        echo json_encode($return);
        exit;
    }
}
?>