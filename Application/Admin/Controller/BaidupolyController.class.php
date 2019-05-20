<?php
/**
 * @desc   机顶盒数据
 * @author zhang.yingtao
 * @since  20180725 
 * 
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class BaidupolyController extends BaseController{
	 public function __construct(){
	     parent::__construct();
	 }
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
	     $maps = ' 1 ';
	     $tpmedia_id = I('tpmedia_id');
	     if($tpmedia_id){
	         $where['a.tpmedia_id'] = $tpmedia_id;
	         $maps .=" and a.tpmedia_id=".$tpmedia_id;
	         $this->assign('tpmedia_id',$tpmedia_id);
	     }
	     $hotel_name = I('hotel_name','','trim');
	     if($hotel_name){
	         $where['hotel.name'] = array('like',"%$hotel_name%");
	         $maps .= " and hotel.name like '".$hotel_name."'";
	         $this->assign('hotel_name',$hotel_name); 
	     }
	     $box_mac    = I('box_mac','','trim');
	     if($box_mac){
	         $where['a.box_mac'] = $box_mac;
	         $maps .= " and a.box_mac='".$box_mac."'";
	         $this->assign('box_mac',$box_mac);
	     }
	     $play_date = I('play_date');
	     if($play_date){
	         $where['a.play_date'] = str_replace('-', '', $play_date);
	         $maps .=" and a.play_date =".str_replace('-', '', $play_date);
	         $this->assign('play_date',$play_date);
	     }
	     
	     $m_baidu_poly_play_record = new \Admin\Model\BaiduPolyPlayRecordModel();
	     
	     $fields = 'a.id,hotel.name hotel_name,room.name room_name, a.box_mac,a.media_name,
	                a.play_date,a.play_times,a.create_time , a.update_time,a.media_md5,a.tpmedia_id';
	     
	     $list = $m_baidu_poly_play_record->getList($fields,$where,$orders,$start,$size);
	     
	     
	     $count =  $m_baidu_poly_play_record->countPlayNums($maps);
	     $tpmedia_arr = C('POLY_SCREEN_MEDIA_LIST');
	     $this->assign('tpmedia_arr',$tpmedia_arr);
	     $this->assign('all_play_nums',intval($count));
	     $this->assign('list',$list['list']);
	     $this->assign('page',$list['page']);
	     $this->display('Report/baidupoly');
	 }
}