<?php
/**
 * @desc   销售端-任务
 * @author zhang.yingtao
 * @since  2019-11-01
 */
namespace Admin\Model\Integral;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class TaskModel extends BaseModel{
	
	protected $tableName='integral_task';
	public function getList($fields,$where,$order,$start,$size){
	    $list = $this->alias('a')
	                 ->join('savor_sysuser user on a.uid=user.id','left')
	                 ->join('savor_sysuser euser on a.e_uid = euser.id','left')
	                 ->field($fields)
	                 ->where($where)
	                 ->order($order)
	                 ->limit($start,$size)
	                 ->select();
	    $count = count($list);
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
}