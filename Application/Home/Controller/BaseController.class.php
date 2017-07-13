<?php
namespace Home\Controller;
use Think\Controller;
use Org\Util\File;
use Think\Exception;
use Weixin\MyWechat;
use Redis\MyRedis;
class BaseController extends Controller {

    /** @var null  */
    public  $wechat = null;
    public $type = 0;
    public $gfrom = 0;
    public $openid = '';

    /**
     * @var 用户微信信息
     */
    public $users = null;

    /**
     * @var 开发
     */
    public $admin = null;
    public $isLogin = false;

    /**
     * 前端接口返回信息
     * @return array
     */
    public function ajax_json()
    {
        $json = array(
            'state' => 4,
            'msg' => '',
            'data' => null
        );

        return $json;
    }

    /**
     * 初始化操作
     */
    public function _initialize(){
        $this->openid = '';
        $this->type = I('request.type',0,'intval');
        $this->gfrom = I('request.gfrom',0,'intval');

        $from_city = I('request.city_id',0,'intval');
        if($from_city){
            $_SESSION['from_city'] = $from_city;
        }
        $this->assign('from_city', $from_city);


        // 刷新token接口不处理以下
        if(strtolower(ACTION_NAME) == 'getaccesstoken0_0'){
            return true;
        }

        if(empty($this->type) ){
            die('NO FOUND CITY');
        }

        // 微信交互操作接口忽略下面
        if(strtolower(CONTROLLER_NAME) == 'access'){
            return true;
        }

        if($this->gfrom != 4 ){
           $this->initPage($this->type, $this->gfrom);
        }

        // 第三方授权调回
        if(isset($_GET['jumpurl']) && $_GET['jumpurl']){
            $url = urldecode($_GET['jumpurl']);
            if($_GET['constraint']){
                $t = time();
                $sign = md5('MlMk2015=！~@'.$t);
                if(strpos($url,'?')!==false){
                    $url = $url."&t={$t}&sign={$sign}";
                }else{
                    $url = $url."?t={$t}&sign={$sign}";
                }
            }
            return header("Location: ".$url);
        }

        $this->openid = $_SESSION['openid'.$this->type];

        // 测试接口分配测试用户
        if($this->gfrom == 4 && empty($this->openid)){
            // 增加自定义测试用户
             $this->openid = $_GET['wx_openid2']?$_GET['wx_openid2']:'obZe1uAe_oUzI3EYuy_akvAAg0Sg';
        }

        // 最高获取三次
        if( empty($this->openid)){
            $_SESSION['reload_num'] =  intval($_SESSION['reload_num'])+1;
            if($_SESSION['reload_num']> 3){
                die("No Found Openid");
            }
            return header("Location: ".tsurl("/index/index"));
        }
        $_SESSION['reload_num'] = 0;

        if($this->openid){
            // 如果还停留在授权链接则跳出， 防止拷贝出去报错
            if($_GET['code'] && $_GET['state']){
                $get = (array)$_GET;
                unset($get['code']);
                unset($get['state']);
                unset($get['isappinstalled']);//groupmessage&isappinstalled=0
                $get['test'] = time();
                $url = tsurl(CONTROLLER_NAME.'/'.ACTION_NAME,$get);
                return header("Location: ".$url);
            }
        }

        $users = D('Users')->get_user($this->openid);
        $city = D('City')->get_city($this->type);
        $this->assign('cityInfo',$city);

        $this->assign('users', $users);

        $this->users = $users;

        $this->assign('type', $this->type);
        $this->assign('gfrom', $this->gfrom);

        $this->isLogin = empty($this->users['mobile'])?false:true;

        if($this->users['bind_user_id']){
            $admin = D('Admin')->get_user_info($users['bind_user_id']);
            $this->admin = $admin;
            $this->assign('admin', $admin);
        }
        $this->assign('isLogin', $this->isLogin);

        if($this->gfrom != 4){
            $signature = $this->getShareSign($this->type, true);

            $this->assign('signature', $signature);//赚了
            $this->assign('share_default_title', "魔座按摩椅带给你不一样的享受！");
            $this->assign("share_default_sub_title","魔座按摩椅带给你不一样的享受~");
            $this->assign('share_default_pic', C('BASE_URL').'Public/images/failure-bg.png');

        }
    }


