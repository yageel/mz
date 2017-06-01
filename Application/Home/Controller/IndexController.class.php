<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){

        $package_list = M('package')->where(['status'=>1])->order("weight DESC, id ASC")->select();
        $this->assign('package_list', $package_list);
        $this->display();
    }

    public function spread(){
        $package_list = M('package')->where(['status'=>1])->order("weight DESC, id ASC")->select();
        $this->assign('package_list', $package_list);
        $this->display();
    }

    /**
     * 用户退出登录
     */
    public function logout(){
        session('is_login', 0);
        session('login_user_id', 0);
        $this->success('安全退出',U('/login/index'));
    }
}