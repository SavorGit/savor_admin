<?php
/**
 * 版本管理 
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Common\Lib\Aliyun;
class VersionController extends BaseController{
    private $oss_host = '';
    public function __construct(){
        parent::__construct();
        $this->oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
    }

	public function client(){
	    $size   = I('numPerPage',50);//显示每页记录数
	    $start = I('pageNum',1);
	    $order = I('_order','id');
	    $sort = I('_sort','desc');
	    $orders = $order.' '.$sort;
	    $pagenum = ($start-1) * $size>0?($start-1) * $size:0;
	    $device_type = array(3,4);//终端类型：1小平台，2机顶盒，3,4手机
	    $result = $this->upgradeList($device_type, $orders, $pagenum, $size);
	    $this->assign('pageNum',$start);
	    $this->assign('numPerPage',$size);
	    $this->assign('_order',$order);
	    $this->assign('_sort',$sort);
	    $this->assign('datalist', $result['list']);
	    $this->assign('page',  $result['page']);
	    $this->display('client');
	}

	public function sqlup(){
		$heartModel = new \Admin\Model\DeviceSqlModel();
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','id');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$where = "1=1";
		$name = I('name');
		$type = I('type');
		if($name){
			$this->assign('name',$name);
			$where .= "	AND hotel_name LIKE '%{$name}%'";
		}

		if($type){
			$where .= "	AND device_type= '{$type}' ";
		}
		$result = $heartModel->getList($where,$orders,$start,$size);
		$time = time();
		$ind = $start;
		foreach ($result['list'] as &$val) {
			$val['indnum'] = ++$ind;

		}

		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('sqlup');
	}
	
	public function box(){
	    $size   = I('numPerPage',50);//显示每页记录数
	    $start = I('pageNum',1);
	    $order = I('_order','id');
	    $sort = I('_sort','desc');
	    $orders = $order.' '.$sort;
	    $pagenum = ($start-1) * $size>0?($start-1) * $size:0;
	    $device_type = 2;//终端类型：1小平台，2机顶盒，3,4手机
	    $result = $this->upgradeList($device_type, $orders, $pagenum, $size);
	    $this->assign('pageNum',$start);
	    $this->assign('numPerPage',$size);
	    $this->assign('_order',$order);
	    $this->assign('_sort',$sort);
	    $this->assign('datalist', $result['list']);
	    $this->assign('page',  $result['page']);
	    $this->display('box');
	}

	public function platform(){
	    $size   = I('numPerPage',50);//显示每页记录数
	    $start = I('pageNum',1);
	    $order = I('_order','id');
	    $sort = I('_sort','desc');
	    $orders = $order.' '.$sort;
	    $pagenum = ($start-1) * $size>0?($start-1) * $size:0;
	    $device_type = 1;//终端类型：1小平台，2机顶盒，3,4手机
	    $result = $this->upgradeList($device_type, $orders, $pagenum, $size);
	    $this->assign('pageNum',$start);
	    $this->assign('numPerPage',$size);
	    $this->assign('_order',$order);
	    $this->assign('_sort',$sort);
	    $this->assign('datalist', $result['list']);
	    $this->assign('page',  $result['page']);
	    $this->display('platform');
	}
	
	public function addUpgrade(){
	    $upgradeModel = new \Admin\Model\UpgradeModel();
	    $versionModel = new \Admin\Model\VersionModel();
	    $name = I('name','client');
	    if(IS_POST){
	        $device_type = I('post.devicetype');//终端类型：1小平台，2机顶盒，3,4手机
	        $add_data = array();
	        $add_data['device_type'] = $device_type;
	        $add_data['version_min'] = I('post.version_min');
	        $add_data['version_max'] = I('post.version_max');
	        $add_data['version'] = I('post.version');
	        $add_data['update_type'] = I('post.update_type');
	        $add_data['create_time'] = date('Y-m-d H:i:s');
	        $add_data['state'] = 0;
	        if($device_type==1 || $device_type==2){
	            $upgrade_time_start = I('post.upgrade_time_start',0,'intval');
	            $upgrade_time_end = I('post.upgrade_time_end',0,'intval');
	            if($upgrade_time_start>$upgrade_time_end){
	                $this->output('升级开始时间不能大于结束时间', 'version/addUpgrade', 3);
	            }
	            $area_id = I('post.area_id',0,'intval');
	            $hotel_id = I('post.hotel_id','');
	            if($area_id)   $add_data['area_id'] = $area_id;
	            if($hotel_id)   $add_data['hotel_id'] = $hotel_id;
	        }
	        $res_data = $upgradeModel->add($add_data);
	        if($res_data){
	            $navTab = "version/$name";
	            $this->output('新增升级版成功', $navTab);
	        }else{
	            $this->output('新增升级版本失败', $navTab);
	        }
	    }else{
	        $filed = 'version_code,version_name,device_type';
	        $device_condition = array(
	            'client'=>array('IN','3,4'),
	            'box'=>2,
	            'platform'=>1
	        );
	        $where = array();
	        if(isset($device_condition[$name]))    $where['device_type']=$device_condition[$name];

	        $order = 'id desc';
	        $datalist = $versionModel->getAllList($filed, $where, $order);
	        $android = array();
	        $ios = array();
	        $version = array();
	        foreach ($datalist as $k=>$v){
	            $version_code = $v['version_code'];
	            $version[$v['device_type']][$version_code] = $v['version_name'];
	        }
	        if($name=='client'){
	            $android = $version[3];
	            krsort($android);
	            $android_max = $android;
	            ksort($android);
	            $android_min = $android;
	            $android_vinfo = array(
	                'min'=>$android_min,
	                'max'=>$android_max
	            );
	            $ios = $version[4];
	            ksort($ios);
	            $ios_min = $ios;
	            krsort($ios);
	            $ios_max = $ios;
	            $ios_vinfo = array(
	                'min'=>$ios_min,
	                'max'=>$ios_max
	            );
	            $devicedata = array('3'=>$android_vinfo,'4'=>$ios_vinfo);
	            $devicedata = json_encode($devicedata,true);
	            $this->assign('devicedata',$devicedata);
	            $this->assign('android_vinfo',$android_vinfo);
	        }else{
	            if(isset($device_condition[$name])){
	                $device_type =$device_condition[$name];
	                $version = $version[$device_type];
	                ksort($version);
	                $version_min = $version;
	                krsort($version);
	                $version_max = $version;
	                $version_vinfo = array(
	                    'min'=>$version_min,
	                    'max'=>$version_max,
	                );

	                $this->assign('version_vinfo',$version_vinfo);
	                $areaModel  = new \Admin\Model\AreaModel();
	                $area_arr = $areaModel->getAllArea();
	                $this->assign('area', $area_arr);
	            }
	        }
	        $display_html = "add$name";
	        $this->display($display_html);
	    }
	}
	
	public function operateStatus(){
	    $id = I('get.id',0,'intval');
	    $state = I('get.state',0,'intval');
	    $upgrade_name = I('get.name','client');
	    $upgradeModel = new \Admin\Model\UpgradeModel();
	    $where = "id='$id'";
	    switch ($state){
	        case 0:
	            $data = array('state'=>$state,'update_time'=>date('Y-m-d H:i:s'));
	            $upgradeModel->where($where)->save($data);
	            $message = '已关闭';
	            break;
	        case 1:
	            $data = array('state'=>$state,'update_time'=>date('Y-m-d H:i:s'));
	            $upgradeModel->where($where)->save($data);
	            $message = '已开启';
	            break;
	        case 20:
	            $upgradeModel->where($where)->delete();
	            $message = '已删除';
	            break;
	        default:
	            $message = '操作失败';
	            break;
	    }
	    $navTab = "version/$upgrade_name";
	    $this->output($message, $navTab, 2);
	}

	public function hotelList(){
	    $hnum = I('get.hnum',0,'intval');
	    $id = I('get.id',0,'intval');
	    $datalist = array();
	    if($id){
	        $upgradeModel = new \Admin\Model\UpgradeModel();
	        $res = $upgradeModel->find($id);
            if($res['hotel_id']){
                $hotelModel = new \Admin\Model\HotelModel();
                $res_hotel = $hotelModel->getHotelByIds($res['hotel_id'],'id,name');
                if(!empty($res_hotel)){
                    $datalist = array_chunk($res_hotel, 5);
                }
            }	        
	    }
	    $this->assign('hnum',$hnum);
	    $this->assign('datalist',$datalist);
	    $this->display('hotelist');
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
	        $version_data['create_time'] = date('Y-m-d H:i:s');
	        if($oss_addr){
	            $version_data['oss_addr'] = $oss_addr;
	            $accessKeyId = C('OSS_ACCESS_ID');
	            $accessKeySecret = C('OSS_ACCESS_KEY');
	            $endpoint = C('OSS_HOST');
	            $bucket = C('OSS_BUCKET');
	            $aliyun = new Aliyun($accessKeyId, $accessKeySecret, $endpoint);
	            $aliyun->setBucket($bucket);
	            $fileinfo = $aliyun->getObject($oss_addr,'');
	            if($fileinfo){
	                $version_data['md5'] = md5($fileinfo);
	            }
	        }
	        $versionModel = new \Admin\Model\VersionModel();
	        $res_version = $versionModel->add($version_data);
	        if($res_version){
	            $this->output('新增版本成功', 'version/versionList');
	        }else{
	            $this->output('新增版本失败', 'version/versionList');
	        }
	    }else{
	        $this->assign('oss_host',$this->oss_host);
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
	
	private function upgradeList($device_type,$orders,$pagenum,$size){
	    $where = array();
	    if(is_array($device_type)){
	        $type_str = join(',', $device_type);
	        $where['device_type'] = array('IN',$type_str);
	    }else{
	        $where['device_type'] = $device_type;
	    }
	     
	    $upgradeModel = new \Admin\Model\UpgradeModel();
	    $versionModel = new \Admin\Model\VersionModel();
	    $result = $upgradeModel->getList($where,$orders,$pagenum,$size);
	    $datalist = $result['list'];
	    $all_types = C('DEVICE_TYPE');
	    $all_uptypes = C('UPDATE_TYPE');
	    foreach ($datalist as $k=>$v){
	        $type = $v['device_type'];
	        if(isset($all_types[$type])){
	            $datalist[$k]['device_typestr'] = $all_types[$type];
	        }else{
	            $datalist[$k]['device_typestr'] = '';
	        }
	        $uptype = $v['update_type'];
	        if(isset($all_uptypes[$uptype])){
	            $datalist[$k]['update_typestr'] = $all_uptypes[$uptype];
	        }else{
	            $datalist[$k]['update_typestr'] = '';
	        }
	        $where = array('version_code'=>$v['version'],'device_type'=>$type);
	        $oss_info = $versionModel->where($where)->find();
	        if($oss_info['oss_addr']){
	            $datalist[$k]['oss_addr'] = $this->oss_host.$oss_info['oss_addr'];
	        }else{
	            $datalist[$k]['oss_addr'] = '';
	        }
	        if($device_type==1 || $device_type==2){
	            if ($v['hotel_id']){
	                $hotel_id = trim($v['hotel_id'],',');
	                $hotel_num = count(explode(',',$hotel_id));
	            }else{
	                $hotel_num = 0;
	            }
	            $datalist[$k]['hotel_num'] = $hotel_num;
	        }
	    }
	    $page = $result['page'];
	    $data = array('list'=>$datalist,'page'=>$page);
	    return $data;
	}
}

