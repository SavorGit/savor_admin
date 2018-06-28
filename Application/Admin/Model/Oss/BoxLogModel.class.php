<?php
namespace Admin\Model\Oss;
use Think\Model;
use Common\Lib\Page;
class BoxLogModel extends Model
{
    protected $connection = 'DB_OSS';

	protected $tablePrefix = 'oss_';

    protected $tableName='box_log';

    public function getList($fields,$where, $order='a.id desc', $start=0,$size=5){	
		 $list = $this->alias('a')
		              ->join('cloud.savor_area_info area on a.area =area.id','left')
		              ->field($fields)
		              ->where($where)
					  ->order($order)
					  ->limit($start,$size)
					  ->select();
		$count = $this->alias('a')
		              ->where($where)
					  ->count('id');
		$objPage = new Page($count,$size);		  
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
        return $data;
	}
	public function getInfo($fields,$where, $order='id desc'){
	    $list = $this->field($fields)->where($where)->order($order)->select();
	    return $list;
	}
	
}