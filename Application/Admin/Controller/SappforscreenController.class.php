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
	    $end_time    = I('end_time','','trim');
	    
	    if($create_time && $end_time){
	        $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
	        $this->assign('create_time',$create_time);
	        $this->assign('end_time',$end_time);
	    } else if($create_time && empty($end_time)){
	        $end_time = date('Y-m-d');
	        $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
	        $this->assign('create_time',$create_time);
	        $this->assign('end_time',date('Y-m-d'));
	    }else if(empty($create_time) && !empty($end_time)){
	        $create_time = '2018-07-23';
	        $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
	        $this->assign('create_time',$create_time);
	        $this->assign('end_time',$end_time);
	    }
	    
	    
	    $fields = 'user.avatarUrl,user.nickName,area.region_name,hotel.name hotel_name,room.name room_name,a.*';
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
	            case '8':
	                $list['list'][$key]['action_name'] = '重投';
	                break;
	            case '9':
	                $list['list'][$key]['action_name'] = '手机呼大码';
	                break;
	            case '11':
	                $list['list'][$key]['action_name'] = '发现点播图片';
	                break;
                case '12':
                    $list['list'][$key]['action_name'] = '发现点播视频';
                    break;
                case '21':
                    $list['list'][$key]['action_name'] = '查看点播视频';
	                break;
                case '22':
                    $list['list'][$key]['action_name'] = '查看发现视频';
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
	/**
	 * @desc 小程序数据统计
	 */
	public function interactStatic(){
	    $size   = I('numPerPage',50);//显示每页记录数
	    $this->assign('numPerPage',$size);
	    
	    $start = I('pageNum',1);
	    $pageNum = $start;
	    $this->assign('pageNum',$start);
	    $order = I('_order','a.hotel_id');
	    $this->assign('_order',$order);
	    $sort = I('_sort','asc');
	    $this->assign('_sort',$sort);
	    $orders = $order.' '.$sort;
	    $start  = ( $start-1 ) * $size;
	    
	    $where = array();
	    $start_date = I('start_date') ? I('start_date')." 00:00:00" : date('Y-m-d 00:00:00',strtotime('-1 day'));
	    $end_date   = I('end_date')   ? I('end_date')." 23:59:59"   : date('Y-m-d 23:59:59',strtotime('-1 day'));
	    $area_id    = I('area_id',0,'intval');
	    if($area_id){
	        $where['area.id'] = $area_id;
	    }
	    
	    //获取酒楼
	    $m_forscreen_record = new \Admin\Model\Smallapp\ForscreenRecordModel(); 
	    $fileds = "hotel.id hotel_id,area.region_name ,hotel.name hotel_name";
	    
	    
	    $where['a.create_time'] = array(array('EGT',$start_date),array('ELT',$end_date));
	    $where['hotel.state'] = 1;
	    $where['hotel.flag']  = 0;
	    $where['box.state']   = 1;
	    $where['box.flag']    = 0;
	    $where['a.mobile_brand'] = array('neq','devtools');
	    $group = "hotel.id";
	    
	    $list = $m_forscreen_record->getStaticList($fileds, $where, $order, $group, $start, $size,$pageNum,$area_id);
	    
	    
	    $m_box = new \Admin\Model\BoxModel();
	    $m_heart_all_log = new \Admin\Model\HeartAllLogModel();
	    $m_turntale_log = new \Admin\Model\Smallapp\TurntableLogModel();
	    $m_turntale_detail = new \Admin\Model\Smallapp\TurntableDetailModel();
	    $m_static_net = new \Admin\Model\Smallapp\StaticNetModel();
	    foreach($list['list'] as $key=>$v){
	        //总屏幕数
	        $map = array();
	        $map['hotel.id']    = $v['hotel_id'];
	        $map['hotel.state'] = 1;
	        $map['hotel.flag']  = 0;
	        $map['box.state']   = 1;
	        $map['box.flag']    = 0;
	        $box_nums = $m_box->countNums($map);
	        $list['list'][$key]['all_num'] = $box_nums;
	        
	        //获取当前酒楼所有的盒子
	        
	        $map =" 1 and h.id=".$v['hotel_id']." and h.state=1 and h.flag=0 and b.state=1 and b.flag=0"; 
	       
	        $box_list = $m_box->isHaveMac('b.mac box_mac', $map);
	        
	        //获取当前酒楼的平均网速
	        $map = array();
	        $map['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
	        $map['hotel_id'] = $v['hotel_id'];
	        $fields = 'sum(`avg_down_speed`) avg_down_speed';
	        $ret  = $m_static_net->searchList($fields, $map);
	        $nums = $m_static_net->countWhere($map);
	        $avg_speed = $ret[0]['avg_down_speed'] / 1000 / $nums;
	        
	        
	        $lunch_online_box_num = 0;     //上午在线屏幕数
	        $dinner_online_box_num = 0;    //下午在线屏幕数
	        
	        $lunch_can_forscreen_box_num = 0;  //上午可投屏总数
	        $dinner_can_forscreen_box_num = 0; //下午可投屏总数
	        $s_dt= date('Ymd',strtotime($start_date));
	        $e_dt = date('Ymd',strtotime($end_date));
	        foreach($box_list as $kk=>$vv){
	            $sql ="select sum(hour2+hour3+hour4+hour5+hour6+hour7+hour8+hour9+hour10+hour11+hour12+hour13+hour14) as lunch_heart_num
	                   from savor_heart_all_log where  date>='".$s_dt
	                   ."' and date<='".$e_dt."' and mac='".$vv['box_mac']."' and type=2";
	            $ret = M()->query($sql);
	            $nums = intval($ret[0]['lunch_heart_num']);
	            if(!empty($nums)) $lunch_online_box_num ++;                           //午饭在线屏幕数
	            if($avg_speed>200 && !empty($nums)) $lunch_can_forscreen_box_num ++;  //午饭可投屏总数
	            
	            
	            
	            $sql ="select sum(hour15+hour16+hour17+hour18+hour19+hour20+hour21+hour22+hour23+hour0+hour1) as dinner_heart_num
	                   from savor_heart_all_log where  date>='".$s_dt
	                   ."' and date<='".$e_dt."' and mac='".$vv['box_mac']."' and type=2";
	            $ret = M()->query($sql);
	            $nums = intval($ret[0]['dinner_heart_num']);
	            if(!empty($nums)) $dinner_online_box_num ++;                          //晚饭在线屏幕数
	            
	            if($avg_speed>200 &&!empty($nums)) $dinner_can_forscreen_box_num++;   //晚饭可投屏总数
	            
	            
	        }
	        $lunch_interact_mobile_nums  = 0;                                         //上午互动手机数
	        $dinner_interact_mobile_nums = 0;                                         //下午互动手机数
	        
	        //午饭互动手机
	        $sql = "select a.id,a.openid from savor_smallapp_forscreen_record a
	                left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']." 
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15
	                and a.mobile_brand!='devtools' group by a.openid";
	        $ret_f = M()->query($sql);
	        //$lunch_interact_mobile_nums += count($ret);
	        $sql = "select a.id,a.openid from savor_smallapp_turntable_log a 
                    left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']." 
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15
	                group by a.openid";
	        $ret_t = M()->query($sql);
	        //$lunch_interact_mobile_nums += count($ret);
	        $sql ="select a.id,a.openid from savor_smallapp_turntable_detail a
                    left join savor_smallapp_turntable_log b on a.activity_id=b.activity_id
                    left join savor_box box on b.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15
	                group by openid";
	        $ret_td = M()->query($sql);
	        $ret = array_merge($ret_f,$ret_t,$ret_td);
	        if(empty($ret)){
	            $lunch_interact_mobile_nums = 0;
	        }else {
	            $ret = assoc_unique($ret, 'openid');
	            $lunch_interact_mobile_nums = count($ret);
	        }
	        
	        
	        //晚饭互动手机
	        $sql = "select a.id,a.openid from savor_smallapp_forscreen_record a
	                left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))
	                and a.mobile_brand!='devtools' group by a.openid";
	        $ret_f = M()->query($sql);
	        //$dinner_interact_mobile_nums += count($ret);
	        $sql = "select a.id,a.openid from savor_smallapp_turntable_log a
                    left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))
	                group by a.openid";
	        $ret_t = M()->query($sql);
	        //$dinner_interact_mobile_nums += count($ret);
	        $sql ="select a.id,a.openid from savor_smallapp_turntable_detail a
                    left join savor_smallapp_turntable_log b on a.activity_id=b.activity_id
                    left join savor_box box on b.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))
	                group by openid";
	        $ret_td = M()->query($sql);
	        $ret = array_merge($ret_f,$ret_t,$ret_td);
	        if(empty($ret)){
	            $dinner_interact_mobile_nums = 0;
	        }else {
	            $ret = assoc_unique($ret, 'openid');
	            $dinner_interact_mobile_nums = count($ret);
	        }
	        //$dinner_interact_mobile_nums += count($ret);
	        /* $map = array();
	        $map['hotel.id'] = $v['hotel_id'];
	        $map['a.create_time'] = array(array('EGT',$start_date),array('ELT',$end_date));
	        $map['a.mobile_brand'] = array('neq','devtools');
	        $map['box.flag'] = 0;
	        $map['box.state']= 1;
	        $forscreen_mobile_arr = $m_forscreen_record->getWhere('a.openid,a.create_time', $map);
	        
	        $lunch_forscreen_mobile_arr = $dinner_forscreen_mobile_arr = array();
	        foreach($forscreen_mobile_arr as $f=>$fv){
	           $c_time = substr($fv['create_time'],11);
	           if($c_time>='02:00:00' && $c_time<='14:59:59'){
	               $lunch_forscreen_mobile_arr[] = $fv['openid'];
	           }else {
	               $dinner_forscreen_mobile_arr[] = $fv['openid'];
	           }
	        }
	        //发起游戏手机
	        $map = array();
	        $map['hotel.id'] = $v['hotel_id'];
	        $map['box.flag'] = 0;
	        $map['box.state']= 1;
	        $map['a.create_time'] = array(array('EGT',$start_date),array('ELT',$end_date));
	        $turntable_mobile_arr = $m_turntale_log->getWhere('a.openid,a.create_time', $map);
	        $lunch_turntable_mobile_arr = $dinner_turntable_mobile_arr = array();
	        foreach($turntable_mobile_arr as $tk=>$tv){
	           $c_time = substr($tv['create_time'],11);
	           if($c_time>='02:00:00' && $c_time<='14:59:59'){
	               $lunch_turntable_mobile_arr[] = $tv['openid'];
	           }else {
	               $dinner_turntable_mobile_arr[] = $tv['openid'];
	           }
	        }
	        //参与手机游戏
	        $map = array();
	        $map['hotel.id'] = $v['hotel_id'];
	        $map['box.flag'] = 0;
	        $map['box.state']= 1;
	        $map['a.create_time'] = array(array('EGT',$start_date),array('ELT',$end_date));
	        $turntable_detail_mobile_arr = $m_turntale_detail->getInfos('a.openid,a.create_time', $map);
	        $lunch_turntable_detail_mobile_arr = $dinner_turntable_detail_mobile_arr = array();

	        foreach($turntable_detail_mobile_arr as $tdk=>$tdv){
	            $c_time = substr($tdv['create_time'],11);
	            if($c_time>='02:00:00' && $c_time<='14:59:59'){
	                $lunch_turntable_detail_mobile_arr[] = $tdv['openid'];
	            }else {
	                $dinner_turntable_detail_mobile_arr[] = $tdv['openid'];
	            }
	        }
	        //午饭互动总手机
	        $lunch_interact_mobile_arr = array_keys(array_flip($lunch_forscreen_mobile_arr)+array_flip($lunch_turntable_mobile_arr)+array_flip($lunch_turntable_detail_mobile_arr));
	        $lunch_interact_mobile_nums= count($lunch_interact_mobile_arr);
            
	        //晚饭互动手机 
	        $dinner_interact_mobile_arr = array_keys(array_flip($dinner_forscreen_mobile_arr)+array_flip($dinner_turntable_mobile_arr)+array_flip($dinner_turntable_detail_mobile_arr));
	        $dinner_interact_mobile_nums = count($dinner_interact_mobile_arr); */

	        //互动饭局数
	        $lunch_fanju_num = 0;   //午饭总饭局数
	        $dinner_fanju_num =0;   //晚饭总饭局数
	        
	        //总互动次数
	        $lunch_interact_num = 0 ;         //午饭总互动次数
	        $dinner_interact_num = 0;         //晚饭总互动次数
	        
	        $lunch_pic_num      = 0;
	        $lunch_video_num    = 0;
	        $lunch_db_num       = 0;
	        $lunch_birthday_num = 0;
	        $lunch_game_num     = 0;
	         
	        $dinner_pic_num      = 0;
	        $dinner_video_num    = 0;
	        $dinner_db_num       = 0;
	        $dinner_birthday_num = 0;
	        $dinner_game_num     = 0;
	        
	        $sql = "select a.action,a.resource_type,forscreen_char from savor_smallapp_forscreen_record a
	                left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']." 
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15
	                and a.mobile_brand!='devtools'";
	        $ret = M()->query($sql);
	       
	        
	        foreach($ret as $rk=>$rv){
	            if(($rv['action']==2 && $rv['resource_type']==1) || $rv['action']==4 ){//图片
	                $lunch_pic_num ++;
	            }else if($rv['action']==2 && $rv['resource_type']==2){//视频
	                $lunch_video_num ++;
	            }else if($rv['action']==5 && $rv['forscreen_char']!='Happy Birthday'){
	                $lunch_db_num ++;
	            }else if($rv['action']==5 && $rv['forscreen_char'] =='Happy Birthday'){
	                $lunch_birthday_num ++;
	            }
	        }
	        
	        $lunch_interact_num += $lunch_pic_num +$lunch_video_num +$lunch_db_num+$lunch_birthday_num;
	        
            $sql = "select count(a.id) as nums from savor_smallapp_turntable_log a 
                    left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']." 
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15";
             $ret = M()->query($sql);
             $lunch_game_num += $ret[0]['nums']; 
             $lunch_interact_num +=$ret[0]['nums']; 
             
             $sql ="select count(a.id) as nums from savor_smallapp_turntable_detail a 
                    left join savor_smallapp_turntable_log b on a.activity_id=b.activity_id
                    left join savor_box box on b.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']." 
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15";
             $ret = M()->query($sql);
             $lunch_game_num +=$ret[0]['nums'];
             $lunch_interact_num +=$ret[0]['nums'];
            
             //午饭投屏电视数
             $lunch_forscreen_box_num = 0;
             //晚饭投屏电视数
             $dinner_forscreen_box_num= 0;
              
             $sql = "select a.id,a.box_mac from savor_smallapp_forscreen_record a
	                left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15
	                and a.mobile_brand !='devtools'
	                group by a.box_mac";
             $ret_f = M()->query($sql);
             
             //$lunch_forscreen_box_num += count($ret);
             $sql = "select a.id,a.box_mac from savor_smallapp_turntable_log a
                left join savor_box box on a.box_mac=box.mac
                left join savor_room room on box.room_id=room.id
                left join savor_hotel hotel on room.hotel_id=hotel.id
                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15
                group by a.box_mac";
             $ret_t = M()->query($sql);
             
             $ret = array_merge($ret_f,$ret_t);
             
             if(empty($ret)){
                 $lunch_forscreen_box_num =0;
             }else {
                 
                 $ret = assoc_unique($ret, 'box_mac');
                 $lunch_forscreen_box_num = count($ret);
             }
             $lunch_fanju_num = $lunch_forscreen_box_num;
              
             $sql = "select a.id,a.box_mac from savor_smallapp_forscreen_record a
	                left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))
	                and a.mobile_brand !='devtools'
	                group by a.box_mac";
             $ret_f = M()->query($sql);
             
             $dinner_forscreen_box_num += count($ret);
             $sql = "select a.id,a.box_mac from savor_smallapp_turntable_log a
                left join savor_box box on a.box_mac=box.mac
                left join savor_room room on box.room_id=room.id
                left join savor_hotel hotel on room.hotel_id=hotel.id
                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))
                group by a.box_mac";
             $ret_t = M()->query($sql);
             $ret = array_merge($ret_f,$ret_t);
             
             if(empty($ret)){
                 $dinner_forscreen_box_num = 0;
             }else {
                 $ret = assoc_unique($ret, 'box_mac');
                 $dinner_forscreen_box_num = count($ret); 
             }
             $dinner_fanju_num =  $dinner_forscreen_box_num;
	         $sql = "select a.action,a.resource_type,forscreen_char from savor_smallapp_forscreen_record a
	                left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']." 
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))
	                and a.mobile_brand!='devtools'";
	        
	        $ret = M()->query($sql);
	        
	        foreach($ret as $rk=>$rv){
	            if(($rv['action']==2 && $rv['resource_type']==1) || $rv['action']==4 ){//图片
	                $dinner_pic_num ++;
	            }else if($rv['action']==2 && $rv['resource_type']==2){//视频
	                $dinner_video_num ++;
	            }else if($rv['action']==5 && $rv['forscreen_char']!='Happy Birthday'){
	                $dinner_db_num ++;
	            }else if($rv['action']==5 && $rv['forscreen_char'] =='Happy Birthday'){
	                $dinner_birthday_num ++;
	            }
	        }
	        $dinner_interact_num += $dinner_pic_num+$dinner_video_num +$dinner_db_num+$dinner_birthday_num;
	        
            $sql = "select count(a.id) as nums from savor_smallapp_turntable_log a
                left join savor_box box on a.box_mac=box.mac
                left join savor_room room on box.room_id=room.id
                left join savor_hotel hotel on room.hotel_id=hotel.id
                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))";
            $ret = M()->query($sql);
            $dinner_game_num +=$ret[0]['nums'];
            $dinner_interact_num +=$ret[0]['nums'];
	        
	        
	        $sql ="select count(a.id) as nums from savor_smallapp_turntable_detail a 
                    left join savor_smallapp_turntable_log b on a.activity_id=b.activity_id
                    left join savor_box box on b.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
                    and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
                    and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))";
	        $ret = M()->query($sql);
	        $dinner_game_num +=$ret[0]['nums']; 
	        $dinner_interact_num +=$ret[0]['nums'];
	        
	        //午饭/晚饭饭局转换率
	        if(empty($lunch_can_forscreen_box_num)) $lunch_fanju_rate = 0;
	        else $lunch_fanju_rate = round($lunch_fanju_num / $lunch_can_forscreen_box_num,2);
	        
	        if(empty($dinner_can_forscreen_box_num)) $dinner_fanju_rate = 0;
	        else $dinner_fanju_rate= round($dinner_fanju_num / $dinner_can_forscreen_box_num,2);

	        //午饭/晚饭人员参与率
	        if(empty($lunch_can_forscreen_box_num)) $lunch_join_rate = 0;
	        else  $lunch_join_rate = round($lunch_interact_num /($lunch_can_forscreen_box_num*8),2);
	        
	        if(empty($dinner_can_forscreen_box_num)) $dinner_join_rate =0;
	        else $dinner_join_rate = round($dinner_interact_num /($dinner_can_forscreen_box_num*8),2);
	        
	        //午饭/晚饭牵引率
	        if(empty($lunch_forscreen_box_num))$lunch_pullability_rate = 0;
	        else $lunch_pullability_rate = round($lunch_interact_mobile_nums / $lunch_forscreen_box_num,2);
	            
	        if(empty($dinner_forscreen_box_num)) $dinner_pullability_rate=0;
	        else $dinner_pullability_rate= round($dinner_interact_mobile_nums / $dinner_forscreen_box_num,2);
	        
	        
	        //午饭单机互动数 晚饭单机互动数
	        
	        if(empty($lunch_interact_mobile_nums)) $lunch_alone_interact_num=0;
	        else  $lunch_alone_interact_num = round($lunch_interact_num/$lunch_interact_mobile_nums,2);
	        
	        if(empty($dinner_interact_mobile_nums)) $dinner_alone_interact_num = 0;
	        else  $dinner_alone_interact_num= round($dinner_interact_num/$dinner_interact_mobile_nums,2);
	        
	        //午饭单屏互动数 //晚饭单凭互动数
	        if(empty($lunch_fanju_num)) $lunch_box_interact_num =0;
	        else $lunch_box_interact_num = round($lunch_interact_num/$lunch_fanju_num,2); 
	        
	        if(empty($dinner_fanju_num)) $dinner_box_interact_num = 0;
	        else $dinner_box_interact_num= round($dinner_interact_num/$dinner_fanju_num,2);
	        
	        
	        //午饭/晚饭投照片转换率
	        if(empty($lunch_can_forscreen_box_num)){
	            $lunch_pic_num_rate =0;
	            
	            $lunch_video_num_rate =0 ;
	            $lunch_db_num_rate = 0;
	            $lunch_birthday_num_rate =0;
	            $lunch_game_num_rate = 0;
	        }
	        else{
	            $lunch_pic_num_rate = round($lunch_pic_num / $lunch_can_forscreen_box_num,2);
	            $lunch_video_num_rate = round($lunch_video_num / $lunch_can_forscreen_box_num,2);
	            $lunch_db_num_rate   = round($lunch_db_num / $lunch_can_forscreen_box_num,2);
	            $lunch_birthday_num_rate = round($lunch_birthday_num /$lunch_can_forscreen_box_num,2);
	            $lunch_game_num_rate = round($lunch_game_num /$lunch_can_forscreen_box_num,2);
	        }
	        
	        if(empty($dinner_can_forscreen_box_num)){
	            $dinner_pic_num_rate =0;
	            $dinner_video_num_rate = 0;
	            $dinner_db_num_rate =0;
	            $dinner_birthday_num_rate =0;
	            $dinner_game_num_rate =0;
	        }
	        else {
	            $dinner_pic_num_rate = round($dinner_pic_num / $dinner_can_forscreen_box_num,2);
	            $dinner_video_num_rate= round($dinner_video_num / $dinner_can_forscreen_box_num,2);
	            $dinner_db_num_rate  = round($dinner_db_num /$dinner_can_forscreen_box_num,2);
	            $dinner_birthday_num_rate= round($dinner_birthday_num/$dinner_can_forscreen_box_num,2);
	            $dinner_game_num_rate= round($dinner_game_num/$dinner_can_forscreen_box_num,2);
	        }
	        $list['list'][$key]['data_static'][] = array('box_num'=>$lunch_online_box_num,
	                                                     'online_box_num'=>$lunch_online_box_num,
	                                                     'interact_mobile_nums'=>$lunch_interact_mobile_nums,
	                                                     'can_forscreen_box_num'=>$lunch_can_forscreen_box_num,
	                                                     'fanju_num'=>$lunch_fanju_num,
	                                                     'fanju_rate'=>$lunch_fanju_rate,
	                                                     'interact_num'=>$lunch_interact_num,
	                                                     'join_rate'  =>$lunch_join_rate,
	                                                     'pullability_rate'=>$lunch_pullability_rate,
	                                                     'alone_interact_num'=>$lunch_alone_interact_num,
	                                                     'box_interact_num'=>$lunch_box_interact_num, 
	                                                     'pic_num_rate'=>$lunch_pic_num_rate,
	                                                     'video_num_rate'=>$lunch_video_num_rate,
	                                                     'db_num_rate'=>$lunch_db_num_rate,
	                                                     'birthday_num_rate'=>$lunch_birthday_num_rate,
	                                                     'game_num_rate'=>$lunch_game_num_rate
	        );
	        
	        $list['list'][$key]['data_static'][] = array('box_num'=>$dinner_online_box_num,
                                        	             'online_box_num'=>$dinner_online_box_num,
	                                                     'interact_mobile_nums'=>$dinner_interact_mobile_nums,
                                        	             'can_forscreen_box_num'=>$dinner_can_forscreen_box_num,
                                        	             'fanju_num'=>$dinner_fanju_num,
                                        	             'fanju_rate'=>$dinner_fanju_rate,
                                        	             'interact_num'=>$dinner_interact_num,
                                        	             'join_rate'  =>$dinner_join_rate,
	                                                     'pullability_rate'=>$dinner_pullability_rate,
	                                                     'alone_interact_num'=>$dinner_alone_interact_num,
	                                                     'box_interact_num'=>$dinner_box_interact_num,
                                        	             'pic_num_rate'=>$dinner_pic_num_rate,
                                        	             'video_num_rate'=>$dinner_video_num_rate,
                                        	             'db_num_rate'=>$dinner_db_num_rate,
                                        	             'birthday_num_rate'=>$dinner_birthday_num_rate,
                                        	             'game_num_rate'=>$dinner_game_num_rate
	        ); 
	    }
	    
	    $m_area = new \Admin\Model\AreaModel();
	    $area_list = $m_area->getAllArea();
	    $this->assign('area_id',$area_id);
	    $this->assign('area_list',$area_list);
	    $this->assign('start_date',substr($start_date, 0,11));
	    $this->assign('end_date',substr($end_date, 0,11));
	    $this->assign('list',$list['list']);
	    $this->assign('page',$list['page']);
	    $this->display('Report/interact');
	}
	/**
	 * @用户公开信息审核
	 */
	public function publicCheck(){
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
	    $m_public = new \Admin\Model\Smallapp\PublicModel();
	    $fields = 'user.nickName,a.id,a.forscreen_id,a.openid,a.box_mac,a.res_type,a.is_pub_hotelinfo,a.create_time,a.status,a.is_recommend,a.create_time';
	    $where = array();
	    $where['a.status'] = array('in','1,2');
	    $list = $m_public->getList($fields,$where, $orders, $start,$size);
	    
	    $this->assign('list',$list['list']);
	    $this->assign('page',$list['page']);
	    $this->display('Report/sapppublic');
	}
	public function pubdetail(){
	    $oss_host = 'http://'. C('OSS_HOST_NEW').'/';
	    $forscreen_id = I('get.forscreen_id',0,'intval');
	    $res_type = I('get.res_type');
	    $m_pubdetail = new \Admin\Model\Smallapp\PubdetailModel();
	    $fields = "concat('".$oss_host."',`res_url`) res_url";
	    $where = array();
	    $where['forscreen_id'] = $forscreen_id;
	    $list = $m_pubdetail->getWhere($fields,$where);
	     
	     
	    $this->assign('res_type',$res_type);
	    $this->assign('list',$list);
	     
	    $this->display('Report/pubdetail');
	}
	/**
	 * @desc 审核通过
	 */
	public function operateStatus(){
	    $id     = I('get.id',0,'intval');
	    $status = I('get.status');
	    $m_public = new \Admin\Model\Smallapp\PublicModel();
	    $where = $data = array();
	    $where['id'] = $id;
	    $data['status'] = $status;
	    $ret = $m_public->updateInfo($where, $data);
	    if($ret){
	        $this->output('审核成功', 'sappforscreen/publiccheck',2);
	    }else {
	        $this->output('审核失败', 'sappforscreen/publiccheck',2);
	    }
	    
	}
	public function operateRecommend(){
	    $id = I('get.id',0,'intval');
	    $is_recommend = I('get.is_recommend');
	    $m_public = new \Admin\Model\Smallapp\PublicModel();
	    $where = $data = array();
	    $where['id'] = $id;
	    $data['is_recommend'] = $is_recommend;
	    $ret = $m_public->updateInfo($where, $data);
	    if($ret){
	        $this->output('修改成功', 'sappforscreen/publiccheck',2);
	    }else {
	        $this->output('修改失败', 'sappforscreen/publiccheck',2);
	    }
	}
	public function delpublic(){
	   $id     = I('get.id',0,'intval');
	   $m_public = new \Admin\Model\Smallapp\PublicModel();
	   $where = $data = array();
	   $where['id'] = $id;
	   $data['status'] = 0;
	   $ret = $m_public->updateInfo($where, $data);
	   if($ret){
	       $this->output('删除成功', 'sappforscreen/publiccheck',2);
	   }else {
	       $this->output('删除失败', 'sappforscreen/publiccheck',2);
	   }
	}
}