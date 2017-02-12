<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class MenuItemModel extends BaseModel
{

	public function getWhere($where, $order, $field){

		$list = $this->where($where)->order($order)->field($field)->select();



		return $list;
	}


	//删除数据
	public function delData($id) {
		$delSql = "DELETE FROM `savor_menu_item` WHERE menu_id = '{$id}'";
		$result = $this -> execute($delSql);

		return  $result;
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
