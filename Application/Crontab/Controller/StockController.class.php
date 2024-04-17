<?php
namespace Crontab\Controller;
use Think\Controller;

class StockController extends Controller{

    public function expirerecycle(){
        $diff_time = time() - 86400*60;
        $where = array('type'=>7,'wo_status'=>2,'recycle_status'=>array('in','1,4'));
        $where['UNIX_TIMESTAMP(add_time)'] = array('elt',$diff_time);
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $m_stock_record->updateData($where,array('recycle_status'=>7));

        $now_time = date('Y-m-d H:i:s');
        echo "expirerecycle time:$now_time \r\n";
    }

    public function idcode(){
        $last_time = time() - 3600*8;
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $m_qrcode_content = new \Admin\Model\FinanceQrcodeContentModel();
        $m_idcode = new \Admin\Model\FinanceIdcodeModel();
        $field = 'idcode,max(id) as last_id';
        $where = array('dstatus'=>1,'add_time'=>array('egt',date('Y-m-d H:i:s',$last_time)));
        $res_data = $m_stock_record->getAllData($field,$where,'','idcode');
        foreach ($res_data as $v){
            $idcode = $v['idcode'];
            $qrcontent = decrypt_data($idcode);
            $qr_id = intval($qrcontent);
            $qr_info = $m_qrcode_content->getInfo(array('id'=>$qr_id));
            $pidcode = '';
            if($qr_info['parent_id']>0){
                $pidcode = encrypt_data($qr_info['parent_id']);
            }
            $last_id = $v['last_id'];
            $rwhere = array('a.id'=>$last_id);
            $fileds = 'a.stock_id,a.stock_detail_id,stock.hotel_id,hotel.area_id,a.goods_id,a.unit_id,a.idcode,
            unit.convert_type as amount,stock.io_type,a.type,a.add_time as update_time';
            $res_record = $m_stock_record->alias('a')
                ->field($fileds)
                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
                ->where($rwhere)
                ->find();
            $res_record['hotel_id'] = intval($res_record['hotel_id']);
            $res_record['area_id'] = intval($res_record['area_id']);
            $add_data = $res_record;
            $add_data['pidcode'] = $pidcode;

            $res_idcode = $m_idcode->getInfo(array('idcode'=>$idcode));
            if(!empty($res_idcode)){
                $m_idcode->updateData(array('id'=>$res_idcode['id']),$add_data);
            }else{
                $m_idcode->add($add_data);
            }
            echo "idcode:$idcode,type:{$add_data['type']} \r\n";

        }

    }
}