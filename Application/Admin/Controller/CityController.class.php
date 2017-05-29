<?php
namespace Admin\Controller;

use Helpers\Helper;
use Helpers\HtmlHelper;
use Helpers\Presenter;
use Redis\MyRedis;
use Think\Controller;
use Think\Page;
use Think\Upload;

/**
 * @shengyue 2016-06-06
 * 公众号管理
 * Class CityController
 * @package Admin\Controller
 */
class CityController extends BaseController
{
    public function index()
    {
        $city = D('City'); // 实例化User对象
        $count = $city->count();// 查询满足要求的总记录数
        $Page = new Page($count, 100);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $city->order('city_id ASC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();
    }

    /**
     * 编辑城市信息
     */
    public function edit()
    {
        $id = I('request.id', 0, 'intval');
        // 自动回跳列表页
        if(strpos($_SERVER['HTTP_REFERER'],'/city/index') !== false){
            $_SESSION['jump_url'] = $_SERVER['HTTP_REFERER'];
        }

        if (IS_POST) {
            $data = $_POST;
            foreach($data as $i=>$item){
                $data[$i] = trim($item);
            }
            if ($id) {
                $data['update_time'] = time();
                $res = M('city')->where(array('id' => $id))->save($data);

                $key = 't_city_'.$data['city_id'];
                MyRedis::getProInstance()->delete($key);
            } else {
                $data['update_time'] = time();
                $data['create_time'] = time();
                $res = M('city')->add($data);
            }

            if ($res) {
                return $this->success('操作成功', $_SESSION['jump_url']?$_SESSION['jump_url']:U('/city/index'));
            } else {
                return $this->error('操作失败');
            }
        }

        $city = [];
        if ($id) {
            $city = M('city')->find($id);
            $access_token = getAccessToken($city['city_id']);
            if ($access_token) {
                $this->assign('access_token', $access_token);
            }
        }

        $this->assign('city', $city);
        $this->display();
    }

    public function menu()
    {
        $this->display();
    }

    public function reply()
    {
        $model = D('city_auto_reply');
        $count = $model->count();
        $Page = new Page($count, 20);

        $show = $Page->show();
        $list = $model->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $city_map = cityMap();

        $this->assign('reply_msg_type_map', Presenter::$reply_msg_type_map);
        $this->assign('reply_type_map', Presenter::$reply_type_map);
        $this->assign('reply_status_map', Presenter::$reply_status_map);
        $this->assign('city_map', $city_map);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    public function replyedit()
    {
        $id = I('request.id', 0, 'intval');

        if (IS_POST) {
            $data = $_POST;
            $data['user_id'] = $_SESSION['login_user_id'];
            if ($id) {
                $data['update_time'] = time();
                $res = M('city_auto_reply')->where(['id' => $id])->save($data);

            } else {
                $data['update_time'] = time();
                $data['create_time'] = time();
                $res = M('city_auto_reply')->add($data);
            }

            if ($res) {
                return $this->success('操作成功', U('/city/reply'));
            } else {
                return $this->error('操作失败', U('/city/replyedit', ['id' => $id]));
            }
        }

        $model = [];
        $model['msg_type'] = null;
        $model['reply_type'] = null;
        $model['city_id'] = null;
        $model['status'] = null;
        if ($id) {
            $model = M('city_auto_reply')->find($id);
        }

        $msg_type_list = HtmlHelper::dropDownList('msg_type', Presenter::$reply_msg_type_map, $model['msg_type']);
        $reply_type_list = HtmlHelper::dropDownList('reply_type', Presenter::$reply_type_map, $model['reply_type']);
        $city_list = HtmlHelper::dropDownList('city_id', cityMap(), $model['city_id']);
        $status_list = HtmlHelper::dropDownList('status', Presenter::$reply_status_map, $model['status']);

        $this->assign('model', $model);
        $this->assign('msg_type_list', $msg_type_list);
        $this->assign('reply_type_list', $reply_type_list);
        $this->assign('city_list', $city_list);
        $this->assign('status_list', $status_list);
        $this->display();
    }

    /**
     * 刷新token
     */
    public function accesstoken(){
       $json = $this->ajax_json();
        do{
            $id = I('id',0,'intval');
            $city = M('city')->where(['city_id' => $id])->find();
            if(empty($city)){
                $json['msg'] = "没有找到要刷新的公众号";
                break;
            }
            $redis = \Redis\MyRedis::getTokenInstance();
            $res = $redis->delete("wechat_access_token".$city['appid']);
            $json['state'] = 200;
            $json['data'] = $res;
        }while(false);
        $this->ajaxReturn($json);
    }

    public function replydelete()
    {
        $id = I('request.id');
        if (D('city_auto_reply')->where(['id' => $id])->delete()) {
            return $this->success('操作成功', U('/city/reply'));
        } else {
            return $this->error('操作失败', U('/city/reply'));
        }
    }

    public function menuedit()
    {
        $city_id = I('request.id');
        $menu = '';
        $result = '';

        $access_token = getAccessToken($city_id);
        if (!$access_token) {
            return $this->error('没有找到Access_Token', U('/city/index'));
        }

        if (IS_GET) {
            $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=' . $access_token['access_token'];
            $result = https_request($url);
            $data = json_decode($result, true);
            if(!isset($data['errcode'])) {
                $menu = json_encode($data['menu'], JSON_UNESCAPED_UNICODE);
                $result = '';
            }
        }

        if (IS_POST) {
            $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token['access_token'];
            $menu = $_POST['menu'];
            $result = https_request($url, $menu);
        }

        $this->assign('city_id', $city_id);
        $this->assign('menu', $menu);
        $this->assign('result', $result);
        $this->display();
    }

    public function media()
    {
//        $model = D('city_media');// 临时素材表
        $model = D('city_material');// 永久素材表
        $count = $model->count();
        $Page = new Page($count, 20);

        $show = $Page->show();
        $list = $model->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $media_type_map = Presenter::$media_type_map;
        $city_map = cityMap();

        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('media_type_map', $media_type_map);
        $this->assign('city_map', $city_map);
        $this->display();
    }

    public function mediaUpload()
    {
        $errors = [];

        if (IS_POST) {
            if ($_FILES['file']['error'] == 0) {
                $type = $_POST['type'];
                $city_id = $_POST['city_id'];
                $fileExt = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                $fileName = time() . '_' . $city_id;
                $fileFullName = time() . '_' . $city_id . '.' . $fileExt;
                $filePath = Helper::getMediaPath($fileFullName);

                $file = new Upload();
                $file->rootPath = 'upload/';
                $file->autoSub = false;
                $file->savePath = Helper::MEDIA_PATH;
                $file->saveName = $fileName;
                if ($file->upload($_FILES)) {
                    $accessToken = getAccessToken($city_id);
                    if ($accessToken) {
                        if (class_exists('CURLFile')) {
                            $postData = ['media' => new \CURLFile($filePath)];
                        } else {
                            $postData = ['media' => '@' . $filePath];
                        }
//                        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$accessToken['access_token']}&type={$type}";// 临时素材
                        $url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$accessToken['access_token']}&type={$type}";// 永久素材
                        $result = https_request($url, $postData);
                        $data = json_decode($result, true);
                        if(!$data) {
                            $errors[] = '没有获取到结果';
                        } else if (isset($data['errcode'])) {
                            $errors = $data;
                        } else {
                            $data['city_id'] = $city_id;
                            $data['file_name'] = $fileFullName;
                            $data['created_at'] = time();// 永久素材需要，临时素材的时间已经获取到了
//                            $model = D('city_media');// 临时素材表
                            $model = D('city_material');// 永久素材表
                            if($model->data($data)->add()) {
                                return $this->success('操作成功', U('city/media'));
                            } else {
                                $errors[] = '保存失败';
                            }
                        }
                    } else {
                        $errors[] = 'AccessToken获取失败';
                    }
                    unlink($filePath);
                } else {
                    $errors[] = $file->getError();
                }
            } else {
                $errors[] = '文件上传有误';
            }
        }

        $this->assign('city_list', HtmlHelper::dropDownList('city_id', cityMap()));
        $this->assign('media_type_list', HtmlHelper::dropDownList('type', Presenter::$media_type_map));
        $this->assign('errors', $errors);
        $this->display();
    }

    public function mediaDelete()
    {
//        $model = D('city_media');// 临时素材表
        $model = D('city_material');// 永久素材表
        $city_media = $model->find($_GET['id']);

        // 永久素材
        if(!empty($city_media)) {
            $accessToken = getAccessToken($city_media['city_id']);
            if ($accessToken) {
                $url = "https://api.weixin.qq.com/cgi-bin/material/del_material?access_token={$accessToken['access_token']}";
                $postData = json_encode(['media_id' => $city_media['media_id']]);
                $result = https_request($url, $postData);
                $data = json_decode($result, true);
                if(empty($data)) {
                    return $this->error('没有获取到结果');
                } else if ($data['errcode'] != 0) {
                    return $this->error('错误：' . $data['errmsg']);
                } else {
                    $filePath = Helper::getMediaPath($city_media['file_name']);
                    if ($model->delete($_GET['id'])) {
                        @unlink($filePath);
                        return $this->success('删除成功');
                    } else {
                        return $this->error('删除失败');
                    }
                }
            } else {
                return $this->error('AccessToken获取失败');
            }
        } else {
            return $this->error('找不到该记录');
        }

        // 原临时素材代码
//        if(!empty($city_media)) {
//            if (strtotime('+3 day', $city_media['created_at']) > time()) {
//                return $this->error('该素材还未过期');
//            } else {
//                $filePath = Helper::getMediaPath($city_media['file_name']);
//                if ($model->delete($_GET['id'])) {
//                    @unlink($filePath);
//                    return $this->success('删除成功');
//                } else {
//                    return $this->error('删除失败');
//                }
//            }
//        } else {
//            return $this->error('找不到该记录');
//        }
    }

    public function mediaRenewal()
    {
        $city_media = D('city_media')->find($_GET['id']);
        if (strtotime('+3 day', $city_media['created_at']) < time()) {
            $city_id = $city_media['city_id'];
            $accessToken = getAccessToken($city_id);
            if ($accessToken) {
                $filePath = Helper::getMediaPath($city_media['file_name']);
                if (class_exists('CURLFile')) {
                    $postData = ['media' => new \CURLFile($filePath)];
                } else {
                    $postData = ['media' => '@' . $filePath];
                }
                $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$accessToken['access_token']}&type={$city_media['type']}";
                $result = https_request($url, $postData);
                $data = json_decode($result, true);
                if (!$data) {
                    $error = '没有获取到结果';
                } else if (isset($data['errcode'])) {
                    $error = $data['errmsg'];
                } else {
                    if (D('city_media')->where(['id' => $_GET['id']])->save($data)) {
                        return $this->success('操作成功', U('city/media'));
                    } else {
                        return $this->error('续期成功，但保存失败，请复制以下信息给管理员：' . $result, 'media', 30);
                    }
                }
            } else {
                $error = 'AccessToken获取失败';
            }
        } else {
            $error = '该素材还未过期';
        }
        return $this->error($error);
    }

