<?php
/**
 *@author hongwei
 * 
 * 
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;
class HeartLogModel extends BaseModel
{
	protected $tableName='heart_log';


	public function getWhereData($field='', $type ) {

		//Сƽ̨
		if($type == 1) {
			$sql = "SELECT $field FROM savor_heart_log  WHERE TYPE = 1  GROUP BY hotel_id ORDER BY lt DESC";
		}else{

			$sql = "SELECT $field FROM savor_heart_log  WHERE TYPE = 2  GROUP BY box_mac ORDER BY lt DESC";
		}

		$result = $this->query($sql);
		return  $result;
	}


	public function getAllBox($where,  $field, $tp){
		if($tp == 1){
			$sql = " select $field from savor_hotel_ext as hex
			left join savor_hotel as h on hex.hotel_id=h.id
            where h.id in (select id from savor_hotel sht 			where $where)";
		}else{
			$sql = "  select $field from savor_box as b
			left join savor_room as r on b.room_id=r.id
			left join savor_hotel as h on r.hotel_id=h.id
			where h.id in (select id from savor_hotel sht where 			$where) and ( b.state = 1 and b.flag = 0)";
		}
		$list = $this->query($sql);
		return $list;

	}


	public function getList($where, $order='id desc', $start=0,$size=5)
	{

		$list = $this->alias('shlog')
			->join(' savor_hotel sht on sht.id = shlog.hotel_id', 'LEFT')
			->field('shlog.area_name,shlog.area_id,shlog.type,
			shlog.last_heart_time,shlog.box_id,shlog.box_mac,shlog.room_id,
			shlog.room_name,shlog.hotel_id,shlog.hotel_ip,shlog.small_ip,
			shlog.ads_period,shlog.demand_period,shlog.apk_version,
			shlog.war_version,shlog.logo_period,shlog.hotel_name,
			sht.maintainer,sht.hotel_box_type'
	              )
			->where($where)
			->order($order)
			->limit($start,$size)
			->select();
		$count = $this->alias('shlog')
			->join(' savor_hotel sht on sht.id = shlog.hotel_id', 'LEFT')
			->where($where)
			->count();

		$objPage = new Page($count,$size);

		$show = $objPage->admin_page();


		$data = array('list'=>$list,'page'=>$show);


		return $data;

	}//End Function



	/**
	 * [areaIdToAareName description]
	 * @param  array  $result [description]
	 * @return [type]         [description]
	 */
	public function areaIdToAareName($result=[])
	{
		if(!$result || !is_array($result))
		{
			return [];
		}

		$area = $this->getAllArea();

		foreach ($result as &$value) 
		{
			foreach($area as $row)
			{	
				if($value['area_id'] == $row['id'])
				{
					$value['area_name'] = $row['region_name'];
				}
					
			}

		}

		return $result;

	}//End Function




}//End Class