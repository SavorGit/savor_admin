<?php
namespace Admin\Model\Oss;
use Think\Model;
use Common\Lib\Page;
class BoxLogDetailModel extends Model
{
    protected $connection = 'DB_OSS';

	protected $tablePrefix = 'oss_';

    protected $tableName='box_log_detail';
    public function addInfo($data,$type=1){
        if($type==1){
            $ret = $this->add($data);
        }else {
            $ret = $this->addAll($data);
        }
        return $ret;
    }
}