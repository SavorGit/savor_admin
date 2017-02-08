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
				$this->output('操作成功!', 'hotel/room?hotel_id='.$hotel_id,2);
			}else{
				 $this->output('操作失败!', 'hotel/doAddRoom');
			}		
		}else{	
			$save['create_time'] = date('Y-m-d H:i:s');
			if($RoomModel->add($save)){
				$this->output('操作成功!', 'hotel/room?hotel_id='.$hotel_id);
			}else{
				 $this->output('操作失败!', 'hotel/doAddRoom');
			}	
		}		
	}


}
