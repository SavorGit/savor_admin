<?php
/**
 * Created by PhpStorm.
 * User: baiyutao
 * Date: 2017/5/16
 * Time: 13:54
 */
namespace Admin\Model;
use Think\Model;

class RepairDetailModel extends BaseModel
{
	protected $tableName='repair_detail';



	public function fetchDataWhere($where, $order, $field, $type=1){
		if( $type == 1) {
			$list = $this->where($where)->order($order)->field($field)->find();
		} else {
			$list = $this->where($where)->order($order)->field($field)->select();
		}
		return $list;
	}




}//End Class