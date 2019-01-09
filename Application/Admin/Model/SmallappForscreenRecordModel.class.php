<?php
/**
 * @desc   小程序投屏日志 
 * @author zhang.yingtao
 *
 */
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;
class SmallappForscreenRecordModel extends Model
{
	protected $tableName='smallapp_forscreen_record';
	
	public function getList($fields="a.id",$where, $order='a.id desc', $start=0,$size=5){
	    $list = $this->alias('a')
	                 ->join('savor_box box on a.box_mac=box.mac','left')
	                 ->join('savor_room room on room.id= box.room_id','left')
	                 ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	                 ->join('savor_area_info area on hotel.area_id=area.id','left')
	                 ->join('savor_smallapp_user user on a.openid=user.openid','left')
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
	                  ->join('savor_smallapp_user user on a.openid=user.openid','left')
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
    public function updateData($condition,$data){
        $result = $this->where($condition)->save($data);
        return $result;
    }

}