<?php
/**
 * @desc 心跳上报历史统计数据表
 * @since 20170815
 * @author zhang.yingtao
 */
namespace Admin\Model;
use Common\Lib\Page;
class HeartAllLogModel extends BaseModel{
	protected $tableName='heart_all_log';
	
	/**
	 * @获取分页数据
	 */
	public function getlist($field= '*',$where,$order,$start=0,$size=5){
	    $list = $this->field($field)->where($where)->order($order)->limit($start,$size)->select();
	    $count = $this->getCount($where);
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;  
	}

    public function getLostBoxNum($hotel_id){
        $sql_lost = "select count(DISTINCT(box_mac)) as num from savor_heart_log where hotel_id={$hotel_id} and type=2 and TIMESTAMPDIFF(DAY,last_heart_time,now())>3";
        $res_lost = $this->query($sql_lost);
        $lost_num = 0;
        if(!empty($res_lost)){
            $lost_num = intval($res_lost[0]['num']);
        }
        return $lost_num;
    }

	public function getHoursCondition($fj_type=0){
        switch ($fj_type){
            case 0:
                $hours_str = 'a.hour0+a.hour1+a.hour2+a.hour3+a.hour4+a.hour5+a.hour6+a.hour7+a.hour8+a.hour9+a.hour10+a.hour11+a.hour12+a.hour13+a.hour14+a.hour15+a.hour16+a.hour17+a.hour18+a.hour19+a.hour20+a.hour21+a.hour22+a.hour23';
                break;
            case 1://午饭 10点到15点
                $hours_str = 'a.hour10+a.hour11+a.hour12+a.hour13+a.hour14';
                break;
            case 2://晚饭 17点到24点
                $hours_str = 'a.hour17+a.hour18+a.hour19+a.hour20+a.hour21+a.hour22+a.hour23';
                break;
            default:
                $hours_str = '';
        }
        return $hours_str;
    }

    public function getHotelAllHeart($date,$hotel_id){
        $hours_str = $this->getHoursCondition();
        $date_condition = '';
        if(is_array($date)){
            $date_condition = "a.date>={$date[0]} and a.date<={$date[1]}";
        }else{
            $date_condition = "a.date={$date}";
        }
        $sql = "select sum({$hours_str}) as heart_num from savor_heart_all_log as a left join savor_box as box on a.mac=box.mac left join savor_room as room on box.room_id=room.id left join savor_hotel as hotel on room.hotel_id=hotel.id 
        where {$date_condition} and a.type=2 and a.hotel_id={$hotel_id} and ({$hours_str})>0 and box.state=1 and box.flag=0";
        $res_heart = $this->query($sql);
        $heart_num = 0;
        if(!empty($res_heart)){
            $heart_num = intval($res_heart[0]['heart_num']);
        }
        return $heart_num;
    }

    public function getHotelMealHeart($date,$hotel_id){
        $hours_lunch_str = $this->getHoursCondition(1);
        $hours_dinner_str = $this->getHoursCondition(2);

        $hours_str = $hours_lunch_str.'+'.$hours_dinner_str;
        $date_condition = '';
        if(is_array($date)){
            $date_condition = "a.date>={$date[0]} and a.date<={$date[1]}";
        }else{
            $date_condition = "a.date={$date}";
        }
        $sql = "select sum({$hours_str}) as heart_num from savor_heart_all_log as a left join savor_box as box on a.mac=box.mac left join savor_room as room on box.room_id=room.id left join savor_hotel as hotel on room.hotel_id=hotel.id 
        where {$date_condition} and a.type=2 and a.hotel_id={$hotel_id} and ({$hours_str})>0 and box.state=1 and box.flag=0";
        $res_heart = $this->query($sql);
        $heart_num = 0;
        if(!empty($res_heart)){
            $heart_num = intval($res_heart[0]['heart_num']);
        }
        return $heart_num;
    }

    /*
     * 获取酒楼在线屏(符合在线条件)
     * $is_interact 1 符合在线条件且版位标识为互动版位
     */
    public function getHotelOnlineBoxnum($date,$hotel_id,$fj_type=0,$is_interact=1){
        $hours_str = $this->getHoursCondition($fj_type);
        if($fj_type==1){
            $hour_condition = "($hours_str)>5 and (300 div ($hours_str))<10 ";

            $hour_condition = "($hours_str)>12";
        }elseif($fj_type==2){
            $hour_condition = "($hours_str)>5 and (420 div ($hours_str))<10 ";

            $hour_condition = "($hours_str)>16";
        }else{
            $lunch_hours_str = $this->getHoursCondition(1);
            $dinner_hours_str = $this->getHoursCondition(2);
            $hour_condition = "((($lunch_hours_str)>5 and (300 div ($lunch_hours_str))<10) or (($dinner_hours_str)>5 and (420 div ($dinner_hours_str))<10))";
            $hour_condition = "(($lunch_hours_str)>12 or ($dinner_hours_str)>16)";
        }


        $sql = "select count(DISTINCT(a.mac)) as box_num from savor_heart_all_log as a left join savor_box as box on a.mac=box.mac left join savor_room as room on box.room_id=room.id left join savor_hotel as hotel on room.hotel_id=hotel.id
        where a.date={$date} and a.type=2 and a.hotel_id={$hotel_id} and {$hour_condition} and box.state=1 and box.flag=0 ";
        if($is_interact){
            $sql.=" and box.is_interact=1";
        }
        $res_boxnum = $this->query($sql);
        $box_num = 0;
        if(!empty($res_boxnum)){
            $box_num = intval($res_boxnum[0]['box_num']);
        }
        return $box_num;
    }


	public function getOne($mac,$type,$date){
	    $where = array();
	    $where['date']= $date;
	    $where['mac'] = $mac;
	    $where['type']= $type;
	    $info = $this->where($where)->find();
	    return $info;
	}

	public function getCount($where){
        $count = $this->where($where)->count();
        return $count;
    }

	public function addInfo($data){
	    if(!empty($data)){
	        $ret = $this->add($data);
	    }else{
	        $ret = false;
	    }
	    return $ret;
	}

	public function updateInfo($mac,$type,$date,$filed,$apk = ''){
	    $set = '';
	    if(!empty($apk)){ 
	        $set  = ",apk_version='".$apk."'";
	    }
	    $sql ="update savor_heart_all_log set `$filed` = `$filed`+1 ".$set."where `date`={$date} and  `mac`='{$mac}' and `type`={$type}";
	    return $this->execute($sql);
	}
}