<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class StaticHotelstaffdataModel extends BaseModel{

    protected $tableName='smallapp_static_hotelstaffdata';

    public function getCustomeList($fields="*",$where,$groupby='',$order='',$countfields='',$start=0,$size=5){
        $list = $this->field($fields)
            ->where($where)
            ->group($groupby)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $res_count = $this->field($countfields)
            ->where($where)->select();
        $count = $res_count[0]['tp_count'];
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        return $data;
    }

    public function handle_hotel_staffdata(){
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $start = date('Y-m-d',strtotime('-1day'));
        $end = date('Y-m-d',strtotime('-1day'));
        $all_dates = $m_statistics->getDates($start,$end);

        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $fields = 'id,hotel_id,name,mobile,status';
        $where = array('status'=>1);
        $where['id'] = array('not in',array(3,92));//九重天-公司员工,小热点自营官方旗舰店-上线专用
        $res_merchant = $m_merchant->getDataList($fields,$where,'id asc');

        $m_staff = new \Admin\Model\Integral\StaffModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $m_sysuser = new \Admin\Model\UserModel();
        $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $m_signin = new \Admin\Model\Smallapp\UserSigninModel();
        $m_userintegralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
        $m_exchange = new \Admin\Model\Smallapp\ExchangeModel();
        $m_stockrecord = new \Admin\Model\FinanceStockRecordModel();
        $m_taskuser = new \Admin\Model\Integral\TaskUserModel();
        $m_taskhotel = new \Admin\Model\Integral\TaskHotelModel();
        $m_smallapp_user = new \Admin\Model\Smallapp\UserModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_room = new \Admin\Model\RoomModel();
        foreach ($all_dates as $v){
            $static_date = $v;
            $time_date = strtotime($v);
            $date = date('Ymd',$time_date);
            $start_time = date('Y-m-d 00:00:00',$time_date);
            $end_time = date('Y-m-d 23:59:59',$time_date);
            foreach ($res_merchant as $mv){
                $merchant_id = $mv['id'];
                $hotel_id = $mv['hotel_id'];
                $hotel_field = 'a.id as hotel_id,a.name as hotel_name,area.id as area_id,area.region_name as area_name,ext.maintainer_id';
                $hotel_where = array('a.id'=>$hotel_id,'a.state'=>1,'a.flag'=>0);
                $res_hotel = $m_hotel->getHotels($hotel_field,$hotel_where);
                if(empty($res_hotel)){
                    echo "merchant_id:{$merchant_id}-hotel_id:{$hotel_id} hotel offline \r\n";
                    continue;
                }
                $hotel_info = $res_hotel[0];
                $res_staff = $m_staff->getDataList('id,openid,level',array('merchant_id'=>$merchant_id,'status'=>1),'level asc');
                if(empty($res_staff)){
                    echo "merchant_id:{$merchant_id}-hotel_id:{$hotel_id} no staff \r\n";
                    continue;
                }
                $task_hotel_where = array('hoteltask.hotel_id'=>$hotel_id,'task.status'=>1,'task.flag'=>1);
                $res_taskhotel = $m_taskhotel->getHoteltasks('hoteltask.task_id,task.task_type',$task_hotel_where,'');
                $invitevip_task_id = $demand_task_id = $invitation_task_id = 0;
                $hotel_box_num = $hotel_room_num = 0;
                if(!empty($res_taskhotel)){
                    foreach ($res_taskhotel as $thv){
                        if($thv['task_type']==6){
                            $invitation_task_id = $thv['task_id'];
                            $rwhere = array('hotel.id'=>$hotel_id,'room.state'=>1,'room.flag'=>0);
                            $res_room = $m_room->getRoomByCondition('count(room.id) as num',$rwhere);
                            $hotel_room_num = intval($res_room[0]['num']);
                        }
                        if($thv['task_type']==25){
                            $demand_task_id = $thv['task_id'];
                            $bwhere = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
                            $res_box = $m_box->getBoxByCondition('count(box.id) as num',$bwhere);
                            $hotel_box_num = intval($res_box[0]['num']);
                        }
                        if($thv['task_type']==26){
                            $invitevip_task_id = $thv['task_id'];
                        }
                    }
                }
                foreach ($res_staff as $sv){
                    $openid = $sv['openid'];

                    $forscreen_fields = 'count(id) as num,action';
                    $forscreen_where = array('hotel_id'=>$hotel_id,'openid'=>$openid,'small_app_id'=>5);
                    $forscreen_where['action'] = array('in',array('41','5'));
                    $forscreen_where['create_time'] = array(array('egt',$start_time),array('elt',$end_time));
                    $res_welcom_brithday = $m_forscreen->field($forscreen_fields)->where($forscreen_where)->group('action')->select();
                    $welcome_num = $birthday_num = $pub_num = 0;
                    $birthday_pub_num = 0;
                    if(!empty($res_welcom_brithday)){
                        foreach ($res_welcom_brithday as $fwbv){
                            if($fwbv['action']==41){
                                $welcome_num = $fwbv['num'];
                            }
                            if($fwbv['action']==5){
                                $birthday_pub_num = $fwbv['num'];
                            }
                        }
                    }
                    if($birthday_pub_num>0){
                        $forscreen_where['action'] = 5;
                        $forscreen_where['forscreen_char']= array('eq','Happy Birthday');
                        $res_birthday = $m_forscreen->field($forscreen_fields)->where($forscreen_where)->select();
                        if(!empty($res_birthday) && $res_birthday[0]['num']>0){
                            $pub_num = $birthday_pub_num - $res_birthday[0]['num'];
                        }else{
                            $pub_num = $birthday_pub_num;
                        }
                    }
                    $forscreen_num = 0;
                    $forscreen_where['action'] = array('in',array('2,4,30,31'));
                    $res_forscreen = $m_forscreen->field('id')->where($forscreen_where)->group('forscreen_id')->select();
                    if(!empty($res_forscreen)){
                        $forscreen_num = count($res_forscreen);
                    }

                    $signin_num = 0;
                    $signin_fields = 'count(id) as num';
                    $signin_where = array('openid'=>$openid);
                    $signin_where['add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                    $res_signin = $m_signin->field($signin_fields)->where($signin_where)->select();
                    if(!empty($res_signin)){
                        $signin_num = intval($res_signin[0]['num']);
                    }

                    $integral = 0;
                    $integral_where = array('hotel_id'=>$hotel_id,'openid'=>$openid,'type'=>array('neq',4));
                    $integral_where['add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                    $res_integral = $m_userintegralrecord->field('sum(integral) as total_integral')->where($integral_where)->select();
                    if(!empty($res_integral)){
                        $integral = intval($res_integral[0]['total_integral']);
                    }
                    $money = 0;
                    $money_where = array('hotel_id'=>$hotel_id,'openid'=>$openid,'status'=>21);
                    $money_where['add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                    $res_money = $m_exchange->field('sum(total_fee) as total_fee')->where($money_where)->select();
                    if(!empty($res_money)){
                        $money = intval($res_money[0]['total_fee']);
                    }
                    $sale_num = 0;
                    $sale_where = array('op_openid'=>$openid,'type'=>7,'wo_status'=>array('in',array('1','2','4')));
                    $sale_where['add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                    $res_sale = $m_stockrecord->field('sum(total_amount) as total_amount')->where($sale_where)->select();
                    if(!empty($res_sale)){
                        $sale_num = abs($res_sale[0]['total_amount']);
                    }
                    $task_invitevip_release_num = $task_invitevip_get_num = $task_invitevip_rewardintegral_num = 0;
                    if($invitevip_task_id){
                        $task_invitevip_release_num = 1;
                        $invitevip_where = array('a.hotel_id'=>$hotel_id,'a.openid'=>$openid,'a.task_id'=>$invitevip_task_id,'a.status'=>1);
                        $invitevip_where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                        $res_task_invitevip = $m_taskuser->getUserTask('a.id,a.add_time',$invitevip_where,'a.id desc','0,1','');
                        if(!empty($res_task_invitevip)){
                            $task_invitevip_get_num = 1;

                            $invitevip_rewardintegral_where = array('hotel_id'=>$hotel_id,'openid'=>$openid,'task_id'=>$invitevip_task_id,'status'=>array('in',array('1','2')));
                            $invitevip_rewardintegral_where['add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                            $res_vipintegral = $m_userintegralrecord->field('sum(integral) as total_integral')->where($invitevip_rewardintegral_where)->select();
                            if(!empty($res_vipintegral)){
                                $task_invitevip_rewardintegral_num = intval($res_vipintegral[0]['total_integral']);
                            }
                        }
                    }
                    $task_invitevip_sale_num = $sale_num;

                    $get_gold_coupon_where = array('invite_gold_openid'=>$openid);
                    $get_gold_coupon_where['invite_gold_time'] = array(array('egt',$start_time),array('elt',$end_time));
                    $res_get_gold_coupon = $m_smallapp_user->getWhere('count(id) as num',$get_gold_coupon_where,'','','');
                    $task_invitevip_getcoupon_num = 0;
                    if(!empty($res_get_gold_coupon)){
                        $task_invitevip_getcoupon_num = intval($res_get_gold_coupon[0]['num']);
                    }

                    $task_demand_release_num = $task_demand_get_num = $task_demand_operate_num = $task_demand_finish_num = $task_demand_rewardintegral_num = 0;
                    if($demand_task_id){
                        $task_demand_release_num = 1;
                        $demand_where = array('a.hotel_id'=>$hotel_id,'a.openid'=>$openid,'a.task_id'=>$demand_task_id,'a.status'=>1);
                        $demand_where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                        $res_task_demand = $m_taskuser->getUserTask('a.id,a.add_time',$demand_where,'a.id desc','0,1','');
                        if(!empty($res_task_demand)){
                            $task_demand_get_num = 1;

                            $demand_rewardintegral_where = array('hotel_id'=>$hotel_id,'openid'=>$openid,'task_id'=>$demand_task_id,'status'=>1);
                            $demand_rewardintegral_where['add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                            $demand_integral_fields = 'sum(integral) as total_integral,count(id) as finish_num';
                            $res_demandintegral = $m_userintegralrecord->field($demand_integral_fields)->where($demand_rewardintegral_where)->select();
                            if(!empty($res_demandintegral)){
                                $task_demand_rewardintegral_num = intval($res_demandintegral[0]['total_integral']);
                                $task_demand_finish_num = intval($res_demandintegral[0]['finish_num']);
                            }
                        }
                    }
                    if($sv['level']==1){
                        $task_demand_operate_num = round(1.8 * $hotel_box_num);
                    }

                    $task_invitation_release_num = $task_invitation_get_num = $task_invitation_operate_num = $task_invitation_finish_num = $task_invitation_rewardintegral_num = 0;
                    if($invitation_task_id){
                        $task_invitation_release_num = 1;
                        $invitation_where = array('a.hotel_id'=>$hotel_id,'a.openid'=>$openid,'a.task_id'=>$invitation_task_id,'a.status'=>1);
                        $invitation_where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                        $res_task_invitation = $m_taskuser->getUserTask('a.id,a.add_time',$invitation_where,'a.id desc','0,1','');
                        if(!empty($res_task_invitation)){
                            $task_invitation_get_num = 1;

                            $invitation_rewardintegral_where = array('hotel_id'=>$hotel_id,'openid'=>$openid,'task_id'=>$invitation_task_id,'status'=>1);
                            $invitation_rewardintegral_where['add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                            $invitation_integral_fields = 'sum(integral) as total_integral,count(id) as finish_num';
                            $res_invitationintegral = $m_userintegralrecord->field($invitation_integral_fields)->where($invitation_rewardintegral_where)->select();
                            if(!empty($res_invitationintegral)){
                                $task_invitation_rewardintegral_num = intval($res_invitationintegral[0]['total_integral']);
                                $task_invitation_finish_num = intval($res_invitationintegral[0]['finish_num']);
                            }
                        }
                    }
                    if($sv['level']==1){
                        $task_invitation_operate_num = round(1.6 * $hotel_room_num);
                    }

                    $add_data = array('openid'=>$sv['openid'],'merchant_staff_id'=>$sv['id'],'merchant_staff_level'=>$sv['level'],'area_id'=>$hotel_info['area_id'],
                        'area_name'=>$hotel_info['area_name'],'hotel_id'=>$hotel_info['hotel_id'],'hotel_name'=>$hotel_info['hotel_name'],
                        'maintainer_id'=>$hotel_info['maintainer_id'],'forscreen_num'=>$forscreen_num,'pub_num'=>$pub_num,'welcome_num'=>$welcome_num,
                        'birthday_num'=>$birthday_num,'signin_num'=>$signin_num,'integral'=>$integral,'money'=>$money,'sale_num'=>$sale_num,
                        'task_invitevip_release_num'=>$task_invitevip_release_num,'task_invitevip_get_num'=>$task_invitevip_get_num,'task_invitevip_sale_num'=>$task_invitevip_sale_num,
                        'task_invitevip_getcoupon_num'=>$task_invitevip_getcoupon_num,'task_invitevip_rewardintegral_num'=>$task_invitevip_rewardintegral_num,
                        'task_demand_release_num'=>$task_demand_release_num,'task_demand_get_num'=>$task_demand_get_num,'task_demand_operate_num'=>$task_demand_operate_num,
                        'task_demand_finish_num'=>$task_demand_finish_num,'task_demand_rewardintegral_num'=>$task_demand_rewardintegral_num,
                        'task_invitation_release_num'=>$task_invitation_release_num,'task_invitation_get_num'=>$task_invitation_get_num,'task_invitation_operate_num'=>$task_invitation_operate_num,
                        'task_invitation_finish_num'=>$task_invitation_finish_num,'task_invitation_rewardintegral_num'=>$task_invitation_rewardintegral_num,
                        'task_invitevip_id'=>$invitevip_task_id,'task_demand_id'=>$demand_task_id,'task_invitation_id'=>$invitation_task_id,
                        'static_date'=>$static_date
                    );
                    if($hotel_info['maintainer_id']){
                        $res_user = $m_sysuser->getUserInfo($hotel_info['maintainer_id']);
                        if(!empty($res_user)){
                            $add_data['maintainer'] = $res_user['remark'];
                        }
                    }
                    $this->add($add_data);
                }

                echo "merchant_id:{$merchant_id}-hotel_id:{$hotel_id} ok \r\n";
            }
            echo "date:$static_date ok \r\n";
        }
    }

}