<?php
namespace Dataexport\Controller;

class ResidentController extends BaseController{

    public function datalist(){
        $static_month = date('Ym',strtotime('-1 month'));
        $m_static_resident = new \Admin\Model\StaticResidentModel();
        $field = 'area_id,area_name,residenter_id,residenter_name,sum(num) as num,avg(task_demand_finish_rate) as task_demand_finish_rate,
        avg(task_invitation_finish_rate) as task_invitation_finish_rate,sum(sale_money) as sale_money';
        $datalist = $m_static_resident->getAllData($field,array('static_month'=>$static_month),'','residenter_id');
        foreach ($datalist as $k=>$v){
            $datalist[$k]['task_demand_finish_rate'] = round($v['task_demand_finish_rate'],2);
            $datalist[$k]['task_invitation_finish_rate'] = round($v['task_invitation_finish_rate'],2);
        }
        $cell = array(
            array('area_name','城市'),
            array('residenter_name','驻店人'),
            array('num','销售量'),
            array('task_demand_finish_rate','点播任务完成率'),
            array('task_invitation_finish_rate','邀请函任务完成率'),
            array('sale_money','餐厅售卖回款额'),
        );
        $filename = '驻店数据统计';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

    public function selldata(){
        $area_id = I('get.area_id',1,'intval');
        $month = I('get.month',1,'intval');

        $cache_key = 'cronscript:resident:selldata'.$area_id.$month;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $res = $redis->get($cache_key);
        if(!empty($res)){
            if($res == 1){
                $this->success('数据正在生成中,请稍后访问下载');
            }else{
                //下载
                $file_name = $res;
                $file_path = SITE_TP_PATH.$file_name;
                $file_size = filesize($file_path);
                header("Content-type:application/octet-tream");
                header('Content-Transfer-Encoding: binary');
                header("Content-Length:$file_size");
                header("Content-Disposition:attachment;filename=".$file_name);
                @readfile($file_path);
            }
        }else{
            $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php dataexport/resident/selldatascript/area_id/$area_id/month/$month > /tmp/null &";
            system($shell);
            $redis->set($cache_key,1,3600);
            $this->success('数据正在生成中,请稍后访问下载');
        }
    }

    public function selldatascript(){
        $area_id = I('get.area_id',1,'intval');
        $month = I('get.month',1,'intval');
        $year = date('Y');
        $cache_file_key = 'cronscript:resident:selldata'.$area_id.$month;
        $startOfMonth = date('Y-m-01', strtotime($year . '-' . $month . '-01'));
        $endOfMonth = date('Y-m-t', strtotime($year . '-' . $month . '-01'));
        /*
        $startDate = new \DateTime($startOfMonth);
        $endDate = new \DateTime($endOfMonth);
        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($startDate, $interval, $endDate);
        $weeks = array();
        foreach ($period as $date) {
            $weekNumber = $date->format('W');
            if (!isset($weeks[$weekNumber])) {
                $weeks[$weekNumber] = array(
                    'start' => $date->format('Y-m-d'),
                    'end' => $date->format('Y-m-d'),
                );
            } else {
                $weeks[$weekNumber]['end'] = $date->format('Y-m-d');
            }
        }
        $weeks = array_values($weeks);
        $week_num = count($weeks);
        if($weeks[$week_num-1]['end']<$endOfMonth){
            $weeks[$week_num-1]['end'] = $endOfMonth;
        }
        */
//        $weeks = array();
//        $first_day_of_month = date('Y-m-01');
//        $first_day_of_month_week = date("W", strtotime($first_day_of_month));
//        $current_week = date("W") - $first_day_of_month_week + 1;
//        for ($i=1; $i<=$current_week; $i++) {
//            $date = date("Y-m-d", strtotime($first_day_of_month ." +".(7 * ($i - 1)) .'days'));
//            $week_start_date = date('Y-m-d', strtotime("last monday", strtotime($date)));
//            $week_end_date = date('Y-m-d', strtotime("next sunday", strtotime($date)));
//            $weeks[]=array('start'=>$week_start_date,'end'=>$week_end_date);
//        }

        $month_start_time = "$startOfMonth 00:00:00";
        $month_end_time = "$endOfMonth 23:59:59";
        $month = date('m',strtotime($month_start_time));
        $weeks = $this->getWeeksMonth($year,$month);
        $test_hotel_ids = join(',',C('TEST_HOTEL'));
        $sql ="select a.id as hotel_id,a.name as hotel_name,a.area_id,area.region_name as area_name,circle.name circle_name,
            ext.signer_id,ext.residenter_id,signer.remark as signer_name,residenter.remark as residenter_name
            from savor_hotel as a left join savor_hotel_ext as ext on a.id=ext.hotel_id
            left join savor_area_info as area on a.area_id=area.id
            left join savor_business_circle as circle on a.business_circle_id = circle.id
            left join savor_sysuser signer on ext.signer_id=signer.id
            left join savor_sysuser residenter on ext.residenter_id=residenter.id
            where a.state=1 and a.flag=0 and ext.is_salehotel=1 and a.area_id={$area_id} and a.id not in ($test_hotel_ids) order by a.id desc";
        $result = M()->query($sql);
        $m_room = new \Admin\Model\RoomModel();
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $m_sale_payment_record = new \Admin\Model\FinanceSalePaymentRecordModel();
        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $m_staff = new \Admin\Model\Integral\StaffModel();
        $m_hotelstaff_data = new \Admin\Model\Smallapp\StaticHotelstaffdataModel();
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(9);
        $cache_key = C('FINANCE_HOTELSTOCK');
        $datalist = array();
        foreach ($result as $v){
            $hotel_id = $v['hotel_id'];
            $circle_name = !empty($v['circle_name'])?$v['circle_name']:'';
            $res_room = $m_room->getRoomByCondition('count(room.id) as num',array('hotel.id'=>$v['hotel_id'],'room.state'=>1,'room.flag'=>0));
            $room_num = intval($res_room[0]['num']);

            $salewhere = array('a.hotel_id'=>$hotel_id,'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $salewhere['record.add_time'] = array(array('egt',$month_start_time),array('elt',$month_end_time));
            $res_stock_record = $m_sale->alias('a')
                ->field('count(a.id) as num')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->where($salewhere)
                ->select();
            $sale_num = intval($res_stock_record[0]['num']);

            $sale_week1_num=$sale_week2_num=$sale_week3_num=$sale_week4_num=$sale_week5_num=$sale_week6_num=0;
            foreach ($weeks as $wk=>$wday){
                $w_stime = "{$wday['start']} 00:00:00";
                $w_etime = "{$wday['end']} 23:59:59";
                $salewhere['record.add_time'] = array(array('egt',$w_stime),array('elt',$w_etime));
                $res_week_stock_record = $m_sale->alias('a')
                    ->field('count(a.id) as num')
                    ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                    ->where($salewhere)
                    ->select();
                if($wk==0)  $sale_week1_num=intval($res_week_stock_record[0]['num']);
                if($wk==1)  $sale_week2_num=intval($res_week_stock_record[0]['num']);
                if($wk==2)  $sale_week3_num=intval($res_week_stock_record[0]['num']);
                if($wk==3)  $sale_week4_num=intval($res_week_stock_record[0]['num']);
                if($wk==4)  $sale_week5_num=intval($res_week_stock_record[0]['num']);
                if($wk==5)  $sale_week6_num=intval($res_week_stock_record[0]['num']);
            }

            $sale_where = array('a.hotel_id'=>$hotel_id,'record.wo_reason_type'=>1,'a.add_time'=>array(array('egt',$month_start_time),array('elt',$month_end_time)));
            $sale_where['a.ptype'] = array('in','0,2');
            $res_sale_qk = $m_sale->getSaleStockRecordList('sum(a.settlement_price-a.pay_money) as money',$sale_where,'','');
            $qk_money = $res_sale_qk[0]['money']>0?$res_sale_qk[0]['money']:0;

            $sale_where['a.is_expire'] = 1;
            $res_sale_qk = $m_sale->getSaleStockRecordList('sum(a.settlement_price-a.pay_money) as money',$sale_where,'','');
            $cqqk_money = $res_sale_qk[0]['money']>0?$res_sale_qk[0]['money']:0;

            $res_merchant = $m_merchant->getMerchants('a.id,a.is_shareprofit',array('a.hotel_id'=>$hotel_id,'a.status'=>1),'');
            $is_shareprofit = 0;
            $is_shareprofit_str = '';
            $sale_people_num = 0;
            if(!empty($res_merchant[0]['id'])){
                $is_shareprofit = $res_merchant[0]['is_shareprofit'];
                if($is_shareprofit==1){
                    $is_shareprofit_str = '是';
                }else{
                    $is_shareprofit_str = '否';
                }
                $res_staff_num = $m_staff->getRow('count(*) as num',array('merchant_id'=>$res_merchant[0]['id'],'status'=>1));
                $sale_people_num = intval($res_staff_num['num']);
            }

            $staff_fields = 'sum(a.task_demand_finish_num) as task_demand_finish_num,sum(task_demand_operate_num) as task_demand_operate_num,
            sum(a.task_invitation_finish_num) as task_invitation_finish_num,sum(a.task_invitation_operate_num) as task_invitation_operate_num';
            $staff_where = array('a.hotel_id'=>$hotel_id,'a.static_date'=>array(array('egt',$startOfMonth),array('elt',$endOfMonth)));
            $res_task_data = $m_hotelstaff_data->getHotelDataList($staff_fields,$staff_where);
            $task_demand_finish_rate = sprintf("%.2f",$res_task_data[0]['task_demand_finish_num']/$res_task_data[0]['task_demand_operate_num']);
            $task_invitation_finish_rate = sprintf("%.2f",$res_task_data[0]['task_invitation_finish_num']/$res_task_data[0]['task_invitation_operate_num']);

            $sku_num = 0;
            $res_cache_stock = $redis->get($cache_key.":$hotel_id");
            if(!empty($res_cache_stock)){
                $cache_stock = json_decode($res_cache_stock,true);
                $sku_num = count($cache_stock['goods_ids']);
            }

            $datalist[]=array('hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],'area_name'=>$v['area_name'],
                'circle_name'=>$circle_name,'room_num'=>$room_num,'sale_num'=>$sale_num,'sale_week1_num'=>$sale_week1_num,
                'sale_week2_num'=>$sale_week2_num,'sale_week3_num'=>$sale_week3_num,'sale_week4_num'=>$sale_week4_num,
                'sale_week5_num'=>$sale_week5_num,'sale_week6_num'=>$sale_week6_num,
                'signer_name'=>$v['signer_name'],'residenter_name'=>$v['residenter_name'],'qk_money'=>$qk_money,'cqqk_money'=>$cqqk_money,'sale_people_num'=>$sale_people_num,
                'sku_num'=>$sku_num,'task_demand_finish_rate'=>$task_demand_finish_rate,'task_invitation_finish_rate'=>$task_invitation_finish_rate,
                'is_shareprofit_str'=>$is_shareprofit_str,
            );
        }
        $cell = array(
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('circle_name','商圈'),
            array('room_num','包间数'),
            array('sale_num','本月销量'),
            array('sale_week1_num','1周销量'),
            array('sale_week2_num','2周销量'),
            array('sale_week3_num','3周销量'),
            array('sale_week4_num','4周销量'),
            array('sale_week5_num','5周销量'),
            array('sale_week6_num','6周销量'),
            array('signer_name','签约人'),
            array('residenter_name','驻店人'),
            array('qk_money','总欠款'),
            array('cqqk_money','超期欠款'),
            array('sale_people_num','销售端人数'),
            array('sku_num','sku数'),
            array('task_demand_finish_rate','点播任务完成率'),
            array('task_invitation_finish_rate','邀请函任务完成率'),
            array('is_shareprofit_str','是否分润'),
        );

        $filename = '驻店人员工作表';
        $path = $this->exportToExcel($cell,$datalist,$filename,2);
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $redis->set($cache_file_key,$path,3600);
    }

    public function hotelselldata(){
        $month = I('month',0,'intval');
        $start_time = date('Y-m-01 00:00:00',strtotime($month));
        $end_time = date('Y-m-31 23:59:59',strtotime($month));
        $model = M();
        $sql = "select a.hotel_id,hotel.name as hotel_name,a.residenter_id,sysuser.remark as residenter_name,count(a.id) as sale_num from savor_finance_sale as a 
            left join savor_hotel as hotel on a.hotel_id=hotel.id
            left join savor_sysuser as sysuser on a.residenter_id=sysuser.id
            where a.add_time>='$start_time' and a.add_time<='$end_time'
            and a.type=1
            group by hotel_id,residenter_id order by hotel_id asc";
        $datalist = $model->query($sql);

        $cell = array(
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('residenter_name','驻店人'),
            array('sale_num','本月销量'),
        );

        $filename = '酒楼驻店人售卖表';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }


    public function getweeks(){
        // 指定年份和月份
        $year = 2023;
        $month = 9;
        $date = new \DateTime("$year-$month-01");
        $weeks = array();
        // 如果第一天不是周一，将第一个周的开始日期设置为第一个周一
        if ($date->format('N') != 1) {
            $date->modify("last monday of previous month");
        }
        // 循环获取每周的开始和结束日期
        while ($date->format('m') <= $month) {
            $startOfWeek = $date->format('Y-m-d');
            $date->modify('next sunday');
            $endOfWeek = $date->format('Y-m-d');
            if(date('m',strtotime($startOfWeek))!=$month && date('m',strtotime($endOfWeek))!=$month){
                break;
            }
            // 存储本周的开始和结束日期到数组中
            $weeks[] = array(
                'start' => $startOfWeek,
                'end' => $endOfWeek
            );
            // 移动到下一周的周一
            $date->modify('next monday');
        }
        print_r($weeks);
        exit;
    }

    private function getWeeksMonth($year,$month){
        $date = new \DateTime("$year-$month-01");
        $weeks = array();
        // 如果第一天不是周一，将第一个周的开始日期设置为第一个周一
        if ($date->format('N') != 1) {
            $date->modify("last monday of previous month");
        }
        // 循环获取每周的开始和结束日期
        while ($date->format('m') <= $month) {
            $startOfWeek = $date->format('Y-m-d');
            $date->modify('next sunday');
            $endOfWeek = $date->format('Y-m-d');
            if(date('m',strtotime($startOfWeek))!=$month && date('m',strtotime($endOfWeek))!=$month){
                break;
            }
            // 存储本周的开始和结束日期到数组中
            $weeks[] = array(
                'start' => $startOfWeek,
                'end' => $endOfWeek
            );
            // 移动到下一周的周一
            $date->modify('next monday');
        }
        return $weeks;
    }

    private function getWeeksInMonth($year, $month) {
        $weeks = array();
        $first_day = date("w", strtotime("$year-$month-01")); // 0 (Sun) to 6 (Sat)
        $last_day = date("t", strtotime("$year-$month-01")); // Number of days in the month
        $current_week = array();
        for ($day = 1; $day <= $last_day; $day++) {
            $date = "$year-$month-" . str_pad($day, 2, "0", STR_PAD_LEFT);
            $day_of_week = date("w", strtotime($date));
            if ($day_of_week == 1) {
                $current_week['start'] = $date;
            } elseif ($day_of_week == 0 || $day == $last_day) {
                // Sunday, end of the week or end of the month
                $current_week['end'] = $date;
                $weeks[] = $current_week;
            }
        }
        return $weeks;
    }



}