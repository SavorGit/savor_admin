<?php
/**
 * @desc 心跳上报历史统计数据表
 * @since 20170815
 * @author zhang.yingtao
 */
namespace Admin\Model;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class AdverWarnModel extends BaseModel
{
	protected $tableName='warning_list';
	
	/**
	 * @获取分页数据
	 */
	public function geWarntlist($field= '*',$where,$order,$start=0,$size=5){
	    $list = $this->alias('awarn')
		    ->field($field)
			->join('savor_box sb on sb.id = awarn.box_id')
			->where($where)
			->order($order)
			->limit($start,$size)
			->select();

	    $count = $this->where($where)->count();
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;  
	}

	public function getData($field= '*',$where,$order){
		$list = $this->alias('awarn')
			->field($field)
			->join('savor_box sb on sb.id = awarn.box_id')
			->where($where)
			->order($order)
			->select();
		return $list;
	}

}