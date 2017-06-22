<?php

namespace Admin\Controller;

use Helpers\Presenter;
use Think\Controller;
use Think\Page;
use Org\Util\Strings;

class AdminUserController extends BaseController
{
    /**
     * 微信openid信息
     */
    public function index()
    {
        $city_list = M('city')->where(array('status'=>1))->select();
        $this->assign('city_list', $city_list);

        $tab = I('request.tab','','trim');
        $kw = I('request.kw','','trim');
        $where = [];

        if($kw){
            $where['contact_name'] = array('like', '%'.$kw.'%');
        }

        if($tab){
            if($tab == 'channel'){
                $where['role'] = 3;
            }elseif($tab == 'device'){
                $where['role'] = 4;
            }elseif($tab == 'spread'){
                $where['role'] = 5;
            }elseif($tab == 'operational'){
                $where['role'] = 2;
            }
        }else{
            if($this->admin['role'] == 1){
                $where['role'] = 2;
                $tab = 'operational';
            }elseif($this->admin['role'] == 2){
                $where['role'] = 3;
                $tab = 'channel';
            }elseif($this->admin['role'] == 3){
                $where['role'] = 4;
                $tab = 'device';
            }elseif($this->admin['role'] == 4){
                $where['role'] = 5;
                $tab = 'spread';
            }
        }

        //
        // 运营筛选
        if($this->admin['role'] == 2){
            if($tab == '' OR $tab == 'operational'){
                $where['role'] = 2;
                $where['id'] = $this->admin['id'];
            }elseif($tab == 'channel'){
                $where['role'] = 3;
                $where['id'] = ['EXP', "IN(SELECT channel_user_id FROM t_devices WHERE operational_user_id='{$this->admin['id']}')"];
            }elseif($tab == 'device'){
                $where['role'] = 4;
                $where['id'] = ['EXP', "IN(SELECT user_id FROM t_devices WHERE operational_user_id='{$this->admin['id']}')"];
            }elseif($tab == 'spread'){
                $where['role'] = 5;//`device_id`, `user_id`,
                $where['id'] = ['EXP', "IN(SELECT user_id FROM t_devices_spread WHERE device_id IN(SELECT id FROM t_devices WHERE operational_user_id='{$this->admin['id']}'))"];
            }

            // 渠道筛选
        }elseif($this->admin['role'] == 3){
            if($tab == 'channel'){
                $where['role'] = 3;
                $where['id'] = ['EXP', "IN(SELECT channel_user_id FROM t_devices WHERE channel_user_id='{$this->admin['id']}')"];
            }elseif($tab == 'device'){
                $where['role'] = 4;
                $where['id'] = ['EXP', "IN(SELECT user_id FROM t_devices WHERE channel_user_id='{$this->admin['id']}')"];
            }elseif($tab == 'spread'){
                $where['role'] = 5;//`device_id`, `user_id`,
                $where['id'] = ['EXP', "IN(SELECT user_id FROM t_devices_spread WHERE device_id IN(SELECT id FROM t_devices WHERE channel_user_id='{$this->admin['id']}'))"];
            }
            // 魔座筛选
        }elseif($this->admin['role'] == 4){
            if($tab == 'device'){
                $where['role'] = 4;
                $where['id'] = ['EXP', "IN(SELECT user_id FROM t_devices WHERE user_id='{$this->admin['id']}')"];
            }elseif($tab == 'spread'){
                $where['role'] = 5;//`device_id`, `user_id`,
                $where['id'] = ['EXP', "IN(SELECT user_id FROM t_devices_spread WHERE device_id IN(SELECT id FROM t_devices WHERE user_id='{$this->admin['id']}'))"];
            }
        }

        $this->assign('tab', $tab);
        $this->assign('kw', $kw);

        $users = M('Admin'); // 实例化User对象
        $count = $users->where($where)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $users->where($where)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $i=>$user){
            // 运营人员
            if($user['role'] == 2){
                $list[$i]['total_channel'] = M()->query("SELECT COUNT(*) AS tp_count FROM (SELECT id FROM t_devices WHERE operational_user_id='{$user['id']}' GROUP BY channel_user_id)t")[0]['tp_count'];// D('Devices')->where(['operational_user_id'=>$user['id']])->group("shop_id")->count();
                $list[$i]['total_device'] = D('Devices')->where(['operational_user_id'=>$user['id']])->count();
            }elseif($user['role'] == 3){
                $list[$i]['total_device'] = D('Devices')->where(['channel_user_id' => $user['id']])->count();
            }elseif($user['role'] == 4){
                $list[$i]['total_device'] = D('Devices')->where(['device_user_id' => $user['id']])->count();
            }elseif($user['role'] == 5){
                 $list[$i]['total_device'] = M('devices_spread')->where(['user_id' => $user['id']])->count();
            }
        }

        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    /**
     * 新建用户
     */
    public function edit(){
        $tab = I('request.tab','','trim');
        $id = I('request.id',0,'intval');

        if(IS_POST){
            $data = $_POST;
            if(empty($data['contact_name'])){
                return $this->error("请输入联系人员姓名~");
            }

            if(!is_mobile($data['mobile'])){
                return $this->error("请正确输入联系人员手机号码~");
            }

            if(empty($data['username'])){
                return $this->error("请输入登陆账户~");
            }

            if($tab){
                if($tab == 'channel'){
                    $data['role'] = 3;
                    if(empty($data['city_id'])){
                        return $this->error("请选择门店所在城市");
                    }

                    if(empty($data['shop_name'])){
                        return $this->error("请输入门店名称");
                    }

                    if(empty($data['shop_address'])){
                        return $this->error("请输入门店地址");
                    }

                    if(empty($data['lon'])){
                        return $this->error("请输入门店地址对应经度");
                    }

                    if(empty($data['lat'])){
                        return $this->error("请输入门店地址对应维度");
                    }
                }elseif($tab == 'device'){
                    $data['role'] = 4;
                }elseif($tab == 'spread'){
                    $data['role'] = 5;
                }elseif($tab == 'operational'){
                    $data['role'] = 2;
                }
            }else{
                $data['role'] = 2;
            }

            if($data['password']){
                $salt = Strings::randString(12);
                $data['salt'] = $salt;
                $data['pwd'] = encrypt_password($data['password'], $salt);
            }

            if($id){
                //  判断登陆名重复
                $info = D("Admin")->where(['id'=>$id])->find();
                if($info['username'] != $data['username']){
                    $username = M('admin')->where(['username'=>$data['username']])->find();
                    if($username){
                        return $this->error("该用户名已经存在~");
                    }
                }

                // 判断手机号重复
                if($info['mobile'] != $data['mobile']){
                    $mobile = M("admin")->where(['mobile'=>$data['mobile']])->find();
                    if($mobile){
                        return $this->error("该手机号已经存在系统~");
                    }
                }
                $data['update_time'] = time();
                $res = M('admin')->where(['id'=>$id])->save($data);
            }else{
                if(empty($data['password'])){
                    return $this->error("新建账户密码不能为空~");
                }

                // 判断手机号重复
                $mobile = M("admin")->where(['mobile'=>$data['mobile']])->find();
                if($mobile){
                    return $this->error("该手机号已经存在系统~");
                }

                // 判断登陆名重复
                $username = M('admin')->where(['username'=>$data['username']])->find();
                if($username){
                    return $this->error("该登陆名已经存在~");
                }

                $data['create_time'] = time();
                $res = M('admin')->add($data);
            }

            if($res){
                return $this->success("操作成功",U('/admin_user/index',['tab'=>$tab]));
            }else{
                return $this->error("操作失败~");
            }
        }

        if($id){
            $detail = M('admin')->where(['id'=>$id])->find();
            if($detail['role'] == 1){
                $tab = "system";
            }elseif($detail['role'] == 2){
                $tab = "operational";
            }elseif($detail['role'] == 3){
                $tab = "channel";
            }elseif($detail['role'] == 4){
                $tab = "device";
            }elseif($detail['role'] == 5){
                $tab = "spread";
            }
            $this->assign('detail', $detail);
        }
        $this->assign('tab', $tab);

        $area_map = D('Area')->get_area_map();
        $this->assign('area_map', $area_map);
        if($tab == 'operational'){
            $this->display('AdminUser/operational');
        }elseif($tab == 'channel'){
            $this->display('AdminUser/channel');
        }elseif($tab == 'device'){
            $this->display('AdminUser/device');
        }elseif($tab == 'spread'){
            $this->display('AdminUser/spread');
        }else{
            $this->display();
        }
    }

