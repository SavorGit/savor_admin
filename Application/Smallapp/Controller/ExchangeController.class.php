<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
use Common\Lib\Curl;

/**
 * @desc 兑换管理
 *
 */
class ExchangeController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function exchangelist() {
        $start_date = I('start_date','');
        $end_date = I('end_date','');
        $is_audit = I('is_audit',99,'intval');
        $area_id = I('area_id',0,'intval');
        $status = I('status',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');
        $hotel_name = I('hotel_name','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array();
        if($area_id)    $where['area.id']=$area_id;
        if($status)    $where['a.status']=$status;
        if($maintainer_id)    $where['ext.maintainer_id']=$maintainer_id;
        if(!empty($hotel_name)) $where['hotel.name'] = array('like',"%$hotel_name%");
        if($is_audit!=99){
            $where['goods.is_audit']=$is_audit;
        }
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'exchange/exchangelist', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        $fields = 'a.id,a.openid,a.hotel_id,a.goods_id,a.price,a.amount,a.total_fee,a.status,a.sysuser_id,a.add_time,a.audit_status,goods.is_audit,goods.type as goods_type,hotel.name as hotel_name,area.region_name as city,ext.maintainer_id';

        $start  = ($page-1) * $size;
        $m_order  = new \Admin\Model\Smallapp\ExchangeModel();
        $result = $m_order->getExchangeList($fields,$where, 'a.id desc', $start, $size);
        $datalist = $result['list'];

        $audit_types = array(99=>'全部',0=>'无需审核',1=>'需审核');
        $audit_status = array(0=>'',1=>'审核通过',2=>'审核不通过');
        $order_status = C('EXCHANGE_STATUS');
        $user_ids = array();
        $open_ids = array();
        foreach ($datalist as $k=>$v){
            $user_ids[] = $v['sysuser_id'];
            if($v['maintainer_id']){
                $user_ids[] = $v['maintainer_id'];
            }
            $open_ids[] = $v['openid'];
            $datalist[$k]['typestr'] = $audit_types[$v['is_audit']];
            $datalist[$k]['integral'] = $v['total_fee'];
            $datalist[$k]['statusstr'] = $order_status[$v['status']];
            $datalist[$k]['audit_statusstr'] = $audit_status[$v['audit_status']];
            $datalist[$k]['goods_type'] = $v['goods_type'];
        }

        $user_ids = array_unique($user_ids);
        $m_sysuser = new \Admin\Model\UserModel();
        $where = array('id'=>array('in',join(',',$user_ids)));
        $res_user = $m_sysuser->where($where)->order('id desc')->select();
        $user = array();
        foreach ($res_user as $v){
            $user[$v['id']] = $v['remark'];
        }
        $open_ids = array_unique($open_ids);
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $where = array('openid'=>array('in',join(',',$open_ids)));
        $res_small_user = $m_user->getWhere('openid,nickName',$where,'id desc','','');
        $small_users = array();
        foreach ($res_small_user as $v){
            $small_users[$v['openid']] = $v['nickname'];
        }
        foreach ($datalist as $k=>$v){
            $sysuser_id = $v['sysuser_id'];
            $maintaineru_id = $v['maintainer_id'];
            $open_id = $v['openid'];
            $datalist[$k]['user_name'] = $small_users[$open_id];
            $datalist[$k]['creater'] = $user[$sysuser_id];
            $datalist[$k]['maintainer'] = $user[$maintaineru_id];
        }

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        $opusers = $this->getOpuser($maintainer_id);

        $this->assign('is_audit',$is_audit);
        $this->assign('audit_types',$audit_types);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('area_id',$area_id);
        $this->assign('status',$status);
        $this->assign('maintainer_id',$maintainer_id);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('area', $area_arr);
        $this->assign('opusers', $opusers);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('exchangelist');
    }

    public function exchange(){
        $order_id = intval($_REQUEST['id']);
        $goods_type = I('goods_type',0,'intval');
        $m_order = new \Admin\Model\Smallapp\ExchangeModel();
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
                    $this->output('商品积分不能为0', 'exchange/exchangelist',2);
                }

                $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
                $res_userintegral = $m_userintegral->getInfo(array('openid'=>$res_order['openid']));
                $userintegral = $res_userintegral['integral'];
                if($integral>$userintegral){
                    $this->output('用户积分不能兑换此商品', 'exchange/exchangelist',2);
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

                $this->output($message, 'exchange/exchangelist');

            }else{
                $res_order['goods_name'] = $goods_info['name'];
                $res_order['goods_integral'] = intval($res_order['amount']*$goods_info['rebate_integral']);
                $this->assign('goods_type',$goods_type);
                $this->assign('vinfo',$res_order);
                $this->display('exchange');
            }
        }else{
            if(IS_POST){
                $userinfo = session('sysUserInfo');
                $sysuser_id = $userinfo['id'];

                if($res_order['status']==21){
                    $this->output('请勿重复兑换', 'exchange/exchangelist',2);
                }
                $audit_status = I('post.audit_status',0,'intval');//1审核通过 2审核不通过
                if($audit_status==1){
                    $hash_ids_key = C('HASH_IDS_KEY_ADMIN');
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
                            $message = '用户无openid,无法提现';
                        }elseif($resapi['code']==99005){
                            $message = '用户积分不够,无法提现';
                        }else{
                            $message = '不满足兑换条件';
                        }
                    }else{
                        $message = '提现成功';
                        $m_order = new \Admin\Model\Smallapp\ExchangeModel();
                        $m_order->updateData(array('id'=>$order_id),array('status'=>21,'audit_status'=>$audit_status,'sysuser_id'=>$sysuser_id));
                    }
                    $this->output($message, 'exchange/exchangelist');
                }else{
                    $m_order = new \Admin\Model\Smallapp\ExchangeModel();
                    $m_order->updateData(array('id'=>$order_id),array('audit_status'=>$audit_status,'sysuser_id'=>$sysuser_id));

                    if($goods_info['rebate_integral']){
                        $integral = $goods_info['rebate_integral'];
                        $integralrecord_data = array('openid'=>$res_order['openid'],'integral' =>$integral,
                            'content'=>$res_order['goods_id'], 'type'=>4, 'integral_time' => date('Y-m-d H:i:s'));
                        $m_userintegralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
                        $m_userintegralrecord->add($integralrecord_data);
                        $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
                        $res_userintegral = $m_userintegral->getInfo(array('openid'=>$res_order['openid']));
                        $userintegral = $res_userintegral['integral'] + $integral;
                        $m_userintegral->updateData(array('id'=>$res_userintegral['id']),array('integral'=>$userintegral));
                    }
                    $this->output('审核不通过，积分已退回', 'exchange/exchangelist');
                }

            }else{
                $res_order['goods_name'] = $goods_info['name'];
                $res_order['goods_integral'] = intval($goods_info['rebate_integral']);
                $this->assign('goods_type',$goods_type);
                $this->assign('vinfo',$res_order);
                $this->display('wxchange');
            }
        }

    }

    private function getOpuser($maintainer_id=0){
        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $fields = 'a.user_id uid,user.remark ';
        $where = array('state'=>1,'role_id'=>1);
        $res_users = $m_opuser_role->getAllRole($fields,$where,'' );

        $opusers = array();
        foreach($res_users as $v){
            $uid = $v['uid'];
            $remark = $v['remark'];
            if($uid==$maintainer_id){
                $select = 'selected';
            }else{
                $select = '';
            }
            $firstCharter = getFirstCharter(cut_str($remark, 1));
            $opusers[$firstCharter][] = array('uid'=>$uid,'remark'=>$remark,'select'=>$select);
        }
        ksort($opusers);
        return $opusers;
    }

}