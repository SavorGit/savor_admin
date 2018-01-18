<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class MenuHotelModel extends BaseModel
{
	protected $tableName='menu_hotel';

	//查找其中的一条
	public function getPrvMenu($field, $where) {
		$data  = array();
		$sql = "SELECT $field FROM savor_menu_hotel smh JOIN savor_menu_list
smlist ON smh.menu_id = smlist.id  WHERE hotel_id IN (SELECT id FROM savor_hotel WHERE $where)";
		$InfoData    = $this->query($sql);
		$data        = !empty($InfoData)? $InfoData : $data;
		return $data;
	}

	public function getWhere($where, $order, $field){

		$list = $this->where($where)->order($order)->field($field)->select();

		return $list;
	}


	public function getList($where, $order='id desc', $start=0,$size=5)
	{	
		
		 $list = $this->where($where)
					  ->order($order)
					  ->limit($start,$size)
					  ->select();

		
		$count = $this->where($where)
					  ->count();

		$objPage = new Page($count,$size);		  

		$show = $objPage->admin_page();

		$data = array('list'=>$list,'page'=>$show);

        return $data;


	}


	/**
	 * getadsPeriod 获取酒楼广告期号拿最新的一条
	 * @access public
	 * @param $hotelid
	 * @return array
	 */
	public function getadsPeriod($hotelid){
		$sql = "select
        menu_hotel.id AS menuHotelId,
        menu_hotel.menu_id AS menuId,
        CONCAT(DATE_FORMAT(menu_hotel.update_time,'%m%d%H%i'),
		  DATE_FORMAT(list.update_time,'%m%d%H%i')) AS period,
        menu_hotel.pub_time AS pubTime,list.menu_name
        FROM savor_menu_hotel menu_hotel
        LEFT JOIN savor_menu_list list on menu_hotel.menu_id=list.id
        where menu_hotel.hotel_id = $hotelid
        ORDER BY menu_hotel.update_time desc,menu_hotel.id desc limit 1";
		$result = $this->query($sql);
		return $result;
	}




	




}//End Class
