<?php
/**
 *@author zhang.yingtao
 *@desc   餐厅端日志上报 
 *@since  20171211
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class DinnerHallLogModel extends BaseModel
{
	protected $tableName='dinnerapp_hall_log';

	public function getAllList($where, $order='id desc')
	{
		$field = ' dhlog.mobile,dhlog.invite_code,sht.name
		hotel_name,sro.name room_name,dhlog.welcome_word wew,
		dhlog.welcome_template wet, dhlog.screen_result, dhlog.screen_type,
		dhlog.device_type,dhlog.device_id,dhlog.screen_num,
		dhlog.screen_time,dhlog.info,dhlog.create_time';
		$res = $this->alias('dhlog')
					->field($field)
			        ->join('left join savor_hotel sht on sht.id = dhlog.hotel_id')
					->join('left join savor_room sro on sro.id = dhlog.room_id')
					->order($order)
					->select();
		return $res;

	}//End Function
}