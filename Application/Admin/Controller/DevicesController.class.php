<?php
namespace Admin\Controller;
use Think\Controller;
use Org\Util\Strings;
use Think\Page;

class DevicesController extends BaseController {
    public function index(){
        $where = [];

        // 运营人员设备
        if($this->admin['role'] == 2){
            $where['operational_user_id'] = $this->admin['id'];
        // 渠道人员
        }elseif($this->admin['role'] == 3){
            $where['channel_user_id'] = $this->admin['id'];
        // 魔座人员
        }elseif($this->admin['role'] == 4){
            $where['user_id'] = $this->admin['id'];
        }

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
                $data['qrcode'] = uniqid() . random(4) . random(4);
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
            if(!file_exists(APP_PATH."/../uploads/qrcode/".$detail['device_number'] .".png")){
                if(!file_exists(APP_PATH."/../uploads/qrcode/")){
                    mkdir(APP_PATH."/../uploads/qrcode/",0755, true);
                }
                //
                $sign = encrypt_password($detail['qrcode'], $detail['id']);
                $value = C('base_url')."index.php?s=/index/index/type/1/gfrom/2/qr/{$detail['qrcode']}/sign/{$sign}.html";
                include APP_PATH."/../ThinkPHP/Library/Vendor/phpqrcode/phpqrcode.php";
                $errorCorrectionLevel = 'L';//容错级别
                $matrixPointSize = 12;//生成图片大小
                //生成二维码图片
                \QRcode::png($value, APP_PATH."/../uploads/qrcode/".$detail['device_number'] .".png", $errorCorrectionLevel, $matrixPointSize, 2);
            }
            return header("location: /uploads/qrcode/{$detail['device_number']}.png");
        }else{
            return $this->error("没找到设备信息~");
        }
    }

    public function qrcode2(){
        $id = I('request.id',0,'intval');
        $detail = M('Devices')->where(['id'=>$id])->find();

        if($detail){
            $qrcode_path = APP_PATH."/../uploads/qrcode/".$detail['qrcode'] .".png";
            //if(!file_exists($qrcode_path))
            {
                if(!file_exists(APP_PATH."/../uploads/qrcode/")){
                    mkdir(APP_PATH."/../uploads/qrcode/",0755, true);
                }
                //
                $sign = encrypt_password($detail['qrcode'], $detail['id']);
                $value = C('base_url')."index.php?s=/index/index/type/1/gfrom/2/qr/{$detail['qrcode']}/sign/{$sign}.html";
                include APP_PATH."/../ThinkPHP/Library/Vendor/phpqrcode/phpqrcode.php";
                $errorCorrectionLevel = 'L';//容错级别
                $matrixPointSize = 12;//生成图片大小
                //生成二维码图片
                \QRcode::png($value, $qrcode_path, $errorCorrectionLevel, $matrixPointSize, 2);

                $logo = APP_PATH . '/../Public/images/logo.png';//需要显示在二维码中的Logo图像
                $QR = $qrcode_path;

                $QR = imagecreatefromstring ( file_get_contents ( $QR ) );
                $QR_width = imagesx ( $QR );
                $QR_height = imagesy ( $QR );

                $font = APP_PATH ."../Public/fonts/msyhbd.ttf";
                $red = imagecolorallocate($QR, 250,0, 0);
                imagettftext($QR, 22, 0, $QR_width/2 - 30, $QR_height- 0, $red, $font,$detail['device_number']);

                if (file_exists($logo)) {

                    $logo = imagecreatefromstring ( file_get_contents ( $logo ) );
                    $logo_width = imagesx ( $logo );
                    $logo_height = imagesy ( $logo );
                    $logo_qr_width = $QR_width / 5;
                    $scale = $logo_width / $logo_qr_width;
                    $logo_qr_height = $logo_height / $scale;
                    $from_width = ($QR_width - $logo_qr_width) / 2;
                    imagecopyresampled ( $QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height );
                }

                imagepng ( $QR, $qrcode_path);//带Logo二维码的文件名

            }


            return header("location: /uploads/qrcode/{$detail['device_number']}.png");
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

    /**
     * 压缩二维码
     */
    public function zip(){
        function addFileToZip($path,$zip){
            $handler=opendir($path); //打开当前文件夹由$path指定。
            while(($filename=readdir($handler))!==false){
                if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
                    if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                        addFileToZip($path."/".$filename, $zip);
                    }else{ //将文件加入zip对象
                        $zip->addFile($path."/".$filename);
                    }
                }
            }
            @closedir($path);
        }

        $zip=new ZipArchive();
        if($zip->open('images.zip', ZipArchive::OVERWRITE)=== TRUE){
            addFileToZip('images/', $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
            $zip->close(); //关闭处理的zip文件
        }
    }

    /**
     * 导入设备
     */
    public function import(){
        if(IS_POST){
            set_time_limit(0);
            header("Content-type: text/html; charset=utf-8");
            setlocale(LC_ALL, 'zh_CN');
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
                        // 0设备编号	1机器串码	2归属城市	3门店名称	4门店地址	5门店经度	6门店纬度	7运营人员	8运营电话	9渠道人员	10渠道电话	11魔座人员	12魔座电话	13连接方式

                        $item = explode(",", $info);
                        //print_r($item);
                        if (count($item) != 14) {
                            $ei++;
                            $error_msg .= "第{$i}行，只有" . count($item) . "列，不标准<br/>";
                            continue;
                        }
                        foreach($item as $k=>$val){
                            $item[$k] = trim($val);
                        }

                        // 运营人员添加
                        $operational_user = M('admin')->where(['mobile'=>trim($item[8])])->find();
                        if($operational_user){
                            $operational_user_id = $operational_user['id'];
                            // 更新角色
                            if(!in_array(2, explode(',', $operational_user['role_list']))){
                                $role_list = $operational_user['role_list']?explode(',', $operational_user['role_list']):[];
                                $role_list[] = 2;
                               M('admin')->where(['id'=>$operational_user['id']])->save(['role_list'=>join(',', $role_list)]);
                            }
                        }else{
                            if(empty($item[7])){
                                $ei++;
                                $error_msg .= "第{$i}行，运营人员为空<br/>";
                                continue;
                            }

                            if(empty($item[8])){
                                $ei++;
                                $error_msg .= "第{$i}行，运营电话为空<br/>";
                                continue;
                            }
                            /*
                             'username',  'contact_name', 'pic', 'pwd', 'salt', 'role', 'rebate_id', 'mobile', 'city_id',
                            'shop_name', 'shop_address', 'lon', 'lat', 'openid', 'status', 'create_time', 'update_time', 'last_time'
                            */
                            $salt = random(12);
                            $operational_user_id = M('admin')->add([
                                'username' => $item[8],
                                'contact_name' => $item[7],
                                'salt' => $salt,
                                'pwd' => encrypt_password('123456', $salt),
                                'role' => 2,
                                'mobile' => $item[8],
                                'create_time' => time(),
                                'update_time' => time(),
                                'last_time' => 0,
                                'status' => 1,
                                'role_list'=>2
                            ]);
                        }

                        if (!$operational_user_id) {
                            $ei++;
                            $error_msg .= "第{$i}行，运营人员添加失败<br/>";
                            continue;
                        }

                        // 渠道人员添加
                        $channel_user = M('admin')->where(['mobile'=>trim($item[10])])->find();
                        if($channel_user){
                            $channel_user_id = $channel_user['id'];

                            // 更新角色
                            if(!in_array(3, explode(',', $channel_user['role_list']))){
                                $role_list = $channel_user['role_list']?explode(',', $channel_user['role_list']):[];
                                $role_list[] = 3;
                                M('admin')->where(['id'=>$channel_user['id']])->save(['role_list'=>join(',', $role_list)]);
                            }
                        }else{
                            /*
                             'username',  'contact_name', 'pic', 'pwd', 'salt', 'role', 'rebate_id', 'mobile', 'city_id',
                            'shop_name', 'shop_address', 'lon', 'lat', 'openid', 'status', 'create_time', 'update_time', 'last_time'
                            */

                            $city = M('area')->where(['city_name'=>$item[2]])->find();
                            if(!$city){
                                $ei++;
                                $error_msg .= "第{$i}行，【$item[2]】没找到对应的城市<br/>";
                                continue;
                            }
                            $city_id = $city['id'];

                            if(empty($item[9])){
                                $ei++;
                                $error_msg .= "第{$i}行，渠道人员为空<br/>";
                                continue;
                            }

                            if(empty($item[10])){
                                $ei++;
                                $error_msg .= "第{$i}行，渠道电话为空<br/>";
                                continue;
                            }

                            $salt = random(12);
                            $channel_user_id = M('admin')->add([
                                'username' => $item[10],
                                'contact_name' => $item[9],
                                'salt' => $salt,
                                'pwd' => encrypt_password('123456', $salt),
                                'role' => 3,
                                'mobile' => $item[10],
                                'create_time' => time(),
                                'update_time' => time(),
                                'last_time' => 0,
                                'city_id' => $city_id,
                                'shop_name' => "{$item[3]}",
                                'shop_address' => "{$item[4]}",
                                'lon' => "{$item[6]}",
                                'lat' => "{$item[5]}",
                                'status'=>1,
                                'role_list' => 3
                            ]);
                        }
                        if (!$channel_user_id) {
                            $ei++;
                            $error_msg .= "第{$i}行，渠道人员添加失败<br/>";
                            continue;
                        }

                        // 魔座人员添加
                        $device_user = M('admin')->where(['mobile'=>trim($item[12])])->find();
                        if($device_user){
                            $device_user_id = $device_user['id'];

                            // 更新角色
                            if(!in_array(4, explode(',', $device_user['role_list']))){
                                $role_list = $device_user['role_list']?explode(',', $device_user['role_list']):[];
                                $role_list[] = 4;
                                M('admin')->where(['id'=>$device_user['id']])->save(['role_list'=>join(',', $role_list)]);
                            }
                        }else{
                            /*
                             'username',  'contact_name', 'pic', 'pwd', 'salt', 'role', 'rebate_id', 'mobile', 'city_id',
                            'shop_name', 'shop_address', 'lon', 'lat', 'openid', 'status', 'create_time', 'update_time', 'last_time'
                            */

                            if(empty($item[11])){
                                $ei++;
                                $error_msg .= "第{$i}行，魔座人员为空<br/>";
                                continue;
                            }

                            if(empty($item[12])){
                                $ei++;
                                $error_msg .= "第{$i}行，魔座电话为空<br/>";
                                continue;
                            }
                            $salt = random(12);
                            $device_user_id = M('admin')->add([
                                'username' => $item[12],
                                'contact_name' => $item[11],
                                'salt' => $salt,
                                'pwd' => encrypt_password('123456', $salt),
                                'role' => 4,
                                'mobile' => $item[12],
                                'create_time' => time(),
                                'update_time' => time(),
                                'last_time' => 0,
                                'status'=>1,
                                'role_list' => 4
                            ]);
                        }

                        if (!$channel_user_id) {
                            $ei++;
                            $error_msg .= "第{$i}行，魔座人员添加失败<br/>";
                            continue;
                        }

                        // 设备信息
                        $device_info = M('devices')->where(['machine_number'=>$item[1]])->find();
                        if($device_info){
                            $device_id = $device_info['id'];
                        }else{
                            if(empty($item[0])){
                                $ei++;
                                $error_msg .= "第{$i}行，设备编号为空<br/>";
                                continue;
                            }

                            if(empty($item[1])){
                                $ei++;
                                $error_msg .= "第{$i}行，机器串号为空<br/>";
                                continue;
                            }
                            $device_id = M('devices')->add(
                                [
                                    'device_number'=>"{$item[0]}",
                                    'machine_number'=>"{$item[1]}",
                                    'user_id'=>"{$device_user_id}",
                                    'operational_user_id'=>"{$operational_user_id}",
                                    'channel_user_id'=>"{$channel_user_id}",
                                    'link_mode'=>strtolower($item[13])=='wifi'?1:2,
                                    'qrcode'=>uniqid() . random(4) . random(4),
                                    'create_time'=>time(),
                                    'update_time'=>time(),
                                    'status'=>1
                                ]
                            );
                        }

                        if (!$device_id) {
                            $ei++;
                            $error_msg .= "第{$i}行，设备添加失败<br/>";
                            continue;
                        }
                        $si++;
                        $success_msg .="第{$i}行，设备添加成功<br/>";

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
        $this->display();
    }
}