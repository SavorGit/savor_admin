<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class DailyLkModel extends BaseModel{
	protected $tableName='daily_lk';
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


	public function getCount($where, $field){
		$number = $this->where($where)
			->field($field)
			->count();
		return $number;

	}

	/**
	 * @desc 添加数据
	 * @access public
	 * @param mixed $data 数据
	 * @return boolean
	 */
	public function addData($data) {
		$bool = $this->add($data);
		return $bool;
	}


}
