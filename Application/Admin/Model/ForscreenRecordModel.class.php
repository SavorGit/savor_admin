<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

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
}