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
    public $from = 0;
    public $openid = '';

    /**
     * @var 用户微信信息
     */
    public $users = null;

    /**
     * @var 开发
     */
    public $usersUnion = null;
    public $usersMember = null;
    public $usersBank = null;
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
        $this->from = I('request.from',0,'intval');

        $from_city = I('request.city_id',0,'intval');
        if($from_city){
            $_SESSION['from_city'] = $from_city;
        }

        $from_city = intval(!empty($_SESSION['from_city'])?$_SESSION['from_city']:0);

        $this->assign('from_city', $from_city);

        /*add by allen 2016/06/28*/
        if ($this->from == 0 && isset($_SERVER['QUERY_STRING']) && strripos($_SERVER['QUERY_STRING'], 'from') != strpos($_SERVER['QUERY_STRING'], 'from')) {
            $pos = strpos($_SERVER['QUERY_STRING'], 'from');
            $this->from = intval(substr($_SERVER['QUERY_STRING'], $pos + 5));
        }

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

        if($this->from != 4 && $this->from != 5 && strtolower(CONTROLLER_NAME) != 'access'){
            $this->initPage($this->type, $this->from);
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
        if($this->from == 4 && empty($this->openid)){
            // 增加自定义测试用户
             $this->openid = $_GET['wx_openid2']?$_GET['wx_openid2']:'ojXJAwe5RvGIc1Blh_8kiDLRMlhk';
        }

        // 最高获取三次
        if( empty($this->openid) && strtolower(CONTROLLER_NAME) != 'access'){
            $_SESSION['reload_num'] =  intval($_SESSION['reload_num'])+1;
            if($_SESSION['reload_num']> 3){
                die("No Found Openid");
            }
            return header("Location: ".tsurl("/index/index"));
        }
        $_SESSION['reload_num'] = 0;

        // 如果还停留在授权链接则跳出， 防止拷贝出去报错
        if($_GET['code'] && $_GET['state']){
            $get = (array)$_GET;
            unset($get['code']);
            unset($get['state']);
            unset($get['isappinstalled']);//groupmessage&isappinstalled=0
            $url = tsurl(CONTROLLER_NAME.'/'.ACTION_NAME,$get);
            return header("Location: ".$url);
        }

        $users = D('Users')->get_user($this->openid);
        $city = D('City')->get_city($this->type);
        $this->assign('cityInfo',$city);

        $this->assign('users', $users);
        $this->users = $users;

        $this->assign('type', $this->type);
        $this->assign('from', $this->from);

        $this->isLogin = empty($this->users)?false:true;

        $this->assign('isLogin', $this->isLogin);
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
            $msg1 = "玩游戏、抢红包、攒积分，聚宝商城百万豪礼，等你来抢!";
            switch($msgType){
                case "text" :
                    $openid =  $weObj->getRevFrom();
                    File::write_file(APP_PATH . 'log/text.log', date("Y-m-d H:i:s")."openid={$openid}&type={$this->type}=\r\n", 'a+');
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
                    // 民歌40关注活动
                    $openid =  $weObj->getRevFrom();

                    File::write_file(APP_PATH . 'log/subscribe.log', date("Y-m-d H:i:s")."openid={$openid}&type={$this->type}=\r\n", 'a+');
                    if($this->type == 44){
                        $time = time();
                        M()->execute("INSERT INTO t_super_mingge_award(`openid`, `create_time`)SELECT '{$openid}' as openid, '{$time}' as create_time FROM DUAL WHERE NOT EXISTS (
                              SELECT * FROM t_super_mingge_award WHERE openid='{$openid}'
                            )");
                    }
                    $user = D('Users')->get_user($openid);
                    $_SESSION['openid'.$this->type] =  $openid;
                    //setcookie("FUserId" . $this->type, $openid, time() + 1800,'/',C('COOKIE_DOMAIN'));
                    $_SESSION["FUserId" . $this->type] = $openid;

                    //设置一个虚拟的设备id，不然签到无法顺利插入数据
                    if($user){
                        $data = array('is_subscribe'=>1, 'subcribe_time'=>time());
                        D('Users')->update_user($openid, $data);
                    }else {
                        $uinfo = $this->getUserInfo($this->type);
                        $city_info = D('City')->get_city($this->type);

                        $data = array(
                            'openid' => $openid,
                            'cityid' => $this->type,
                            'is_subscribe' => 1,
                            'subcribe_time' => time(),
                            'create_time' => time(),
                            'platform' => $city_info['platform']
                        );

                        if ($uinfo) {
                            $data['unionid'] = strval($uinfo['unionid']);
                            $data['wx_name'] = strval($uinfo['nickname']);
                            $data['wx_pic'] = strval($uinfo['headimgurl']);
                            $data['wx_sex'] = intval($uinfo['sex']);
                            $data['wx_city'] = strval($uinfo['city']);
                            $data['wx_province'] = strval($uinfo['province']);
                            $data['wx_country'] = strval($uinfo['country']);
                            $data['wx_remark'] = strval($uinfo['remark']);
                            $data['wx_groupid'] = strval($uinfo['groupid']);
                        }

                        // subscribe, openid,nickname,sex,language,city,province,country,headimgurl,subscribe_time,unionid,remark,groupid
                        // 初始化用户数据 包括 users 记录, users_union记录 , users_brand记录
                        D('Users')->init_user($data);
                    }

                    $msg_data = D('CityAutoReply')->get_reply_msg($this->type, 'event', 'subscribe');
                    if($this->type == 44 || $this->type == 1){
                        $weObj->text("玩游戏、抢红包、攒积分，聚宝商城百万豪礼，等你来抢！
                        <a href='http://hd.millionmake.com/index.php?s=/taskmall/index/type/44/from/2.html'>领取超级任务奖励</a>")->reply();
                    }else{
                        $weObj->text($msg_data?$msg_data['reply_content']:$msg1)->reply();
                    }

                    die();
                    break;


                // 用户取消关注
                case 'unsubscribe':
                    $openid =  $weObj->getRevFrom();
                    File::write_file(APP_PATH . 'log/unsubscribe.log', date("Y-m-d H:i:s")."openid={$openid}&type={$this->type}=\r\n", 'a+');
                    $data = array('is_subscribe'=>0, 'unsubcribe_time'=>time());
                    D('Users')->update_user($openid,$data);
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

     */
    protected function getCardSign($type, $ajax = false, $card_id = '', $card_type = '') {
        $wechat = $this->initWechat($type);
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $signPackage = $wechat->getJsSignCard($url, $card_id, $card_type);

        $share = [
            "appid" => $signPackage["appid"],
            "str" => $signPackage['noncestr'],
            "time" => $signPackage['timestamp'],
            "ticket" => $signPackage['signature']
        ];
        if ($ajax) {
            return $share;
        }
        $this->assign('onlyurl', $share);
    }
    /**
     * 初始化页面,主要是要获取用户的openid 并将openid写入reidis
     * @param int $type 城市ID
     * @param int $from 来源 1:摇一摇进来的, 2:菜单进来的, 3:分享链接进来的, 4:用来调试的
     */
    protected function initPage($type,$from){
        /*******************初始化******/
        $wechat = $this->initWechat($type);

//        $FUserId = $_COOKIE['FUserId' . $type];
//        $FDeviceId = $_COOKIE['FDeviceId' . $type];
        $FUserId = $_SESSION['FUserId' . $type];
        $FDeviceId = $_SESSION['FDeviceId' . $type];
        // 强制拉取授权， 防刷红包
        // 2016-12-27 ShengYue
        $constraint = intval($_GET['constraint']);
        $error = '';

        switch ($from) {
            case 1:

                //如果cookie里的FDeviceId为空（没有进去过），或者为notNeedDeviceid（从公众号点击会设置上，要重设)
                /**
                 * @shengyue 2016-05-26 session改成redis
                 */

                if (
                    empty($FDeviceId) ||
                    empty($FUserId) ||
                    $FDeviceId === "notNeedDeviceid" ||
                    empty($_SESSION["openid".$type]) ||
                    !($_SESSION["isYaoYao" . $type])
                ){
                    $ticket = $_GET['ticket'];
                    if (empty($ticket)) {  //ticket 参数为空
                        $reurl = $_SERVER['HTTP_REFERER'];
                        $url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
                        File::write_file(APP_PATH .'log/error_yaoyiyao.log',date("Y-m-d H:i:s")."票据为空~ url={$url}&reurl={$reurl}&session=" .json_encode($_SESSION).
                            " --city: ".$type."&from={$from} 摇一摇设备无法获取票据\r\n",'a+');
                        $error = "摇一摇设备无法获取票据"."deviceid=$FDeviceId&FUserId=$FUserId&openid={$_SESSION["openid".$type]}&isyaoyao={$_SESSION["isYaoYao" . $type]}".'+';
                        break;
                    }

                    //从微信摇一摇接口获取设备id及用户id
                    $user = $wechat->getShakeInfoShakeAroundUser($ticket);

                    if (empty($user)) {
                        $error = "票据用户获取失败,请重新摇一摇";
                        File::write_file(APP_PATH .'log/error_yaoyao.log',date("Y-m-d H:i:s"). "摇一摇空返回错误: ticket-" . $ticket . "--errCode: " . $wechat->errCode ."--errMsg: " .$wechat->errMsg. "--city: ".$type."\r\n",'a+');
                        break;
                    }
                    //记录用户openid
                    //$user = json_decode($user_data);
                    $FUserId = $user['data']['openid'];
                    $this->openid = $FUserId;
                    $uuid = $user['data']['beacon_info']['uuid'];
                    if (empty($FUserId)) {
                        File::write_file(APP_PATH .'log/error_yaoyao.log', date("Y-m-d H:i:s")."摇一摇空openid,ticket: " . $ticket . "--errCode: " . $wechat->errCode ."--errMsg: " .$wechat->errMsg. "--city: ".$type."\r\n",'a+');
                        $error = "票据已失效,请重新摇一摇";
                        break;
                    }

                    $_SESSION["openid".$type] =  $FUserId;
                    $_SESSION["isYaoYao" . $type] = true;
                    $_SESSION['FUserId' . $type] =  $FUserId;//, time() + 1800,'/',C('COOKIE_DOMAIN'));

                    $_SESSION['beacon_info'.$type] = $user['data'];
                    $_SESSION["openid".$type] = $FUserId;
                    $_SESSION['FDeviceId' . $type] = $uuid;
                    // 2017-02-22 @ShengYue 对被刷设备404处理
                    if(in_array($user['data']['beacon_info']['minor'],[52368,17213,63276])){
                        $_SESSION['FUserId' . $type] = '';
                        $_SESSION['FDeviceId' . $type] = 'notNeedDeviceid';
                        header("Content-type: text/html; charset=utf-8");
                        echo '<span style="font-size: 20px;;">404</span>';
                        die();
                    }
                    $_SESSION['minorId'.$type] = $user['data']['beacon_info']['minor'];
                    $_SESSION['majorId'.$type] = $user['data']['beacon_info']['major'];

                    // 2017-1-3 @ShengYue 查找设备信息
                    //
                    $device = D('Devices')->get_device_info($type,intval($user['data']['beacon_info']['major']),
                        intval($user['data']['beacon_info']['minor']));
                    if($device){
                        $_SESSION['deviceId'.$type] = $device['device_id'];
                    }

                    M('yaoyiyao_log')->add([
                        'city_id'=>$type,
                        'openid' =>$user['data']['openid'],
                        'minor'=>$user['data']['beacon_info']['minor'],
                        'major' => $user['data']['beacon_info']['major'],
                        'device_id' => intval($device['device_id']),
                        'ip'=>get_client_ip(),
                        'agent'=>substr($_SERVER['HTTP_USER_AGENT'],0,225),
                        'create_time'=>time()
                    ]);

                    $user_info = D('Users')->get_user($FUserId);

                    if(!$user_info){
                        $userInfo = $this->getUserInfo($type);

                        //openid,nickname,sex,province,city,country,headimgurl,unionid
                        $city_info = D('City')->get_city($type);
                        $unionid = $userInfo['unionid'];
                        /*
                         `id`, `unionid`, `openid`, `cityid`, `wx_name`, `wx_pic`, `wx_sex`, `wx_city`, `wx_province`, `wx_country`,
                        `wx_remark`, `wx_groupid`, `tag_list`, `beacon_id`, `is_subscribe`, `subcribe_time`, `unsubcribe_time`, `create_time`*/
                        $users = [
                            'openid' =>$FUserId,
                            'cityid' => $type,
                            'platform' => intval($city_info['platform']),
                            'wx_name' =>strval($userInfo['nickname']),
                            'unionid' =>strval($userInfo['unionid']),
                            'wx_sex' =>intval($userInfo['sex']),
                            'wx_city' =>strval($userInfo['city']),
                            'wx_province'=>strval($userInfo['province']),
                            'wx_country' => strval($userInfo['country']),
                            'wx_pic' => strval($userInfo['headimgurl']),
                            'is_subscribe' => intval($userInfo['subscribe']),
                            'subcribe_time' => intval($userInfo['subscribe_time'])
                        ];

                        // 初始化用户数据 包括 users 记录, users_union记录 , users_brand记录
                        D('Users')->init_user($users);
                    }else{
                        $unionid = $user_info['unionid'];
                        if(empty(trim($user_info['wx_name']))){
                            $userInfo = $this->getUserInfo($type);

                            //openid,nickname,sex,province,city,country,headimgurl,unionid
                            $city_info = D('City')->get_city($type);
                            $unionid = $userInfo['unionid'];
                            /*
                             `id`, `unionid`, `openid`, `cityid`, `wx_name`, `wx_pic`, `wx_sex`, `wx_city`, `wx_province`, `wx_country`,
                            `wx_remark`, `wx_groupid`, `tag_list`, `beacon_id`, `is_subscribe`, `subcribe_time`, `unsubcribe_time`, `create_time`*/
                            $users = [
                                'wx_name' =>strval($userInfo['nickname']),
                                'wx_sex' =>intval($userInfo['sex']),
                                'wx_city' =>strval($userInfo['city']),
                                'wx_province'=>strval($userInfo['province']),
                                'wx_country' => strval($userInfo['country']),
                                'wx_pic' => strval($userInfo['headimgurl'])
                            ];

                            D('Users')->update_user($FUserId, $users);
                        }

                    }
                    $ip = get_client_ip();
                    $agent = $_SERVER['HTTP_USER_AGENT'];
                    $login_log = [
                        'openid' => $FUserId,
                        'unionid' => $unionid,
                        'login_time' => time(),
                        'login_ip' => $ip,
                        'login_agent' => substr($agent,0,200),
                        'city_id' => $type
                    ];

                    M('users_login_log')->add($login_log);
                }else{
                    //从微信摇一摇接口获取设备id及用户id
                    $ticket = $_GET['ticket'];

                    //从微信摇一摇接口获取设备id及用户id
                    $user = $wechat->getShakeInfoShakeAroundUser($ticket);

                    if ($user) {
                        // 2017-1-3 @ShengYue 查找设备信息
                        //
                        $device = D('Devices')->get_device_info($type,intval($user['data']['beacon_info']['major']),
                            intval($user['data']['beacon_info']['minor']));
                        if($device){
                            $_SESSION['deviceId'.$type] = $device['device_id'];
                        }

                        M('yaoyiyao_log')->add([
                            'city_id'=>$type,
                            'openid' =>$user['data']['openid'],
                            'minor'=>$user['data']['beacon_info']['minor'],
                            'major' => $user['data']['beacon_info']['major'],
                            'device_id' => intval($device['device_id']),
                            'ip'=>get_client_ip(),
                            'agent'=>substr($_SERVER['HTTP_USER_AGENT'],0,225),
                            'create_time'=>time()
                        ]);
                        $_SESSION['beacon_info'.$type] = $user['data'];
                        $_SESSION['minorId'.$type] = $user['data']['beacon_info']['minor'];
                        $_SESSION['majorId'.$type] = $user['data']['beacon_info']['major'];

//                        if($type == 28){
//                            File::write_file(APP_PATH .'log/yyyyy.log',date("Y-m-d H:i:s")." minor={$user['data']['beacon_info']['minor']}===openid=={$FUserId}--=city: ".$type."= 摇一摇设备无法获取票据\r\n",'a+');
//                        }
                    }

                }

                break;
            case 2:
            case 3:
                /**
                 * @shengyue 2016-05-26 session改成redis
                 */
                //if (empty($FUserId) || !isset($_SESSION["openid".$type]))
                 if (empty($FUserId) || empty($_SESSION["openid".$type]) || $constraint)
                 //if(true)
                {
                    //add by allen for 提交数据过程中正好出现cookie 或session过期
                    if(IS_POST || IS_AJAX){

                        $reffer = $_SERVER['HTTP_REFERER'];
                        File::write_file(APP_PATH .'log/error.log',  " authorize error : " . '提交数据过程中正好出现cookie 或session过期 cooke ='.$FUserId." session =".$_SESSION["openid".$type].
                                 "reffer={$reffer}&====url = ".$_SERVER["REQUEST_URI"]." g =".json_encode($_GET)." p = ".json_encode($_POST)
                                ."\r\n",'a+');
                        echo json_encode(array("state" => -1, "data" => "", "msg" => "请刷新页面，重试^-^^-^"));
                        die();
                    }

                    //用户授权
                    $info = $this->authorize($type);
                    if ($info) {
                        $FUserId = $info['openid'];

                        $_SESSION["FUserId" . $type] = $info['openid'];
                        $_SESSION['FDeviceId' . $type] = "notNeedDeviceid";

                        // $_SESSION["openid".$type] = $FUserId;
                        $_SESSION["openid".$type] = $FUserId;

                        // 获取微信用户信息
                        $weObj = $this->initWechat($type);
                        $openid = $info["openid"];
                        $access_token = $info["access_token"];
                        $info = $weObj->getOauthUserinfo($access_token, $openid);

                        //openid,nickname,sex,province,city,country,headimgurl,unionid
                        $city_info = D('City')->get_city($type);
                        /*
                         `id`, `unionid`, `openid`, `cityid`, `wx_name`, `wx_pic`, `wx_sex`, `wx_city`, `wx_province`, `wx_country`,
                        `wx_remark`, `wx_groupid`, `tag_list`, `beacon_id`, `is_subscribe`, `subcribe_time`, `unsubcribe_time`, `create_time`*/
                        $users = [
                            'openid' =>$openid,
                            'cityid' => $type,
                            'platform' => strval($city_info['platform']),
                            'wx_name' =>strval($info['nickname']),
                            'unionid' =>strval($info['unionid']),
                            'wx_sex' =>intval($info['sex']),
                            'wx_city' =>strval($info['city']),
                            'wx_province'=>strval($info['province']),
                            'wx_country' => strval($info['country']),
                            'wx_pic' => strval($info['headimgurl'])
                        ];
                        $user_info = D('Users')->get_user($openid);
                        if($user_info){
                            $unionid = $user_info['unionid'];
                            D('Users')->update_user($openid, $users);
                        }else{
                            $unionid = $users['unionid'];
                            // 初始化用户数据 包括 users 记录, users_union记录 , users_brand记录
                            D('Users')->init_user($users);
                        }

                        $ip = get_client_ip();
                        $agent = $_SERVER['HTTP_USER_AGENT'];
                        $login_log = [
                            'openid' => $FUserId,
                            'unionid' => $unionid,
                            'login_time' => time(),
                            'login_ip' => $ip,
                            'login_agent' => substr($agent,0,200),
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

                $_SESSION['FDeviceId' . $type] = "notNeedDeviceid";
                $_SESSION['minorId'.$type] = '';
                /**
                 * @shengyue 2016-05-6 session改成redis
                 */
                //$_SESSION["isYaoYao" . $type] = false;
                $_SESSION["isYaoYao" . $type] = false;
                break;
            default:

                $error = "访问来源无法确定".$from;
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
                $expire = $info["expires_in"] ? intval($info["expires_in"]) - 100 : 3600;
                $oauthname = "oauth_access_token" . $type . $info["openid"];
                //$_SESSION[$oauthname] = $info['access_token'];
                $_SESSION[$oauthname] =  $info['access_token'];
                return $info;
            } else {
                File::write_file(APP_PATH.'log/error.log', "getOauthAccessToken error,errCode: city_id={$type}=" . $weObj->errCode . "  errMsg: " . $weObj->errMsg."\r\n",'a+');
            }
            return false;
        } else {
            $url = $weObj->getOauthRedirect('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], uniqid());
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

    /**
     * 修改M币商城兑换规则
     * @author maofei 2016-10-22
     * @param  int $type  1 抽奖  2 刮刮卡   3 M币兑换
     */
    public function exchangeRules($type=3){
    	$data = array("state" => 55, "data" => "", "msg" => "符合兑换规则");
    	//$theObj   兑换规则设置
    	$theObj=M("exchange_rules")->where("id=1")->find();
    	$now_time=time();
    	//如果不在有效活动时间内或者兑换规则无效，则不做限制条件
    	if( $theObj['begin_time']<$now_time && $theObj['end_time']>$now_time && $theObj['status']==1 ){
    		$draw_num=$theObj['draw_num'];	//限定抽奖次数
    		$gua_num=$theObj['gua_num'];		//限定刮刮卡次数
    		$mb_num=$theObj['mb_num'];			//限定M币商城兑换次数
    		
    		if($type ==2){
    			$theArray['uid']=$this->usersMember['id'];
    			$theArray['updated_at']=array(array("egt",$theObj['begin_time']),array("elt",$theObj['end_time']));
    			//array(array("egt",$theObj['begin_time']),array("elt",$theObj['end_time']))
    			
    			$theArray['status']=1;  //0未刮，1已刮
    			//刮刮乐
    			$gglNum=M('card_record')->where($theArray)->count();
    			//判断是否超过限定次数
    			if($gua_num >0){
    				if( $gglNum >=$gua_num ){
    					$data['state']=50;
    					$data['msg']="不符合兑换规则";
    					$data['data']=$theObj['popup_txt'];
    					$data['msg']=$theObj['btn_txt'];
    				}
    			}
    		}elseif ($type ==1){
    			$condition['user_id']=$this->usersMember['id'];
//     			$condition['create_time']=array("egt",$theObj['begin_time']);
//     			$condition['create_time']=array("elt",$theObj['end_time']);
    			$condition['create_time']=array(array("egt",$theObj['begin_time']),array("elt",$theObj['end_time']));
    			//抽奖
    			$cjNum =  M("users_lottery")->where($condition)->count();
    			//判断是否超过限定次数
    			if($draw_num >0){
    				if( $cjNum >=$draw_num ){
    					$data['state']=50;
    					$data['msg']="不符合兑换规则";
    					$data['data']=$theObj['popup_txt'];
    					$data['msg']=$theObj['btn_txt'];
    				}
    			}
    		}elseif($type ==3){
    			//M币兑换
    			$condition2['user_id']=$this->usersMember['id'];
//     			$condition2['create_time']=array("egt",$theObj['begin_time']);
//     			$condition2['create_time']=array("elt",$theObj['end_time']);
    			$condition2['create_time']=array(array("egt",$theObj['begin_time']),array("elt",$theObj['end_time']));
    			$dhNum = M('users_exchange')->where($condition2)->count();
    			//判断是否超过限定次数
    			if($mb_num >0){
    				if( $dhNum >=$mb_num ){
    					$data['state']=50;
    					$data['msg']="不符合兑换规则";
    					$data['data']=$theObj['popup_txt'];
    					$data['msg']=$theObj['btn_txt'];
    				}
    			}
    		}
    			
    	}else{
    		//不做限制条件
    		$data['state']=55;
    		$data['msg']="不限制兑换规则";
    	}
    	return $data;
    }


    /**
     * 兑换操作
     * @param $goods_id
     * @param $content
     * @param int $check  如果等于1 只检测是否能兑换
     * @return array
     */
    public function goods_order_post($goods_id,$content,$check=0){

        $json = $this->ajax_json();
        $json['state'] = 1;
        $json['data']['type'] = 4;
        $json['data']['address'] = '';
        $json['data']['url'] = '';

        //begin
        $data_rules=$this->exchangeRules(3);
        if( !empty($data_rules) && ($data_rules['state'] ==50) ){
            $json['data']['type']=9;
            $json['data']['content'] = $data_rules['data'];
            return $json;
        }
        $config = D('config');
        if(empty($goods_id)){
            $json['data']['content'] = '请选择兑换商品';
            return $json;
        }

        $goods = D('Goods')->get_goods($goods_id);
        if(empty($goods)){
            $json['data']['content'] = '没找到对应的兑换商品';
            return $json;
        }

        if( !in_array($goods['goods_class'],array(1,3))){
            $json['data']['content'] = '该商品不能兑换';
            return $json;
        }

        if(empty($this->usersMember)){
            $json['data']['content'] = '先注册才能参与兑换';
            return $json;
        }

        // 库存不够
        if($goods['sku'] < 1){
            $json['data']['content'] = '该商品已经被兑完';
            return $json;
        }

        // 用户抽奖金额
        if(intval($goods['goods_credit']) > intval($this->usersBank['total_integral'])){
            $exchangeLackMb = $config->get_config_one('exchange_lack_mb');
            $json['data']['content'] = $exchangeLackMb['config_data'] ?: '账户M币不足, 无法参与抽奖';
            $json['data']['address'] = '';
            $json['data']['url'] = '';
            $json['state'] = 99;
            $json['data']['type'] = 3;
            return $json;
        }

        // 用户兑换次数
        $total = M('users_exchange')->where(array('openid'=>$this->openid,'goods_id'=>$goods['id']))->select();
        if(count($total) >= $goods['per_limit']){
            $json['msg'] = '你已经兑换过该商品, 不能再参与兑换了';
            $json['state'] = 5;
            $json['data'] = tsurl('/user/detail',array('id'=>$total[0]['id'],'class'=>'exchange'));
            return $json;
        }

        if($goods['sku'] < 1){
            $json['data']['content'] = '商品库存不足, 不能参与兑换了';
            return $json;
        }

        if($check == 1){
            return $check;
        }

        File::write_file(APP_PATH . 'log/recharge.log', "order-check:".$check."\r\n", 'a+');

        //////////////////////////////////////////////兑换开始
        $json = D('Goods')->get_goods_order($goods,$this->usersUnion,$this->usersBank,$this->users['id'],$this->openid,$this->usersMember['id'],$this->type,$this->users['unionid'],$content);
        //////////////////////////////////////////////兑换结束

        if($json['state'] == 3){
            $card = $this->getCardSign($this->type,true,$goods['card_id']);
            $json['data'] = array(
                array(
                    'cardId' => $goods['card_id'],
                    'cardExt' => '{"timestamp":"'.$card['time'].'","signature":"'.$card['ticket'].'"}'
                )
            );
        }

        return $json;

    }






















}