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


	public function saveData($data, $where) {
		$bool = $this->where($where)->save($data);
		return $bool;
	}

	public function addData($data) {
		$result = $this->add($data);
		return $result;
	}

	public function saveStRedis($data, $id){
		$redis  =  \Common\Lib\SavorRedis::getInstance();
		$redis->select(15);
		$cache_key = C('DB_PREFIX').$this->tableName.'_'.$id;
		$redis->set($cache_key, json_encode($data));
	}


	public function getData($field, $where){
		$list = $this->field($field)->where($where)->select();
		return $list;
	}
}
