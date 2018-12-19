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

    public function getHotels(){
        $table_name = C('DB_PREFIX').$this->tableName;
        $sql = "SELECT `hotel_id`,`hotel_name` FROM $table_name GROUP BY hotel_id";
        $res_dates = $this->query($sql);
        return $res_dates;
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
            $where = array('static_date'=>$date);
            if($box_mac)    $where['box_mac'] = $box_mac;
            if($hotel_id)   $where['hotel_id'] = $hotel_id;
            if($static_fj)  $where['static_fj'] = $static_fj;
            $where['all_interact_nums'] = array('GT',0);
            $fields = "count(box_mac) as fjnum";
            $ret = $this->getOne($fields, $where);
            $nums['fjnum'] = $ret['fjnum'];
        }
        if(in_array($type,array(0,1,2,3,4,6))){
            //在线屏幕数
            $where = array('static_date'=>$date);
            if($box_mac)    $where['box_mac'] = $box_mac;
            if($hotel_id)   $where['hotel_id'] = $hotel_id;
            if($static_fj)  $where['static_fj'] = $static_fj;
            $where['heart_log_meal_nums'] = array('GT',12);
            $where['_string'] = 'case static_fj when 1 then (120 div heart_log_meal_nums)<10  else (180 div heart_log_meal_nums)<10 end';
            $fields = 'count(box_mac) as zxnum';
            $ret = $this->getOne($fields, $where);
            $nums['zxnum'] = $ret['zxnum'];
        }
        if($type==0 || $type==4){
            //可投屏数
            $where = array('static_date'=>$date);
            if($box_mac)    $where['box_mac'] = $box_mac;
            if($hotel_id)   $where['hotel_id'] = $hotel_id;
            if($static_fj)  $where['static_fj'] = $static_fj;
            $where['heart_log_meal_nums'] = array('GT',0);
            $where['_string'] = '(avg_down_speed div 1024)>200';
            $fields = 'count(box_mac) as ktnum';
            $ret = $this->getOne($fields, $where);
            $nums['ktnum'] = $ret['ktnum'];
        }

        if($type==0 || $type==3){
            //网络屏幕数
            $where = array('static_date'=>$date);
            if($box_mac)    $where['box_mac'] = $box_mac;
            if($hotel_id)   $where['hotel_id'] = $hotel_id;
            if($static_fj){
                $where['static_fj'] = $static_fj;
            }else{
                $where['static_fj'] = array('eq',1);
            }
            $fields = "count(id) as wlnum";
            $ret = $this->getOne($fields, $where);
            $nums['wlnum'] = $ret['wlnum'];
        }
        if($type==0 || $type==7){
            //互动次数
            $where = array('static_date'=>$date);
            if($box_mac)    $where['box_mac'] = $box_mac;
            if($hotel_id)   $where['hotel_id'] = $hotel_id;
            if($static_fj)  $where['static_fj'] = $static_fj;
            $fields = 'sum(all_interact_nums) as hdnum';
            $ret = $this->getOne($fields, $where);
            $nums['hdnum'] = $ret['hdnum'];
        }
        if($type==9){
            $where = array('static_date'=>$date);
            if($box_mac)    $where['box_mac'] = $box_mac;
            if($hotel_id)   $where['hotel_id'] = $hotel_id;
            if($static_fj)  $where['static_fj'] = $static_fj;
            $where['heart_log_nums'] = array('GT',0);
            $fields = "sum(`heart_log_nums`) as xtnum";
            $ret = $this->getOne($fields, $where);
            $nums['xtnum'] = intval($ret['xtnum']);
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
                $rate = 0;
//                $rate = sprintf("%.2f", 互动手机数/$nums['fjnum']) * 100;
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