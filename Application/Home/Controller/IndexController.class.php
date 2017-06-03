<?php
namespace Home\Controller;
use Think\Controller;
require_once LIB_PATH .'Wxpay/weixin.class.php';
require_once LIB_PATH ."Wxpay/lib/WxPay.Config.php";
class IndexController extends BaseController {
    public $device_id = 0;
    public $device_info = [];
    public function _initialize()
    {
        parent::_initialize();

        // 如果扫描了其他按摩椅
        $device_qr = $_REQUEST['qr'];
        if($device_qr) {
            if ($_SESSION['global_qr'] != $device_qr) {
                $_SESSION['global_qr'] = $device_qr;
            }
        }

        // 默认删除qr 防止分享出去
        if($_REQUEST['qr']){
            $get = (array)$_GET;
            unset($get['qr']);
            $get['test'] = time();
            $url = tsurl(CONTROLLER_NAME.'/'.ACTION_NAME,$get);
            return header("Location: ".$url);
        }

        // 如果没有按摩椅
        if(empty($_SESSION['global_qr'])){
             $this->assign("msg", "抱歉~ 请重新扫描按摩椅二维码试下吧~");
             $this->display("error");
            die();
        }

        // 设备初始化
        $this->device_info = M('devices')->where(['qrcode'=>$_SESSION['global_qr']])->find();
        if(empty($this->device_info)){
            $this->assign("msg", "抱歉~ 请正确扫描按摩椅~");
            $this->display("error");
            die();
        }



        if($this->device_info['status'] != 1){
            $this->assign("msg", "抱歉~ 该按摩椅暂不提供服务~ 请扫描其他按摩椅吧~");
            $this->display("error");
            die();
        }



        $this->device_id = intval($this->device_info['id']);

    }

    /**
     *
     */
    public function index(){
        $package_list = M('package')->where(['status'=>1])->order("weight DESC, id ASC")->select();
        $this->assign('package_list', $package_list);
        $this->display();
    }

    /*
    * 初始化wxpayconfig配置
    */
    private function wpconfig(){
        $cityInfo = D('City')->get_city($this->type);
        c('WXPAY.APPID', $cityInfo['appid']);
        c('WXPAY.APPSECRET',$cityInfo['appsecret']);
        c('WXPAY.MCHID', $cityInfo['mchid']);
        c('WXPAY.KEY', $cityInfo['zhifu']);

        $path = LIB_PATH . 'Weixin/zhengshu_' . $this->type;
        c('WXPAY.SSLCERT_PATH', $path . '/apiclient_cert.pem');
        c('WXPAY.SSLKEY_PATH', $path . '/apiclient_key.pem');
        $config = new \WxPayConfig();
    }

