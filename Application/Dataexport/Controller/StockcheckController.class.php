<?php
namespace Dataexport\Controller;

class StockcheckController extends BaseController{

    public function taskdata(){
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
        }else{
            $start_time = date('Y-m-01 00:00:00',strtotime("-1 month"));
            $end_time = date('Y-m-31 23:59:59',strtotime("-1 month"));
        }

        $release_sql  = "select count(DISTINCT a.task_id) as num,a.hotel_id,GROUP_CONCAT(a.task_id) as task_ids from savor_integral_task_hotel as a left join savor_integral_task as task 
            on a.task_id=task.id where task.task_type=29 and a.create_time>='{$start_time}' and a.create_time<='{$end_time}' group by a.hotel_id";
        $res_release = M()->query($release_sql);
        $all_release_nums = array();
        foreach ($res_release as $v){
            $all_release_nums[$v['hotel_id']] = $v['num'];
        }
        $get_sql = "select count(DISTINCT a.task_id) as num,a.hotel_id,GROUP_CONCAT(a.task_id) as task_ids from savor_integral_task_user as a left join savor_integral_task as task 
            on a.task_id=task.id where task.task_type=29  and a.add_time>='{$start_time}' and a.add_time<='{$end_time}' group by a.hotel_id";
        $res_get = M()->query($get_sql);
        $all_get_nums = array();
        foreach ($res_get as $v){
            $all_get_nums[$v['hotel_id']] = $v['num'];
        }
        $finish_sql = "select count(id) as num,hotel_id from savor_smallapp_stockcheck where add_time>='{$start_time}' and add_time<='{$end_time}' group by hotel_id";
        $res_finish = M()->query($finish_sql);
        $all_finish_nums = array();
        foreach ($res_finish as $v){
            $all_finish_nums[$v['hotel_id']] = $v['num'];
        }
        $datalist = array();
        $test_hotel_ids = join(',',C('TEST_HOTEL'));
        $sql = "select area.region_name as area_name,hotel.id as hotel_id,hotel.name as hotel_name,ext.maintainer_id,sysuser.remark as maintainer_name from savor_hotel as hotel 
            left join savor_hotel_ext as ext on hotel.id=ext.hotel_id left join savor_area_info as area on hotel.area_id=area.id 
            left join savor_sysuser as sysuser on ext.maintainer_id=sysuser.id 
            where hotel.state=1 and hotel.flag=0 and ext.is_salehotel=1 and hotel.id not in ($test_hotel_ids) order by hotel.id asc ";
        $model = M();
        $res_hotel = $model->query($sql);
        foreach ($res_hotel as $v){
            $v['release_num'] = isset($all_release_nums[$v['hotel_id']])?$all_release_nums[$v['hotel_id']]:0;
            $v['get_num'] = isset($all_get_nums[$v['hotel_id']])?$all_get_nums[$v['hotel_id']]:0;
            $v['finish_num'] = isset($all_finish_nums[$v['hotel_id']])?$all_finish_nums[$v['hotel_id']]:0;
            $check_rate = $v['finish_num']/$v['release_num']>=1?1:$v['finish_num']/$v['release_num'];
            $v['check_rate'] = sprintf("%.2f",$check_rate);
            $datalist[]=$v;
        }

        $cell = array(
            array('area_name','城市'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('maintainer_name','维护人'),
            array('release_num','发布盘点任务数'),
            array('get_num','领取任务数'),
            array('finish_num','完成盘点次数'),
            array('check_rate','盘点率'),
        );
        $filename = '酒楼盘点任务数据';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}