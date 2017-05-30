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
        $tab = I('request.tab','','trim');
        $id = I('request.id',0,'intval');
        $user = D('Admin')->get_user_info($id);

        // 订单流水
        if($tab == ''){
            $where = [];
            $where['status'] = 1;
            // 运营人员
            if($user['role'] == 2){
                $where['operational_user_id'] = $user['id'];
            // 渠道人员
            }elseif($user['role'] == 3){
                $where['channel_user_id'] = $user['id'];
            // 魔座人员
            }elseif($user['role'] == 4){
                $where['device_user_id'] = $user['id'];
            // 推广人员
            }elseif($user['role'] == 5){
                $where['spread_user_id'] = $user['id'];
            }
            $db = M('order');
            $count = $db->where($where)->count();// 查询满足要求的总记录数
            $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

            $show = $Page->show();// 分页显示输出
            // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
            $list = $db->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();

        // 提现流水
        }elseif($tab == 'balance'){
            $where = [];
            $where['status'] = ['in', [0,1]];
            $where['user_id'] = $user['id'];

            $db = M('cash_record');
            $count = $db->where($where)->count();// 查询满足要求的总记录数
            $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

            $show = $Page->show();// 分页显示输出
            // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
            $list = $db->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();

        // 旗下设备
        }elseif($tab == 'device'){

            if(in_array($user['role'],[2,3,4])){
                $where = [];
                $where['status'] = 1;
                // 运营人员
                if($user['role'] == 2){
                    $where['operational_user_id'] = $user['id'];
                    // 渠道人员
                }elseif($user['role'] == 3){
                    $where['channel_user_id'] = $user['id'];
                    // 魔座人员
                }elseif($user['role'] == 4){
                    $where['user_id'] = $user['id'];
                    // 推广人员
                }
                $db = M('order');
                $count = $db->where($where)->count();// 查询满足要求的总记录数
                $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

                $show = $Page->show();// 分页显示输出
                // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
                $list = $db->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
            }elseif($user['role'] == 5){
                // 推广
                $where = [];
                $where['user_id'] = $user['id'];
                $db = M('devices_spread');
                $count = $db->where($where)->count();// 查询满足要求的总记录数
                $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

                $show = $Page->show();// 分页显示输出
                // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
                $device_list = $db->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
                //
                $list = [];
                foreach($device_list as $i=>$item){
                    $list[] = M('devices')->where(['id'=>$item['device_id']])->find();
                }
            }

        }

        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->assign('user', $user);
        $this->assign('tab',$tab);
        $this->display();
    }

    /**
     * 订单详情
     */
    public function detail(){

        $this->display();
    }

}