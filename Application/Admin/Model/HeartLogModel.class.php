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

			$sql = "SELECT $field FROM savor_heart_log join savor_box sb on sb.mac = savor_heart_log.box_mac WHERE TYPE = 2 and sb.state=1 and sb.flag=0 GROUP BY savor_heart_log.box_mac ORDER BY lt DESC";
		}

		$result = $this->query($sql);
		return  $result;
	}


	public function getBoxNum($hid){
		$sql = "  select b.mac from savor_box as b
			left join savor_room as r on b.room_id=r.id
			left join savor_hotel as h on r.hotel_id=h.id
			where h.id = $hid";

		$list = $this->query($sql);
		$len  = count($list);
		return $len;
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
			sht.maintainer,sht.hotel_box_type,shlog.pro_period,shlog.adv_period,
			shlog.pro_download_period,shlog.ads_download_period,shlog.net_speed'
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

	}
	public function getOnlineHotel($where,$fields = '*'){
	    $result = $this->field($fields)->where($where)->group('hotel_id')->select();
	    /* echo $this->getLastSql();
	     echo "<br>"; */
	    return $result;
	}
	
	public function getInfo($fileds,$where,$order){
	    $data = $this->field($fileds)->where($where)->order($order)->find();
	    return $data;
	
	}
	public function getHotelHeartBox($where,$fields = '*',$group=''){
	    $result = $this->field($fields)->where($where)->group($group)->select();
	    return $result;
	}

    public function deleteInfo($where,$limit){
        $ret = $this->where($where)->limit($limit)->delete();
        return $ret;
    }

}//End Class