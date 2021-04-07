<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController;

class UsertaskController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }

    public function datalist() {
        $openid   = I('openid','','trim');
        $hotel_name   = I('hotel_name','','trim');
        $status   = I('status',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $m_usertask = new \Admin\Model\Smallapp\UsertaskModel();
        $start  = ($page-1) * $size;
        $where = array();
        if($status){
            $where['a.status'] = $status;
        }
        if($openid){
            $where['a.openid'] = $openid;
        }
        if($hotel_name){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }

        $fields = 'a.id,a.openid,a.money,a.get_money,a.status,a.finish_time,a.withdraw_time,a.add_time,
        user.avatarUrl as avatar_url,hotel.name as hotel_name';
        $orderby = 'a.id desc';
        $result = $m_usertask->getList($fields,$where, $orderby, $start,$size);
        $datalist = $result['list'];
        $all_status = array('1'=>'进行中','2'=>'已完成',3=>'未完成',4=>'已提现',5=>'提现失败');
        if(!empty($datalist)){
            foreach ($datalist as $k=>$v){
                $datalist[$k]['status_str'] = $all_status[$v['status']];
                if($v['finish_time']=='0000-00-00 00:00:00'){
                    $datalist[$k]['finish_time'] = '';
                }
                if($v['withdraw_time']=='0000-00-00 00:00:00'){
                    $datalist[$k]['withdraw_time'] = '';
                }
            }
        }
        $this->assign('openid',$openid);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('status',$status);
        $this->assign('all_status',$all_status);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('datalist');
    }



}