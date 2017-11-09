<?php
/**
 *@author hongwei
 * @desc 心跳显示列表
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Think\Exception;

class ReportController extends BaseController{

	public $path = 'category/img';
	public $oss_host = '';
	public function __construct() {
		parent::__construct();
	}



    /**
     * @Purpose
     * 处理小平台报警
     * @Access public
     * @Method Name:doAddSmall
     * @Http POST
     * @Param int $smallid 小平台日志唯一id
     * @return mixed
     */
    public function doAddSmall(){

        $smWarn = new \Admin\Model\SmallPlaModel();
        $id                  = I('post.id');
        $save                = [];
        $save['state']        = I('post.smallconfig',0,'intval');
        $save['remark1']    = I('post.small_mark','','trim');
        $save['update_time'] = date('Y-m-d H:i:s');
        if($id){
            if($smWarn->where('id='.$id)->save($save)){
                $this->output('操作成功!', 'report/smallplatwarn');
            }else{
                $this->output('操作失败!', 'report/smallplatwarn');
            }
        }else{

                $this->output('操作失败!', 'report/smallplatwarn');

        }
    }


    /**
     * @Purpose
     * 编辑小平台报警
     * @Access public
     * @Method Name:editSmallwarn
     * @Http GET
     * @Param int $smallid 小平台日志唯一id
     * @return mixed
     */
    public function editSmallwarn(){
        $smWarn = new \Admin\Model\SmallPlaModel();
        $id = I('get.smaid', 0, 'intval');
        $small_warn_arr = C('SMALL_WARN');
        try{
            if (is_int($id)) {
                $vinfo = $smWarn->where('id='.$id)->find();
                $this->assign('vinfo',$vinfo);
                $this->assign('smallwarnid',$id);
                $this->assign('smallwarndat',$small_warn_arr);
            } else {
               throw new Exception('必须是整数');
            }
        }catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->display('editsmall');
    }


	/**
	 * 分类列表
	 * @return [type] [description]
	 */
	public function heart(){

		$heartModel = new \Admin\Model\HeartLogModel();
		$areaModel  = new \Admin\Model\AreaModel();
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order',' shlog.last_heart_time ');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$where = "1=1";
		$name = I('he_name');
		$type = I('baotype');
		//城市
		$area_arr = $areaModel->getAllArea();
		$this->assign('area', $area_arr);
		//酒店名称
		$where = ' 1=1 ';
		if($name){
			$this->assign('name',$name);
			$where .= "	AND shlog.hotel_name LIKE '%{$name}%' ";
		}
		//城市
		$area_v = I('he_area_bv');
		if ($area_v) {
			$this->assign('area_k',$area_v);
			$where .= "	AND shlog.area_id = $area_v ";
		}
		//查询类型
		if($type){
		    $this->assign('typea',$type);
			$where .= "	AND shlog.type= {$type} ";
		}
		//合作维护人
		$main_v = I('he_main_v');
		if ($main_v) {
			$this->assign('main_k',$main_v);
			$where .= "	AND sht.maintainer LIKE '%{$main_v}%' ";
		}
		//机顶盒类型
		$hbt_v = I('he_hbt_v');
		if ($hbt_v) {
			$this->assign('hbt_k',$hbt_v);
			$where .= "	AND sht.hotel_box_type = $hbt_v";
		}
        //城市
        $userinfo = session('sysUserInfo');
        $gid = $userinfo['groupid'];

        $usergrp = new \Admin\Model\SysusergroupModel();
        $p_user_arr = $usergrp->getInfo($gid);
        $pcity = $p_user_arr['area_city'];
        if($p_user_arr['id'] == 1 ||
            $p_user_arr['area_city'] == 9999) {
            $this->assign('hightest', 9999);
            $this->assign('pusera', $p_user_arr);
        }else {
            $where .= "	AND shlog.area_id in ($pcity)";
            $this->assign('hightest', $pcity);
        }

		$result = $heartModel->getList($where,$orders,$start,$size);
		$time = time();
		$ind = $start;
		$m_hotel = new \Admin\Model\HotelModel();
		$m_box   = new \Admin\Model\BoxModel();
		foreach ($result['list'] as $key=> &$val) {


			$val['indnum'] = ++$ind;
			$d_time = strtotime($val['last_heart_time']);
			$diff = $time - $d_time;
			if($diff< 3600) {
				$val['last_heart_time'] = floor($diff/60).'分';

			}else if ($diff >= 3600 && $diff <= 86400) {
				$hour = floor($diff/3600);
				$min = floor($diff%3600/60);
				$val['last_heart_time'] = $hour.'小时'.$min.'分';
			}else if ($diff > 86400) {
				$day = floor($diff/86400);
				$hour = floor($diff%86400/3600);
				$val['last_heart_time'] = $day.'天'.$hour.'小时';
			}
			if($val['type']==1){
			    
			    $hotel_ext_info = $m_hotel->getHotelInfoByMac($val['box_mac']);
			    $val['tag'] = $hotel_ext_info['tag'];
			}else if($val['type']==2){
			    
			    $temp = $m_box->getInfo('tag'," mac='".$val['box_mac']."'",'');
			    $val['tag'] = $temp[0]['tag'];
			}
			foreach (C('DEVICE_TYPE') as  $key=>$kv){
				if($val['type'] == $key){
					$val['type'] = $kv;
				}
			}
			foreach (C('heart_hotel_box_type') as  $key=>$kv){
				if($val['hotel_box_type'] == $key){
					$val['hotel_box_type'] = $kv;
				}
			}
			
			
		}
		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('heartlist');
	}



    public function contAndProm(){
        $size   = I('numPerPage',50);     //显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);          //当前页码
        $this->assign('pageNum',$start);
        $order = I('_order','s_read_count'); //排序字段
        $this->assign('_order',$order);
        $sort = I('_sort','desc');        //排序类型
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where =' 1=1';
        
       /*  $start_date = I('start_date');
        $end_date   = I('end_date');
        $userid = I('userid');
        $category_id = I('category_id','0','intval');
        $content_name = I('content_name','','trim');
        if($start_date && $end_date){
            if($end_date<$start_date){
                $this->error('结束时间不能小于开始时间');
            }
        }
        if($start_date){
            $this->assign('start_date',$start_date);
            $start_date = date('YmdH',strtotime($start_date));
            $where .= " and date_time >='".$start_date."'";
        }
        if($end_date){
            $this->assign('end_date',$end_date);
            $end_date = date('YmdH',strtotime($end_date));
            $where .= " and date_time <='".$end_date."'";
        }
        $m_sysuser = new \Admin\Model\UserModel();
        if($userid){
            
            $this->assign('userid',$userid);
            $users = $m_sysuser->getUser(" and id=$userid",'id,username,remark');
           
            $userinfo = $users[0];
            if($userinfo){
                $where .=" and operators='".$userinfo['username']."' or operators='".$userinfo['remark']."'";
            }
            
        }
        if($category_id){
            $this->assign('category_id',$category_id);
            $where .=" and category_id=$category_id";
        } */
        $m_sysuser = new \Admin\Model\UserModel();
        $content_name = I('content_name','','trim');
        if($content_name){
            $this->assign('content_name',$content_name);
            $where .=" and content_name like '%".$content_name."%'";
        }
        
        $m_content_details_final = new \Admin\Model\ContDetFinalModel();
        $data = $m_content_details_final->getDataList($where,$orders,$start,$size);
        
        //分类
        $m_category = new \Admin\Model\CategoModel();
        $category_list = $m_category->getWhere('state = 1', 'id,name');
        array_unshift($category_list, array('id'=>'-1','name'=>'热点'),array('id'=>'-2','name'=>'点播'));
        
        //编辑
        
        $user_list = $m_sysuser->getUser(' and groupid=11');
        $this->assign('user_list',$user_list);
        $this->assign('category_list',$category_list);
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);
        $this->display('contandprom');
    }
    public function smallPlatWarn(){
        $areaModel  = new \Admin\Model\AreaModel();
        $area_arr = $areaModel->getAllArea();
        $this->assign('area', $area_arr);
    
        $size   = I('numPerPage',50);     //显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);          //当前页码
        $this->assign('pageNum',$start);
        $order = I('_order','spl.create_time'); //排序字段
        $this->assign('_order',$order);
        $sort = I('_sort','desc');        //排序类型
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start_date = I('start_date');    //搜索条件 开始日期
        $where =" 1=1 ";
        $hotel_name = I('hotel_name','','trim');
        $area_v = I('area_v');
        $start  = ( $start-1 ) * $size;
        if(!empty($hotel_name)){
            $where .=" and sht.name like '%".$hotel_name."%'";
            $this->assign('hotel_name',$hotel_name);
        }
        if ($area_v) {
            $this->assign('area_k',$area_v);
            $where .= "	AND spl.area_id = $area_v";
        }
    
        if($start_date){
            $where .=" and spl.create_time>='".$start_date." 00:00:00'";
            $this->assign('start_date',$start_date);
        }
        $end_date   = I('end_date');     //搜索条件  结束日期
        if($end_date){
            $where .= " and spl.create_time<='".$end_date." 23:59:59'";
            $this->assign('end_date',$end_date);
        }
        if(!empty($start_date) && !empty($end_date)){
            if($end_date<$start_date){
                $this->error('结束时间不能小于开始时间');
            }
        }
        $smWarn = new \Admin\Model\SmallPlaModel();
        $result = $smWarn->getWarnInfo($where,$orders,$start,$size);
        $result['list'] = $areaModel->areaIdToAareName($result['list']);
        $ind = $start;
        $small_warn = C('SMALL_WARN');
        foreach ($result['list'] as &$val) {
            $val['indnum'] = ++$ind;
            $val['state'] = $small_warn[$val['state']];
        }
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('smallpla');
    }
    /**
     * @desc 查看心跳上报历史数据
     */
    public function heartAllLog(){
        $size       = I('numPerPage',50);     //显示每页记录数
        $start      = I('pageNum',1);         //当前页码
        $order      = I('_order','id'); //排序字段        
        $sort       = I('_sort','desc');      //排序类型
        $orders     = $order.' '.$sort;
        $start = ($start-1)* $size;
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$start);
        $this->assign('_order',$order);
        $this->assign('_sort',$sort);
        $where =" 1=1 ";
        
        $start_date = I('start_date');        // 开始日期
        $end_date   = I('end_date');          // 结束日期
        $type       = I('type');              //设备类型
        $areaid     = I('areaid');            //城市id
        $hotel_name = I('hotel_name','','trim'); //酒楼名称
        $mac        = I('mac','','trim');     //mac地址
        
        if(!empty($start_date) && !empty($end_date)){
            if($end_date<$start_date){
                $this->error('结束时间不能小于开始时间');
            }
        }
        if(!empty($start_date)){
            $sql_start_date = str_replace('-', '', $start_date);
            $where .= " and date>={$sql_start_date}";
            $this->assign('start_date',$start_date);
        }
        if(!empty($end_date)){
            $sql_end_date = str_replace('-', '', $end_date);
            $where .=" and date<={$sql_end_date}";
            $this->assign('end_date',$end_date);
        }
        if(!empty($type)){
            $where .=" and type ={$type}";
            $this->assign('type',$type);
        }
        if(!empty($areaid)){
            $where .= " and area_id = {$areaid}";
            $this->assign('areaid',$areaid);
        }
        if(!empty($hotel_name)){
            $where .=" and hotel_name like '%{$hotel_name}%'";
            $this->assign('hotel_name',$hotel_name);
        }
        if(!empty($mac)){
            $where .=" and mac like '%{$mac}%'";
            $this->assign('mac',$mac);
        }
        //城市
        $userinfo = session('sysUserInfo');
        $gid = $userinfo['groupid'];

        $usergrp = new \Admin\Model\SysusergroupModel();
        $p_user_arr = $usergrp->getInfo($gid);
        $pcity = $p_user_arr['area_city'];
        if($p_user_arr['id'] == 1 ||
            $p_user_arr['area_city'] == 9999) {

            $this->assign('pusera', $p_user_arr);
        }else {
            $where .= "	AND area_id in ($pcity)";
        }
        $m_heart_all_log = new \Admin\Model\HeartAllLogModel();
        $result = $m_heart_all_log->getlist('*',$where,$orders,$start,$size);
        $m_hotel = new \Admin\Model\HotelModel();
        $m_box   = new \Admin\Model\BoxModel();
        
        foreach($result['list'] as $key=>$v){
            
            if($v['type']==1){
                $hotel_ext_info = $m_hotel->getHotelInfoByMac($v['mac']);
                $result['list'][$key]['tag'] = $hotel_ext_info['tag'];
            }else if($v['type']==2){
                
                $temp = $m_box->getInfo('tag'," mac='".$v['mac']."'",'');
                $result['list'][$key]['tag'] = $temp[0]['tag'];
            }
        }
        $device_type_arr = C('DEVICE_TYPE');
        //城市
        $m_area_info = new \Admin\Model\AreaModel();
        $area_arr = $m_area_info->getAllArea();
        $this->assign('area', $area_arr);
        $this->assign('device_type_arr',$device_type_arr);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('heartalllog');
    }
}
