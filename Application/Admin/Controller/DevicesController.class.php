<?php
namespace Admin\Controller;
use Think\Controller;
class DevicesController extends BaseController {
    public function index(){

        $this->display();
    }

    public function edit(){
        $uname = I('request.uname','','htmlspecialchars');
        $pass = I('request.pwd','','htmlspecialchars');

        if(empty($uname) || empty($pass)){
            return $this->error('请先提交登陆信息',U('/login/index'));
        }

        $user = M('admin')->where(array('uname'=>$uname))->find();
        if(!$user){
            return $this->error('登陆失败',U('/login/index'));
        }

        $password = encrypt_password($pass, $user['salt']);

        if($password != $user['pwd']){
            return $this->error('登陆失败',U('/login/index'));
        }

        session('is_login', 1);
        session('login_user_id',$user['id']);
        session('login_user', $user);
        redirect(U('/index/index'));
    }
}