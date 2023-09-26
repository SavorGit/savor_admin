<?php
namespace Dataexport\Controller;

class ReturnedmoneyController extends BaseController{
    
    public function datalist(){
        $start_date = I('start_date',date('Y-m-d',strtotime('-7 days')));
        $end_date = I('end_date',date('Y-m-d',strtotime('-1 day')));
        $init_start_date = date('Y-m-d',strtotime('-3 months'));
        if($start_date<$init_start_date){
            exit('只支持导出最近三个月数据');
        }
        $start_time = "$start_date 00:00:00";
        $end_time = "$end_date 23:59:59";
        $where = "a.add_time>='{$start_time}' and a.add_time<='{$end_time}' and sale.type=1";
        $sql = "select hotel.id hotel_id,sale.residenter_id,hotel.name hotel_name,user1.remark as sign_user,
                user2.remark residenter_user
                from savor_finance_sale_payment_record a 
                left join savor_finance_sale sale on a.sale_id= sale.id
                left join savor_hotel      hotel on sale.hotel_id     = hotel.id
                left join savor_hotel_ext  ext   on hotel.id          = ext.hotel_id
                left join savor_sysuser    user1 on ext.signer_id     = user1.id
                left join savor_sysuser    user2 on sale.residenter_id = user2.id
                where $where group by sale.hotel_id,sale.residenter_id";
        $data = M()->query($sql);
        $datalist = array();
        foreach($data as $key=>$v){
            $money_where = $where;
            $money_where .= ' and sale.hotel_id='.$v['hotel_id'];
            $money_where .= ' and sale.residenter_id ='.$v['residenter_id'];
            $sql = "select sum(a.pay_money) as total_money from savor_finance_sale_payment_record a
                     left join savor_finance_sale sale on a.sale_id=sale.id where ".$money_where;
            $ret = M()->query($sql);
            $v['total_money'] = $ret[0]['total_money'];
            $v['type_str'] = '餐厅售卖';

            $datalist[]=$v;
        }
        $groupby_where = "a.add_time>='{$start_time}' and a.add_time<='{$end_time}' and sale.type=2";
        $sql = "select sale.maintainer_id,user2.remark residenter_user,sum(a.pay_money) as total_money
                from savor_finance_sale_payment_record a 
                left join savor_finance_sale sale on a.sale_id= sale.id
                left join savor_sysuser    user2 on sale.maintainer_id = user2.id
                where {$groupby_where} group by sale.maintainer_id";
        $data_groupby = M()->query($sql);
        foreach ($data_groupby as $v){
            $datalist[]=array('hotel_id'=>'','hotel_name'=>'','sign_user'=>'','type_str'=>'团购售卖',
                'residenter_user'=>$v['residenter_user'],'total_money'=>$v['total_money']);
        }
        $cell = array(
            array('type_str','类型'),
            array('hotel_id','餐厅ID'),
            array('hotel_name','餐厅名称'),
            array('sign_user','签约人'),
            array('residenter_user','驻店人'),
            array('total_money','餐厅售卖回款额'),
        );
        $filename = '回款报表';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}