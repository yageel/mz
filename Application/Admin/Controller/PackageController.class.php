<?php
namespace Admin\Controller;
use Think\Controller;
use Org\Util\Strings;
use Think\Page;

class PackageController extends BaseController {
    public function index(){
        $where = ['status'=>['lt', 4]];
        $db = M('Package'); // 实例化User对象
        $count = $db->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $db->where($where)->order("weight DESC, id DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 编辑套餐
     */
    public function edit(){
        $id = I('request.id',0,'intval');
        // 自动回跳列表页
        if(strpos($_SERVER['HTTP_REFERER'],'/package/index') !== false){
            $_SESSION['jump_url'] = $_SERVER['HTTP_REFERER'];
        }
        if(IS_POST){
            $data = $_POST;
            if(empty($data['package_name'])){
                return $this->error("请输入套餐名称~");
            }

            if(empty($data['package_money'])){
                return $this->error("请输入套餐价格~");
            }

            if(empty($data['package_time'])){
                return $this->error("请输入套餐时长~");
            }


            if( $id ){
                $data['update_time'] = time();
                $res = M('package')->where(['id'=>$id])->save($data);
            }else{
                $data['update_time'] = time();
                $data['create_time'] = time();
                // 添加必要字段
                $res = M('package')->add($data);
            }

            if($res){
                return $this->success("操作成功", $_SESSION['jump_url']?$_SESSION['jump_url']:U('/package/index'));
            }else{
                return $this->error("操作失败");
            }
        }

        if($id){
            $detail = M('package')->where(['id'=>$id])->find();
            $this->assign('detail', $detail);
        }

        $this->display();
    }

    /**
     * 套餐上下架
     */
    public function up(){
        $id = I('request.id',0,'intval');
        $status = I('request.status', 1, 'intval');
        M('package')->where(['id'=>$id])->save(['status'=>$status]);
        return $this->success("操作成功~");
    }

    /**
     * 设备二维码
     */
    public function del(){
        $id = I('request.id',0,'intval');
        M('package')->where(['id'=>$id])->save(['status'=>4]);
        return $this->success("删除成功~");
    }
}