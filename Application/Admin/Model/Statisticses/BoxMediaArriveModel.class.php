<?php
namespace Admin\Model\Statisticses;
use Think\Model;
use Common\Lib\Page;
class BoxMediaArriveModel extends Model
{
    protected $connection = 'DB_STATIS';

	protected $tablePrefix = 'statistics_';

    protected $tableName='box_media_arrive';

    public function getCount($where){
        $nums = $this->where($where)->count();
        return $nums;
    }
}