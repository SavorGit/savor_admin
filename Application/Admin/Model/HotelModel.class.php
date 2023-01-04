<?php
namespace Admin\Model;
use Common\Lib\Page;

class HotelModel extends BaseModel{
	protected $tableName = 'hotel';

	public function gethotellogoInfo($hotelid){
		$sql = "SELECT
        media.id AS id,
        media.name as logoname,
        media.md5 AS logo_md5,
        media.oss_addr as lourl
        FROM savor_hotel hotel
        LEFT JOIN savor_media media on media.id=hotel.media_id
        where
            hotel.id={$hotelid}";

		$result = $this->query($sql);
		return $result;
	}

	public function getListMac($field, $where, $order='id desc'){
		$list = $this->alias('a')
			->field($field)
			->where($where)
			->join('left join savor_hotel_ext b on a.id=b.hotel_id')
			->order($order)
			->select();
		return $list;
	}


	public function getList($where, $order='id desc', $start=0,$size=5,$fields="*"){	
		 $list = $this->field($fields)
		              ->where($where)
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

	public function getListExt($where, $order='id desc', $start=0,$size=5,$fields){
		$list = $this->alias('a')
		        ->field($fields)
		        ->where($where)
    			->join('savor_hotel_ext ext on ext.hotel_id=a.id','left')
    			->join('savor_area_info area on a.area_id=area.id','left')
    			->order($order)
    			->limit($start,$size)
    			->select();
		$count = $this->alias('a')
		              ->join('savor_hotel_ext ext on ext.hotel_id=a.id')
                      ->join('savor_area_info area on a.area_id=area.id','left')
		              ->where($where)
			          ->count();
		$objPage = new Page($count,$size);
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
		return $data;
	}

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

	public function getStatisticalNumByHotelIdNew($hotel_id,$type=''){
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
	        $sql = "select id as box_id,room_id from savor_box where room_id in ($rooms_str) and state!=3 and flag=0";
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

	public function getHotelCount($where){
	    return $this->where($where)->count();
	}

	public function getHotelCountNums($where){
	    $count =$this->alias('a')
	    ->join('savor_hotel_ext b on a.id=b.hotel_id','left')
	    ->where($where)
	    ->count();
	    return $count;
	}

	public function getWhereData($where, $field='') {
		$result = $this->where($where)->field($field)->select();
		return  $result;
	}

	public function getWhereorderData($where, $field='', $order='') {
		$result = $this->where($where)->field($field)->order($order)->select();
		return  $result;
	}

	public function getHotelidByArea($where, $field='', $order='') {
		$result = $this->alias('sht')
			->where($where)
			->field($field)
			->order($order)
			->join('savor_area_info sari on sari.id = sht.area_id')
			->select();
		return  $result;
	}

	public function getHotelInfo($field,$where){
        $result =$this->alias('a')
            ->join('savor_hotel_ext ext on a.id=ext.hotel_id','left')
            ->join('savor_sysuser sysuser on ext.maintainer_id=sysuser.id','left')
            ->join('savor_area_info area on a.area_id=area.id','left')
            ->field($field)
            ->where($where)
            ->find();
        return $result;
    }

	public function getBoxOrderMacByHid($field, $where, $order){
		$list = $this->alias('sht')
			->join('savor_room room on sht.id = room.hotel_id')
			->join('savor_box box on room.id = box.room_id')
			->order($order)
			->field($field)
			->where($where)
			->select();
		return $list;
	}

	public function getBoxMacByHid($field, $where){
		$list = $this->alias('sht')
			->join('savor_room room on sht.id = room.hotel_id')
			->join('savor_box box on room.id = box.room_id')
			->join(' join savor_area_info sari on sari.id = sht.area_id')
			->join('savor_tv tv on tv.box_id = box.id')
			->field($field)
			->where($where)
			->select();
		return $list;
	}

	public function getHotelInfoByMac($mac){
	    $sql ="select he.tag, he.mac_addr,h.name as hotel_name,a.id as area_id,a.region_name as area_name,h.flag,h.state
               from savor_hotel as h
               left join savor_hotel_ext as he on h.id=he.hotel_id
               left join savor_area_info as a on h.area_id =a.id where h.flag=0 and h.state=1 and he.mac_addr='".$mac."'";
	    $result =  $this->query($sql);
	    if($result){
	        return $result[0];
	    }else {
	        return false;
	    }
	}

	public function getHotelList($where,$order,$limit,$fields = '*'){
	    $data = $this->field($fields)->where($where)->order($order)->limit($limit)->select();
	    return $data;
	}

	public function getHotelLists($where,$order,$limit,$fields = '*'){
	    $data = $this->alias('a')
	    ->join('savor_hotel_ext b on a.id=b.hotel_id')
	    ->field($fields)->where($where)->order($order)->limit($limit)->select();
	    return $data;
	}

    public function getHotels($field,$where){
        $result =$this->alias('a')
            ->join('savor_hotel_ext ext on a.id=ext.hotel_id','left')
            ->join('savor_area_info area on a.area_id=area.id','left')
            ->field($field)
            ->where($where)
            ->select();
        return $result;
    }

    public function cleanWanHotelCache($hotel_ids){
	    $where = array('is_5g'=>1);
	    if(!empty($hotel_ids)){
	        $where['id'] = array('in',$hotel_ids);
        }
        $res_hotels = $this->getDataList('id,is_5g',$where,'id desc');
        if(!empty($res_hotels)){
            $redis = new \Common\Lib\SavorRedis();
            $redis->select(21);
            $download_key = C('BOX_LANHOTEL_DOWNLOAD');
            $fail_key = C('BOX_LANHOTEL_DOWNLOAD_FAIL');
            $queue_key = C('BOX_LANHOTEL_DOWNLOADQUEUE');
            foreach ($res_hotels as $v){
                $redis->remove($download_key.$v['id']);
                $redis->remove($fail_key.$v['id']);

                $keys_arr = $redis->keys($queue_key.$v['id'].":*");
                foreach($keys_arr as $vv){
                    $redis->remove($vv);
                }
            }
        }
        return true;
    }

    public function handle_timeout_download(){
        $where = array('is_5g'=>1);
        $res_hotels = $this->getDataList('id',$where,'id desc');
        if(!empty($res_hotels)){
            $redis = new \Common\Lib\SavorRedis();
            $redis->select(21);
            $download_key = C('BOX_LANHOTEL_DOWNLOAD');
            $fail_key = C('BOX_LANHOTEL_DOWNLOAD_FAIL');
            $queue_key = C('BOX_LANHOTEL_DOWNLOADQUEUE');
            foreach ($res_hotels as $v){
                $hotel_id  = $v['id'];
                $res_download = $redis->get($download_key.$hotel_id);
                if(!empty($res_download)){
                    $res_download = json_decode($res_download,true);
                    $is_change = 0;
                    foreach ($res_download as $dk=>$dv){
                        if($dv['status']==1){
                            $now_time = time();
                            $start_time = strtotime($dv['start_time']);
                            if($now_time-$start_time>600){
                                $box_mac = $dk;

                                if(!empty($dv['from_box'])){
                                    $lan_box = $dv['from_box'];
                                    $download_queuecache_key = $queue_key."$hotel_id:$lan_box";
                                    $redis->lrem($download_queuecache_key,$box_mac,0);
                                }
                                $res_fail = $redis->get($fail_key.$hotel_id);
                                if(!empty($res_fail)){
                                    $fail_info = json_decode($res_fail,true);
                                }else{
                                    $fail_info = array();
                                }
                                $dv['status'] = 2;
                                $fail_info[$box_mac] = $dv;
                                $redis->set($fail_key.$hotel_id,json_encode($fail_info),86400*14);
                                unset($res_download[$dk]);
                                $is_change = 1;
                                $now_datetime = date('Y-m-d H:i:s');
                                echo "$now_datetime hotel_id:$hotel_id,box_mac:$box_mac,time:{$dv['start_time']} timeout \r\n";
                            }
                        }
                    }
                    if($is_change){
                        $redis->set($download_key.$hotel_id,json_encode($res_download),86400*14);
                    }
                }
            }
        }
        return true;
    }

    public function getHotelDatas($field,$where,$order){
        $res = $this->alias('hotel')
            ->field($field)
            ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
            ->join('savor_area_info area on area.id=hotel.area_id','left')
            ->where($where)
            ->order($order)
            ->select();
        return $res;
    }
}
