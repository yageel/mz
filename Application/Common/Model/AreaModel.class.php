<?php
namespace Common\Model;
use Redis\MyRedis;


/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/6/6
 * Time: 16:15
 */
class AreaModel extends BaseModel
{
    protected $tableName = 'area';

    /**
     * 获取公众号响起
     * @param int $city_id
     * @return bool|mixed
     */
    public function get_area($city_id=0){
        $key = 't_area_'.$city_id;
        $data = MyRedis::getProInstance()->new_get($key);
        if(!$data){
            $data = $this->where(array('id'=>$city_id))->find();
            if($data){
                MyRedis::getProInstance()->new_set($key, $data);
            }
        }

        return $data;
    }

    /**
     *
     */
    public function get_area_list(){
       return $this->order('id ASC')->select();
    }

    /**
     * 获取城市列表
     * @return mixed
     */
    public function get_area_map(){
        $list = $this->where(['parent_id'=>0, "status"=>1])->select();
        foreach($list as $i=>$item){
            $list[$i]['city_list'] = $this->where(['parent_id'=>$item['id'], "status"=>1])->select();
        }

        return $list;
    }
}