<?php
/**
 *资源管理控制器
 * 
 */
namespace Admin\Controller;
class AdvertController extends BaseController{

	 private $oss_host = '';
	 public function __construct(){
	     parent::__construct();
	     $this->oss_host = get_oss_host();
	 }

	 /**
	  * 资源列表
	  */
	 public function adsList(){	
		$size   = I('numPerPage',50);//显示每页记录数
        $start = I('pageNum',1);
        $order = I('_order','id');
        $sort = I('_sort','desc');
        $name = I('keywords','','trim');
        $beg_time = I('begin_time','');
        $end_time = I('end_time','');
        $adstype = I('adstype','0','intval');
        $orders = $order.' '.$sort;
        $pagenum = ($start-1) * $size>0?($start-1) * $size:0;
        $where = array();
        $where['flag'] = array('EQ',"0");
        if($name)       $where['name'] = array('LIKE',"%$name%");
        if($beg_time)   $where['create_time'] = array('EGT',"$beg_time");
        if($end_time)   $where['create_time'] = array('ELT',"$end_time");
        if($adstype){
            $where['type'] = $adstype;
        }else{
            $where['type'] = array('IN',"1,2");
        }
	 	$adsModel = new \Admin\Model\AdsModel();
	 	$mediaModel = new \Admin\Model\MediaModel();
        $result = $adsModel->getList($where,$orders,$pagenum,$size);
        $datalist = $result['list'];
        $all_adstypes = C('ADS_TYPE');
        foreach ($datalist as $k=>$v){
            $oss_addr = '';
            $type_str = '';
            $type = $v['type'];
            if(isset($all_adstypes[$type])){
                $type_str = $all_adstypes[$type];
            }
            if($v['media_id']){
                $mediainfo = $mediaModel->getMediaInfoById($v['media_id']);
                $oss_addr = $mediainfo['oss_addr'];
            }
            if($v['resource_type']==1){
                $resource_typestr = '视频';
            }else{
                $resource_typestr = '图片';
            }
            $datalist[$k]['resource_typestr'] = $resource_typestr;
            $datalist[$k]['type_str'] = $type_str;
            $datalist[$k]['oss_addr'] = $oss_addr;
            $datalist[$k]['cover_img_url'] = !empty($v['img_url']) ? $this->oss_host.$v['img_url'] : '';
        }
        $time_info = array('now_time'=>date('Y-m-d H:i:s'),'begin_time'=>$beg_time,'end_time'=>$end_time);
        $this->assign('timeinfo',$time_info);
        $this->assign('pageNum',$start);
        $this->assign('numPerPage',$size);
        $this->assign('_order',$order);
        $this->assign('_sort',$sort);
   		$this->assign('datalist', $datalist);
   	    $this->assign('page',  $result['page']);
    	$this->assign('keywords',$name);
    	$this->assign('adstype',$adstype);
        $this->display('advertlist');
	 }

	 public function addAdvert(){
	     $resource_type = I('resource_type',1,'intval');
	     if(IS_POST){
	         $adsModel = new \Admin\Model\AdsModel();
			 $minu = I('post.minu','0','intval');
			 $seco = I('post.seco','0','intval');
			 $is_sapp_qrcode = I('post.is_sapp_qrcode','0','intval'); //小程序二维码是否显示
			 if(empty($minu) && empty($seco)){
			     $this->error('请输入有效的时长');
			 }
	         $duration = $minu*60+$seco;
	         $adstype = I('post.type',0,'intval');

	         $name = I('post.name','','trim');
	         $description = I('post.description','');
	         $cover_img_media_id = I('post.cover_img_media_id',0,'intval');
	         
	         if($resource_type==1){
                 $result_media = $this->handle_resource();
                 if(!$result_media['media_id']){
                     $this->error($result_media['message'], 'advert/adsList');
                 }
                 $media_id = $result_media['media_id'];
             }else{
                 $media_id =$cover_img_media_id;
             }

             $ads_data = array();
             $ads_data['name'] = $name;
             $ads_data['media_id'] = $media_id;
             $ads_data['type'] = $adstype;
             $ads_data['create_time'] = date('Y-m-d H:i:s');
			 $ads_data['is_online'] = 2;
			 $ads_data['is_sapp_qrcode'] = $is_sapp_qrcode;
			 $ads_data['resource_type'] = $resource_type;
			 if($duration)  $ads_data['duration'] = $duration;
             if($description)   $ads_data['description'] = $description;
             if($cover_img_media_id){
                 $m_media = new \Admin\Model\MediaModel();
                 $media_info = $m_media->field('oss_addr')->where('id='.$cover_img_media_id)->find();
                 $ads_data['img_url'] = $media_info['oss_addr'];
             }
             
             $user = session('sysUserInfo');
             $ads_data['creator_name'] = $user['username'];
             $nass = $adsModel->where(array('name'=>$name))->field('name')->find();
             if(empty($nass['name'])){
                 $adsModel->add($ads_data);
                 $message = '添加成功!';
                 $url = 'advert/adsList';
             }else{
                 $message = '文件名已存在，请换一个名称';
                 $url = 'advert/adsList';
             }
             $this->output($message, $url);
	     }else{
	         $this->get_file_exts($resource_type);
	         $oss_host = $this->oss_host;
			 $vinfo['type'] = 1;
			 $vinfo['duration'] = 0;
			 $this->assign('vinfo',$vinfo);
	         $this->assign('oss_host',$oss_host);
	         $this->assign('resource_type',$resource_type);
	         $this->assign('action_url','advert/addAdvert');
	         if($resource_type==1){
                 $this->display('addadvert');
             }else{
                 $this->display('addimgadvert');
             }
	     }
	 }
	 
