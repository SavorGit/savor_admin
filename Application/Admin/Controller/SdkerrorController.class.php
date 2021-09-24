<?php
/**
 * @desc SD卡异常上报
 * @author zhang.yingtao
 * @since  20180510
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class SdkerrorController extends BaseController {
    public function __construct() {
        parent::__construct();
    }
    /**
     * @desc 异常列表
     */
    public function index(){
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','last_report_date');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        
        
        $hotel_name = I('hotel_name');
        
        
        $m_sdk_error = new \Admin\Model\SdkErrorModel(); 
        
        $fields = "a.id,hotel.name hotel_name,hotel.addr,room.name room_name,
                   box.name box_name ,a.erro_count,a.full_count,a.last_report_date,area.region_name,box.mac";
        $where  = array();
        $where['hotel.state'] = 1;
        $where['hotel.flag']  = 0;
        $where['box.state']   = 1;
        $where['box.flag']    = 0;  
        if($hotel_name){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        } 
        $data = $m_sdk_error->getList($fields, $where, $orders, $start, $size);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);
        $this->display('index');
    }
    public function deldata(){
        
        $del_date = date("Y-m-d H:i:s",strtotime('-1 month'));
        
        $m_sdk_error = new \Admin\Model\SdkErrorModel();
        $where = array();
        $where['last_report_date'] = array('ELT',"$del_date");
        $ret = $m_sdk_error->delData($where);
        if($ret){
            $this->output('删除成功', 'sdkerror/index', 2);
        }else {
            $this->output('删除成功', 'sdkerror/index', 2);
        }
        
    }
}