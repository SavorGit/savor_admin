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

		$menliModel  = new \Admin\Model\MenuListModel();
		$menuHoModel = new \Admin\Model\MenuHotelModel();
		$menlistModel = new \Admin\Model\MenuListModel();
		$hotelModel = new \Admin\Model\HotelModel();
		$areaModel  = new \Admin\Model\AreaModel();
		//城市
		$area_arr = $areaModel->getAllArea();

		$this->assign('area', $area_arr);
		//包含酒楼
		$men_arr = $menliModel->select();
		$this->assign('include', $men_arr);
		/*//合作维护人
		$per_arr = $hotelModel->distinct(true)->field('area_id')->select();
		$per_ho_arr = $areaModel->areaIdToAareName($per_arr);
		$this->assign('per_ho', $per_ho_arr);*/
		$ajaxversion   = I('ajaxversion',0,'intval');//1 版本升级酒店列表
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
		$beg_time = I('starttime','');
		$end_time = I('endtime','');
		if($beg_time)   $where.=" AND install_date>='$beg_time'";
		if($end_time)   $where.=" AND install_date<='$end_time'";
		if($name){
			$this->assign('name',$name);
			$where .= "	AND name LIKE '%{$name}%'";
		}
		//城市
		$area_v = I('area_v');
		if ($area_v) {
			$this->assign('area_k',$area_v);
			$where .= "	AND area_id = $area_v";
		}
		//级别
		$level_v = I('level_v');
		if ($level_v) {
			$this->assign('level_k',$level_v);
			$where .= "	AND level = $level_v";
		}
		//状态
		$state_v = I('state_v');
		if ($state_v) {
			$this->assign('state_k',$state_v);
			$where .= "	AND state = $state_v";
		}

		//重点
		$key_v = I('key_v');
		if ($key_v) {
			$this->assign('key_k',$key_v);
			$where .= "	AND iskey = $key_v";
		}
		//合作维护人
		$main_v = I('main_v');
		if ($main_v) {
			$this->assign('main_k',$main_v);
			$where .= "	AND maintainer LIKE '%{$main_v}%'";
		}
		//包含
		$include_v = I('include_v');
		//获取节目单对应hotelid
		if ($include_v) {
			//取部分包含节目单
			$bak_ho_arr = array();
			foreach ($include_v as $iv) {
				$sql = "SELECT hotel_id FROM savor_menu_hotel WHERE create_time=
                (SELECT MAX(create_time) FROM savor_menu_hotel WHERE menu_id={$iv})";
				$bak_hotel_id_arr = $menuHoModel->query($sql);
				foreach ($bak_hotel_id_arr as $bk=>$bv){
					$bak_ho_arr[] = $bv['hotel_id'];
				}
			}
			$bak_ho_arr = array_unique($bak_ho_arr);
			$bak_ho_str = implode(',', $bak_ho_arr);
			if($bak_ho_str){
				$where .= "	AND id  in ($bak_ho_str)";
			}else{
				$where .= "	AND id  in ('')";
			}
			$this->assign('include_k',$include_v);
		} else {
			$exc_v = I('exc_v');
			if ($exc_v) {
				$bak_ho_arr_p = array();
				foreach ($exc_v as $iv) {
					$sql = "SELECT hotel_id FROM savor_menu_hotel WHERE create_time=
                (SELECT MAX(create_time) FROM savor_menu_hotel WHERE menu_id={$iv})";
					$bak_hotel_id_arr = $menuHoModel->query($sql);
					foreach ($bak_hotel_id_arr as $bk=>$bv){
						$bak_ho_arr_p[] = $bv['hotel_id'];
					}
				}
				$bak_ho_arr_p = array_unique($bak_ho_arr_p);
				$bak_ho_str = implode(',', $bak_ho_arr_p);
				if($bak_ho_str){
					$where .= "	AND id not in ($bak_ho_str)";
				}
			}
		}
		if($ajaxversion){
		    $start = 0;
		    $size = 1000;
		    $result = $hotelModel->getList($where,$orders,$start,$size);
		    $res_hotel = array();
		    foreach ($result['list'] as $v){
		        $res_hotel[] = array('hotel_id'=>$v['id'],'hotel_name'=>$v['name']);
		    }
		    echo json_encode($res_hotel);
		    exit;
		}else{
		    $result = $hotelModel->getList($where,$orders,$start,$size);
		}
		$datalist = $areaModel->areaIdToAareName($result['list']);
		foreach ($datalist as $k=>$v){
			$conditon = array();
			$men_arr = array();
			$nums = $hotelModel->getStatisticalNumByHotelId($v['id']);
			$datalist[$k]['room_num'] = $nums['room_num'];
			$datalist[$k]['box_num'] = $nums['box_num'];
			$datalist[$k]['tv_num'] = $nums['tv_num'];
			$hotel_id = $datalist[$k]['id'];
			$condition['hotel_id'] = $hotel_id;
			$arr = $menuHoModel->where($condition)->order('id desc')->find();
			$menuid = $arr['menu_id'];
			if($menuid){
				$men_arr = $menlistModel->find($menuid);
				$menuname = $men_arr['menu_name'];
				$datalist[$k]['menu_id'] = $menuid;
				$datalist[$k]['menu_name'] = $menuname;

			}else{
				$datalist[$k]['menu_id'] = '';
				$datalist[$k]['menu_name'] = '无';
			}

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
			$res_hotelext = $hotelModel->getMacaddrByHotelId($id);
			$vinfo['mac_addr'] = $res_hotelext['mac_addr'];
			$vinfo['ip_local'] = $res_hotelext['ip_local'];
			$vinfo['ip'] = $res_hotelext['ip'];
			$vinfo['server_location'] = $res_hotelext['server_location'];
			$this->assign('vinfo',$vinfo);
		}else{
			$vinfo['state'] = 2;
			$this->assign('vinfo',$vinfo);
		}
		$this->display('add');
	}


	/**
	 * 保存或者更新酒店信息
	 */
	public function doAdd(){
		$hotel_id                    = I('post.id');
		$save                        = [];
		$save['name']                = I('post.name','','trim');
		$save['addr']                = I('post.addr','','trim');
		$save['contractor']          = I('post.contractor','','trim');
		$save['maintainer']          = I('post.maintainer','','trim');
		$save['tech_maintainer']          = I('post.techmaintainer','','trim');
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
		if($hotel_id){
		    $res = $hotelModel->where('id='.$hotel_id)->save($save);
		}else{
			$save['create_time'] = date('Y-m-d H:i:s');
			$hotel_id = $hotelModel->add($save);
		}
		if($hotel_id){
		    $mac_addr = I('post.mac_addr','','trim');
			$ip_local = I('post.ip_local','','trim');
			$ip = I('post.ip','','trim');
			$server_location = I('post.server_location','','trim');
		    $res_hotelext = $hotelModel->getMacaddrByHotelId($hotel_id);
		    $model = M('hotel_ext');
		    $data['mac_addr'] = $mac_addr;
			$data['ip_local'] = $ip_local;
			$data['ip'] = $ip;
			$data['server_location'] = $server_location;
		    if(empty($res_hotelext)){
		        $data['hotel_id'] = $hotel_id;
		        $model->add($data);
		    }else{
		        if($mac_addr!=$res_hotelext['mac_addr'] || $ip_local!=$res_hotelext['ip_local'] || $ip!=$res_hotelext['ip'] || $server_location!=$res_hotelext['server_location'] ){
		            $model->where('id='.$res_hotelext['id'])->save($data);
		        }
		    }
		    $this->output('操作成功!', 'hotel/manager');
		}else{
		    $this->output('操作失败!', 'hotel/add');
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
		if(!empty($result['list'])){
		    $boxModel = new \Admin\Model\BoxModel();
		    foreach ($result['list'] as $k=>$v){
		        $room_id = $v['id'];
		        $result['list'][$k]['box_num'] = $boxModel->where("room_id='$room_id'")->count();
		    }
		    $result['list'] = $hotelModel->hotelIdToName($result['list']);
		}
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
		$vinfo['state'] = 2;
		$this->assign('vinfo',$vinfo);
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
		$save['flag']        = 0;

		$RoomModel = new \Admin\Model\RoomModel();
		if($id){
			if($RoomModel->where('id='.$id)->save($save)){
				$this->output('操作成功!', 'hotel/room');
			}else{
				$this->output('操作失败!', 'hotel/doAddRoom');
			}
		}else{
			$save['create_time'] = date('Y-m-d H:i:s');
			$save['flag']        = 0;
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
		$oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
		foreach ($datalist as $k=>$v){
			$media_id = $v['media_id'];
			if($media_id){
				$mediainfo = $mediaModel->getMediaInfoById($media_id);
				$oss_addr = $mediainfo['oss_addr'];
			}else{
				$oss_addr = '';
			}
			$datalist[$k]['oss_addr'] = $oss_addr;
			$datalist[$k]['img_url'] = $oss_host.$datalist[$k]['img_url'];
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
	 * 批量新增牌位
	 */
	public function batchposition() {
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
		$oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
		foreach ($datalist as $k=>$v){
			$media_id = $v['media_id'];
			if($media_id){
				$mediainfo = $mediaModel->getMediaInfoById($media_id);
				$oss_addr = $mediainfo['oss_addr'];
			}else{
				$oss_addr = '';
			}
			$datalist[$k]['oss_addr'] = $oss_addr;
			$datalist[$k]['img_url'] = $oss_host.$datalist[$k]['img_url'];
		}

		$time_info = array('now_time'=>date('Y-m-d H:i:s'),'begin_time'=>$beg_time,'end_time'=>$end_time);
		$this->assign('timeinfo',$time_info);
		$this->assign('keywords',$name);
		$this->assign('hotelinfo',$hotelinfo);
		$this->assign('list', $datalist);
		$this->assign('page',  $result['page']);
		$this->display('batchposition');
	}

	/*
	 * 显示图片
	 */
	public function getpic(){
		//获取地址
		$pic_url = I('get.img');
		$this->assign('shw', $pic_url);
		$this->display('showpic');
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


		$menuHoModel = new \Admin\Model\MenuHotelModel();
		$adsModel = new \Admin\Model\AdsModel();
		$mediaModel = new \Admin\Model\MediaModel();
		$ads_id = I('post.ads_id');
		$covermedia_id = I('post.covervideo_id','0','intval');//视频封面id
		$media_id = I('post.media_id','0','intval');//视频id

		$save = [];
		$save['description'] = I('post.descri');
		$save['duration'] = I('post.duration');
		$save['name'] = I('post.adsname');
		if($covermedia_id){
			$oss_arr = $mediaModel->find($covermedia_id);
			$oss_addr = $oss_arr['oss_addr'];
			$save['img_url'] = $oss_addr;
		}
		if($media_id){
			$oss_arr = $mediaModel->find($media_id);
			$save['media_id']    = $media_id;
		}
		$save['hotel_id'] = I('post.hotel_id');
		if($ads_id){
			$res_save = $adsModel->where('id='.$ads_id)->save($save);
			$dat['update_time'] = date("Y-m-d H:i:s");
			$menuHoModel->where(array('hotel_id'=>$save['hotel_id']))->save($dat);
			if($res_save){
				$this->output('操作成功!', 'hotel/pubmanager');
			}else{
				$this->output('操作失败!', 'hotel/doAddPub');
			}
		}else{
			//判断宣传片名称是否存在
			$count = $adsModel->where(array('name'=>$save['name'],'hotel_id'=>$save['hotel_id']))->count();
			if ($count >1 ){
				$this->output('宣传片已经存在', 'hotel/addpub',1,0);
			}
			$userInfo = session('sysUserInfo');
			$save['creator_id'] = $userInfo['id'];
			$save['creator_name'] = $userInfo['username'];
			$save['create_time'] = date('Y-m-d H:i:s');
			$save['type'] = 3;
			//刷新页面，关闭当前
			$dat['update_time'] = date("Y-m-d H:i:s");
			$res_save = $adsModel->add($save);
			$menuHoModel->where(array('hotel_id'=>$save['hotel_id']))->save($dat);
			if($res_save){
				$this->output('添加宣传片成功!', 'hotel/pubmanager');
			}else{
				$this->output('操作失败!', 'hotel/doAddPub');
			}
		}
	}
	/*
	 * 修改状态
	 */
	public function operateStatus(){


		$adsid = I('request.adsid','0','intval');
		$adsModel = new \Admin\Model\AdsModel();
		$message = '';
		$flag = I('request.flag');
		$data = array('state'=>$flag);

		$res = $adsModel->where("id='$adsid'")->save($data);

		if($res){
			$message = '更新状态成功';
		}

		if($message){
			$this->output($message, 'hotel/pubmanager',2);
		}else{
			$this->output('操作失败', 'hotel/pubmanager');
		}


	}

	public function delpub(){
		$ads_id = I('get.ads_id');
		$hotel_id = I('get.hotel_id');
		$adsModel = new \Admin\Model\AdsModel();
		$bool = $adsModel->where('id='.$ads_id)->delete();
		if($bool){
			$this->output('删除宣传片成功!', U('hotel/pubmanager?hotel_id='.$hotel_id));
		} else {
			$this->output('删除宣传片失败!', 'hotel/pubmanager');
		}
		;
	}

}
