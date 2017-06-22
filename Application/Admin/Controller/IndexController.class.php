<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        $this->display();
    }

    public function index1(){

        $this->display();
    }

    /**
     * 用户退出登录
     */
    public function logout(){
        session('is_login', 0);
        session('login_user_id', 0);
        session('role', 0);
        session('login_uesr',[]);
        $this->success('安全退出',U('/login/index'));
    }

    /**
     * 切换角色
     */
    public function checkrole(){
        $this->assign('role_list', explode(',',$this->admin['role_list']));
        $this->display();
    }
}