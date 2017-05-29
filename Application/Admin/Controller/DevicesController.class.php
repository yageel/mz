<?php
namespace Admin\Controller;
use Think\Controller;
use Org\Util\Strings;
use Think\Page;

class DevicesController extends BaseController {
    public function index(){
        $where = [];
        $db = M('Devices'); // 实例化User对象
        $count = $db->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $db->where($where)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 编辑设备
     */
    public function edit(){
        $id = I('request.id',0,'intval');
        // 自动回跳列表页
        if(strpos($_SERVER['HTTP_REFERER'],'/devices/index') !== false){
            $_SESSION['jump_url'] = $_SERVER['HTTP_REFERER'];
        }
        if(IS_POST){
            $data = $_POST;
            if(empty($data['device_number'])){
                return $this->error("请输入设备编号~");
            }

            if(empty($data['channel_user_id'])){
                return $this->error("请选择渠道信息~");
            }

            if(empty($data['operational_user_id'])){
                return $this->error("请选择运营人员信息~");
            }

            if(empty($data['user_id'])){
                return $this->error("请选择拥有者(魔座)人员信息~");
            }

            if( $id ){
                $data['update_time'] = time();
                $res = M('devices')->where(['id'=>$id])->save($data);
            }else{
                // 添加必要字段
                $data['create_time'] = time();
                $data['qrcode'] = uniqid() . Strings::randString(4) . Strings::randString(4);
                $res = M('devices')->add($data);
            }

            if($res){
                return $this->success("操作成功", $_SESSION['jump_url']?$_SESSION['jump_url']:U('/devices/index'));
            }else{
                return $this->error("操作失败");
            }
        }

        if($id){
            $detail = M('devices')->where(['id'=>$id])->find();
            $this->assign('detail', $detail);
        }

        // 对应城市
        $area_list = D('Area')->get_area_map();
        $this->assign('area_list', $area_list);

        // 运营人员
        $operational_list = D('Admin')->get_user_list_role(2);
        $this->assign('operational_list', $operational_list);

        // 对应渠道
        $operational_list = D('Admin')->get_user_list_role(3);
        $this->assign('channel_list', $operational_list);

        // 拥有者
        $user_list = D('Admin')->get_user_list_role(4);
        $this->assign('user_list', $user_list);

        $this->display();
    }

    /**
     * 运营流水
     */
    public function stream(){
        $id = I('request.id',0,'intval');

        $where = [];
        $where['device_id'] = $id;
        $where['status'] = 1;
        $db = M('order'); // 实例化User对象
        $count = $db->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $db->where($where)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('page', $show);
        $this->assign('list', $list);

        $this->display();
    }

    /**
     * 设备详情
     */
    public function detail(){
        $id = I('request.id',0,'intval');
        $detail = M('devices')->where(['id'=>$id])->find();
        $detail['operational'] = M('admin')->where(['id'=>$detail['operational_user_id']])->find();
        $detail['channel'] = M('admin')->where(['id'=>$detail['channel_user_id']])->find();
        $detail['user'] = M('admin')->where(['id'=>$detail['user_id']])->find();
        $this->assign('detail', $detail);
        // print_r($detail);
        $this->display();
    }

    /**
     * 设备二维码
     */
    public function qrcode(){
        $id = I('request.id',0,'intval');
        $detail = M('Devices')->where(['id'=>$id])->find();
        if($detail){
            if(!file_exists(APP_PATH."/../uploads/qrcode/".$detail['qrcode'] .".png")){
                if(!file_exists(APP_PATH."/../uploads/qrcode/")){
                    mkdir(APP_PATH."/../uploads/qrcode/",0755, true);
                }
                $sign = encrypt_password($detail['qrcode'], $detail['id']);
                $value = C('base_url')."index.php?s=/jump/qr/id/{$detail['qrcode']}/sign/{$sign}.html";
                include APP_PATH."/../ThinkPHP/Library/Vendor/phpqrcode/phpqrcode.php";
                $errorCorrectionLevel = 'L';//容错级别
                $matrixPointSize = 12;//生成图片大小
                //生成二维码图片
                \QRcode::png($value, APP_PATH."/../uploads/qrcode/".$detail['qrcode'] .".png", $errorCorrectionLevel, $matrixPointSize, 2);
            }
            return header("location: /uploads/qrcode/{$detail['qrcode']}.png");
        }else{
            return $this->error("没找到设备信息~");
        }
    }

    /**
     * 删除设备
     */
    public function del(){
        $id = I('request.id',0,'intval');
        M('devices')->where(['id'=>$id])->save(['status'=>4, 'update_time'=>time()]);
        return $this->success("操作成功~");
    }
}