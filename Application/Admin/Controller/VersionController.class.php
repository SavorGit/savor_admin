<?php
/**
 * 版本管理 
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
class VersionController extends BaseController{
    private $oss_host = '';
    public function __construct(){
        parent::__construct();
        $this->oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
    }

	public function client(){
	    $this->display('client');
	}
	
	public function addClient(){
	    $versionModel = new \Admin\Model\VersionModel();
	    if(IS_POST){
	        $add_data = array();
	        $add_data['device_type'] = I('post.devicetype');
	        $add_data['version_min'] = I('post.version_min');
	        $add_data['version_max'] = I('post.version_max');
	        $add_data['version'] = I('post.version');
	        $add_data['update_type'] = I('post.update_type');
	        $res_data = $versionModel->add($add_data);
	        if($res_data){
	            $this->output('新增版本成功', 'version/client', 2);
	        }else{
	            $this->output('新增版本失败', 'version/client');
	        }
	    }else{
    	    $filed = 'version_code,version_name,device_type';
    	    $where = array();
    	    $where['device_type'] = array('IN','3,4');
    	    $order = 'id desc';
    	    $datalist = $versionModel->getAllList($filed, $where, $order);
    	    $android = array();
    	    $ios = array();
    	    foreach ($datalist as $k=>$v){
    	        $version_code = $v['version_code'];
    	        if($v['device_type']==3){
    	            $android[$version_code]=$v['version_name'];
    	        }elseif($v['device_type']==4){
    	            $ios[$version_code]=$v['version_name'];
    	        }
    	    }
    	    ksort($android);
    	    $android_min = $android;
    	    krsort($android);
    	    $android_max = $android;
    	    $android_vinfo = array(
    	        'min'=>$android_min,
    	        'max'=>$android_max,
    	    );
    	    ksort($ios);
    	    $ios_min = $ios;
    	    krsort($ios);
    	    $ios_max = $ios;
    	    $ios_vinfo = array(
    	        'min'=>$ios_min,
    	        'max'=>$ios_max,
    	    );
    	    $devicedata = array('3'=>$android_vinfo,'4'=>$ios_vinfo);
    	    $this->assign('devicedata',json_encode($devicedata));
    	    $this->assign('android_vinfo',$android_vinfo);
    	    $this->display('addclient');
	    }
	}
	
	

	public function versionList(){
	    $size   = I('numPerPage',50);//显示每页记录数
	    $start = I('pageNum',1);
	    $order = I('_order','id');
	    $sort = I('_sort','desc');
	    $name = I('keywords','','trim');
	    $device_type = I('device_type','0','intval');
	    $orders = $order.' '.$sort;
	    $pagenum = ($start-1) * $size>0?($start-1) * $size:0;
	    $where = array();
	    if($name)      $where['version_name'] = array('LIKE',"%$name%");
	    if($device_type)   $where['device_type'] = $device_type;
	    $versionModel = new \Admin\Model\VersionModel();
	    $result = $versionModel->getList($where,$orders,$pagenum,$size);
	    $datalist = $result['list'];
	    $all_types = C('DEVICE_TYPE');
	    foreach ($datalist as $k=>$v){
	        $type_str = '';
	        $type = $v['device_type'];
	        if(isset($all_types[$type])){
	            $type_str = $all_types[$type];
	        }
	        $datalist[$k]['device_typestr'] = $type_str;
	        $datalist[$k]['oss_addr'] = $this->oss_host.$v['oss_addr'];
	    }
	    $this->assign('pageNum',$start);
	    $this->assign('numPerPage',$size);
	    $this->assign('_order',$order);
	    $this->assign('_sort',$sort);
	    $this->assign('datalist', $datalist);
	    $this->assign('page',  $result['page']);
	    $this->assign('keywords',$name);
	    $this->assign('device_type',$device_type);
	    $this->display('versionlist');
	}
	
	public function addVersion(){
	    if(IS_POST){
	        $devicetype = I('post.devicetype','0','intval');//终端类型：1小平台，2机顶盒，3手机
	        $clienttype = I('post.clienttype','3','intval');//设备3手机android，4手机iphone
	        $oss_addr = I('post.oss_addr','');
	        $version_name = I('post.version_name','');
	        $version_code = I('post.version_code','');
	        $remark = I('post.remark','');
	        if($devicetype==3) $devicetype = $clienttype;
	        if(empty($oss_addr)){
	            if($devicetype==4){
	                $oss_addr = '';
	            }else{
    	            $this->output('文件不能为空', 'version/versionList');
	            }
	        }
	        $version_data = array('version_name'=>$version_name,'version_code'=>$version_code,'device_type'=>$devicetype);
	        if($remark)    $version_data['remark'] = $remark;
	        if($oss_addr){
	            $version_data['oss_addr'] = $oss_addr;
	            $file_url = $this->oss_host.$oss_addr;
	            $file_info = file_get_contents($file_url);
	            $md5_file = md5_file($file_info);
	            $version_data['md5'] = $md5_file;
	        }
	        $versionModel = new \Admin\Model\VersionModel();
	        $res_version = $versionModel->add($version_data);
	        if($res_version){
	            $this->output('新增版本成功', 'version/versionList');
	        }else{
	            $this->output('新增版本失败', 'version/versionList');
	        }
	    }else{
    	    $this->display('addversion');
	    }
	}
	
	public function delVersion(){
	    $vid = I('get.vid','0','intval');
	    if($vid){
	        $where = "id='$vid'";
	        $versionModel = new \Admin\Model\VersionModel();
	        $versionModel->where($where)->delete();
    	    $this->output('删除成功', 'version/versionList',2);
	    }else{
    	    $this->output('删除失败', 'version/versionList',2);
	    }
	}
}

