<?php
/**
 * @desc 活动数据
 * @author zhang.yingtao
 * @since  2017-07-21 
 */
namespace Admin\Model;
use Common\Lib\Page;
use Admin\Model\BaseModel;

class ActivityConfigModel extends BaseModel
{
	protected $tableName='activity_config';
	/**
	 * @desc  获取列表
	 */
	public function getList($field="*",$where,$order,$start,$size){
	    $list = $this->alias('a')
	    ->join('savor_sysuser b on a.operator_id = b.id')
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
	 * @desc 新增数据
	 */
	public function addInfo($data){
	    return $this->add($data);
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
	 * @desc 修改信息
	 */
	public function editInfo($map,$data){
	    $ret = $this->where($map)->save($data);
	    return $ret;
	}
}