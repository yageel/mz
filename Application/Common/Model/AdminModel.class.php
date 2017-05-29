<?php
namespace Admin\Model;
use Redis\MyRedis;


/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/6/6
 * Time: 16:15
 */
class AdminModel extends BaseModel
{
    protected $tableName = 'admin';

    /**
     * 获取角色列表
     */
    public function get_user_list_role($role_id = 0){
        return $this->where(['role_id'=>$role_id])->select();
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function get_user_info($user_id = 0){
        return $this->where(['id'=>$user_id])->find();
    }
}