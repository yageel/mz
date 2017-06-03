<?php
namespace Admin\Controller;

use Helpers\Helper;
use Helpers\HtmlHelper;
use Helpers\Presenter;
use Redis\MyRedis;
use Think\Controller;
use Think\Page;
use Think\Upload;
use Org\Util\File;

/**
 * @shengyue 2016-07-04
 * 刮刮卡管理
 * Class CardController
 * @package Admin\Controller
 */
class SysController extends BaseController
{
    /**
     * 消息列表
     */
    public function msg()
    {
        $list = M('auto_msg')->select();

        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 消息编辑
     */
    public function msg_edit(){
        $id = I('id','');
        if(IS_POST){
            $data = $_POST;
            $msg = M('auto_msg')->where(array('send_type'=>$data['send_type']))->find();
            if($msg && $msg['id'] != $id){
                return $this->error('该消息类型已经存在，请不要重复添加', U('sys/msg_edit',array('id'=>$id)));
            }
            $data['create_time'] = time();
            $data['update_time'] = time();
            if($id){
                $res = M('auto_msg')->where(array('id'=>$id))->save($data);
            }else{
                $res = M('auto_msg')->add($data);
            }
            if($res){
                return $this->success('操作成功',U('sys/msg'));
            }else{
                return $this->error('保存失败', U('sys/msg_edit',array('id'=>$id)));
            }
        }
        if($id){
            $msg = M('auto_msg')->where(array('id'=>$id))->find();
            $this->assign('msg', $msg);
        }
        $this->display();
    }

    /**
     * 签到数据
     */
    public function signed(){
        $signed = 1;
        $redis = \Redis\MyRedis::getGameInstance();
        if($redis->exists("set:signed")){
            $signed = intval($redis->get("set:signed"));
        }

        if(IS_POST){
            $signed = I('post.signed',1,'intval');
            $signed = $signed < 1?1:$signed;
            $redis->set("set:signed", $signed);
        }

        $this->assign('signed', $signed);
        $this->display();
    }

    /**
     * 短信发送
     */
    public function sms(){
        // $this

        if(IS_POST){
            $send_member = I('send_member',"","trim");
            $send_msg = I('send_msg','','trim');

            $send_member = explode("\r\n", $send_member);
            $member_list = [];
            foreach($send_member as $member){
                if(trim($member)){
                    $member_list[] = trim($member);
                }
            }
            if($send_member && $send_msg){
                file_put_contents("/data/log/send_sys.log",date("Y-m-d H:i:s=")."【{$this->admin['id']}={$this->admin['uname']}】[".join(',',$member_list)."][{$send_msg}]\r\n",FILE_APPEND);

                $result = send_msgs(join(',',$member_list), $send_msg);

                file_put_contents("/data/log/send_sys.log",date("Y-m-d H:i:s=").$result."\r\n", FILE_APPEND);
                if($result >0){
                    $this->success("发送成功【".$result.'】');
                }else{
                    $this->error("发送失败【".$result.'】');
                }

            }else{
                $this->error("请输入接收用户手机号和短信内容");
            }

            return false;
        }
        $this->display();
    }

    /**
     * 网站基础设置
     */
    public function basic(){
        if(IS_POST){
            $data = $_POST;
            $settingstr = "<?php \n return ".var_export($data, true).";\n ?>";
            file::write_file(COMMON_PATH . 'Conf/other.php',$settingstr);
            return $this->success("编辑成功~");
        }

        $this->assign('config',(array)load_config(COMMON_PATH . 'Conf/other.php'));
        $this->display();
    }

}
