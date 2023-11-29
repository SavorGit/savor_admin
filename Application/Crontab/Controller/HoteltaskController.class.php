<?php
namespace Crontab\Controller;
use Think\Controller;

class HoteltaskController extends Controller{
    
    public function updateHotelSaleInfo(){
        
        $now_month_start_time = date('Y-m-01 00:00:00');
        $now_month_end_time  = date('Y-m-d H:i:s');
        
        
        $last_month_start_time = date('Y-m-01 00:00:00', strtotime('last month'));
        $last_month_end_time   = date('Y-m-t 23:59:59', strtotime('last month'));
        
        /*echo $now_month_start_time."<br>";
        echo $now_month_end_time."<br>";
        echo $last_month_start_day."<br>";
        echo $last_month_end_day."<br>";
        exit;*/
        
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $fields = 'hotel_id,sale_hotel_in_time';
        
        $where = [];
        $where['is_salehotel'] = 1;
        $hotel_list = $m_hotel_ext->field($fields)->where($where)->select();
        
        foreach($hotel_list as $key=>$v){
            
            $data = [];
            $map  = [];
            $map['hotel_id']      = $v['hotel_id'];
            //超期欠款
            $sql = 'select sum(settlement_price-pay_money) as sale_cqmoney 
                    from savor_finance_sale where hotel_id='.$v['hotel_id'].' and ptype in (0,2)  and is_expire=1 ';
            $ret = M()->query($sql);
            
            $data['sale_cqmoney'] = $ret[0]['sale_cqmoney'];
            $m_hotel_ext->where($map)->save($data);
            
            //应收账款
            $data = [];
            $sql = 'select sum(settlement_price-pay_money) as sale_ysmoney 
                    from savor_finance_sale where hotel_id='.$v['hotel_id'].' and ptype in (0,2)';
            $ret = M()->query($sql);
            $data['sale_ysmoney'] = $ret[0]['sale_ysmoney'];
            $m_hotel_ext->where($map)->save($data);
            
            //未动销时间
            $data = [];
            $sql = 'select add_time as sale_last_time 
                    from savor_finance_sale where hotel_id='.$v['hotel_id'].' order by id desc limit 0,1';
            
            $ret = M()->query($sql);
            if(!empty($ret)){//如果上次售酒时间不为空   
                $data['sale_last_time'] = $ret[0]['sale_last_time'];
                $m_hotel_ext->where($map)->save($data);
                
                //未动销时间 = 当前时间-上次售酒时间
                $diff_time = time() -  strtotime($ret[0]['sale_last_time']);
                $sale_not_day = floor($diff_time / 86400);
                
                
            }else {//如果上次售酒时间为空
                if($v['sale_hotel_in_time']!='0000-00-00 00:00:00'){ //当上次售酒时间为空时 当前时间-新进酒时间
                    $diff_time = time() -  strtotime($v['sale_hotel_in_time']);
                    $sale_not_day = floor($diff_time / 86400);
                }else {//当新进酒时间为空时   未动销时间为：0
                    $sale_not_day = 0;
                }
                
            }
            $data = [];
            $data['sale_not_day'] = $sale_not_day;
            $m_hotel_ext->where($map)->save($data);
            
            //未动销天数
            
            
            
            
            //动销下滑
            $data = [];
            $sql = "select sum(num) as sale_num 
                    from savor_finance_sale 
                    where hotel_id=".$v['hotel_id']." and add_time>='".$last_month_start_time."' and add_time<='".$last_month_end_time."'";
            $ret = M()->query($sql);
            $last_month_sale_num = intval($ret[0]['sale_num']);
            
            $sql = "select sum(num) as sale_num
                    from savor_finance_sale
                    where hotel_id=".$v['hotel_id']." and add_time>='".$now_month_start_time."' and add_time<='".$now_month_end_time."'";
            $ret = M()->query($sql);
            $now_month_sale_num = intval($ret[0]['sale_num']);
            
            
            if($last_month_sale_num==0 && $now_month_sale_num>0){
                $data['sale_decline_percent'] = 9999.00;
            }else if($last_month_sale_num==0 && $now_month_sale_num==0){
                $data['sale_decline_percent'] = 1.00;
            }else if($last_month_sale_num>0 && $now_month_sale_num==0){
                $data['sale_decline_percent'] = 0.00;
            }
            else {
                $data['sale_decline_percent'] = sprintf("%.2f", $now_month_sale_num / $last_month_sale_num);
                
            }
            $m_hotel_ext->where($map)->save($data);
        }
        
        echo date('Y-m-d H:i:s').'OK'."\n";
        
    }
}