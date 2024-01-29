<?php
namespace Crontab\Controller;
use Think\Controller;

class FinanceaccountageController extends Controller{
    private $days_range_arr = array(
        array('min'=>1,'max'=>7,'name'=>'accountage_1_7','money'=>0),
        array('min'=>8,'max'=>15,'name'=>'accountage_8_15','money'=>0),
        array('min'=>16,'max'=>30,'name'=>'accountage_16_30','money'=>0),
        array('min'=>31,'max'=>60,'name'=>'accountage_31_60','money'=>0),
        array('min'=>61,'max'=>90,'name'=>'accountage_61_90','money'=>0),
        array('min'=>91,'max'=>180,'name'=>'accountage_91_180','money'=>0),
        array('min'=>181,'max'=>9999999,'name'=>'accountage_181','money'=>0),
    );
    private $bill_days_range_arr = array(
        array('min'=>1,'max'=>7,'name'=>'overdue_1_7','money'=>0),
        array('min'=>8,'max'=>15,'name'=>'overdue_8_15','money'=>0),
        array('min'=>16,'max'=>30,'name'=>'overdue_16_30','money'=>0),
        array('min'=>31,'max'=>60,'name'=>'overdue_31_60','money'=>0),
        array('min'=>61,'max'=>90,'name'=>'overdue_61_90','money'=>0),
        array('min'=>91,'max'=>180,'name'=>'overdue_91_180','money'=>0),
        array('min'=>181,'max'=>9999999,'name'=>'overdue_181','money'=>0),
        
    );
    private $bill_days = 7;
    public function accountage(){
        
        $end_date   = date('Y-m-d',strtotime('-1 day'));
        
        $orders = "a.id desc";
        $where = [];
        $where['a.add_time'] = array(array('ELT',$end_date.' 23:59:59'));
        
        
        $fields = "a.type,a.hotel_id,hotel.name hotel_name,area.id area_id,area.region_name,
                   user.id user_id,user.remark user_name,ar.id tg_area_id,ar.region_name tg_region_name";
        $group  = "a.hotel_id";
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $list =   $m_sale->alias('a')
                         ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                         ->join('savor_sysuser user on a.maintainer_id=user.id','left')
                         ->join('savor_area_info area on hotel.area_id= area.id','left')
                         ->join('savor_area_info ar on a.area_id=ar.id','left')
                         ->field($fields)
                         ->where($where)
                         ->order($orders)
                         ->group($group)
                         ->select();
        $m_contract_hotel = new \Admin\Model\ContracthotelModel();
        $m_accountage     = new \Admin\Model\FinanceDataAccountageModel();
        
        foreach($list as $key=>$v){
            //账期
            $map = [] ;
            $map['a.hotel_id'] = $v['hotel_id'];
            $map['contract.type'] = 20;
            
            $contract_info = $m_contract_hotel->alias('a')
                                              ->join('savor_finance_contract contract on a.contract_id=contract.id','left')
                                              ->field('contract.bill_days')->where($map)->find();
            
            
            $bill_days =    !empty($contract_info['bill_days']) ? $contract_info['bill_days'] : $this->bill_days;
            $list[$key]['bill_days'] = $bill_days;
            
            $fields = 'a.settlement_price,a.status,a.pay_time,a.add_time';
            $map = [];
            $map['a.add_time'] = array(array('ELT',$end_date.' 23:59:59'));
            $map['a.hotel_id'] = $v['hotel_id'];
            
            $rts = $m_sale->alias('a')
                          ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                          ->join('savor_sysuser user on a.maintainer_id=user.id','left')
                          ->join('savor_area_info area on hotel.area_id= area.id','left')
                          ->field($fields)
                          ->where($map)
                          ->order($orders)
                          ->select();
            
            $days_range_arr = $this->days_range_arr;
            $bill_days_range_arr = $this->bill_days_range_arr;
            //print_r($days_range_arr);exit;
            //print_r($rts);exit;
            foreach($rts as $kk=>$vv){
                if($vv['status']==2){
                    continue;
                }
                $diff_day = ceil((time() - strtotime($vv['add_time'])) / 86400);
                
                foreach($days_range_arr as $dk=>$dv){
                    if($diff_day>=$dv['min'] && $diff_day<=$dv['max']){
                        $days_range_arr[$dk]['money'] +=$vv['settlement_price'];
                        break;
                    }
                    
                }
                if($diff_day>$bill_days){
                    $bill_diff_day = $diff_day - $bill_days;
                    
                    foreach($bill_days_range_arr as $dk=>$dv){
                        if($bill_diff_day>=$dv['min'] && $bill_diff_day<=$dv['max']){
                            
                            $bill_days_range_arr[$dk]['money'] += $vv['settlement_price'];
                            break;
                        }
                        
                    }
                }
            }
            
            foreach($days_range_arr as $dk=>$dv){
                $list[$key][$days_range_arr[$dk]['name']] = $days_range_arr[$dk]['money'];
            }
            foreach($bill_days_range_arr as $dk=>$dv){
                $list[$key][$bill_days_range_arr[$dk]['name']] = $bill_days_range_arr[$dk]['day'];
            }
            if($v['type']==1){
                $list[$key]['area_id']     = !empty($v['area_id']) ? $v['area_id'] : 0;
                $list[$key]['region_name'] = !empty($v['region_name']) ? $v['region_name'] : '' ;
            }else {
                $list[$key]['area_id']  = !empty($v['tg_area_id']) ? $v['tg_area_id'] :0 ;
                $list[$key]['region_name'] = !empty($v['tg_region_name']) ?$v['tg_region_name']:'';
            }
            $add_info = [];
            $add_info['area_id']             = $list[$key]['area_id'];
            $add_info['area_name']           = $list[$key]['region_name'];
            $add_info['hotel_id']            = !empty($list[$key]['hotel_id']) ? $list[$key]['hotel_id'] :0 ;
            $add_info['hotel_name']          = !empty($list[$key]['hotel_name']) ? $list[$key]['hotel_name'] :'';
            
            $add_info['business_man_id']     = !empty($list[$key]['user_id']) ? $list[$key]['user_id'] : 0;
            $add_info['business_man']        = !empty($list[$key]['user_name']) ? $list[$key]['user_name'] :'';
            $add_info['bill_days']   = $list[$key]['bill_days'];
            foreach($days_range_arr as $dk=>$dv){
                $add_info[$days_range_arr[$dk]['name']] = $days_range_arr[$dk]['money'];
            }
            foreach($bill_days_range_arr as $dk=>$dv){
                $add_info[$bill_days_range_arr[$dk]['name']] = $bill_days_range_arr[$dk]['money'];
            }
            $add_info['static_date'] = $end_date;
            $m_accountage->addData($add_info);
            
        }
        echo date('Y-m-d H:i:s')." ok \n";
    }
}