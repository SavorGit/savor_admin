<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class SingleMenuModel extends BaseModel
{
	protected $tableName='single_menu';
    public function getList($fields,$where, $order='a.id desc', $start=0,$size=5){	
		 $list = $this->alias('a')
		              ->join('savor_sysuser user on a.creator_id=user.id','left')
		              ->field($fields)
		              ->where($where)
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
	public function addInfo($data){
	    $ret = $this->add($data);
	    return $ret;
	}
}