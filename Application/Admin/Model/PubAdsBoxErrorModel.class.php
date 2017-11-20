<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;

class PubAdsBoxErrorModel extends BaseModel
{
	protected $tableName='pub_ads_box_error';

	public function addData($data, $acttype) {
		if(0 == $acttype) {
			$result = $this->add($data);
		} else {
			$uid = $data['id'];
			$result = $this->where("id={$uid}")->save($data);
		}
		return $result;
	}

	public function getWhere($where, $field, $group=''){
		$list = $this->where($where)
					 ->field($field)
					 ->group($group)
			         ->select();

		return $list;
	}

	public function getList($where,$order='id desc', $start, $size) {
		$list = $this->where($where)
			->order($order)
			->limit($start,$size)
			->select();
		$count = $this->where($where)
					  ->count();


		$data = array('list'=>$list, 'count'=>$count);
		return $data;
	}

}//End Class



