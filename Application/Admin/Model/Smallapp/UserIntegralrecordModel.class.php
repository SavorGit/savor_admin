<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class UserIntegralrecordModel extends BaseModel{
	protected $tableName='smallapp_user_integralrecord';

    public function getList($fields,$where,$orderby,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->alias('a')
                ->join('savor_smallapp_user user on a.openid=user.openid','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->limit($start,$size)
                ->select();
            $count = $this->alias('a')
                ->join('savor_smallapp_user user on a.openid=user.openid','left')
                ->where($where)
                ->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->alias('a')
                ->join('savor_smallapp_user user on a.openid=user.openid','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->select();
        }
        return $data;
    }

    public function confirmActivityAward($award_info,$integral_status=2){
        $award_hoteldata_id = $award_info['id'];

        $integral = $award_info['integral'];
        $step_integral = $award_info['step_integral'];
        $now_integral = $integral+$step_integral;
        $integralrecord_openid = $award_info['award_openid'];
        $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
        if($integral_status==1 && $now_integral>0){
            $res_integral = $m_userintegral->getInfo(array('openid'=>$integralrecord_openid));
            if(!empty($res_integral)){
                $userintegral = $res_integral['integral']+$now_integral;
                $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
            }else{
                $m_userintegral->add(array('openid'=>$integralrecord_openid,'integral'=>$now_integral));
            }
        }

        $m_hotel = new \Admin\Model\HotelModel();
        $hfield = 'hotel.area_id,area.region_name as area_name,hotel.name as hotel_name,hotel.hotel_box_type';
        $res_hotel = $m_hotel->getHotelById($hfield,array('hotel.id'=>$award_info['hotel_id']));
        if($integral>0){
            $integralrecord_data = array('openid'=>$integralrecord_openid,'area_id'=>$res_hotel['area_id'],'area_name'=>$res_hotel['area_name'],
                'hotel_id'=>$award_info['hotel_id'],'hotel_name'=>$res_hotel['hotel_name'],'hotel_box_type'=>$res_hotel['hotel_box_type'],
                'integral'=>$integral,'jdorder_id'=>$award_hoteldata_id,'content'=>1,'status'=>$integral_status,'type'=>26);
            if($integral_status==1){
                $integralrecord_data['integral_time'] = date('Y-m-d H:i:s');
            }
            $this->add($integralrecord_data);
        }
        if($step_integral>0){
            $integralrecord_data = array('openid'=>$integralrecord_openid,'area_id'=>$res_hotel['area_id'],'area_name'=>$res_hotel['area_name'],
                'hotel_id'=>$award_info['hotel_id'],'hotel_name'=>$res_hotel['hotel_name'],'hotel_box_type'=>$res_hotel['hotel_box_type'],
                'integral'=>$step_integral,'jdorder_id'=>$award_hoteldata_id,'content'=>1,'status'=>$integral_status,'type'=>27);
            if($integral_status==1){
                $integralrecord_data['integral_time'] = date('Y-m-d H:i:s');
            }
            $this->add($integralrecord_data);
        }
        return $award_hoteldata_id;
    }
}