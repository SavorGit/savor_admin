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

//        $start_time = '2024-02-01 00:00:00';
//        $end_time = '2024-02-02 10:00:00';

        $m_sale = new \Admin\Model\FinanceSaleModel();
        $fileds = 'a.id as sale_id,a.add_time,record.wo_reason_type';
        $where = array('a.type'=>1,'record.wo_status'=>2,'record.wo_reason_type'=>array('in','1,2'));
        $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $where['a.push_u8_status13'] = 0;
        $where['a.hotel_id'] = array('not in',C('TEST_HOTEL'));

        $res_data = $m_sale->getSaleStockRecordList($fileds,$where,'a.id asc','');
        foreach ($res_data as $v){
            $sale_id = $v['sale_id'];
            $reason_type = $v['wo_reason_type'];
            $push_type = intval(80+$reason_type);
            sendSmallappTopicMessage($sale_id,$push_type);

            usleep(500000);

            echo "sale_id:$sale_id,wo_reason_type:$reason_type,add_time:{$v['add_time']} \r\n";
        }
        $now_time = date('Y-m-d H:i:s');
        echo "voucher end:$now_time \r\n";
    }

    public function salevoucher(){
        $now_time = date('Y-m-d H:i:s');
        echo "salevoucher start:$now_time \r\n";

        $last_time = time() - 3600;
        $start_time = date('Y-m-d 00:00:00',$last_time);
        $end_time = date('Y-m-d H:59:59',$last_time);

//        $start_time = '2024-01-01 00:00:00';
//        $end_time = '2024-01-31 23:59:59';

        $m_sale = new \Admin\Model\FinanceSaleModel();
        $fileds = 'id as sale_id,type,add_time';
        $where = array('type'=>array('in','1,4'),'ptype'=>1);
        $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $where['push_u8_status2'] = 0;
        $where['hotel_id'] = array('not in',C('TEST_HOTEL'));

        $res_data = $m_sale->getDataList($fileds,$where,'id asc');
        $map_push_type = array('1'=>89,'4'=>88);
        foreach ($res_data as $v){
            $sale_id = $v['sale_id'];
            $type = $v['type'];
            $push_type = $map_push_type[$type];

            sendSmallappTopicMessage($sale_id,$push_type);

            usleep(500000);

            echo "sale_id:$sale_id,type:$type,add_time:{$v['add_time']} \r\n";
        }
        $now_time = date('Y-m-d H:i:s');
        echo "salevoucher end:$now_time \r\n";
    }

}
