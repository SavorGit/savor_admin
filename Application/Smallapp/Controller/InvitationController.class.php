<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class InvitationController extends BaseController {

    public function datalist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $start_date = I('start_time','');
        $end_date = I('end_time','');
        $keyword = I('keyword','','trim');

        if(empty($start_date)){
            $start_date = date('Y-m-d');
        }
        if(empty($end_date)){
            $end_date = date('Y-m-d');
        }
        $start_time = "$start_date 00:00:00";
        $end_time = "$end_date 23:59:59";
        $where = array('a.add_time'=>array(array('egt',$start_time),array('elt',$end_time)));
        if(!empty($keyword)){
            $where['a.hotel_name'] = array('like',"%{$keyword}%");
        }
        $m_invitation = new \Admin\Model\Smallapp\InvitationModel();
        $start = ($page-1) * $size;
        $fields = 'a.*,user.nickName,user.mobile as cust_mobile,sysuser.remark as maintainer_name';
        $result = $m_invitation->getInvitationList($fields,$where,'a.id desc',$start,$size);
        $m_invitation_user = new \Admin\Model\Smallapp\InvitationUserModel();
        $datalist = array();
        $all_room_type_str = array('1'=>'包间','2'=>'大厅');
        foreach ($result['list'] as $v){
            $room_type_str = $all_room_type_str[$v['room_type']];
            $send_time = $v['send_time'];
            if($send_time=='0000-00-00 00:00:00'){
                $send_time = '';
            }
            $customer_name = $v['nickname']."({$v['cust_mobile']})";
            $is_open = '否';
            $res_open = $m_invitation_user->getInfo(array('invitation_id'=>$v['id']));
            if(!empty($res_open)){
                $is_open = '是';
            }
            $datalist[]=array('id'=>$v['id'],'hotel_name'=>$v['hotel_name'],'hotel_id'=>$v['hotel_id'],'room_name'=>$v['room_name'],
                'room_type_str'=>$room_type_str,'book_time'=>$v['book_time'],'name'=>$v['name'],'mobile'=>$v['mobile'],'send_time'=>$send_time,
                'is_open'=>$is_open,'maintainer_name'=>$v['maintainer_name'],'customer_name'=>$customer_name,'add_time'=>$v['add_time']
            );
        }

        $this->assign('start_time',$start_date);
        $this->assign('end_time',$end_date);
        $this->assign('keyword', $keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

}