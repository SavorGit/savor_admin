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

    public function salemoney(){
        $start_time = "2023-07-01 00:00:00";
        $end_time = "2023-07-31 23:59:59";
        $all_citys = array('1'=>'北京','9'=>'上海','236'=>'广州','246'=>'深圳','248'=>'佛山');

        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $fields = 'a.manage_city,user.id as residenter_id,user.remark as residenter_name';
        $where = array('a.state'=>1,'user.status'=>1);
        $res_opusers = $m_opuser_role->getAllRole($fields,$where,'a.id desc');
        $datalist = array();
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $m_sale_payment_record = new \Admin\Model\FinanceSalePaymentRecordModel();
        foreach ($res_opusers as $v){
            $area_id = $v['manage_city'];

            $salewhere = array('record.wo_reason_type'=>1,'record.wo_status'=>2,'ext.residenter_id'=>$v['residenter_id'],
                'a.ptype'=>1);
            $salewhere['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $res_avg_paydaydata = $m_sale->alias('a')
                ->field('record.update_time as wo_time,a.idcode,a.id as sale_id,a.settlement_price')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
                ->where($salewhere)
                ->select();

            $hk_money = 0;
            $hk_day_money = 0;
            foreach ($res_avg_paydaydata as $apd){
                $hk_money +=$apd['settlement_price'];
                $res_payrecord = $m_sale_payment_record->getInfo(array('sale_id'=>$apd['sale_id']));
                $pay_day = round((strtotime($res_payrecord['add_time']) - strtotime($apd['wo_time']))/86400,2);
                $pay_money = $pay_day*$apd['settlement_price'];
                $hk_day_money+=$pay_money;
            }
            $pjhk_day = round($hk_day_money/$hk_money,2);

            $yszlwhere = array('record.wo_reason_type'=>1,'record.wo_status'=>2,'ext.residenter_id'=>$v['residenter_id'],
                'a.ptype'=>0);
            $yszlwhere['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $res_yszldata = $m_sale->alias('a')
                ->field('record.update_time as wo_time,a.idcode,a.id as sale_id,a.settlement_price')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
                ->where($yszlwhere)
                ->select();
            $ar_day_money=$ar_money=0;
            $fx_ar_day_money = 0;
            foreach ($res_yszldata as $yszl){
                $ar_day = round((time() - strtotime($yszl['wo_time']))/86400,2);

                $ar_day_money_tmp = $ar_day*$yszl['settlement_price'];
                $ar_day_money+=$ar_day_money_tmp;
                $ar_money+=$yszl['settlement_price'];

                if($ar_day<=15){
                    $rate = 1;
                }elseif($ar_day>15 && $ar_day_money<=30){
                    $rate = 1.5;
                }else{
                    $rate = 2;
                }
                $fx_ar_day_money+=$ar_day_money_tmp*$rate;

            }
            $yszl = round($ar_day_money/$ar_money,2);
            $fx_yszl = round($fx_ar_day_money/$ar_money,2);

            $datalist[]=array('area_id'=>$area_id,'area_name'=>$all_citys[$area_id],'residenter_id'=>$v['residenter_id'],'residenter_name'=>$v['residenter_name'],
                'pjhk_day'=>$pjhk_day,'ar_money'=>$ar_money,'yszl'=>$yszl,'fx_yszl'=>$fx_yszl
            );
        }

        $cell = array(
            array('area_name','城市'),
            array('residenter_name','驻店人'),
            array('pjhk_day','平均回款天数'),
            array('ar_money','应收款总额'),
            array('yszl','平均账龄'),
            array('fx_yszl','有风险的平均账龄'),
        );
        $filename = '驻店回款账龄表';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

    public function hotelsalemoney(){
        $start_time = "2023-07-01 00:00:00";
        $end_time = "2023-07-31 23:59:59";
        $all_citys = array('1'=>'北京','9'=>'上海','236'=>'广州','246'=>'深圳','248'=>'佛山');

        $m_hotel = new \Admin\Model\HotelModel();
        $fields = 'a.id as hotel_id,a.name as hotel_name,a.area_id';
        $where = array('a.state'=>1,'a.flag'=>0,'ext.is_salehotel'=>1);
        $res_hotels = $m_hotel->getHotels($fields,$where);

        $datalist = array();
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $m_sale_payment_record = new \Admin\Model\FinanceSalePaymentRecordModel();
        foreach ($res_hotels as $v){
            $area_id = $v['area_id'];

            $salewhere = array('record.wo_reason_type'=>1,'record.wo_status'=>2,'a.hotel_id'=>$v['hotel_id'],
                'a.ptype'=>1);
            $salewhere['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $res_avg_paydaydata = $m_sale->alias('a')
                ->field('record.update_time as wo_time,a.idcode,a.id as sale_id,a.settlement_price')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
                ->where($salewhere)
                ->select();

            $hk_money = 0;
            $hk_day_money = 0;
            foreach ($res_avg_paydaydata as $apd){
                $hk_money +=$apd['settlement_price'];
                $res_payrecord = $m_sale_payment_record->getInfo(array('sale_id'=>$apd['sale_id']));
                $pay_day = round((strtotime($res_payrecord['add_time']) - strtotime($apd['wo_time']))/86400,2);
                $pay_money = $pay_day*$apd['settlement_price'];
                $hk_day_money+=$pay_money;
            }
            $pjhk_day = round($hk_day_money/$hk_money,2);

            $yszlwhere = array('record.wo_reason_type'=>1,'record.wo_status'=>2,'a.hotel_id'=>$v['hotel_id'],
                'a.ptype'=>0);
            $yszlwhere['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
            $res_yszldata = $m_sale->alias('a')
                ->field('record.update_time as wo_time,a.idcode,a.id as sale_id,a.settlement_price')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
                ->where($yszlwhere)
                ->select();
            $ar_day_money=$ar_money=0;
            $fx_ar_day_money = 0;
            foreach ($res_yszldata as $yszl){
                $ar_day = round((time() - strtotime($yszl['wo_time']))/86400,2);

                $ar_day_money_tmp = $ar_day*$yszl['settlement_price'];
                $ar_day_money+=$ar_day_money_tmp;
                $ar_money+=$yszl['settlement_price'];

                if($ar_day<=15){
                    $rate = 1;
                }elseif($ar_day>15 && $ar_day_money<=30){
                    $rate = 1.5;
                }else{
                    $rate = 2;
                }
                $fx_ar_day_money+=$ar_day_money_tmp*$rate;

            }
            $yszl = round($ar_day_money/$ar_money,2);
            $fx_yszl = round($fx_ar_day_money/$ar_money,2);

            $datalist[]=array('area_id'=>$area_id,'area_name'=>$all_citys[$area_id],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                'pjhk_day'=>$pjhk_day,'ar_money'=>$ar_money,'yszl'=>$yszl,'fx_yszl'=>$fx_yszl
            );
        }

        $cell = array(
            array('area_name','城市'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('pjhk_day','平均回款天数'),
            array('ar_money','应收款总额'),
            array('yszl','平均账龄'),
            array('fx_yszl','有风险的平均账龄'),
        );
        $filename = '酒楼回款账龄表';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

}