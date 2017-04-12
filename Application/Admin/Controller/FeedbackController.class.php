<?php
namespace Admin\Controller;
/**
 * @desc 功能测试类
 *
 */
use Admin\Controller\BaseController;
use Admin\Model\HomeModel;
class FeedbackController extends BaseController {
    var $content_type_arr;
    public function __construct() {
        parent::__construct();
    }


    public function rplist(){
        $fdModel = new \Admin\Model\FeedbackModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','create_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = "1=1";
        $name = I('titlename');
        $beg_time = I('begin_time','');
        $end_time = I('end_time','');
        $d_arr = C('DEVICE_TYPE');
        if($beg_time)   $where.=" AND create_time>='$beg_time'";
        if($end_time)   $where.=" AND create_time<='$end_time'";
        if($name){
            $this->assign('name',$name);
            $where .= "	AND suggestion LIKE '%{$name}%'";
        }
        $result = $fdModel->getList($where,$orders,$start,$size);
        $res = $result['list'];
        foreach($res as $rk=>$rv){
            foreach($d_arr as $k=>$v){
                if($rv['device_type'] == $k) {
                    $res[$rk]['device_type'] = $v;
                }
            }
        }
        $time_info = array('now_time'=>date('Y-m-d H:i:s'),'begin_time'=>$beg_time,'end_time'=>$end_time);
        $this->assign('timeinfo',$time_info);
        $this->assign('list', $res);
        $this->assign('page',  $result['page']);
        $this->display('feedlist');
    }

}