<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class HotelModel extends BaseModel{
	protected $tableName = 'hotel';
	public function getList($where, $order='id desc', $start=0,$size=5){	
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
	}



	/**
	 * 酒店ID转换为酒店名称
	 * @param  array  $result [description]
	 * @return [type]         [description]
	 */
	public function hotelIdToName($result=[]){
		if(!$result || !is_array($result)){
			return [];
		}
		$arrHotelId = [];
		foreach ($result as $value){
			$arrHotelId[] = $value['hotel_id'];
		}
		$filter       = [];
		$filter['id'] = ['IN',$arrHotelId];
		$arrHotel = $this->getAll('id,name',$filter);
		foreach ($result as &$value){
			foreach ($arrHotel as  $row){
				if($value['hotel_id'] == $row['id']){
					$value['hotel_name'] = $row['name'];
				}
			}
		}
		return $result;
	}
	
	public function getHotelByIds($hotel_ids,$field="*"){
	    $hotel_ids = trim($hotel_ids,',');
	    $filter = array();
	    $filter['id'] = array('IN',$hotel_ids);
	    $res = $this->field($field)
	    ->where($filter)
	    ->select();
	    return $res;
	}
	
	public function getStatisticalNumByHotelId($hotel_id,$type=''){
	    $sql = "select id as room_id,hotel_id from savor_room where hotel_id='$hotel_id'";
	    $res = $this->query($sql);
	    $room_num = $box_num = $tv_num = 0;
	    $all_rooms = array();
	    foreach ($res as $k=>$v){
	        $room_num++;
	        $all_rooms[] = $v['room_id'];
	    }
	    if($type == 'room'){
	        $nums = array('room_num'=>$room_num,'room'=>$all_rooms);
	        return $nums;
	    }
	    if($room_num){
	        $rooms_str = join(',', $all_rooms);
	        $sql = "select id as box_id,room_id from savor_box where room_id in ($rooms_str)";
	        $res = $this->query($sql);
	        $all_box = array();
	        foreach ($res as $k=>$v){
	            $box_num++;
	            $all_box[] = $v['box_id'];
	        }
	        if($type == 'box'){
	            $nums = array('box_num'=>$box_num,'box'=>$all_box);
	            return $nums;
	        }
	        if($box_num){
	            $box_str = join(',', $all_box);
	            $sql = "select count(id) as tv_num from savor_tv where box_id in ($box_str)";
	            $res = $this->query($sql);
                $tv_num = $res[0]['tv_num'];
                if($type == 'tv'){
                    $nums = array('tv_num'=>$tv_num);
                    return $nums;
                }
	        }
	    }
	    $nums = array('room_num'=>$room_num,'box_num'=>$box_num,'tv_num'=>$tv_num);
	    return $nums;
	}
	
	public function getMacaddrByHotelId($hotel_id){
	    $sql = "select * from savor_hotel_ext where hotel_id='$hotel_id' limit 1";
	    $result = $this->query($sql);
	    $data = !empty($result)?$result[0]:array();
	    return $data;
	}
    public function getInfo($field ='*',$where,$order,$limit){
        $result = $this->field($field)->where($where)->order($order)->limit($limit)->select();
        return $result;
    }

	public function saveData($data, $where) {
		$bool = $this->where($where)->save($data);
		return $bool;
	}

	public function addData($data) {
		$result = $this->add($data);
		return $result;
	}


	public function getOne($id){
		if ($id) {
			$res = $this->find($id);
			return $res;
		}

	}

	public function saveStRedis($data, $id){
		$redis  =  \Common\Lib\SavorRedis::getInstance();
		$redis->select(15);
		$cache_key = C('DB_PREFIX').$this->tableName.'_'.$id;
		$redis->set($cache_key, json_encode($data));
	}

}
