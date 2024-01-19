<?php
namespace Crontab\Controller;
use Think\Controller;

class U8pushvoucherController extends Controller{

    public function voucher(){
        $now_time = date('Y-m-d H:i:s');
        echo "voucher start:$now_time \r\n";

        $last_time = time() - 3600;
        $start_time = date('Y-m-d 00:00:00',$last_time);
        $end_time = date('Y-m-d H:59:59',$last_time);

        $m_sale = new \Admin\Model\FinanceSaleModel();
        $fileds = 'a.id as sale_id,a.add_time,record.wo_reason_type';
        $where = array('a.type'=>1,'record.wo_status'=>2,'record.wo_reason_type'=>array('in','1,2'));
        $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $where['a.push_u8_status13'] = 0;
        $res_data = $m_sale->getSaleStockRecordList($fileds,$where,'a.id asc','');
        foreach ($res_data as $v){
            $sale_id = $v['sale_id'];
            $reason_type = $v['wo_reason_type'];
            $push_type = intval(80+$reason_type);
            sendSmallappTopicMessage($sale_id,$push_type);

            usleep(500000);

            echo "sale_id:$sale_id,wo_reason_type:$reason_type \r\n";
        }
        $now_time = date('Y-m-d H:i:s');
        echo "voucher end:$now_time \r\n";
    }

}