    /**
     * 导入用户
     */
    public function import(){
        $tab = I('request.tab','','trim');
        $role = 0;
        if($tab == 'operational'){
            $this->assign('import_name',"运营账户");
            $role = 2;
        }elseif($tab == 'channel'){
            $this->assign('import_name',"渠道账户");
            $role = 3;
        }elseif($tab == 'device'){
            $this->assign('import_name',"模座账户");
            $role = 4;
        }elseif($tab == 'spread'){
            $this->assign('import_name',"推广账户");
            $role = 5;
        }else{
            $this->assign('import_name',"系统账户");
        }


        if(IS_POST){
            if($role < 1){
                return $this->error("请选择导入角色类型~");
            }

            do{
                $error_msg = '';
                $success_msg = '';
                $i = 0;
                $ei = 0;
                $si = 0;
                $file_path = $_FILES['file']['tmp_name'];

                if (file_exists($file_path)) {
                    $fp = fopen($file_path, "r");

                    while ($line = fgetcsv($fp, 10240, "\t")) {
                        usleep(10);// 10微秒

                        $info = iconv('gb2312', "utf-8//IGNORE", $line[0]);
                        if ($info === false) {
                            $info = $line[0];
                        }

                        if ($i == 0) {
                            if (strpos($info, "设备编号") === false) {
                                $error_msg .= '请上传标准CSV文件2';
                                break;
                            }
                            $i++;
                            continue;
                        }
                        $i++;
                        // 0联系人	1联系号码	2登录用户	3登录密码
                        $item = explode(",", $info);
                        $num = $role == 3?9:4;
                        if (count($item) != $num) {
                            $ei++;
                            $error_msg .= "第{$i}行，只有" . count($item) . "列，不标准<br/>";
                            continue;
                        }
                        foreach($item as $k=>$val){
                            $item[$k] = trim($val);
                        }

                        // 运营人员添加//
                        $user = M('admin')->where(['username'=>trim($item[2])])->find();
                        if($user){
                            if($user['mobile'] != trim($item[1])){
                                $ei++;
                                $error_msg .= "第{$i}行，【{$item[2]}】用户名已经存在<br/>";
                                continue;
                            }
                            if(in_array($role, explode(',', $user['role_list']))){
                                $si++;
                                $success_msg .="第{$i}行，用户已经存在成功<br/>";
                                continue;
                            }else{
                                $role_list = explode(',', $user['role_list']);
                                foreach($role_list as $ky=>$ry){
                                    if(empty($ry)){
                                        unset($role_list[$ky]);
                                    }
                                }
                                $role_list[] = $role;
                                M('admin')->where(['id'=>$user['id']])->save(['role_list'=>join(',', $role_list)]);
                                $si++;
                                $success_msg .="第{$i}行，用户编辑成功<br/>";
                                continue;
                            }
                        }else{
                            $salt = random(12);
                            $pwd = encrypt_password(trim($item[3]), $salt);
                            if($role == 3){
                                $city = M('area')->where(['city_name'=>trim($item[8])])->find();
                                if(empty($city)){
                                    $ei++;
                                    $error_msg .= "第{$i}行，【{$item[8]}】城市不存在，请在【基础设置->运营城市】里面查找对比<br/>";
                                    continue;
                                }
                                M('admin')->add([
                                    'username' => time($item[2]),
                                    'mobile' => trim($item[1]),
                                    'contact_name' => trim($item[0]),
                                    'salt' => $salt,
                                    'pwd' => $pwd,
                                    'role'=>$role,
                                    'role_list' => $role,
                                    'status' => 1,
                                    'shop_name' => trim($item[4]),
                                    'shop_address' => trim($item[5]),
                                    'lon' => trim($item[7]),
                                    'lat' => trim($item[6]),
                                    'city_id' => $city['id']
                                ]);
                            }else{
                                M('admin')->add([
                                    'username' => time($item[2]),
                                    'mobile' => trim($item[1]),
                                    'contact_name' => trim($item[0]),
                                    'salt' => $salt,
                                    'pwd' => $pwd,
                                    'role'=>$role,
                                    'role_list' => $role,
                                    'status' => 1

                                ]);
                            }


                            $si++;
                            $success_msg .="第{$i}行，用户添加成功<br/>";

                        }
                    }
                }
            }while(false);
            $this->assign('show', true);
            $this->assign('i', $i); // 总执行
            $this->assign('ei', $ei); // 失败行
            $this->assign('si', $si); // 成功行
            $this->assign('error_msg', $error_msg); // 失败详情
            $this->assign("success_msg", $success_msg); // 成功详情
        }
        $this->assign('tab', $tab);
        $this->display();
    }

