<?php
/**
 *@author hongwei
 * 
 * 
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class RoomModel extends BaseModel
{
	protected $tableName='room';

    public function getRoomByCondition($fields='room.*',$where,$group=''){
        $res = $this->alias('room')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->field($fields)
            ->where($where)
            ->group($group)
            ->select();
        return $res;
    }

	public function getRoomBox($field ='*',$where){
		$result = $this->alias('rom')
					   ->field($field)
			           ->join('LEFT JOIN savor_box sbox ON rom.id = sbox.room_id')
			           ->where($where)
			           ->select();
		return $result;
	}


	public function getList($where, $order='id desc', $start=0,$size=5)
	{	
		
		 $list = $this->where($where)
					  ->order($order)
					  ->limit($start,$size)
					  ->select();

		
		$count = $this->where($where)
					  ->count();

		$objPage = new Page($count,$size);		  

		$show = $objPage->admin_page();

		$data = array('list'=>$list,'page'=>$show);

        return $data;
        
    }//End Function

	public function saveBatdat($data, $id) {
		$redis  =  \Common\Lib\SavorRedis::getInstance();
		$redis->select(15);
		$cache_key = C('DB_PREFIX').$this->tableName.'_'.$id;
		$redis->set($cache_key, json_encode($data));
	}


	public function saveData($data, $id = 0) {
		$redis  =  \Common\Lib\SavorRedis::getInstance();
		$redis->select(15);
		if($id){
			$bool = $this->where('id='.$id)->save($data);
			$res = $this->find($id);
			$data['create_time'] = $res['create_time'];
			$cache_key = C('DB_PREFIX').$this->tableName.'_'.$id;
			$redis->set($cache_key, json_encode($data));
			$redis->select(12);
			$cache_key = C('SMALL_ROOM_LIST').$data['hotel_id'];
			$redis->remove($cache_key);
		}else{
			$data['create_time'] = date('Y-m-d H:i:s');
			$bool = $this->add($data);
			$insert_id = $this->getLastInsID();
			$cache_key = C('DB_PREFIX').$this->tableName.'_'.$insert_id;
			$redis->set($cache_key, json_encode($data));
			$redis->select(12);
			$cache_key = C('SMALL_ROOM_LIST').$data['hotel_id'];
			$redis->remove($cache_key);
		}
		return $bool;
	}
	public function getInfo($field ='*',$where,$order,$limit){
	    $result = $this->field($field)->where($where)->order($order)->limit($limit)->select();
	    return $result;
	}



}//End Class