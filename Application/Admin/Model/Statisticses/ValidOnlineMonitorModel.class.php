<?php
namespace Admin\Model\Statisticses;
use Think\Model;
use Common\Lib\Page;
class ValidOnlineMonitorModel extends Model
{
    protected $connection = 'DB_STATIS';

	protected $tablePrefix = 'statistics_';

    protected $tableName='valid_online_monitor';
    public function countNums($where){
        $nums = $this->where($where)->count();
        return $nums;
    }
}