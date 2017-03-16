<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 *
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class HotelExtModel extends BaseModel{
	protected $tableName='hotel_ext';

	public function saveData($table,$data, $id = 0) {
		$redis  =  \Common\Lib\SavorRedis::getInstance();
		$redis->select(15);
		$res = $this->where(array('hotel_id'=>$id))->find();
		if ($res) {
			$bool = $this->where('hotel_id='.$id)->save($data);
		} else {
			$bool = $this->add($data);
		}
		$s_key = $table.'_'.$id;
		$redis->set($s_key, json_encode($data));
		return $bool;
	}
}
