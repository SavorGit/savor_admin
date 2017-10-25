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

}//End Class
