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
            $integral = '';
            if($goods_info['type'] == 30 || $goods_info['type'] == 31){
                $integral = $v['amount']*$goods_info['rebate_integral'];
            }
            $datalist[$k]['integral'] = $integral;
            $datalist[$k]['goods_name'] = $goods_info['name'];
            $datalist[$k]['goods_type'] = $goods_info['type'];
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

    public function exchange(){
        $order_id = I('id',0,'intval');
        $goods_type = I('goods_type',0,'intval');
        $m_order = new \Admin\Model\Smallapp\OrderModel();
        $res_order = $m_order->getInfo(array('id'=>$order_id));
        $m_goods = new \Admin\Model\Smallapp\GoodsModel();
        $goods_info = $m_goods->getInfo(array('id'=>$res_order['goods_id']));

        if($goods_info['type']==31){
            if(IS_POST){
                $contact = I('post.contact','','trim');
                $phone = I('post.phone','','trim');
                $address = I('post.address','','trim');
                $status = I('post.status',20,'intval');

                $integral = intval($res_order['amount']*$goods_info['rebate_integral']);
                if(empty($integral)){
                    $this->output('商品积分不能为0', 'order/orderlist',2);
                }

                $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
                $res_userintegral = $m_userintegral->getInfo(array('openid'=>$res_order['openid']));
                $userintegral = $res_userintegral['integral'];
                if($integral>$userintegral){
                    $this->output('用户积分不能兑换此商品', 'order/orderlist',2);
                }
                if($status==21){
                    $integralrecord_data = array('openid'=>$res_order['openid'],'integral'=>-$integral,
                        'content'=>$res_order['goods_id'],'type'=>4,'integral_time'=>date('Y-m-d H:i:s'));
                    $m_userintegralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
                    $m_userintegralrecord->add($integralrecord_data);

                    $userintegral = $res_userintegral['integral'] - $integral;
                    $m_userintegral->updateData(array('id'=>$res_userintegral['id']),array('integral'=>$userintegral));
                    $message = '商品兑换成功';
                }else{
                    $message = '修改成功';
                }
                $odata = array('contact'=>$contact,'phone'=>$phone,'address'=>$address,'status'=>$status);
                $m_order->updateData(array('id'=>$order_id),$odata);

                $this->output($message, 'order/orderlist',2);

            }else{
                $res_order['goods_name'] = $goods_info['name'];
                $res_order['goods_integral'] = intval($res_order['amount']*$goods_info['rebate_integral']);
                $this->assign('goods_type',$goods_type);
                $this->assign('vinfo',$res_order);
                $this->display('exchange');
            }
        }else{
            $hash_ids_key = C('HASH_IDS_KEY');
            $hashids = new \Common\Lib\Hashids($hash_ids_key);
            $params = $hashids->encode($order_id);
            $url = C('SAVOR_API_URL').'/payment/wxPay/integralwithdraw';
            $curl = new Curl();
            $data = array('params'=>$params);
            $resapi = array('code'=>10000);
            $curl::post($url,$data,$resapi,10);
            $resapi = json_decode($resapi,true);
            if($resapi['code']!=10000){
                if($resapi['code']==99003){
                    $message = '用户无mopenid,无法提现';
                }elseif($resapi['code']==99005){
                    $message = '用户积分不够,无法提现';
                }else{
                    $message = '不满足兑换条件';
                }
            }else{
                $message = '提现成功';
                $m_order = new \Admin\Model\Smallapp\OrderModel();
                $m_order->updateData(array('id'=>$order_id),array('status'=>21));

                if($goods_info['rebate_integral']){
                    $integral = $goods_info['rebate_integral'];
                    $integralrecord_data = array('openid' => $res_order['openid'],'integral' => -$integral,
                        'content' => $res_order['goods_id'], 'type' => 4, 'integral_time' => date('Y-m-d H:i:s'));
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

}