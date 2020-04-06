<?php
namespace Admin\Model\Integral;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class MerchantModel extends BaseModel{
	
	protected $tableName='integral_merchant';

    public function getMerchantList($fields,$where,$order,$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
            ->join('savor_area_info area on hotel.area_id=area.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $count = $this->alias('a')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
            ->join('savor_area_info area on hotel.area_id=area.id','left')
            ->field('a.id')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }


    public function getMerchants($fields,$where,$order){
        $data = $this->alias('a')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->select();
        return $data;
    }

}