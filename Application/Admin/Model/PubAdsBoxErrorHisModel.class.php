<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;

class PubAdsBoxErrorHisModel extends BaseModel
{
	protected $tableName='pub_ads_box_error_history';

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

	public function removeToNew($insfield, $oldfield, $where,$newtable){
		$list = $this->where($where)->field($oldfield)->selectAdd($insfield, $newtable);
		return $list;
	}

	public function deleteInfo($where){
		$ret = $this->where($where)->delete();
		return $ret;
	}

}//End Class



