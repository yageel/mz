<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function index(){
        $this->display();
    }

    public function post(){
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
        redirect(U('/index/index'));
    }
}