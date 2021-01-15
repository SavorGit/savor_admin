<?php

namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class StaticUserdataModel extends BaseModel{

	protected $tableName='smallapp_static_userdata';

    public function getCustomeList($fields="*",$where,$groupby='',$order='',$countfields='',$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field($fields)
            ->where($where)
            ->group($groupby)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $res_count = $this->alias('a')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field($countfields)
            ->where($where)
            ->select();
        $count = $res_count[0]['tp_count'];
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        return $data;
    }

	public function handle_user_data(){
	    $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $start = date('Y-m-d',strtotime('-1day'));
        $end = date('Y-m-d',strtotime('-1day'));
        $all_dates = $m_statistics->getDates($start,$end);

        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $feast_time = C('MEAL_TIME');
        foreach ($all_dates as $v){
            $time_date = strtotime($v);
            $static_date = date('Y-m-d',$time_date);
            $start_time = date('Y-m-d 00:00:00',$time_date);
            $end_time = date('Y-m-d 23:59:59',$time_date);
            $user_sql = "SELECT openid FROM savor_smallapp_forscreen_record WHERE create_time>='$start_time' AND create_time<='$end_time'
            and mobile_brand!='devtools' AND is_valid = 1 AND small_app_id in(1,2) group by openid";
            $res_user = $m_smallapp_forscreen_record->query($user_sql);
            if(!empty($res_user)){
                foreach ($res_user as $uv){
                    $openid = $uv['openid'];
                    $forscreen_sql = "SELECT DISTINCT hotel_id FROM savor_smallapp_forscreen_record 
                        WHERE create_time >= '$start_time' AND create_time <= '$end_time' and openid='$openid' AND small_app_id in(1,2)";
                    $res_forscreen_hotel = $m_smallapp_forscreen_record->query($forscreen_sql);
                    foreach ($res_forscreen_hotel as $hv){
                        $forscreen_hotel_id = $hv['hotel_id'];

                        $box_sql = "SELECT count(DISTINCT box_mac) as box_num FROM savor_smallapp_forscreen_record 
                          WHERE create_time >= '$start_time' AND create_time <= '$end_time' and openid='$openid' AND hotel_id={$forscreen_hotel_id} AND small_app_id in(1,2)";
                        $res_box = $m_smallapp_forscreen_record->query($box_sql);
                        $box_num = 0;
                        if(!empty($res_box)){
                            $box_num = intval($res_box[0]['box_num']);
                        }
                        $lunch_start = date("Y-m-d {$feast_time['lunch'][0]}:00",$time_date);
                        $lunch_end = date("Y-m-d {$feast_time['lunch'][1]}:00",$time_date);
                        $dinner_start = date("Y-m-d {$feast_time['dinner'][0]}:00",$time_date);
                        $dinner_end = date("Y-m-d {$feast_time['dinner'][1]}:59",$time_date);

                        $sql_lunch = "SELECT count(DISTINCT box_mac) as lunch_num FROM savor_smallapp_forscreen_record 
                          WHERE create_time >= '$lunch_start' AND create_time <= '$lunch_end' and openid='$openid' AND hotel_id={$forscreen_hotel_id} AND small_app_id in(1,2)";
                        $res_lunch = $m_smallapp_forscreen_record->query($sql_lunch);
                        $lunch_num = 0;
                        if(!empty($res_lunch)){
                            $lunch_num = intval($res_lunch[0]['lunch_num']);
                        }
                        $sql_lunch = "SELECT count(DISTINCT box_mac) as dinner_num FROM savor_smallapp_forscreen_record 
                          WHERE create_time >= '$dinner_start' AND create_time <= '$dinner_end' and openid='$openid' AND hotel_id={$forscreen_hotel_id} AND small_app_id in(1,2)";
                        $res_lunch = $m_smallapp_forscreen_record->query($sql_lunch);
                        $dinner_num = 0;
                        if(!empty($res_lunch)){
                            $dinner_num = intval($res_lunch[0]['dinner_num']);
                        }
                        $meal_num = $lunch_num+$dinner_num;

                        $hotel_sql = "SELECT area_id,area_name,hotel_id,hotel_name FROM savor_smallapp_forscreen_record 
                          WHERE create_time >= '$start_time' AND create_time <= '$end_time' and openid='$openid' AND hotel_id={$forscreen_hotel_id} AND small_app_id in(1,2)
                          order by id desc limit 0,1";
                        $res_hotel = $m_smallapp_forscreen_record->query($hotel_sql);

                        $add_data = array('openid'=>$openid,'box_num'=>$box_num,'meal_num'=>$meal_num,'static_date'=>$static_date);
                        if(!empty($res_hotel)){
                            $add_data['area_id'] = $res_hotel[0]['area_id'];
                            $add_data['area_name'] = $res_hotel[0]['area_name'];
                            $add_data['hotel_id'] = $res_hotel[0]['hotel_id'];
                            $add_data['hotel_name'] = $res_hotel[0]['hotel_name'];
                        }
                        $this->add($add_data);
                    }
                }
            }
            echo "date:$static_date ok \r\n";
        }
    }

}