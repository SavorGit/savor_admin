<?php
namespace Dataexport\Controller;

class ReturnedmoneyController extends BaseController{
    
    public function datalist(){
        //set_time_limit(360);
        //ini_set("memory_limit","1024M");
        
        
        $start_date = I('start_date',date('Y-m-d',strtotime('-7 days')));
        $end_date = I('end_date',date('Y-m-d',strtotime('-1 day')));
        //echo $end_date;exit;
        $init_start_date = date('Y-m-d',strtotime('-3 months'));
        if($start_date<$init_start_date){
            exit('只支持导出最近三个月数据');
        }
        $start_date .= ' 00:00:00';
        $end_date   .= ' 23:59:59';
        $where = "  a.add_time>='".$start_date."' and a.add_time<='".$end_date."'";
        
        $sql = "select hotel.id hotel_id,sale.residenter_id,hotel.name hotel_name,user1.remark as sign_user,user2.remark residenter_user 
                from savor_finance_sale_payment_record a 
                left join savor_finance_sale sale on a.sale_id= sale.id

                left join savor_hotel      hotel on sale.hotel_id     = hotel.id
                left join savor_hotel_ext  ext   on hotel.id          = ext.hotel_id

                left join savor_sysuser    user1 on ext.signer_id     = user1.id
                left join savor_sysuser    user2 on sale.residenter_id = user2.id
                where $where group by sale.hotel_id,sale.residenter_id";
        
        
        $data = M()->query($sql);
        foreach($data as $key=>$v){
            $map  = '';
            $map .= $where;
            $map .= ' and sale.hotel_id='.$v['hotel_id'];
            $map .= ' and sale.residenter_id ='.$v['residenter_id'];
            $sql  = "select sum(a.pay_money) as total_money from savor_finance_sale_payment_record a
                     left join savor_finance_sale sale on a.sale_id=sale.id where ".$map;
            $ret = M()->query($sql);
            $data[$key]['total_money'] = $ret[0]['total_money'];
            
        }
        
        $cell = array(
            array('hotel_id','餐厅ID'),
            array('hotel_name','餐厅名称'),
            array('sign_user','签约人'),
            array('residenter_user','驻店人'),
            array('total_money','餐厅售卖回款额'),
            
        );
        $filename = '回款报表';
        $this->exportToExcel($cell,$data,$filename,1);
    }
}