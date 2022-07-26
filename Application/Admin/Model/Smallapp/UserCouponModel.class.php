<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class UserCouponModel extends BaseModel{
	protected $tableName='smallapp_usercoupon';

    public function getUserCouponList($fields,$where,$order, $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_smallapp_activity activity on a.activity_id=activity.id','left')
            ->join('savor_smallapp_coupon coupon on a.coupon_id=coupon.id','left')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $count = $this->alias('a')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_smallapp_activity activity on a.activity_id=activity.id','left')
            ->join('savor_smallapp_coupon coupon on a.coupon_id=coupon.id','left')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
}