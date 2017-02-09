<?php
/**
 *@author hongwei
 *
 *
 * 
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Admin\Model\AdsModel;
use Admin\Model\HotelModel;
use Admin\Model\AreaModel;
use Admin\Model\RoomModel;

class HotelController extends BaseController {
    public function __construct() {
        parent::__construct();
    }

	/*
	 * 添加宣传片media_1,media_2;
	 */

	public function addpub(){
		//acctype=0 添加 ，acctype=1修改
		$acctype = I('get.acctype');

		$hoid = I('get.id');
		$id = 1485;
		$hotelModel = new HotelModel;

		$hoinfo = $hotelModel->where('id='.$id)->find();
		if($acctype == 1) {
			$ads_id = I('get.adsid');
			$adsModel = new AdsModel();
			$vainfo = $adsModel->find($ads_id);
			$this->assign('vainfo', $vainfo);
		}
		$this->assign('vinfo',$hoinfo);

		$this->display('addpub');
	}

	/*
	 * 对宣传片添加或者修改
	 */
	public function doAddPub(){
		$adsModel = new AdsModel();
		$id                  = I('post.ads_id');
		$save                = [];
		$save['description']        = I('post.descri');
		$save['name']    = I('post.adsname');
		//视频封面mediaid
		$media_a   = I('post.media_id');
		$mediaModel = new \Admin\Model\MediaModel();
		$oss_arr = $mediaModel->find($media_a);
		$oss_addr = $oss_arr['oss_addr'];
		$save['duration'] = $oss_arr['oss_addr'];
		$image_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
		$oss_addr = $image_host.$oss_addr;
		$save['img_url'] = $oss_addr;

		//视频资源mediaid
		$save['media_id']    = I('post.media_id');

		$save['hotel_id']    = I('post.ho_id');
		if($id){
			$res_save = $adsModel->where('id='.$id)->save($save);
			if($res_save){
				 $this->output('操作成功!', 'hotel/doAddPub');
			}else{
				 $this->output('操作失败!', 'hotel/doAddPub');
			}
		}else{
			$save['create_time'] = date('Y-m-d H:i:s');
			$save['type'] = 3;
			//刷新页面，关闭当前
			$res_save = $adsModel->add($save);
			if($res_save){
				ob_clean();
				 $this->output('添加宣传片成功!', 'hotel/pubmanager');
			}else{
				 $this->output('操作失败!', 'hotel/doAddPub');
			}
		}
	}


	/*
	 * 宣传片列表
	 */
	public function pubmanager() {
		var_dump($_REQUEST);
		$hoid = I('hotel_id');
		$hotelModel = new HotelModel;
		$hoinfo = $hotelModel->find($hoid);
		var_dump($hoinfo);
		$this->assign('vainfo',$hoinfo);

		$adsModel = new AdsModel();
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

		if($name)
		{
			$this->assign('name',$name);
			$where .= "	AND name LIKE '%{$name}%'";
		}


		$where .= "	AND hotel_id =  $hoid";
		var_dump($where);



		$result = $adsModel->getList($where,$orders,$start,$size);


		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('pubmanager');
	}

	public function delpub(){
		$ads_id = I('get.$ads_id');
		//判断节目单是否含有ads_id
		
	}

    /**
     * 酒店列表
     * 
     */
	public function manager(){	
		$hotelModel = new HotelModel;
		$areaModel  = new AreaModel;
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

        if($name)
        {
        	$this->assign('name',$name);
        	$where .= "	AND name LIKE '%{$name}%'";
        }

        $result = $hotelModel->getList($where,$orders,$start,$size);

        $result['list'] = $areaModel->areaIdToAareName($result['list']);
   		$this->assign('list', $result['list']);
   	    $this->assign('page',  $result['page']);
        $this->display('index');
	}



	/**
	 * 新增酒店
	 * 
	 */
	public function add(){	
		$id = I('get.id');
		$hotelModel = new HotelModel;
		$areaModel  = new AreaModel;
		$area = $areaModel->getAllArea();
		$this->assign('area',$area);
		if($id){
			$vinfo = $hotelModel->where('id='.$id)->find();
			$this->assign('vinfo',$vinfo);
		}
		$this->display('add');
	}



	/**
	 * 保存或者更新酒店信息
	 * 
	 * @return [type] [description]
	 */
	public function doAdd()
	{
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
		
		$hotelModel = new HotelModel;

		if($id)
		{
			if($hotelModel->where('id='.$id)->save($save))
			{
				$this->output('操作成功!', 'hotel/manager');
			}
			else
			{
				 $this->output('操作失败!', 'hotel/add');
			}		
		}
		else
		{	
			
			$save['create_time'] = date('Y-m-d H:i:s');
			if($hotelModel->add($save))
			{
				$this->output('操作成功!', 'hotel/manager');
			}
			else
			{
				 $this->output('操作失败!', 'hotel/add');
			}	

		}		


	}//End Function




	/**
	 * 包间列表
	 *
	 * 
	 * @return [type] [description]
	 */
	public function room()
	{

		$roomModel = new RoomModel;
		$hotelModel = new HotelModel;


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

        if($name)
        {
        	$this->assign('name',$name);
        	$where .= "	AND name LIKE '%{$name}%'";
        }

        $result = $roomModel->getList($where,$orders,$start,$size);

        $result['list'] = $hotelModel->hotelIdToName($result['list']);

  		
   		$this->assign('list', $result['list']);
   	    $this->assign('page',  $result['page']);
        $this->display('room');


	}//End Function





	/**
	 * 新增酒店包间
	 * 
	 */
	public function addRoom()
	{	
		$id = I('get.hotel_id');

		$hotelModel = new HotelModel;
		
		
		$temp = $hotelModel->getRow('name',['id'=>$id]);

		$this->assign('hotel_name',$temp['name']);
		
		$this->assign('hotel_id',$id);

			
		return $this->display('addRoom');

	}



	/**
	 * 新增酒店包间
	 * 
	 */
	public function editRoom()
	{	
		$id = I('get.id');
			
		$roomModel = new RoomModel;
		$hotelModel = new HotelModel;

		if($id)
		{
			$vinfo = $roomModel->where('id='.$id)->find();

			$temp = $hotelModel->getRow('name',['id'=>$vinfo['hotel_id']]);
			
			$vinfo['hotel_name'] = $temp['name'];

			$this->assign('vinfo',$vinfo);

		}
			
		return $this->display('addRoom');

	}





	/**
	 * 保存或者更新酒店信息
	 * 
	 * @return [type] [description]
	 */
	public function doAddRoom()
	{
		$id                  = I('post.id');
		$save                = [];
		$save['hotel_id']    = I('post.hotel_id','','intval');
		$save['name']        = I('post.name','','trim');
		$save['type']        = I('post.type','','intval');
		$save['flag']        = I('post.flag','','intval');
		$save['state']       = I('post.state','','intval');
		$save['remark']      = I('post.remark','','trim');
		$save['update_time'] = date('Y-m-d H:i:s');

		
		$RoomModel = new RoomModel;

		if($id)
		{
			if($RoomModel->where('id='.$id)->save($save))
			{
				$this->output('操作成功!', 'hotel/addRoom');
			}
			else
			{
				 $this->output('操作失败!', 'hotel/doAddRoom');
			}		
		}
		else
		{	
			
			$save['create_time'] = date('Y-m-d H:i:s');

			if($RoomModel->add($save))
			{
				$this->output('操作成功!', 'hotel/addRoom');
			}
			else
			{
				 $this->output('操作失败!', 'hotel/doAddRoom');
			}	

		}		


	}//End Function





}//End Class
