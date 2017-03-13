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

	public function saveData($data, $id = 0) {
		$redis  =  \Common\Lib\SavorRedis::getInstance();
		$redis->select(15);
		$table = 'savor_hotel_ext';
		//判定key是否有没有的，如果存在则修改
		if($id){
			//获取创建时间
			$bool = $this->where('id='.$id)->save($data);
			$s_key = $table.'_'.$id;
			$redis->set($s_key, json_encode($data));
		}else{
			$bool = $this->add($data);
			$insert_id = $this->getLastInsID();
			$s_key = $table.'_'.$insert_id;
			$redis->set($s_key, json_encode($data));
		}
		return $bool;
	}
}