    /**
     * 支付订单
     */
    public function order(){
        $json = $this->ajax_json();
        $json['state'] = 99;
        $json['error'] = 1;
        $paackage_id = I('request.package_id',0,'intval');
        $spread_id = I('request.spread_id',0,'intval');
        do{
            if(empty($paackage_id)){
                $json['msg'] = "请选择购买套餐~";
                break;
            }

            $package_info = M('package')->where(['id'=>$paackage_id])->find();
            if(empty($package_info) || $package_info['status'] != 1){
                $json['msg'] = "购买套餐已下架~";
                break;
            }

            // 参与分成用户
            $user_spread_id = 0;
            $user_device_id = intval($this->device_info['user_id']);
            $user_channel_id = intval($this->device_info['channel_user_id']);
            $user_operational_id = intval($this->device_info['operational_user_id']);
            $user_platform_id = 1;

            // 分成规则
            $rebate_info = [];

            // 优先级 1. 推广分成， 2. 设备所有者分成, 3. 渠道分成 4. 运营分成
            if($spread_id){
                $spread_info = M('admin')->where(['id'=>$spread_id])->find();
                if($spread_info){
                    $user_spread_id = $spread_id;
                    if($spread_info['rebate_id']){
                        $rebate_info = M('rebate')->where(['id'=>$spread_info['rebate_id'],'status'=>1])->find();
                    }
                }
            }

            // 如果没推广规则， 使用设备所有者规则
            if(empty($rebate_info)){
                //
                $device_user = M('admin')->where(['id'=>$user_channel_id])->find();
                if($device_user['rabate_id']){
                    $rebate_info = M('rebate')->where(['id'=>$device_user['rabate_id'],'status'=>1])->find();
                }
            }

            // 如果没推广规则， 使用渠道所有者规则
            if(empty($rebate_id)){
                //
                $channel_user = M('admin')->where(['id'=>$user_channel_id])->find();
                if($channel_user['rabate_id']){
                    $rebate_info = M('rebate')->where(['id'=>$channel_user['rabate_id'],'status'=>1])->find();
                }
            }

            // 如果没推广规则， 使用运营所有者规则
            if(empty($rebate_id)){
                //
                $operational_user = M('admin')->where(['id'=>$user_operational_id])->find();
                if($operational_user['rabate_id']){
                    $rebate_info = M('rebate')->where(['id'=>$operational_user['rabate_id']['rabate_id'],'status'=>1])->find();
                }
            }

            // 默认系统返利规则
            if(! $rebate_info ){
                $rebate_info = M('rebate')->where(['rebate_type'=>0])->find();
            }

            if( ! $rebate_info){
                $json['msg'] = "系统有误，请联系服务人员~";
                break;
            }

            // 分成计算
            // M()->startTrans();
            
            $operational_price = number_format(($package_info['package_amount'] * $rebate_info['operational_rebate'] / 100),2,'.','');
            $channel_price = number_format(($package_info['package_amount'] * $rebate_info['channel_rebate'] / 100),2,'.','');
            $device_price = number_format(($package_info['package_amount'] * $rebate_info['device_rebate'] / 100),2,'.','');
            if($spread_info){
                $spread_price = number_format(($package_info['package_amount'] * $rebate_info['spread_rebate'] / 100),2,'.','');
            }else{
                $spread_price = 0;
            }

            $platform_price = $package_info['package_amount'] - $operational_price - $channel_price - $device_price - $spread_price;
            ////////////////////////////////////////////////////////////////
            // `id`, `openid`, `device_id`, `package_id`, `package_amount`, `package_time`, `order_sn`, `status`, `start_status`,
            // `start_log`, `platform_rebate`, `platform_money`, `operational_rebate`, `operational_user_id`, `operational_money`,
            // `channel_rebate`, `channel_user_id`, `channel_money`, `device_rebate`, `device_user_id`, `device_money`, `spread_rebate`,
            // `spread_user_id`, `spread_money`, `payment_no`, `send_status`, `create_time`, `update_time`, `from_id`,
            // `city_id`, `client_ip`, `client_agent`
            $order_sn = get_order_no();
            $order = [
                'openid' => $this->openid,
                'device_id' => $this->device_id,
                'package_id' => $paackage_id,
                'package_amount' => $package_info['package_amount'],
                'package_time' => $package_info['package_time'],
                'order_sn' => $order_sn,
                'platform_rebate' => $rebate_info['platform_rebate'],
                'platform_money' => $platform_price,
                'operational_rebate' =>$rebate_info['operational_rebate'],
                'operational_money' => $operational_price,
                'operational_user_id' => $user_operational_id,
                'channel_rebate' => $rebate_info['channel_rebate'],
                'channel_money' => $channel_price,
                'channel_user_id' => $user_channel_id,
                'device_rebate' => $rebate_info['device_rebate'],
                'device_money' => $device_price,
                'device_user_id' => $user_device_id,
                'spread_rebate' => $rebate_info['spread_rebate'],
                'spread_money' => $spread_price,
                'spread_user_id' => $user_spread_id,
                'create_time' => time(),
                'from_id' => $this->gfrom,
                'type' => $this->type,
                'client_ip' =>get_client_ip(),
                'client_agent' => substr($_SERVER['HTTP_USER_AGENT'],0,255)
            ];

            $order_id = M('order')->add($order);
            ////////////////////////////////////////////////////////////////
            if($order_id){
                // 初始化支付
                $this->wpconfig();
                $data = [];
                $data['body'] = "购买{$package_info['package_name']}按摩套餐";
                $data['order_sn'] = $order_sn;
                $payment = $order['package_amount'] * 100;
                if( $this->openid == 'ochkGv1tTGLLRNJ0n6VmdkggifIQ'){
                    $payment = 1;
                }
                $data['total_fee'] = $payment;
                $data['goods_tag'] = "MZAM";
                $data['openid'] = $this->openid;
                $data['notify_url'] = "http://mz.hotwifibox.com/pay/notify/type/{$this->type}.html";

                $jsApiParameters = jsapipay($data, true);
                $json['data'] = json_decode($jsApiParameters);
                $json['error'] = 0;
                break;
            }else{
                $json['msg'] = "订单提交失败~";
                break;
            }

        }while(false);
        return $this->ajaxReturn($json);
    }


    /**
     * 邀请人
     */
    public function spread(){
        $spread_list = M('devices_spread')->where(['device_id'=>$this->device_id])->select();

        $this->assign('spread_list', $spread_list);
        $package_list = M('package')->where(['status'=>1])->order("weight DESC, id ASC")->select();
        $this->assign('package_list', $package_list);
        $this->display();
    }

    /**
     * 用户退出登录
     */
    public function logout(){
        session('is_login', 0);
        session('login_user_id', 0);
        $this->success('安全退出',U('/login/index'));
    }
}