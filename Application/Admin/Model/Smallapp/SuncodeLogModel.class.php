<?php
/**
 * @desc   电视显示小程序码日志
 * @author zhang.yingtao
 * @since  2018-09-10
 */
namespace Admin\Model\Smallapp;
use Think\Model;
use Common\Lib\Page;
class SuncodeLogModel extends Model
{
	protected $tableName='smallapp_suncode_log';
	
	public function getList($fields="a.id",$where, $order='a.id desc', $start=0,$size=5){
	    $list = $this->alias('a')
	                 ->join('savor_box box on a.box_mac=box.mac','left')
	                 ->join('savor_room room on room.id= box.room_id','left')
	                 ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	                 ->join('savor_area_info area on hotel.area_id=area.id','left')
	                 ->join('savor_media media on a.media_id=media.id')
	                 ->field($fields)
            	     ->where($where)
            	     ->order($order)
            	     ->limit($start,$size)
            	     ->select();
	    
	    $count = $this->alias('a')
	                  ->join('savor_box box on a.box_mac=box.mac','left')
	                  ->join('savor_room room on room.id= box.room_id','left')
	                  ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	                  ->join('savor_area_info area on hotel.area_id=area.id','left')
	                  ->where($where)->count();
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
	public function getWhere($fields,$where,$limit,$group){
	    $data = $this->alias('a')
	                 ->join('savor_box box on a.box_mac=box.mac','left')
	                 ->join('savor_room room on box.room_id=room.id','left')
	                 ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	                 ->field($fields)
	                 ->where($where)->limit($limit)->select();
        return $data;	    
	}
	public function delWhere($where,$order,$limit){
	    $ret =  $this->where($where)->order($order)->limit($limit)->delete();
	    return $ret;
	}
	public function getOne($fields,$where){
	    $data = $this->field($fields)->where($where)->find();
	    return $data;
	}
	public function updateInfo($where,$data){
	    $ret = $this->where($where)->save($data);
	    return $ret;
	}
    public function addInfo($data,$type=1){
	    if($type==1){
	        $ret = $this->add($data);
	        
	    }else {
	        $ret = $this->addAll($data);
	    }
	    return $ret;
	}
	public function countNums($where){
	    $nums = $this->where($where)->count();
	    return $nums;
	}
}