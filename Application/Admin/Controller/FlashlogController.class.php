<?php
/**
 *@desc u盘日志上报
 *
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class FlashlogController extends BaseController{
    
    public function __construct(){
        parent::__construct();
    }
    public function index(){
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','create_time');
        $plan_finish_time = I('plan_finish_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $personid = I('personid');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        
        $where = '';
        $m_stand_alone_log = new \Admin\Model\StandAloneLogModel(); 
		$list= $m_stand_alone_log->getList($where,$orders,$start,$size);
		$m_box= new \Admin\Model\BoxModel();
		foreach($list['list'] as $key=>$v){
		    //获取酒楼、包间、机顶盒信息 
		    $hotel_info = $m_box->getHotelInfoByBoxMac($v['box_mac']);
		    $list['list'][$key]['hotel_name'] = $hotel_info['hotel_name'];
		    $list['list'][$key]['room_name']  = $hotel_info['room_name'];
		    $list['list'][$key]['box_name']   = $hotel_info['box_name'];
		}
		$this->assign('list',$list['list']);
		$this->assign('page',$list['page']);
		$this->display('Report/flashlog');
		
    }   
}