    /**
     * 用户编辑
     */
    public function info(){
        $id = $this->admin['id'];
        if(IS_POST){
            $data = $_POST;
            if(empty($data['contact_name'])){
                return $this->error("请输入联系人员姓名~");
            }

            if(!is_mobile($data['mobile'])){
                return $this->error("请正确输入联系人员手机号码~");
            }

            if(empty($data['username'])){
                return $this->error("请输入登陆账户~");
            }

            if($data['password']){
                $salt = Strings::randString(12);
                $data['salt'] = $salt;
                $data['pwd'] = encrypt_password($data['password'], $salt);
            }

            if($id){
                //  判断登陆名重复
                $info = D("Admin")->where(['id'=>$id])->find();
                if($info['username'] != $data['username']){
                    $username = M('admin')->where(['username'=>$data['username']])->find();
                    if($username){
                        return $this->error("该用户名已经存在~");
                    }
                }

                // 判断手机号重复
                if($info['mobile'] != $data['mobile']){
                    $mobile = M("admin")->where(['mobile'=>$data['mobile']])->find();
                    if($mobile){
                        return $this->error("该手机号已经存在系统~");
                    }
                }
                $data['update_time'] = time();
                $res = M('admin')->where(['id'=>$id])->save($data);
            }
            if($res){
                return $this->success("操作成功",U('/admin_user/info'));
            }else{
                return $this->error("操作失败~");
            }
        }

        $this->assign('detail', $this->admin);
        $this->display();
    }

}