<?php
/**
 * @desc 百度聚屏广告播放记录
 * @author zhang.yingtao
 * @since 2018-07-25
 * 
 */
namespace Admin\Model;

use Think\Model;
use Common\Lib\Page;
class BaiduPolyPlayRecordModel extends Model
{
	protected $tableName='baidu_poly_play_record';
    
	public function getList($fields="a.id",$where, $order='a.id desc', $start=0,$size=5){
	    $list = $this->alias('a')
	                 ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
	                 ->join('savor_room room on a.room_id = room.id','left')
	                 ->join('savor_media media on a.media_id=media.id','left')
            	     ->field($fields)
            	     ->where($where)
            	     ->order($order)
            	     ->limit($start,$size)
            	     ->select();
	    $count = $this->alias('a')->where($where)->count();
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
	
	
	public function addInfo($data,$type = 1){
	    if($type ==1){
	        $ret = $this->add($data);
	    }else {
	        $ret = $this->addAll($data);
	    }
	    return $ret;
	}
	public function editInfo($where,$data){
	    $ret = $this->where($where)->save($data);
	    return $ret;
	}
	public function addCount($where,$column){
	    $ret = $this->where($where)->setInc($column);
	    return $ret;
	}
	public function countRows($feilds,$where){
        $nums = $this->field($feilds)->where($where)->count();
        return $nums;	    
	}
	public function modifyInfo($sql_d,$where){
	    $sql ="update savor_baidu_poly_play_record set ".$sql_d." where ".$where;
	    return $this->execute($sql);
	}
	public function countPlayNums(){
	    $sql = "select sum(play_times) as count from savor_baidu_poly_play_record";
	    $ret = $this->query($sql);
	    return $ret[0]['count'];
	}
}