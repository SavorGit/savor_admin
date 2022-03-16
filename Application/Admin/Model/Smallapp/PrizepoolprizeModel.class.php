<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class PrizepoolprizeModel extends BaseModel{
	protected $tableName='smallapp_prizepool_prize';

    public function getHotelpoolprizeList($fields,$where,$order){
        $datas = $this->alias('a')
            ->join('savor_smallapp_prizepool p on a.prizepool_id=p.id', 'left')
            ->join('savor_smallapp_hotel_prizepool hp on a.prizepool_id=hp.prizepool_id', 'left')
            ->join('savor_hotel h on hp.hotel_id=h.id', 'left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->select();
        return $datas;
    }
}