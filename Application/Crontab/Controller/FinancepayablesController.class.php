<?php
namespace Crontab\Controller;
use Think\Controller;

class FinancepayablesController extends Controller{
    
    //应付账款汇总表
    public function payablesSummary(){
        $fields = 's.id stock_id,a.id stock_detail_id,s.area_id,goods.supplier_id,
                   goods.name goods_name,goods.id goods_id';
        
        $end_date =  date('Y-m-d',strtotime('-1 day'));
        $now_end_time = date('Y-m-d 23:59:59',strtotime('-1 day'));
        
        $where = [];
        $where['s.type']  = 10;
        $where['s.io_type'] = 11;
        $where['a.status'] =1;
        
        $where['s.io_date'] = array(array('elt',$now_end_time));
        
        $m_stock = new \Admin\Model\FinanceStockDetailModel();
        $res_list = $m_stock->alias('a')
                            ->join('savor_finance_stock s on a.stock_id=s.id','left')
                            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
                            ->join('savor_finance_purchase p on s.purchase_id=p.id','left')
                            ->field($fields)
                            ->where($where)
                            ->order('a.id desc')
                            ->select();
                           
        //print_r($res_list);exit;   
        
        $area_arr = $department_list = $supplier_arr = $departmentuser_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }
        
        
        $m_supplier = new \Admin\Model\FinanceSupplierModel();
        $res_supplier = $m_supplier->getAll('id,name',array('status'=>1),0,1000,'id asc');
        foreach ($res_supplier as $v){
            $supplier_arr[$v['id']]=$v;
        }
        //print_r($res_list);exit; 
        $m_stock_reord = new \Admin\Model\FinanceStockRecordModel();
        $m_stock_payment_record = new \Admin\Model\FinanceStockPaymentRecordModel();
        $m_payables  =new \Admin\Model\FinanceDataPayablesModel();
        foreach($res_list as $key=>$v){
            $add_info = [] ;
            $add_info['supplier_id'] = !empty($v['supplier_id']) ? $v['supplier_id'] : 0; //供应商id
            if(!empty($add_info['supplier_id'])){
                $add_info['supplier'] = $supplier_arr[$v['supplier_id']]['name'];             //供应商名称
            }else {
                $add_info['supplier'] = '';             //供应商名称
            }
            
            
            $add_info['area_id']   = $v['area_id'];                                       //区域id
            $add_info['area_name'] = $area_arr[$v['area_id']]['region_name'];             //区域名称
            
            $add_info['goods_id']  = !empty($v['goods_id']) ? $v['goods_id'] :0 ;         //商品id
            $add_info['goods_name']= !empty($v['goods_name']) ? $v['goods_name'] : '' ;   //商品名称
            
            $fields = 'sum(price) total_price ';
            $map = [];
            $map = array('stock_detail_id'=>$v['stock_detail_id']);
            
            $ret = $m_stock_reord->field($fields)
                                 ->where($map)
                                 ->find();
            
                                 $add_info['purchase_total_money'] = !empty($ret['total_price']) ? $ret['total_price']:0;
            
            
            $ret = $m_stock_payment_record->field('sum(pay_money) pay_money')->where(array('stock_id'=>$v['stock_id']))->find();
            
            $add_info['have_pay_money'] = !empty($ret['pay_money']) ? $ret['pay_money'] : 0;
            
            if(!empty($add_info['purchase_total_money'])){
                $add_info['not_pay_money']  = $add_info['purchase_total_money'] - $add_info['have_pay_money'];
            }else {
                $add_info['not_pay_money']  = 0;
            }
            $add_info['static_date'] = $end_date;
            
            $m_payables->addData($add_info);
              
        }
        echo date('Y-m-d H:i:s').'OK'."\n";
        
    }
    
}