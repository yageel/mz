<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once dirname(__FILE__) . "/lib/WxPay.Api.php";
require_once dirname(__FILE__) . "/example/WxPay.JsApiPay.php";
require_once dirname(__FILE__) . '/lib/WxPay.Notify.php';
require_once dirname(__FILE__) . '/example/log.php';
//初始化日志
//$logHandler= new CLogFileHandler(dirname(__FILE__) . "/logs/".date('Y-m-d').'.log');
$logHandler= new CLogFileHandler("/data/log/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);


function testmodel(){
    $goods_info = D('Goods')->get_goods(69);
    print_r($goods_info);die;
}
//打印输出数组信息
function printf_info($data)
{
    //foreach($data as $key=>$value){
        //echo "<font color='#00ff55;'>$key</font> : $value <br/>";
    //}
    Log::DEBUG(json_encode($data));
}

/**
 * 生成支付接口内容
 * @param $data
 * @param bool $debug
 * @return json
 */
function jsapipay($data, $debug = false){
    // C('weixin.weixin_')
    //①、获取用户openid
    $tools = new JsApiPay();
//    $openId = $tools->GetOpenid();
    if(!empty($data['openid'])){
        $openId = $data['openid'];
    }else{
        echo "empty openid";
        die();
    }

    if(empty($data['order_sn'])){
        echo "empty order sn";
        die();
    }
    //②、统一下单
    $order_sn = $data['order_sn'];
    $input = new WxPayUnifiedOrder();
    $input->SetBody($data['body']);
    $input->SetAttach($data['attach']);
    $input->SetOut_trade_no($order_sn);
    $input->SetTotal_fee($data['total_fee']);
    $input->SetTime_start(date("YmdHis"));
    $input->SetTime_expire(date("YmdHis", time() + 600));
    $input->SetGoods_tag($data['goods_tag']);
    $input->SetNotify_url($data['notify_url']);
    $input->SetTrade_type("JSAPI");
    $input->SetOpenid($openId);

    $order = WxPayApi::unifiedOrder($input);

    if($debug){
        //echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
        Log::DEBUG('data-'.json_encode($data));
        Log::DEBUG('unifiedOrder-'.json_encode($order));
    }


    $jsApiParameters = $tools->GetJsApiParameters($order);
    Log::DEBUG('jsApiParameters-'.json_encode($jsApiParameters));
    return $jsApiParameters;
}

/**
 * 系统回调
 */
function notify(){
    Log::DEBUG("begin notify");
    $notify = new PayNotifyCallBack();
    Log::DEBUG('Come in');
    $notify->Handle(false);
}

/**
 * 退单
 * $transaction_id 微信订单号
 * $out_trade_no 系统订单号
 * $total_fee 订单金额
 * $refund_fee 退款金额
 *
 */
function refund($data = array()){
    $input = new WxPayRefund();
    if($data['transaction_id']){
        $input->SetTransaction_id($data['transaction_id']);
    }

    if($data['out_trade_no']){
        $input->SetOut_trade_no($data['out_trade_no']);
    }

    $input->SetTotal_fee($data['total_fee']);
    $input->SetRefund_fee($data['refund_fee']);
    $input->SetOut_refund_no(get_order_sn());
    $input->SetOp_user_id(WxPayConfig::$MCHID);

    return WxPayApi::refund($input);

}


class PayNotifyCallBack extends WxPayNotify
{

    /**
     * 添加充值记录
     */
    public function czRecord($result,$pay_log_id){
        $order_sn = $result['out_trade_no'];
        $recharge = M('order')->where(['order_sn'=>$order_sn])->find();

        if($recharge && $recharge['status'] < 1){
            $data = array(
                'payment_log_id' => $pay_log_id,
                'pay_time' => time(),
                'payment_no'=>$result['transaction_id'],
                'status' => 1
            );
            $res1 = M('order')->where("partner_trade_no='{$order_sn}'")->save($data);
            ///////////////////////记录流水
            if($res1){
                // 平台进账//
                // `record_type`, `user_id`, `water_id`, `amount`, `total_amount`, `create_time`

                // `openid`, `device_id`, `package_id`, `package_amount`, `package_time`, `order_sn`, `status`, `start_status`,
                // `start_log`, `platform_rebate`, `platform_money`, `operational_rebate`, `operational_user_id`, `operational_money`,
                // `channel_rebate`, `channel_user_id`, `channel_money`, `device_rebate`, `device_user_id`, `device_money`, `spread_rebate`,
                // `spread_user_id`, `spread_money`, `payment_no`, `payment_log_id`, `pay_time`, `send_status`, `create_time`, `update_time`, `
                //`, `city_id`, `client_ip`, `client_agent`
                $user = M('admin')->where(['id'=>1])->find();
                $log = [
                    'record_type' => 1,
                    'user_id' => 1,
                    'water_id' => $recharge['id'],
                    'amount' => $recharge['platform_money'],
                    'total_amount' => $recharge['platform_money'] + $user['total_amount'],
                    'create_time' => time()
                ];
                M('amount_record')->add($log);
                M()->query("UPDATE t_admin SET total_amount = total_amount + '{$recharge['platform_money']}', total_income_amount = total_income_amount+ '{$recharge['platform_money']}',
                  total_sales_amount = total_sales_amount+'{$recharge['package_amount']}', total_orders = total_orders+1  WHERE id=1");

                // 运营进账//
                if($recharge['operational_user_id']){
                    $user = M('admin')->where(['id'=>$recharge['operational_user_id']])->find();
                    $log = [
                        'record_type' => 1,
                        'user_id' => $recharge['operational_user_id'],
                        'water_id' => $recharge['id'],
                        'amount' => $recharge['operational_money'],
                        'total_amount' => $recharge['operational_money'] + $user['total_amount'],
                        'create_time' => time()
                    ];
                    M('amount_record')->add($log);
                    M()->query("UPDATE t_admin SET total_amount = total_amount + '{$recharge['platform_money']}', total_income_amount = total_income_amount+ '{$recharge['platform_money']}',
                      total_sales_amount = total_sales_amount+'{$recharge['package_amount']}', total_orders = total_orders+1  WHERE id='{$recharge['operational_user_id']}'");
                }

                // 渠道进账//

                if($recharge['channel_user_id']){
                    $user = M('admin')->where(['id'=>$recharge['channel_user_id']])->find();
                    $log = [
                        'record_type' => 1,
                        'user_id' => $recharge['channel_user_id'],
                        'water_id' => $recharge['id'],
                        'amount' => $recharge['channel_money'],
                        'total_amount' => $recharge['channel_money'] + $user['total_amount'],
                        'create_time' => time()
                    ];
                    M('amount_record')->add($log);
                    M()->query("UPDATE t_admin SET total_amount = total_amount + '{$recharge['channel_money']}', total_income_amount = total_income_amount+ '{$recharge['channel_money']}',
                      total_sales_amount = total_sales_amount+'{$recharge['package_amount']}', total_orders = total_orders+1  WHERE id='{$recharge['channel_user_id']}'");
                }

                // 魔座进账//

                if($recharge['device_user_id']){
                    $user = M('admin')->where(['id'=>$recharge['device_user_id']])->find();
                    $log = [
                        'record_type' => 1,
                        'user_id' => $recharge['device_user_id'],
                        'water_id' => $recharge['id'],
                        'amount' => $recharge['device_money'],
                        'total_amount' => $recharge['device_money'] + $user['total_amount'],
                        'create_time' => time()
                    ];
                    M('amount_record')->add($log);
                    M()->query("UPDATE t_admin SET total_amount = total_amount + '{$recharge['device_money']}', total_income_amount = total_income_amount+ '{$recharge['device_money']}',
                      total_sales_amount = total_sales_amount+'{$recharge['package_amount']}', total_orders = total_orders+1  WHERE id='{$recharge['device_user_id']}'");
                }

                // 推广进账//
                if($recharge['spread_user_id']) {
                    $user = M('admin')->where(['id'=>$recharge['spread_user_id']])->find();
                    $log = [
                        'record_type' => 1,
                        'user_id' => $recharge['spread_user_id'],
                        'water_id' => $recharge['id'],
                        'amount' => $recharge['spread_money'],
                        'total_amount' => $recharge['spread_money'] + $user['total_amount'],
                        'create_time' => time()
                    ];
                    M('amount_record')->add($log);
                    M()->query("UPDATE t_admin SET total_amount = total_amount + '{$recharge['spread_money']}', total_income_amount = total_income_amount+ '{$recharge['spread_money']}',
                      total_sales_amount = total_sales_amount+'{$recharge['package_amount']}', total_orders = total_orders+1  WHERE id='{$recharge['spread_user_id']}'");
                }
            }
            ///////////////////////记录流水
        }

    }


    /**
     * 用户添加M币
     * @param $record_num
     * @param $uid
     * @param $title
     * @return bool|mixed
     */
    public function consume_user_integral($recharge,$recordnum){

        $where = array();
        if($recharge['uid']){
            $where['uid'] = $recharge['uid'];
        }elseif($recharge['user_union_id']){
            $where['union_id'] = $recharge['user_union_id'];
        }else{
            return false;
        }

        if($recordnum > 0){
            $rechargeArr = array('10'=>'1200','20'=>'2400','50'=>'6000','100'=>'12000');
            $rechargeSource = array('10'=>'1000','20'=>'2000','50'=>'5000','100'=>'10000');
            $rechargeGive = array('10'=>'200','20'=>'400','50'=>'1000','100'=>'2000');
            $money = $rechargeArr[intval($recharge['money'])];
            $title = "用户充值".$rechargeSource[intval($recharge['money'])]."M币,平台赠送".$rechargeGive[intval($recharge['money'])]."M币";
            M('db_hd_v4.users_bank')->where($where)->setInc('total_integral', $money);



        }elseif($recordnum <= 0){
            $money = -intval($recharge['money']);
            $title = "现金+M币商品兑换消耗".$recharge['money'].'M币';
            M('db_hd_v4.users_bank')->where($where)->setDec('total_integral', abs($money));
        }else{
            return false;
        }

        $data = array(
            'openid' => $recharge['openid'],
            'user_open_id' => $recharge['user_open_id'],
            'user_union_id' => $recharge['user_union_id'],
            'title' => $title,
            'uid' => $recharge['uid'],
            'city_id' => $recharge['city_id'],
            'record_num' => $money,
            'create_time' => time()
        );

        Log::DEBUG('users_integral_record-data:'.json_encode($data));

        // 增加日志
        if($recharge['openid']){
            $table = get_hash_table('users_integral_record',$data['openid']);
            Log::DEBUG('users_integral_record-table:'.$table);
            $integral = M($table)->add($data);
            Log::DEBUG('users_integral_record-sql:'.M($table)->getLastSql());
            return $integral;
        }

        return false;

    }

    //查询订单
    public function Queryorder($transaction_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        Log::DEBUG("query:" . json_encode($result));
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {

            $result['addtime'] = time();
            $id = M('wexin_pay_log')->add($result);
            Log::DEBUG("wexin_pay_log-sql:" . M('wexin_pay_log')->getLastSql());
            $this->czRecord($result,$id);
            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        Log::DEBUG("call back:" . json_encode($data));
        $notfiyOutput = array();

        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }
        return true;
    }
}


?>
