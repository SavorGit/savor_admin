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
	public function getListInfos($fields,$where,$order,$start=0,$size = 5){
	    $list = $this->alias('a')
	                 ->join('savor_hotel h on a.hotel_id=h.id','left')
	                 ->join('savor_area_info area on a.task_area=area.id','left')
	                 ->join('savor_sysuser puser on a.publish_user_id=puser.id','left')
	                 ->join('savor_sysuser apuser on a.appoint_user_id = apuser.id','left')
	                 ->join('savor_sysuser exeuser on a.exe_user_id = exeuser.id','left')
	                 ->field($fields)
	                 ->where($where)
	                 ->order($order)
	                 ->limit($start,$size)
	                 ->select();
	    $count = $this->alias('a')
	                  ->where($where)
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