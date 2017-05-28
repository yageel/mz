<?php
namespace Admin\Controller;
use Think\Controller;
use Org\Util\Strings;
class DevicesController extends BaseController {
    public function index(){

        $this->display();
    }

    public function edit(){
        $id = I('request.id',0,'intval');
        // 自动回跳列表页
        if(strpos($_SERVER['HTTP_REFERER'],'/devices/index') !== false){
            $_SESSION['jump_url'] = $_SERVER['HTTP_REFERER'];
        }
        if(IS_POST){
            $data = $_POST;

            if( $id ){

                $res = M('devices')->where(['id'=>$id])->save($data);
            }else{
                // 添加必要字段
                $data['create_time'] = time();
                $data['qrcode'] = uniqid() . Strings::randString(4) . Strings::randString(4);
                $res = M('devices')->add($data);
            }

            if($res){
                $this->success("操作成功", $_SESSION['jump_url']?$_SESSION['jump_url']:U('/devices/index'));
            }else{
                $this->error("操作失败");
            }
        }

        if($id){
            $detail = M('devices')->where(['id'=>$id])->find();
            $this->assign('detail', $detail);
        }

        // 对应城市
        $area_list = D('Area')->get_area_map();
        $this->assign('area_list', $area_list);

        // 对应渠道
        $channel_list = D('Channel')->where([ 'status' => 1 ])->select();
        foreach($channel_list as $i=>$channel){
            $channel_list[$i]['user'] = D('Admin')->get_user_info($channel['user_id']);
        }
        $this->assign('channel_list', $channel_list);

        // 运营人员
        $operational_list = D('Admin')->get_user_list_role(2);
        $this->assign('operational_list', $operational_list);

        // 拥有者
        $user_list = D('Admin')->get_user_list_role(4);
        $this->assign('user_list', $user_list);

        $this->display();
    }
}