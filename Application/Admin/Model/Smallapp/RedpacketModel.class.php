<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model\Smallapp;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class RedpacketModel extends BaseModel
{
	protected $tableName='smallapp_redpacket';
	
	public function getList($fields="a.id",$where, $order='a.id desc', $start=0,$size=5){
	    $list = $this->alias('a')
	    ->join('savor_box box on a.mac=box.mac','left')
	    ->join('savor_room room on room.id= box.room_id','left')
	    ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	    ->join('savor_area_info area on hotel.area_id=area.id','left')
	    ->join('savor_smallapp_user user on a.user_id =user.id','left')
	    ->field($fields)
	    ->where($where)
	    ->order($order)
	    ->limit($start,$size)
	    ->select();
	    
	    $count = $this->alias('a')
            	      ->join('savor_box box on a.mac=box.mac','left')
            	      ->join('savor_room room on room.id= box.room_id','left')
            	      ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            	      ->join('savor_area_info area on hotel.area_id=area.id','left')
            	      ->join('savor_smallapp_user user on a.user_id =user.id','left')
            	      ->where($where)->count();
	    
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
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
}