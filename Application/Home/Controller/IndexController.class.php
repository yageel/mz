<?php
namespace Home\Controller;
use Think\Controller;
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
        $this->device_id = $this->device_info['id'];
    }

    /**
     *
     */
    public function index(){
        $package_list = M('package')->where(['status'=>1])->order("weight DESC, id ASC")->select();
        $this->assign('package_list', $package_list);
        $this->display();
    }

    /**
     * 支付订单
     */
    public function order(){
        $josn = $this->ajax_json();
        $paackage_id = I('request.package_id',0,'intval');
        $spread_id = I('request.spread_id',0,'intval');
        $device_id = $this->device_id;
        do{
            if(empty($paackage_id)){
                $json['msg'] = "请选择购买套餐~";
                break;
            }

            $package = M('package')->where(['id'=>$paackage_id])->find();
            if($package['status'] != 1){
                $json['msg'] = "购买套餐已下架~";
                break;
            }

            // 参与分成用户
            $user_spread_id = 0;
            $user_device_id = $this->device_info['user_id'];
            $user_channel_id = $this->device_info['channel_user_id'];
            $user_operational_id = $this->device_info['operational_user_id'];
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

            //
            if(! $rebate_info ){
                $rebate_info = M('rebate')->where(['rebate_type'=>0])->find();
            }

            if( ! $rebate_info){
                $json['msg'] = "系统有误，请联系服务人员~";
                break;
            }

            // 分成计算
            $operational_rebate = $rebate_info['operational_rebate'];
            M()->startTrans();
            
            $operational_price = number_format(($package['package_amount'] * $rebate_info['operational_rebate'] / 100),2,'.','');
            $channel_price = number_format(($package['package_amount'] * $rebate_info['channel_rebate'] / 100),2,'.','');
            $device_price = number_format(($package['package_amount'] * $rebate_info['device_rebate'] / 100),2,'.','');
            if($spread_info){
                $spread_price = number_format(($package['package_amount'] * $rebate_info['spread_rebate'] / 100),2,'.','');
            }else{
                $spread_price = 0;
            }

            $platform_price = $package['package_amount'] - $operational_price - $channel_price - $device_price - $spread_price;
            /////////////////////////



        }while(false);
        return $this->ajaxReturn($josn);
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