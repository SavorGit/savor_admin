<?php
/**
 *资源管理控制器
 * 
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;

class ResourceController extends BaseController{
	 
	 /**
	  * 资源列表
	  */
	 public function resourceList(){
		$size   = I('numPerPage',50);//显示每页记录数
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
        if($end_time){
            $end_time = "$end_time 23:59:59";
            $where.=" AND create_time<='$end_time'";
        }
        $rtype = I('get.rtype',0,'intval');
        if($rtype){
            $where.=" AND type='$rtype'";
        }
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


	 public function addResource(){
	     if(IS_POST){
	         $result = $this->handle_resource();
             $this->output($result['message'], $result['url']);
	     }else{
	         $this->get_file_exts();
	         $oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
	         $this->assign('oss_host',$oss_host);
	         $this->display('addresource');
	     }
	 }
	 
	 public function uploadResource(){
	     $code = 10001;
	     $data = array();
	     if(IS_POST){
	         $msg = '';
	         $result = $this->handle_resource();
	         if($result['media_id']){
	             $code = 10000;
	             $data['media_id'] = $result['media_id'];
	             $data['path'] = $result['oss_addr'];
	         }else{
	             $code = 10001;
	             $msg = $result['message'];
	         }
	         $res_data = array('code'=>$code,'data'=>$data,'msg'=>$msg);
	         echo json_encode($res_data);
	         exit;
	     }else{
	         /*
	          * 隐藏域文件规则：
	          * filed 为:media_id时
	          * <img id="media_idimg" src="/Public/admin/assets/img/noimage.png" border="0" />
              * <span id="media_idimgname"></span>
	          */
	         $hidden_filed = I('get.filed','media_id');
	         $rtype = I('get.rtype',0);
	         $autofill = I('get.autofill',0);
	         $where = ' flag=0';
	         if($rtype){
	             $where.=" and type='$rtype'";	             
	         }
	         $orders = 'id desc';
	         $start = 0;
	         $size = 50;
	         $mediaModel = new \Admin\Model\MediaModel();
	         $result = $mediaModel->getList($where,$orders,$start,$size);
	         $this->assign('datalist', $result['list']);
	         $oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
	         if($rtype){
	             $this->get_file_exts($rtype);
	         }else{
	             $this->get_file_exts();
	         }
	         $this->assign('autofill',$autofill);
	         $this->assign('rtype',$rtype);
	         $this->assign('hidden_filed',$hidden_filed);
	         $this->assign('oss_host',$oss_host);
	         $this->display('uploadresource');
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
	             $save['state'] = $flag;
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
	         $this->output($message, $url);
	     }else{
	         $vinfo = $mediaModel->getMediaInfoById($media_id);
	         $this->assign('vinfo',$vinfo);
	         $this->display('editresource');
	     }
	 }
	 
}
