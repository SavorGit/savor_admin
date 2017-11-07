<?php
/**
 *@desc 专题组MODEL,对专题组MODEL类操作
 * @Package Name: SpecialGroupModel
 *
 * @author      白玉涛
 * @version     3.0.1
 * @copyright www.baidu.com
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class SpecialGroupModel extends BaseModel{
	protected $tableName='special_group';
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
	 * @desc 获取专题组对应关系的数据
	 * @access public
	 * @param $field 字段名
	 * @param $where 筛选条件
	 * @return boolean|array()
	 */
	public function fetchDataBySql($field,$where ) {
		$sql = " SELECT $field FROM savor_special_group
        sg INNER JOIN savor_special_relation sr ON sg.id = sr.sgid
        LEFT JOIN savor_media sm ON sm.id = sr.spictureid
        LEFT JOIN savor_mb_content smc ON smc.id =
        sr.sarticleid WHERE $where ";
		$res = $this->query($sql);
		if($res) {
			return $res;
		} else {
			return false;
		}
	}

	/**
	 * @desc 获取专题组一条数据
	 * @access public
	 * @param $field 字段名
	 * @param $where 筛选条件
	 * @param $field 顺序
	 * @return boolean|array()
	 */
	public function getOneRow($where, $field,$order){
		$list = $this->where($where)
			->order($order)
			->limit(1)
			->field($field)->select();
		if(empty($list)){
			return false;
		}else{
			return $list[0];
		}

	}



}
