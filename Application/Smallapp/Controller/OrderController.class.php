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

        $where = array('buy_type'=>1);
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

        $order_status = C('ORDER_STATUS');
        $m_goods = new \Admin\Model\Smallapp\GoodsModel();
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_invite_code = new \Admin\Model\HotelInviteCodeModel();
        $m_integralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
        $m_box = new \Admin\Model\BoxModel();
        foreach ($datalist as $k=>$v){
            $goods_info = $m_goods->getInfo(array('id'=>$v['goods_id']));
            $integral = 0;
            $res_integralrecord = $m_integralrecord->getInfo(array('jdorder_id'=>$v['id']));
            if(!empty($res_integralrecord)){
                $integral = $res_integralrecord['integral'];
            }
            $datalist[$k]['integral'] = $integral;
            $datalist[$k]['goods_name'] = $goods_info['name'];
            $datalist[$k]['statusstr'] = $order_status[$v['status']];

            $user_info = $m_user->getOne('openid,nickName',array('id'=>$v['sale_uid']),'id desc');
            $datalist[$k]['nickName'] = $user_info['nickname'];
            $res_invite_code = $m_invite_code->getInviteExcel('ht.name',array('a.openid'=>$user_info['openid'],'a.flag'=>0),'ht.id desc');
            $datalist[$k]['hotel_name'] = $res_invite_code[0]['name'];
            $room_name = '';
            if(!empty($v['box_mac'])){
                $res_box = $m_box->getHotelInfoByBoxMac($v['box_mac']);
                $room_name = $res_box['room_name'];
            }
            $datalist[$k]['room_name'] = $room_name;
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

    public function rewardintegral(){
        $order_id = I('id',0,'intval');
        $m_order = new \Admin\Model\Smallapp\OrderModel();
        $res_order = $m_order->getInfo(array('id'=>$order_id));
        if(IS_POST){
            $integral = I('post.integral',0,'intval');
            if($res_order['status']!=12){
                $this->output('订单未支付', 'order/orderlist',2,0);
            }
            if(empty($integral)){
                $this->output('奖励积分不能为0', 'order/orderlist',2,0);
            }
            if($integral>9999){
                $this->output('奖励积分不能大于最大值', 'order/orderlist',2,0);
            }
            $m_user_integralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
            $res_order_integralrecord = $m_user_integralrecord->getInfo(array('jdorder_id'=>$res_order['id']));
            if(!empty($res_order_integralrecord) && !empty($res_order_integralrecord['integral'])){
                $this->output('该订单奖励积分已发放', 'order/orderlist',2,0);
            }
            $m_user = new \Admin\Model\Smallapp\UserModel();
            $user_info = $m_user->getOne('openid,nickName',array('id'=>$res_order['sale_uid']),'id desc');
            if(empty($user_info)){
                $this->output('奖励用户不存在', 'order/orderlist',2,0);
            }
            $record_data = array('openid'=>$user_info['openid'],'integral'=>$integral,'goods_id'=>$res_order['goods_id'],
                'jdorder_id'=>$res_order['id'],'content'=>$res_order['amount'],'type'=>3,
                'integral_time'=>date('Y-m-d H:i:s'),'status'=>1);

            if(!empty($res_order_integralrecord)){
                $m_user_integralrecord->updateData(array('id'=>$res_order_integralrecord['id']),$record_data);
            }else{
                $m_user_integralrecord->add($record_data);
            }

            $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
            $res_userintegral = $m_userintegral->getInfo(array('openid'=>$user_info['openid']));
            if(!empty($res_userintegral)){
                $userintegral = $res_userintegral['integral']+$integral;
                $m_userintegral->updateData(array('id'=>$res_userintegral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
            }else{
                $integraldata = array('openid'=>$user_info['openid'],'integral'=>$integral,'update_time'=>date('Y-m-d H:i:s'));
                $m_userintegral->add($integraldata);
            }
            $this->output('奖励积分发放成功', 'order/orderlist');

        }else{
            $m_goods = new \Admin\Model\Smallapp\GoodsModel();
            $goods_info = $m_goods->getInfo(array('id'=>$res_order['goods_id']));
            $res_order['goods_name'] = $goods_info['name'];
            $res_order['goods_integral'] = intval($res_order['amount']*$goods_info['rebate_integral']);
            $this->assign('vinfo',$res_order);
            $this->display('rewardintegral');
        }
    }

}