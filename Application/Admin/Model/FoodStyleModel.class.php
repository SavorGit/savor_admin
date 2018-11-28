<?php
/**
 * @author zhang.yingtao
 * @desc   酒楼菜系
 * @since  20181127
 * 
 */
namespace Admin\Model;
use Common\Lib\Page;
use Think\Model;

class FoodStyleModel extends Model
{
	protected $tableName='hotel_food_style';
    public function getList($fields,$where, $order='id desc', $start=0,$size=5){	
		 $list = $this->field($fields)
		              ->where($where)
					  ->order($order)
					  ->limit($start,$size)
					  ->select();
		$count = $this->where($where)
					  ->count();
		$objPage = new Page($count,$size);		  
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
        return $data;
	}
	public function getWhere($fields,$where,$order,$limit){
	    $data = $this->field($fields)->where($where)->order($order)->limit($limit)->select();
	    return $data;
	}
	public function updateInfo($where,$data){
	    $ret = $this->where($where)->save($data);
	    return $ret;
	}public function getOne($fields,$where,$order){
	    $data =  $this->field($fields)->where($where)->order($order)->find();
	    return $data;
	}
	public function countNum($where){
	    $nums = $this->where($where)->count();
	    return $nums;
	}
	public function addInfo($data,$type=1){
	    if($type==1){
	        $ret = $this->add($data);
	
	    }else {
	        $ret = $this->addAll($data);
	    }
	    return $ret;
	}
}