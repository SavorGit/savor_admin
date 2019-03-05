<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model\Smallapp;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class RedpacketReceiveModel extends BaseModel
{
	protected $tableName='smallapp_redpacket_receive';
	
	
	public function getOne($fields,$where){
	    $data = $this->field($fields)->where($where)->find();
	    return $data;
	}
	public function countWhere($where){
	    $nums = $this->where($where)->count();
	    return $nums;
	}
	public function getList($fields,$where,$order,$start,$size){
	    $data = $this->alias('a')
	         ->join('savor_smallapp_user user on a.user_id=user.id','left')
	         ->field($fields)
	         ->where($where)
	         ->order($order)
	         ->limit($start,$size)
	         ->select();
	    return $data;
	}
}