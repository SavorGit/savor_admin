<?php
/**
 *@author hongwei
 *
 *
 *
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Think\Model;
use Common\Lib\SavorRedis;
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

		$ajaxversion   = I('ajaxversion',0,'intval');//1 版本升级酒店列表
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','a.id');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;

		//城市
		$area_arr = $areaModel->getAllArea();
		$this->assign('area', $area_arr);
		
		$where = "1=1";
		$beg_time = I('starttime','');   //安装开始时间
		$end_time = I('endtime','');     //安装结束时间
		if($beg_time){
		    $where.=" AND a.install_date>='$beg_time'";
		    $this->assign('beg_time',$beg_time);
		}
		if($end_time){
		    $where.=" AND a.install_date<='$end_time'";
		    $this->assign('end_time',$end_time);
		}
		$name = I('name');
		if($name){
		    $search_name = addslashes($name);
			$this->assign('name',$name);
			$where .= "	AND a.name LIKE '%{$search_name}%'";
		}
		//机顶盒类型
		$hbt_v = I('hbt_v');
		if ($hbt_v) {
			$this->assign('hbt_k',$hbt_v);
			$where .= "	AND a.hotel_box_type = $hbt_v";
		}
		//城市
		$userinfo = session('sysUserInfo');
		$pcity = $userinfo['area_city'];
		$is_city_search = 0;
		if($userinfo['groupid'] == 1 || empty($userinfo['area_city'])) {
			$pawhere = '1=1';
			$is_city_search = 1;
			$this->assign('is_city_search',$is_city_search);
			$this->assign('pusera', $userinfo);
		}else {
		   
		    $this->assign('is_city_search',$is_city_search);
			$where .= "	AND a.area_id in ($pcity)";
			$pawhere = '1=1 and area_id = '.$pcity;
		}
		//包含酒楼
		$pafield = 'DISTINCT smh.menu_id id,smlist.menu_name';
		$men_arr = $menuHoModel->getPrvMenu($pafield, $pawhere);
		//获取包含有该地区酒楼
		$this->assign('include', $men_arr);
		//城市
		$area_v = I('area_v');
		if ($area_v) {
			$this->assign('area_k',$area_v);
			if(!empty($area_v) ){
				$where .= "	AND a.area_id = $area_v";
			}
		}
		//级别
		$level_v = I('level_v');
		if ($level_v) {
			$this->assign('level_k',$level_v);
			$where .= "	AND a.level = $level_v";
		}
		//状态
		$state_v = I('state_v');
		if ($state_v) {
			$this->assign('state_k',$state_v);
			$where .= "	AND a.state = $state_v";
		}

		//重点
		$key_v = I('key_v');
		if ($key_v) {
			$this->assign('key_k',$key_v);
			$where .= "	AND a.iskey = $key_v";
		}
		//合作维护人
		$maintainer_id = I('maintainer_id',0,'intval');
		if($maintainer_id){
		    $where .=" and ext.maintainer_id=$maintainer_id";
		    //echo $where;exit;
		    $this->assign('maintainer_id',$maintainer_id);
		}
		/* $main_v = I('main_v');
		if ($main_v) {
			$this->assign('main_k',$main_v);
			$where .= "	AND maintainer LIKE '%{$main_v}%'";
		} */
		//广告机选项
		$select_ad_mache = I('adv_machine');
		$this->assign('se_ad_machince',$select_ad_mache);
		$is_4g = I('is_4g','0','intval');
		if(!empty($is_4g)){
		    $where .=" AND a.is_4g=".$is_4g;   
		}
		$this->assign('is_4g',$is_4g);
		//是否虚拟小平台
		$is_virtual = I('is_virtual','0','intval');
		if(!empty($is_virtual)){
		    if($is_virtual==1){
		        $where .=" and ext.mac_addr ='000000000000'";
		    }else {
		        $where .=" and ext.mac_addr !='000000000000'";
		    }
		}
		$this->assign('is_virtual',$is_virtual);
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
				$where .= "	AND a.id  in ($bak_ho_str)";
			}else{
				$where .= "	AND a.id  in ('')";
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
					$where .= "	AND a.id not in ($bak_ho_str)";
				}
			}
		}
		$hotelExt = new \Admin\Model\HotelExtModel();
		if($select_ad_mache != 0) {
			$ad_machin_arr = array();
			if($select_ad_mache == 1) {
				//求出所有>0
				$ad_machin_arr['adplay_num'] = array('gt', 0);

			} else {
				//求出所有<=0
				$ad_machin_arr['adplay_num'] = array('elt', 0);
			}
			$se_ad_hid_arr = $hotelExt->getData('hotel_id', $ad_machin_arr);

			if($se_ad_hid_arr) {
				$se_ad_hid_arr = array_column($se_ad_hid_arr, 'hotel_id');
				$se_ad_hid_arr = array_unique($se_ad_hid_arr);
				$se_ad_hid_arr = array_filter($se_ad_hid_arr);
				$se_ad_machine_str = implode(',', $se_ad_hid_arr);
				$where .= "	AND a.id in ($se_ad_machine_str) ";
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
			//获取所有合作维护人
			$m_opuser_role = new \Admin\Model\OpuserroleModel();
			$fields = 'a.user_id uid,user.remark ';
			$map = array();
			$map['state']   = 1;
			$map['role_id']   = 1;
			$user_info = $m_opuser_role->getAllRole($fields,$map,'' );
			$u_arr = array();
			$hezuo_arr = array();
			foreach($user_info as $uv) {
				$u_arr[$uv['uid']] = trim($uv['remark']);
			}
			foreach($u_arr as $key=>$v){
			    $firstCharter = getFirstCharter(cut_str($v, 1));
			    $tmp['uid'] = $key;
			    $tmp['remark'] = $v;
			    $hezuo_arr[$firstCharter][] = $tmp;
			}
			ksort($hezuo_arr);
			$this->assign('hezuo_arr',$hezuo_arr);
            $fields = "a.id,a.name,a.addr,a.contractor,a.mobile,a.state,ext.maintainer_id,area.region_name";
			$result = $hotelModel->getListExt($where, $orders,$start,$size, $fields);
		    //$result = $hotelModel->getList($where,$orders,$start,$size);
		}
        
		$datalist = $result['list'];
		//$datalist = $areaModel->areaIdToAareName($result['list']);
		foreach ($datalist as $k=>$v){

			$conditon = array();
			$men_arr = array();
			$nums = $hotelModel->getStatisticalNumByHotelId($v['id']);
			$datalist[$k]['room_num'] = $nums['room_num'];
			$datalist[$k]['box_num'] = $nums['box_num'];
			$datalist[$k]['tv_num'] = $nums['tv_num'];
			$hotel_id = $datalist[$k]['id'];
			
			//$main_info = $hotelExt->where('hotel_id='.$hotel_id)->find();
			if($v['maintainer_id']) {
				$datalist[$k]['maintainer'] = $u_arr[$v['maintainer_id']];
			} else {
				$datalist[$k]['maintainer'] = '无';
			}

			$condition['hotel_id'] = $hotel_id;
			$arr = $menuHoModel->where($condition)->order('id desc')->find();
			$promenuHoModel = new \Admin\Model\ProgramMenuHotelModel();
			$new_menu_arr = $promenuHoModel->where($condition)->order('id desc')->find();
			$promenuid = $new_menu_arr['menu_id'];
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

			if($promenuid){
				$promenulistModel = new \Admin\Model\ProgramMenuListModel();
				$promen_arr = $promenulistModel->find($promenuid);
				$promenuname = $promen_arr['menu_name'];
				$datalist[$k]['promenu_id'] = $promenuid;
				$datalist[$k]['promenu_name'] = $promenuname;

			}else{
				$datalist[$k]['promenu_id'] = '';
				$datalist[$k]['promenu_name'] = '无';
			}
            if(empty($v['contractor']) || $v['contractor']=='null'){
                $datalist[$k]['contractor'] = '';
            }
		}
		$se_ad_machine = C('SELECT_ADV_MACH');
		$this->assign('select_ad_mache', $se_ad_machine);
		$this->assign('list', $datalist);
		$this->assign('page',  $result['page']);
		$this->display('index');
	}
	/**
	 * @机顶盒、小平台升级选择酒楼
	 */
	public function manager_list(){
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
	    $order = I('_order','update_time');
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
	    //机顶盒类型
	    $hbt_v = I('hbt_v');
	    if ($hbt_v) {
	        $this->assign('hbt_k',$hbt_v);
	        $where .= "	AND hotel_box_type = $hbt_v";
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
	    }
	   
	}

	/**
	 * 新增酒店
	 *
	 */
	public function add(){
		$id = I('get.id');
		$hotelModel = new \Admin\Model\HotelModel();
		$areaModel  = new \Admin\Model\AreaModel();
		
		
		$userinfo = session('sysUserInfo');
		$pcity = $userinfo['area_city'];
		if($userinfo['groupid'] ==1 || empty($pcity)){
		    $area = $areaModel->getAllArea();
		}else {
		    $where = array();
		    $where['is_in_hotel'] = 1;
		    $where['id'] = $pcity;
		    $area = $areaModel->getWhere('id,region_name',$where);
		}
		
		$this->assign('area',$area);
		//获取所有发布者列表
//获取发布者列表
		$m_opuser_role = new \Admin\Model\OpuserroleModel();
		$fields = 'a.user_id main_id,user.remark ';
		$map['state']   = 1;
		$map['role_id']   = 1;
		$user_info = $m_opuser_role->getAllRole($fields,$map,'' );
		$l_c = count($user_info);
		$user_info[$l_c] = array(
			'main_id'=>0,
			'remark'=>'无',
		);

		$this->assign('pub_info',$user_info);
		if($id){

			$vinfo = $hotelModel->where('id='.$id)->find();
			$hotelextModel = new \Admin\Model\HotelExtModel();
			$main_info = $hotelextModel->where('hotel_id='.$id)->find();
			$vinfo['main_id'] = $main_info['maintainer_id'];
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
			$vinfo['tag'] = $res_hotelext['tag'];
			$navtp = I('get.navtp');
			$this->assign('navtp',$navtp);
			$this->assign('vinfo',$vinfo);
		}else{
			$vinfo['state'] = 2;
			$vinfo['state_change_reason'] = 1;
			$this->assign('vinfo',$vinfo);
		}
		$this->display('add');
	}








	/*
	 * 查看酒楼详情
	 *
	 */
	public function getdetail(){


		$id = I('get.id');
		if(!$id){
			$id = I('post.id');
		}
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','update_time');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);

		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;

		$hotelModel = new \Admin\Model\HotelModel();
		$areaModel  = new \Admin\Model\AreaModel();
		$tvModel = new \Admin\Model\TvModel();
		$menuHoModel = new \Admin\Model\MenuHotelModel();
		$menlistModel = new \Admin\Model\MenuListModel();
		$hextModel = new \Admin\Model\HotelExtModel();
		$area = $areaModel->getAllArea();
		$this->assign('area',$area);

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
		$vinfo['is_open_customer'] = $res_hotelext['is_open_customer'];
	    $vinfo['id'] = $id;
		$main_info = $hextModel->where('hotel_id='.$id)->find();
		$se_ad_machine = $main_info['adplay_num'];
		$this->assign('se_ad_machine_num', $se_ad_machine);
		//获取所有发布运维者
		$m_opuser_role = new \Admin\Model\OpuserroleModel();
		$fields = 'a.user_id uid,user.remark ';
		$map = array();
		$map['state']   = 1;
		$map['role_id']   = 1;
		$user_info = $m_opuser_role->getAllRole($fields,$map,'' );
		$u_arr = array();
		foreach($user_info as $uv) {
			$u_arr[$uv['uid']] = trim($uv['remark']);
		}
		if($main_info['maintainer_id']) {
			$vinfo['maintainer'] = $u_arr[$main_info['maintainer_id']];
		} else {
			$vinfo['maintainer'] = '无';
		}
		$condition['hotel_id'] = $id;
		$arr = $menuHoModel->where($condition)->order('id desc')->find();
		$menuid = $arr['menu_id'];
		if($menuid){
			$men_arr = $menlistModel->find($menuid);
			$menuname = $men_arr['menu_name'];
			$vinfo['menu_id'] = $menuid;
			$vinfo['menu_name'] = $menuname;

		}else{
			$vinfo['menu_id'] = '';
			$vinfo['menu_name'] = '无';
		}

		$nums = $hotelModel->getStatisticalNumByHotelId($id);
		$vinfo['room_num'] = $nums['room_num'];
		$vinfo['box_num'] = $nums['box_num'];
		$vinfo['tv_num'] = $nums['tv_num'];
		//获取批量信息
		$where = " h.id = ".$id;
		$boxModel = new \Admin\Model\BoxModel();
		$list = $boxModel->getBoxTvInfo(' r.id as rid,b.id as bid,
		 tv.id as tid,h.name as hotel_name,r.name as room_name,r.type as rtp,b.name as bna,b.mac as bmac,b.switch_time as bstime,b.volum as bvm,tv.tv_brand as tbr,tv.tv_size as tsize,tv_source as tsource,tv.state as tstate  ', $where,$start,$size);
		/*$list = $tvModel->isTvInfo(' r.id as rid,b.id as bid,
		 tv.id as tid,h.name as hotel_name,r.name as room_name,r.type as rtp,b.name as bna,b.mac as bmac,b.switch_time as bstime,b.volum as bvm,tv.tv_brand as tbr,tv.tv_size as tsize,tv_source as tsource,tv.state as tstate  ', $where,$start,$size);*/

		$isHaveTv = $list['list'];
		$page = $list['page'];
		if(!empty($isHaveTv)){
			$isRealTv = $tvModel->changeTv($isHaveTv);
		}
		//继续获取机顶盒不为空电视为空的

		$ind = $start;
		foreach ($isRealTv as &$val) {
			$val['indnum'] = ++$ind;
		}

		$this->assign('list',$isRealTv);
		$this->assign('vinfo',$vinfo);
		$this->assign('hotelid',$id);
		$this->assign('page',$page);
		$this->display('detail');
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

		$save['tech_maintainer']          = I('post.techmaintainer','','trim');
		$save['tel']                 = I('post.tel','','trim');
		$save['level']               = I('post.level','','trim');
		$save['iskey']               = I('post.iskey','','intval');
		$save['install_date']        = I('post.install_date');
		$save['remote_id']           = I('post.remote_id');
		$save['hotel_wifi']          = I('post.hotel_wifi','','trim');
		$save['hotel_wifi_pas']      = I('post.hotel_wifi_pas','','trim');
		$save['collection_company']  = I('post.collection_company','','trim');
		$save['bank_account']        = I('post.bank_account','','trim');
		$save['bank_name']           = I('post.bank_name','','trim');
		$save['is_4g']               = I('post.is_4g',0,'intval');  //是否为4G酒楼
		if(mb_strlen($save['collection_company'])>50 || mb_strlen($save['bank_account'])>50 || mb_strlen($save['bank_name'])>50){
			$this->error('收款公司名称，银行账号以及开户行名称最多50个字');
		}

		if(!($save['install_date'])){
			$save['install_date'] = date("Y-m-d",time());
		}
		$save['level']               = I('post.level','','trim');
		$save['state']               = I('post.state','','intval');
		$save['state_change_reason'] = I('post.state_change_reason',0);
		$save['remark']              = I('post.remark','','trim');
		$save['flag']                = I('post.flag','','intval');
		$save['update_time']         = date('Y-m-d H:i:s');
		$save['mobile']              = I('post.mobile','','trim');
		$save['gps']				 = I('post.gps','','trim');
		$save['hotel_box_type']      = I('post.hotel_box_type',0,'intval');
		$save['bill_per']				 = I('post.bill_per','','trim');
		$save['bill_tel']				 = I('post.bill_tel','','trim');
		if($save['bill_tel']){
			if(!preg_match('/^1[34578]{1}\d{9}$/',$save['bill_tel'], $result)){
				$this->error('手机号非法输入');
			}
		}
		if($save['bill_per']){
			if(  mb_strlen($save['bill_per'])<2 ||  mb_strlen($save['bill_per'])>10 ){
				$this->error('联系人2至10个字符');
			}
		}
		if($save['gps']){
			if(!preg_match('/^([\d]+\.[\d]*),([\d]+\.[\d]*)$/',$save['gps'], $result)){
				$this->error('不可输入非法字符');
			}
		    if(!strstr($save['gps'], ',')){
		        $this->error('请输入正确的经纬度');
		    }
		    $gps_arr = explode(',', $save['gps']);
		    if($gps_arr[0]<0 || $gps_arr[0]>180){
		        $this->error('请输入正确的经度');
		    }
		    if($gps_arr[1]<0 || $gps_arr[1]>90){
		        $this->error('请输入正确的维度');
		    } 
		}

		$save['area_id']             = I('post.area_id','','intval');
		$save['media_id']             = I('post.media_id','0','intval');
		$mac_addr = I('post.mac_addr','','trim');
		$server_location = I('post.server_location','','trim');
		$hotelModel = new \Admin\Model\HotelModel();
		//判断酒楼重名start
		$hotel_name = addslashes($save['name']);
		if(!empty($hotel_name)){
		    if($hotel_id){
		        $where = " name='".$hotel_name."' and  id !=".$hotel_id;
		    }else {
		        $where = " name='".$hotel_name."'";
		    }
		    $nums = $hotelModel->getHotelCount($where);
		    if(!empty($nums)){
		        $this->error('该酒楼名称已存在');
		    }
		}
		//判断酒楼重名end
		$hextModel = new \Admin\Model\HotelExtModel();
		$data['mac_addr'] = $mac_addr;
		if(!empty($mac_addr) && $mac_addr !='000000000000'){
		    if($hotel_id){
		        $where = "he.mac_addr='".$mac_addr."' and h.state=1 and he.hotel_id !=".$hotel_id;
		    }else {
		        $where = "he.mac_addr='".$mac_addr."' and h.state=1";
		    }
		    
		    $have_mac_addr = $hextModel->isHaveMac('h.id,h.name',$where);
		    if(!empty($have_mac_addr)){
		        $this->error('Mac地址存在于'.$have_mac_addr[0]['name'].'酒楼');
		    }
		}else if(!empty($mac_addr) && $mac_addr=='000000000000'){
		    if($hotel_id){
		          $m_heart_log = new \Admin\Model\HeartLogModel();
		          $m_heart_log->deleteInfo(array('hotel_id'=>$hotel_id,'type'=>1), '1');
		    }
		}
		
		$data['server_location'] = $server_location;
		$data['tag']             = I('post.tag','','trim');
		$data['maintainer_id']   = I('post.maintainer',0);
		$tranDb = new Model();
		$tranDb->startTrans();
		if ($hotel_id) {
			$where =  'id='.$hotel_id;
			$bool = $hotelModel->saveData($save, $where);
			if( $bool ) {
				$res = $hotelModel->getOne($hotel_id);
				$save['create_time'] = $res['create_time'];
				$hotelModel->saveStRedis($save, $hotel_id);
			} else {
				$this->error('操作失败1');
			}
		} else {
			$save['create_time'] = date('Y-m-d H:i:s');
			$bool = $hotelModel->addData($save);
			if($bool){
				$hotel_id = $hotelModel->getLastInsID();
				$hotelModel->saveStRedis($save, $hotel_id);
			} else {
				$this->error('操作失败2');
			}

		}
		$field = 'mac_addr,server_location';
		$where = array('hotel_id'=>$hotel_id);
		$res = $hextModel->getData($field, $where);
		if ( $res ) {
			$res = $res[0];
			ksort($data);
			ksort($res);
			$hextModel->saveData($data,$where);
		}else {
		    $data['hotel_id'] = $hotel_id;
			$bool = $hextModel->addData($data);
		}
		if($bool){
			$tranDb->commit();
			$hextModel->saveStRedis($data, $hotel_id);
			$navtp				 = I('post.navtp','');
			if($navtp == 34) {
				$this->output('操作成功!', 'hotel/detail');
			} else {
				$this->output('操作成功!', 'hotel/manager');
			}

		} else {
			$tranDb->rollback();
			$this->error('操作失败3!');
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
		$id                  = I('post.id','0');
		$save                = [];
		$hotel_id    = I('post.hotel_id','','intval');
		$save['hotel_id'] = $hotel_id;
		$save['name']        = I('post.name','','trim');
		$save['type']        = I('post.type','','intval');
		$save['flag']        = I('post.flag','','intval');
		$save['state']       = I('post.state','','intval');
		$save['probe']       = I('post.probe','','trim');
		$save['remark']      = I('post.remark','','trim');
		$save['update_time'] = date('Y-m-d H:i:s');
		$RoomModel = new \Admin\Model\RoomModel();
		if(!$id){
			//判断包间名称
			$temp = $RoomModel->getRow('name',['hotel_id'=>$hotel_id,'name'=>$save['name']]);
			if($temp){
				$this->error('包间名称已经存在');
			}
		}
		$bool = $RoomModel->saveData($save, $id);
		if($id){
			if($bool){
				$this->output('操作成功!', 'hotel/room');
			}else{
				$this->output('操作失败!', 'hotel/doAddRoom');
			}
		}else{
			if($bool){
				$this->output('操作成功!', 'hotel/manager');
			}else{
				$this->output('操作失败!', 'hotel/doAddRoom');
			}
		}
	}


	/*
	 * 批量新增牌位
	 */
	public function batchposition() {
		$r_arr = array(
			1=>'包间',
			2=>'大厅',
			3=>'等候区',
		);
		$tv_arr = array(
			1=>'ant',
			2=>'av',
			3=>'hdmi',
		);
		$tv_stet = array(
			1=>'正常',
			2=>'冻结',
			3=>'报损',
		);
		$b_arr = array(
			'rname' => 'V1',
			'voloume' => 50,
			'swtime' => 30,
			'numb' => 2,
			'boxxname' => 'V1',
			'bacadd' => 'FFFFFFFFF',
			'tvbran' => 'SONY',
			'tvsizea'=>'32',
		);

		$hotel_id= I('get.hotel_id',0);
		$hotel_name= I('get.name','');
		if ($hotel_id) {

			$this->assign('hotelname',$hotel_name);
			$this->assign('hotelid',$hotel_id);
			$this->assign('rtype_list',$r_arr);
			$this->assign('bar',$b_arr);
			$this->assign('tvlist',$tv_arr);
			$this->assign('tvstate',$tv_stet);
		} else {

		}
		$ad_machine = C('ADV_MACH');
		$this->assign('ad_mache', $ad_machine);
		$this->display('batchposition');
	}


	/*
	 * 批量新增牌位
	 */
	public function doAddBatch() {

		$r_arr = array(
			1=>'包间',
			2=>'大厅',
			3=>'等候区',
		);
		$hotelid = $_POST['hotelid'];
		$h_str = $_POST['hval'];
		$bat_arr = explode('???',$h_str);
		//var_dump($bat_arr);
		$len = count($bat_arr);
		if( empty($bat_arr[0]) ){
			$this->error('创建不可为空');
		}
		$model = new Model();
		//print_r($bat_arr);
		foreach ($bat_arr as $k=>$v){
			$v = json_decode($v,true);
			//var_export($v);
			foreach($v as $ks=>$vs){
				if ( empty($vs) && ($vs!== '0') ) {
					$this->error('所有元素不可为空');
				}else{
					if($ks == 'bao_mac'){
						if(strlen($vs)!=12){
							$this->error('MAC地址提示12位数字与字母组合');
						}else{


							$preg = '/^[0-9A-Z]+$/';
							$prg = preg_match($preg,$vs)?true:false;
							if(!$prg){

								$this->error('请输入正确12位的mac地址， 只允许输入数字或字母');
							}


						}
					}

				}
			}
		}

		$ba_name = array();
		$ba_mac = array();
		$mac_mes = array();
		$ba_r_harr = array();
		$bac_hotel_rarr = array();
		$hp = array();
		//遍历包间名称不可重复，MAC地址不可重复
		$RoomModel = new \Admin\Model\RoomModel();
		$boxModel = new \Admin\Model\BoxModel();
		$hotelModel = new \Admin\Model\HotelModel();
		$tvModel = new \Admin\Model\TvModel();
		$dahotels = array();
		$dahotelmac = array();
		$dahotelmaname = array();
		foreach ($bat_arr as $k=>$v) {
			$v = json_decode($v, true);
			$dahotels[$k]=$v;
			$dahotelmac[$k] = $v['bao_mac'];
			$dahotelmaname[$k] = $v['box_name'];
		}
		$dahotel = $dahotels;
		$dahotelmaname = array_count_values($dahotelmaname);
		//对mac地址一样进行判断
		foreach ($dahotels as $k=>$v) {
			$boxname = $v['box_name'];
			$macname = $v['bao_mac'];
			$boxstr = $v['bao_name'].','.$v['bao_lx'];
			foreach($dahotels as $dak=>$dav){
				if($dav['box_name'] == $boxname ){
					$boxstp = $dav['bao_name'].','.$dav['bao_lx'];
					if($boxstp!=$boxstr){
						$this->error('酒楼不允许出现机顶盒重名的情况');
					}else{
						if($dav['bao_mac'] != $macname){
							$this->error('相同名称的机顶盒mac地址不一致，请重新输入');
						}
					}
				}
			}
			$dahotelmacc = $dahotelmac;
			$macho = $v['bao_mac'];
			unset($dahotelmacc[$k]);
			$gets = array_search($macho, $dahotelmacc);
			if(is_numeric($gets)){
				if (preg_match("/^\d*$/", $gets)) {
					if (($v['bao_name'] != $dahotel[$gets]['bao_name']) && ($v['bao_lx'] != $dahotel[$gets]['bao_lx']) || ($v['box_name'] != $dahotel[$gets]['box_name'])) {
						$this->error('一个机顶盒mac地址不允许对应多个包间');
					}
				}
			}

		}

		//进行数据库判断
		foreach ($dahotels as $k=>$v) {

			//判断机顶盒名称是否存在且mac地址不一样
			$wherec = " b.name='" . $v['box_name'] ."' and h.id = ".$hotelid;
			$isHavebox = $boxModel->isHaveMac(' h.name as hotel_name,h.id as hotel_id,r.name as room_name,r.type as rtp,b.name as bna,b.mac as mac ', $wherec);
			if (!empty($isHavebox)) {
				if($isHavebox[0]['mac']!=$v['bao_mac']){
					$this->error('该机顶盒名称'.$v['box_name'].'已经存在于该酒店对应mac为'.$isHavebox[0]['mac']);
				}
			}
			//判断是否有该机顶盒mac地址
			$where = " b.mac='" . $v['bao_mac'] . "' and b.flag=0 ";
			$isHaveMac = $boxModel->isHaveMac(' h.name as hotel_name,h.id as hotel_id,r.name as room_name,r.type as rtp,b.name as bna,b.id as id,b.mac as mac ', $where);

			if (!empty($isHaveMac)) {
				foreach ($isHaveMac as $ks=>$vs) {
					$hp[$ks] = $vs['hotel_id'].','.$vs['room_name'].','.$vs['rtp'].','.$vs['bna'];
					$hps[$ks] = $vs['hotel_id'];
				}
				$hpp = $hotelid.','.$v['bao_name'].','.$v['bao_lx'].','.$v['box_name'];
				$hpps = $hotelid;
				//4个值完全相同同一个hotelid
				if(in_array($hpp, $hp)){
					$mac_mes[]= $v['bao_mac'];
				}else{
					//同一个hotelid
					//3个值相同
					$rttp = $isHaveMac[0]['rtp'];
					if (in_array($hpps, $hps)){
							$str = $isHaveMac[0]['mac'].'机顶盒在本酒店已经存在!<br/>'.'已存在信息为:<br/>包间名称:'.$isHaveMac[0]['room_name'].'<br/>包间类型:'.$r_arr[$rttp].' <br/>机顶盒名称:'.$isHaveMac[0]['bna'];

					}else{
						$str = 'Mac地址对应机顶盒'.$v['box_name'].'存在于' . $isHaveMac[0]['hotel_name'] . '酒楼' . $isHaveMac[0]['room_name'] . '包间';
					}
					$this->error($str);
				}
			}
		}

		$bool = false;
		//获取所有包间id
		$room_bai = array();
		//var_dump($bat_arr);
		//获取酒楼下的广告机数量
		$hextModel = new \Admin\Model\HotelExtModel();
		$h_adv_num = 0;
		foreach ($bat_arr as $k=>$v) {
			$model->startTrans();
			$v = json_decode($v, true);
			if ( $v['adv_machi'] == 1) {
				$h_adv_num++;
			}
			$where = " r.name='".$v['bao_name']."' and b.flag=0  and r.type =  ".$v['bao_lx']." and h.id = ".$hotelid;
			$isHaveTv = $boxModel->isHaveTv(' h.name as hotel_name,r.name as room_name,r.id as rid,r.type as rtp,b.name as bna,b.id as id,b.mac as bmacc ',$where);
			if (!empty($isHaveTv)) {
				foreach ($isHaveTv as $ktv=>$vtv) {
					$bac_hotel_rmac[$ktv] = $vtv['bmacc'];
				}


				if(in_array($v['bao_mac'],$bac_hotel_rmac)){
					//只加电视
					$mac_key = array_search($v['bao_mac'], $bac_hotel_rmac);
					//找到机顶盒名称
					$ma_name = $isHaveTv[$mac_key]['bna'];
						$dap = array();
						$dap['box_id'] = $isHaveTv[$mac_key]['id'];
						$dap['tv_brand'] = $v['tv_brand'];
						$dap['tv_size'] = $v['tv_size'];
						$dap['tv_source'] = $v['tv_source'];
						$dap['state'] = $v['tv_state'];
						$bool = $model->table(C('DB_PREFIX').'tv')->add($dap);
						if($bool){
							$ttid = $model->table(C('DB_PREFIX').'tv')->getLastInsID();
						} else {
							$model->rollback();
							$this->error('失败请重新操作添加');
						}


					$room_bai[] = array('room_id'=>$isHaveTv[$mac_key]['rid'],
						'box_id'=>$isHaveTv[$mac_key]['id'],'tv_id'=>$ttid);
				} else {
					//新增机顶盒与电视
					$dat = array();
					$dat['room_id'] = $isHaveTv[0]['rid'];
					$dat['name'] = $v['box_name'];
					$dat['mac'] = $v['bao_mac'];
					$dat['switch_time'] = $v['bao_time'];
					$dat['volum'] = $v['bao_volume'];
					$dat['flag']        = 0;
					$dat['state']       = 1;
					$dat['update_time'] = date('Y-m-d H:i:s');
					$dat['create_time'] = date('Y-m-d H:i:s');
					$dat['adv_mach'] = $v['adv_machi'];
					$bool = $model->table(C('DB_PREFIX').'box')->add($dat);
					if ($bool) {
						$dap = array();
						$dap['box_id'] = $model->table(C('DB_PREFIX').'box')->getLastInsID();
						$dap['tv_brand'] = $v['tv_brand'];
						$dap['tv_size'] = $v['tv_size'];
						$dap['tv_source'] = $v['tv_source'];
						$dap['state'] = $v['tv_state'];
						$bool = $model->table(C('DB_PREFIX').'tv')->add($dap);
						if ($bool) {
							$datv['tv_id'] = $model->table(C('DB_PREFIX').'tv')->getLastInsID();
						} else {
							$model->rollback();
							$this->error('失败请重新操作添加电视');
						}
					} else {
						$model->rollback();
						$this->error('失败请重新操作添加机顶');
					}

					$room_bai[] = array('room_id'=>$dat['room_id'],
						'box_id'=>$dap['box_id'],'tv_id'=>$datv['tv_id']);

				}
			} else{
				//添加所有
				$save = array();
				//添加包间
				$save['hotel_id'] = $hotelid;
				$save['name']        = $v['bao_name'];
				$save['type']        = $v['bao_lx'];
				$save['flag']        = 0;
				$save['state']       = 1;
				$save['update_time'] = date('Y-m-d H:i:s');
				$save['create_time'] = date('Y-m-d H:i:s');
				$bool = $model->table(C('DB_PREFIX').'room')
					->add($save);
				if($bool){
					$dat = array();
					$dat['room_id'] = $model->table(C('DB_PREFIX').'room')->getLastInsID();
					$dat['name'] = $v['box_name'];
					$dat['mac'] = $v['bao_mac'];
					$dat['switch_time'] = $v['bao_time'];
					$dat['volum'] = $v['bao_volume'];
					$dat['flag']        = 0;
					$dat['state']       = 1;
					$dat['update_time'] = date('Y-m-d H:i:s');
					$dat['create_time'] = date('Y-m-d H:i:s');
					$dat['adv_mach'] = $v['adv_machi'];
					$bool = $model->table(C('DB_PREFIX').'box')->add($dat);
					if ($bool) {
						$dap = array();
						$dap['box_id'] = $model->table(C('DB_PREFIX').'box')->getLastInsID();
						$dap['tv_brand'] = $v['tv_brand'];
						$dap['tv_size'] = $v['tv_size'];
						$dap['tv_source'] = $v['tv_source'];
						$dap['state'] = $v['tv_state'];
						$bool = $model->table(C('DB_PREFIX').'tv')->add($dap);
						if ($bool) {
							$datv['tv_id'] = $model->table(C('DB_PREFIX').'tv')->getLastInsID();
						} else {
							$model->rollback();
							$this->error('失败请重新操作添加电视');
						}
					} else {
						$model->rollback();
						$this->error('失败请重新操作添加机顶');
					}
				}else{
					$model->rollback();
					$this->error('失败请重新操作添加包间');
				}
				$room_bai[] = array('room_id'=>$dat['room_id'],
					'box_id'=>$dap['box_id'],'tv_id'=>$datv['tv_id']);
			}
			$model->commit();
		}
		//递增广告机
		$hextModel->where('hotel_id='.$hotelid)->setInc('adplay_num', $h_adv_num);
		
		if($bool){
		    $is_room = $is_box = $is_tv = 0;
			foreach ($room_bai as $k=>$v) {
				if($v['room_id']){
					$rinfo = $RoomModel->find($v['room_id']);
					$RoomModel->saveBatdat($rinfo, $v['room_id']);
					$is_room = 1;
				}
				if($v['box_id']){
					$bo_info = $boxModel->find($v['box_id']);
					$boxModel->saveBatdat($bo_info, $v['box_id']);
                    $is_box = 1;
				}
				if($v['tv_id']){
					$tv_info = $tvModel->find($v['tv_id']);
					$tvModel->saveBatdat($tv_info, $v['tv_id']);
                    $is_tv = 1;
				}
			}
			if($mac_mes){
				$spt = '';
				$mac_mes = array_unique($mac_mes);
				foreach($mac_mes as $v){
					$spt .= '['.$v.']|';
				}
				$mac_mes = substr($spt,0,-1);
				$sps = '机顶盒('.$mac_mes.'),时间与音量已经存在，请在机顶盒管理中进行修改';
			}else{
				$sps = '添加成功了';

			}
			$redis = SavorRedis::getInstance();
			$redis->select(12);
			if(!empty($is_room)){
			    $cache_key = C('SMALL_ROOM_LIST').$hotelid;
			    $redis->remove($cache_key);
			}
			if(!empty($is_box)){
			    $cache_key = C('SMALL_BOX_LIST').$hotelid;
			    $redis->remove($cache_key);
			}
			if(!empty($is_tv)){
			    $cache_key = C('SMALL_TV_LIST').$hotelid;
			    $redis->remove($cache_key);
			}
			
			$this->output($sps,'hotel/manager');
		}else{
			$this->error('失败请重新操作');
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
		$oss_host = get_oss_host();
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
			$oss_host = get_oss_host();
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
		//$this->output('操作成功!', 'hotel/pubmanager');
		$menuHoModel = new \Admin\Model\MenuHotelModel();
		$adsModel = new \Admin\Model\AdsModel();
		$mediaModel = new \Admin\Model\MediaModel();
		$ads_id = I('post.ads_id');
		$covermedia_id = I('post.covervideo_id','0','intval');//视频封面id
		$media_id = I('post.media_id','0','intval');//视频id

		$save = [];
		$save['description'] = I('post.descri');
		$minu = I('post.minu','0','intval');
		$seco = I('post.seco','0','intval');
		$save['duration'] = I('post.duration','0','intval');
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
		$save['is_sapp_qrcode'] = I('is_sapp_qrcode');
		$redis = SavorRedis::getInstance();
		$redis->select(12);
		$cache_key = C('PROGRAM_ADV_CACHE_PRE').$save['hotel_id'];
		if($ads_id){
		    $maps = array();
		    $maps['name'] = $save['name'];
		    $maps['hotel_id'] = $save['hotel_id'];
		    $maps['id'] = array('neq',$ads_id);
		    $count = $adsModel->where($maps)->count();
		    if ($count >0 ){
		        $this->output('宣传片已经存在', 'hotel/addpub',1,0);
		    }
		    
		    $save['update_time'] = date('Y-m-d H:i:s');
			$res_save = $adsModel->where('id='.$ads_id)->save($save);

			$ads_info = $adsModel->find($ads_id);
			$media_cid = $ads_info['media_id'];
			$media_data['duration'] = $save['duration'];
			$mediaModel->where("id='$media_cid'")->save($media_data);

			$dat['update_time'] = date("Y-m-d H:i:s");
			$menuHoModel->where(array('hotel_id'=>$save['hotel_id']))->save($dat);
			if($res_save){
			    $redis->remove($cache_key);
			    //期刊
			    $mbperModel = new \Admin\Model\MbPeriodModel();
			    $num = $mbperModel->count();
			    $time = time();
			    $dat['period'] = date("YmdHis",$time);
			    $dat['update_time'] = date("Y-m-d H:i:s",$time);
			    if($num>0){
			        $sql = "update savor_mb_period set period=".$dat['period'].",update_time='".$dat['update_time']."'";
			        $rest = $mbperModel->execute($sql);
			    }else{
			        $mbperModel->add($dat);
			    }
				$this->output('操作成功!', 'hotel/pubmanager');
			}else{
				$this->output('操作失败!', 'hotel/doAddPub');
			}
		}else{
			//判断宣传片名称是否存在
			$count = $adsModel->where(array('name'=>$save['name'],'hotel_id'=>$save['hotel_id']))->count();
			if ($count >0 ){
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
			if($media_id){
				$media_data['duration'] = $save['duration'];
				$mediaModel->where("id='$media_id'")->save($media_data);
			}
			$menuHoModel->where(array('hotel_id'=>$save['hotel_id']))->save($dat);
			if($res_save){
			    $redis->remove($cache_key);
			    //期刊
			    $mbperModel = new \Admin\Model\MbPeriodModel();
			    $num = $mbperModel->count();
			    $time = time();
			    $dat['period'] = date("YmdHis",$time);
			    $dat['update_time'] = date("Y-m-d H:i:s",$time);
			    if($num>0){
			        $sql = "update savor_mb_period set period=".$dat['period'].",update_time='".$dat['update_time']."'";
			        $rest = $mbperModel->execute($sql);
			    }else{
			        $mbperModel->add($dat);
			    }
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
		$data = array('state'=>$flag,'update_time'=>date('Y-m-d H:i:s'));

		$res = $adsModel->where("id='$adsid'")->save($data);

		if($res){
			$message = '更新状态成功';
		}

		if($message){
		    $infos = $adsModel->getWhere(array('id'=>$adsid), 'hotel_id,type');
		    $infos = $infos[0];
		    if(!empty($infos['hotel_id']) && $infos['type']==3){
		        $redis = SavorRedis::getInstance();
		        $redis->select(12);
		        $cache_key = C('PROGRAM_ADV_CACHE_PRE').$infos['hotel_id'];
		        $redis->remove($cache_key);
		    }
		    
		    //期刊
		    $mbperModel = new \Admin\Model\MbPeriodModel();
		    $num = $mbperModel->count();
		    $time = time();
		    $dat['period'] = date("YmdHis",$time);
		    $dat['update_time'] = date("Y-m-d H:i:s",$time);
		    if($num>0){
		        $sql = "update savor_mb_period set period=".$dat['period'].",update_time='".$dat['update_time']."'";
		        $rest = $mbperModel->execute($sql);
		    }else{
		        $mbperModel->add($dat);
		    }
		    
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


	public function changeCustomState(){
		$cid = I('request.cid');
		$save = array();
		$save['is_open_customer
'] = I('request.cus_state');

		$hextModel = new \Admin\Model\HotelExtModel();
		$res_save = $hextModel->where('hotel_id='.$cid)->save($save);

		if($res_save){
			$message = '更新成功!';
			$url = 'hotel/detail';
		} else {
			$message = '更新失败!';
			$url = 'hotel/detail';
		}
		$this->output($message, $url,2);
	}

}
