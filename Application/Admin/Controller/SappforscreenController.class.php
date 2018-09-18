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
class SappforscreenController extends BaseController {
	public function __construct() {
		parent::__construct();
	}
	/**
	 * @desc  首页
	 */
	public function index(){
	    
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
	    $where = array();
	    $where['box.flag'] = 0;
	    $where['box.state'] =1;
	    $where['a.mobile_brand'] = array('neq','devtools');
	    $where['a.openid']       = array('neq','ofYZG4yZJHaV2h3lJHG5wOB9MzxE');
	    
	    $hotel_name = I('hotel_name','','trim');
	    if($hotel_name){
	        $where['hotel.name'] = array('like',"%$hotel_name%");
	        $this->assign('hotel_name',$hotel_name); 
	    }
	    $box_mac    = I('box_mac','','trim');
	    if($box_mac){
	        $where['a.box_mac'] = $box_mac;
	        $this->assign('box_mac',$box_mac);
	    }
	    $openid = I('openid','','trim');
	    if($openid){
	        $where['a.openid'] = $openid;
	        $this->assign('openid',$openid);
	    }
	    $create_time = I('create_time','','trim');
	    if($create_time){
	        $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$create_time.' 23:59:59'));
	        $this->assign('create_time',$create_time);
	    } 
	    
	    
	    
	    
	    
	    $fields = 'area.region_name,hotel.name hotel_name,room.name room_name,a.*';
	    $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();  
	    $list = $m_smallapp_forscreen_record->getList($fields,$where,$orders,$start,$size);
	    
	    foreach ($list['list'] as $key=>$v){
	        if(!empty($v['resource_size'])){
	            $list['list'][$key]['resource_size'] = formatBytes($v['resource_size']);
	        }else {
	            $list['list'][$key]['resource_size'] = '';
	        }
	        if(!empty($v['res_sup_time'])){
	            $list['list'][$key]['res_sup_time'] = date('Y-m-d H:i:s',intval($v['res_sup_time']/1000)) ;
	        }else {
	            $list['list'][$key]['res_sup_time'] = '';
	        }
	        if(!empty($v['res_sup_time']) && !empty($v['res_eup_time'])){
	            $list['list'][$key]['res_eup_time'] = ($v['res_eup_time'] - $v['res_sup_time']) /1000 ;
	        }else {
	            $list['list'][$key]['res_eup_time'] = '';
	        }
	        if(!empty($v['box_res_sdown_time'])){
	            $list['list'][$key]['box_res_sdown_time'] = date('Y-m-d H:i:s',intval($v['box_res_sdown_time']/1000)) ;
	        }else {
	            $list['list'][$key]['box_res_sdown_time'] = '';
	        }
	        if(!empty($v['box_res_sdown_time']) && !empty($v['box_res_edown_time'])){
	            $list['list'][$key]['box_res_edown_time'] = ($v['box_res_edown_time'] - $v['box_res_sdown_time']) /1000;
	        }else {
	            $list['list'][$key]['box_res_edown_time'] = '';
	        }
	        
	        $list['list'][$key]['imgs'] = json_decode(str_replace('\\', '', $v['imgs']),true);
	        switch ($v['action']){
	            case '1':
	                $list['list'][$key]['action_name'] = '发送呼码';
	                break;
	            case '2':
	                if($v['resource_type']==1) $list['list'][$key]['action_name'] = '滑动';
	                if($v['resource_type']==2) $list['list'][$key]['action_name'] = '视频投屏';
	                break;
	            case '3':
	                $list['list'][$key]['action_name'] = '退出投屏';
	                break;
	            case '4':
	                $list['list'][$key]['action_name'] = '多图投屏';
	                break;
	            case '5':
	                $list['list'][$key]['action_name'] = '视频点播';
	                break;
	            case '6':
	                $list['list'][$key]['action_name'] = '广告跳转';
	                break;
	            case '7':
	                $list['list'][$key]['action_name'] = '点击互动游戏';
	                break;
	            default :
	                $list['list'][$key]['action_name'] = '图片投屏';
	                break;
	        }
	        
	    }
	    $this->assign('list',$list['list']);
	   	$this->assign('oss_host',C('OSS_HOST_NEW'));
	   	$this->assign('page',$list['page']);   
	    $this->display('Report/sappforscreen');
	}
	/**
	 * @desc 删除永峰测试数据
	 */
	public function delTestRecord(){
	    $hotel_id = array(7,791);
	    $fields = "a.box_mac";
	    $where = array();
	    $where['hotel.id'] = array('in',$hotel_id);
	    $group = 'a.box_mac';
	    $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel(); 
	    $list = $m_smallapp_forscreen_record->getWhere($fields, $where,  $limit='', $group);
	    //echo $m_smallapp_forscreen_record->getLastSql();exit;
	    
	    foreach($list as $key=>$v){
	        $where = array();
	        $where['box_mac'] = $v['box_mac'];
	        $m_smallapp_forscreen_record->delWhere($where, $order='', $limit='');
	    }
	    $this->output('删除成功', 'sappforscreen/index', 2);
	}
	/**
	 * @desc 互动游戏日志
	 */
	public function gameLog(){
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
	    $where = array();
	    $where['box.flag'] = 0;
	    $where['box.state'] =1;
	     
	     
	    $hotel_name = I('hotel_name','','trim');
	    if($hotel_name){
	        $where['hotel.name'] = array('like',"%$hotel_name%");
	        $this->assign('hotel_name',$hotel_name);
	    }
	    $box_mac    = I('box_mac','','trim');
	    if($box_mac){
	        $where['a.box_mac'] = $box_mac;
	        $this->assign('box_mac',$box_mac);
	    }
	    
	    $create_time = I('create_time','','trim');
	    if($create_time){
	        $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$create_time.' 23:59:59'));
	        $this->assign('create_time',$create_time);
	    }
	    
	    $m_turntable_log = new \Admin\Model\Smallapp\TurntableLogModel(); 
	    $fields = 'area.region_name,hotel.name hotel_name,room.name room_name,a.*';
	    
	    $list = $m_turntable_log->getList($fields,$where,$orders,$start,$size);
	    
	    $m_turntable_detail = new \Admin\Model\Smallapp\TurntableDetailModel();
	    foreach($list['list'] as $key=>$v){
	        if(!empty($v['orggame_time'])){
	            $list['list'][$key]['orggame_time'] = date('Y-m-d H:i:s',intval($v['orggame_time']/1000));
	        }else {
	            $list['list'][$key]['orggame_time'] = '';
	        }
	        if(!empty($v['orggame_time']) && !empty($v['box_orggame_time'])){
	            $list['list'][$key]['box_orggame_time'] = ($v['box_orggame_time'] - $v['orggame_time']) /1000;
	        }else {
	            $list['list'][$key]['box_orggame_time'] = '';
	        }
	        if(!empty($v['startgame_time'])){
	            $list['list'][$key]['startgame_time'] = date('Y-m-d H:i:s',intval($v['startgame_time']/1000));
	        }else {
	            $list['list'][$key]['startgame_time'] = '';
	        }
	        if(!empty($v['startgame_time']) && !empty($v['box_startgame_time'])){
	            $list['list'][$key]['box_startgame_time'] = ($v['box_startgame_time'] - $v['startgame_time']) /1000;
	        }else {
	            $list['list'][$key]['box_startgame_time'] = '';
	        }
	        
	        $nums = $m_turntable_detail->countNums(array('activity_id'=>$v['activity_id']));
	        $list['list'][$key]['nums']= $nums+1;
	    }
	    
	    $this->assign('list',$list['list']);
	    $this->assign('page',$list['page']);
	    $this->display('Report/turntablelog');
	    
	}
	/**
	 * @desc 查看参加游戏的详细数据
	 */
	public function detail(){
	    $activity_id = I('get.activity_id');
	    $m_turntable_log = new \Admin\Model\Smallapp\TurntableLogModel();
	    
	    $fields = "openid,mobile_brand,mobile_model,'发起人' as `person_type`, '0' as join_time, '0' as box_join_time";
	    $where = array();
	    $where['activity_id'] = $activity_id;
	    $fq_info = $m_turntable_log->getOne($fields, $where);
	    
	    $m_turntable_detail = new \Admin\Model\Smallapp\TurntableDetailModel();
	    $fields = "openid,mobile_brand,mobile_model,'参与人' as `person_type`,join_time,box_join_time";
	    $cy_info = $m_turntable_detail->getWhere($fields,$where);
	    
	    array_unshift($cy_info, $fq_info);
	    foreach($cy_info as $key=>$v){
	        if(!empty($v['join_time'])){
	            $cy_info[$key]['join_time'] = date('Y-m-d H:i:s',intval($v['join_time']/1000));
	        }else {
	            $cy_info[$key]['join_time'] = '';
	        }
	        if(!empty($v['join_time']) && !empty($v['box_join_time'])){
	            $cy_info[$key]['box_join_time'] = ($v['box_join_time'] - $v['join_time']) /1000;
	        }else {
	            $cy_info[$key]['box_join_time'] = '';
	        }
	        
	        
	    }
	    
	    $this->assign('list',$cy_info);
	    
	    $this->display('Report/turtbdetail');
	}
	
	
	/**
	 * @desc 删除永峰测试数据
	 */
	public function delTestGamelog(){
	    $hotel_id = array(7,791);
	    $fields = "a.box_mac";
	    $where = array();
	    $where['hotel.id'] = array('in',$hotel_id);
	    $group = 'a.box_mac';
	    $m_turntable_log = new \Admin\Model\Smallapp\TurntableLogModel(); 
	    $list = $m_turntable_log->getWhere($fields, $where,  $limit='', $group);
	    //echo $m_smallapp_forscreen_record->getLastSql();exit;
	    //print_r($list);exit;   
	    foreach($list as $key=>$v){
	        $where = array();
	        $where['box_mac'] = $v['box_mac'];
	        $m_turntable_log->delWhere($where, $order='', $limit='');
	        //$m_smallapp_forscreen_record->delWhere($where, $order='', $limit='');
	    }
	    $this->output('删除成功', 'sappforscreen/gamelog', 2);
	}
    /**
     * @desc 小程序码显示日志
     */
	public function suncodeLog(){
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
	    $where = array();
	    $where['box.flag'] = 0;
	    $where['box.state'] =1;
	     
	     
	    $hotel_name = I('hotel_name','','trim');
	    if($hotel_name){
	        $where['hotel.name'] = array('like',"%$hotel_name%");
	        $this->assign('hotel_name',$hotel_name);
	    }
	    $box_mac    = I('box_mac','','trim');
	    if($box_mac){
	        $where['a.box_mac'] = $box_mac;
	        $this->assign('box_mac',$box_mac);
	    }
	    
	    $create_time = I('create_time','','trim');
	    if($create_time){
	        $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$create_time.' 23:59:59'));
	        $this->assign('create_time',$create_time);
	    }
	    
	    $fields = 'area.region_name,hotel.name hotel_name,room.name room_name,media.name media_name,media.duration,a.*';
	    $m_suncode_log = new \Admin\Model\Smallapp\SuncodeLogModel();
	    $list = $m_suncode_log->getList($fields,$where,$orders,$start,$size);
	    
	    $this->assign('list',$list['list']);
	    $this->assign('page',$list['page']);
	    $this->display('Report/suncodelog');
	}
	public function delSuncodeLog(){
	    $hotel_id = array(7,791);
	    $fields = "a.box_mac";
	    $where = array();
	    $where['hotel.id'] = array('in',$hotel_id);
	    $group = 'a.box_mac';
	    $m_suncode_log = new \Admin\Model\Smallapp\SuncodeLogModel();
	    $list = $m_suncode_log->getWhere($fields, $where,  $limit='', $group);
	    
	    foreach($list as $key=>$v){
	        $where = array();
	        $where['box_mac'] = $v['box_mac'];
	        $m_suncode_log->delWhere($where, $order='', $limit='');
	        
	    }
	    $this->output('删除成功', 'sappforscreen/suncodeLog', 2);
	}
	/**
	 * @desc 小程序网络状况监测
	 */
	public function staticnet(){
	    $ajaxversion   = I('ajaxversion',0,'intval');//1 版本升级酒店列表
	    $size   = I('numPerPage',50);//显示每页记录数
	    $this->assign('numPerPage',$size);
	    $start = I('pageNum',1);
	    $this->assign('pageNum',$start);
	    $order = I('_order','a.hotel_id');
	    $this->assign('_order',$order);
	    $sort = I('_sort','asc');
	    $this->assign('_sort',$sort);
	    $orders = $order.' '.$sort;
	    $start  = ( $start-1 ) * $size;
	    
	    $m_static_net = new \Admin\Model\Smallapp\StaticNetModel();
	    $fields = "a.id,a.hotel_id,hotel.name hotel_name,area.region_name";
	    $where =array();
	    $yesterday = date('Y-m-d',strtotime('-1 day'));
	    $start_date = I('start_date','','trim') ? I('start_date','','trim') : $yesterday;
	    $hotel_name = I('hotel_name','','trim');
	    $end_date   = I('end_date','','trim') ? I('end_date','','trim') : $yesterday;
	    $is_4g      = I('is_4g','0','intval');
	    $where['a.static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
	    if($hotel_name) $where['hotel.name'] = array('like',"%$hotel_name%");
	    if(!empty($is_4g)){
	        $where['hotel.is_4g'] = $is_4g;
	        
	    }
	    $group = "a.hotel_id";
	    
	    $hotel_list = $m_static_net->getWhere($fields,$where,$orders,$group,$start,$size);

	    foreach($hotel_list['list'] as $key=>$v){
	        $map = array();
	        $map['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
	        $map['hotel_id'] = $v['hotel_id'];
	        $fields = 'sum(`box_donw_nums`) box_donw_nums,sum(`res_size`) res_size,
	                   sum(`order_times`) order_times,sum(`avg_down_speed`) avg_down_speed,
	                   sum(`avg_delay_time`) avg_delay_time,max(`max_down_speed`) max_down_speed,
	                   min(`min_down_speed`) min_down_speed,max(`max_delay_times`) max_delay_times,
	                   min(`min_delay_times`) min_delay_times';
	        $ret  = $m_static_net->searchList($fields, $map);
	        $nums = $m_static_net->countWhere($map);
	        $hotel_list['list'][$key]['box_down_nums'] = $ret[0]['box_donw_nums'];              //总下载次数
	        $hotel_list['list'][$key]['res_sizev']     = formatBytes($ret[0]['res_size']);      //总资源大小
	        $hotel_list['list'][$key]['order_times']   = $ret[0]['order_times'];                //总质量次数
	        
	        $hotel_list['list'][$key]['avg_down_speed']= formatBytes($ret[0]['avg_down_speed'] / $nums).'/S';
	        $hotel_list['list'][$key]['avg_delay_time']= $ret[0]['avg_delay_time'] /$nums;
	        $hotel_list['list'][$key]['max_down_speed']= formatBytes($ret[0]['max_down_speed']) .'/S';
	        $hotel_list['list'][$key]['min_down_speed']= formatBytes($ret[0]['min_down_speed']) .'/S';
	        $hotel_list['list'][$key]['max_delay_times']= $ret[0]['max_delay_times'];
	        $hotel_list['list'][$key]['min_delay_times']= $ret[0]['min_delay_times'];
	        
	        
	    }
	    $this->assign('list',$hotel_list['list']);
	    $this->assign('page',$hotel_list['page']);
	    $this->assign('start_date',$start_date);
	    $this->assign('end_date',$end_date);
	    $this->assign('hotel_name',$hotel_name);
	    $this->assign('is_4g',$is_4g);
	    $this->display('Report/staticnet');
	}
}