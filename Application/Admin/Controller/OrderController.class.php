<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Page;

class OrderController extends BaseController {

    public function index(){
        $tab = I('request.tab','', 'trim');

        $where = ['status'=>['lt', 4]];
        if($tab == '' OR $tab == 'operational'){
            $where['role'] = 2;
        }elseif($tab == 'channel'){
            $where['role'] = 3;
        }elseif($tab == 'device'){
            $where['role'] = 4;
        }elseif($tab == 'spread'){
            $where['role'] = 5;
        }


        $db = M('admin'); // 实例化User对象
        $count = $db->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $db->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('tab', $tab);
        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 用户订单详情
     */
    public function user(){
        $this->display();
    }

    /**
     * 订单详情
     */
    public function detail(){

        $this->display();
    }

}