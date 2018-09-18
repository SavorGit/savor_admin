<?php
/**
 * @desc   下程序网络情况统计
 * @author zhang.yingtao
 * @since  2018-09-11
 */
namespace Admin\Model\Smallapp;
use Think\Model;
use Common\Lib\Page;
class StaticNetModel extends Model
{
	protected $tableName='smallapp_static_net';
	
	
	public function addInfo($data,$type = 1){
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
	public function getWhere($fields,$where,$order,$group,$start,$size){
	    $list = $this->alias('a')
	                 ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
	                 ->join('savor_area_info area on hotel.area_id=area.id','left')
	                 ->field($fields)->where($where)->order($order)
	                 ->limit($start,$size)->group($group)->select();
	    $ret = $this->alias('a')
	         ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
	         ->where($where)
	         ->group($group)
	         ->select();
	    $count = count($ret);
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
	public function searchList($fields,$where,$order,$group,$start,$size){
	    $data = $this->field($fields)->where($where)->order($order)
	                 ->limit($start,$size)->group($group)->select();
	    return $data;
	}
	public function countWhere($where){
	    $nums = $this->where($where)->count();
	    return $nums;
	}
	public function getList($fields,$where,$order,$group,$start,$size){
	    $list = $this->alias('a')
	                 ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
	                 ->join('savor_area_info area on hotel.area_id=area.id','left')
	                 ->field($fields)->where($where)->order($order)
	                 ->limit($start,$size)->group($group)->select();
	    
	    return $list;
	}
}