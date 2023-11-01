<?php
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class HotelExtModel extends BaseModel{
	protected $tableName='hotel_ext';


	public function saveData($data, $where) {
		$bool = $this->where($where)->save($data);
		return $bool;
	}

	public function addData($data) {
		$result = $this->add($data);
		return $result;
	}

    public function getHotelMaintainer($field,$where){
        $list = $this->alias('a')
            ->field($field)
            ->where($where)
            ->join('left join savor_sysuser user on a.maintainer_id=user.id')
            ->select();
        return $list;
    }

	public function saveStRedis($data, $id){
		$redis  =  \Common\Lib\SavorRedis::getInstance();
		$redis->select(15);
		$cache_key = C('DB_PREFIX').$this->tableName.'_'.$id;
		$redis->set($cache_key, json_encode($data));
		
		$redis->select(12);
		$cache_key = C('SMALL_HOTEL_INFO').$id;
		$redis->remove($cache_key);
		
		$hotelModel = new \Admin\Model\HotelModel();
		$v_hotel_result = $hotelModel->getListMac('a.id hotel_id',array('b.mac_addr'=>'000000000000'),'a.id asc');
		$redis->select(10);
		$cache_key = C('VSMALL_HOTELLIST');
		$redis->set($cache_key, json_encode($v_hotel_result));	
	}


	public function getData($field, $where){
		$list = $this->field($field)->where($where)->select();
		return $list;
	}

	public function getOneData($field, $where){
		$list = $this->field($field)->where($where)->find();
		return $list;
	}

	public function isHaveMac($field,$where){
	    $sql ="select $field from savor_hotel_ext as he 
	           left join savor_hotel as h on he.hotel_id = h.id where ".$where;
	    $result = $this->query($sql);
	    return $result;
	}

    public function getSellwineList($fields,$where,$orderby,$start=0,$size=0){
        if($start>=0 && $size>0){
            $list = $this->alias('a')
                ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                ->join('savor_area_info area on area.id=hotel.area_id','left')
                ->join('savor_sysuser su on a.maintainer_id=su.id','left')
                ->join('savor_sysuser susigner on a.signer_id=susigner.id','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->limit($start,$size)
                ->select();
            $count = $this->alias('a')
                ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                ->join('savor_area_info area on area.id=hotel.area_id','left')
                ->join('savor_sysuser su on a.maintainer_id=su.id','left')
                ->join('savor_sysuser susigner on a.signer_id=susigner.id','left')
                ->where($where)
                ->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->alias('a')
                ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                ->join('savor_area_info area on area.id=hotel.area_id','left')
                ->join('savor_sysuser su on a.maintainer_id=su.id','left')
                ->join('savor_sysuser susigner on a.signer_id=susigner.id','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->select();
        }
        return $data;
    }

}
