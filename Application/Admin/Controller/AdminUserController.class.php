<?php

namespace Admin\Controller;

use Helpers\Presenter;
use Think\Controller;
use Think\Page;

class AdminUserController extends BaseController
{
    /**
     * 微信openid信息
     */
    public function index()
    {
        $city_list = M('city')->where(array('status'=>1))->select();
        $this->assign('city_list', $city_list);

        $city_id = I('request.city_id',0,'intval');
        $wx_name = I('request.wx_name','','trim');
        $openid = I('request.openid','','trim');
        $where = [];
        if($city_id){
            $where['city_id'] = $city_id;
        }
        if($openid){
            $where['openid'] = $openid;
        }

        if($wx_name){
            $where['wx_name'] = array('like', '%'.$wx_name.'%');
        }

        $this->assign('city_id', $city_id);
        $this->assign('wx_name', $wx_name);
        $this->assign('openid', $openid);

        $users = M('Admin'); // 实例化User对象
        $count = $users->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $users->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $i=>$user){

        }

        $this->assign('user_subscribe_map', Presenter::$user_subscribe_map);
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

}