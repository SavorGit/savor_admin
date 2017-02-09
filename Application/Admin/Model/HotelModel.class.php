<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class HotelModel extends BaseModel
{

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


	}//End Function





	/**
	 * 酒店ID转换为酒店名称
	 * @param  array  $result [description]
	 * @return [type]         [description]
	 */
	public function hotelIdToName($result=[])
	{
		if(!$result || !is_array($result))
		{
			return [];
		}

		$arrHotelId = [];

		foreach ($result as $value) 
		{
			$arrHotelId[] = $value['hotel_id'];
		}

		$filter       = [];
		$filter['id'] = ['IN',$arrHotelId];

		
		$arrHotel = $this->getAll('id,name',$filter);

		foreach ($result as &$value) 
		{
			foreach ($arrHotel as  $row) 
			{
				if($value['hotel_id'] == $row['id'])
				{
					$value['hotel_name'] = $row['name'];
				}
			}
		}
		
		return $result;

	}//End Function





}//End Class
