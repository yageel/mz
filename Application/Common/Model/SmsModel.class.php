<?php
namespace Common\Model;
use Redis\MyRedis;


/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/6/6
 * Time: 16:15
 */
class SmsModel extends BaseModel
{
    protected $tableName = 'sms_log';

    /**
     * 短信测试
     * @param $data
     * @return bool|mixed
     */
    public function add_sms($data){
        $mobile = $data['mobile'];

        if(empty($mobile)) return false;
        $data['send_time'] = time();
        return $this->add($data);
    }

    /**
     * 校验手机号码30分钟内发送次数
     * @param $mobile
     * @param int $max_time
     */
    public function check_mobile($mobile, $max_num=3,$max_time=30){

        $data = $this->where(array('mobile'=>$mobile))->where("send_time > '".(time()-$max_time * 60)."'" )->count();

        if($data >= $max_num){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 校验手机号码30分钟内发送次数
     * @param $mobile
     * @param int $max_time
     */
    public function check_user($openid, $max_num=5,$max_time=30){
        if(!$openid){return true;}
        $data = $this->where(array('openid'=>$openid))->where("send_time > '".(time()-$max_time * 60)."'" )->count();
        if($data >= $max_num){
            return false;
        }else{
            return true;
        }
    }


}