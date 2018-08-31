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
	        $list['list'][$key]['imgs'] = json_decode(str_replace('\\', '', $v['imgs']),true);
	        switch ($v['action']){
	            case '1':
	                $list['list'][$key]['action_name'] = '发送呼码';
	                break;
	            case '2':
	                if($v['resource_type']==1) $list['list'][$key]['action_name'] = '指定投单图';
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
	    
	    $this->assign('list',$list['list']);
	    $this->assign('page',$list['page']);
	    $this->display('Report/turntablelog');
	    
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
	    $this->output('删除成功', 'gameLog/index', 2);
	}	
}