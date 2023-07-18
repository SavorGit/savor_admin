<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class SellwineController extends BaseController {

    public function datalist(){
        $hotel_ids = array(395,962,964,1056,955,1064,1257,912,898,1250,1284,810,941,720,1110,
            1211,1287,1321,847,970,1240,1271,1289,1033,1049,1062,1107,1124,1031,1029,920);

        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $hotel_name = I('hotel_name','','trim');
        $static_date = I('date','');
        if(empty($static_date)){
            $static_date = date('Y-m-d',strtotime('-1 day'));
        }else{
            $static_date = date('Y-m-d',strtotime($static_date));
        }
        $start_time = "$static_date 00:00:00";
        $end_time = "$static_date 23:59:59";

        $m_basicdata = new \Admin\Model\Smallapp\StaticHotelbasicdataModel();
        $fields = 'hotel_id,hotel_name,static_date,dinner_zxrate as zxrate,wlnum,scancode_num,user_num,heart_num';
        $where = array('hotel_id'=>array('in',$hotel_ids),'static_date'=>$static_date);
        if(!empty($hotel_name)){
            $where['hotel_name'] = array('like',"%{$hotel_name}%");
        }
        $res_datas = $m_basicdata->getDataList($fields,$where,'dinner_zxrate asc');
        $m_order = new \Admin\Model\Smallapp\OrderModel();
        $datalist = array();
        foreach ($res_datas as $v){
            $order_num = 0;
            $where = array('hotel.id'=>$v['hotel_id'],'box.state'=>1,'box.flag'=>0);
            $where['a.status'] = array('not in',array(10,11));
            $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
            $ofields = 'count(a.id) as num';
            $res_orders = $m_order->getOrderinfoList($ofields,$where,'a.id desc');
            if(!empty($res_orders)){
                $order_num = $res_orders[0]['num'];
            }
            $info = array('static_date'=>$v['static_date'],'hotel_name'=>$v['hotel_name'],'hotel_id'=>$v['hotel_id'],'zxrate'=>$v['zxrate'],
                'wlnum'=>$v['wlnum'],'scancode_num'=>$v['scancode_num'],'user_num'=>$v['user_num'],'heart_num'=>$v['heart_num'],
                'order_num'=>$order_num
            );
            $datalist[]=$info;
        }

        $this->assign('date',$static_date);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('datalist',$datalist);
        $this->assign('page',array());
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function salelist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $keyword = I('keyword','','trim');

        if(empty($start_time)){
            $start_time = date('Y-m-d',strtotime('-1 day'));
        }else{
            $start_time = date('Y-m-d',strtotime($start_time));
        }
        if(empty($end_time)){
            $end_time = $start_time;
        }else{
            $end_time = date('Y-m-d',strtotime($end_time));
        }
        $where = array('a.is_salehotel'=>1,'hotel.state'=>1,'hotel.flag'=>0);
        if(!empty($keyword)){
            $where['hotel.name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $fields = 'a.hotel_id,hotel.name as hotel_name,hotel.area_id,area.region_name as area_name,su.remark as maintainer,a.sale_start_date,a.sale_end_date';
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $result = $m_hotel_ext->getSellwineList($fields,$where,'hotel.pinyin asc',$start,$size);
        $datalist = array();
        $m_finance_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $in_hotel_dates = $m_finance_stock_record->getSellIndateHotels();
        $sell_hotel_dates = $m_finance_stock_record->getSellDateHotels();
        $sell_nums = $m_finance_stock_record->getHotelSellwineNums($start_time,$end_time);
        foreach ($result['list'] as $k=>$v){
            $in_hotel_date = '';
            if(isset($in_hotel_dates[$v['hotel_id']])){
                $in_hotel_date = $in_hotel_dates[$v['hotel_id']];
            }
            $sell_date = '';
            if(isset($sell_hotel_dates[$v['hotel_id']])){
                $sell_date = $sell_hotel_dates[$v['hotel_id']];
            }
            $sell_num = 0;
            if(isset($sell_nums[$v['hotel_id']])){
                $sell_num = $sell_nums[$v['hotel_id']];
            }
            if($v['sale_start_date']=='0000-00-00'){
                $v['sale_start_date'] = '';
            }
            if($v['sale_end_date']=='0000-00-00'){
                $v['sale_end_date'] = '';
            }
            $v['in_hotel_date'] = $in_hotel_date;
            $v['sell_date'] = $sell_date;
            $v['sell_num'] = $sell_num;
            $datalist[]=$v;
        }

        $this->assign('start_time',date('Y-m-d',strtotime($start_time)));
        $this->assign('end_time',date('Y-m-d',strtotime($end_time)));
        $this->assign('keyword', $keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function hoteldata(){
        $hotel_id = I('hotel_id',0,'intval');
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        if(empty($start_time)){
            $start_time = date('Y-m-d 00:00:00',strtotime('-1 day'));
        }else{
            $start_time = date('Y-m-d 00:00:00',strtotime($start_time));
        }
        if(empty($end_time)){
            $end_time = $start_time;
        }else{
            $end_time = date('Y-m-d 23:59:59',strtotime($end_time));
        }
        $data = array();
        if($hotel_id){
            $m_hotel = new \Admin\Model\HotelModel();
            $res_hotel = $m_hotel->getOne($hotel_id);

            $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
            $fileds = 'sum(a.total_amount) as total_amount';
            $where = array('stock.hotel_id'=>$hotel_id,'a.type'=>7,'a.wo_reason_type'=>1,'a.wo_status'=>array('in','1,2,4'),
                'a.add_time'=>array(array('egt',$start_time),array('elt',$end_time)));
            $res_worecord = $m_stock_record->getStockRecordList($fileds,$where,'a.id desc','','');
            $sale_num = abs(intval($res_worecord[0]['total_amount']));

            $redis = new \Common\Lib\SavorRedis();
            $redis->select(9);
            $cache_key = C('FINANCE_HOTELSTOCK').":$hotel_id";
            $res_cache_stock = $redis->get($cache_key);
            $stock_num = 0;
            if(!empty($res_cache_stock)){
                $res_cache_stock = json_decode($res_cache_stock,true);
                foreach ($res_cache_stock['goods_list'] as $v){
                    $stock_num+=$v['stock_num'];
                }
            }

            $m_integral_record = new \Admin\Model\Smallapp\UserIntegralrecordModel();
            $where = array('hotel_id'=>$hotel_id,'type'=>17,'add_time'=>array(array('egt',$start_time),array('elt',$end_time)));
            $res_integral = $m_integral_record->getRow('sum(integral) as total_integral',$where);
            $integral = intval($res_integral['total_integral']);

            $m_sale = new \Admin\Model\FinanceSaleModel();
            $sale_where = array('a.hotel_id'=>$hotel_id,'a.add_time'=>array(array('egt',$start_time),array('elt',$end_time)),'record.wo_reason_type'=>1);
            $fileds = 'sum(a.settlement_price) as sale_money';
            $res_sale = $m_sale->getSaleStockRecordList($fileds,$sale_where,'','');
            $sale_money = abs(intval($res_sale[0]['sale_money']));

            $sale_where['a.ptype'] = array('in','0,2');
            $res_sale_qk = $m_sale->getSaleStockRecordList('sum(a.settlement_price) as money,a.ptype',$sale_where,'','','a.ptype');
            $qk_money = 0;
            $bf_qk_money = 0;
            foreach ($res_sale_qk as $v){
                if($v['ptype']==0){
                    $qk_money = $v['money'];
                }elseif($v['ptype']==2){
                    $bf_qk_money = $v['money'];
                }
            }
            $m_salepayrecord = new \Admin\Model\FinanceSalePaymentRecordModel();
            if($bf_qk_money>0){
                $where = array('sale.hotel_id'=>$hotel_id,'record.wo_reason_type'=>1,'sale.ptype'=>2);
                $where['sale.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                $fileds = 'sum(a.pay_money) as has_pay_money';
                $res_payrecord = $m_salepayrecord->getSalePaymentRecordList($fileds,$where);
                $has_pay_money = intval($res_payrecord[0]['has_pay_money']);
                $qk_money = $qk_money+($bf_qk_money-$has_pay_money);
            }

            $sale_where['a.is_expire'] = 1;
            $res_sale_cqqk = $m_sale->getSaleStockRecordList('sum(a.settlement_price) as money,a.ptype',$sale_where,'','','a.ptype');
            $cqqk_money = 0;
            $bf_cqqk_money = 0;
            foreach ($res_sale_cqqk as $v){
                if($v['ptype']==0){
                    $cqqk_money = $v['money'];
                }elseif($v['ptype']==2){
                    $bf_cqqk_money = $v['money'];
                }
            }
            if($bf_cqqk_money>0){
                $where = array('sale.hotel_id'=>$hotel_id,'record.wo_reason_type'=>1,'sale.ptype'=>2,'sale.is_expire'=>1);
                $where['sale.add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                $fileds = 'sum(a.pay_money) as has_pay_money';
                $res_payrecord = $m_salepayrecord->getSalePaymentRecordList($fileds,$where);
                $has_pay_money = intval($res_payrecord[0]['has_pay_money']);
                $cqqk_money = $cqqk_money+($bf_cqqk_money-$has_pay_money);
            }
            $data = array('hotel_id'=>$hotel_id,'hotel_name'=>$res_hotel['name'],'sale_num'=>$sale_num,'sale_money'=>$sale_money,
                'stock_num'=>$stock_num,'integral'=>$integral,'qk_money'=>intval($qk_money),'cqqk_money'=>intval($cqqk_money));
        }

        $m_hotel = new \Admin\Model\HotelModel();
        $fields = 'a.id as hotel_id,a.name as hotel_name';
        $where = array('a.state'=>1,'a.flag'=>0,'b.is_salehotel'=>1);
        $hotels = $m_hotel->getHotelLists($where,'a.pinyin asc','',$fields);
        foreach ($hotels as $k=>$v){
            if($v['hotel_id']==$hotel_id){
                $v['is_select'] = 'selected';
            }else{
                $v['is_select'] = '';
            }
            $hotels[$k] = $v;
        }
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('hotels', $hotels);
        $this->assign('hotel_id', $hotel_id);
        $this->assign('dinfo', $data);
        $this->display();
    }
}