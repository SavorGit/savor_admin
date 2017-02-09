<?php
/**
 *@author hongwei
 *
 *
 * 
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
class HotelController extends BaseController {
    public function __construct() {
        parent::__construct();
    }

    /**
     * 酒店列表
     * 
     */
	public function manager(){	
		$hotelModel = new \Admin\Model\HotelModel();
		$areaModel  = new \Admin\Model\AreaModel();
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
        if($name){
        	$this->assign('name',$name);
        	$where .= "	AND name LIKE '%{$name}%'";
        }
        $result = $hotelModel->getList($where,$orders,$start,$size);
        $datalist = $areaModel->areaIdToAareName($result['list']);
        foreach ($datalist as $k=>$v){
            $nums = $hotelModel->getStatisticalNumByHotelId($v['id']);
            $datalist[$k]['room_num'] = $nums['room_num'];
            $datalist[$k]['box_num'] = $nums['box_num'];
            $datalist[$k]['tv_num'] = $nums['tv_num'];
        }
   		$this->assign('list', $datalist);
   	    $this->assign('page',  $result['page']);
        $this->display('index');
	}


	/**
	 * 新增酒店
	 * 
	 */
	public function add(){	
		$id = I('get.id');
		$hotelModel = new \Admin\Model\HotelModel();
		$areaModel  = new \Admin\Model\AreaModel();
		$area = $areaModel->getAllArea();
		$this->assign('area',$area);
		if($id){
			$vinfo = $hotelModel->where('id='.$id)->find();
			if(!empty($vinfo['media_id'])){
			    $mediaModel = new \Admin\Model\MediaModel();
			    $media_info = $mediaModel->getMediaInfoById($vinfo['media_id']);
			    $vinfo['oss_addr'] = $media_info['oss_addr'];
			}
			$this->assign('vinfo',$vinfo);
		}
		$this->display('add');
	}


	/**
	 * 保存或者更新酒店信息
	 */
	public function doAdd(){
		$id                          = I('post.id');
		$save                        = [];
		$save['name']                = I('post.name','','trim');
		$save['addr']                = I('post.addr','','trim');
		$save['contractor']          = I('post.contractor','','trim');
		$save['maintainer']          = I('post.maintainer','','trim');
		$save['tel']                 = I('post.tel','','trim');
		$save['level']               = I('post.level','','trim');
		$save['iskey']               = I('post.iskey','','intval');
		$save['install_date']        = I('post.install_date','','trim');
		$save['level']               = I('post.level','','trim');
		$save['state']               = I('post.state','','intval');
		$save['state_change_reason'] = I('post.state_change_reason','','trim');
		$save['remark']              = I('post.remark','','trim');
		$save['flag']                = I('post.flag','','intval');
		$save['update_time']         = date('Y-m-d H:i:s');
		$save['mobile']              = I('post.mobile','','trim');
		$save['gps']				 = I('post.gps','','trim');
		$save['area_id']             = I('post.area_id','','intval');
		$save['media_id']             = I('post.media_id','0','intval');
		$hotelModel = new \Admin\Model\HotelModel();
		if($id){
			if($hotelModel->where('id='.$id)->save($save)){
			    $this->output('操作成功!', 'hotel/manager');
			}else{
			    $this->output('操作失败!', 'hotel/add');
			}		
		}else{	
			$save['create_time'] = date('Y-m-d H:i:s');
			if($hotelModel->add($save)){
				$this->output('操作成功!', 'hotel/manager');
			}else{
				 $this->output('操作失败!', 'hotel/add');
			}	
		}		

	}




	/**
	 * 包间列表
	 */
	public function room(){
		$roomModel = new \Admin\Model\RoomModel();
		$hotelModel = new \Admin\Model\HotelModel();
		$hotel_id = I('hotel_id',0);
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

        if($name){
        	$this->assign('name',$name);
        	$where .= "	AND name LIKE '%{$name}%'";
        }
        if($hotel_id){
            $where.=" AND hotel_id='$hotel_id'";
        }
        $result = $roomModel->getList($where,$orders,$start,$size);
        $result['list'] = $hotelModel->hotelIdToName($result['list']);
        $this->assign('hotel_id',$hotel_id);
   		$this->assign('list', $result['list']);
   	    $this->assign('page',  $result['page']);
        $this->display('room');

	}

	/**
	 * 新增酒店包间
	 * 
	 */
	public function addRoom(){	
		$id = I('get.hotel_id');
		$hotelModel = new \Admin\Model\HotelModel();
		$temp = $hotelModel->getRow('name',['id'=>$id]);
		$this->assign('hotel_name',$temp['name']);
		$this->assign('hotel_id',$id);
		$this->display('addRoom');
	}

	/**
	 * 新增酒店包间
	 * 
	 */
	public function editRoom(){	
		$id = I('get.id');
		$roomModel = new \Admin\Model\RoomModel();
		$hotelModel = new \Admin\Model\HotelModel();
		if($id){
			$vinfo = $roomModel->where('id='.$id)->find();
			$temp = $hotelModel->getRow('name',['id'=>$vinfo['hotel_id']]);
			$this->assign('hotel_name',$temp['name']);
			$this->assign('hotel_id',$vinfo['hotel_id']);
			$this->assign('vinfo',$vinfo);
		}
		$this->display('addRoom');
	}

	/**
	 * 保存或者更新酒店信息
	 */
	public function doAddRoom(){
		$id                  = I('post.id');
		$save                = [];
		$hotel_id    = I('post.hotel_id','','intval');
		$save['hotel_id'] = $hotel_id;
		$save['name']        = I('post.name','','trim');
		$save['type']        = I('post.type','','intval');
		$save['flag']        = I('post.flag','','intval');
		$save['state']       = I('post.state','','intval');
		$save['remark']      = I('post.remark','','trim');
		$save['update_time'] = date('Y-m-d H:i:s');

		$RoomModel = new \Admin\Model\RoomModel();
		if($id){
			if($RoomModel->where('id='.$id)->save($save)){
				$this->output('操作成功!', 'hotel/room');
			}else{
				 $this->output('操作失败!', 'hotel/doAddRoom');
			}		
		}else{	
			$save['create_time'] = date('Y-m-d H:i:s');
			if($RoomModel->add($save)){
				$this->output('操作成功!', 'hotel/room');
			}else{
				 $this->output('操作失败!', 'hotel/doAddRoom');
			}	
		}		
	}

	/*
	 * 宣传片列表
	 */
	public function pubmanager() {
	    $hotel_id= I('hotel_id');
	    $size   = I('numPerPage',50);//显示每页记录数
	    $name = I('keywords','','trim');
	    $beg_time = I('begin_time','');
	    $end_time = I('end_time','');
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
	    if($hotel_id)   $where .= "	AND hotel_id =  $hotel_id";
	    if($name)   $where.= "	AND name LIKE '%{$name}%'";
	    if($beg_time)   $where.=" AND create_time>='$beg_time'";
	    if($end_time)   $where.=" AND create_time<='$end_time'";
	
	    $hotelModel = new \Admin\Model\HotelModel();
	    $hotelinfo = $hotelModel->find($hotel_id);
	    $adsModel = new \Admin\Model\AdsModel();
	    $result = $adsModel->getList($where,$orders,$start,$size);
	    $datalist = $result['list'];
	    $mediaModel = new \Admin\Model\MediaModel();
	    foreach ($datalist as $k=>$v){
	        $media_id = $v['media_id'];
	        if($media_id){
	            $mediainfo = $mediaModel->getMediaInfoById($media_id);
	            $oss_addr = $mediainfo['oss_addr'];
	        }else{
	            $oss_addr = '';
	        }
	        $datalist[$k]['oss_addr'] = $oss_addr;
	    }
	
	    $time_info = array('now_time'=>date('Y-m-d H:i:s'),'begin_time'=>$beg_time,'end_time'=>$end_time);
	    $this->assign('timeinfo',$time_info);
	    $this->assign('keywords',$name);
	    $this->assign('hotelinfo',$hotelinfo);
	    $this->assign('list', $datalist);
	    $this->assign('page',  $result['page']);
	    $this->display('pubmanager');
	}
	
	
	/*
	 * 添加宣传片
	 */
	public function addpub(){
	    $hoid = I('get.hotel_id',0,'intval');
	    $ads_id = I('get.ads_id',0,'intval');
	    if($hoid){
	        $hotelModel = new \Admin\Model\HotelModel();
	        $hoinfo = $hotelModel->where('id='.$hoid)->find();
	        $this->assign('vinfo',$hoinfo);
	    }
	    if($ads_id){
	        $oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
	        $adsModel = new \Admin\Model\AdsModel();
	        $vainfo = $adsModel->find($ads_id);
	        $vainfo['oss_addr'] = $oss_host.$vainfo['img_url'];
	        if($vainfo['media_id']){
	            $mediaModel = new \Admin\Model\MediaModel();
	            $mediainfo = $mediaModel->getMediaInfoById($vainfo['media_id']);
	            $vainfo['videooss_addr'] = $mediainfo['oss_addr'];
	        }
	        $this->assign('vainfo',$vainfo);
	    }
	    $this->display('addpub');
	}
	
	/*
	 * 对宣传片添加或者修改
	 */
	public function doAddPub(){
	    $adsModel = new \Admin\Model\AdsModel();
	    $mediaModel = new \Admin\Model\MediaModel();
	    $ads_id = I('post.ads_id');
	    $covermedia_id = I('post.covervideo_id','0','intval');//视频封面id
	    $media_id = I('post.media_id','0','intval');//视频id
	
	    $save = [];
	    $save['description'] = I('post.descri');
	    $save['name'] = I('post.adsname');
	    if($covermedia_id){
	        $oss_arr = $mediaModel->find($covermedia_id);
	        $oss_addr = $oss_arr['oss_addr'];
	        $save['img_url'] = $oss_addr;
	    }
	    if($media_id){
	        $oss_arr = $mediaModel->find($media_id);
	        $save['duration'] = $oss_arr['duration'];
	        $save['media_id']    = $media_id;
	    }
	    $save['hotel_id'] = I('post.hotel_id');
	    if($ads_id){
	        $res_save = $adsModel->where('id='.$ads_id)->save($save);
	        if($res_save){
	            $this->output('操作成功!', 'hotel/pubmanager');
	        }else{
	            $this->output('操作失败!', 'hotel/doAddPub');
	        }
	    }else{
	        $save['create_time'] = date('Y-m-d H:i:s');
	        $save['type'] = 3;
	        //刷新页面，关闭当前
	        $res_save = $adsModel->add($save);
	        if($res_save){
	            $this->output('添加宣传片成功!', 'hotel/pubmanager');
	        }else{
	            $this->output('操作失败!', 'hotel/doAddPub');
	        }
	    }
	}
	
	public function delpub(){
	    $ads_id = I('get.ads_id');
	    $this->output('白玉涛开发', 'hotel/pubmanager');
	}

}
