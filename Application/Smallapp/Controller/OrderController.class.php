<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
use Common\Lib\Curl;

/**
 * @desc 订单管理
 *
 */
class OrderController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function orderlist() {
        $start_date = I('post.start_date','');
        $end_date = I('post.end_date','');
    	$status = I('status',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array();
        if($status){
            $where['status'] = $status;
        }
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'goods/goodsadd', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        $start  = ($page-1) * $size;
        $m_order  = new \Admin\Model\Smallapp\OrderModel();
        $result = $m_order->getDataList('*',$where, 'id desc', $start, $size);
        $datalist = $result['list'];

        $buy_types = C('BUY_TYPE');
        $order_types = C('ORDER_OTYPE');
        $order_status = C('ORDER_STATUS');
        $m_goods = new \Admin\Model\Smallapp\GoodsModel();
        foreach ($datalist as $k=>$v){
            $goods_info = $m_goods->getInfo(array('id'=>$v['goods_id']));
            $datalist[$k]['goods_name'] = $goods_info['name'];
            $datalist[$k]['buy_typestr'] = $buy_types[$v['buy_type']];
            $datalist[$k]['otypestr'] = $order_types[$v['otype']];
            $datalist[$k]['statusstr'] = $order_status[$v['status']];

        }
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('status',$status);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('orderlist');
    }

    public function orderaudit(){
        $order_id = I('order_id',0,'intval');
        $hash_ids_key = C('HASH_IDS_KEY');
        $hashids = new \Common\Lib\Hashids($hash_ids_key);
        $params = $hashids->encode($order_id);
        $url = C('SAVOR_API_URL').'/payment/wxPay/integralwithdraw';
        $curl = new Curl();
        $data = array('params'=>$params);
        $curl::post($url,$data,$result,10);
        if($result['code']!=10000){
            if($result['code']==99003){
                $message = '用户无mopenid,无法提现';
            }elseif($result['code']==99005){
                $message = '用户积分不够,无法提现';
            }else{
                $message = '不满足兑换条件';
            }
        }else{
            $message = '提现成功';
            $m_order = new \Common\Model\Smallapp\OrderModel();
            $m_order->updateData(array('id'=>$order_id),array('status'=>20));
        }
        $this->output($message, 'order/orderlist',2);
    }

}