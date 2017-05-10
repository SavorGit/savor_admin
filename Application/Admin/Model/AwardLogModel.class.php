<?php
/**
 *@author zhang.yingtao
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class AwardLogModel extends BaseModel
{
	protected $tableName='award_log';
	
	public function getList($where,$orders,$start,$size){
	    $sql ="select a.* ,b.name as prizename,c.name as boxname,d.name as roomname,e.name as hotelname
	           from savor_award_log as a 
	           left join savor_award_config as b on a.prizeid=b.id 
	           left join savor_box c on a.mac=c.mac
	           left join savor_room d on d.id=c.room_id
	           left join savor_hotel e on e.id=d.hotel_id where 1 "
	           .$where.' order by '.$orders.' limit '.$start.','.$size;
	    $list = $this->query($sql);
	    
	    $sql = "select count(a.id) as count from savor_award_log as a where 1 ".$where;
	    $ret = $this->query($sql);
	    $count = $ret[0]['count'];
	    $objPage = new Page($count,$size);
	  
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
        return $data;
	}
}
