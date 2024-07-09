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
        $now_time = date('Y-m-d H:i:s');
        echo "idcode start:$now_time \r\n";

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
            echo "idcode:$idcode,type:{$add_data['type']}   ";

        }
        $now_time = date('Y-m-d H:i:s');
        echo "idcode end:$now_time \r\n";
    }

    public function tmpidcode(){
        $m_qrcode_content = new \Admin\Model\FinanceQrcodeContentModel();
        $sql_1 = "SELECT a.idcode,a.price,a.avg_price,a.goods_id,a.add_time,goods.name as goods_name,area.id as area_id,area.region_name as area_name,stock.id as in_stock_id,stock.name as in_stock_name,
            stock.serial_number as in_serial_number,p.id as purchase_id,p.serial_number as purchase_serial_number,
            p.name as purchase_name from savor_finance_stock_record as a 
            left join savor_finance_stock as stock on a.stock_id=stock.id
            left join savor_area_info as area on stock.area_id=area.id
            left join savor_finance_purchase as p on 	stock.purchase_id=p.id
            left join savor_finance_goods as goods on a.goods_id=goods.id 
            where a.type=1 and a.dstatus=1 and a.goods_id not in (56,62) and stock.io_type=11 and a.add_time>='2024-01-01 00:00:00' and a.total_amount>1
            order by a.id asc ";
        $now_time = date('Y-m-d H:i:s');
        echo "box code begin $now_time \r\n";
        $m_tmpidcode = new \Admin\Model\FinanceTmpidcodeModel();
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $m_price_template_hotel = new \Admin\Model\FinancePriceTemplateHotelModel();
        $res_data = $m_tmpidcode->query($sql_1);
        foreach ($res_data as $v){
            $idcode = $v['idcode'];
            $qrcontent = decrypt_data($idcode);
            $qr_id = intval($qrcontent);
            $res_sondata = $m_qrcode_content->getDataList('id',array('parent_id'=>$qr_id),'id asc');
            $son_datas = array();
            foreach ($res_sondata as $sv){
                $son_idcode = encrypt_data($sv['id']);

                $son_fileds = 'stock.id as out_stock_id,stock.name as out_stock_name,stock.serial_number as out_serial_number,a.type,a.unit_id,a.wo_reason_type,a.wo_status,
                stock.hotel_id,hotel.name as hotel_name';
                $res_son_record = $m_stock_record->alias('a')
                    ->field($son_fileds)
                    ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                    ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                    ->where(array('a.idcode'=>$son_idcode,'a.dstatus'=>1))
                    ->order('a.id desc')
                    ->limit('0,1')
                    ->select();

                $sale_type = 0;
                if(!empty($res_son_record[0]['out_stock_id'])){
                    $v['idcode'] = $son_idcode;
                    if($res_son_record[0]['out_stock_id']==$v['in_stock_id']){
                        $res_son_record[0]['out_stock_id'] = 0;
                        $res_son_record[0]['out_stock_name'] = '';
                        $res_son_record[0]['out_serial_number'] = '';
                    }
                    $now_son_data = array_merge($v,$res_son_record[0]);
                    if($res_son_record[0]['type']==7){
                        $res_sale = $m_sale->getInfo(array('idcode'=>$son_idcode));
                        $sale_type = $res_sale['type'];
                        $settlement_price = $res_sale['settlement_price'];
                    }else{
                        $res_sale = $m_sale->getAll('type,num,settlement_price',array('idcode'=>array('like',"%$son_idcode%")),0,1,'id desc');
                        if(!empty($res_sale[0]['type'])){
                            $sale_type = $res_sale[0]['type'];
                            $settlement_price = $res_sale[0]['settlement_price']/$res_sale[0]['num'];
                        }else{
                            $hotel_id = intval($res_son_record[0]['hotel_id']);
                            $settlement_price = $m_price_template_hotel->getHotelGoodsPrice($hotel_id,$v['goods_id']);
                        }
                    }
                    $now_son_data['settlement_price'] = $settlement_price;
                    $now_son_data['sale_type'] = $sale_type;
                    if(empty($now_son_data['hotel_name'])){
                        $now_son_data['hotel_name'] = '';
                    }
                    $son_datas[]=$now_son_data;
                }
            }
            if(!empty($son_datas)){
                $son_num = count($son_datas);
                $m_tmpidcode->addAll($son_datas);
                echo "$idcode,son_num:$son_num \r\n";
            }else{
                $p_fileds = 'stock.id as out_stock_id,stock.name as out_stock_name,stock.serial_number as out_serial_number,a.type,a.unit_id,a.wo_reason_type,a.wo_status,
                stock.hotel_id,hotel.name as hotel_name';
                $res_p_record = $m_stock_record->alias('a')
                    ->field($p_fileds)
                    ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                    ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                    ->where(array('a.idcode'=>$idcode,'a.dstatus'=>1))
                    ->order('a.id desc')
                    ->limit('0,1')
                    ->select();
                if(!empty($res_p_record[0]['out_stock_id'])){
                    if($res_p_record[0]['out_stock_id']==$v['in_stock_id']){
                        $res_p_record[0]['out_stock_id'] = 0;
                        $res_p_record[0]['out_stock_name'] = '';
                        $res_p_record[0]['out_serial_number'] = '';
                    }
                    $now_p_data = array_merge($v,$res_p_record[0]);
                    if(empty($now_p_data['hotel_name'])){
                        $now_p_data['hotel_name'] = '';
                    }
                    $m_tmpidcode->add($now_p_data);

                    echo "$idcode,son_num:0 \r\n";
                }
            }
        }
        $now_time = date('Y-m-d H:i:s');
        echo "box code end $now_time \r\n";

        echo "idcode start $now_time \r\n";

        $sql_2 = "SELECT a.idcode,a.price,a.avg_price,a.goods_id,a.add_time,goods.name as goods_name,area.id as area_id,area.region_name as area_name,stock.id as in_stock_id,stock.name as in_stock_name,
            stock.serial_number as in_serial_number,p.id as purchase_id,p.serial_number as purchase_serial_number,
            p.name as purchase_name from savor_finance_stock_record as a 
            left join savor_finance_stock as stock on a.stock_id=stock.id
            left join savor_area_info as area on stock.area_id=area.id
            left join savor_finance_purchase as p on 	stock.purchase_id=p.id
            left join savor_finance_goods as goods on a.goods_id=goods.id 
            where a.type=1 and a.goods_id not in (56,62) and stock.io_type=11 and a.add_time>='2024-01-01 00:00:00' and a.total_amount=1
            order by a.id asc ";
        $res_data = $m_tmpidcode->query($sql_2);
        foreach ($res_data as $v){
            $idcode = $v['idcode'];

            $idcode_fileds = 'stock.id as out_stock_id,stock.name as out_stock_name,stock.serial_number as out_serial_number,a.type,a.unit_id,a.wo_reason_type,a.wo_status,
                stock.hotel_id,hotel.name as hotel_name';
            $res_idcode_record = $m_stock_record->alias('a')
                ->field($idcode_fileds)
                ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
                ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
                ->where(array('a.idcode'=>$idcode,'a.dstatus'=>1))
                ->order('a.id desc')
                ->limit('0,1')
                ->select();

            $sale_type = 0;
            if(!empty($res_idcode_record[0]['out_stock_id'])){
                if($res_idcode_record[0]['out_stock_id']==$v['in_stock_id']){
                    $res_idcode_record[0]['out_stock_id'] = 0;
                    $res_idcode_record[0]['out_stock_name'] = '';
                    $res_idcode_record[0]['out_serial_number'] = '';
                }

                $now_idcode_data = array_merge($v,$res_idcode_record[0]);
                if($res_idcode_record[0]['type']==7){
                    $res_sale = $m_sale->getInfo(array('idcode'=>$idcode));
                    $sale_type = $res_sale['type'];
                    $settlement_price = $res_sale['settlement_price'];
                }else{
                    $res_sale = $m_sale->getAll('type,num,settlement_price',array('idcode'=>array('like',"%$idcode%")),0,1,'id desc');
                    if(!empty($res_sale[0]['type'])){
                        $sale_type = $res_sale[0]['type'];
                        $settlement_price = $res_sale[0]['settlement_price']/$res_sale[0]['num'];
                    }else{
                        $hotel_id = intval($res_idcode_record[0]['hotel_id']);
                        $settlement_price = $m_price_template_hotel->getHotelGoodsPrice($hotel_id,$v['goods_id']);
                    }
                }
                $now_idcode_data['settlement_price'] = $settlement_price;
                $now_idcode_data['sale_type'] = $sale_type;
                if(empty($now_idcode_data['hotel_name'])){
                    $now_idcode_data['hotel_name'] = '';
                }
                $m_tmpidcode->add($now_idcode_data);
            }
            echo "$idcode \r\n";
        }
        $now_time = date('Y-m-d H:i:s');
        echo "idcode end $now_time \r\n";

    }
}