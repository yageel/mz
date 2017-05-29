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

        $tab = I('request.tab','','trim');
        $kw = I('request.kw','','trim');
        $where = [];

        if($kw){
            $where['contact_name'] = array('like', '%'.$kw.'%');
        }

        if($tab){
            if($tab == 'channel'){
                $where['role'] = 3;
            }elseif($tab == 'device'){
                $where['role'] = 4;
            }elseif($tab == 'spread'){
                $where['role'] = 5;
            }
        }else{
            $where['role'] = "2";
        }

        $this->assign('tab', $tab);
        $this->assign('kw', $kw);

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

    /**
     * 新建用户
     */
    public function edit(){
        $tab = I('reqeust.tab','','trim');
        $id = I('request.id',0,'intval');
        if(IS_POST){

            $res = '';
            if($res){
                return $this->success("操作成功",U('/admin_user/index',['tab'=>$tab]));
            }else{
                return $this->error("操作失败~");
            }
        }

        if($id){
            $detail = D('Admin')->get_user_info($id);
            $this->assign('detail', $detail);
        }
        $this->assign('tab', $tab);
        if($tab == 'operational'){
            $this->display('AdminUser/operational.html');
        }elseif($tab == 'channel'){
            $this->display('AdminUser/channel.html');
        }elseif($tab == 'device'){
            $this->display('AdminUser/device.html');
        }elseif($tab == 'speard'){
            $this->display('AdminUser/speard.html');
        }else{
            $this->display();
        }
    }

    /**
     * 导入用户
     */
    public function import(){
        $tab = I('request.tab','','trim');
        if(IS_POST){

        }

        $this->assign('tab', $tab);
        $this->display();
    }

}