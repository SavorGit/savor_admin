<?php
namespace Admin\Model;
use Common\Lib\Page;

class StoresaleAdsHotelModel extends BaseModel{
	protected $tableName='storesale_ads_hotel';

    public function getList($field,$where, $order='id desc', $start=0,$size=5){
        $list = $this->alias('adshotel')
            ->where($where)
            ->field($field)
            ->join('LEFT JOIN savor_hotel hotel ON adshotel.hotel_id=hotel.id')
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('adshotel')
            ->field('adshotel.id')
            ->join('LEFT JOIN savor_hotel hotel ON adshotel.hotel_id=hotel.id')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $pagestyle = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$pagestyle);
        return $data;
    }

    public function getDataCount($where){
        $count = $this->where($where)->count();
        return $count;
    }

}



