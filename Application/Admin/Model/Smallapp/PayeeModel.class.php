<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class PayeeModel extends BaseModel{
	protected $tableName='integral_merchant_payee';

    public function getPayeeList($fields,$where,$order,$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_integral_merchant merchant on a.merchant_id=merchant.id','left')
            ->join('savor_hotel hotel on merchant.hotel_id=hotel.id','left')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $count = $this->alias('a')
            ->join('savor_integral_merchant merchant on a.merchant_id=merchant.id','left')
            ->join('savor_hotel hotel on merchant.hotel_id=hotel.id','left')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field('a.id')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

}