<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class ProgramMenuItemModel extends BaseModel
{
	protected $tableName='programmenu_item';


	public function getWhere($where, $order, $field){

		$list = $this->where($where)->order($order)->field($field)->select();



		return $list;
	}

	public function getCopyMenuInfo($where, $order, $field){

		$list = $this->alias('spi')
					 ->where($where)
					 ->join('`savor_ads` as sads on spi.ads_id = sads.id','left')
					 ->order($order)
					 ->field($field)
					 ->select();



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


	}//End Function





	




}//End Class
