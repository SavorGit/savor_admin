<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model\Smallapp;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class ForscreenRecordModel extends BaseModel
{
	protected $tableName='smallapp_forscreen_record';
	
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
	    $data = $this->alias('a')
	                 ->join('savor_box box on a.box_mac=box.mac','left')
	                 ->join('savor_room room on box.room_id=room.id','left')
	                 ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	                 ->field($fields)->where($where)
	                 ->order($order)->limit($limit)
	                 ->group($group)->select();
	    return $data;
	}
	public function getOne($fields,$where){
	    $data = $this->field($fields)->where($where)->find();
	    return $data;
	}
	public function countWhere($where){
	    $nums = $this->alias('a')
	                 ->join('savor_box box on a.box_mac=box.mac','left')
	                 ->join('savor_room room on box.room_id=room.id','left')
	                 ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	                 ->where($where)->count();
	    return $nums;
	}
	public function getStaticList($fields,$where,$order,$group,$start,$size){
	    $list = $this->alias('a')
            	     ->join('savor_box box on a.box_mac=box.mac','left')
            	     ->join('savor_room room on box.room_id=room.id','left')
            	     ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            	     ->join('savor_area_info area on hotel.area_id= area.id','left')
            	     ->field($fields)
            	     ->where($where)
	                 ->group($group)
	                 ->limit($start,$size)
	                 ->select();
	    $ret = $this->alias('a')
	                ->field('hotel.id')
            	     ->join('savor_box box on a.box_mac=box.mac','left')
            	     ->join('savor_room room on box.room_id=room.id','left')
            	     ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            	     ->join('savor_area_info area on hotel.area_id= area.id','left')
            	     ->where($where)
	                 ->group($group)
	                 ->select();
	    $count = count($ret);
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
}