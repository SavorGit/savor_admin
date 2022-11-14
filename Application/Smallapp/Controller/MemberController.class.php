<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class MemberController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $vip_level = I('vip_level',0,'intval');
        $invite_type = I('invite_type',0,'intval');
        $keyword = I('keyword','','trim');

        $all_itypes = C('INVITE_TYPES');
        $all_levels = C('VIP_LEVELS');
        $where = array();
        if($vip_level){
            $where['a.vip_level'] = $vip_level;
        }else{
            $where['a.vip_level'] = array('in',array_keys($all_levels));
        }
        if(!empty($keyword)){
            $where['a.nickName'] = array('like',"%$keyword%");
        }
        if($invite_type){
            $where['a.invite_type'] = $invite_type;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'a.id desc';
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $fields = 'a.id,a.openid,a.nickName,a.avatarUrl,a.mobile,a.vip_level,a.invite_type,a.invite_openid,a.invite_time,a.hotel_id,a.room_id';
        $res_list = $m_user->getUserList($fields,$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_staff = new \Admin\Model\Integral\StaffModel();
            $m_hotel = new \Admin\Model\HotelModel();
            foreach ($res_list['list'] as $v){
                if($v['invite_type']==3 || $v['invite_type']==5){
                    $res_hotel = $m_hotel->getOne($v['hotel_id']);
                    $sale_name = '';
                    $hotel_id = $v['hotel_id'];
                    $hotel_name = $res_hotel['name'];
                }else{
                    $sfields = 'u.nickName as sale_name,h.id as hotel_id,h.name as hotel_name';
                    $res_staff = $m_staff->getMerchantStaffUserList($sfields,array('a.openid'=>$v['invite_openid']));
                    $sale_name = $res_staff[0]['sale_name'];
                    $hotel_id = $res_staff[0]['hotel_id'];
                    $hotel_name = $res_staff[0]['hotel_name'];
                }
                $extfields = 'area.region_name as area_name,sysuser.remark as maintainer_name';
                $res_hotel_ext = $m_hotel->getHotelInfo($extfields,array('a.id'=>$hotel_id));
                $area_name = $maintainer_name = '';
                if(!empty($res_hotel_ext)){
                    $area_name = $res_hotel_ext['area_name'];
                    $maintainer_name = $res_hotel_ext['maintainer_name'];
                }
                $v['area_name'] = $area_name;
                $v['maintainer_name'] = $maintainer_name;
                $v['sale_name'] = $sale_name;
                $v['hotel_id'] = $hotel_id;
                $v['hotel_name'] = $hotel_name;

                $v['vip_level_str'] = $all_levels[$v['vip_level']];
                $v['invite_type_str'] = $all_itypes[$v['invite_type']];
                $data_list[] = $v;
            }
        }
        $this->assign('invite_type',$invite_type);
        $this->assign('vip_level',$vip_level);
        $this->assign('data_list',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('keyword',$keyword);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function cleandata(){
        $id = I('get.id',0,'intval');
        $openid = I('get.openid','');
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $data = array('vip_level'=>0,'buy_wine_num'=>0,'invite_openid'=>'','invite_time'=>'0000-00-00 00:00:00',
            'invite_gold_openid'=>'','invite_gold_time'=>'0000-00-00 00:00:00','invite_type'=>0,
            'hotel_id'=>0,'room_id'=>0
        );
        $m_user->updateInfo(array('id'=>$id),$data);

        $m_usercoupon = new \Admin\Model\Smallapp\UserCouponModel();
        $condition = array('openid'=>$openid);
        $m_usercoupon->updateData($condition,array('status'=>2));

        $this->output('操作成功!', 'member/datalist',2);
    }

}