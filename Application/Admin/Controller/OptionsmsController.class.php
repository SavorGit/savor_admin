<?php
/**
 *@desc 维修任务完成发送短信
 *@since 2018-05-23
 *@author  zhang.yingtao
 *
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class OptionsmsController extends BaseController{
    
    public function __construct(){
        parent::__construct();
    }
    
    /**
     * @desc 列表
     */
    public function index(){
        
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','create_time');
        // $plan_finish_time = I('plan_finish_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        
        $start_date = I('start_date');
        $end_date   = I('end_date');
        if(($start_date && $end_date) && $start_date>$end_date){
            $this->error('开始时间不能大于结束时间');
        }
        
        
        
        $m_account_smg_log = new \Admin\Model\AccountMsgLogModel();
        
        $fields = 'url,tel,resp_code,create_time';
        $where = array();
        if(!empty($start_date) && empty($end_date)){
            $where['create_time'] = array('egt',$start_date.' 00:00:00');
            $this->assign('start_date',$start_date);
        }else if(empty($start_date) && !empty($end_date)){
            $where['create_time'] = array('elt',$end_date.' 23:59:59');
            $this->assign('end_date',$end_date);
        }else if(!empty($start_date) && !empty($end_date)){
            $where['create_time'] = array(array('egt',$start_date.' 00:00:00'),array('elt',$end_date.' 23:59:59'),'and');
            $this->assign('start_date',$start_date);
            $this->assign('end_date',$end_date);
        }
        
        $where['type'] = 6;
        
        $list = $m_account_smg_log->getList($fields, $where, $orders, $start, $size);
        
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->display('Optiontask/smglog');
    }
}