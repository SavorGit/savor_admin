<?php

namespace Admin\Model\Smallapp;
use Think\Model;
class StaticHoteldataModel extends Model{

	protected $tableName='smallapp_static_hoteldata';

	public function handle_hotel_data(){
	    $m_hotel = new \Admin\Model\HotelModel();
        $field = 'a.id as hotel_id,a.name as hotel_name,area.id as area_id,area.region_name as area_name,a.hotel_box_type,a.level as hotel_level,
	    ext.trainer_id,ext.train_date,ext.maintainer_id,a.tech_maintainer';
        $where = array('a.state'=>1,'a.flag'=>0,'a.type'=>1);
        $all_hotel_types = C('heart_hotel_box_type');
        $where['a.hotel_box_type'] = array('in',array_keys($all_hotel_types));
        $res_hotel = $m_hotel->getHotels($field,$where);

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
//        $start = date('Y-m-d',strtotime('-1day'));
//        $end = date('Y-m-d',strtotime('-1day'));

        $start = '2020-08-12';
        $end = '2020-08-12';


        $all_dates = $m_statistics->getDates($start,$end);

        $m_box = new \Admin\Model\BoxModel();
        $m_qrcodelog = new \Admin\Model\Smallapp\QrcodeLogModel();
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $m_sysuser = new \Admin\Model\UserModel();
        foreach ($all_dates as $v){
            $time_date = strtotime($v);
            $static_date = date('Ymd',$time_date);
            $start_time = date('Y-m-d 00:00:00',$time_date);
            $end_time = date('Y-m-d 23:59:59',$time_date);
            foreach ($res_hotel as $hv){
                $hotel_id = $hv['hotel_id'];
                $box_fields = 'count(box.id) as num,box.fault_status';
                $box_where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
                $group = 'fault_status';
                $res_box = $m_box->getBoxByCondition($box_fields,$box_where,$group);
                $faultbox_num = $normalbox_num = 0;
                foreach ($res_box as $bv){
                    if($bv['fault_status']==1){
                        $normalbox_num = $bv['num'];
                    }elseif($bv['fault_status']==2){
                        $faultbox_num = $bv['num'];
                    }
                }
                $box_num = $normalbox_num+$faultbox_num;
                $fault_rate = 0;
                if($box_num && $faultbox_num){
                    $fault_rate = sprintf("%.2f",$faultbox_num/$box_num);
                }

                $static_where = array('s.hotel_id'=>$hotel_id,'s.static_date'=>$static_date,'b.state'=>1,'b.flag'=>0);
                //网络屏幕数
                $fields = "count(DISTINCT s.box_mac) as wlnum";
                $ret = $m_statistics->getOnlinnum($fields, $static_where);
                $wlnum = intval($ret[0]['wlnum']);

                //故障网络屏幕数
                $fault_where = $static_where;
                $fields = "count(DISTINCT s.box_mac) as faultwlnum";
                $fault_where['b.fault_status'] = 2;
                $ret_fault = $m_statistics->getOnlinnum($fields, $fault_where);
                $fault_wlnum = intval($ret_fault[0]['faultwlnum']);

//                $now_wlnum = $wlnum-$fault_wlnum;
//                $static_where['b.fault_status'] = 1;
                //午饭，晚饭在线屏幕数
                $feast_where = $static_where;
                $feast_where['s.heart_log_meal_nums'] = array('GT',5);
                $feast_where['_string'] = 'case s.static_fj when 1 then (120 div s.heart_log_meal_nums)<10  else (180 div s.heart_log_meal_nums)<10 end';
                $fields = 'count(s.box_mac) as zxnum,s.static_fj';
                $res_online = $m_statistics->getOnlinnum($fields, $feast_where,'s.static_fj');
                $lunch_zxnum = $dinner_zxnum = $lunch_rate = $dinner_rate = 0;
                foreach ($res_online as $ov){
                    if($ov['static_fj']==1){
                        $lunch_zxnum = $ov['zxnum'];
                        $lunch_rate = sprintf("%.2f",$lunch_zxnum/$wlnum);
                    }
                    if($ov['static_fj']==2){
                        $dinner_zxnum = $ov['zxnum'];
                        $dinner_rate = sprintf("%.2f",$dinner_zxnum/$wlnum);
                    }
                }
                //午饭，晚饭在线屏幕数(去重)
                $uniq_where = $static_where;
                $uniq_where['s.heart_log_meal_nums'] = array('GT',5);
                $uniq_where['_string'] = 'case s.static_fj when 1 then (120 div s.heart_log_meal_nums)<10  else (180 div s.heart_log_meal_nums)<10 end';
                $fields = "count(DISTINCT s.box_mac) as feastnum";
                $ret_uniq = $m_statistics->getOnlinnum($fields, $uniq_where);
                $zxnum = intval($ret_uniq[0]['feastnum']);
                $zxrate = 0;
                if($zxnum){
                    $zxrate = sprintf("%.2f",$zxnum/$wlnum);
                }
                //互动饭局数
                $fj_where = $static_where;
                $fj_where['s.all_interact_nums'] = array('GT',0);
                $fields = "count(DISTINCT s.box_mac) as feastnum";
                $ret_fj = $m_statistics->getOnlinnum($fields, $fj_where);
                $fjnum = intval($ret_fj[0]['feastnum']);
                $fjrate = 0;
                if($fjnum){
                    $fjrate = sprintf("%.2f",$fjnum/$wlnum);
                }
                //扫码数
                $fields = "count(a.id) as num";
                $qrcode_where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
                $qrcode_where['a.type'] = array('in',array(8,13));
                $qrcode_where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
                $res_qrcode = $m_qrcodelog->getScanqrcodeNum($fields,$qrcode_where);
                $scancode_num = intval($res_qrcode[0]['num']);

                //互动总数
                $interact_standard_num = $interact_mini_num = $interact_sale_num = 0;
                $forscreen_where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0,'a.is_valid'=>1);
                $forscreen_where['a.mobile_brand'] = array('neq','devtools');
                $forscreen_where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
                $forscreen_where['a.small_app_id'] = array('in',array(1,2,5));//1普通版,2极简版,5销售端
                $fields = "count(a.id) as fnum,a.small_app_id";
                $res_forscreen = $m_smallapp_forscreen_record->getWhere($fields,$forscreen_where,'','a.small_app_id');
                foreach ($res_forscreen as $fv){
                    switch ($fv['small_app_id']){
                        case 1:
                            $interact_standard_num = $fv['fnum'];
                            break;
                        case 2:
                            $interact_mini_num = $fv['fnum'];
                            break;
                        case 5:
                            $interact_sale_num = $fv['fnum'];
                            break;
                    }
                }
                $interact_num = $interact_standard_num+$interact_mini_num+$interact_sale_num;
                $add_data = array('area_id'=>$hv['area_id'],'area_name'=>$hv['area_name'],'hotel_id'=>$hv['hotel_id'],'hotel_name'=>$hv['hotel_name'],
                    'hotel_box_type'=>$hv['hotel_box_type'],'hotel_level'=>$hv['hotel_level'],'trainer_id'=>$hv['trainer_id'],'train_date'=>$hv['train_date'],
                    'maintainer_id'=>$hv['maintainer_id'],'tech_maintainer'=>$hv['tech_maintainer'],'box_num'=>$box_num,'faultbox_num'=>$faultbox_num,
                    'normalbox_num'=>$normalbox_num,'fault_rate'=>$fault_rate,'wlnum'=>$wlnum,'fault_wlnum'=>$fault_wlnum,'lunch_zxnum'=>$lunch_zxnum,
                    'dinner_zxnum'=>$dinner_zxnum,'zxnum'=>$zxnum,'lunch_rate'=>$lunch_rate,'dinner_rate'=>$dinner_rate,'zxrate'=>$zxrate,'fjnum'=>$fjnum,
                    'fjrate'=>$fjrate,'scancode_num'=>$scancode_num,'interact_num'=>$interact_num,'interact_standard_num'=>$interact_standard_num,
                    'interact_mini_num'=>$interact_mini_num,'interact_sale_num'=>$interact_sale_num,'date'=>$static_date
                    );
                if($hv['trainer_id']){
                    $res_user = $m_sysuser->getUserInfo($hv['trainer_id']);
                    $add_data['trainer'] = $res_user['remark'];
                }
                if($hv['maintainer_id']){
                    $res_user = $m_sysuser->getUserInfo($hv['maintainer_id']);
                    $add_data['maintainer'] = $res_user['remark'];
                }
                $this->add($add_data);
//                echo "hotel_id:$hotel_id ok \r\n";
            }
            echo "date:$static_date ok \r\n";
        }
    }

}