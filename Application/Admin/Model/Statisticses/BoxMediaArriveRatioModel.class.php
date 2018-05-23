<?php
namespace Admin\Model\Statisticses;
use Think\Model;
use Common\Lib\Page;
class BoxMediaArriveRatioModel extends Model
{
    protected $connection = 'DB_STATIS';

	protected $tablePrefix = 'statistics_';

    protected $tableName='box_media_arrive_ratio';

    public function getList($fields,$where,$order,$start,$limit){
        $list = $this->alias('a')
                     ->join('cloud.savor_hotel hotel on a.hotel_id=hotel.id','left')
                     ->field($fields)
                     ->where($where)
                     ->order($order)
                     ->limit($start,$limit)
                     ->select();
        return $list;
    }
    public function getOne($fields,$where){
        $data = $this->field($fields)->where($where)->find();
        return $data;
    }
    public function getCount($where){
        $count = $this->alias('a')
             ->join('cloud.savor_hotel hotel on a.hotel_id=hotel.id','left')
             
             ->where($where)
             ->count();
        return $count;
    }
}