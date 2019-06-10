<?php
/**
 * @desc   小程序数据统计
 * @author zhang.yingtao
 * @since  2018-11-22
 */
namespace Admin\Model\Smallapp;
use Think\Model;
use Common\Lib\Page;
class StatisticsModel extends Model
{
	protected $tableName='smallapp_statistics';
	public function getPageList($fields,$where,$order,$group,$start,$size){
	    $list = $this->alias('a')
	                 ->join('savor_sysuser user on a.maintainer_id=user.id','left')
	                 ->field($fields)->where($where)->order($order)->group($group)->limit($start,$size)->select();
	    $ret = $this->alias('a')
	                ->where($where)
	                ->group($group)
	                ->select();
	    $count = count($ret);
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}

    public function getOnlinnum($fields,$where){
        $data =$this->alias('s')
            ->join('savor_box b on s.box_mac=b.mac','left')
            ->field($fields)->where($where)->select();
        return $data;
    }


	public function getWhere($fields,$where,$order,$limit,$group){
	    $data = $this->field($fields)->where($where)->order($order)->group($group)->limit($limit)->select();
	    return $data;
	}
	public function getOne($fields,$where,$order){
	    $data =  $this->field($fields)->where($where)->order($order)->find();
	    return $data;
	}
	public function countNum($where){
	    $nums = $this->where($where)->count();
	    return $nums;
	}

	public function getDays($day,$start_date,$end_date){
	    $table_name = C('DB_PREFIX').$this->tableName;
        if($start_date && $end_date){
            $start_date = date('Ymd',strtotime($start_date));
            $end_date = date('Ymd',strtotime($end_date));
        }else{
            if($day == 0 || $day == 1){
                $total_day = 30;
            }else{
                $total_day = $day;
            }
            $end_date = date('Ymd',strtotime('-1 day'));
            $start_date = date('Ymd',strtotime("-$total_day day"));
        }
        $sql = "select static_date from $table_name where static_date>=$start_date && static_date<=$end_date GROUP BY static_date ORDER BY static_date asc";
        $res_dates = $this->query($sql);
        $dates = array();
        foreach ($res_dates as $v){
            $dates[] = $v['static_date'];
        }
        return $dates;
    }

    public function getDates($start,$end){
        $all_dates = array();
        $dt_start = strtotime($start);
        $dt_end = strtotime($end);
        while ($dt_start<=$dt_end){
            $all_dates[]=date('Y-m-d',$dt_start);
            $dt_start=strtotime('+1 day',$dt_start);
        }
        return $all_dates;
    }

    public function getHotels(){
        $table_name = C('DB_PREFIX').$this->tableName;
        $sql = "SELECT `hotel_id`,`hotel_name` FROM $table_name GROUP BY hotel_id";
        $res_dates = $this->query($sql);
        return $res_dates;
    }

