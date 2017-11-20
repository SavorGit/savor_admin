<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class PubAdsHotelModel extends BaseModel
{
	protected $tableName='pub_ads_hotel';



	public function getCurrentBox($field, $where, $group) {
		$list = $this->alias('adhotel')
			->join('savor_hotel sht on sht.id = adhotel.hotel_id', 'left')
			->join('savor_room room on room.hotel_id = sht.id', 'left')
			->join('savor_box box on box.room_id = room.id', 'left')
			->group($group)
			->where($where)
			->field($field)
			->select();
		return $list;
	}


	public function getAdsHotelId($pub_ads_id){
		$fields = 'hotel_id';
		$where = array('pub_ads_id'=>$pub_ads_id);
		$group = '';
		$data = $this->field($fields)->where($where)->group($group)->select();
		return $data;
	}



}//End Class