    /**
     * 调用微信类返回 access_token
     * @param  int $type 城市ID
     * @return object 微信公共类的对象
     */
    protected function initWechat($type)
    {
        if ($this->wechat) {
            return $this->wechat;
        }

        $cityInfo = D('City')->get_city($type);

        if(empty($cityInfo)){
            die('No Found Weixin Option.');
        }
        $options = array(
            'token' => $cityInfo['red_token'], //填写你设定的key
            'encodingaeskey' => $cityInfo['encodingaeskey'], //填写加密用的EncodingAESKey
            'appid' => $cityInfo['appid'], //填写高级调用功能的app id
            'appsecret' => $cityInfo['appsecret'] //填写高级调用功能的密钥
        );

        return $this->wechat = new MyWechat($options);
    }

    /**
     * 用户给公众号发信息,公众号处理并返回下面的信息
     * @param object $weObj 微信公共类
     * @param string $msgType 事件类型
     */
    protected function msgReply($weObj,$msgType,$type=0){
        if(true){
            switch($msgType){
                case "text" :
                    $content = trim($weObj->getRevContent());
                    $msg_data = D('CityAutoReply')->get_reply_msg($this->type, 'text', $content);

                    if($msg_data){
                        if($msg_data['reply_type'] == 'text'){
                            $weObj->text($msg_data['reply_content'])->reply();
                            exit;
                        }elseif($msg_data['reply_type'] == 'img'){
                           $weObj->image($msg_data['reply_content'])->reply();
                            exit;
                        }
                    }
                    echo '';
                    exit;
                // 关注
                case "subscribe" :
                    $openid =  $weObj->getRevFrom();

                    $user = D('Users')->get_user($openid);
                    $_SESSION['openid'.$this->type] =  $openid;
                    $_SESSION["FUserId" . $this->type] = $openid;

                    //设置一个虚拟的设备id，不然签到无法顺利插入数据
                    if($user){
                        $data = array('is_subscribe'=>1, 'subcribe_time'=>time());
                        if(empty($user['wx_name']) && empty(($user['wx_pic']))){
                            $uinfo = $this->getUserInfo($this->type);
                            if ($uinfo) {
                                $data['wx_name'] = strval($uinfo['nickname']);
                                $data['wx_pic'] = strval($uinfo['headimgurl']);
                            }
                        }

                        D('Users')->update_user($openid, $data);
                    }else {
                        $uinfo = $this->getUserInfo($this->type);
                        $data = array(
                            'openid' => $openid,
                            'cityid' => $this->type,
                            'is_subscribe' => 1,
                            'subcribe_time' => time(),
                            'create_time' => time(),
                        );

                        if ($uinfo) {
                            $data['wx_name'] = strval($uinfo['nickname']);
                            $data['wx_pic'] = strval($uinfo['headimgurl']);
                        }

                        D('Users')->add_user($data);
                    }

                    $msg_data = D('CityAutoReply')->get_reply_msg($this->type, 'event', 'subscribe');
                   if($msg_data){
                       $reply_content = $msg_data['reply_content'];
                       $reply_type = $msg_data['reply_type'];
                       if ($reply_type == 'text') {
                           $weObj->text($reply_content)->reply();
                       } else if ($reply_type == 'img') {
                           $weObj->image($reply_content)->reply();
                       }
                       die();
                   }
                   break;


                // 用户取消关注
                case 'unsubscribe':
                    $openid =  $weObj->getRevFrom();
                    $data = array('is_subscribe'=>0, 'unsubcribe_time'=>time());
                    D('Users')->update_user($openid, $data);
                    break;

                case MyWechat::EVENT_MENU_CLICK:
                    $event = $weObj->getRevEvent();
                    $msg_data = D('CityAutoReply')->get_reply_msg($this->type, 'button', $event['key']);
                    if ($msg_data) {
                        $reply_content = $msg_data['reply_content'];
                        $reply_type = $msg_data['reply_type'];
                        if ($reply_type == 'text') {
                            $weObj->text($reply_content)->reply();
                        } else if ($reply_type == 'img') {
                            $weObj->image($reply_content)->reply();
                        }
                        exit();
                    }
                    break;

                default:
                    break;
            }
        }
    }

    /**
     * 分享接口的信息
     * @param $type 城市id
     * @param bool $ajax 是否是ajax请求,是的话返回数据
     * @return array|null 直接渲染模板 或 返回分享接口的凭据信息
     */
    protected function getShareSign($type,$ajax=false,$url=null)
    {

        $wechat = $this->initWechat($type);
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

		if(empty($url)){
			$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		}
        
        $signPackage = $wechat->getJsSign($url);

        $share = [
            "appid" => $signPackage["appid"],
            "str" => $signPackage['noncestr'],
            "time" => $signPackage['timestamp'],
            "ticket" => $signPackage['signature']
        ];
        if($ajax){
            return $share;
        }

        $this->assign('onlyurl', $share);
    }

