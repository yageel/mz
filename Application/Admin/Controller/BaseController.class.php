<?php
namespace Admin\Controller;
use Think\Controller;
use Weixin\MyWechat;
class BaseController extends Controller {
	public $admin = [];
	public $wechat = null;
    public function _initialize(){
        /**
         * 验证登陆
         */
        if(! session('is_login')){
            return redirect(U('/login/index'));
        }

        $this->admin = session('login_user');
        if(!$this->admin){
            return redirect(U('/login/index'));
        }

		// 切换角色
		if(empty(session('role')) && ACTION_NAME != 'checkrole' && ACTION_NAME != 'logout'){
			return redirect(U('/index/checkrole'));
		}
		// 设置角色
		$this->admin['role'] = session('role');
        $this->assign('admin', $this->admin);
    }

	/**
	 * 调用微信类返回 access_token
	 * @param  int $type 城市ID
	 * @return object 微信公共类的对象
	 */
	protected function initWechat($type)
	{
		if ($this->wechat) {
			return $this->wechat;
		}

		$cityInfo = M('City')->where(['city_id'=>$type])->find();

		if(empty($cityInfo)){
			die('No Found Weixin Option.');
		}
		$options = array(
				'token' => $cityInfo['red_token'], //填写你设定的key
				'encodingaeskey' => $cityInfo['encodingaeskey'], //填写加密用的EncodingAESKey
				'appid' => $cityInfo['appid'], //填写高级调用功能的app id
				'appsecret' => $cityInfo['appsecret'] //填写高级调用功能的密钥
		);


		return $this->wechat = new MyWechat($options);
	}

    /**
     * 前端接口返回信息
     * @return array
     */
    public function ajax_json()
    {
        $json = array(
            'state' => 4,
            'msg' => '',
            'data' => null
        );

        return $json;
    }

    public function index1(){
        $this->display();
    }
    
    /**
     * 导出csv文件
     * @param $head = array ('订单号','应付结算','是否已回款','是否已提批次','备注');
     * @param $data=array();  输出文件内容
     * @param $filename   文件名称
     */
    public function exportCsv($head,$data,$filename){
    	// 输出Excel文件头
    	//header ( 'Content-Type: application/vnd.ms-excel' );
    	header("Content-type:text/csv");
    	header ( "Content-Disposition: attachment;filename=$filename.csv");
    	header ( 'Cache-Control: max-age=0' );
    	// 打开PHP文件句柄，php://output 表示直接输出到浏览器
    	$fp = fopen ( 'php://output', 'a' );
    	//文件的标题头部
    	foreach ( $head as $i => $v ) {
    		// CSV的Excel支持GBK编码，一定要转换，否则乱码
    		$head [$i] = iconv ( 'utf-8', 'gbk', $v );
    	}
    	// 将数据通过fputcsv写到文件句柄
    	fputcsv ( $fp, $head );
    	 
    	//文件的内容
    	$cnt = 0;// 计数器
    	// 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
    	$limit = 1000;
    	foreach ( $data as $rows ) {
    		$cnt ++;
    		if ($limit == $cnt) { // 刷新一下输出buffer，防止由于数据过多造成问题
    			ob_flush ();
    			flush ();
    			$cnt = 0;
    		}
    		// 读取表数据
    		$content = array ();
    		foreach($rows as $keyName=>$value){// 列写入
    			$content [] = iconv ( 'utf-8', 'gbk', $value);
    			//     			$a = @iconv("utf-8","gbk",$res);$b = @iconv("gbk","utf-8",$a);
    		}
    		fputcsv ( $fp, $content );
    
    
    	}
    	fclose($fp);
    	 
    }
}