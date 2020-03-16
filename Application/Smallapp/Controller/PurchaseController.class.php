<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

/**
 * @desc 代购用户管理
 *
 */
class PurchaseController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }

    public function userlist() {
        $start_date = I('start_date','');
        $end_date = I('end_date','');
        $status = I('status',99,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array('a.role_id'=>3);
        if($status!=99)   $where['a.status'] = $status;
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'purchase/userlist', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['a.create_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        $start  = ($page-1) * $size;

        $m_user = new \Admin\Model\Smallapp\UserModel();
        $result = $m_user->getUserList('*',$where,'id desc',$start,$size);
        $datalist = $result['list'];
        $all_status = array('0'=>'审核不通过','1'=>'审核通过','2'=>'待审核');
        if(!empty($datalist)){
            $oss_host = get_oss_host();
            foreach ($datalist as $k=>$v){
                $idcard_imgs = array();
                $idcard_arr = explode(',',$v['idcard']);
                foreach ($idcard_arr as $iv){
                    if(!empty($iv)){
                        $idcard_imgs[]=$oss_host.$iv;
                    }
                }
                $datalist[$k]['status_str'] = $all_status[$v['status']];
                $datalist[$k]['idcard_imgs'] = $idcard_imgs;
            }
        }

        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('all_status',$all_status);
        $this->assign('status',$status);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('userlist');
    }

    public function changestatus(){
        $id = I('get.id',0,'intval');
        $status = I('get.status',1,'intval');
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $userinfo = session('sysUserInfo');
        $sysuser_id = $userinfo['id'];

        $data = array('status'=>$status);
        $where = array('id'=>$id);
        $result = $m_user->updateInfo($where,$data);
        if($result){
            $this->output('操作成功!', 'purchase/userlist',2);
        }else{
            $this->output('操作失败', 'purchase/userlist',2,0);
        }
    }

}