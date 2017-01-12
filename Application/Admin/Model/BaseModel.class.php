<?php
/**
 * model基类
 * @author hongwei <[<email address>]>
 *
 * 
 */
namespace Admin\Model;

use Think\Model;

class BaseModel extends Model
{


	/**
	 * 获取单条数据
	 * @param  string  $field  [description]
	 * @param  string  $filter [description]
	 * @param  integer $offset [description]
	 * @param  integer $limit  [description]
	 * @param  string  $order  [description]
	 * @param  string  $group  [description]
	 * @return [type]          [description]
	 */
	public function getRow($field='*',$filter='',$order='',$group='')
	{

		return $this->field($field)
				    ->where($filter)
				    ->order($order)
				    ->group($group)
				    ->find();

	}//End Function
	



	/**
	 * 获取多条数据
	 * @param  string  $field  [description]
	 * @param  string  $filter [description]
	 * @param  integer $offset [description]
	 * @param  integer $limit  [description]
	 * @param  string  $order  [description]
	 * @param  string  $group  [description]
	 * @return [type]          [description]
	 */
	public function getAll($field='*',$filter='',$offset=0,$limit=10,$order='',$group='')
	{

		return $this->field($field)
					->where($filter)
					->limit($offset,$limit)
					->order($order)
					->group($group)
					->select();

	}//End Function








}//End CLASS

