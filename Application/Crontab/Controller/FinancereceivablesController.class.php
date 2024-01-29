<?php
namespace Crontab\Controller;
use Think\Controller;

class FinancereceivablesController extends Controller{
    
    //根据每个餐厅 统计某一天为时间结点的应收账款数据
    public function hotelreceivables(){
        
        $orders = "a.id desc";
        $end_date = date('Y-m-d',strtotime('-1 day')) ;
        $where = array('a.type'=>1);
        $where['a.add_time'] = array(array('ELT',$end_date.' 23:59:59'));
        $where['a.hotel_id'] = array(array('not in',C('TEST_HOTEL')));
        
        $fields = "a.type,a.maintainer_id,a.hotel_id,hotel.name hotel_name,
                   area.id area_id,area.region_name,user.remark,
                   ar.region_name tg_region_name,sum(a.settlement_price-a.pay_money) as ys_money";
        $group  = "a.hotel_id";
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $m_sale_paymeng_record = new \Admin\Model\FinanceSalePaymentRecordModel();
        $list =   $m_sale->alias('a')
                         ->join('savor_hotel hotel on a.hotel_id = hotel.id','left')
                         ->join('savor_sysuser user on a.maintainer_id=user.id','left')
                         ->join('savor_area_info area on hotel.area_id= area.id','left')
                         ->join('savor_area_info ar on a.area_id= ar.id','left')
                         ->field($fields)
                         ->where($where)
                         ->order($orders)
                         ->group($group)
                         ->select();
        $m_finance_data_receivables = new \Admin\Model\FinanceDataReceivablesModel();
        
        foreach($list as $key=>$v){
            
            $map = [];
            /*$map['sale.hotel_id'] = $v['hotel_id'];
            $map['sale.add_time'] = array(array('ELT',$end_date.' 23:59:59'));
            $map['record.wo_status'] = 2;
            
            $payment_result = $m_sale_paymeng_record->alias('a')
                                                    ->join('savor_finance_sale sale on a.sale_id=sale.id','left')
                                                    ->join('savor_finance_stock_record record on sale.stock_record_id=record.id','left')
                                                    ->field('sum(a.pay_money) pay_money')
                                                    ->where($map)->find();
                              
            
            if(!empty($v['ys_money'])){
               $v['pay_money'] = $v['ys_money'];
           }else {
               $v['pay_money'] = 0 ;
           }*/
           
           if($v['hotel_id'] && !empty($v['hotel_name'])){
               $add_info = [];
               $add_info['area_id']          = intval($v['area_id']);
               $add_info['area_name']        = !empty($v['region_name']) ? $v['region_name'] : '';
               $add_info['hotel_name']       = !empty($v['hotel_name']) ? $v['hotel_name']   : '';
               $add_info['hotel_id']         = $v['hotel_id'];
               $add_info['business_man_id']  = intval($v['maintainer_id']);
               $add_info['business_man']     = !empty($v['remark']) ? $v['remark'] :'';
               $add_info['receivable_money'] = $v['ys_money'];
               $add_info['static_date']      = $end_date;
               
               $m_finance_data_receivables->addData($add_info);
               
           }
           
        }
        
        echo date('Y-m-d H:i:s').'OK'."\n";
    }
    
}