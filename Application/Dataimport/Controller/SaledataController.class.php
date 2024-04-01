<?php
namespace Dataimport\Controller;
use Think\Controller;

class SaledataController extends Controller{

    public function addsale(){
        $file_path = '/application_data/web/php/savor_admin/Public/content/测试033101.xlsx';
//        $file_path = SITE_TP_PATH.'/Public/uploads/'.$file_name;
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");
        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);

        $objPHPExcel = $objReader->load($file_path);
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $m_stock = new \Admin\Model\FinanceStockModel();
        $m_stock_detail = new \Admin\Model\FinanceStockDetailModel();
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $m_staff = new \Admin\Model\Integral\StaffModel();
        $m_salepayment = new \Admin\Model\FinanceSalePaymentModel();
        $m_salerecord = new \Admin\Model\FinanceSalePaymentRecordModel();
        $all_stock_in_ids = $all_stock_out_ids = array();
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

            $hotel_id = intval($rowData[0][0]);
            $idcode = $rowData[0][2];
            $stock_in_id = intval($rowData[0][3]);
            $stock_out_id = intval($rowData[0][4]);
            $wo_time = $rowData[0][5];
            $settlement_price = $rowData[0][6];
            $now_avg_price = $rowData[0][7];

            if(is_numeric($wo_time)) {
                // 读取excel日期型为整数，日期时间型为整数.小数 $phptime = ($wo_time - 25569) * 86400;
                $phptime = ($wo_time - 25569) * 86400 - (8 * 60 * 60);
                $wo_time = date('Y-m-d H:i:s',$phptime);
            }

            $fields = 'hotel.area_id,ext.maintainer_id,ext.residenter_id';
            $res_hotelext = $m_hotel->getHotelById($fields,array('hotel.id'=>$hotel_id));
            $area_id = $res_hotelext['area_id'];
            $maintainer_id = intval($res_hotelext['maintainer_id']);
            $residenter_id = intval($res_hotelext['residenter_id']);

            $res_indetail = $m_stock_detail->getInfo(array('stock_id'=>$stock_in_id));
            $goods_id = $res_indetail['goods_id'];
            $unit_id = $res_indetail['unit_id'];
            $price = $res_indetail['price'];
            $res_outdetail = $m_stock_detail->getInfo(array('stock_id'=>$stock_out_id,'goods_id'=>$goods_id,'unit_id'=>$unit_id));
            if(empty($res_outdetail)){
                $out_stock_detail_id = $m_stock_detail->add(array('stock_id'=>$stock_out_id,'goods_id'=>$goods_id,'unit_id'=>$unit_id,'status'=>1));
            }else{
                $out_stock_detail_id = $res_outdetail['id'];
            }
            $total_fee = $price;
            $amount = 1;
            $total_amount = 1;
            $stock_detail_id = $res_indetail['id'];
            $batch_no = getMillisecond();
            if($area_id==1){
                $op_openid = 'o9GS-4lU4v_wQclbeoHosBZQ1UMc';//徐寅
            }else{
                $op_openid = 'o9GS-4mTCZvkRCDRnkg77QqohMI4';//胡子凤
            }

            $all_stock_in_ids[$stock_in_id]=$op_openid;
            $all_stock_out_ids[$stock_out_id]=$op_openid;

            //入库
            $indata = array('stock_id'=>$stock_in_id,'stock_detail_id'=>$stock_detail_id,'goods_id'=>$goods_id,'batch_no'=>$batch_no,'idcode'=>$idcode,'avg_price'=>$now_avg_price,
                'price'=>$price,'total_fee'=>$total_fee,'unit_id'=>$unit_id,'amount'=>$amount,'total_amount'=>$total_amount,'type'=>1,'op_openid'=>$op_openid
            );
            $m_stock_record->add($indata);
            $m_stock_detail->where(array('id'=>$stock_detail_id))->setInc('amount',1);
            $m_stock_detail->where(array('id'=>$stock_detail_id))->setInc('total_amount',1);

            $outdata = $indata;
            $batch_no = getMillisecond();
            //出库
            $outdata['type'] = 2;
            $outdata['stock_id'] = $stock_out_id;
            $outdata['stock_detail_id'] = $out_stock_detail_id;
            $outdata['batch_no'] = $batch_no;
            $outdata['price'] = -$indata['price'];
            $outdata['total_fee'] = -$indata['total_fee'];
            $outdata['amount'] = -$indata['amount'];
            $outdata['total_amount'] = -$indata['total_amount'];
            $m_stock_record->add($outdata);
            $m_stock_detail->where(array('id'=>$out_stock_detail_id))->setInc('amount',-1);
            $m_stock_detail->where(array('id'=>$out_stock_detail_id))->setInc('total_amount',-1);

            //领取
            $receive_data = $outdata;
            $batch_no = getMillisecond();
            $receive_data['type'] = 4;
            $receive_data['price'] = abs($outdata['price']);
            $receive_data['total_fee'] = abs($outdata['total_fee']);
            $receive_data['amount'] = abs($outdata['amount']);
            $receive_data['total_amount'] = abs($outdata['total_amount']);
            $receive_data['op_openid'] = $op_openid;
            $receive_data['batch_no'] = $batch_no;
            $m_stock_record->add($receive_data);

