<?php

namespace Admin\Controller;

use Helpers\Presenter;
use Think\Controller;
use Think\Page;

class UsersController extends BaseController
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

        $users = M('users'); // 实例化User对象
        $count = $users->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $users->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $i=>$user){
            $city = D('City')->get_city($user['cityid']);
            $list[$i]['city_name'] = $city['city_name'];
        }

        $this->assign('user_subscribe_map', Presenter::$user_subscribe_map);
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    /**
     * 微信用户信息详情
     */
    public function userview()
    {
        $id = I('request.id');
        $model = M('users_all')->find($id);
        $this->assign('user_wx_sex_map', Presenter::$user_wx_sex_map);
        $this->assign('user_subscribe_map', Presenter::$user_subscribe_map);
        $this->assign('model', $model);
        $this->display();
    }

    /**
     * 微信union信息
     *
     */
    public function union()
    {
        $city = M('users_union'); // 实例化User对象
        $count = $city->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $city->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    /**
     * 微信开放平台信息详情
     */
    public function unionview()
    {
        $id = I('request.id');
        $model = M('users_union')->find($id);
        $this->assign('model', $model);
        $this->display();
    }

    /**
     * 绑定用户
     */
    public function member()
    {
        $city = D('users_member'); // 实例化User对象
        $count = $city->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $city->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('member_status_map', Presenter::$member_status_map);
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    /**
     * 注册用户详情
     */
    public function memberview()
    {
        $id = I('request.id');
        $model = M('users_member')->find($id);
        $this->assign('member_status_map', Presenter::$member_status_map);
        $this->assign('model', $model);
        $this->display();
    }

    /**
     * 地址管理
     */
    public function address()
    {
        $model = D('users_address');
        $count = $model->count();
        $Page = new Page($count, 20);

        $show = $Page->show();
        $list = $model->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('address_default_map', Presenter::$address_default_map);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * 地址详情
     */
    public function addressview()
    {
        $id = I('request.id');
        $model = M('users_address')->find($id);
        $this->assign('address_default_map', Presenter::$address_default_map);
        $this->assign('model', $model);
        $this->display();
    }

    /**
     * 用户银行账户
     */
    public function bank(){
        $model = D('UsersBank');
        $count = $model->count();
        $Page = new Page($count, 20);

        $show = $Page->show();
        $list = $model->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach($list as $i=>$item){
            $union = D('UsersUnion')->get_user_union_by_id($item['union_id']);

            if($union){
                $list[$i]['user'] = D('Users')->get_user($union['openid']);
            }
        }

        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * M币流水
     */
    public function integral_record(){
        $model = M('users_integral_record_all');
        $count = $model->count();
        $Page = new Page($count, 20);

        $show = $Page->show();
        $list = $model->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach($list as $i=>$item){
            $list[$i]['user'] = D('Users')->get_user($item['openid']);
            $city = D('City')->get_city($item['city_id']);
            $list[$i]['city_name'] = $city['city_name'];
        }

        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * 金额流水
     */
    public function money_record(){
        $city_id = I('city_id',0,'intval');
        $kw = I('kw','','trim');
        $date1 = I('date1','','trim');
        $date2 = I('date2','','trim');

        $model = D('wechat_pay_record');
        $where = "1";
        if($city_id){
            // $where['city_id'] = $city_id;
            $where .= " AND city_id='{$city_id}'";
        }

        if($kw){
            //$where['openid'] = $kw;
            $where .= " AND openid='{$kw}'";
        }

        if($date1){
            //$where['created_at'] = [ 'egt', strtotime($date1)];
            $where .= " AND create_time>='".strtotime($date1)."'";
        }

        if($date2){
            //$where['created_at'] = [ 'elt', strtotime($date2,"+1 day")];
            $where .= " AND create_time<='".strtotime($date2.".23:59:59 ")."'";
        }


        $model = M('users_money_record');
        $count = $model->where($where)->count();
        $Page = new Page($count, 20);

        $show = $Page->show();
        $list = $model->where($where)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach($list as $i=>$item){
            $list[$i]['user'] = D('Users')->get_user($item['openid']);
            $city = D('City')->get_city($item['city_id']);
            $list[$i]['city_name'] = $city['city_name'];
        }

        $wechat_pay_type_map = Presenter::$wechat_pay_type_map;
        $city_map = cityMap();

        $this->assign('date1', $date1);
        $this->assign('date2', $date2);
        $this->assign('city_id', $city_id);
        $this->assign('kw', $kw);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('wechat_pay_type_map', $wechat_pay_type_map);
        $this->assign('city_map', $city_map);

        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * 提现流水
     */
    public function case_record()
    {
        $city_id = I('city_id',0,'intval');
        $kw = I('kw','','trim');
        $date1 = I('date1','','trim');
        $date2 = I('date2','','trim');
        $status = I('status',0,'intval');

        $where = "1";
        if($status == 1){
            $where .= " AND status = 1";
        }
        if($status == 2){
            $where .= " AND status = 2";
        }
        if($status == 3){
            $where .= " AND status = 0";
        }

        if($city_id){
            // $where['city_id'] = $city_id;
            $where .= " AND city_id='{$city_id}'";
        }

        if($kw){
            //$where['openid'] = $kw;
            $where .= " AND openid='{$kw}'";
        }

        if($date1){
            //$where['created_at'] = [ 'egt', strtotime($date1)];
            $where .= " AND created_at>='".strtotime($date1)."'";
        }

        if($date2){
            //$where['created_at'] = [ 'elt', strtotime($date2,"+1 day")];
            $where .= " AND created_at<='".strtotime($date2.".23:59:59 ")."'";
        }


        $model = D('users_cash_record');
        $count = $model->where($where)->count();
        $Page = new Page($count, 20);

        $show = $Page->show();
        $list = $model->where($where)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $city_map = cityMap();

        $wechat_pay_type_map = Presenter::$wechat_pay_type_map;


        $this->assign('status', $status);
        $this->assign('date1', $date1);
        $this->assign('date2', $date2);
        $this->assign('city_id', $city_id);
        $this->assign('kw', $kw);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('wechat_pay_type_map', $wechat_pay_type_map);
        $this->assign('city_map', $city_map);

        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('city_map', $city_map);
        $this->display();
    }

    /**
     * 用户兑换处理
     */
    public function integral_exchange(){
        $exchange_vo = M('exchange_logs');
        $count = $exchange_vo->count();

        $Page = new Page($count, 20);

        $show = $Page->show();
        $list = $exchange_vo->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->order('id DESC')->select();
        $this->assign('list', $list);
        $this->assign('page', $show);

        $city_map = cityMap();
        $this->assign('city_map', $city_map);
        $this->display();

    }

    /**
     * 关闭提现
     */
    public function close_cash(){
        $id = I('id',0,'intval');

        $item = M('users_cash_record')->where(['id'=>$id])->find();
        if($item && empty($item['status'])){
            M('users_cash_record')->where(['id'=>$item['id']])->save(['status'=>2]);
        }

        echo "<script type='text/javascript'>window.location.href = document.referrer;</script>";
    }
}