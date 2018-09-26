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
	public function getInfos($fields,$where,$limit,$group){
	    $data = $this->alias('a') 
	                 ->join('savor_smallapp_turntable_log b on a.activity_id=b.activity_id','left')
            	     ->join('savor_box box on b.box_mac=box.mac','left')
            	     ->join('savor_room room on box.room_id=room.id','left')
            	     ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            	     ->field($fields)
            	     ->where($where)->limit($limit)->select();
	    return $data;
	}
}