<?php
/**
 *资源管理控制器
 * 
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;

class AdvertController extends BaseController{
	 private $oss_host = '';
	 public function __construct(){
	     parent::__construct();
	     $this->oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
	 }
	 /**
	  * 资源列表
	  */
	 public function adsList(){	
		$size   = I('numPerPage',8);//显示每页记录数
        $start = I('pageNum',1);
        $order = I('_order','id');
        $sort = I('_sort','desc');
        $name = I('keywords','','trim');
        $beg_time = I('begin_time','');
        $end_time = I('end_time','');
        $orders = $order.' '.$sort;
        $pagenum = ($start-1) * $size>0?($start-1) * $size:0;
        $where = "1=1";
        if($name)   $where.= "	AND name LIKE '%{$name}%'";
        if($beg_time)   $where.=" AND create_time>='$beg_time'";
        if($end_time)   $where.=" AND create_time<='$end_time'";
        $isbrowse = I('isbrowse');
	 	$mediaModel = new \Admin\Model\MediaModel();
        $result = $mediaModel->getList($where,$orders,$pagenum,$size);
        if($isbrowse){
            $res_data = array('code'=>10000,'data'=>$result['list']);
            echo json_encode($res_data);
            exit;
        }else{
            $display_html = 'resourcelist';
            $time_info = array('now_time'=>date('Y-m-d H:i:s'),'begin_time'=>$beg_time,'end_time'=>$end_time);
            $this->assign('timeinfo',$time_info);
            $this->assign('pageNum',$start);
            $this->assign('numPerPage',$size);
            $this->assign('_order',$order);
            $this->assign('_sort',$sort);
       		$this->assign('datalist', $result['list']);
       	    $this->assign('page',  $result['page']);
        	$this->assign('keywords',$name);
    	 	$this->display($display_html);
        }
        
	 }


	 public function addAdvert(){
	     $code = 10001;
	     $data = array();
	     if(IS_POST){
	         $result = $this->add_media();
	         if($result['media_id']){
	             $code = 10000;
	             $data['media_id'] = $result['media_id'];
	             $data['path'] = $result['oss_addr'];
	         }
	         $res_data = array('code'=>$code,'data'=>$data);
	         echo json_encode($res_data);
	         exit;
	     }else{
	         $where = ' flag=0';
	         $orders = 'id desc';
	         $start = 0;
	         $size = 8;
	         $mediaModel = new \Admin\Model\MediaModel();
	         $result = $mediaModel->getList($where,$orders,$start,$size);
	         $this->assign('datalist', $result['list']);
	         $this->assign('oss_host',$this->oss_host);
	         $this->display('addadvert');
	     }
	 }
	 
	 public function editResource(){
         $mediaModel = new \Admin\Model\MediaModel();
         $media_id = I('request.id',0,'intval');
	     if(IS_POST){
	         $save = array();
	         $flag = I('request.flag');
	         if($flag){
	             if($flag==2)  $flag = 0;
	             $save['flag'] = $flag;
	         }else{
	             $name = I('post.name','','trim');
	             $type = I('post.type',3,'intval');
	             $duration = I('post.duration','');
	             $description = I('post.description','');
	             $save['name'] = $name;
	             $save['type'] = $type;
	             if($duration)  $save['duration'] = $duration;
	             if($description)   $save['description'] = $description;
	         }
	         $message = $url = '';
	         if($media_id){
	             if($mediaModel->where('id='.$media_id)->save($save)){
	                 $message = '更新成功!';
	                 $url = 'resource/resourceList';
	             }else{
	                 $message = '更新失败!';
	                 $url = 'resource/resourceList';
	             }
	         }
	         $this->output($message, $url,2);
	     }else{
	         $vinfo = $mediaModel->getMediaInfoById($media_id);
	         $this->assign('vinfo',$vinfo);
	         $this->display('editresource');
	     }
	 }
	 
	 private function add_media(){
	     $mediaModel = new \Admin\Model\MediaModel();
	     $save = array();
	     $type = I('post.type',0,'intval');
	     $duration = I('post.duration','');
	     $description = I('post.description','');
	     $save['name'] = I('post.name','','trim');
	     $save['oss_addr'] = I('post.oss_addr','','trim');
	     if($duration)  $save['duration'] = $duration;
	     if($description)   $save['description'] = $description;
	     $message = $url = $oss_addr = '';
	     $media_id = 0;
	     if(!$save['oss_addr']){
	         $message = 'OSS上传失败!';
	         $url = 'resource/resourceList';
	     }else{
	         $user = session('sysUserInfo');
	         $tempInfo = pathinfo($save['oss_addr']);
	         $surfix = $tempInfo['extension'];
	         $typeinfo = C('RESOURCE_TYPEINFO');
	         if(!$type){
	             if(isset($typeinfo[$surfix])){
	                 $type = $typeinfo[$surfix];
	             }else{
	                 $type = 3;
	             }
	         }
	         $save['surfix'] = $surfix;
	         $save['create_time'] = date('Y-m-d H:i:s');
	         $save['creator'] = $user['username'];
	         $save['type'] = $type;
	         $media_id = $mediaModel->add($save);
	         if($media_id){
	             $message = '添加成功!';
	             $url = 'resource/resourceList';
	         }else{
	             $message = '添加失败!';
	             $url = 'resource/resourceList';
	         }
	         $oss_addr = $this->oss_host.$save['oss_addr'];
	     }
	     $result = array('media_id'=>$media_id,'oss_addr'=>$oss_addr,'message'=>$message,'url'=>$url);
	     return $result;
	 }
	 
}
