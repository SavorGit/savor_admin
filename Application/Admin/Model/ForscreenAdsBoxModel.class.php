<?php
namespace Admin\Model;
use Common\Lib\Page;

class ForscreenAdsBoxModel extends BaseModel{
	protected $tableName='forscreen_ads_box';


    public function getWhere($where, $field){
        $list = $this->where($where)->field($field)->select();
        return $list;
    }

    public function getDataCount($where){
        $count = $this->where($where)->count();
        return $count;
    }



	public function getBoxInfoBySize($field, $where,$order='id desc',$group, $start, $size) {
		$list = $this->alias('adbox')
			->field($field)
			->where($where)
			->join('savor_box box on box.id = adbox.box_id', 'left')
			->join('savor_room room on room.id = box.room_id', 'left')
			->join('savor_hotel sht on sht.id = room.hotel_id', 'left')
			->group($group)
			->order($order)
			->limit($start,$size)
			->select();

		$data = array('list'=>$list);
		return $data;

	}
	public function getBoxArrByForscreenAdsId($pub_ads_id){
	    $fields = 'box_id';
	    $where = array('forscreen_ads_id'=>$pub_ads_id);
	    $group = ' box_id';
	    $data = $this->field($fields)->where($where)->group($group)->select();
	    return $data;
	}
	public function getBoxPlayTimes($where, $field) {
		$list = $this->alias('ads')
					 ->where($where)
					 ->join('savor_pub_ads_box ads_box ON ads.id
					 = ads_box.pub_ads_id')
			         ->field($field)
			         ->select();
		return $list;
	}

	public function getAllBoxPubAds($field, $where, $group) {
		$list = $this->alias('sbox')
			->join('savor_pub_ads sads ON sbox.pub_ads_id =
			sads.id')
			->join('savor_ads ads ON sads.ads_id = ads.id')
			->group($group)
			->where($where)
			->field($field)
			->select();
		return $list;
	}

	public function getCurrentBox($field, $where, $group) {
		$list = $this->alias('adbox')
			    ->join('savor_box box on box.id = adbox.box_id')
				->join('savor_room room on room.id = box.room_id')
			    ->join('savor_hotel sht on sht.id = room.hotel_id')
			    ->group($group)
				->where($where)
			    ->field($field)
				->select();
		return $list;
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

	}

	public function addData($data, $acttype) {
		if(0 === $acttype) {
			$result = $this->add($data);
		} else {
			$uid = $data['id'];
			$result = $this->where("id={$uid}")->save($data);
		}
		return $result;
	}


	
	public function getEmptyLocation($fields,$pub_ads_id,$box_id){
	    $where = array();
	    $where['pub_ads_id'] = $pub_ads_id;
	    $where['box_id']     = $box_id;
	    $where['location_id']= array('eq',0);
	    $order = ' id asc';
	    $data = $this->field($fields)->where($where)->order($order)->select();
	    return $data;
	}
	public function getLocationList($box_id,$start_date,$end_date){
	    /* $where  = '  a.pub_ads_id='.$pub_ads_id
	              .' and a.box_id='.$box_id
	              ." and ((b.start_date>='".$start_date."' and b.start_date<='".$end_date."')
	                       or (b.start_date<='".$start_date."' and b.end_date>='".$end_date."')
	                       or (b.end_date>='".$start_date."' and b.end_date<='".$end_date."'))"; */
	    $where = 'a.box_id='.$box_id
	              ." and '".$end_date."'>=b.start_date and '".$start_date."'<=b.end_date and a.location_id!=0 and b.state!=2";
	    $data = $this->alias('a')
	         ->field('a.location_id')
	         ->join('savor_pub_ads b on a.pub_ads_id=b.id','left')
	         ->where($where)
	         ->select();           
	    return $data;
	}
	public function updateInfo($where,$data){
	    $ret = $this->where($where)->save($data);
	    return $ret;
	}

	public function deleteInfo($where){
		$ret = $this->where($where)->delete();
		return $ret;
	}

	public function removeToNew($insfield, $oldfield, $where,$newtable){
		$list = $this->where($where)->field($oldfield)->selectAdd($insfield, $newtable);
		return $list;
	}

    public function getHotelArrByPubAdsId($pub_ads_id){
        $fields = 'hotel.id hotel_id';
        $where = array('pads.pub_ads_id'=>$pub_ads_id);
        $group = ' hotel.id';
        $data = $this->alias('pads')
                     ->join('savor_box box ON pads.`box_id`=box.`id`','left')
                     ->join('savor_room room ON box.`room_id`=room.`id`','left')
                     ->join('savor_hotel hotel ON room.`hotel_id`=hotel.`id`','left')
                     ->field($fields)
                     ->where($where)
                     ->group($group)
                     ->select();
        return $data;
    }



}//End Class



