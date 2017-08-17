<?php
/**
 *@author zhang.yingtao
 * @desc app下载统计
 * 
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;
class SmallPlaModel extends BaseModel
{
	protected $tableName='small_platform';

	public function getWarnAll($where){
		$sql = "SELECT sht.name hname , spl.hotel_ip,spl.area_id,spl.small_ip,spl.state,spl.remark,spl.remark1,spl.create_time FROM  savor_hotel sht  JOIN savor_small_platform spl ON spl.hotel_id=sht.id WHERE $where";
		$list = $this->query($sql);
		return $list;
	}


	public function getWarnInfo($where, $order='id desc', $start=0,$size=5){
		$sql = "SELECT sht.name hname , spl.hotel_ip,spl.area_id,spl.small_ip,spl.state,spl.remark,spl.remark1,spl.create_time
        FROM (SELECT max(`id`) spid, hotel_id FROM savor_small_platform
        group by  hotel_id) spm join savor_small_platform spl  on spm.hotel_id = spl.hotel_id and
        spm.spid =  spl.id JOIN savor_hotel sht
        ON spm.hotel_id=sht.id WHERE $where order by $order limit $start, $size";

		$list = $this->query($sql);
		$sqlb = "SELECT hotel_id FROM savor_small_platform spl JOIN savor_hotel sht  ON spl.hotel_id=sht.id WHERE $where
        group by  spl.hotel_id";
		$count_arr = $this->query($sqlb);
		$count = count($count_arr);
		$objPage = new Page($count,$size);
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
		return $data;
	}


}