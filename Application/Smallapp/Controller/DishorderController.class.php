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
        $this->orders('order');
    }

    public function dishorderlist() {
        $this->orders('dish');
    }

    private function orders($display_type) {
        $start_date = I('start_date','');
        $end_date = I('end_date','');
        $area_id = I('area_id',0,'intval');
        $status   = I('status',0,'intval');
        $otype   = I('otype',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $otypes = array('5'=>'商品订单','6'=>'赠送订单','3'=>'外卖订单','51'=>'商品销售订单');
        if($display_type=='dish'){
            $otype = 3;
            $display_html = 'dishorderlist';
        }else{
            $display_html = 'orderlist';
            unset($otypes['3']);
        }
        if($otype){
            if($otype==51){
                $where = array('a.otype'=>5);
                $where['a.sale_uid'] = array('gt',0);
            }else{
                $where = array('a.otype'=>$otype);
            }
        }else{
            $all_otypes = $otypes;
            unset($all_otypes['51']);
            $where = array('a.otype'=>array('in',array_keys($all_otypes)));
        }
        switch ($status){
            case 1:
                $where['a.status'] = array('in',array(1,13,14,15,16,51,12,61));
                break;
            case 2:
                $where['a.status'] = array('in',array(2,17,18,19,53));
                break;
            case 3:
                $where['a.status'] = array('in',array(52,63));
                break;
            default:
                $where['a.status'] = array('not in',array(10,11,12));
        }
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
        $fields = 'a.id,a.openid,a.price,a.amount,a.total_fee,a.status,a.contact,a.phone,
        a.address,a.remark,a.delivery_time,a.add_time,a.otype,a.sale_uid,a.address,a.gift_oid,
        hotel.name as hotel_name,area.region_name as area_name';
        $result = $m_order->getOrderList($fields,$where, 'a.add_time desc', $start, $size);
        $datalist = $result['list'];

        $order_status = C('ORDER_ALLSTATUS');
        if(!empty($datalist)){
            $m_goods = new \Admin\Model\Smallapp\DishgoodsModel();
            $m_ordergoods = new \Admin\Model\Smallapp\OrdergoodsModel();
            $m_user = new \Admin\Model\Smallapp\UserModel();
            $m_ordergift = new \Admin\Model\Smallapp\OrdergiftModel();
            foreach ($datalist as $k=>$v){
                $sale_uname = '';
                if($v['otype']==5 && $v['sale_uid']){
                    $v['otype'] = 51;
                    $res_user = $m_user->getOne('name',array('id'=>$v['sale_uid']),'');
                    $sale_uname = $res_user['name'];
                }
                $datalist[$k]['sale_uname'] = $sale_uname;
                $datalist[$k]['otype_str'] = $otypes[$v['otype']];
                $datalist[$k]['status_str'] = $order_status[$v['status']];
                if($v['delivery_time']=='0000-00-00 00:00:00'){
                    $datalist[$k]['delivery_time'] = '';
                }
                $field_goods = 'goods.name,goods.attr_name,goods.gtype,goods.parent_id,og.amount,og.price';
                $res_ordergoods = $m_ordergoods->getOrdergoodsList($field_goods,array('og.order_id'=>$v['id']),'og.id asc');
                $details = array();
                foreach ($res_ordergoods as $gv){
                    $goods_name = $gv['name'];
                    if($gv['gtype']==3){
                        $res_pgoods = $m_goods->getInfo(array('id'=>$gv['parent_id']));
                        $goods_name = $res_pgoods['name'].' '.$gv['attr_name'];
                    }
                    $details[]=$goods_name.',数量：'.$gv['amount'].',价格：'.$gv['price'];
                }
                $details = join('、',$details);
                $datalist[$k]['details'] = $details;

                $field_goods = 'goods.name,goods.attr_name,goods.gtype,og.amount';
                $res_ordergiftgoods = $m_ordergift->getOrderGiftgoodsList($field_goods,array('og.order_id'=>$v['id']),'og.id asc');
                $gifts = array();
                foreach ($res_ordergiftgoods as $gfv){
                    $goods_name = $gfv['name'];
                    $gifts[]=$goods_name.',数量：'.$gfv['amount'];
                }
                $gifts = join('、',$gifts);
                $datalist[$k]['gifts'] = $gifts;


                $is_send = 0;
                if(in_array($v['status'],array(52,53,63)) && !empty($v['address'])){
                    $is_send = 1;
                }
                $datalist[$k]['is_send'] = $is_send;
            }
        }
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $this->assign('area_id',$area_id);
        $this->assign('area',$area_arr);
        $this->assign('status',$status);
        $this->assign('otypes',$otypes);
        $this->assign('otype',$otype);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->display($display_html);
    }

    public function expresslist(){
        $order_id = I('order_id',0);
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        if(empty($size)){
            $size = 50;
        }
        $start  = ($page-1) * $size;
        $m_orderexpress = new \Admin\Model\Smallapp\OrderexpressModel();
        $result = $m_orderexpress->getDataList('*',array('order_id'=>$order_id),'id desc',$start,$size);
        $datalist = $result['list'];
        $express = new \Common\Lib\Express();
        $all_express = $express->getCompany();
        foreach ($datalist as $k=>$v){
            $datalist[$k]['company'] = $all_express[$v['comcode']]['name'];
        }
        $this->assign('order_id', $order_id);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->display();
    }

    public function orderreceive(){
        $order_id = I('id',0,'intval');
        $m_order  = new \Admin\Model\Smallapp\OrderModel();
        $res_order = $m_order->getInfo(array('id'=>$order_id));
        if(IS_GET){
            $this->assign('vinfo',$res_order);
            $this->display();
        }else{
            $action = I('post.action',0,'intval');
            switch ($action){
                case 1://接单
                    $m_order->updateData(array('id'=>$order_id),array('status'=>52,'finish_time'=>date('Y-m-d H:i:s')));
                    $message = '接单成功';
                    break;
                case 2://不接单
                    $is_cancel = 0;
                    $message = '不接单成功';
                    if($res_order['pay_type']==10){
                        if(!empty($res_order['parent_oid'])){
                            $refund_oid = $res_order['parent_oid'];
                            $res_porder = $m_order->getInfo(array('id'=>$refund_oid));
                            $pay_fee = $res_porder['pay_fee'];
                        }else{
                            $refund_oid = $res_order['id'];
                            $pay_fee = $res_order['pay_fee'];
                        }
                        $m_ordermap = new \Admin\Model\Smallapp\OrdermapModel();
                        $res_ordermap = $m_ordermap->getDataList('id',array('order_id'=>$refund_oid),'id desc',0,1);
                        $trade_no = $res_ordermap['list'][0]['id'];

                        $oinfo = array('trade_no'=>$trade_no,'batch_no'=>$order_id,'pk_type'=>2,'pay_fee'=>$pay_fee,'refund_money'=>$res_order['pay_fee']);
                        $params = encrypt_data(json_encode($oinfo),C('API_SECRET_KEY'));

                        $url = 'http://'.C('SAVOR_API_URL')."/payment/wxPay/refundMoney?params=$params";
                        $curl = new \Common\Lib\Curl();
                        $response = json_encode(array());
                        $curl::get($url,$response,5);
                        $res = json_decode($response,true);
                        if($res["code"]==10000 && $res['is_refund']==1){
                            $is_cancel = 1;
                            $m_order->updateData(array('id'=>$order_id),array('status'=>18,'finish_time'=>date('Y-m-d H:i:s')));
                            $message = '取消订单成功,且已经退款.款项在1到7个工作日内,退还到用户的支付账户';
                        }else{
                            $message = '取消订单失败';
                        }

                    }else{
                        $is_cancel = 1;
                        $m_order->updateData(array('id'=>$order_id),array('status'=>18,'finish_time'=>date('Y-m-d H:i:s')));
                    }

                    if($is_cancel && in_array($res_order['otype'],array(5,6))){
                        $m_goods = new \Admin\Model\Smallapp\DishgoodsModel();
                        $m_ordergoods = new \Admin\Model\Smallapp\OrdergoodsModel();
                        $gfields = 'goods.id as goods_id,goods.status,goods.amount as all_amount,og.amount';
                        $res_goods = $m_ordergoods->getOrdergoodsList($gfields,array('og.order_id'=>$order_id),'og.id asc');
                        foreach ($res_goods as $v){
                            $now_amount = $v['all_amount'] + $v['amount'];
                            $updata = array('amount'=>$now_amount);
                            $m_goods->updateData(array('id'=>$v['goods_id']),$updata);
                        }
                        $m_income = new \Admin\Model\Smallapp\UserincomeModel();
                        $m_income->delData(array('order_id'=>$order_id));
                    }
                    break;
                default:
                    $message = '操作失败';
            }
            $this->output($message, "dishorder/orderlist");
        }
    }

    public function addexpress(){
        $order_id = I('id',0,'intval');
        $eid = I('eid',0,'intval');

        $m_orderexpress = new \Admin\Model\Smallapp\OrderexpressModel();
        $m_order  = new \Admin\Model\Smallapp\OrderModel();
        $vinfo = $m_order->getInfo(array('id'=>$order_id));
        if(IS_GET){
            $einfo = array();
            if($eid){
                $einfo = $m_orderexpress->getInfo(array('id'=>$eid));
            }
            $express = new \Common\Lib\Express();
            $all_express = $express->getCompany();
            $result = array();
            foreach ($all_express as $v){
                if(!empty($einfo) && $v['comcode']==$einfo['comcode']){
                    $einfo['comname'] = $v['name'];
                    $v['is_select'] = 'selected';
                }else{
                    $v['is_select'] = '';
                }
                $result[]=$v;
            }
            $this->assign('eid',$eid);
            $this->assign('einfo',$einfo);
            $this->assign('express',$result);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }else{
            $enum = I('post.enum','','trim');
            $comcode = I('post.comcode','');
//            $res_express = $m_orderexpress->getInfo(array('order_id'=>$order_id));
//            if(!empty($res_express)){
//                $this->output('请勿重复录入物流单号', "dishorder/orderlist",2,0);
//            }
            $data = array('order_id'=>$order_id,'comcode'=>$comcode,'enum'=>$enum);
            if($eid){
                $m_orderexpress->updateData(array('id'=>$eid),$data);
            }else{
                $m_orderexpress->add($data);
            }
            if($vinfo['status']!=53){
                $res = $m_order->updateData(array('id'=>$order_id),array('status'=>53));
            }
            if($vinfo['add_time']>'2020-05-19 17:20:00'){
                $res = false;
            }

            if($res && $vinfo['otype']==5 && !empty($vinfo['sale_uid'])){
                $m_config = new \Admin\Model\SysConfigModel();
                $res_config = $m_config->getAllconfig();
                $profit = $res_config['distribution_profit'];

                $m_ordergoods = new \Admin\Model\Smallapp\OrdergoodsModel();
                $fields = 'og.goods_id,og.price,og.amount,goods.supply_price,goods.distribution_profit';
                $where = array('og.order_id'=>$vinfo['id']);
                $res_ordergoods = $m_ordergoods->getOrdergoodsList($fields,$where,'og.id asc');

                $add_data = array();
                foreach ($res_ordergoods as $v){
                    if($v['distribution_profit']>0){
                        $profit = $v['distribution_profit'];
                    }
                    $income_fee = 0;
                    if($v['price']>$v['supply_price']){
                        $income_fee = ($v['price']-$v['supply_price'])*$profit;
                        $income_fee = sprintf("%.2f",$income_fee);
                    }
                    $total_fee = sprintf("%.2f",$v['price']*$v['amount']);
                    $add_data[] = array('user_id'=>$vinfo['sale_uid'],'openid'=>$vinfo['openid'],'order_id'=>$vinfo['id'],
                        'goods_id'=>$v['goods_id'],'price'=>$v['price'],'supply_price'=>$v['supply_price'],'amount'=>$v['amount'],
                        'total_fee'=>$total_fee,'income_fee'=>$income_fee, 'profit'=>$profit
                    );
                }
                $m_income = new \Admin\Model\Smallapp\UserincomeModel();
                $m_income->addAll($add_data);
            }
            if($eid){
                $nav_url = 'dishorder/expresslist';
            }else{
                $nav_url = 'dishorder/orderlist';
            }
            $this->output('发货成功', $nav_url);

        }
    }

    public function getexpress(){
        $express_id = I('eid',0,'intval');
        $order_id = I('id',0,'intval');
        $url = 'http://'.C('SAVOR_API_URL')."/Smallsale19/express/getExpress?order_id=$order_id&express_id=$express_id";
        $curl = new \Common\Lib\Curl();
        $response = json_encode(array());
        $curl::get($url,$response,5);
        $res = json_decode($response,true);
        $vinfo = array();
        if(!empty($res['result'])){
            $vinfo = $res['result'];
        }
        $vinfo['order_id'] = $order_id;
        $this->assign('vinfo',$vinfo);
        $this->display('express');

    }


    public function autonumber(){
        $enum = I('enum','');
        $url = 'http://'.C('SAVOR_API_URL')."/Smallsale19/express/autonumber?enum=$enum";
        $curl = new \Common\Lib\Curl();
        $response = json_encode(array());
        $curl::get($url,$response,5);
        $res = json_decode($response,true);
        $html_str = '';
        if(!empty($res['result'])){
            foreach ($res['result'] as $k=>$v){
                $select_str = "";
                if($k==0){
                    $select_str = "selected";
                }
                $html_str.="<option value='{$v['comcode']}' $select_str>{$v['name']}</option>";
            }
        }
        echo $html_str;
    }



}