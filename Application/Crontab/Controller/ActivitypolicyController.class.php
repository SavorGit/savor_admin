<?php
namespace Crontab\Controller;
use Think\Controller;

class ActivitypolicyController extends Controller{

    public function awardhoteldata(){
        $now_time = date('Y-m-d H:i:s');
        echo "awardhoteldata start:$now_time \r\n";

        $start_time = date('Y-m-01 00:00:00');
        $end_time = date('Y-m-d 23:59:59');
        $data_goods_ids = join(',',C('DATA_GOODS_IDS'));
        $test_hotel_ids = join(',',C('TEST_HOTEL'));
        $sql_record="select a.hotel_id,a.area_id,a.goods_id,sum(a.num) as sale_num from savor_finance_sale as a 
            left join savor_finance_stock_record as record on a.stock_record_id=record.id 
            where a.type=1 and record.wo_reason_type=1 and record.wo_status=2 and record.recycle_status=2 
            and a.goods_id not in ($data_goods_ids) and a.hotel_id not in ($test_hotel_ids)
            and record.add_time>='$start_time' and record.add_time<='$end_time'
            group by a.hotel_id,a.goods_id";
        $model = M();
        $record_list = $model->query($sql_record);
        $this->handle_award_hoteldata($record_list,date('Ym'));
        $now_time = date('Y-m-d H:i:s');
        echo "awardhoteldata end:$now_time \r\n";
    }

    public function outmonthawardhoteldata(){
        $now_time = date('Y-m-d H:i:s');
        echo "outmonthawardhoteldata start:$now_time \r\n";

        $start_time = date('Y-m-01 00:00:00',strtotime('-1 month'));
        $end_time = date('Y-m-t 23:59:59',strtotime('-1 month'));
        $pre_month = date('Ym',strtotime('-1 month'));
        $data_goods_ids = join(',',C('DATA_GOODS_IDS'));
        $test_hotel_ids = join(',',C('TEST_HOTEL'));
        $model = M();
        $sql = "select hotel_id from savor_finance_award_hoteldata where is_confirm=1 and static_date={$pre_month} order by id desc";
        $res_confirm_hotels = $model->query($sql);
        $confirm_hotels = array();
        foreach ($res_confirm_hotels as $v){
            $confirm_hotels[]=$v['hotel_id'];
        }
        $where_confirm_hotels = '';
        if(!empty($confirm_hotels)){
            $confirm_hotel_str = join(',',$confirm_hotels);
            $where_confirm_hotels = "and a.hotel_id not in ($confirm_hotel_str)";
        }
        $sql_record="select a.hotel_id,a.area_id,a.goods_id,sum(a.num) as sale_num from savor_finance_sale as a 
            left join savor_finance_stock_record as record on a.stock_record_id=record.id 
            where a.type=1 and record.wo_reason_type=1 and record.wo_status=2 and record.recycle_status=2 
            and a.goods_id not in ($data_goods_ids) and a.hotel_id not in ($test_hotel_ids)  
            and record.add_time>='$start_time' and record.add_time<='$end_time' {$where_confirm_hotels}
            group by a.hotel_id,a.goods_id";
        $model = M();
        $record_list = $model->query($sql_record);
        $this->handle_award_hoteldata($record_list,$pre_month);
        $now_time = date('Y-m-d H:i:s');
        echo "awardhoteldata end:$now_time \r\n";
    }

