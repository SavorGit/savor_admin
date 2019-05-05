<?php
namespace Smallapp\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序数据统计-小程序扫码
 * @since 20190430
 */
class QrcodelogController extends BaseController {
    public function __construct() {
        parent::__construct();
    }
    public function index(){
        
        $size       = I('numPerPage',50);       //显示每页记录数
        $pagenum      = I('pageNum',1);          //当前页码
        $pagenum      = $pagenum ? $pagenum :1;
        $order      = I('_order','a.id');         //排序字段
        $sort       = I('_sort','desc');        //排序类型
        $orders     = $order.' '.$sort;
        $start = ($pagenum-1)* $size;
        
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$start);
        $this->assign('_order',$order);
        $this->assign('_sort',$sort);
        
        $where = array();
        $start_date = I('start_date') ? I('start_date') : date('Y-m-d',strtotime('-7 days'));
        $end_date   = I('end_date') ? I('end_date') : date('Y-m-d');
        $is_wx_auth = I('is_wx_auth',-1,'intval');
        //$gender     = I('gender',-1,'intval');
        //var_dump($is_wx_auth);
        $sapp_qrcode_type = I('sapp_qrcode_type');
        if($start_date && $end_date){
            if($end_date<$start_date){
                $this->error('开始时间不能大于结束时间');
            }
            $where['a.create_time'] = array(array('EGT',$start_date." 00:00:00"),array('ELT',$end_date." 23:59:59"));
            $this->assign('start_date',$start_date);
            $this->assign('end_date',$end_date);
        }else if(!empty($start_date) && empty($end_date)){
            $where['a.create_time']= array('EGT',$start_date." 00:00:00");
            $this->assign('start_date',$start_date);
        }else if(empty($start_date) && !empty($end_date)){
            $where['a.create_time'] = array('ELT',$end_date." 23:59:59");
            $this->assign('end_date',$end_date);
        }
        if(!empty($sapp_qrcode_type)){
            $where['a.type'] = $sapp_qrcode_type;
            $this->assign('sapp_qrcode_type',$sapp_qrcode_type);
        }
        
        //$limit ="limit $start,$size";
        $where['hotel.state'] = 1;
        $where['hotel.flag']  = 0;
        $where['box.state']   = 1;
        $where['box.flag']    = 0;
        $where['a.openid']    = array('neq','undefined');
        $where['a.box_mac']   = array('neq','undefined');
           
        $m_qrcode_log = new \Admin\Model\Smallapp\QrcodeLogModel();
        
        $fields = "a.id,a.type,area.region_name,hotel.name hotel_name,room.name room_name,box.mac,
                   suser.openid,suser.unionid,suser.avatarUrl,suser.nickName,suser.gender,
                   a.create_time,suser.is_wx_auth,suser.small_app_id";
        //echo $orders;exit;
        $data = $m_qrcode_log->getList($fields, $where, $orders, $start, $size);
        
        
        //扫码类型
        $sapp_qrcode_type_arr = C('SAPP_QRCODE_TYPE_ARR');
        $this->assign('sapp_qrcode_type_arr',$sapp_qrcode_type_arr);
        $this->assign('page',$data['page']);
        
        $this->assign('list',$data['list']);
        $this->display();
        
    }
}