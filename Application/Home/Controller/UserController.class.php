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

        // 绑定用户~
        if(empty($this->admin)){
            return $this->error("没找到关联用户~", tsurl('/register/index'));
        }
    }

    public function index(){
        $this->display();
    }

    /**
     * 角色流水
     */
    public function record(){
        $role = I('request.role',0,'intval');

        $this->assign('user_role', $role);
        $where = [];
        $where['status'] = 1;
        if($role == 2){
            $where['operational_user_id'] = $this->admin['id'];
        }elseif($role == 3){
            $where['channel_user_id'] = $this->admin['id'];
        }elseif($role == 4){
            $where['device_user_id'] = $this->admin['id'];
        }elseif($role == 5){
            $where['spread_user_id'] = $this->admin['id'];
        }else{
            $where['status'] = 10;
        }

        $count = M('order')->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        $list = M('order')->where( $where)->limit($Page->firstRow . ',' . $Page->listRows)->order("id DESC")->select();
        if($role == 2){
            $total_amount = M('order')->where($where)->sum('operational_money');
        }elseif($role == 3){
            $total_amount = M('order')->where($where)->sum('channel_money');
        }elseif($role == 4){
            $total_amount = M('order')->where($where)->sum('device_money');
        }elseif($role == 5){
            $total_amount = M('order')->where($where)->sum('spread_money');
        }
        $total_amount = number_format(floatval($total_amount),2,'.','');
        $this->assign('total_amount', $total_amount);
        $this->assign('total_pages', $Page->totalPages);
        $this->assign('page', $show);
        $this->assign('list', $list);

        $this->display();
    }

    /**
     * 角色流水
     */
    public function record_api(){
        $role = I('request.role',0,'intval');

        $this->assign('user_role', $role);
        $where = [];
        $where['status'] = 1;
        if($role == 2){
            $where['platform_user_id'] = $this->admin['id'];
        }elseif($role == 3){
            $where['channel_user_id'] = $this->admin['id'];
        }elseif($role == 4){
            $where['device_user_id'] = $this->admin['id'];
        }elseif($role == 5){
            $where['spread_user_id'] = $this->admin['id'];
        }else{
            $where['status'] = 10;
        }

        $count = M('order')->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->show();
        $list = M('order')->where( $where)->limit($Page->firstRow . ',' . $Page->listRows)->order("id DESC")->select();
        $json = $this->ajax_json();

        $json['state'] = 99;
        $json['html'] = [];
        $json['a'] = [];
        foreach($list as $item){
            $price = 0;
            if($role == 2){
                $price = $item['operational_money'];
            }elseif($role == 3){
                $price = $item['channel_money'];
            }elseif($role == 4){
                $price = $item['device_money'];
            }elseif($role == 5){
                $price = $item['spread_money'];
            }
            $json['a'][] = 'wrap '.($item['record_type'] == 2?'color-green':'color-link');
            $json['html'][] = '<span class="wrap-content" style="width: 60%;"><i class="text-overhide">￥'.$item['package_amount'].'订单分成</i><i>'.date("Y-m-d H:i:s",$item['create_time']).'</i></span><span class="color-text">+'.$price.' ￥</span>';
        }

        $json['data']['total_pages'] = $Page->totalPages;
        $this->ajaxReturn($json);
    }

    /**
     * 个人月详情
     */
    public function money_record(){

        $where = [];
        $where['user_id'] = $this->admin['id'];

        $db = M('amount_record'); // 实例化User对象
        $count = $db->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $db->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('total_pages', $Page->totalPages);
        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 个人月详情
     */
    public function money_record_api(){

        $where = [];
        $where['user_id'] = $this->admin['id'];

        $db = M('amount_record'); // 实例化User对象
        $count = $db->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $db->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $json = $this->ajax_json();
        $json['state'] = 99;
        $json['html'] = [];
        $json['a'] = [];
        foreach($list as $item){
            $json['a'][] = 'wrap '.($item['record_type'] == 2?'color-green':'color-link');
            $json['html'][] = '<span class="wrap-content" style="width: 60%;"><i class="text-overhide">'.($item['record_type'] == 1?'订单分成':'余额提现').'</i><i>'.date("Y-m-d H:i:s",$item['create_time']).'</i></span><span class="'.($item['record_type'] == 0?'color-green':'color-text').'">'.($item['record_type'] == 1?'+':'-').$item['amount'].' ￥</span>';
        }
        $json['data']['total_pages'] = $Page->totalPages;
        $this->ajaxReturn($json);
    }

    /**
     * 提现流水
     */
    public function cash_record(){
        $where = [];
        $where['openid'] = $this->openid;

        $db = M('cash_record'); // 实例化User对象
        $count = $db->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $db->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('total_pages', $Page->totalPages);
        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 提现流水
     */
    public function cash_record_api(){
        $where = [];
        $where['openid'] = $this->openid;

        $db = M('cash_record'); // 实例化User对象
        $count = $db->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $db->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $json = $this->ajax_json();
        $json['state'] = 99;
        $json['html'] = [];
        $json['a'] = [];
        foreach($list as $item){
            $json['a'][] = 'wrap color-green';
            $json['html'][] = '<span class="wrap-content" style="width: 60%;"><i class="text-overhide">用户提现</i><i>'.date("Y-m-d H:i:s",$item['create_time']).'</i></span><span class="color-green">'.$item['cash_amount'].' ￥</span>';
        }
        $json['data']['total_pages'] = $Page->totalPages;
        $this->ajaxReturn($json);
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

    /**
     * 绑定设备列表
     */
    public function device_list(){
        /*
         <volist id="shop" name="list">
                        <div class="group">
                            <div class="input_group_block"><input type="checkbox" id="id0" class="group_block" value="1" /> {$shop.shop_name}</div>
                            <volist id="device" name="shop[device_list]">
                            <div class="input_block"><input type="checkbox" id="id1" name="spread_id" value="{$device.id}" /> {$device.device_number}</div>
                            </volist>
                        </div>
                    </volist>
        */
        $json = $this->ajax_json();
        header("Content-Type: text/html; charset=UTF-8");

        $html = "<div style='text-align: center; line-height: 30px;'>暂无可推广设备~</div>";
        $latitude = I('request.latitude',0,'floatval');// 纬度
        $longitude = I('request.longitude',0,'floatval');// 经度

        $spread_distance = intval(C('basic.spread_distance'));

        // 计算指定距离内的门店
        $sql = "SELECT*,ROUND(6378.138 * 2 * ASIN(SQRT(POW( SIN(($longitude * PI() / 180 - lat * PI() / 180) / 2),2) +".
            "COS($longitude * PI() / 180) * COS(lat * PI() / 180) * POW( SIN(($latitude * PI() / 180 - lon * PI() / 180 ) / 2),2)))".
            "* 1000) AS juli FROM t_admin  WHERE role = 3 AND status=1 HAVING  juli<$spread_distance ORDER BY juli ASC";
        $shop_list = M()->query($sql);

        // 计算推广账户~
        $time = time() - intval(C('basic.spread_time')) * 3600;
        $device_list = M('devices_spread')->where(['user_id'=>$this->admin['id'], 'update_time' =>['gt', $time]])->field('channel_user_id')->select();
        $user_list = [];
        foreach($device_list as $row){
            $user_list[] = $row['channel_user_id'];
        }

        if($shop_list){
            $html = '';
            foreach($shop_list as $shop){
                $str = '';
                if(in_array($shop['id'], $user_list)){
                    $str = 'checked';
                }
                $html .= '<div class="group">';
                $html .= '<div class="input_group_block"><input type="checkbox"  '.$str.' class="group_block spread_id" name="spread_id[]" value="'.$shop['id'].'" /> '.$shop['shop_name'].'</div>';
                $html .= '</div>';
            }
        }

        $json['state'] = 1;
        $json['html'] = $html;
        $this->ajaxReturn($json);
    }

    /**
     * 设备绑定
     */
    public function device(){

        $this->display();
    }

    /**
     * 角色对应设备
     */
    public function user_device(){
        $user_role = I('request.role',0,'intval');
        $this->assign('user_role', $user_role);
        $where = [];
        if($user_role == 2){
            $where['operational_user_id'] = $this->admin['id'];
        }elseif($user_role == 3){
            $where['channel_user_id'] = $this->admin['id'];
        }elseif($user_role == 4){
            $where['user_id'] = $this->admin['id'];
        }

        if($user_role == 5){
            // 已经推广过的设备~
            $where = [];
            $where['user_id'] = $this->admin['id'];
            $count = M('devices_spread')->where($where)->count();
            $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

            $show = $Page->show();// 分页显示输出
            // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
            $list = M('devices_spread')->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $i=>$row){
                $device = M('devices')->where(['id'=>$row['device_id']])->find();
                $list[$i]['device_id'] = $device['device_number'];
                $list[$i]['create_time'] = $row['update_time'];
                if($row['user_id']){
                    $list[$i]['user'] = M('admin')->where(['id'=>$row['channel_user_id']])->field('id,username,shop_name')->find();
                }

                if($row['rebate_id']){
                    $rebate_info = M('rebate')->where(['id'=>$row['rebate_id']])->find();
                }else{
                    $rebate_info = M('rebate')->where(['rebate_type'=>0])->find();
                }

                $list[$i]['rebate'] = $rebate_info['spread_rebate'];
            }
        }else{
            $count = M('devices')->where($where)->count();
            $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

            $show = $Page->show();// 分页显示输出
            // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
            $list = M('devices')->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $i=>$row){
                $list[$i]['device_id'] = $row['device_number'];
                $list[$i]['create_time'] = $row['create_time'];
                if($row['user_id']){
                    $list[$i]['user'] = M('admin')->where(['id'=>$row['user_id']])->field('id,username,shop_name')->find();
                }

                if($row['rebate_id']){
                    $rebate_info = M('rebate')->where(['id'=>$row['rebate_id']])->find();
                }else{
                    $rebate_info = M('rebate')->where(['rebate_type'=>0])->find();
                }
                if($user_role == 2){
                    $list[$i]['rebate'] = $rebate_info['operational_rebate'];
                }elseif($user_role == 3){
                    $list[$i]['rebate'] = $rebate_info['channel_rebate'];
                }elseif($user_role == 4){
                    $list[$i]['rebate'] = $rebate_info['device_rebate'];
                }

            }
        }

        $this->assign('show', $show);
        $this->assign('list', $list);
        $this->assign('total_page', $Page->totalPages);
        $this->assign('user_role', $user_role);
        $this->display();
    }

    /**
     * 用户设备API
     */
    public function user_device_api(){
        $user_role = I('request.role',0,'intval');
        $this->assign('user_role', $user_role);
        $where = [];
        if($user_role == 2){
            $where['operational_user_id'] = $this->admin['id'];
        }elseif($user_role == 3){
            $where['channel_user_id'] = $this->admin['id'];
        }elseif($user_role == 4){
            $where['user_id'] = $this->admin['id'];
        }
        $json = $this->ajax_json();
        $json['html'] = [];
        $json['a'] = [];
        if($user_role == 5){
            // 已经推广过的设备~
            $where = [];
            $where['user_id'] = $this->admin['id'];
            $count = M('devices_spread')->where($where)->count();
            $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

            $show = $Page->show();// 分页显示输出
            $json['total_pages'] = $Page->totalPages;
            
            // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
            $list = M('devices_spread')->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $i=>$row){
                $device = M('devices')->where(['id'=>$row['device_id']])->find();
                $user = M('admin')->where(['id'=>$row['channel_user_id']])->field('id,username,shop_name')->find();
                if($row['rebate_id']){
                    $rebate = M('rebate')->where(['id'=>$row['rebate_id']])->find();
                }else{
                    $rebate = M('rebate')->where(['rebate_type'=>0])->find();
                }

                $json['a'] = 'wrap color-link';
                $json['html'] = '<span class="wrap-content" style="width: 60%;"><i class="text-overhide">'.$user['shop_name'].$device['device_number'].'</i><i>'.date("Y-m-d H:i:s",$row['update_time']).'</i></span><span class="color-text">分成比例：'.$rebate['spread_rebate'].'%</span>';
            }
        }else{
            $count = M('devices')->where($where)->count();
            $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

            $show = $Page->show();// 分页显示输出
            $json['total_pages'] = $Page->totalPages;
            // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
            $list = M('devices')->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $i=>$row){
                $list[$i]['device_id'] = $row['device_number'];
                $list[$i]['create_time'] = $row['create_time'];
                $user = M('admin')->where(['id'=>$row['user_id']])->field('id,username,shop_name')->find();
                if($row['rebate_id']){
                    $rebate_info = M('rebate')->where(['id'=>$row['rebate_id']])->find();
                }else{
                    $rebate_info = M('rebate')->where(['rebate_type'=>0])->find();
                }
                $rebate = 0;
                if($user_role == 2){
                    $rebate = $rebate_info['operational_rebate'];
                }elseif($user_role == 3){
                    $rebate = $rebate_info['channel_rebate'];
                }elseif($user_role == 4){
                    $rebate = $rebate_info['device_rebate'];
                }
                $json['a'] = 'wrap color-link';
                $json['html'] = '<span class="wrap-content" style="width: 60%;"><i class="text-overhide">'.$user['shop_name'].$row['device_number'].'</i><i>'.date("Y-m-d H:i:s",$row['update_time']).'</i></span><span class="color-text">分成比例：'.$rebate.'%</span>';

            }
        }
        $json['state'] = 99;
        $this->ajaxReturn($json);
    }

    /**
     * 绑定设备
     */
    public function bind_device(){
        //
        $spread_id = (array)I('spread_id',[],'');
        $json = $this->ajax_json();
        do{
            if(!empty($spread_id) ){
                foreach($spread_id as $spread){
                    $spread_list = M('devices')->where(['user_id'=>$spread, "status"=>1])->field('id')->select();
                    foreach($spread_list as $device_info){
                        $device = M('devices_spread')->where(['device_id'=>$device_info['id'], 'user_id'=>$this->admin['id']])->find();
                        if($device){
                            M('devices_spread')->where(['id'=>$device['id']])->save(['update_time'=>time()]);
                        }else{
                            M('devices_spread')->add(['device_id'=>$device_info['id'],'channel_user_id'=>$spread, 'user_id'=>$this->admin['id'], 'update_time'=>time(),'create_time'=>time()]);
                        }
                    }

                }
            }else{
                $json['msg'] = "请选择推广设备~";
                break;
            }

        }while(false);
        $json['state'] = 1;
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