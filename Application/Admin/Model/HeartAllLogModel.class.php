<?php
/**
 * @desc 心跳上报历史统计数据表
 * @since 20170815
 * @author zhang.yingtao
 */
namespace Admin\Model;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class HeartAllLogModel extends BaseModel
{
	protected $tableName='heart_all_log';
	
	/**
	 * @获取分页数据
	 */
	public function getlist($field= '*',$where,$order,$start=0,$size=5){
	    $list = $this->field($field)->where($where)->order($order)->limit($start,$size)->select();
	    $count = $this->where($where)->count();
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;  
	}
}