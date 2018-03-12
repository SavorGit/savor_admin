<?php
namespace Admin\Model\Statisticses;
use Think\Model;
use Common\Lib\Page;
class VersionMonitorModel extends Model
{
    protected $connection = 'DB_STATIS';

	protected $tablePrefix = 'statistics_';

    protected $tableName='version_monitor';
    public function countNums($where){
        $nums = $this->where($where)->count();
        return $nums;
    }
}