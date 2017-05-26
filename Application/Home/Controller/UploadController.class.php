<?php

namespace Admin\Controller;

use Think\Controller;
use Think\Page;
use Org\Util\File;

class UploadController extends BaseController
{
// 列表
    public function show(){
        $sid = trim($_REQUEST['sid']);//模型
        $fileback = !empty($_REQUEST['fileback']) ? trim($_REQUEST['fileback']) : 'pic';//回跳input
        $this->assign('sid', $sid);
        $this->assign('fileback',$fileback);
        $this->display();
    }
    // 本地图片上传
    public function upload(){

        echo('<div style="font-size:12px; height:30px; line-height:30px">');
        $uppath = './upload/';
        $sid = trim($_POST['sid']);//模型
        $fileback = !empty($_POST['fileback']) ? trim($_POST['fileback']) : 'pic';//回跳input
        if ($sid) {
            $uppath.= $sid.'/';
            @mkdir($uppath, 0755, true);
        }
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =    $uppath; // 设置附件上传根目录
        $upload->subName = array('date','Y/m');
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        if (!$info = $upload->upload()) {
            $error = $upload->getError();
            if($error == '上传文件类型不允许'){
                $error .= '，可上传<font color=red>JPEG,JPG,PNG,GIF</font>';
            }
            exit($error.' [<a href="?s=/admin/upload/show/sid/'.$sid.'/fileback/'.$fileback.'">重新上传</a>]');
            //dump($up->getErrorMsg());
        }

        //print_r($info);
//        //是否添加水印
//        if (C('upload_water')) {
//            import("ORG.Util.Image");
//            Image::water($uppath.$uploadList[0]['savename'],C('upload_water_img'),'',C('upload_water_pct'),C('upload_water_pos'));
//        }
//        //是否生成缩略图
//        if (C('upload_thumb')) {
//            $thumbdir = substr($uploadList[0]['savename'],0,strrpos($uploadList[0]['savename'], '/'));
//            mkdirss($uppath_s.$thumbdir);
//            import("ORG.Util.Image");
//            Image::thumb($uppath.$uploadList[0]['savename'],$uppath_s.$uploadList[0]['savename'],'',C('upload_thumb_w'),C('upload_thumb_h'),true);
//        }
        echo "<script type='text/javascript'>parent.document.getElementById('".$fileback."').value='/upload/".$sid.'/'.$info['upthumb']['savepath'].$info['upthumb']['savename']."';parent.document.getElementById('".$fileback."').setAttribute('data-url','/upload/".$sid.'/'.$info['upthumb']['savepath'].$info['upthumb']['savename']."');</script>";
        echo '文件上传成功　[<a href="?s=/admin/upload/show/sid/'.$sid.'/fileback/'.$fileback.'">重新上传</a>]';
        //<a href="'.$uppath.$uploadList[0]['savename'].'" target="_blank"><font color=red>'.$uploadList[0]['savename'].'</font></a>
        echo '</div>';
    }

    public function select(){
        $allowFiles = array('.jpg','.png','.gif');
        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

        $listSize = 50;
        /* 获取参数 */
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
        $start = isset($_GET['p']) ? intval($_GET['p']) : 1;
        $start = $start<1?1:$start;
        $start = ($start - 1)*$listSize;
        $end = $start + $size;

        $uppath = realpath('./upload/').'/';
        $sid = trim($_REQUEST['sid']);//模型
        $fileback = !empty($_REQUEST['fileback']) ? trim($_REQUEST['fileback']) : 'pic';//回跳input

        $this->assign('sid', $sid);
        $this->assign('fileback', $fileback);

        if ($sid) {
            $uppath.= $sid.'/';
            @mkdir($uppath, 0755, true);
        }

        /* 获取文件列表 */
        $uppath = str_replace('\\','/',$uppath);
        $files = getfiles($uppath, $allowFiles);

        if (!count($files)) {

            /* 返回数据 */
            $result = array(
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => count($files)
            );

            $this->assign('data', $result);
            $this->display();
            return true;
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }
//倒序
//for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
//    $list[] = $files[$i];
//}


        /* 返回数据 */
        $result = array(
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        );


        $Page = new Page(count($files), $listSize);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        $this->assign('page', $show);
        $this->assign('data', $result);
        $this->display();

    }

    public function showgood(){
        $sid = trim($_REQUEST['sid']);//模型
        $fileback = !empty($_REQUEST['fileback']) ? trim($_REQUEST['fileback']) : 'pic';//回跳input
        $this->assign('sid', $sid);
        $this->assign('fileback',$fileback);
        $this->display();
    }

    public function selectgood(){
        $methods = intval($_REQUEST['methods']);//模型
        $goods = D('Goods'); // 实例化User对象

        $count = $goods->where(array('goods_class' => $methods,'status'=>1,'visible_platform'=>1))->count();// 查询满足要求的总记录数
        $Page = new Page($count, 50);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出

        $list = $goods->where(array('goods_class' => $methods,'status'=>1,'visible_platform'=>1))->order('create_time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('page', $show);
        $this->assign('data', $list);
        $this->display();
    }





}