<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends BaseController {

    public function _initialize()
    {
        parent::_initialize();
        // 没登陆自动登录
        if(empty($this->openid) && ACTION_NAME != 'login' && ACTION_NAME != 'agreement'){
            $this->redirect(U('/user/login'));
        }
    }

    public function index(){

        $this->display();
    }

    /**
     * 提现操作
     */
    public function cash(){

    }

    public function login(){

        $this->display();
    }

    public function  agreement(){
        $this->display();
    }
}