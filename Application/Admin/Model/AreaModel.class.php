<?php
/**
 *@author hongwei
 * 
 * 
 */
namespace Admin\Model;

use Think\Model;

class AreaModel extends Model
{

	/**
	 * 
	 * 
	 * @return [type] [description]
	 */
	public function getAllArea()
	{
		return $this->limit(20)->select();

	}



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
					$value['area_name'] = $row['name'];
				}
					
			}

		}

		return $result;

	}//End Function




}//End Class