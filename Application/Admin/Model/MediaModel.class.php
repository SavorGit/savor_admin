<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class MediaModel extends BaseModel
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





	




}//End Class
