<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class SingleMenuItemModel extends BaseModel
{
	protected $tableName='single_menu_item';
	public function getList($where, $order='id asc'){
	    $data = $this->where($where)
        	    ->order($order)
        	    ->select();
        
	    return $data;
	}
	public function addInfos($data){
	    $ret = $this->addAll($data);
	    return $ret;
	}
    
}