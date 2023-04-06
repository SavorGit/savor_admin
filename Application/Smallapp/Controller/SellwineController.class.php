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
}