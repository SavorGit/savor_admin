<?php
namespace Admin\Controller;
/**
 * @desc 系统日志记录类
 *
 */
class AdminlogController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        /* $sysMenu = new \Admin\Model\SysmenuModel();
        $result = $sysMenu->getList($where="where `menulevel`=1  ", 'id desc', $start=0,$size=500);
        $this->assign('classList',  $result['list']); */
    }
    public function index(){
        $m_sys_user = new \Admin\Model\UserModel();
        $userinfo = $m_sys_user->getUser();
        foreach ($userinfo as $k=>$v){
            if($v['id']==1){
                unset($userinfo[$k]);
            }
        }

        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = " where userid!=1";
        $searchUserid= I('searchUserid');
        if($searchUserid){
            $where .=" and userid=".$searchUserid;
            $this->assign('userid',$searchUserid);
        }
        $beg_time = I('begin_time','');
        $end_time = I('end_time','');
        if($beg_time)   $where.=" AND addtime>='$beg_time'";
        if($end_time){
            $end_time = "$end_time 23:59:59";
            $where.=" AND addtime<='$end_time'";
        }
        $m_admin_log = new \Admin\Model\AdminLogModel();
        $result = $m_admin_log->getList($where, $orders, $start, $size);
        foreach($result['list'] as $key=>$v){
            $result['list'][$key]['areaname'] = $this->getNameByIp($v['ipaddr']);
        }
        $time_info = array('now_time'=>date('Y-m-d H:i:s'),'begin_time'=>$beg_time,'end_time'=>$end_time);
        $this->assign('timeinfo',$time_info);
        //print_r($result);exit;
        $this->assign('userinfo',$userinfo);
        $this->assign('result',$result['list']);
        $this->assign('page',$result['page']);
        $this->display('index');
    }
}