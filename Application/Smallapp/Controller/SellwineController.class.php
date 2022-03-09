<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class SellwineController extends BaseController {

    public function datalist(){
        $hotel_ids = array(962,964,1056,955,1064,1257,912,898,1250,1284,810,941,720,1110,
            1211,1287,1321,847,970,1240,1271,1289,1033,1049,1062,1107,1124,1031,1029,920);

        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $hotel_name = I('hotel_name','','trim');
        $static_date = I('date','');
        if(empty($sdate)){
            $static_date = date('Y-m-d',strtotime('-1 day'));
        }else{
            $static_date = date('Y-m-d',strtotime($static_date));
        }

        $m_basicdata = new \Admin\Model\Smallapp\StaticHotelbasicdataModel();
        $fields = 'hotel_id,hotel_name,static_date,dinner_zxrate as zxrate,wlnum,scancode_num,user_num';
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
            $ofields = 'count(a.id) as num';
            $res_orders = $m_order->getOrderinfoList($ofields,$where,'a.id desc');
            if(!empty($res_orders)){
                $order_num = $res_orders[0]['num'];
            }
            $info = array('static_date'=>$v['static_date'],'hotel_name'=>$v['hotel_name'],'hotel_id'=>$v['hotel_id'],'zxrate'=>$v['zxrate'],
                'wlnum'=>$v['wlnum'],'scancode_num'=>$v['scancode_num'],'user_num'=>$v['user_num'],'order_num'=>$order_num
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

}