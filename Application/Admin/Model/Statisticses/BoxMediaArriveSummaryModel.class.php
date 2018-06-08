<?php
namespace Admin\Model\Statisticses;
use Think\Model;
use Common\Lib\Page;
class BoxMediaArriveSummaryModel extends Model
{
    protected $connection = 'DB_STATIS';

	protected $tablePrefix = 'statistics_';

    protected $tableName='box_media_arrive_summary';

    public function getCount($where){
        $nums = $this->where($where)->count();
        return $nums;
    }
    public function addInfo($data){
        $ret = $this->add($data);
        return $ret;
    }
    public function getOne($fields,$where){
        $data = $this->field($fields,$where)->find();
        return $data;
    }
    public function updateInfo($data,$where){
        $ret = $this->where($where)->save($data);
        return $ret;
    }
}