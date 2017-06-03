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
        $type = intval($_REQUEST['type']);


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

    public function record(){
        $result['out_trade_no'] = '2017060318562361819916458394';
        $result['transaction_id'] = '2017060318562361819916458394';
        $pay_log_id = 1;
        $order_sn = $result['out_trade_no'];
        $recharge = M('order')->where(['order_sn'=>$order_sn])->find();

        if($recharge && $recharge['status'] < 1){
            $data = array(
                'payment_log_id' => $pay_log_id,
                'pay_time' => time(),
                'payment_no'=>$result['transaction_id'],
                'status' => 1
            );
            $res1 = M('order')->where(['order_sn'=>$order_sn])->save($data);
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
}

?>