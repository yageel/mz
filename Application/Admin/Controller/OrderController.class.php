<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Page;

class OrderController extends BaseController {

    public function index(){
        $tab = I('request.tab','', 'trim');

        $where = ['status'=>['lt', 4]];

        if($tab == '' OR $tab == 'operational'){
            $where['_string']="FIND_IN_SET(2,role_list)";
        }elseif($tab == 'channel'){
            $where['_string']="FIND_IN_SET(3,role_list)";
        }elseif($tab == 'device'){
            $where['_string']="FIND_IN_SET(4,role_list)";
        }elseif($tab == 'spread'){
            $where['_string']="FIND_IN_SET(5,role_list)";
        }

        // 运营筛选
        if($this->admin['role'] == 2){
            if($tab == '' OR $tab == 'operational'){
                $where['_string']="FIND_IN_SET(2,role_list)";
                $where['id'] = $this->admin['id'];
            }elseif($tab == 'channel'){
                $where['_string']="FIND_IN_SET(3,role_list)";
                $where['id'] = ['EXP', "IN(SELECT channel_user_id FROM t_devices WHERE operational_user_id='{$this->admin['id']}')"];
            }elseif($tab == 'device'){
                $where['_string']="FIND_IN_SET(4,role_list)";
                $where['id'] = ['EXP', "IN(SELECT user_id FROM t_devices WHERE operational_user_id='{$this->admin['id']}')"];
            }elseif($tab == 'spread'){
                $where['_string']="FIND_IN_SET(5,role_list)";
                $where['id'] = ['EXP', "IN(SELECT user_id FROM t_devices_spread WHERE device_id IN(SELECT id FROM t_devices WHERE operational_user_id='{$this->admin['id']}'))"];
            }

        // 渠道筛选
        }elseif($this->admin['role'] == 3){
            if($tab == 'channel'){
                $where['_string']="FIND_IN_SET(3,role_list)";
                $where['id'] = ['EXP', "IN(SELECT channel_user_id FROM t_devices WHERE channel_user_id='{$this->admin['id']}')"];
            }elseif($tab == 'device'){
                $where['_string']="FIND_IN_SET(4,role_list)";
                $where['id'] = ['EXP', "IN(SELECT user_id FROM t_devices WHERE channel_user_id='{$this->admin['id']}')"];
            }elseif($tab == 'spread'){
                $where['_string']="FIND_IN_SET(5,role_list)";
                $where['id'] = ['EXP', "IN(SELECT user_id FROM t_devices_spread WHERE device_id IN(SELECT id FROM t_devices WHERE channel_user_id='{$this->admin['id']}'))"];
            }
        // 魔座筛选
        }elseif($this->admin['role'] == 4){
            if($tab == 'device'){
                $where['_string']="FIND_IN_SET(4,role_list)";
                $where['id'] = ['EXP', "IN(SELECT user_id FROM t_devices WHERE user_id='{$this->admin['id']}')"];
            }elseif($tab == 'spread'){
                $where['_string']="FIND_IN_SET(5,role_list)";
                $where['id'] = ['EXP', "IN(SELECT user_id FROM t_devices_spread WHERE device_id IN(SEELCT id FROM t_devices WHERE user_id='{$this->admin['id']}'))"];
            }
        }




        $db = M('admin'); // 实例化User对象
        $count = $db->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $db->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();

        //
        foreach($list as $i=>$item){
            // $role_list = explode(',', $item['role_list']);

            if($tab == '' OR $tab == 'operational'){
                $list[$i]['total_device'] = D('Devices')->where(['operational_user_id'=>$item['id']])->count();
            }elseif($tab == 'channel'){
                $list[$i]['total_device'] = D('Devices')->where(['channel_user_id' => $item['id']])->count();
            }elseif($tab == 'device'){
                $list[$i]['total_device'] = D('Devices')->where(['device_user_id' => $item['id']])->count();
            }elseif($tab == 'spread'){
                $list[$i]['total_device'] = M('devices_spread')->where(['user_id' => $item['id']])->count();
            }


            // 运营人员
//            if(in_array(2, $role_list)){
//                // $list[$i]['total_channel'] = M()->query("SELECT COUNT(*) AS tp_count FROM (SELECT id FROM t_devices WHERE operational_user_id='{$user['id']}' GROUP BY channel_user_id)t")[0]['tp_count'];// D('Devices')->where(['operational_user_id'=>$user['id']])->group("shop_id")->count();
//                $list[$i]['total_device'] = D('Devices')->where(['operational_user_id'=>$item['id']])->count();
//            }elseif(in_array(3, $role_list)){
//                $list[$i]['total_device'] = D('Devices')->where(['channel_user_id' => $item['id']])->count();
//            }elseif(in_array(4, $role_list)){
//                $list[$i]['total_device'] = D('Devices')->where(['device_user_id' => $item['id']])->count();
//            }elseif(in_array(5, $role_list)){
//                $list[$i]['total_device'] = M('devices_spread')->where(['user_id' => $item['id']])->count();
//            }
        }

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
        $tab2 = I('request.tab2','','trim');
        $id = I('request.id',0,'intval');
        $user = D('Admin')->get_user_info($id);

        // 用户角色对应跳转标签
        $uesr['tab'] = $tab2;

        $this->assign('tab2', $tab2);
        if(empty($tab2)){
            $this->error("请选择要查看角色数据~");
        }

        // 订单流水
        if($tab == ''){
            $where = [];
            $where['status'] = 1;
            // 运营人员
            if($tab2 == 'operational'){
                $user['role'] = 2;
                $where['operational_user_id'] = $user['id'];
            // 渠道人员
            }elseif($tab2 == 'channel'){
                $user['role'] = 3;
                $where['channel_user_id'] = $user['id'];
            // 魔座人员
            }elseif($tab2 == 'device'){
                $user['role'] = 4;
                $where['device_user_id'] = $user['id'];
            // 推广人员
            }elseif($tab2 == 'spread'){
                $user['role'] = 5;
                $where['spread_user_id'] = $user['id'];
            }else{
                $user['role'] = 1;
                $where['user_id'] = 0;
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
            if($tab2 == 'operational' || $tab2 == 'channel' || $tab2 == 'device'){
                $where = [];
                $where['status'] = 1;
                // 运营人员
                if($tab2 == 'operational'){
                    $where['operational_user_id'] = $user['id'];
                    // 渠道人员
                }elseif($tab2 == 'channel'){
                    $where['channel_user_id'] = $user['id'];
                    // 魔座人员
                }elseif($tab2 == 'device'){
                    $where['user_id'] = $user['id'];
                    // 推广人员
                }
                $db = M('devices');
                $count = $db->where($where)->count();// 查询满足要求的总记录数
                $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

                $show = $Page->show();// 分页显示输出
                // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
                $list = $db->where($where)->order("id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
            }elseif($tab2 == 'spread'){
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