<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class GoodsstockModel extends BaseModel{
	protected $tableName='smallapp_goods_stock';

    public function getStockList($fields,$where,$order, $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_smallapp_dishgoods goods on a.goods_id=goods.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $res_count = $this->alias('a')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_smallapp_dishgoods goods on a.goods_id=goods.id','left')
            ->field('count(a.id) as total_num')
            ->where($where)
            ->select();
        $count = intval($res_count[0]['total_num']);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
}