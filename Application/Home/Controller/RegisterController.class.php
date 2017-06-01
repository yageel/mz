<?php
namespace Home\Controller;
use Think\Controller;
class RegisterController extends BaseController {


    public function index(){
        $this->display();
    }

    public function  agreement(){
        $this->display();
    }


    /**
     * 获取短信接口
     */
    public function vercode_api(){
        $json = $this->ajax_json();
        do{
            $mobile = I('request.mobile','','strval');

            if(!$mobile){
                $json['msg'] = '请正确数据手机号码';
                break;
            }

            if(! preg_match("/^1[34578]\d{9}$/", $mobile)){
                //手机格式不通过
                $json['msg'] = '请正确输入手机号码';
                break;
            }

            $member = D('Users')->where(['mobile'=>$mobile])->find();
            if($member){
                $json['msg'] = '该手机已经被注册了~';
                break;
            }

            $member = D('Admin')->where(['mobile'=>$mobile])->find();
            if(!$member){
                $json['msg'] = '该手机号不能注册，请联系服务确认~';
                break;
            }

            $code = create_code();
            $bool = send_msg($mobile, "【魔座驾到】您的验证码是".$code, $code, $this->openid);
            if(!$bool){
                $json['msg'] = '短信发送失败，请不要频繁提交';
                break;
            }

            $json['state'] = 1;
            $json['msg'] = null;
            $json['data'] = null;

        }while(false);
        echo json_encode($json);die();
    }

    /**
     * 优化用户注册，注册成功不跳转页面，返回json
     */
    public function post(){

        $mobile = I('post.mobile','','strval');
        $code = I('post.vercode','','strval');
        $json = $this->ajax_json();
        do{
            if(! preg_match("/^1[34578]\d{9}$/", $mobile)){
                //手机格式不通过
                $json['msg'] = '请正确输入手机号码';
                break;
            }

            if(!is_numeric($code) || strlen($code) != 6){
                // 短信验证不通过
                $json['msg'] = '验证码错误';
                break;
            }

            if(!check_code($code, $mobile)){
                // 短信验证不通过
                $json['msg'] = '验证码错误';
                break;
            }

            // $this
            $admin = D('Admin')->where(['mobile'=>$mobile])->find();
            if(!$admin){
                $json['msg'] = '该手机号不能注册，请联系服务确认~';
                break;
            }

            $user = D('Users')->where(['mobile'=>$mobile])->find();
            if($user) {
                $json['msg'] = '该手机号已经注册了~';
                break;
            }

            $user = D('Users')->where(['openid'=>$this->openid])->find();
            if(!$user)
            {
                $json['msg'] = '用户账户异常，请联系服务确认~';
                break;
            }
            D('Users')->where(['openid'=>$this->openid])->save(['mobile'=>$mobile, 'bind_user_id'=>$admin['id']]);
            D('Admin')->where(['id'=>$admin['id']])->save(['city_id'=>$this->type, 'openid'=>$this->openid]);
            $json['state'] = 5;
            $json['msg'] = "注册成功~";
            $json['data'] = tsurl('/user/index');
        }while(false);
        $this->ajaxReturn($json);
    }
}