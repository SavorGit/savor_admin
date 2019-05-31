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
		
		$redis->select(12);
		$cache_key = C('SMALL_HOTEL_INFO').$id;
		$redis->remove($cache_key);
		
		$hotelModel = new \Admin\Model\HotelModel();
		$v_hotel_result = $hotelModel->getListMac('a.id hotel_id',array('b.mac_addr'=>'000000000000'),'a.id asc');
		$redis->select(10);
		$cache_key = C('VSMALL_HOTELLIST');
		$redis->set($cache_key, json_encode($v_hotel_result));	
	}


	public function getData($field, $where){
		$list = $this->field($field)->where($where)->select();
		return $list;
	}

	public function getOneData($field, $where){
		$list = $this->field($field)->where($where)->find();
		return $list;
	}

	public function isHaveMac($field,$where){
	    $sql ="select $field from savor_hotel_ext as he 
	           left join savor_hotel as h on he.hotel_id = h.id where ".$where;
	    $result = $this->query($sql);
	    return $result;
	}
}
