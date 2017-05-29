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
     *
     */
    public function edit(){
        $id = I('request.id',0,'intval');
        // 自动回跳列表页
        if(strpos($_SERVER['HTTP_REFERER'],'/rebate/index') !== false){
            $_SESSION['jump_url'] = $_SERVER['HTTP_REFERER'];
        }

        if(IS_POST){
            $data = $_POST;
            $data['operational_rebate'] = intval($data['operational_rebate']);
            $data['channel_rebate'] = intval($data['channel_rebate']);
            $data['device_rebate'] = intval($data['device_rebate']);
            $data['spread_rebate'] = intval($data['spread_rebate']);
            if($data['operational_rebate'] < 0 || $data['operational_rebate'] > 100){
                return $this->error("分成比例在0-100之间~");
            }

            if($data['channel_rebate'] < 0 || $data['operational_rebate'] > 100){
                return $this->error("分成比例在0-100之间~");
            }

            if($data['device_rebate'] < 0 || $data['device_rebate'] > 100){
                return $this->error("分成比例在0-100之间~");
            }

            if($data['spread_rebate'] < 0 || $data['spread_rebate'] > 100){
                return $this->error("分成比例在0-100之间~");
            }

            $data['platform_rebate'] = 100 - $data['operational_rebate'] - $data['channel_rebate'] - $data['device_rebate'] - $data['spread_rebate'];
            if($data['platform_rebate'] < 0){
                return $this->error("总分成不能大于100");
            }
            $data['update_time'] = time();
            if($id){
                $res = M('rebate')->where(['id'=>$id])->save($data);
                // 处理订阅用户

                if($data['subscribe']){
                    // 处理现有的
                    M('admin')->where(['id'=>['in',$data['subscribe']]])->save(['rebate_id'=>$id]);
                    M('admin')->where(['rebate_id'=>$id, 'id'=>['NOT IN', $data['subscribe']]])->save(['rebate_id'=>0]);
                }
            }else{
                $data['create_time'] = time();
                $data['rebate_type'] = 1;
                $res = M('rebate')->add($data);
                if($res && $data['subscribe']){
                    M('admin')->where(['id'=>['in',$data['subscribe']]])->save(['rebate_id'=>$res]);
                }
            }

            if($res){
                return $this->success("操作成功", $_SESSION['jump_url']?$_SESSION['jump_url']:U('/rebate/index'));
            }else{
                return $this->error("操作失败~");
            }
        }
        if($id){
            $detail = M('rebate')->where(['id'=>$id])->find();
            $user_list = M('admin')->where(['rebate_id'=>$detail['id']])->select();
            $subscribe = [];
            foreach($user_list as $user){
                $subscribe[] = $user['id'];
            }
            $detail['subscribe_list'] = $subscribe;
            $this->assign('detail', $detail);
        }

        $user_list = M('admin')->where(['status'=>['lt', 4], 'role'=>['in',[2,3,4,5]]])->order("role ASC")->select();
        $this->assign('user_list', $user_list);
        $this->display();
    }
}