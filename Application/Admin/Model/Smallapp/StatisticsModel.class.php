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
}