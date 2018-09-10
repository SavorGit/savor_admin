<?php
/**
 * @desc   小程序投屏日志 
 * @author zhang.yingtao
 *
 */
namespace Admin\Model\Smallapp;
use Think\Model;
use Common\Lib\Page;
class TurntableDetailModel extends Model
{
	protected $tableName='smallapp_turntable_detail';
	public function getWhere($fields,$where,$order,$limit){
	    $data = $this->field($fields)->where($where)->order($order)->limit($limit)->select();
	    return $data;
	}
	public function countNums($where){
	    $nums = $this->where($where)->count();
	    return $nums;
	}
	public function updateInfo($where,$data){
	    $ret = $this->where($where)->save($data);
	    return $ret;
	}
}