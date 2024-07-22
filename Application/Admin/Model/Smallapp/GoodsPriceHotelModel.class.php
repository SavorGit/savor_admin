<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class GoodsPriceHotelModel extends BaseModel{
	protected $tableName='smallapp_goods_price_hotel';

    public function getHotelDatas($fields,$where,$order,$group,$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_hotel h on a.hotel_id=h.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->group($group)
            ->select();

        $count = $this->alias('a')
            ->join('savor_hotel h on a.hotel_id=h.id','left')
            ->field('a.id')
            ->where($where)
            ->group($group)
            ->select();
        $count = count($count);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

    public function getGoodsPriceHotels($fields,$where,$order,$limit=''){
        $list = $this->alias('a')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_smallapp_goods_price gp on a.goods_price_id=gp.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();
        return $list;
    }
}