    private function handle_award_hoteldata($record_list,$static_date){
        $now_date = date('Y-m-d');
        $all_hotel_data = array();
        foreach ($record_list as $v){
            $all_hotel_data[$v['hotel_id']][]=$v;
        }
        $m_hotel = new \Admin\Model\HotelModel();
        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $m_award_hoteldata = new \Admin\Model\FinanceAwardHoteldataModel();
        $m_sale = new \Admin\Model\FinanceSaleModel();
        foreach ($all_hotel_data as $k=>$v){
            $hotel_id = $k;
            $res_merchant = $m_merchant->getInfo(array('hotel_id'=>$hotel_id,'status'=>1));
            $award_openid = !empty($res_merchant['award_openid'])?$res_merchant['award_openid']:'';

            $res_award_data = $m_award_hoteldata->getInfo(array('static_date'=>$static_date,'hotel_id'=>$hotel_id));
            if(!empty($res_award_data)){
                $award_data_id = $res_award_data['id'];
                if($res_award_data['is_confirm']==1){
                    echo "hotel_id:$hotel_id,static_date:$static_date,is_confirm:1 \r\n";
                    continue;
                }
            }else{
                $award_data_id = 0;
            }
            $num = $integral = 0;
            $step_num = $step_integral = 0;
            $dp_policy_id = $jt_policy_id = 0;
            $real_step_num = 0;
            $area_id = $v[0]['area_id'];

            //单瓶激励
            $sql_award_1 = "select a.policy_id from savor_finance_activity_policy_hotel as a left join savor_finance_activity_policy as ap on 
                a.policy_id=ap.id where a.area_id={$area_id} and a.hotel_id in ({$hotel_id},0) and ap.type=1 and ap.status=1 order by a.policy_id desc limit 0,1";
            $res_policy1 = $m_hotel->query($sql_award_1);

            //开瓶阶梯激励
            $sql_award_2 = "select a.policy_id,ap.integral_config from savor_finance_activity_policy_hotel as a left join savor_finance_activity_policy as ap on 
                a.policy_id=ap.id where a.area_id={$area_id} and a.hotel_id in ({$hotel_id},0) and ap.type=2 and ap.status=1 order by a.policy_id desc limit 0,1";
            $res_policy2 = $m_hotel->query($sql_award_2);
            foreach ($v as $hv){
                $goods_id = $hv['goods_id'];
                $sale_num = $hv['sale_num'];

                if(!empty($res_policy1[0]['policy_id'])){
                    $policy_id1 = $res_policy1[0]['policy_id'];
                    $dp_policy_id = $policy_id1;
                    $sql_policy_goods1 = "select goods_id,coefficient,integral from savor_finance_activity_policy_goods where policy_id={$policy_id1} and goods_id={$goods_id}";
                    $res_policy_goods1 = $m_hotel->query($sql_policy_goods1);
                    if(!empty($res_policy_goods1[0]['goods_id'])){
                        $goods_num = round($sale_num*$res_policy_goods1[0]['coefficient']);
                        $goods_integral = $goods_num*$res_policy_goods1[0]['integral'];

                        $num+=$goods_num;
                        $integral+=$goods_integral;

                        echo "hotel_id:$hotel_id,goods_id:$goods_id,calculate:goods_num=$sale_num*{$res_policy_goods1[0]['coefficient']} \r\n";
                        echo "hotel_id:$hotel_id,goods_id:$goods_id,calculate:goods_integral=$goods_num*{$res_policy_goods1[0]['integral']} \r\n";
                    }
                }

                if(!empty($res_policy2[0]['policy_id'])){
                    $policy_id2 = $res_policy2[0]['policy_id'];
                    $jt_policy_id = $policy_id2;
                    $sql_policy_goods2 = "select goods_id,coefficient,integral from savor_finance_activity_policy_goods where policy_id={$policy_id2} and goods_id={$goods_id}";
                    $res_policy_goods2 = $m_hotel->query($sql_policy_goods2);
                    if(!empty($res_policy_goods2[0]['goods_id'])){
                        $goods_step_num = $sale_num*$res_policy_goods2[0]['coefficient'];
                        $real_step_num+=$goods_step_num;
                        $step_num+=$goods_step_num;
                        echo "hotel_id:$hotel_id,goods_id:$goods_id,calculate:goods_step_num=$sale_num*{$res_policy_goods2[0]['coefficient']} \r\n";
                    }
                }
            }

            if(!empty($res_policy2[0]['policy_id'])){
                $real_step_num = round($real_step_num);
                $step_num = round($step_num);
                $integral_config = json_decode($res_policy2[0]['integral_config'],true);
                foreach ($integral_config as $icv){
                    if($step_num>=$icv['n']){
                        $step_integral+=$icv['i'];
                    }
                }
                echo "hotel_id:$hotel_id,calculate:goods_step_num=$step_num,step_integral=$step_integral \r\n";
            }

            if($num==0 && $step_num==0){
                echo "hotel_id:$hotel_id,num:$num,step_num:$step_num \r\n";
                continue;
            }
            $sql_bill_day = "select a.bill_days,h.hotel_id from savor_finance_contract as a 
                left join savor_finance_contract_hotel as h on a.id=h.contract_id
                where a.type=20 and a.status=1 and a.contract_etime>='$now_date' and h.hotel_id=$hotel_id order by a.id desc limit 0,1";
            $res_bill_day = $m_hotel->query($sql_bill_day);
            $bill_day = 0;
            if(!empty($res_bill_day[0]['bill_days'])){
                $bill_day = $res_bill_day[0]['bill_days'];
            }
            $qk_data = $m_sale->getqkmoney($hotel_id,0,1);
            $cqqk_money = $qk_data['cqqk_money'];
            $award_data = array('hotel_id'=>$hotel_id,'num'=>$num,'award_openid'=>$award_openid,'integral'=>$integral,'step_num'=>$step_num,'step_integral'=>$step_integral,'real_step_num'=>$real_step_num,
                'dp_policy_id'=>$dp_policy_id,'jt_policy_id'=>$jt_policy_id,'bill_day'=>$bill_day,'overdue_money'=>$cqqk_money,'status'=>3,'static_date'=>$static_date);
            if($award_data_id){
                $award_data['update_time'] = date('Y-m-d H:i:s');
                $m_award_hoteldata->updateData(array('id'=>$award_data_id),$award_data);
            }else{
                $hfield = 'hotel.name as hotel_name,hotel.area_id,area.region_name as area_name,ext.bd_name,ext.bdm_name';
                $res_hotel = $m_hotel->getHotelById($hfield,array('hotel.id'=>$hotel_id));
                $award_data['hotel_name'] = $res_hotel['hotel_name'];
                $award_data['area_id'] = $res_hotel['area_id'];
                $award_data['area_name'] = $res_hotel['area_name'];
                $award_data['bd_name'] = $res_hotel['bd_name'];
                $award_data['bdm_name'] = $res_hotel['bdm_name'];
                $m_award_hoteldata->add($award_data);
            }
        }

    }
}
