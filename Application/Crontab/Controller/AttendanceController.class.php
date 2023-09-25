<?php
namespace Crontab\Controller;
use Think\Controller;

class AttendanceController extends Controller{

    public function statsalerecord(){
        $m_attendance = new \Admin\Model\AttendanceModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $sql_ops = "select a.id as ops_staff_id,a.area_id,area.region_name as area_name,a.job,suser.remark as ops_uname from savor_ops_staff as a 
            left join savor_area_info as area on a.area_id=area.id
            left join savor_sysuser as suser on a.sysuser_id=suser.id where suser.remark!=''
            order by a.area_id asc ";
        $res_staff = $m_attendance->query($sql_ops);

        $static_date = date('Y-m-d',strtotime('-1 day'));
        $static_month = date('Ym',strtotime($static_date));
        $start_time = "$static_date 00:00:00";
        $end_time = "$static_date 23:59:59";
        $sql = "select ops_staff_id,GROUP_CONCAT(id) as record_ids,min(signin_time) as min_signin_time,max(signout_time) as max_signout_time
            from savor_crm_salerecord 
            where add_time>='$start_time' and add_time<='$end_time' and type=1 and status=2 group by ops_staff_id";
        $res_data = $m_attendance->query($sql);
        $salerecord_data = array();
        if(!empty($res_data)){
            foreach ($res_data as $v){
                $salerecord_data[$v['ops_staff_id']]=$v;
            }
        }
        foreach ($res_staff as $sv){
            $job = $sv['job'];
            $area_id = $sv['area_id'];
            $area_name = $sv['area_name'];
            $ops_staff_name = $sv['ops_uname'];
            $ops_staff_id = $sv['ops_staff_id'];

            if(!in_array($job,array('驻店销售','渠道开发','运维工程师'))){
                continue;
            }
            $belate_status = 0;
            $leaveearly_status = 0;
            $visit_hotel_num = 0;
            $first_clock_hotel_id=0;
            $first_clock_hotel_name = '';
            $clock_detail = '';
            $start_clock_time = '';
            $end_clock_time = '';
            if(isset($salerecord_data[$ops_staff_id])){
                $salerecord_info = $salerecord_data[$ops_staff_id];

                $all_clock_detail = array();
                $all_hotels = array();
                $salerecord_ids = $salerecord_info['record_ids'];
                $sql_detail = "select DATE_FORMAT(signin_time, '%H:%i:%s') as signin_time,DATE_FORMAT(signout_time, '%H:%i:%s') as signout_time,signin_hotel_id 
                    from savor_crm_salerecord where id in ({$salerecord_ids}) order by signin_time asc ";
                $res_detail = $m_attendance->query($sql_detail);
                foreach ($res_detail as $dv){
                    $all_hotels[$dv['signin_hotel_id']] = $dv['signin_hotel_id'];
                    if($first_clock_hotel_id==0){
                        $first_clock_hotel_id = $dv['signin_hotel_id'];
                    }
                    $all_clock_detail[]=$dv['signin_time'].'-'.$dv['signout_time'];
                }
                $visit_hotel_num = count($all_hotels);
                $clock_detail = join(',',$all_clock_detail);
                if($first_clock_hotel_id){
                    $res_hotel = $m_hotel->getOne($first_clock_hotel_id);
                    $first_clock_hotel_name = $res_hotel['name'];
                }
                $clock_in_time = $clock_out_time = '';
                if($job=='驻店销售'){
                    $clock_in_time = "$static_date 10:30:00";
                    $clock_out_time = "$static_date 20:30:00";
                }elseif($job=='渠道开发' || $job=='运维工程师'){
                    $clock_in_time = "$static_date 10:15:00";
                    $clock_out_time = "$static_date 18:00:00";
                }
                $start_clock_time = $salerecord_info['min_signin_time'];
                $end_clock_time = $salerecord_info['max_signout_time'];
                if(!empty($clock_in_time)){
                    if($salerecord_info['min_signin_time']>$clock_in_time){
                        $belate_status = 1;
                    }else{
                        $belate_status = 2;
                    }
                    if($salerecord_info['max_signout_time']<$clock_out_time){
                        $leaveearly_status = 1;
                    }else{
                        $leaveearly_status = 2;
                    }
                }
            }

            $add_data = array('area_id'=>$area_id,'area_name'=>$area_name,'ops_staff_id'=>$ops_staff_id,'ops_staff_name'=>$ops_staff_name,
                'job'=>$job,'visit_hotel_num'=>$visit_hotel_num,'start_clock_time'=>$start_clock_time,'end_clock_time'=>$end_clock_time,
                'belate_status'=>$belate_status,'leaveearly_status'=>$leaveearly_status,'first_clock_hotel_id'=>$first_clock_hotel_id,'first_clock_hotel_name'=>$first_clock_hotel_name,
                'clock_detail'=>$clock_detail,'static_date'=>$static_date,'static_month'=>$static_month
            );
            $m_attendance->add($add_data);
        }
        echo "static_date:$static_date ok \r\n";

    }
}
