<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class MediaStaModel extends BaseModel
{
	protected $tableName='medias_sta';

	public function getWhere($where, $field,$group=''){
		$list = $this->where($where)
			->field($field)
			->group($group)
			->select();
		return $list;
	}

	public function getAdvMachine($where, $field,$group=''){
		$list = $this->alias('sms')
			->join('savor_box sbo on sbo.mac=sms.mac')
			->where($where)
			->field($field)
			->group($group)
			->select();
		return $list;
	}

	public function getList($where, $order='id desc', $start=0,$size=5){
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





}