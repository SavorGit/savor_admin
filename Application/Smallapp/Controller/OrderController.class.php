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
        $order_id = I('get.id',0,'intval');
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
            $m_order = new \Admin\Model\Smallapp\OrderModel();
            $m_order->updateData(array('id'=>$order_id),array('status'=>20));
            $res_order = $m_order->getInfo(array('id'=>$order_id));
            $m_goods = new \Admin\Model\Smallapp\GoodsModel();
            $goods_info = $m_goods->getInfo(array('id'=>$res_order['goods_id']));
            if($goods_info['rebate_integral']){
                $m_box = new \Admin\Model\BoxModel();
                $box_info = $m_box->getHotelInfoByBoxMac($res_order['box_mac']);
                $integral = $goods_info['rebate_integral'];
                $integralrecord_data = array('openid' => $res_order['openid'], 'area_id' => $box_info['area_id'],
                    'area_name' => $box_info['area_name'], 'hotel_id' => $box_info['hotel_id'], 'hotel_name' => $box_info['hotel_name'],
                    'hotel_box_type' => $box_info['hotel_box_type'], 'room_id' => $box_info['room_id'], 'room_name' => $box_info['room_name'],
                    'box_id' => $box_info['box_id'], 'box_mac' => $res_order['box_mac'], 'box_type' => $box_info['box_type'],
                    'integral' => -$integral, 'content' => $res_order['goods_id'], 'type' => 4, 'integral_time' => date('Y-m-d H:i:s'));
                $m_userintegralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
                $m_userintegralrecord->add($integralrecord_data);
                $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
                $res_userintegral = $m_userintegral->getInfo(array('openid'=>$res_order['openid']));
                $userintegral = $res_userintegral['integral'] - $integral;
                $m_userintegral->updateData(array('id'=>$res_userintegral['id']),array('integral'=>$userintegral));
            }
        }
        $this->output($message, 'order/orderlist',2);
    }

}