<?php
namespace Common\Model;
use Redis\MyRedis;


/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/6/6
 * Time: 16:15
 */
class UsersModel extends BaseModel
{
    protected $tableName = 'users';

    public function add_user($data){
        $openid = $data['openid'];
        if(empty($openid)) return false;
        $data['create_time'] = time();
        return $this->add($data);
    }

    /**
     * 获取公众号用户
     * @param int $city_id
     * @return bool|mixed
     */
    public function get_user($openid, $is_cache=true){
        if(empty($openid)) return false;
        $key = 't_users_'.$openid;
        $data = MyRedis::getProInstance()->new_get($key);

        if(!$data || !$is_cache){
            $data = $this->where(array('openid'=>$openid))->find();
            if($data){
                MyRedis::getProInstance()->new_set($key, $data);
            }
        }

        return $data;
    }

    /**
     * 更新用户信息
     * @param $openid
     * @param $data
     */
    public function update_user($openid, $data){
        if(empty($openid)) return false;
        $key = 't_users_'.$openid;
        $this->where(array('openid'=>$openid))->save($data);
        MyRedis::getProInstance()->delete($key);

        return true;
    }

}