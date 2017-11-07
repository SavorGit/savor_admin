<?php
/**
 *@desc 专题组MODEL,对专题组关系MODEL类操作
 * @Package Name: SpecialGroupRelationModel
 *
 * @author      白玉涛
 * @version     3.0.1
 * @copyright www.baidu.com
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class SpecialGroupRelationModel extends BaseModel{
	protected $tableName='special_relation';
	/**
	 * @desc 获取专题组列表和页数
	 * @method getList
	 * @access public
	 * @http post
	 * @param numPerPage intger 显示每页记录数
	 * @param pageNum intger 当前页数
	 * @param _order $record 排序
	 * @return void|array
	 */
	public function getList($join, $where, $order='id desc', $start=0,$size=5){
		 if ($join == 1) {

			 $joina = 'savor_sysuser ss on ss.id = sg.creator_id';
			 $field = ' sg.id, sg.NAME,sg.create_time,sg.update_time,sg.state,ss.username ';
		 } else {
			 $joina = '';
			 $field = '';
		 }
		 $list = $this->alias('sg')
		              ->where($where)
					  ->order($order)
			          ->field($field)
					  ->limit($start,$size)
			          ->join($joina)
					  ->select();
		$count = $this->alias('sg')
			          ->where($where)
					  ->count();

		$objPage = new Page($count,$size);		  
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
        return $data;
	}

	/**
	 * @desc 保存数据
	 * @access public
	 * @param mixed $data 数据
	 * @param array $options 表达式
	 * @return boolean
	 */
	public function saveData($data, $where) {
		$bool = $this->where($where)->save($data);
		return $bool;
	}

	/**
	 * @desc 添加数据
	 * @access public
	 * @param mixed $data 数据
	 * @return boolean
	 */
	public function addData($data) {
		$bool = $this->add($data);
		return $bool;
	}


	/**
	 * @desc 删除数据
	 * @access public
	 * @param mixed $where 数据
	 * @param array $options 表达式
	 * @return boolean
	 */
	public function delData($where) {
		$bool = $this->where($where)->delete();
		return $bool;
	}

	public function judgeArtRelation($fields, $map){

		$data = $this->alias('sgrp')
			->field($fields)
			->join('savor_special_group sgr ON sgrp.sgid =
					 sgr.id')
			->where($map)
			->find();
		return $data;
	}


}