    /**
     * 初始化页面,主要是要获取用户的openid 并将openid写入reidis
     * @param int $type 城市ID
     * @param int $from 来源 1:摇一摇进来的, 2:菜单进来的, 3:分享链接进来的, 4:用来调试的
     */
    protected function initPage($type,$gfrom){
        /*******************初始化******/
        $FUserId = $_SESSION['FUserId' . $type];
        // 2016-12-27 ShengYue
        $error = '';
        switch ($gfrom) {
            case 1:
            case 2:
            case 3:
                /**
                 * @shengyue 2016-05-26 session改成redis
                 */
                 if (empty($FUserId) || empty($_SESSION["openid".$type]) )
                 //if(true)
                {
                    //用户授权
                    $info = $this->authorize($type);

                    if ($info) {
                        $FUserId = $info['openid'];

                        $_SESSION["FUserId" . $type] = $info['openid'];
                        $_SESSION["openid".$type] = $FUserId;

                        // 获取微信用户信息
                        $weObj = $this->initWechat($type);
                        $openid = $info["openid"];
                        $access_token = $info["access_token"];
                        $info = $weObj->getOauthUserinfo($access_token, $openid);

                        $users = [
                            'openid' =>$openid,
                            'city_id' => $type,
                            'wx_name' =>strval($info['nickname']),
                            'wx_pic' => strval($info['headimgurl'])
                        ];
                        $user_info = D('Users')->get_user($openid);
                        if($user_info){
                            D('Users')->update_user($openid, $users);
                        }else{
                            // 初始化用户数据 包括 users 记录, users_union记录 , users_brand记录
                            D('Users')->add_user($users);
                        }

                        $ip = get_client_ip();
                        $agent = $_SERVER['HTTP_USER_AGENT'];
                        $login_log = [
                            'openid' => $FUserId,
                            'login_time' => time(),
                            'login_ip' => $ip,
                            'login_agent' => substr($agent,0,250),
                            'city_id' => $type
                        ];

                        M('users_login_log')->add($login_log);

                    } else {
                        if($_GET['code']){
                            if(intval($_SESSION['jumpcodetime'.$this->type]) > 2){
                                $_SESSION['jumpcodetime'.$this->type] = 0;
                            }else{
                                $_SESSION['jumpcodetime'.$this->type] = intval($_SESSION['jumpcodetime'.$this->type]) + 1;
                                $get = (array)$_GET;
                                unset($get['code']);
                                unset($get['state']);
                                unset($get['isappinstalled']);//groupmessage&isappinstalled=0
                                $url = tsurl(CONTROLLER_NAME.'/'.ACTION_NAME,$get);
                                return header("Location: ".$url);
                            }
                        }
                        $error = "网络繁忙，请稍后再试";
                    }
                }
                break;
            default:

                $error = "访问来源无法确定".$gfrom;
                break;
        }

        if (empty($FUserId) || $error) {
            header("Content-type: text/html; charset=utf-8");
            $context = "<html><script>alert('" . $error . "')</script></html>";
            exit($context);
        }
    }


    /**
     * 获取授权access_token 用户进来必须要用到此方法
     * @param int $type 城市ID
     * @return bool 获取授权access_token 是否成功
     */
    protected function authorize($type)
    {

        $weObj = $this->initWechat($type);

        if (isset($_GET['code'])) {

            $info = $weObj->getOauthAccessToken();

            if ($info) {
                /**
                 * @ShengYue 2016-05-26 session改成使用redis
                 */
                $oauthname = "oauth_access_token" . $type . $info["openid"];
                $_SESSION[$oauthname] =  $info['access_token'];
                return $info;
            } else {
                File::write_file(APP_PATH.'log/error.log', "getOauthAccessToken error,errCode: city_id={$type}=" . $weObj->errCode . "  errMsg: " . $weObj->errMsg."\r\n",'a+');
            }
            return false;
        } else {
            // 强制公众号2需要授权
            $snsapi_base = 'snsapi_base';

            if( $type == 2 ){
                $snsapi_base = 'snsapi_userinfo';
            }

            $url = $weObj->getOauthRedirect('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], uniqid(),$snsapi_base);
            if ($url) {
                header("Location:$url");
                exit;
            }
            return false;
        }
    }

    /*获取用户的详细信息*/
    protected function getUserInfo($type){
        /**
         * @shengyue 2016-05-26 session改成redis
         */
        // $openid = $_SESSION["openid".$type];
        $openid = $_SESSION["openid".$type];

        if(empty($type) || empty($openid)) return false;

        $weObj = $this->initWechat($type);
        $uinfo = $weObj->getUserInfo($openid);

        return $uinfo;
    }

}