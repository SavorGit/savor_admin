<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class DailyHomeModel extends BaseModel{
	protected $tableName='daily_home';
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


	public function getDailyHomeInfo($where, $field) {
		$list = $this->alias('sh')
			->where($where)
			->field($field)
			->join('savor_daily_content sc ON sh.dailyid= sc.id')
			->join('savor_daily_lk lk  ON sh.lkid = lk.id')
			->join('savor_sysuser su ON su.id = lk.creator_id ')
			->select();
		return $list;
	}
	

}
