<?php
namespace Home\Controller;
use Think\Controller;
use Think\Page;
use Weixin\MyWechat;
class UserController extends BaseController {

    public function _initialize()
    {
        parent::_initialize();
        // 没登陆自动登录
        if(empty($this->users['mobile'])){
            return header("location: ".tsurl('/register/index'));
        }
    }

    public function index(){

        $this->display();
    }

    /**
     * 个人月详情
     */
    public function money_record(){

        $where = [];
        $where['user_id'] = $this->users['bind_user_id'];

        $db = M('amount_record'); // 实例化User对象
        $count = $db->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $db->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 提现流水
     */
    public function cash_record(){

    }

    /**
     * 提现操作
     */
    public function cash(){
        $beginTime = strtotime('first day of this month midnight');
        $endTime = strtotime('-1 second first day of next month midnight');
        $cash_count = D('cash_record')->where("openid = '{$this->openid}' AND `city_id` = {$this->type} AND `create_time` BETWEEN {$beginTime} AND {$endTime}")->count();
        $this->assign('surplus_cash_count', (3 - $cash_count));

        $this->display();
    }

    /**
     * 用户提现
     */
    public function cashSave()
    {
        $result = $this->ajax_json();
        $result['state'] = 5;
        $result['data'] = tsurl('/user/index');
        do{
            $money = I('post.money',0,'floatval');
//            $result['msg'] = '系统维护，提现将在2016年8月9日恢复，请耐心等待。对于任何形式的作弊行为，本公司持有法律起诉权。';
//            break;
            if(empty($this->admin) || $this->admin['status'] != 1){
                $result['msg'] = '您的账户异常~ 请先联系服务人员确认~';
                break;
            }
            $time = time();
            $title = '提现';
            //判断提现次数
            $beginTime = strtotime('first day of this month midnight');
            $endTime = strtotime('-1 second first day of next month midnight');
            $cash_count = D('cash_record')->where("`openid` = '{$this->openid}' AND `city_id` = {$this->type} AND `create_time` BETWEEN {$beginTime} AND {$endTime}")->count();
            if ($cash_count >= 3) {
                $result['msg'] = '本月提现次数已用完，无法提现';
                break;
            }

            //判断提现金额
            if ($money < 1) {
                $result['msg'] = '提现的金额不能小于1元';
                break;
            }

            //判断余额
            $total_amount = $this->admin['total_amount'];

            if ($total_amount < $money) {
                $result['msg'] = '余额不足,无法提现';
                break;
            }

//            //企业付款

            $partner_trade_no = get_order_no(); // 本地订单号

            //修改用户余额、记录本次交易
            // 开始操作
            M()->startTrans();

            // `id`, `cash_amount`, `order_sn`, `user_id`, `openid`, `city_id`, `payment_no`, `payment_log`, `status`, `is_send`, `create_time`
            $users_cash_record_data = [
                'openid' => $this->openid,
                'city_id' => $this->type,
                'cash_amount' => $money,
                'payment_no'=>'',
                'user_id' => intval($this->admin['id']),
                'order_sn' => $partner_trade_no,
                'create_time' => $time
            ];

            $a3 = M('cash_record')->add($users_cash_record_data);
            // `record_type`, `user_id`, `water_id`, `amount`, `total_amount`, `create_time`
            $users_money_record = [
                'user_id'=> intval($this->admin['id']),
                'city_id' => intval($this->type),
                'amount' => $money,
                'water_id' => $a3,
                'total_amount' => floatval($this->admin['total_amount'] + $money),
                'create_time' => $time
            ];
            $a2 = M('amount_record')->add($users_money_record);
            $a1 = M()->execute("UPDATE ".C('DB_PREFIX')."admin SET update_time='$time', total_amount=total_amount-{$money},
            total_cash_amount=total_cash_amount+{$money} WHERE id='{$this->admin['id']}' AND total_amount>={$money}");

            if ($a1 && $a2 && $a3) {
                M()->commit();
                //////////////////////////////////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////具体体现操作//
                $city = D('city')->get_city($this->type);
                $data = [
                    'mch_appid' => $city['appid'],
                    'mchid' => $city['mchid'],
                    'partner_trade_no' => $users_cash_record_data['order_sn'],
                    'openid' => $this->openid,
                    'check_name' => 'NO_CHECK',
                    'amount' => $users_cash_record_data['cash_amount'] * 100,
                    'desc' => "用户提现",
                ];
                $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
                $returnData = MyWechat::pay($url, $data, $city['zhifu'], $this->type);
                //$this->assign('returnData', json_encode($data));
                if ($returnData) {
                    $wechat_pay_record_data = [
                        'openid' => $users_cash_record_data['openid'],
                        'city_id' => $users_cash_record_data['city_id'],
                        'reopenid' => $this->openid,
                        'money' => $users_cash_record_data['cash_amount'],
                        'partner_trade_no' => $users_cash_record_data['order_sn'],
                        'payment_no' => strval($returnData['payment_no']),
                        'return_msg' => strval($returnData['return_msg']),
                        'result_code' => strval($returnData['result_code']),
                        'err_code' => strval($returnData['err_code']),
                        'err_code_des' => strval($returnData['err_code_des']),
                        'spbill_create_ip' => strval($_SERVER['SERVER_ADDR']),
                        'type' => 1,
                        'created_at' => $time,
                    ];

                    M('wechat_pay_record')->add($wechat_pay_record_data);

                    if ($returnData['result_code'] != 'SUCCESS') {
                        M('cash_record')->where(array('id'=>$a3))->save(array('payment_log'=>strval($returnData['err_code_des'])));
                        $result['msg'] = '提现金额稍后会转入到你的余额账户,谢谢~';
                        break;
                    }else{
                        // 更改提现状态
                        M('cash_record')->where(array('id'=>$a3))->save(array('status'=>1, 'payment_no'=>strval($returnData['payment_no'])));
                        $result['msg'] = '恭喜您，红包提现成功，请查看微信钱包的零钱收入';
                        break;
                    }
                } else {
                    $result['msg'] = '提现金额稍后会转入到你的余额账户,谢谢~';
                    break;
                }
                //////////////////////////////////////////////////////////////////////////////////////////////////////////
            } else {
                M()->rollback();
                $result[ 'msg'] = '太火爆了，等会儿再来呗！';

            }
        }while(false);
        $this->ajaxReturn($result);
    }

    /**
     * 更换手机验证码
     */
    public function vercode_api(){
        $json = $this->ajax_json();
        do{
            $mobile = I('request.mobile','','strval');
            $sign = I('request.sign','','strval');

            if(!$mobile){
                $json['msg'] = '请正确数据手机号码';
                break;
            }

            if(! preg_match("/^1[34578]\d{9}$/", $mobile)){
                //手机格式不通过
                $json['msg'] = '请正确输入手机号码';
                break;
            }

            // 判断手机是否注册
            $member = D('Users')->where(['mobile'=>$mobile])->find();
            if($member){
                $json['msg'] = '该手机已经被注册了~';
                break;
            }

            $member = D('Admin')->where(['mobile'=>$mobile])->find();
            if(!$member){
                $json['msg'] = '该手机号不能注册，请联系服务确认~';
                break;
            }

            $code = create_code();
            $bool = send_msg($mobile, "【魔座驾到】您的验证码是".$code, $code, $this->openid);
            if(!$bool){
                $json['msg'] = '短信发送失败，请不要频繁提交';
                break;
            }

            $json['state'] = 1;
            $json['msg'] = null;
            $json['data'] = null;

        }while(false);
        echo json_encode($json);die();
    }

    // 提交更换绑定手机
    public function post_change_bind(){
        $mobile = I('post.mobile','','strval');
        $code = I('post.vercode','','strval');
        $json = $this->ajax_json();
        do {
            if (!preg_match("/^1[34578]\d{9}$/", $mobile)) {
                //手机格式不通过
                $json['msg'] = '请正确输入手机号码';
                break;
            }
            if (!check_code($code,$mobile)) {
                // 短信验证不通过
                $json['msg'] = '验证码错误';
                break;
            }

            $member = D('Users')->where(['mobile'=>$mobile])->find();
            if($member){
                $json['msg'] = '该手机已经被注册了~';
                break;
            }

            // 账户异常~
            if(empty($this->users['bind_user_id'])){
                $json['msg'] = '账户没有绑定用户~ 请联系服务人员~';
                break;
            }

//            $member = D('Admin')->where(['mobile'=>$mobile])->find();
//            if(!$member){
//                $json['msg'] = '该手机号不能注册，请联系服务确认~';
//                break;
//            }

            // 后台
            $res = D('Users')->where(['id'=>$this->users['id']])->save(array('update_time'=>time(), 'mobile'=>$mobile));
            // D('Admin')->where(['id'=>$this->users['bind_user_id']])->save(['mobile'=>$mobile]);
            if($res){
                $json['state'] = 5;
                $json['msg'] = '绑定成功';
                $json['data'] = tsurl('/user/index');
            }else{
                $json['msg'] = '手机绑定失败';
                break;
            }
        }while(false);
        $this->ajaxReturn($json);
    }

    public function test(){
        $users_cash_record_data = M('cash_record')->find();
        $city = D('city')->get_city($this->type);
        $data = [
            'mch_appid' => $city['appid'],
            'mchid' => $city['mchid'],
            'partner_trade_no' => $users_cash_record_data['order_sn'],
            'openid' => $this->openid,
            'check_name' => 'NO_CHECK',
            'amount' => $users_cash_record_data['cash_amount'] * 100,
            'desc' => "用户提现",
        ];
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $returnData = MyWechat::pay($url, $data, $city['zhifu'], $this->type);
        var_dump($returnData);
    }
}