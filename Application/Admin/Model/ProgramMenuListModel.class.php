<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class ProgramMenuListModel extends BaseModel
{
	protected $tableName='programmenu_list';

	public function getWhere($where, $field){
		$list = $this->where($where)->field($field)->select();
		return $list;
	}

	public function getAllmenu()
	{
		return $this->select();

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

	}

	//新增和修改
	public function addData($data, $acttype) {
		if(0 === $acttype) {
			$result = $this->add($data);
		} else {
			$uid = $data['id'];
			$result = $this->where("id={$uid}")->save($data);
		}
		return $result;
	}
}//End Class