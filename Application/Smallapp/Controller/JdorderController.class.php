<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

/**
 * @desc 订单管理
 *
 */
class JdorderController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function orderlist() {
        $all_valid_codes = array(
            '-1'=>'未知','2'=>'无效-拆单','3'=>'无效-取消','4'=>'无效-京东帮帮主订单','5'=>'无效-账号异常',
            '6'=>'无效-赠品类目不返佣','7'=>'无效-校园订单','8'=>'无效-企业订单','9'=>'无效-团购订单','10'=>'无效-开增值税专用发票订单',
            '11'=>'无效-乡村推广员下单','12'=>'无效-自己推广自己下单','13'=>'无效-违规订单','14'=>'无效-来源与备案网址不符',
            '15'=>'待付款','16'=>'已付款','17'=>'已完成','18'=>'已结算'
        );
        $start_date = I('post.start_date','');
        $end_date = I('post.end_date','');
    	$status = I('status',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array();
        if($status){
            $where['valid_code'] = $status;
        }
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'goods/goodsadd', 2, 0);
            }
            if($stime==$etime){
                $where["FROM_UNIXTIME(order_time,'%Y-%m-%d')"] = date('Y-m-d',$stime);
            }else{
                $start_time = date('Y-m-d',$stime);
                $end_time = date('Y-m-d',$etime);
                $where["FROM_UNIXTIME(order_time,'%Y-%m-%d')"] = array(array('egt',$start_time),array('elt',$end_time), 'and');
            }
        }
        $start  = ($page-1) * $size;
        $m_order  = new \Admin\Model\Smallapp\JdorderModel();
        $result = $m_order->getDataList('*',$where, 'id desc', $start, $size);
        $datalist = $result['list'];
        $m_goods = new \Admin\Model\Smallapp\GoodsModel();
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_invite_code = new \Admin\Model\HotelInviteCodeModel();
        foreach ($datalist as $k=>$v){
            $sku_id = $v['sku_id'];
            $res_goods = $m_goods->getInfo(array('item_id'=>$sku_id));
            $integral = intval($res_goods['rebate_integral']*$v['sku_num']);
            $datalist[$k]['integral'] = $integral;
            $datalist[$k]['goods_name'] = $res_goods['name'];
            $datalist[$k]['order_time'] = date('Y-m-d H:i:s',$v['order_time']);
            if($v['finish_time']){
                $datalist[$k]['finish_time'] = date('Y-m-d H:i:s',$v['finish_time']);
            }else{
                $datalist[$k]['finish_time'] = '';
            }
            $datalist[$k]['status_str'] = $all_valid_codes[$v['valid_code']];
            $user_info = $m_user->getOne('openid,nickName',array('id'=>$v['sub_union_id']),'id desc');
            $datalist[$k]['openid'] = $user_info['openid'];
            $datalist[$k]['nickName'] = $user_info['nickname'];

            $res_invite_code = $m_invite_code->getInviteExcel('ht.name',array('a.openid'=>$user_info['openid'],'a.flag'=>0),'ht.id desc');
            $datalist[$k]['hotel_name'] = $res_invite_code[0]['name'];

        }

        $this->assign('all_valid_codes',$all_valid_codes);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('status',$status);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('orderlist');
    }

}