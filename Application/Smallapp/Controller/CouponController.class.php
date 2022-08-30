<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class CouponController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keywords = I('keywords','','trim');
        $type = I('type',0,'intval');
        $status = I('status',0,'intval');

        $m_coupon = new \Admin\Model\Smallapp\CouponModel();
        $where = array();
        if($keywords){
            $where['name'] = array('like',"%$keywords%");
        }
        if($type){
            $where['type'] = $type;
        }
        if($status){
            $where['status'] = $status;
        }

        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_coupon->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_couponhotel = new \Admin\Model\Smallapp\CouponHotelModel();
            $all_types = C('COUPON_TYPES');
            foreach ($res_list['list'] as $v){
                $v['min_price'] = '满'.$v['min_price'].'可用';
                $v['date_str'] = $v['start_time'].'到'.$v['end_time'];
                if($v['status']==1){
                    $v['status_str'] = '正常';
                }else{
                    $v['status_str'] = '禁用';
                }
                $v['type_str'] = $all_types[$v['type']];
                if($v['type']==2){
                    $fields = "count(DISTINCT hotel_id) as num";
                    $res_couponhotel = $m_couponhotel->getRow($fields,array('coupon_id'=>$v['id']),'id desc');
                    $hotels = intval($res_couponhotel['num']);
                }else{
                    $hotels = '';
                }
                $v['hotels'] = $hotels;
                $data_list[] = $v;
            }
        }
        $this->assign('type',$type);
        $this->assign('status',$status);
        $this->assign('keywords',$keywords);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function couponadd(){
        $id = I('id',0,'intval');
        $m_coupon = new \Admin\Model\Smallapp\CouponModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $money = I('post.money',0,'intval');
            $min_price = I('post.min_price',0,'intval');
            $remark = I('post.remark','','trim');
            $start_time = I('post.start_time');
            $end_time = I('post.end_time');
            $status = I('post.status',0,'intval');
            $type = I('post.type',0,'intval');
            $use_range = I('post.use_range',0,'intval');
            $range_finance_goods_ids = I('post.range_finance_goods_ids','');
            $start_hour = I('post.start_hour',0,'intval');

            $data = array('name'=>$name,'money'=>$money,'min_price'=>$min_price,'remark'=>$remark,
                'start_time'=>$start_time,'end_time'=>$end_time,'type'=>$type,'use_range'=>$use_range,
                'start_hour'=>$start_hour,'status'=>$status);
            if($type==2){
                if($use_range==0){
                    $this->output('请选择使用范围', 'coupon/couponadd',2,0);
                }
                if($use_range==2){
                    if(empty($range_finance_goods_ids)){
                        $this->output('请选择部分酒水', 'coupon/couponadd',2,0);
                    }else{
                        $goods_ids_str = join(',',$range_finance_goods_ids);
                        $data['range_finance_goods_ids'] = ",$goods_ids_str,";
                    }
                }
            }
            if($start_hour==0 && empty($start_time)){
                $this->output('请选择开始时间或小时', 'coupon/couponadd',2,0);
            }
            if(empty($end_time)){
                $this->output('请选择结束时间', 'coupon/couponadd',2,0);
            }

            if($id){
                $result = $m_coupon->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_coupon->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'coupon/datalist');
            }else{
                $this->output('操作失败', 'coupon/datalist',2,0);
            }
        }else{
            $vinfo = array('status'=>1);
            $range_finance_goods_ids = array();
            if($id){
                $vinfo = $m_coupon->getInfo(array('id'=>$id));
                if(!empty($vinfo['range_finance_goods_ids'])){
                    $range_finance_goods_ids = explode(',',trim($vinfo['range_finance_goods_ids'],','));
                }
            }
            $m_finance_goods = new \Admin\Model\FinanceGoodsModel();
            $res_goods = $m_finance_goods->getDataList('id,name',array('status'=>1),'id desc');
            foreach ($res_goods as $k=>$v){
                $select = '';
                if(in_array($v['id'],$range_finance_goods_ids)){
                    $select = 'selected';
                }
                $res_goods[$k]['select'] = $select;
            }
            $this->assign('goods',$res_goods);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function hotelcouponadd(){
        $coupon_id = I('coupon_id',0,'intval');
        if(IS_POST){
            $hbarr = $_POST['hbarr'];
            if(empty($hbarr)){
                $this->output('请选择酒楼','coupon/datalist',2,0);
            }
            $hotel_arr = json_decode($hbarr, true);
            if(empty($hotel_arr)){
                $this->output('请选择酒楼','coupon/datalist',2,0);
            }
            $m_couponhotel = new \Admin\Model\Smallapp\CouponHotelModel();
            foreach ($hotel_arr as $v){
                $hotel_id = $v['hotel_id'];
                $where = array('hotel_id'=>$hotel_id,'coupon_id'=>$coupon_id);
                $res = $m_couponhotel->where($where)->find();
                if(empty($res)){
                    $m_couponhotel->add($where);
                }
            }
            $this->output('添加成功','coupon/datalist');
        }else{
            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();
            $m_coupon  = new \Admin\Model\Smallapp\CouponModel();
            $dinfo = $m_coupon->getInfo(array('id'=>$coupon_id));
            $this->assign('vinfo', $dinfo);
            $this->assign('areainfo', $area_arr);
            $this->display('hotelcouponadd');
        }
    }

    public function hotelcouponlist() {
        $coupon_id = I('coupon_id',0,'intval');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array('a.coupon_id'=>$coupon_id);
        if(!empty($keyword)){
            $where['h.name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $fields = 'a.id,a.add_time,h.id as hotel_id,h.name as hotel_name';
        $m_couponhotel = new \Admin\Model\Smallapp\CouponHotelModel();
        $result = $m_couponhotel->getHotelCouponList($fields,$where,'a.id desc', $start,$size);
        $datalist = $result['list'];

        $this->assign('coupon_id',$coupon_id);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('hotelcouponlist');
    }

    public function hotelcoupondel(){
        $id = I('get.id',0,'intval');
        $m_couponhotel = new \Admin\Model\Smallapp\CouponHotelModel();
        $result = $m_couponhotel->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'coupon/hotelcouponlist',2);
        }else{
            $this->output('操作失败', 'coupon/hotelcouponlist',2,0);
        }
    }

    public function usercouponlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $hotel_name = I('hotel_name','','trim');
        $ustatus = I('ustatus',0,'intval');

        $where = array('coupon.type'=>2);
        if($ustatus){
            $where['a.ustatus'] = $ustatus;
        }
        if(!empty($hotel_name)){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }

        $start = ($pageNum-1)*$size;
        $orderby = 'a.id desc';
        $fields = 'a.id,a.coupon_id,a.money,a.add_time,a.end_time,a.use_time,a.hotel_id,a.type,hotel.name as hotel_name,
        user.nickName as user_name,a.op_openid,activity.type as activity_type,a.ustatus';
        $m_coupon = new \Admin\Model\Smallapp\UserCouponModel();
        $res_list = $m_coupon->getUserCouponList($fields,$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $all_status = C('COUPON_STATUS');
            $all_source = array('1'=>'售酒抽奖','2'=>'会员礼包');
            $m_user = new \Admin\Model\Smallapp\UserModel();
            foreach ($res_list['list'] as $v){
                $res_user = $m_user->getOne('nickName',array('openid'=>$v['op_openid']),'id desc');
                $v['sale_name'] = $res_user['nickname'];
                $source = $all_source[$v['type']];
                if($v['use_time']=='0000-00-00 00:00:00'){
                    $v['use_time'] = '';
                }
                $v['source'] = $source;
                $v['status_str'] = $all_status[$v['ustatus']];

                $data_list[] = $v;
            }
        }
        $this->assign('ustatus',$ustatus);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }


}