            //送达
            $check_data = $receive_data;
            $batch_no = getMillisecond();
            $check_data['type'] = 5;
            $check_data['op_openid'] = $op_openid;
            $check_data['batch_no'] = $batch_no;
            $m_stock_record->add($check_data);

            //核销
            $res_staff = $m_staff->getMerchantStaffList('a.openid',array('m.hotel_id'=>$hotel_id,'m.status'=>1,'a.status'=>1));
            $staffs = array();
            $sale_openid = '';
            if(!empty($res_staff)){
                $staffs = $res_staff;
                shuffle($staffs);
                $sale_openid = $staffs[0]['openid'];
            }
            $wo_data = $check_data;
            $wo_data['type'] = 7;
            $wo_data['op_openid'] = $sale_openid;
            $wo_data['batch_no'] = $batch_no;
            $wo_data['price'] = -abs($check_data['price']);
            $wo_data['total_fee'] = -abs($check_data['total_fee']);
            $wo_data['amount'] = -abs($check_data['amount']);
            $wo_data['total_amount'] = -abs($check_data['total_amount']);
            $wo_data['wo_reason_type'] = 1;
            $wo_data['wo_data_imgs'] = '';
            $wo_data['wo_status'] = 2;
            $wo_data['recycle_status'] = 4;
            $wo_data['out_time'] = date('Y-m-d H:i:s');
            $wo_data['wo_num'] = 1;
            $wo_data['is_notifymsg'] = 0;
            $wo_data['wo_time'] = $wo_time;
            $wo_data['add_time'] = $wo_time;
            $wo_data['update_time'] = $wo_time;
            $record_id = $m_stock_record->add($wo_data);

            //销售出库单
            $add_data = array('stock_record_id'=>$record_id,'goods_id'=>$wo_data['goods_id'],'sale_price'=>$settlement_price,'now_avg_price'=>$now_avg_price,
                'idcode'=>$wo_data['idcode'],'cost_price'=>$settlement_price,'settlement_price'=>$settlement_price,'goods_settlement_price'=>$settlement_price,
                'hotel_id'=>$hotel_id,'maintainer_id'=>$maintainer_id,'residenter_id'=>$residenter_id,'add_time'=>$wo_time,
                'type'=>1,'area_id'=>$area_id,'sale_openid'=>$sale_openid);
            $sale_id = $m_sale->add($add_data);

            //收款
            $nowdate = date('Ymd',strtotime($wo_time));
            $where = array('DATE_FORMAT(add_time, "%Y%m%d")'=>$nowdate);
            $res_salepayment = $m_salepayment->getAllData('count(id) as num',$where);
            if($res_salepayment[0]['num']>0){
                $number = $res_salepayment[0]['num']+1;
            }else{
                $number = 1;
            }
            $num_str = str_pad($number,4,'0',STR_PAD_LEFT);
            $serial_number = "SKD-$nowdate-$num_str";
            $payment_info = array('serial_number'=>$serial_number,'tax_rate'=>13,'pay_money'=>$settlement_price,
                'pay_time'=>date('Y-m-d',strtotime($wo_time)),'type'=>1,'hotel_id'=>$hotel_id,'add_time'=>$wo_time);
            $sale_payment_id = $m_salepayment->add($payment_info);
            $payment_record_info = array('sale_id'=>$sale_id,'sale_payment_id'=>$sale_payment_id,'pay_money'=>$settlement_price,
                'add_time'=>$wo_time);
            $m_salerecord->add($payment_record_info);

            //更新出库单收款
            $up_sale = array('status'=>2,'sale_payment_id'=>$sale_payment_id,'ptype'=>1,'pay_time'=>$wo_time,'pay_money'=>$settlement_price);
            $m_sale->updateData(array('id'=>$sale_id),$up_sale);

            echo "icdoe:$idcode,sale_id:$sale_id";
            exit;
        }

        exit;
        //入库单 所有商品入库完毕后
        foreach ($all_stock_in_ids as $k=>$v){
            $stock_in_id = $k;
            $op_openid = $v;

            $rfields = 'sum(total_amount) as total_num,sum(total_fee) as total_fee';
            $rwhere = array('stock_id'=>$stock_in_id,'type'=>1,'dstatus'=>1);
            $res_stock_num = $m_stock_record->getALLDataList($rfields,$rwhere,'','','');
            $up_data = array('status'=>2,'op_openid'=>$op_openid);
            $up_data['amount'] = intval($res_stock_num[0]['total_num']);
            $up_data['total_fee'] = $res_stock_num[0]['total_fee']>0?$res_stock_num[0]['total_fee']:0;
            $up_data['total_money'] = $up_data['total_fee'];
            $m_stock->updateData(array('id'=>$stock_in_id),$up_data);
        }
        //出库单 所有商品领取,验收完毕
        foreach ($all_stock_out_ids as $k=>$v){
            $stock_out_id = $k;
            $op_openid = $v;
            $up_data = array('status'=>4,'receive_openid'=>$op_openid,'check_openid'=>$op_openid,'update_time'=>date('Y-m-d H:i:s'));
            $m_stock->updateData(array('id'=>$stock_out_id),$up_data);
        }

    }
}