<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class MemberController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $vip_level = I('vip_level',0,'intval');

        $all_levels = C('VIP_LEVELS');
        $where = array();
        if($vip_level){
            $where['a.vip_level'] = $vip_level;
        }else{
            $where['a.vip_level'] = array('in',array_keys($all_levels));
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'a.id desc';
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $fields = 'a.id,a.openid,a.nickName,a.avatarUrl,a.mobile,a.vip_level,a.invite_openid,a.invite_time';
        $res_list = $m_user->getUserList($fields,$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_staff = new \Admin\Model\Integral\StaffModel();
            foreach ($res_list['list'] as $v){
                $sfields = 'u.nickName as sale_name,h.id as hotel_id,h.name as hotel_name';
                $res_staff = $m_staff->getMerchantStaffUserList($sfields,array('a.openid'=>$v['invite_openid'],'a.status'=>1));
                $v['sale_name'] = $res_staff[0]['sale_name'];
                $v['hotel_id'] = $res_staff[0]['hotel_id'];
                $v['hotel_name'] = $res_staff[0]['hotel_name'];
                $v['vip_level_str'] = $all_levels[$v['vip_level']];
                $data_list[] = $v;
            }
        }
        $this->assign('vip_level',$vip_level);
        $this->assign('data_list',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

}