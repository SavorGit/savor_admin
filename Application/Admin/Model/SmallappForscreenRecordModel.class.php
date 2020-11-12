<?php
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;
class SmallappForscreenRecordModel extends Model{

	protected $tableName='smallapp_forscreen_record';
	
	public function getList($fields="a.id",$where, $order='a.id desc', $start=0,$size=5){
	    $list = $this->alias('a')
	                 ->join('savor_smallapp_user user on a.openid=user.openid','left')
	                 ->field($fields)
            	     ->where($where)
            	     ->order($order)
            	     ->limit($start,$size)
            	     ->select();
	    $count = $this->alias('a')
	                  ->join('savor_smallapp_user user on a.openid=user.openid','left')
	                  ->where($where)->count();
	    
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}

    public function getCustomeList($fields="a.id",$where,$groupby='',$order='a.id desc',$countfields='',$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_box box on a.box_mac=box.mac','left')
            ->join('savor_room room on room.id= box.room_id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->join('savor_hotel_ext hotelext on hotel.id=hotelext.hotel_id','left')
            ->join('savor_area_info area on hotel.area_id=area.id','left')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field($fields)
            ->where($where)
            ->group($groupby)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $res_count = $this->alias('a')
            ->join('savor_box box on a.box_mac=box.mac','left')
            ->join('savor_room room on room.id= box.room_id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->join('savor_hotel_ext hotelext on hotel.id=hotelext.hotel_id','left')
            ->join('savor_area_info area on hotel.area_id=area.id','left')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field($countfields)
            ->where($where)->select();
        $count = $res_count[0]['tp_count'];
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        return $data;
    }

    public function getAllDatas($fields="a.id",$where,$groupby='',$order='a.id desc',$countfields='',$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_hotel_ext hotelext on a.hotel_id=hotelext.hotel_id','left')
            ->field($fields)
            ->where($where)
            ->group($groupby)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $res_count = $this->alias('a')
            ->join('savor_hotel_ext hotelext on a.hotel_id=hotelext.hotel_id','left')
            ->field($countfields)
            ->where($where)->select();
        $count = $res_count[0]['tp_count'];
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        return $data;
    }

    public function getInfo($fields='a.*',$where,$group='',$is_one=1){
        $res = $this->alias('a')
            ->join('savor_box box on a.box_mac=box.mac','left')
            ->join('savor_room room on room.id= box.room_id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->join('savor_hotel_ext hotelext on hotel.id=hotelext.hotel_id','left')
            ->join('savor_area_info area on hotel.area_id=area.id','left')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field($fields)
            ->where($where)
            ->select();
        if($group){
            $res = $this->alias('a')
                ->join('savor_box box on a.box_mac=box.mac','left')
                ->join('savor_room room on room.id= box.room_id','left')
                ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
                ->join('savor_hotel_ext hotelext on hotel.id=hotelext.hotel_id','left')
                ->join('savor_area_info area on hotel.area_id=area.id','left')
                ->join('savor_smallapp_user user on a.openid=user.openid','left')
                ->field($fields)
                ->where($where)
                ->group($group)
                ->select();
        }
        if($is_one){
            $data = !empty($res)?$res[0]:array();
        }else{
            $data = $res;
        }
        return $data;
    }
    public function getForscreenInfo($fields='a.*',$where){
        $res = $this->alias('a')
            ->join('savor_hotel_ext hotelext on a.hotel_id=hotelext.hotel_id','left')
            ->field($fields)
            ->where($where)
            ->select();
        return $res;
    }
	public function getWhere($fields,$where,$limit,$group){
	    $data = $this->alias('a')
	                 ->join('savor_box box on a.box_mac=box.mac','left')
	                 ->join('savor_room room on box.room_id=room.id','left')
	                 ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	                 ->field($fields)
	                 ->where($where)->limit($limit)->group($group)->select();
        return $data;	    
	}
    public function getDatas($fields,$where,$limit,$group){
        $data = $this->alias('a')
            ->field($fields)
            ->where($where)->limit($limit)->group($group)->select();
        return $data;
    }
	public function delWhere($where,$order,$limit){
	    $ret =  $this->where($where)->order($order)->limit($limit)->delete();
	    return $ret;
	}
    public function updateData($condition,$data){
        $result = $this->where($condition)->save($data);
        return $result;
    }

    public function cleanTestdata(){
        $m_invalid = new \Admin\Model\ForscreenInvalidlistModel();
        $orderby = 'id desc';
        $res_list = $m_invalid->getDataList('*','',$orderby);
        $all_invalidlist = array();
        foreach ($res_list as $v){
            $all_invalidlist[$v['type']][] = $v['invalidid'];
        }
        $hotel_ids = $all_invalidlist[1];
        $fields = "box.mac as box_mac";
        $where = array();
        $where['hotel.id'] = array('in',$hotel_ids);

        $m_box = new \Admin\Model\BoxModel();
        $hotel_boxs = $m_box->getBoxByCondition($fields,$where);
        $boxs = array();
        foreach ($hotel_boxs as $v){
            $boxs[]=$v['box_mac'];
        }

        if(isset($all_invalidlist[3]) && !empty($all_invalidlist[3])){
            $boxs = array_merge($boxs,$all_invalidlist[3]);
            $boxs = array_unique($boxs);
        }

        $condition = array();
        $condition['a.box_mac'] = array('in',$boxs);
        $condition['a.mobile_brand'] = array('neq','devtools');
        $res_boxdata = $this->getWhere('a.*',$condition,'','');
        $m_smallapp_forscreen_invalidrecord = new \Admin\Model\Smallapp\ForscreeninvalidrecordModel();

        foreach ($res_boxdata as $v){
            $v['forscreen_record_id'] = $v['id'];
            unset($v['id'],$v['category_id'],$v['spotstatus'],$v['scene_id'],$v['contentsoft_id'],$v['dinnernature_id'],$v['personattr_id'],$v['remark'],$v['resource_name'],$v['md5_file'],$v['save_type'],$v['file_conversion_status'],$v['box_finish_downtime'],$v['serial_number'],$v['quality_type']);
            $m_smallapp_forscreen_invalidrecord->addData($v);
        }
        $delcondition = array('box_mac'=>array('in',$boxs));
        $delcondition['mobile_brand'] = array('neq','devtools');
        $this->where($delcondition)->delete();

        if(isset($all_invalidlist[2])){
            $condition = array('a.openid'=>array('in',$all_invalidlist[2]));
            $condition['a.mobile_brand'] = array('neq','devtools');
            $res_userdata = $this->getWhere('a.*',$condition,'','');
            foreach ($res_userdata as $v){
                $v['forscreen_record_id'] = $v['id'];
                unset($v['id'],$v['category_id'],$v['spotstatus'],$v['scene_id'],$v['contentsoft_id'],$v['dinnernature_id'],$v['personattr_id'],$v['remark'],$v['resource_name'],$v['md5_file'],$v['save_type'],$v['file_conversion_status'],$v['box_finish_downtime'],$v['serial_number'],$v['quality_type']);
                $m_smallapp_forscreen_invalidrecord->addData($v);
            }
            $delcondition = array('openid'=>array('in',$all_invalidlist[2]));
            $delcondition['mobile_brand'] = array('neq','devtools');
            $this->where($delcondition)->delete();
        }
        return true;
    }
    /*
     * $time 时间戳
     * $fj_type 0全部饭局 1午饭 2晚饭
     */
    public function getFeastBoxByHotelId($hotel_id,$time,$fj_type=0,$small_id=1){
        $feast_time = C('FEAST_TIME');
        if($small_id==5){
            $feast_time = C('SALEFEAST_TIME');
        }
        $lunch_start = date("Y-m-d {$feast_time['lunch'][0]}:00",$time);
        $lunch_end = date("Y-m-d {$feast_time['lunch'][1]}:00",$time);
        $dinner_start = date("Y-m-d {$feast_time['dinner'][0]}:00",$time);
        $dinner_end = date("Y-m-d {$feast_time['dinner'][1]}:00",$time);

        $where = array('a.hotel_id'=>$hotel_id);
        $where_lunch = array('a.create_time'=>array(array('EGT',$lunch_start),array('ELT',$lunch_end)));
        $where_dinner = array('a.create_time'=>array(array('EGT',$dinner_start),array('ELT',$dinner_end)));
        switch ($fj_type){
            case 0:
                $where['_complex'] = array(
                    $where_lunch,
                    $where_dinner,
                    '_logic' => 'or'
                );
                break;
            case 1:
                $where['a.create_time'] = $where_lunch['a.create_time'];
                break;
            case 2:
                $where['a.create_time'] = $where_dinner['a.create_time'];
                break;
        }
        $where['a.is_valid'] = 1;
        $where['a.mobile_brand'] = array('neq','devtools');
        if($small_id==1){
            $where['a.small_app_id'] = array('in',array(1,2));
        }elseif($small_id==5){
            $where['a.small_app_id'] = 5;
        }else{
            $where['a.small_app_id'] = array('in',array(1,2));
        }

        $fields = 'a.box_mac';
        $groupby = 'a.box_mac';
        $result = $this->getDatas($fields,$where,'',$groupby);
        return $result;
    }

    /*
     * 获取饭局互动过的可互动版位(饭局时间内有互动且版位标识为互动版位的)
     * $time 时间戳
     * $fj_type 0全部饭局 1午饭 2晚饭
     */
    public function getFeastInteractBoxByHotelId($hotel_id,$time,$fj_type=0,$small_id=1){
        $feast_time = C('MEAL_TIME');
        $lunch_start = date("Y-m-d {$feast_time['lunch'][0]}:00",$time);
        $lunch_end = date("Y-m-d {$feast_time['lunch'][1]}:00",$time);
        $dinner_start = date("Y-m-d {$feast_time['dinner'][0]}:00",$time);
        $dinner_end = date("Y-m-d {$feast_time['dinner'][1]}:59",$time);

        $where = array('a.hotel_id'=>$hotel_id);
        $where_lunch = array('a.create_time'=>array(array('EGT',$lunch_start),array('ELT',$lunch_end)));
        $where_dinner = array('a.create_time'=>array(array('EGT',$dinner_start),array('ELT',$dinner_end)));
        switch ($fj_type){
            case 0:
                $where['_complex'] = array(
                    $where_lunch,
                    $where_dinner,
                    '_logic' => 'or'
                );
                break;
            case 1:
                $where['a.create_time'] = $where_lunch['a.create_time'];
                break;
            case 2:
                $where['a.create_time'] = $where_dinner['a.create_time'];
                break;
        }
        $where['a.is_valid'] = 1;
        $where['box.state'] = 1;
        $where['box.flag'] = 0;
        $where['box.is_interact'] = 1;
        $where['a.mobile_brand'] = array('neq','devtools');
        if($small_id==1){
            $where['a.small_app_id'] = array('in',array(1,2,11));
        }elseif($small_id==5){
            $where['a.small_app_id'] = 5;
        }

        $fields = 'a.box_mac';
        $groupby = 'a.box_mac';
        $result = $this->getWhere($fields,$where,'',$groupby);
        return $result;
    }

    public function getFeastForscreenNumByHotelId($hotel_id,$time,$fj_type=0,$small_id=1){
        $feast_time = C('MEAL_TIME');
        $lunch_start = date("Y-m-d {$feast_time['lunch'][0]}:00",$time);
        $lunch_end = date("Y-m-d {$feast_time['lunch'][1]}:00",$time);
        $dinner_start = date("Y-m-d {$feast_time['dinner'][0]}:00",$time);
        $dinner_end = date("Y-m-d {$feast_time['dinner'][1]}:59",$time);

        $where = array('a.hotel_id'=>$hotel_id);
        $where_lunch = array('a.create_time'=>array(array('EGT',$lunch_start),array('ELT',$lunch_end)));
        $where_dinner = array('a.create_time'=>array(array('EGT',$dinner_start),array('ELT',$dinner_end)));
        switch ($fj_type){
            case 0:
                $where['_complex'] = array(
                    $where_lunch,
                    $where_dinner,
                    '_logic' => 'or'
                );
                break;
            case 1:
                $where['a.create_time'] = $where_lunch['a.create_time'];
                break;
            case 2:
                $where['a.create_time'] = $where_dinner['a.create_time'];
                break;
        }
        $where['a.is_valid'] = 1;
        $where['a.mobile_brand'] = array('neq','devtools');
        if($small_id==1){//1主干版 2极简版 5销售端 11游戏
            $where['a.small_app_id'] = array('in',array(1,2,11));
        }elseif($small_id==5){
            $where['a.small_app_id'] = 5;
        }

        $fields = 'count(a.id) as num';
        $result = $this->getDatas($fields,$where,'','');
        $num = 0;
        if(!empty($result)){
            $num = intval($result[0]['num']);
        }
        return $num;
    }

    public function getSaleSignForscreenNumByHotelId($hotel_id,$time){
        $start_time = date("Y-m-d 00:00:00",$time);
        $end_time = date("Y-m-d 23:59:59",$time);

        $where = array();
        $where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
        $where['a.hotel_id'] = $hotel_id;
        $where['a.small_app_id'] = 5;
        $where['a.action'] = 5;
        $where['a.mobile_brand'] = 'iPhone';
        $where['a.mobile_model'] = 'iPhone XR';

        $fields = 'count(a.id) as num';
        $result = $this->getDatas($fields,$where,'','');
        $num = 0;
        if(!empty($result)){
            $num = intval($result[0]['num']);
        }
        return $num;
    }

    public function getAvgspeedByHotelId($hotel_id,$time){
        if(is_array($time)){
            $start = date("Y-m-d 00:00:00",$time[0]);
            $end = date("Y-m-d 23:59:59",$time[1]);
        }else{
            $start = date("Y-m-d 00:00:00",$time);
            $end = date("Y-m-d 23:59:59",$time);
        }

        $where = array('a.create_time'=>array(array('EGT',$start),array('ELT',$end)));
        $where['a.hotel_id'] = $hotel_id;
        $where['_string'] = 'a.resource_size>0 and a.box_res_sdown_time>0 AND a.box_res_edown_time>0 AND (a.box_res_edown_time>a.box_res_sdown_time)';
        $fields = 'sum(a.resource_size) as resource_size,sum(a.box_res_edown_time-a.box_res_sdown_time) as down_time';
        $result = $this->getDatas($fields,$where,'','');
        $avgspeed = 0;
        if(!empty($result)){
            $down_time = $result[0]['down_time']/1000;
            $resource_size = $result[0]['resource_size']/1024;
            $avgspeed = intval($resource_size/$down_time);
        }
        return $avgspeed;
    }

    public function getAvgspeedByStaticHotelId($hotel_id,$date){
        $sql = "select avg(NULLIF(avg_down_speed,0)) as avg_speed from savor_smallapp_statistics where hotel_id={$hotel_id} and static_date={$date}";
        $result = $this->query($sql);
        $avgspeed = 0;
        if(!empty($result)){
            $avgspeed = intval($result[0]['avg_speed']/1024);
        }
        return $avgspeed;
    }
}