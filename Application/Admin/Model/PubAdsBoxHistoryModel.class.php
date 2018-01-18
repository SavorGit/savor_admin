<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class PubAdsBoxHistoryModel extends BaseModel
{
	protected $tableName='pub_ads_box_history';

	public function getBoxInfoBySize($field, $where,$order='id desc',$group, $start, $size) {
		$list = $this->alias('adbox')
			->field($field)
			->where($where)
			->join('savor_box box on box.id = adbox.box_id', 'left')
			->join('savor_room room on room.id = box.room_id', 'left')
			->join('savor_hotel sht on sht.id = room.hotel_id', 'left')
			->group($group)
			->order($order)
			->limit($start,$size)
			->select();

		$data = array('list'=>$list);
		return $data;

	}

	public function deleteInfo($where){
		$ret = $this->where($where)->delete();
		return $ret;
	}

	public function getDataCount($where){
		$count = $this->where($where)
			->count();
		return $count;

	}


	public function getCurrentBox($field, $where, $group) {
		$list = $this->alias('adbox')
			->join('savor_box box on box.id = adbox.box_id')
			->join('savor_room room on room.id = box.room_id')
			->join('savor_hotel sht on sht.id = room.hotel_id')
			->group($group)
			->where($where)
			->field($field)
			->select();
		return $list;
	}

	public function getWhere($where, $field){
		$list = $this->where($where)->field($field)->select();
		return $list;
	}


}//End Class


