<?php
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;
class StandAloneLogModel extends Model
{
    protected $connection = 'DB_OSS';

    protected $tablePrefix = "oss_";

    protected $tableName='stand_alone_log';


    public function __consruct($name){
        parent::__construct();
        $this->tableName = $name;
    }
    public function getList($where, $order='id desc', $start=0,$size=5){
	    $list = $this->where($where)
	    ->order($order)
	    ->limit($start,$size)
	    ->select();
 
	    $count = $this->where($where)
	    ->count();
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
}