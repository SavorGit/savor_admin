<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

/**
 * @desc 菜品订单管理
 *
 */
class DishorderController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }

    public function orderlist() {
        $start_date = I('start_date','');
        $end_date = I('end_date','');
        $area_id = I('area_id',0,'intval');
        $status   = I('status',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array();
        if($status)     $where['a.status'] = $status;
        if($area_id)    $where['area.id']=$area_id;
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'dishorder/orderlist', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        $start  = ($page-1) * $size;
        $m_order  = new \Admin\Model\Smallapp\OrderModel();
//        $m_order  = new \Admin\Model\Smallapp\DishorderModel();
        $fields = 'a.id,a.openid,a.price,a.amount,a.total_fee,a.status,a.contact,a.phone,
        a.address,a.remark,a.delivery_time,a.add_time,
        hotel.name as hotel_name,area.region_name as area_name';
        $result = $m_order->getOrderList($fields,$where, 'a.id desc', $start, $size);
        $datalist = $result['list'];

        $order_status = C('DISH_ORDERSTATUS');
        if(!empty($datalist)){
            $m_ordergoods = new \Admin\Model\Smallapp\OrdergoodsModel();
            foreach ($datalist as $k=>$v){
                $datalist[$k]['status_str'] = $order_status[$v['status']];
                if($v['delivery_time']=='0000-00-00 00:00:00'){
                    $datalist[$k]['delivery_time'] = '';
                }
                $res_ordergoods = $m_ordergoods->getOrdergoodsList('goods.name',array('og.order_id'=>$v['id']),'og.id asc');
                $goods_names = array();
                foreach ($res_ordergoods as $gv){
                    $goods_names[]=$gv['name'];
                }
                $name = join(',',$goods_names);
                $datalist[$k]['name'] = $name;
            }
        }

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $this->assign('area_id',$area_id);
        $this->assign('area',$area_arr);
        $this->assign('status',$status);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('orderlist');
    }

}