    public function getFeast($fields,$where){
        $res = $this->alias('a')
            ->join('savor_box box on a.box_mac=box.mac','left')
            ->join('savor_room room on room.id= box.room_id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->join('savor_area_info area on hotel.area_id=area.id','left')
            ->field($fields)
            ->where($where)
            ->select();
        $data = !empty($res)?$res[0]:array();
        return $data;
    }

    public function getRatenumDatewhere($date){
        $where = array();
        if(is_array($date)){
            $start_time = $date[0];
            $end_time = $date[1];
            $where['static_date'] = array(array('EGT',$start_time),array('ELT',$end_time));
        }else{
            $where['static_date'] = $date;
        }
        return $where;
    }

    /*
     * 获取比率对应数
     * type 0所有 1转换率 2传播率 3屏幕在线率 4网络质量 5互动饭局数,6在线屏幕数,7互动次数,8酒楼评级,9心跳次数
     *
     */
    public function getRatenum($date,$static_fj=0,$type=0,$hotel_id=0,$box_mac=''){
        $nums = array();
        if(in_array($type,array(0,1,2,3,4,5))){
            //互动饭局数
            $where = $this->getRatenumDatewhere($date);
            if($box_mac)    $where['box_mac'] = $box_mac;
            if(!empty($hotel_id)){
                if(is_array($hotel_id)){
                    $where['hotel_id'] = array('in',$hotel_id);
                }else{
                    $where['hotel_id'] = $hotel_id;
                }
            }
            if($static_fj)  $where['static_fj'] = $static_fj;
            $where['all_interact_nums'] = array('GT',0);
            $fields = "count(box_mac) as fjnum";
            if(is_array($date)){
                $fields = "count(box_mac) as fjnum,static_date";
                $groupby = "static_date";
                $ret = $this->getWhere($fields, $where,'','',$groupby);
                $nums['fjnum'] = $ret;
            }else{
                $ret = $this->getOne($fields, $where);
                $nums['fjnum'] = intval($ret['fjnum']);
            }

        }
        if(in_array($type,array(0,1,2,3,4,6))){
            //在线屏幕数
            $where = $this->getRatenumDatewhere($date);
            if($box_mac)    $where['box_mac'] = $box_mac;
            if(!empty($hotel_id)){
                if(is_array($hotel_id)){
                    $where['hotel_id'] = array('in',$hotel_id);
                }else{
                    $where['hotel_id'] = $hotel_id;
                }
            }
            if($static_fj)  $where['static_fj'] = $static_fj;
//            $where['heart_log_meal_nums'] = array('GT',12);
            $where['heart_log_meal_nums'] = array('GT',5);
            $where['_string'] = 'case static_fj when 1 then (120 div heart_log_meal_nums)<10  else (180 div heart_log_meal_nums)<10 end';
            $fields = 'count(box_mac) as zxnum';
            if(is_array($date)){
                $fields = "count(box_mac) as zxnum,static_date";
                $groupby = "static_date";
                $ret = $this->getWhere($fields, $where,'','',$groupby);
                $nums['zxnum'] = $ret;
            }else{
                $ret = $this->getOne($fields, $where);
                $nums['zxnum'] = intval($ret['zxnum']);
            }

        }
        if($type==0 || $type==4){
            //可投屏数
            $where = $this->getRatenumDatewhere($date);
            if($box_mac)    $where['box_mac'] = $box_mac;
            if(!empty($hotel_id)){
                if(is_array($hotel_id)){
                    $where['hotel_id'] = array('in',$hotel_id);
                }else{
                    $where['hotel_id'] = $hotel_id;
                }
            }
            if($static_fj)  $where['static_fj'] = $static_fj;
            $where['heart_log_meal_nums'] = array('GT',0);
            $where['_string'] = '(avg_down_speed div 1024)>200';
            $fields = 'count(box_mac) as ktnum';
            if(is_array($date)){
                $fields = "count(box_mac) as ktnum,static_date";
                $groupby = "static_date";
                $ret = $this->getWhere($fields, $where,'','',$groupby);
                $nums['ktnum'] = $ret;
            }else{
                $ret = $this->getOne($fields, $where);
                $nums['ktnum'] = intval($ret['ktnum']);
            }

        }

        if($type==0 || $type==3){
            //网络屏幕数
            $where = $this->getRatenumDatewhere($date);
            if($box_mac)    $where['box_mac'] = $box_mac;
            if(!empty($hotel_id)){
                if(is_array($hotel_id)){
                    $where['hotel_id'] = array('in',$hotel_id);
                }else{
                    $where['hotel_id'] = $hotel_id;
                }
            }
            if($static_fj){
                $where['static_fj'] = $static_fj;
            }
            $fields = "count(id) as wlnum";
            if(is_array($date)){
                $fields = "count(id) as wlnum,static_date";
                $groupby = "static_date";
                $ret = $this->getWhere($fields, $where,'','',$groupby);
                $nums['wlnum'] = $ret;
            }else{
                $ret = $this->getOne($fields, $where);
                $nums['wlnum'] = intval($ret['wlnum']);
            }

        }
        if($type==0 || $type==7){
            //互动次数
            $where = $this->getRatenumDatewhere($date);
            if($box_mac)    $where['box_mac'] = $box_mac;
            if(!empty($hotel_id)){
                if(is_array($hotel_id)){
                    $where['hotel_id'] = array('in',$hotel_id);
                }else{
                    $where['hotel_id'] = $hotel_id;
                }
            }
            if($static_fj)  $where['static_fj'] = $static_fj;
            $fields = 'sum(all_interact_nums) as hdnum';
            if(is_array($date)){
                $fields = "sum(all_interact_nums) as hdnum,static_date";
                $groupby = "static_date";
                $ret = $this->getWhere($fields, $where,'','',$groupby);
                $nums['hdnum'] = $ret;
            }else{
                $ret = $this->getOne($fields, $where);
                $nums['hdnum'] = intval($ret['hdnum']);
            }

        }
        if($type==9){
            $where = $this->getRatenumDatewhere($date);
            if($box_mac)    $where['box_mac'] = $box_mac;
            if(!empty($hotel_id)){
                if(is_array($hotel_id)){
                    $where['hotel_id'] = array('in',$hotel_id);
                }else{
                    $where['hotel_id'] = $hotel_id;
                }
            }
            if($static_fj)  $where['static_fj'] = $static_fj;
            $where['heart_log_nums'] = array('GT',0);
            $fields = "sum(`heart_log_nums`) as xtnum";
            if(is_array($date)){
                $fields = "sum(`heart_log_nums`) as xtnum,static_date";
                $groupby = "static_date";
                $ret = $this->getWhere($fields, $where,'','',$groupby);
                $nums['xtnum'] = $ret;
            }else{
                $ret = $this->getOne($fields, $where);
                $nums['xtnum'] = intval($ret['xtnum']);
            }

        }

        if($type==0 || $type==2){
            $where = $this->getRatenumDatewhere($date);
            if($box_mac)    $where['box_mac'] = $box_mac;
            if(!empty($hotel_id)){
                if(is_array($hotel_id)){
                    $where['hotel_id'] = array('in',$hotel_id);
                }else{
                    $where['hotel_id'] = $hotel_id;
                }
            }
            if($static_fj)  $where['static_fj'] = $static_fj;
            $where['hd_mobile_nums'] = array('GT',0);
            $fields = "sum(`hd_mobile_nums`) as mobilenum";
            if(is_array($date)){
                $fields = "sum(`hd_mobile_nums`) as mobilenum,static_date";
                $groupby = "static_date";
                $ret = $this->getWhere($fields, $where,'','',$groupby);
                $nums['mobilenum'] = $ret;
            }else{
                $ret = $this->getOne($fields, $where);
                $nums['mobilenum'] = intval($ret['mobilenum']);
            }
        }
        return $nums;
    }

    /*
     * 获取比率
     * type 1转换率 2传播率 3屏幕在线率 4 网络质量
     */
    public function getRate($nums,$type){
        switch ($type){
            case 1:
                $rate = sprintf("%.2f", $nums['fjnum']/$nums['zxnum']) * 100;
                break;
            case 2:
                $rate = sprintf("%.2f", $nums['mobilenum']/$nums['fjnum']);
                break;
            case 3:
                $rate = sprintf("%.2f", $nums['zxnum']/$nums['wlnum']) * 100;
                break;
            case 4:
                $rate = sprintf("%.2f", $nums['ktnum']/$nums['zxnum']) * 100;
                break;
            default:
                $rate = 0;
        }
        return $rate;
    }

}