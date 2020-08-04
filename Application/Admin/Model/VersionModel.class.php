<?php
namespace Admin\Model;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class VersionModel extends BaseModel{
	protected $tableName='device_version';

	public function getList($where, $order='id desc', $start=0,$size=5){
		$list = $this->where($where)
			->order($order)
			->limit($start,$size)
			->select();
		$count = $this->where($where)->count();
		$objPage = new Page($count,$size);
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
		return $data;
	}

	public function getAllList($filed,$where,$order,$limit=''){
		$data = $this->field($filed)->where($where)->order($order)->limit($limit)->select();
	    return $data;
	}
}