	 public function editAds(){
	     $adsid = I('adsid','0','intval');
	     $mediaModel = new \Admin\Model\MediaModel();
	     $adsModel = new \Admin\Model\AdsModel();
	     if(IS_POST){
			 $minu = I('post.minu','0','intval');
			 $seco = I('post.seco','0','intval');
			 $is_sapp_qrcode = I('post.is_sapp_qrcode','0','intval');
             $resource_type = I('post.resource_type',1,'intval');
			 $duration = $minu*60+$seco;
	         $adstype = I('post.type',0,'intval');
	         $name = I('post.name','','trim');
	         $description = I('post.description','');
	         $cover_img_media_id = I('post.cover_img_media_id',0,'intval');
	         $media_id = I('post.media_id','0','intval');
	         $ads_data = array();
	         $ads_data['name'] = $name;
			 $ads_data['duration'] = $duration;
	         $ads_data['type'] = $adstype;
	         $ads_data['is_sapp_qrcode'] = $is_sapp_qrcode;
	         $ads_data['resource_type'] = $resource_type;
	         $ads_data['update_time'] = date('Y-m-d H:i:s');
	         if($description)  $ads_data['description'] = $description;
	         if($cover_img_media_id){
                 $m_media = new \Admin\Model\MediaModel();
                 $media_info = $m_media->field('oss_addr')->where('id='.$cover_img_media_id)->find();
                 $ads_data['img_url'] = $media_info['oss_addr'];
             }
	         
	         if(empty($media_id)){
	             $nass = $adsModel->where(array('name'=>$name))->field('name')->find();
	         }else {
	             $dts = array();
	             $dts['name'] = $name;
	             $dts['media_id'] = array('neq',$media_id);
	             $nass = $adsModel->where($dts)->field('name')->find();
	         }
			 
			 if($nass){
				 $message = '文件名已存在，请换一个名称';
				 $this->error($message);
			 }
	         $res_ads = $adsModel->where("id='$adsid'")->save($ads_data);
	         if($res_ads){
	             
	             if($media_id){
	                 $media_data = array();
	                 $media_data['name'] = $name;
	                 if($duration)  $media_data['duration'] = $duration;
	                 if($description)   $media_data['description'] = $description;
	                 $mediaModel->where("id='$media_id'")->save($media_data);
	             }
	             $this->output('更新成功', 'advert/adsList');
	         }else{
	             $this->output('更新失败', 'advert/adsList');
	         }
	     }else{
	         $vinfo = $adsModel->find($adsid);
	         $oss_addr = '';
             $resource_type = 1;
	         if($vinfo['media_id']){
	             $media_info = $mediaModel->getMediaInfoById($vinfo['media_id']);
	             $vinfo['oss_addr'] = $media_info['oss_addr'];
                 $resource_type = $vinfo['resource_type'];
	         }
             $this->assign('resource_type',$resource_type);
	         $this->assign('is_editads',1);
	         $this->assign('vinfo',$vinfo);
	         $this->assign('oss_host','http://'.C('OSS_HOST_NEW').'/');
	         $this->assign('action_url','advert/editAds');
             if($resource_type==1){
                 $this->display('addadvert');
             }else{
                 $this->display('addimgadvert');
             }

	     }
	 }
	 
	 public function operateStatus(){
	     $adsid = I('request.adsid','0','intval');
	     $atype = I('request.atype');//1状态 2操作
	     $adsModel = new \Admin\Model\AdsModel();
		 $mItemModel = new \Admin\Model\MenuItemModel();
		 $menuliModel = new \Admin\Model\MenuListModel();
	     $message = '';
	     switch ($atype){
	         case 1:
	             $flag = I('request.flag');
				 if($flag == 0) {
					 //判断是否引用
					 //遍历节目单

					 $menu_arr = $menuliModel->field('id,menu_name')->select();
					 $ret = 0;
					 $dat['ads_id'] = $adsid;
					 foreach ($menu_arr as $k=>$v) {
						 $dat['menu_id'] = $v['id'];
						 $rec = $mItemModel->where($dat)->find();
						if( count($rec)>0 ){
							$ret = 1;
							$menu_name = $v['menu_name'];
							break;
						}
					 }
					 if($ret == 1){
						 $this->output('广告存在节目列表'.$menu_name.'中', 'advert/adsList',2,0);
						 die;
					 }
				 }
				 $data = array('state'=>$flag);
	             $res = $adsModel->where("id='$adsid'")->save($data);
				 if($res){
	                 $message = '更新状态成功';
	             }
	             break;
	         case 2:
	             $data = array('flag'=>1);
	             $res = $adsModel->where("id='$adsid'")->save($data);
	             if($res){
	                 $message = '已删除';
	             }
	             break;
	         default:
	             $message = '';
	             break;
	     }
	     if($message){
	         $this->output($message, 'advert/adsList',2);
	     }else{
	         $this->output('操作失败了啊', 'advert/adsList',2,0);
	     }
	 }
	 
}
