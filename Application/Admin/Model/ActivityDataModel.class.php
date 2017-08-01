<?php
/**
 * @desc 活动数据
 * @author zhang.yingtao
 * @since  2017-07-18 
 */
namespace Admin\Model;
use Common\Lib\Page;
use Admin\Model\BaseModel;

class ActivityDataModel extends BaseModel
{
	protected $tableName='activity_data';
	/**
	 * @desc  获取列表
	 */
	public function getList($field="*",$where,$order,$start,$size){
	    $list = $this->alias('a')
	                 ->field($field)
	                 ->where($where)
					 ->order($order)
					 ->limit($start,$size)
					 ->select();
		$count = $this->alias('a')
		              ->where($where)
					  ->count();
		$objPage = new Page($count,$size);		  
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
		return $data;
	}
	/**
	 * @desc 获取数据
	 */
	public function getInfo($field = "*",$where='',$order,$limit ,$type = 1){
	    if($type ==1){
	        $data = $this->field($field)->where($where)->order($order)->limit($limit)->find();
	    }else {
	        $data = $this->field($field)->where($where)->order($order)->limit($limit)->select();
	    }
	    return $data;
	}
	/**
	 * @desc 添加数据
	 */
	public function addInfo($data){
	    $ret = $this->add($data);
	    return $ret;
	}
	/**
	 * @desc 统计数据
	 */
	public function countData($where){
	    $count = $this->where($where)->count();
	    return $count;
	}
}