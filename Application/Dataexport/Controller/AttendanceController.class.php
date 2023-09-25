<?php
namespace Dataexport\Controller;

class AttendanceController extends BaseController{

    public function datalist(){
        $static_month = I('month',0,'intval');

        $static_month = date('Ym',strtotime($static_month));
        $m_oa_attendance = new \Admin\Model\AttendanceModel();
        $res_data = $m_oa_attendance->getAllData('*',array('static_month'=>$static_month),'static_date asc');
        $datalist = array();
        $all_status_str = array('1'=>'是','2'=>'否');
        foreach ($res_data as $v){
            $belate_str = isset($all_status_str[$v['belate_status']])?$all_status_str[$v['belate_status']]:'';
            $leaveearly_str = isset($all_status_str[$v['leaveearly_status']])?$all_status_str[$v['leaveearly_status']]:'';
            $clock_detail = $v['clock_detail'];
            $all_clock_detail = explode(',',$clock_detail);
            for($i=0;$i<10;$i++){
                $di = $i+1;
                $v["clock_detail{$di}"] = isset($all_clock_detail[$i])?$all_clock_detail[$i]:'';
            }

            $v['belate_str'] = $belate_str;
            $v['leaveearly_str'] = $leaveearly_str;
            $datalist[]=$v;
        }

        $cell = array(
            array('area_name','城市'),
            array('ops_staff_name','姓名'),
            array('job','职位'),
            array('static_date','日期'),
            array('start_clock_time','第一次打卡时间'),
            array('end_clock_time','最后一次打卡时间'),
            array('visit_hotel_num','拜访家数'),
            array('belate_str','迟到'),
            array('leaveearly_str','早退'),
            array('first_clock_hotel_name','第一次打卡酒楼名称'),
            array('first_clock_hotel_id','第一次打卡酒楼ID'),
            array('clock_detail1','打卡明细1'),
            array('clock_detail2','打卡明细2'),
            array('clock_detail3','打卡明细3'),
            array('clock_detail4','打卡明细4'),
            array('clock_detail5','打卡明细5'),
            array('clock_detail6','打卡明细6'),
            array('clock_detail7','打卡明细7'),
            array('clock_detail8','打卡明细8'),
            array('clock_detail9','打卡明细9'),
            array('clock_detail10','打卡明细10'),
        );
        $filename = '考勤数据统计';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}