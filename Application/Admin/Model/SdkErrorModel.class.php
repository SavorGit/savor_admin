<?php
/**
 *@author zhang.yingao
 *@since  20180510
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class SdkErrorModel extends BaseModel
{
	protected $tableName='sdk_error';
	
	public function getList($fields,$where,$order,$start,$size){
	    $list = $this->alias('a')
	                 ->join('savor_box box on box.id=a.box_id','left')
	                 ->join('savor_room room on box.room_id=room.id','left')
	                 ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	                 ->field($fields)
	                 ->where($where)
	                 ->order($order)
	                 ->limit($start,$size)
	                 ->select();
	    $count = $this->alias('a')
            	      ->join('savor_box box on box.id=a.box_id','left')
            	      ->join('savor_room room on box.room_id=room.id','left')
            	      ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	                  ->where($where)
	                  ->count();
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
	public function delData($where){
	    $ret = $this->where($where)->delete();
	    return $ret;
	}
}