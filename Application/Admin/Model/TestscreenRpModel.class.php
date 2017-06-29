<?php
/**
 *@author hongwei
 * 
 * 
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;
class TestscreenRpModel extends BaseModel
{
	protected $trueTableName='';

	public function __construct($table) {
		parent::__construct();
		$this->trueTableName = $table;

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

	}//End Function

	public function getAllList($where, $order='id desc')
	{

		$list = $this->where($where)
			->order($order)
			->select();
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