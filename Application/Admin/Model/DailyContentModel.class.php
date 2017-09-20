<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class DailyContentModel extends BaseModel
{
	protected $tableName='daily_content';



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


	/**
	 * @desc 获取专题组对应关系的数据
	 * @access public
	 * @param $field 字段名
	 * @param $where 筛选条件
	 * @return boolean|array()
	 */
	public function fetchDataBySql($field,$where ) {
		$sql = " SELECT $field FROM savor_daily_content
        sg left JOIN savor_daily_relation sr ON sg.id = sr.dailyid
        LEFT JOIN savor_media sm ON sm.id = sr.spictureid
        left join savor_article_source sas on sas.id = sg.source_id
         WHERE $where ";
		$res = $this->query($sql);
		if($res) {
			return $res;
		} else {
			return false;
		}
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

	public function getWhere($where, $field, $order, $limit){
		$list = $this->where($where)
		->field($field)
		->order($order)
		->limit($limit)
		->select();
		return $list;
	}

	public function getList($field, $where, $order='id desc', $start=0,$size=5){
		$list = $this->alias('dc')
			->where($where)
			->field($field)
			->join('savor_daily_home  sh ON dc.id = sh.dailyid',
				'left')
			->join('savor_daily_lk  lk ON sh.lkid =  lk.id', 'left')
			->join('savor_sysuser su ON su.id = dc.creator_id ')
			->order($order)
			->limit($start,$size)
			->select();
		$count = $this->alias('dc')
			->where($where)
			->count();
		$objPage = new Page($count,$size);
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
		return $data;
	}





}