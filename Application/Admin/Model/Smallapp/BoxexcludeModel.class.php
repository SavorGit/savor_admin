<?php
namespace Admin\Model\Smallapp;
use Think\Model;
use Common\Lib\Page;
class BoxexcludeModel extends Model{
	protected $tableName='smallapp_static_boxexclude';

	public function addInfo($data,$type=1){
	    if($type ==1){
	        $ret = $this->add($data);
	    }else {
	        $ret = $this->addAll($data);
	    }
	    return $ret;
	}

	public function getOne($fields,$where){
	    $data = $this->field($fields)->where($where)->find();
	    return $data;
	}

	public function getList($fields,$where,$order,$start,$size){
	    $list = $this->field($fields)->where($where)->order($order)->limit($start,$size)->select();
        $count = $this->where($where)->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
	}

    public function updateData($condition,$data){
        $result = $this->where($condition)->save($data);
        return $result;
    }

	public function delData($where){
	    $res = $this->where($where)->delete();
	    return $res;
    }
}