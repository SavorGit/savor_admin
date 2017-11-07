<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class DailyShareWeixinModel extends BaseModel{
	protected $tableName='daily_share_weixin';
	public function addData($data  = array()){
		if(!empty($data) && is_array($data)){
			$this->add($data);
			$id = $this->getLastInsID();
			return $id;
		}else {
			return false;
		}
	}

	public function getOne($map = array(),$order='')
	{
		if (!empty($map)) {
			$result = $this->where($map)->order($order)->find();
			return $result;
		}
	}
}
