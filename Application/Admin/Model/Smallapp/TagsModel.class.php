<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class TagsModel extends BaseModel{
	protected $tableName='smallapp_tags';

    public function getTagList($fields,$where,$order,$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('a')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->field('a.id')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

}