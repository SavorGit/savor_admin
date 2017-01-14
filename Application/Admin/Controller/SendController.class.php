<?php
/**
 *@author hongwei
 *资源管理控制器
 *
 * 
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Admin\Controller\MediaModel;

class SendController extends BaseController 
{
	 
	 /**
	  * 资源列表
	  * [addVideo description]
	  */
	 public function video()
	 {
	 	return $this->display('video');

	 }//End Function


	 /**
	  * [addMedia description]
	  */
	 public function addMedia()
	 {	
	 	$id = I('get.id');

	 	if($id)
	 	{	
	 		//todo 如果资源已经存在节目单和各个业务当中,则不能修改

	 		$mediaModel = new MediaModel;
	 		$temp = $mediaModel->getRow('*',['id'=>$id]);
	 		$this->assign('row',$row);
	 	}		



	 	return $this->display('addMedia');
	 }


	 /**
	  * 获取OSS资源上传的配置初始化参数
	  * 
	  * @return [type] [description]
	  */
	 public function getOssParams()
	 {

	 	$id          = C('OSS_ACCESS_ID');
	
		$key         = C('OSS_ACCESS_KEY');
		
		$host        = 'http://'.C('OSS_TEST_BUCKET').'.'.C('OSS_ENDPOINT');
		
		$callbackUrl = C('OSS_SYNC_CALLBACK_URL');

	    $callback_param = array(
	    			 'callbackUrl'=>$callbackUrl, 
	                 'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}', 
	                 'callbackBodyType'=>"application/x-www-form-urlencoded"
	                 );
	   	
		$callback_string      = json_encode($callback_param);
		
		$base64_callback_body = base64_encode($callback_string);
		$now                  = time();
		$expire               = 30; //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问
		$end                  = $now + $expire;
		$expiration           = $this->_gmt_iso8601($end);
		
		//资源空间的目录前缀
		$dir                  = 'user-dir/';
		
		//最大文件大小.用户可以自己设置
		$condition            = array(0=>'content-length-range', 1=>0, 2=>1048576000);
		$conditions[]         = $condition; 
		
		//表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
		$start                = array(0=>'starts-with', 1=>'$key', 2=>$dir);
		$conditions[]         = $start; 


		$arr                   = array('expiration'=>$expiration,'conditions'=>$conditions);
		//echo json_encode($arr);
		//return;
		$policy                = json_encode($arr);
		$base64_policy         = base64_encode($policy);
		$string_to_sign        = $base64_policy;
		$signature             = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));
		
		$response              = array();
		$response['accessid']  = $id;
		$response['host']      = $host;
		$response['policy']    = $base64_policy;
		$response['signature'] = $signature;
		$response['expire']    = $end;
		$response['callback']  = $base64_callback_body;
		//这个参数是设置用户上传指定的前缀
		$response['dir']       = $dir;
		
	    echo json_encode($response);


	 }//End Function






	 /**
	  * [_gmt_iso8601 description]
	  * @return [type] [description]
	  */
	 private function _gmt_iso8601($time)
	 {
	    $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";

	 }//End Function
    

   	


	 /**
	  * OSS异步回调
	  * @return [type] [description]
	  */
	 public function syncCallback()
	 {

	 	// 1.获取OSS的签名header和公钥url header
		$authorizationBase64 = "";
		$pubKeyUrlBase64 = "";
		/*
		 * 注意：如果要使用HTTP_AUTHORIZATION头，你需要先在apache或者nginx中设置rewrite，以apache为例，修改
		 * 配置文件/etc/httpd/conf/httpd.conf(以你的apache安装路径为准)，在DirectoryIndex index.php这行下面增加以下两行
		    RewriteEngine On
		    RewriteRule .* - [env=HTTP_AUTHORIZATION:%{HTTP:Authorization},last]
		 * */
		if(isset($_SERVER['HTTP_AUTHORIZATION']))
		{
		    $authorizationBase64 = $_SERVER['HTTP_AUTHORIZATION'];
		}

		if (isset($_SERVER['HTTP_X_OSS_PUB_KEY_URL']))
		{
		    $pubKeyUrlBase64 = $_SERVER['HTTP_X_OSS_PUB_KEY_URL'];
		}

		if ($authorizationBase64 == '' || $pubKeyUrlBase64 == '')
		{
		    exit();
		}

		// 2.获取OSS的签名
		$authorization = base64_decode($authorizationBase64);

		// 3.获取公钥
		$pubKeyUrl = base64_decode($pubKeyUrlBase64);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $pubKeyUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$pubKey = curl_exec($ch);

		if($pubKey == "")
		{
		    //header("http/1.1 403 Forbidden");
		    exit();
		}

		// 4.获取回调body
		$body = file_get_contents('php://input');

		// 5.拼接待签名字符串
		$authStr = '';
		$path = $_SERVER['REQUEST_URI'];
		$pos = strpos($path, '?');
		if ($pos === false)
		{
		    $authStr = urldecode($path)."\n".$body;
		}
		else
		{
		    $authStr = urldecode(substr($path, 0, $pos)).substr($path, $pos, strlen($path) - $pos)."\n".$body;
		}

		// 6.验证签名
		$ok = openssl_verify($authStr, $authorization, $pubKey, OPENSSL_ALGO_MD5);

		if($ok == 1)
		{
		    header("Content-Type: application/json");
		    $data = array("Status"=>"Ok");
		    echo json_encode($data);
		}
		else
		{
		    //header("http/1.1 403 Forbidden");
		    exit();
		}


	 }//End Function



   	
	 /**
	  * [doAddMedia description]
	  * @return [type] [description]
	  */
	 public function doAddMedia()
	 {	

	 	$id                = I('post.id');
		$save              = [];
		$save['name']  	   = I('post.name','','trim');

		
		$mediaModel = new MediaModel;

		if($id)
		{	
			$save['flag']      = I('post.flag','','intval');
			$save['state']     = I('post.state','','intval');

			if($mediaModel->where('id='.$id)->save($save))
			{
				$this->output('更新成功!', 'send/doAddMedia');
			}
			else
			{
				 $this->output('更新失败!', 'send/doAddMedia');
			}		
		}
		else
		{	
			$user                = session('sysUserInfo');
			$save['create_time'] = date('Y-m-d H:i:s');
			$save['creator']     = $user['username'];
			$save['oss_addr']    = I('post.oss_addr','','trim');

			if(!$save['oss_addr'])
			{
				return $this->output('OSS上传失败!', 'send/doAddMedia');
			}

			if($mediaModel->add($save))
			{
				return $this->output('添加成功!', 'send/doAddMedia');
			}
			else
			{
				return  $this->output('添加失败!', 'send/doAddMedia');
			}	

		}	

	 }//End Function





}//End Class
