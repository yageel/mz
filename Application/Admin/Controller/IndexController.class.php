<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        /**
         * modify by maofei for 1057优乐广播朗读比赛后台 at 2017-3-23
         */
        $uname=$this->admin['uname'];
        if($uname =="system"){
            $this->display("index1057");
        }elseif ($uname =="helens"){
            $this->display("indexHelens");
        }else{
            $this->display();
        }

//        $this->display();
    }

    public function index1(){
        $log = realpath(APP_PATH . "/logs/monitor_total.log");
        if(file_exists($log)){
            $data = file_get_contents($log);
            $data = str_replace('==','还剩【',$data);
            $this->assign('total_msg', $data);
        }
        // 最新提现
        $time = time() - 60*10;
        $cash_list = M('wechat_pay_record')->where("created_at > $time AND result_code='FAIL'")->select();
        $this->assign('cash_list', $cash_list);
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