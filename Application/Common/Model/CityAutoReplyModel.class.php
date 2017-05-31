<?php
namespace Common\Model;
use Redis\MyRedis;

/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/6/6
 * Time: 16:15
 */
class CityAutoReplyModel extends BaseModel
{
    protected $tableName = 'city_auto_reply';

    /**
     * 获取公众号响起
     * @param int $city_id
     * @return bool|mixed
     */
    public function get_reply_msg($city_id, $msg_type, $content){
        //$key = 't_city_reply_'.$city_id.$msg_type.md5($content);

        //$data = MyRedis::getProInstance()->new_get($key);
        //if(!$data)
        {
            $data = $this->where(array('msg_type'=>$msg_type, 'msg_content'=>$content, 'city_id'=>$city_id,'status'=>1))->find();
            if($data){
                // MyRedis::getProInstance()->new_set($key, $data, 1800);
            }
        }
        return $data;
    }
}