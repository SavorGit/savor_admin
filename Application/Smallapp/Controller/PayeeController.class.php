<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

/**
 * @desc 收款人管理
 *
 */
class PayeeController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }

    public function userlist() {
        $start_date = I('start_date','');
        $end_date = I('end_date','');
        $status = I('status',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array();
        if($status){
            $where['a.status'] = $status;
        }else{
            $where['a.status'] = array('in',array(1,2,3));
        }
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'payee/userlist', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        $start  = ($page-1) * $size;
        $fields = 'a.id,a.openid,a.status,a.add_time,user.nickName,user.avatarurl,hotel.name as hotel_name';
        $m_payee = new \Admin\Model\Smallapp\PayeeModel();
        $result = $m_payee->getPayeeList($fields,$where,'a.id desc',$start,$size);
        $datalist = $result['list'];
        $all_status = array('1'=>'待审核','2'=>'审核通过','3'=>'审核不通过');
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
        $m_payee = new \Admin\Model\Smallapp\PayeeModel();
        $userinfo = session('sysUserInfo');
        $sysuser_id = $userinfo['id'];

        $data = array('status'=>$status);
        $where = array('id'=>$id);
        $result = $m_payee->updateInfo($where,$data);
        if($result){
            $this->output('操作成功!', 'payee/userlist',2);
        }else{
            $this->output('操作失败', 'payee/userlist',2,0);
        }
    }

}