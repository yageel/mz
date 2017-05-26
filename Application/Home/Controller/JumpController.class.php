<?php

namespace Admin\Controller;
use Think\Controller;
use Think\Page;
use Redis\MyRedis;

class JumpController extends Controller
{
    /**
     * 微信openid信息
     */
    public function index()
    {
        $count = M('qrcode')->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        $list = M('qrcode')->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $i=>$item){
            $list[$i]['count'] = intval(MyRedis::getGameInstance()->hGet('qrcode:url:count', "id:{$item['id']}"));
            $list[$i]['member'] = intval(MyRedis::getGameInstance()->hGet('qrcode:url:member', "id:{$item['id']}"));

        }
        $this->assign('page', $show);
        $this->assign('list', $list);

        $this->display();
    }

    /**
     * 二维码配置
     */
    public function edit(){

        $id = I('request.id', 0,'intval');
        $qrcodel = [];
        if($id){
            $qrcodel = M('qrcode')->where(['id'=>$id])->find();
            if(!$qrcodel){
                $this->error('没找到要编辑的二维码', U('/jump/index'));
            }
        }


        if(IS_POST){
            $id = I('post.id', 0,'intval');
            $data = $_POST;
            $qrcode_id = intval($data['qrcode_id']);
            if($id){
                $data['create_time'] = time();
                unset($data['id']);
                $res = M('qrcode')->where(['id'=>$id])->save($data);
                $data = M('qrcode')->where(['id'=>$id])->find();

                 MyRedis::getProInstance()->new_set("qrcode:{$id}", $data);
            }else{
                $data['url'] = uniqid();
                $data['create_time'] = time();
                $res = M('qrcode')->add($data);
                $data = M('qrcode')->where(['id'=>$res])->find();
                MyRedis::getProInstance()->new_set("qrcode:{$res}", $data);

                if(!file_exists(APP_PATH."/../upload/qrcode/".$data['url'] .".png")){
                    if(!file_exists(APP_PATH."/../upload/qrcode/")){
                        mkdir(APP_PATH."/../upload/qrcode/",0755, true);
                    }
                    include APP_PATH."/../file/phpqrcode/phpqrcode.php";
                    $value = "http://hd.millionmake.com/jump/{$data['url']}.html"; //二维码内容
                    $errorCorrectionLevel = 'L';//容错级别
                    $matrixPointSize = 12;//生成图片大小
                    //生成二维码图片
                    \QRcode::png($value, APP_PATH."/../upload/qrcode/".$data['url'] .".png", $errorCorrectionLevel, $matrixPointSize, 2);
                }
            }

            return $this->success('操作成功', U('/jump/index'));
        }
        $this->assign('qrcode', $qrcodel);
        $this->display();
    }

    /**
     * 删除二维码
     */
    public function del(){
        $id = I('request.id',0,'intval');
        if($id){
            M('qrcode')->where(['id'=>$id])->delete();
            M('qrcode_url')->where(['qrcode_id'=>$id])->delete();
            echo "<script type='text/javascript'>window.location.href = document.referrer;//返回上一页并刷新 ;</script>";
            return false;
        }
        return $this->error('请选择要删除二维码？');
    }

    /**
     * 删除二维码
     */
    public function url_del(){
        $id = I('request.id',0,'intval');
        if($id){
            M('qrcode_url')->where(['id'=>$id])->delete();
            echo "<script type='text/javascript'>window.location.href = document.referrer;//返回上一页并刷新 </script>";
            return false;
        }
        return $this->error('请选择要删除链接？');
    }

    /**
     * 链接列表
     */
    public function url(){
        $qrcode_id = I('request.id',0,'intval');
        $where = [];
        if($qrcode_id){
            $where[] = ['qrcode_id'=>$qrcode_id];
        }

        if(empty($qrcode_id)){
            $this->error('选择要操作的二维码', U('/jump/index'));
        }

        $qrcode = M('qrcode')->where(['id'=>$qrcode_id])->find();
        $this->assign('qrcode', $qrcode);
        $count = M('qrcode_url')->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        $list = M('qrcode_url')->where($where)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->assign('id', $qrcode_id);
        $this->display();
    }

    /**
     * 链接配置
     */
    public function url_edit(){
        $qrcode_id = I('request.qrcode_id',0,'intval');
        $id = I('request.id', 0,'intval');
        if($id){
            $qrcode_url = M('qrcode_url')->where(['id'=>$id])->find();
            if(!$qrcode_url){
                $this->error('没找到要编辑链接', U('/jump/index'));
            }
            $qrcode_id = $qrcode_url['qrcode_id'];
        }
        if(empty($qrcode_id)){
            $this->error('请选择操作二维码', U('/jump/index'));
        }

        if(IS_POST){
            $id = I('post.id', 0,'intval');
            $data = $_POST;
            $qrcode_id = intval($data['qrcode_id']);
            if($id){
                $data['create_time'] = time();
                $res = M('qrcode_url')->where(['id'=>$id])->save($data);
            }else{
                $data['url'] = uniqid();
                $data['create_time'] = time();
                $res = M('qrcode_url')->add($data);
            }



            return $this->success('操作成功', U('/jump/url?id='.$qrcode_id));
        }
        $this->assign('qrcode_id', $qrcode_id);
        $this->assign('qrcode', $qrcode_url);
        $this->display();
    }
}