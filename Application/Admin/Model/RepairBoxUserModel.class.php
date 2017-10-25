<?php
/**
 * Created by PhpStorm.
 * User: baiyutao
 * Date: 2017/5/16
 * Time: 13:54
 */
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;
class RepairBoxUserModel extends BaseModel
{
	protected $tableName='repair_box_user';

	public function getRepairInfo($field, $where, $order) {
		//上拉
		$where .= " and sru.flag = 0 and (sbo.flag=0 or sbo.flag is null)";

		$sql = "select ".$field." FROM savor_repair_box_user
		sru JOIN savor_sysuser sys ON sys.id = sru.userid
		left JOIN savor_box sbo ON sbo.mac = sru.mac JOIN
		savor_hotel sht ON sht.id = sru.hotel_id where ".$where." order by ".$order;
		$result = $this->query($sql);
/*
		$sqlb = "select count(*) number FROM savor_repair_box_user
		sru JOIN savor_sysuser sys ON sys.id = sru.userid
		left JOIN savor_box sbo ON sbo.mac = sru.mac JOIN
		savor_hotel sht ON sht.id = sru.hotel_id where ".$where;
		$count_arr =  $this->query($sqlb);
		$count = $count_arr[0]['number'];*/
		$data = array('list'=>$result);
		return $data;
	}

	public function getRepairUserInfo($fields, $map){
		$map['flag'] = 0;
		$data = $this->alias('sru')
					 ->field($fields)
					 ->join('savor_sysuser sys ON sys.id =
					 sru.userid')
					 ->where($map)
					 ->select();
		return $data;
	}


}//End Class