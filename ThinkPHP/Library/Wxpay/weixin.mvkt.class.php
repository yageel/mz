<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once dirname(__FILE__) . "/lib/WxPay.Api.php";
require_once dirname(__FILE__) . "/example/WxPay.JsApiPay.php";
require_once dirname(__FILE__) . '/lib/WxPay.Notify.php';
require_once dirname(__FILE__) . '/example/log.php';
//初始化日志
//$logHandler= new CLogFileHandler(dirname(__FILE__) . "/logs/".date('Y-m-d').'.log');
$logHandler= new CLogFileHandler("/data/log/".'mvkt'.date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);


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

    //②、统一下单
    $order_sn = $data['order_sn']?$data['order_sn']:'CZ'.WxPayConfig::$MCHID.date("YmdHis");
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
     * 添加参与者信息
     */
    public function addUser($result,$pay_log_id){
        $order_sn = $result['out_trade_no'];
        $recharge = M('helens_pay')->where("partner_trade_no='{$order_sn}'")->find();

        if($recharge['status'] < 1){
            $data = array(
                'pay_id' => $pay_log_id,
                'status' => 2,
                'pay_time' => time(),
                'payment_no'=>$result['transaction_id']

            );
            M()->startTrans();
            //更新支付记录
            $res1 = M('helens_pay')->where("partner_trade_no='{$order_sn}'")->save($data);
            Log::DEBUG("回调更新helens_pay结果:".$res1."|sql:" . M('helens_pay')->getLastSql());
            //添加参与者
            $theArray['openid']=$recharge['openid'];
            $theArray['wx_name']=$recharge['wx_name'];
            $theArray['active_id']=$recharge['active_id'];
            $theArray['headimg']=$recharge['headimg'];
            $theArray['sex']=$recharge['sex'];
            $theArray['create_time']=time();
            $theArray['money']=$recharge['money'];

            $res2 = M('helens_join')->add($theArray);

            //开台完成更新状态
            $active=M("active_helens")->where("id='{$recharge['active_id']}'")->find();
            $joinNum=M("helens_join")->where("active_id='{$recharge['active_id']}'")->count();
            Log::DEBUG("活动:".$recharge['active_id']."已参加人数:".$joinNum);
            if($joinNum ==$active['total_num']){
                $clearTime=time()+600;
                $the_res=M("active_helens")->where("id='{$recharge['active_id']}'")->save(array("status"=>2,'clear_time'=>$clearTime));
                if(!$the_res){
                    Log::DEBUG("开台完成更新状态status=2失败:" . M('active_helens')->getLastSql());
                }
            }

            if ($res1 && $res2) {
                $res = true;
                M()->commit();
            }else{
                $res = false;
                Log::DEBUG("支付回调更新支付记录失败:" . M('helens_pay')->getLastSql());
                Log::DEBUG("支付回调添加参与者失败:" . M('helens_join')->getLastSql());
                M()->rollback();
            }
            Log::DEBUG('helensRecord-result-'.$res);
        }

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
            $this->addUser($result,$id);
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
