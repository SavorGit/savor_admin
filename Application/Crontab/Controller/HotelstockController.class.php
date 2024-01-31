<?php
namespace Crontab\Controller;
use Think\Controller;

class HotelstockController extends Controller{
    public function stathotelstock(){
        $now_time = date('Y-m-d H:i:s');
        echo "stathotelstock start:$now_time \r\n";
        $m_stock = new \Admin\Model\FinanceStockModel();
        $m_stock->handle_hotel_stock();
        $now_time = date('Y-m-d H:i:s');
        echo "stathotelstock end:$now_time \r\n";
    }

    public function statgoodsstock(){
        $now_time = date('Y-m-d H:i:s');
        echo "handle_goods_stock start:$now_time \r\n";
        $m_stock = new \Admin\Model\FinanceStockModel();
        $m_stock->handle_goods_stock();
        $now_time = date('Y-m-d H:i:s');
        echo "handle_goods_stock end:$now_time \r\n";
    }

    public function archivedata(){
        $now_time = date('Y-m-d H:i:s');
        echo "archivedata start:$now_time \r\n";

        $static_date = date('Y-m-d',strtotime('-1 day'));
        $archivedata_date = date('Y-m-d',strtotime('-2 day'));
        $area_arr = array();
        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }

        $where = array('stock.type'=>20,'stock.io_type'=>22);
        $where['stock.hotel_id'] = array(array('gt',0),array('not in',C('TEST_HOTEL')));
        $fileds = 'a.goods_id,stock.area_id,goods.name as goods_name,goods.category_id,cate.name as category_name,
        hotel.id as hotel_id,hotel.name as hotel_name';
        $group = 'stock.hotel_id,a.goods_id';
        $m_stock_detail = new \Admin\Model\FinanceStockDetailModel();
        $res_list = $m_stock_detail->getHotelStockGoods($fileds,$where,$group,0,0);
        if(!empty($res_list)){
            $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
            $m_price_template_hotel = new \Admin\Model\FinancePriceTemplateHotelModel();
            $m_hotelstock_archivedata = new \Admin\Model\FinanceHotelStockArchivedataModel();
            foreach ($res_list as $v){
                $hotel_id = $v['hotel_id'];
                $in_num = $out_num = 0;
                $in_total_fee = $out_total_fee = 0;
                $goods_id = $v['goods_id'];
                $settlement_price = $m_price_template_hotel->getHotelGoodsPrice($hotel_id,$goods_id);

                $rfileds = 'sum(a.total_amount) as total_amount,sum(a.total_fee) as total_fee,a.type';
                $rwhere = array('stock.hotel_id'=>$hotel_id,'stock.type'=>20,'stock.io_type'=>22,'a.goods_id'=>$goods_id,'a.dstatus'=>1);
                $rwhere['a.type'] = 2;
                $rwhere['a.add_time'] = array('elt',"$static_date 23:59:59");
                $res_record = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
                if(!empty($res_record[0]['total_amount'])){
                    $in_num = abs($res_record[0]['total_amount']);
                    $in_total_fee = $in_num*$settlement_price;
                }

                $rwhere['a.type']=7;
                $rwhere['a.wo_status']= array('in',array(1,2,4));
                $res_worecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
                $wo_num = 0;
                if(!empty($res_worecord[0]['total_amount'])){
                    $wo_num = abs($res_worecord[0]['total_amount']);
                }
                $rwhere['a.type']=6;
                unset($rwhere['a.wo_status']);
                $rwhere['a.status']= array('in',array(1,2));
                $res_worecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
                $report_num = 0;
                if(!empty($res_worecord[0]['total_amount'])){
                    $report_num = abs($res_worecord[0]['total_amount']);
                }
                $out_num = $wo_num+$report_num;
                $out_total_fee = $out_num*$settlement_price;

                if($in_num>0){
                    $stock_num = $in_num-$out_num;
                    $stock_total_fee = $stock_num*$settlement_price;

                    $bwhere = array('hotel_id'=>$hotel_id,'goods_id'=>$goods_id,'static_date'=>$archivedata_date);
                    $res_begin_data = $m_hotelstock_archivedata->getAll('stock_num,stock_total_fee',$bwhere,0,1,'id desc');
                    if(!empty($res_begin_data[0]['stock_num'])){
                        $begin_num = $res_begin_data[0]['stock_num'];
                        $begin_total_fee = $res_begin_data[0]['stock_total_fee'];
                    }else{
                        $begin_num = $stock_num;
                        $begin_total_fee = $stock_total_fee;
                    }

                    $v['in_num'] = $in_num;
                    $v['in_total_fee'] = $in_total_fee;
                    $v['out_num'] = $out_num;
                    $v['out_total_fee'] = $out_total_fee;
                    $v['stock_num'] = $stock_num;
                    $v['stock_total_fee'] = $stock_total_fee;
                    $v['begin_num'] = $begin_num;
                    $v['begin_total_fee'] = $begin_total_fee;
                    $v['static_date'] = $static_date;
                    $v['settlement_price'] = $settlement_price;
                    $v['area_name'] = $area_arr[$v['area_id']]['region_name'];

                    $m_hotelstock_archivedata->add($v);
                }

            }
        }

        $now_time = date('Y-m-d H:i:s');
        echo "archivedata end:$now_time \r\n";
    }

}