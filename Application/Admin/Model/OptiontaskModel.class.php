<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class OptiontaskModel extends BaseModel
{
	protected $tableName='option_task';
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

	public function countNums($where){
	    $nums = $this->where($where)->count();
	    return $nums;
	}
	public function getListByGroup($fields,$where,$order,$group, $limit){
		$data = $this->field($fields)->where($where)->group($group)->order($order)->limit($limit)->select();
		return $data;
	}
}