<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class RtbPubAdsHotelModel extends BaseModel
{
	protected $tableName='pub_rtbads_hotel';

	public function getAdsHotelId($pub_ads_id){
		$fields = 'hotel_id';
		$where = array('pub_ads_id'=>$pub_ads_id);
		$group = '';
		$data = $this->field($fields)->where($where)->group($group)->select();
		return $data;
	}



}//End Class



