<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class OrderModel extends BaseModel{
	protected $tableName='smallapp_order';


    public function getOrderList($fields,$where,$order,$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->join('savor_integral_merchant merchant on a.merchant_id=merchant.id','left')
            ->join('savor_hotel hotel on merchant.hotel_id=hotel.id','left')
            ->join('savor_area_info area on hotel.area_id=area.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('a')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->join('savor_integral_merchant merchant on a.merchant_id=merchant.id','left')
            ->join('savor_hotel hotel on merchant.hotel_id=hotel.id','left')
            ->join('savor_area_info area on hotel.area_id=area.id','left')
            ->field('a.id')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

    public function getOrderInvoiceList($fields,$where,$order, $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_smallapp_orderinvoice i on a.id=i.order_id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $count = $this->alias('a')
            ->join('savor_smallapp_orderinvoice i on a.id=i.order_id','left')
            ->field('a.id')
            ->where($where)
            ->select();
        $count = count($count);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
}