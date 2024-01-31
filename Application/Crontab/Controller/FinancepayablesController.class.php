<?php
namespace Crontab\Controller;
use Think\Controller;

class FinancepayablesController extends Controller{
    
    //应付账款汇总表
    public function payablesSummary(){
        
        
        $end_date =  date('Y-m-d',strtotime('-1 day'));
        $now_end_time = date('Y-m-d',strtotime('-1 day'));
        
        $area_arr  = $supplier_arr = $goods_arr = $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        
        $m_supplier = new \Admin\Model\FinanceSupplierModel();
        $res_supplier = $m_supplier->getAll('id,name',array('status'=>1),0,1000,'id asc');
        /*$res_supplier = $m_supplier->alias('a')
                                   ->join('savor_area_info area on a.city_id=area.id','left')
                                   ->field('a.id,a.name,area.region_name')
                                   ->select();*/
        foreach ($res_supplier as $v){
            $supplier_arr[$v['id']]=$v;
        }
        
        $m_goods = new \Admin\Model\FinanceGoodsModel();
        $res_goods = $m_goods->field('id ,name ')->select();
        foreach ($res_goods as $v){
            $goods_arr[$v['id']]=$v;
        }
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        
        
        $fields = 'p.supplier_id';
        $where = [];
        $where['a.type']  = 10;
        $where['a.io_type'] = 11;
        $where['a.io_date'] = array(array('elt',$now_end_time));
        
        $m_stock = new \Admin\Model\FinanceStockModel();
        $m_stock_detail = new \Admin\Model\FinanceStockDetailModel();
        $m_stock_reord  = new \Admin\Model\FinanceStockRecordModel();
        $m_stock_payment_record = new \Admin\Model\FinanceStockPaymentRecordModel();
        $m_payables  =new \Admin\Model\FinanceDataPayablesModel();
       
        $supplier_list = $m_stock->alias('a')
                          ->join('savor_finance_purchase p on a.purchase_id=p.id','left')
                          
                          ->field($fields)
                          ->where($where)
                          ->group('p.supplier_id')
                          ->select();
        
        //echo $m_stock->getLastSql();exit;
        //print_r($supplier_list);exit;
                          
                          
                          
        foreach($supplier_list as $key=>$v){
            if(empty($v['supplier_id'])) {
                unset($supplier_list[$key]);
                continue;
            }
            $supplier_list[$key]['supplier_name'] = $supplier_arr[$v['supplier_id']]['name'];
            //$supplier_list[$key]['area_name']     = $supplier_arr[$v['supplier_id']]['region_name'];
            $fields = 'a.id stock_id';
            $where['p.supplier_id'] = $v['supplier_id'];
            $stock_list = $m_stock->alias('a')
                    ->join('savor_finance_purchase p on a.purchase_id=p.id','left')
                    ->field($fields)
                    ->where($where)
                    ->select();
            $stock_str = '';
            $space = '';
            if(!empty($stock_list)){
                foreach($stock_list as $sk=>$sv){
                    $stock_str .= $space .$sv['stock_id'];
                    $space      = ',';
                }
                $map = [];
                $map['stock_id'] = array('in',$stock_str);
                $map['status']   = 1;
                $goods_list = $m_stock_detail->field('goods_id')->where($map)->group('goods_id')->select();
                foreach($goods_list as $gk=>$gv){
                    $goods_list[$gk]['goods_name'] = $goods_arr[$gv['goods_id']]['name'] ;
                }
                $supplier_list[$key]['goods_list'] = $goods_list;
            }
            
        }
        //print_r($supplier_list);exit;
        foreach($supplier_list as $key=>$v){
            
            foreach($v['goods_list'] as $gk=>$gv){
                
                //SELECT sum(total_amount) as total_amount,sum(total_fee) as total_fee 
                //FROM `savor_finance_stock_record` WHERE `stock_id` = '60006947' AND `type` = 1 LIMIT 1
                
                
                foreach($res_area as $ak=>$av){
                    $add_data = [];
                    $map = [];
                    $map['a.goods_id'] = $gv['goods_id'];
                    //$map['a.dstatus'] =1;
                    $map['a.type']  = 1;
                    
                    
                    $map['stock.type']    = 10;
                    $map['stock.io_type'] = 11;
                    $map['stock.area_id'] = $av['id'];
                    $map['p.supplier_id'] = $v['supplier_id'];
                    $map['stock.io_date'] = array(array('elt',$now_end_time));
                    
                    $now_total_fee = 0;
                    $res_stock_record = $m_stock_reord->alias('a')
                                  ->field('sum(a.total_fee) as total_fee')
                                  ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                  ->join('savor_finance_purchase p on stock.purchase_id=p.id','left')
                                  ->where($map)
                                  ->find();
                    //echo $m_stock_reord->getLastSql();
                    if(!empty($res_stock_record['total_fee'])){
                        $now_total_fee = intval($res_stock_record['total_fee']);
                    }
                    
                    $have_pay_money = 0;
                    $res_stock_list = $m_stock_reord->alias('a')
                                                    ->field('stock.id stock_id')
                                                    ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                                                    ->join('savor_finance_purchase p on stock.purchase_id=p.id','left')
                                                    ->where($map)
                                                    ->group('stock.id')
                                                    ->select();
                    if(!empty($res_stock_list)){
                        $stock_str = '';
                        $space     = '';
                        foreach($res_stock_list as $stk=>$stv){
                            $stock_str .= $space . $stv['stock_id'];
                            $space = ',';
                        }
                        $smap = [];
                        $smap['stock_id'] = array('in',$stock_str);
                        $ret = $m_stock_payment_record->field('sum(pay_money) pay_money')->where($smap)->find();
                        
                        $have_pay_money = !empty($ret['pay_money']) ? $ret['pay_money'] : 0;
                    }
                   
                    
                    
                    $add_data['supplier_id'] = $v['supplier_id'];
                    $add_data['supplier']    = $v['supplier_name'];
                    $add_data['area_id']     = $av['id'];
                    $add_data['area_name']   = $av['region_name'];
                    $add_data['goods_id']    = $gv['goods_id'];
                    $add_data['goods_name']  = $gv['goods_name'];
                    $add_data['purchase_total_money'] = $now_total_fee;
                    $add_data['have_pay_money'] = $have_pay_money;
                    
                    if(!empty($add_data['purchase_total_money'])){
                        $add_data['not_pay_money']  = $add_data['purchase_total_money'] - $add_data['have_pay_money'];
                    }else {
                        $add_data['not_pay_money']  = 0;
                    }
                    $add_data['static_date'] = $end_date;
                    //print_r($add_data);exit;
                    $m_payables->addData($add_data);
                }
            }
        }
        echo date('Y-m-d H:i:s').'OK'."\n";
    }  
}