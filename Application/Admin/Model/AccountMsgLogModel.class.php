<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;


use Admin\Model\BaseModel;

class AccountMsgLogModel extends BaseModel{
	protected $tableName = 'account_msg_log';




	public function addData($data) {
		$bool = $this->add($data);
		return $bool;
	}



}
