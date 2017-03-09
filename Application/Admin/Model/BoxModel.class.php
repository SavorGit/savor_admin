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

class BoxModel extends BaseModel{

	public function getExNum(){
		$tvModel = new \Admin\Model\TvModel();
		$t_arr = $tvModel->field('id')->select();
		$t_str = '';
		foreach ($t_arr as $t=>$v) {
			$t_str .= $v['id'].',';
		}
		$t_str = substr($t_str,0,-1);
		$Model = new \Think\Model();
	 $sql = 'select hotel.install_date, hotel.state hsta, room.state rsta, box.mac mac, room.name rname, room.type rtype, tv.tv_brand tbrd, tv.tv_size tsiz, tv.tv_source, hotel.name hname, hotel.level, hotel.area_id, hotel.addr, hotel.contractor, hotel.mobile, hotel.tel, hotel.iskey, hotel.maintainer, hotel.tech_maintainer from savor_tv as tv left join savor_box as box on tv.box_id = box.id left join savor_room as room on box.room_id = room.id left join savor_hotel as hotel on room.hotel_id = hotel.id where tv.id in ('.$t_str.')';
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


}
