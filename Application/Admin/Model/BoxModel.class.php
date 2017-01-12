<?php
/**
 *机顶盒model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;
use Admin\Model\RoomModel;

class BoxModel extends BaseModel
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
	 * 包间ID转换为包间名称
	 * @param  array  $result [description]
	 * @return [type]         [description]
	 */
	public function roomIdToRoomName($result=[])
	{
		if(!$result || !is_array($result))
		{
			return [];
		}

		$arrHotelId = [];

		foreach ($result as $value) 
		{
			$arrHotelId[] = $value['room_id'];
		}

		$filter       = [];
		$filter['id'] = ['IN',$arrHotelId];

		$roomModel = new RoomModel;
		
		$arrHotel = $roomModel->getAll('id,name',$filter);
		
		foreach ($result as &$value) 
		{
			foreach ($arrHotel as  $row) 
			{
				if($value['room_id'] == $row['id'])
				{
					$value['room_name'] = $row['name'];
				}
			}
		}
		
		return $result;

	}//End Function





}//End Class
