<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class ProgramMenuHotelModel extends BaseModel
{
	protected $tableName='programmenu_hotel';

	//查找其中的一条
	public function getPrvMenu($field, $where) {
		$data  = array();
		$sql = "SELECT $field FROM savor_programmenu_hotel smh JOIN savor_programmenu_list
smlist ON smh.menu_id = smlist.id  WHERE hotel_id IN (SELECT id FROM savor_hotel WHERE $where) order by smlist.id desc limit 50";
		$InfoData    = $this->query($sql);
		$data        = !empty($InfoData)? $InfoData : $data;
		return $data;
	}

	public function getWhere($where, $order, $field, $group=''){

		$list = $this->where($where)
					->order($order)
					->field($field)
					->group($group)
					->select();

		return $list;
	}

	public function getMenuHotelPub($where, $field){

		$list = $this->alias('nh')
			         ->where($where)
			         ->join('savor_hotel nho on nho.id = nh
			         .hotel_id')
			         ->field($field)
			         ->select();

		return $list;
	}
	public function getProgramByHotelId($hotel_id,$fields,$order,$limit){
	    $where =  array();
	    $where['a.hotel_id'] = $hotel_id;
	    $where['pl.state'] = 1;
	    $data = $this->alias('a')
	         ->join('savor_programmenu_list pl on a.menu_id=pl.id','left')
	         ->field($fields)
	         ->where($where)
	         ->order($order)
	         ->limit($limit)
	         ->select();
	    return $data;
	}
	public function countWhere($where,$group){
	    $nums = $this->where($where)->group($group)->count();
	    return $nums;
	}

}//End Class
