<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;

class HotCategoryModel extends BaseModel
{
	protected $tableName='mb_hot_category';

	public function getWhere($where, $field){
		$list = $this->where($where)->field($field)->select();
		return $list;
	}

}