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

	

	public function getRepairDetail($field, $where, $order, $start, $size) {
		//上拉
		$where .= " and sru.flag = 0 and (sbo.flag=0 or sbo.flag is null)";

		$sql = "SELECT ".$field." FROM savor_repair_box_user sru
		JOIN savor_sysuser sys ON sys.id = sru.userid
		LEFT JOIN savor_box sbo ON sbo.mac = sru.mac JOIN savor_hotel
		 sht ON sht.id = sru.hotel_id LEFT JOIN savor_repair_detail
		 sdetail ON sru.id = sdetail.repair_id WHERE ".$where."
		 GROUP BY sru.id ORDER BY ".$order." limit ". $start.','
			.$size;
		$result = $this->query($sql);

		$sqlb = "SELECT count(*) number FROM savor_repair_box_user sru
		JOIN savor_sysuser sys ON sys.id = sru.userid
		LEFT JOIN savor_box sbo ON sbo.mac = sru.mac JOIN savor_hotel
		 sht ON sht.id = sru.hotel_id LEFT JOIN savor_repair_detail
		 sdetail ON sru.id = sdetail.repair_id WHERE ".$where."
		 GROUP BY sru.id";

		$count_arr =  $this->query($sqlb);
		$count = count($count_arr);
		$objPage = new Page($count,$size);
		$show = $objPage->admin_page();
		$data = array('list'=>$result,'page'=>$show);
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