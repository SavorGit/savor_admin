<?php
/**
 * @desc   小程序用户切换tabbar埋点
 * @author zhang.yingtao
 * @since  2018-10-30
 */
namespace Admin\Model\Smallapp;
use Think\Model;
use Common\Lib\Page;
class StaticConfigModel extends Model
{
	protected $tableName='smallapp_static_config';
	public function addInfo($data,$type=1){
	    if($type==1){
	        $ret = $this->add($data);
	         
	    }else {
	        $ret = $this->addAll($data);
	    }
	    return $ret;
	}
	public function updateInfo($where,$data){
	    $ret = $this->where($where)->save($data);
	    return $ret;
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