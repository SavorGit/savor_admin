<?php
/**
 *@author zhang.yingtao
 *@since  2018-12-21
 *
 */
namespace Admin\Model\Smallapp;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class GameInteractModel extends BaseModel
{
	protected $tableName='smallapp_game_interact';
	
	public function getList($fields,$where,$order,$start,$size){
	   $list = $this->alias('a')
	                ->join('savor_box box on a.box_mac=box.mac','left')
	                ->join('savor_room room on box.room_id=room.id','left')
	                ->join('savor_hotel h on room.hotel_id=h.id','left')
	                ->join('savor_area_info area on h.area_id=area.id','left')
	                ->field($fields)
	                ->where($where)
	                ->limit($start,$size)
	                ->order($order)
	                ->select();
	   $count = count($list);
	   $objPage = new Page($count,$size);
	   $show = $objPage->admin_page();
	   $data = array('list'=>$list,'page'=>$show);
	   return $data;
	}
	
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
	
	public function getOne($fields,$where){
	    $data = $this->field($fields)->where($where)->find();
	    return $data;
	}
}