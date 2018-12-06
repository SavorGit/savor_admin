<?php
/**
 * @desc   小程序数据统计
 * @author zhang.yingtao
 * @since  2018-11-22
 */
namespace Admin\Model\Smallapp;
use Think\Model;
use Common\Lib\Page;
class StatisticsModel extends Model
{
	protected $tableName='smallapp_statistics';
	public function getPageList($fields,$where,$order,$group,$start,$size){
	    $list = $this->alias('a')
	                 ->join('savor_sysuser user on a.maintainer_id=user.id','left')
	                 ->field($fields)->where($where)->order($order)->group($group)->limit($start,$size)->select();
	    $ret = $this->alias('a')
	                ->where($where)
	                ->group($group)
	                ->select();
	    $count = count($ret);
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
	public function getWhere($fields,$where,$order,$limit,$group){
	    $data = $this->field($fields)->where($where)->order($order)->group($group)->limit($limit)->select();
	    return $data;
	}
	public function getOne($fields,$where,$order){
	    $data =  $this->field($fields)->where($where)->order($order)->find();
	    return $data;
	}
	public function countNum($where){
	    $nums = $this->where($where)->count();
	    return $nums;
	}
}