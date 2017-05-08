<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class AwardConfigModel extends BaseModel
{
	protected $tableName='award_config';

	public function getOne($id){
		if ($id) {
			$res = $this->find($id);
			return $res;
		}

	}

}