<?php
/**
 *机顶盒model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;
use Admin\Model\RoomModel;
use Common\Lib\SavorRedis;

class BoxModel extends BaseModel{
    protected $tableName  ='box';


	public function getBoxTvInfo($field,$where,$start,$size){
		//savor_tv
		$sql ="select $field from savor_box AS b LEFT JOIN
				savor_tv AS tv ON b.id=tv.box_id
	           left join savor_room as r on r.id=b.room_id
	           left join savor_hotel as h on h.id=r.hotel_id
	           where ".$where.' limit '.$start.','.$size;

		$countsql ="select count('id') as num from savor_box
 				AS b LEFT JOIN 	savor_tv AS tv ON b.id=tv.box_id
	           left join savor_room as r on r.id=b.room_id
	           left join savor_hotel as h on h.id=r.hotel_id
	           where ".$where;


		$result = $this->query($sql);
		$counts = $this->query($countsql);
		$count = $counts[0]['num'];
		$objPage = new Page($count,$size);
		$show = $objPage->admin_page();
		$data = array('list'=>$result,'page'=>$show);
		return $data;
	}


	public function getBoxExNumNew(){
		$Model = new \Think\Model();
		$sql = 'select hotel.id,hotel.install_date, hotel.state hsta, room.state rsta,tv.state tsta,box.state boxstate,hotel.hotel_box_type,
	         box.mac mac,box.name bname, room.name rname, room.type rtype, tv.tv_brand tbrd, tv.tv_size tsiz,
	         tv.tv_source, hotel.name hname, hotel.level, hotel.area_id, hotel.addr, hotel.contractor,
	         hotel.mobile, hotel.tel, hotel.iskey, sys.remark as maintainer, hotel.tech_maintainer
	         from savor_tv as tv
	         left join savor_box as box on tv.box_id = box.id
	         left join savor_room as room on box.room_id = room.id
	         left join savor_hotel as hotel on room.hotel_id = hotel.id
	         left join savor_hotel_ext as hext on hext.hotel_id = hotel.id
	         left join savor_sysuser as sys on sys.id = hext.maintainer_id
	         where tv.flag=0 AND tv.state != 3 and hotel.flag=0 and hotel.state=1 order by hotel.id';
		$volist = $Model->query($sql);

		$res = $this->changeInfoName($volist);
		return $res;
	}
    public function getBoxList(){
        $Model = new \Think\Model();
        $sql = 'select hotel.id,hotel.install_date, hotel.state hsta, room.state rsta,box.state boxstate,hotel.hotel_box_type,
	         box.mac mac,box.name bname, room.name rname, room.type rtype, 
	          hotel.name hname, hotel.level, hotel.area_id, hotel.addr, hotel.contractor,
	         hotel.mobile, hotel.tel, hotel.iskey, sys.remark as maintainer, hotel.tech_maintainer,
             room.remark,box.tag
	         from savor_box as box 
	         left join savor_room as room on box.room_id = room.id
	         left join savor_hotel as hotel on room.hotel_id = hotel.id
	         left join savor_hotel_ext as hext on hext.hotel_id = hotel.id
	         left join savor_sysuser as sys on sys.id = hext.maintainer_id
	         where 1 and hotel.flag=0 and hotel.state=1 and box.flag=0 and box.state!=3 order by hotel.id';
        $volist = $Model->query($sql);
        
        $res = $this->changeInfoName($volist);
        return $res;
    }


	public function getBoxExNum(){
		$Model = new \Think\Model();
		$sql = 'select hotel.id,hotel.install_date, hotel.state hsta, room.state rsta,tv.state tsta,box.state boxstate,
	         box.mac mac,box.name bname, room.name rname, room.type rtype, tv.tv_brand tbrd, tv.tv_size tsiz,
	         tv.tv_source, hotel.name hname, hotel.level, hotel.area_id, hotel.addr, hotel.contractor,
	         hotel.mobile, hotel.tel, hotel.iskey, hotel.maintainer, hotel.tech_maintainer
	         from savor_tv as tv
	         left join savor_box as box on tv.box_id = box.id
	         left join savor_room as room on box.room_id = room.id
	         left join savor_hotel as hotel on room.hotel_id = hotel.id
	         where tv.flag=0 AND tv.state != 3 and hotel.flag=0 and hotel.state=1 order by hotel.id';
		$volist = $Model->query($sql);

		$res = $this->changeInfoName($volist);
		return $res;
	}

	public function changeInfoName($data){
		//酒楼状态，版位状态即包间状态，
		$h_arr = C('HOTEL_STATE');
		//包间类型
		$r_arr = array(
			1=>'包间',
			2=>'大厅',
			3=>'等候区',
		);
		//电视信号源
		$tv_arr = array(
			1=>'ant',
			2=>'av',
			3=>'hdmi',
			4=>null,
		);
		//酒楼级别
		$level_arr = C('HOTEL_LEVEL');
		//酒楼区域
		$areaModel = new \Admin\Model\AreaModel();

		$area_info = $areaModel->field('id,region_name')->select();
		//重点酒楼
		$ho_key = C('HOTEL_KEY');


		foreach ($data as &$value){
			foreach ($h_arr as  $k=>$v){
				if($value['hsta'] == $k){
					$value['hsta'] = $v;
				}
				if($value['rsta'] == $k){
					$value['rsta'] = $v;
				}
				if($value['tsta'] == $k){
					$value['tsta'] = $v;
				}
				if($value['boxstate'] == $k){
				    $value['boxstate'] = $v;
				}
			}
			foreach ($r_arr as  $k=>$v){
				if($value['rtype'] == $k){
					$value['rtype'] = $v;
				}
			}
			foreach ($tv_arr as  $k=>$v){
				if($value['tv_source'] == $k){
					$value['tv_source'] = $v;
				}
			}
			foreach ($level_arr as  $k=>$v){
				if($value['level'] == $k){
					$value['level'] = $v;
				}
			}
			foreach ($area_info as  $v){
				if($value['area_id'] == $v['id']){
					$value['area_id'] = $v['region_name'];
				}
			}
			foreach ($ho_key as  $k=>$v){
				if($value['iskey'] == $k){
					$value['iskey'] = $v;
				}
			}

		}
		return $data;
	}

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
	 * 包间ID转换为包间名称
	 * @param  array  $result [description]
	 * @return [type]         [description]
	 */
	public function roomIdToRoomName($result=[]){
		if(!$result || !is_array($result)){
			return [];
		}
		$arrHotelId = [];

		foreach ($result as $value){
			$arrHotelId[] = $value['room_id'];
		}

		$filter       = [];
		$filter['id'] = ['IN',$arrHotelId];
		$roomModel = new RoomModel;
		$arrHotel = $roomModel->getAll('id,name',$filter,0,100);
		foreach ($result as &$value){
			foreach ($arrHotel as  $row){
				if($value['room_id'] == $row['id']){
					$value['room_name'] = $row['name'];
				}
			}
		}
		return $result;
	}
    /**
     * @desc 添加机顶盒信息
     */
	public function addData($data){
	    if(!empty($data)){
	        $this->add($data);
	        $insert_id = $this->getLastInsID();
	        if($insert_id){
	            $redis = SavorRedis::getInstance();
	            $redis->select(15);
	            $cache_key =  C('DB_PREFIX').$this->tableName.'_'.$insert_id;
	            $redis->set($cache_key, json_encode($data));
	            return $insert_id;
	        }else {
	            return false;
	        }
	    }else {
	        return false;
	    }
	}
    /**
     * @desc 编辑机顶盒信息
     */
	public function editData($id,$data){
	    if(!empty($id)){
	        $rt = $this->where('id='.$id)->save($data);
	        if($rt){
	            $redis = SavorRedis::getInstance();
	            $redis->select(15);
	            $cache_key =  C('DB_PREFIX').$this->tableName.'_'.$id;
	            $redis->set($cache_key,json_encode($data));
	            return true;
	        } else {
	            return false;
	        }
	    }else {
	        return false;
	    }
	}
	public function isHaveMac($field,$where){
	    $sql ="select $field from savor_box as b 
	           left join savor_room as r on b.room_id=r.id
	           left join savor_hotel as h on r.hotel_id=h.id
	           where ".$where;
	    $result = $this->query($sql);
	    return $result;
	}

	public function isHaveTv($field,$where){
		//savor_tv
		$sql ="select $field from savor_box as b
	           left join savor_room as r on b.room_id=r.id
	           left join savor_hotel as h on r.hotel_id=h.id
	           left join  savor_tv as tv on tv.box_id = b.id
	           where ".$where;
		$result = $this->query($sql);
		return $result;
	}

	public function saveBatdat($data, $id) {
		$redis  =  \Common\Lib\SavorRedis::getInstance();
		$redis->select(15);
		$cache_key = C('DB_PREFIX').$this->tableName.'_'.$id;
		$redis->set($cache_key, json_encode($data));
	}
	public function getUsedBoxByMac($mac){
	    $where['mac'] = $mac;
	    $where['state'] = 1;
	    return $this->where($where)->find();
	}
	public function getInfo($field ='*',$where,$order,$limit){
	    $result = $this->field($field)->where($where)->order($order)->limit($limit)->select();
	    return $result;
	}
	public function getHotelInfoByBoxMac($mac){
	    if($mac){
	        $sql ="select b.id as box_id,b.name as box_name,b.room_id,r.name as room_name, h.id as hotel_id,
                   h.name as hotel_name,a.id as area_id, a.region_name as area_name
                   from savor_box as b
                   left join savor_room as r on b.room_id=r.id
                   left join savor_hotel as h on r.hotel_id=h.id
                   left join savor_area_info as a on h.area_id=a.id
                   where b.flag=0 and  b.mac='".$mac."' limit 1";
	        $result = $this->query($sql);
	        if($result){
	            return $result[0];
	        }else {
	            return false;
	        }
	    }
	}
	public function getListInfo($fields ,$where, $order,$limit){
	    $data = $this->alias('a')
	    ->join('savor_room as room on a.room_id = room.id ')
	    ->field($fields)
	    ->where($where)
	    ->order($order)
	    ->limit($limit)
	    ->select();
	    return $data;
	
	}

	public function getInfoByHotelid($hotelid , $field,$where){
		$sql = 'select '.$field;
		$sql  .= ' FROM  savor_box box  LEFT JOIN savor_room room ON  box.room_id = room.id  WHERE room.hotel_id=' . $hotelid.$where;

		$result = $this->query($sql);
		return $result;
	}
    public function countNums($where){
        $nums = $this->alias('box')
             ->join('savor_room room on box.room_id=room.id ','left')
             ->join('savor_hotel hotel on room.hotel_id= hotel.id','left')
             ->where($where)
             ->count();
        return $nums;
    }

    public function getInfoByCondition($fields='box.*',$where){
        $res = $this->alias('box')
            ->join('savor_room room on room.id= box.room_id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->join('savor_hotel_ext hotelext on hotel.id=hotelext.hotel_id','left')
            ->field($fields)
            ->where($where)
            ->select();
        $data = !empty($res)?$res[0]:array();
        return $data;
    }

    public function getBoxByCondition($fields='box.*',$where,$group=''){
        $res = $this->alias('box')
            ->join('savor_room room on room.id= box.room_id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->field($fields)
            ->where($where)
            ->group($group)
            ->select();
        return $res;
    }

}
