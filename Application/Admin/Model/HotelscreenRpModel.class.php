<?php
/**
 *@author hongwei
 * 
 * 
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;
class HotelscreenRpModel extends BaseModel
{
	protected $trueTableName='';

	public function __construct() {
		parent::__construct();
		$this->trueTableName = 'box_play_msg';

	}

	public function getList($where, $order='id desc', $start=0,$size=5)
	{
		$field = '`area_name`,`hotel_name`,`room_name`,`mac`,`play_date`,`ads_name`,sum(`duration`) dur,sum(`play_count`) plc   ';
		//没有主键的话count(1)比count(*)快
		$fieldb = 'count(1) cot';
		$sqla = "select $field from $this->trueTableName where $where group by `hotel_id`,`room_id`,`mac`,`media_id` order by $order limit $start, $size";

		//$sqlb = "select $fieldb from $this->trueTableName where $where group by `hotel_id`,`room_id`,`box_mac`,`mobile_id` order by $order";
		$fieldb = 'count(distinct `hotel_id`,`room_id`,`mac`,`media_id`) cot';
		$sqlb = "select $fieldb from $this->trueTableName where $where";
		$list = $this->query($sqla);
		$count = $this->query($sqlb);
		$count = $count[0]['cot'];
		$objPage = new Page($count,$size);

		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
		return $data;

	}//End Function

	public function getAllList($where, $order='id desc')
	{

		$field = '`area_name`,`hotel_name`,`room_name`,`mac`,`play_date`,`ads_name`,sum(`duration`) dur,sum(`play_count`) plc   ';
		//没有主键的话count(1)比count(*)快
		$sqla = "select $field from $this->trueTableName where $where group by `hotel_id`,`room_id`,`mac`,`media_id` order by $order";
		$list = $this->query($sqla);
		$data = array('list'=>$list);
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