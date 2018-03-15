<?php
namespace Admin\Model;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class UpgradeModel extends BaseModel{
	protected $tableName='device_upgrade';

	public function getLastOneByDevice($field, $device_type, $hotel_id){
		$where = " 1 and FIND_IN_SET('".$hotel_id."', sug.`hotel_id`) and
		sug.`device_type`='".$device_type ."' and sdv.`device_type` = '".$device_type."'";
		$info = $this->alias('sug')
			         ->field($field)
					 ->join('LEFT JOIN savor_device_version sdv
					 ON sug.VERSION = sdv.version_code')
					 ->where($where)
			         ->order(' sug.create_time desc')
			         ->find();
		return $info;
	}

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

	public function getAllList($filed,$where,$order){
		$data = $this->field($filed)->where($where)->order($order)->select();
	    return $data;
	}
}