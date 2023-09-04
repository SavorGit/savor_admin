<?php
namespace Admin\Model\Crm;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class TaskHotelModel extends BaseModel{
	protected $tableName='crm_task_hotel';

    public function getHotelList($fields,$where,$order, $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_hotel h on a.hotel_id=h.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $count = $this->alias('a')
            ->join('savor_hotel h on a.hotel_id=h.id','left')
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