    /**
     * 微信支付记录
     */
    public function wechat_pay_record()
    {

        $city_id = I('city_id',0,'intval');
        $kw = I('kw','','trim');
        $status = I('status','','trim');
        $date1 = I('date1','','trim');
        $date2 = I('date2','','trim');

        $model = D('wechat_pay_record');
        $where = "1";
        if($city_id){
           // $where['city_id'] = $city_id;
            $where .= " AND city_id='{$city_id}'";
        }
        if($status == 1){
            //$where['result_code'] = 'SUCCESS';
            $where .= " AND result_code='SUCCESS'";
        }
        if($status == 2){
            //$where['result_code'] = 'FAIL';
            $where .= " AND result_code='FAIL'";
        }
        if($kw){
            //$where['openid'] = $kw;
            $where .= " AND openid='{$kw}'";
        }

        if($date1){
            //$where['created_at'] = [ 'egt', strtotime($date1)];
            $where .= " AND created_at>='".strtotime($date1)."'";
        }

        if($date2){
            //$where['created_at'] = [ 'elt', strtotime($date2,"+1 day")];
            $where .= " AND created_at<='".strtotime($date2.".23:59:59 ")."'";
        }

        $count = $model->where($where)->count();
        $Page = new Page($count, 20);

        $show = $Page->show();
        $list = $model->where($where)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $wechat_pay_type_map = Presenter::$wechat_pay_type_map;
        $city_map = cityMap();


        $this->assign('date1', $date1);
        $this->assign('date2', $date2);
        $this->assign('city_id', $city_id);
        $this->assign('status', $status);
        $this->assign('kw', $kw);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('wechat_pay_type_map', $wechat_pay_type_map);
        $this->assign('city_map', $city_map);
        $this->display();
    }

    /**
     * 关闭提现
     */
    public function close_cash(){
        $id = I('id',0,'intval');
        if($id){
            $detail = M('wechat_pay_record')->where(['id'=>$id])->find();
            if($detail['partner_trade_no']){
                $item = M('users_cash_record')->where(['partner_trade_no'=>$detail['partner_trade_no']])->find();
                if($item && empty($item['status'])){
                    M('users_cash_record')->where(['id'=>$item['id']])->save(['status'=>2]);
                }
            }
        }
        echo "<script type='text/javascript'>window.location.href = document.referrer;</script>";
    }
}
