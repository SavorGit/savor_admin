<?php
namespace Admin\Model;
class FinanceStockModel extends BaseModel{

    protected $tableName='finance_stock';

    public function handle_hotel_stock(){
        $where = array('type'=>20);
        $where['hotel_id'] = array('gt',0);
        $res_stockhotels = $this->getAllData('hotel_id',$where,'','hotel_id');
        $m_stock_detail = new \Admin\Model\FinanceStockDetailModel();
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $m_hotel_stock = new \Admin\Model\FinanceHotelStockModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(9);
        $cache_key = C('FINANCE_HOTELSTOCK');
        $data_list = array();
        foreach ($res_stockhotels as $v){
            $hotel_id = $v['hotel_id'];
            $res_hotel = $m_hotel->getHotelById('hotel.name as hotel_name,hotel.area_id,ext.is_salehotel',array('hotel.id'=>$hotel_id));
            $hotel_area_id = $res_hotel['area_id'];
            $is_salehotel = $res_hotel['is_salehotel'];

            $hotel_name = '';
            $goods_ids = array();
            $goods_list = array();

            $fileds = 'a.goods_id,goods.name,goods.barcode,cate.name as cate_name,spec.name as sepc_name,a.unit_id,unit.name as unit_name,hotel.id as hotel_id,hotel.name as hotel_name';
            $group = 'a.goods_id';
            $where = array('stock.hotel_id'=>$hotel_id,'stock.type'=>20,'stock.io_type'=>22);
            $res_list = $m_stock_detail->getHotelStockGoods($fileds,$where,$group,0,10000);
            if(!empty($res_list)){
                $hotel_name = $res_list[0]['hotel_name'];
                foreach ($res_list as $gv){
                    $out_num = $unpack_num = $wo_num = $report_num = 0;
                    $goods_id = $gv['goods_id'];
                    $rfileds = 'sum(a.total_amount) as total_amount,sum(a.total_fee) as total_fee,a.type';
                    $rwhere = array('stock.hotel_id'=>$hotel_id,'stock.type'=>20,'stock.io_type'=>22,'a.goods_id'=>$goods_id,'a.dstatus'=>1);
                    $rwhere['a.type'] = 2;
                    $rgroup = 'a.type';

                    $res_outrecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','',$rgroup);
                    if(!empty($res_outrecord[0]['total_amount'])){
                        $out_num = abs($res_outrecord[0]['total_amount']);
                    }

                    $rwhere['a.type']=7;
                    $rwhere['a.wo_status']= array('in',array(1,2,4));
                    $res_worecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
                    if(!empty($res_worecord[0]['total_amount'])){
                        $wo_num = $res_worecord[0]['total_amount'];
                    }

                    $rwhere['a.type']=6;
                    unset($rwhere['a.wo_status']);
                    $rwhere['a.status']= array('in',array(1,2));
                    $res_worecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
                    if(!empty($res_worecord[0]['total_amount'])){
                        $report_num = $res_worecord[0]['total_amount'];
                    }

                    $stock_num = $out_num+$wo_num+$report_num;
                    if($stock_num>0){
                        $goods_ids[]=$goods_id;
                        $goods_list[]=array('id'=>$goods_id,'name'=>$gv['name'],'stock_num'=>$stock_num);
                    }
                }
            }
            $hotel_cache_key = $cache_key.":$hotel_id";
            $is_salehotel_stock = 0;
            if(!empty($goods_list)){
                $is_salehotel_stock = 1;
                $hotel_data = array('hotel_id'=>$hotel_id,'hotel_name'=>$hotel_name,'goods_ids'=>$goods_ids,'goods_list'=>$goods_list);
                $redis->set($hotel_cache_key,json_encode($hotel_data));

                $data_list[$hotel_id]=$hotel_data;
            }else{
                $redis->del($hotel_cache_key);
            }
            $m_hotel_ext->saveData(array('is_salehotel_stock'=>$is_salehotel_stock),array('hotel_id'=>$hotel_id));

            if(!empty($goods_list)){
                $del_stock_goods_ids = array('hotel_id'=>$hotel_id,'goods_id'=>array('not in',$goods_ids));
                $m_hotel_stock->delData($del_stock_goods_ids);
                foreach ($goods_list as $gv){
                    $n_ginfo = array('area_id'=>$hotel_area_id,'hotel_id'=>$hotel_id,'goods_id'=>$gv['id']);
                    $res_hstock = $m_hotel_stock->getInfo($n_ginfo);
                    $n_ginfo['num']=$gv['stock_num'];
                    if(!empty($res_hstock)){
                        $n_ginfo['update_time'] = date('Y-m-d H:i:s');
                        $m_hotel_stock->updateData(array('id'=>$res_hstock['id']),$n_ginfo);
                    }else{
                        $n_ginfo['num']=$gv['stock_num'];
                        $m_hotel_stock->add($n_ginfo);
                    }
                }
            }else{
                $m_hotel_stock->delData(array('hotel_id'=>$hotel_id));
            }

        }

        $redis->set($cache_key,json_encode($data_list));
        return true;
    }

    public function handle_goods_stock(){
        $where = array('stock.hotel_id'=>array('gt',0),'stock.type'=>20,'stock.io_type'=>22);

        $fileds = 'a.goods_id,goods.name,goods.barcode,cate.name as cate_name,spec.name as sepc_name';
        $group = 'a.goods_id';
        $m_stock_detail = new \Admin\Model\FinanceStockDetailModel();
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $res_list = $m_stock_detail->getHotelStockGoods($fileds,$where,$group,0,10000);
        $goods_list = array();
        if(!empty($res_list)){
            foreach ($res_list as $gv){
                $out_num = $unpack_num = $wo_num = $report_num = 0;
                $goods_id = $gv['goods_id'];
                $rfileds = 'sum(a.total_amount) as total_amount';
                $rwhere = array('stock.type'=>20,'stock.io_type'=>22,'a.goods_id'=>$goods_id,'a.dstatus'=>1);
                $rwhere['a.type'] = 2;

                $res_outrecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
                if(!empty($res_outrecord[0]['total_amount'])){
                    $out_num = abs($res_outrecord[0]['total_amount']);
                }

                $rwhere['a.type']=7;
                $rwhere['a.wo_status']= array('in',array(1,2,4));
                $res_worecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
                if(!empty($res_worecord[0]['total_amount'])){
                    $wo_num = $res_worecord[0]['total_amount'];
                }

                $rwhere['a.type']=6;
                unset($rwhere['a.wo_status']);
                $rwhere['a.status']= array('in',array(1,2));
                $res_worecord = $m_stock_record->getStockRecordList($rfileds,$rwhere,'a.id desc','','');
                if(!empty($res_worecord[0]['total_amount'])){
                    $report_num = $res_worecord[0]['total_amount'];
                }

                $stock_num = $out_num+$wo_num+$report_num;

                $goods_list[$goods_id]=array('id'=>$goods_id,'name'=>$gv['name'],'stock_num'=>$stock_num);
            }
        }
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(9);
        $cache_key = C('FINANCE_GOODSSTOCK');
        $redis->set($cache_key,json_encode($goods_list));
    }
}
