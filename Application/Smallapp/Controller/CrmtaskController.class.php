<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class CrmtaskController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $type = I('type',0,'intval');
        $status = I('status',0,'intval');

        $m_crmtask = new \Admin\Model\Smallapp\CrmtaskModel();
        $where = array();
        if($type){
            $where['type'] = $type;
        }
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_crmtask->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_sysuser = new \Admin\Model\UserModel();
            $all_types = C('CRM_TASK_TYPES');
            $all_status = array('1'=>'正常','2'=>'禁用');
            foreach ($res_list['list'] as $v){
                if($v['update_time']=='0000-00-00 00:00:00'){
                    $v['update_time'] = '';
                }
                $res_suser = $m_sysuser->getUserInfo($v['sysuser_id']);
                $v['sys_uname'] = $res_suser['remark'];
                $v['type_str'] = $all_types[$v['type']];
                $v['status_str'] = $all_status[$v['status']];
                $data_list[] = $v;
            }
        }
        $this->assign('type',$type);
        $this->assign('status',$status);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function addtask(){
        $id = I('id',0,'intval');
        $m_crmtask = new \Admin\Model\Smallapp\CrmtaskModel();
        $vinfo = $m_crmtask->getInfo(array('id'=>$id));
        $task_type = $vinfo['type'];
        if(IS_POST){
            $name = I('post.name','','trim');
            $sale_manager_num = I('post.sale_manager_num',0,'intval');
            $cate_num = I('post.cate_num',0,'intval');
            $stock_num = I('post.stock_num',0,'intval');
            $task_finish_rate = I('post.task_finish_rate',0);
            $task_finish_day = I('post.task_finish_rate',0,'intval');
            $residenter_ids = I('post.residenter_ids');
            $is_upimg = I('post.is_upimg',0,'intval');
            $is_check_location = I('post.is_check_location',0,'intval');
            $start_time = I('post.start_time');
            $end_time = I('post.end_time');
            $desc = I('post.desc','','trim');
            $notify_day = I('post.notify_day',0,'intval');
            $notify_handle_day = I('post.notify_handle_day',0,'intval');
            $status = I('post.status',2,'intval');

            $sysuserInfo = session('sysUserInfo');
            $sysuser_id = $sysuserInfo['id'];
            $update_time = date('Y-m-d H:i:s');

            if(!empty($residenter_ids)){
                $residenter_ids = join(',',$residenter_ids);
            }else{
                $residenter_ids = '';
            }
            $updata = array('name'=>$name,'sale_manager_num'=>$sale_manager_num,'cate_num'=>$cate_num,'stock_num'=>$stock_num,
                'task_finish_rate'=>$task_finish_rate,'task_finish_day'=>$task_finish_day,'is_upimg'=>$is_upimg,'is_check_location'=>$is_check_location,
                'start_time'=>$start_time,'end_time'=>$end_time,'desc'=>$desc,'notify_day'=>$notify_day,'notify_handle_day'=>$notify_handle_day,
                'residenter_ids'=>$residenter_ids,'status'=>$status,'update_time'=>$update_time,'sysuser_id'=>$sysuser_id
                );
            $m_crmtask->updateData(array('id'=>$id),$updata);

            $this->output('操作成功!', 'crmtask/datalist');
        }else{
            $all_jump_url = array('1'=>'addopensale','2'=>'adddeliverdrinks','3'=>'addarrears','4'=>'addoverduearrears','5'=>'addroom',
                '6'=>'addinvitation','7'=>'addcheck','8'=>'adddemand','9'=>'addboot','10'=>'addwechat','11'=>'addcustom'
            );
            $display_html = $all_jump_url[$task_type];
            if($task_type==11){
                $all_residenter_ids = explode(',',$vinfo['residenter_ids']);
                $m_opuser_role = new \Admin\Model\OpuserroleModel();
                $fields = 'a.user_id as main_id,user.remark';
                $where = array('a.state'=>1,'user.status'=>1,'a.role_id'=>array('in',array(1,3)),'user.id'=>array('gt',0));
                $residenter_list = $m_opuser_role->getAllRole($fields,$where,'' );
                foreach ($residenter_list as $k=>$v){
                    $is_select = '';
                    if(in_array($v['main_id'],$all_residenter_ids)){
                        $is_select = 'selected';
                    }
                    $residenter_list[$k]['is_select'] = $is_select;
                }
                $this->assign('residenter_list',$residenter_list);
            }
            $this->assign('vinfo',$vinfo);
            $this->display($display_html);
        }
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
        $start_date = I('start_date','');
        $end_date = I('end_date','');
        $idcode = I('idcode','','trim');
        $openid = I('openid','','trim');
        $ustatus = I('ustatus',0,'intval');
        $wxpay_status = I('wxpay_status',0,'intval');

        $where = array('coupon.type'=>2,'coupon.status'=>1,'a.status'=>1);
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'coupon/usercouponlist', 2, 0);
            }
            $ustatus = 2;
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['a.use_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        if($ustatus){
            $where['a.ustatus'] = $ustatus;
        }
        if(!empty($idcode)){
            $where['a.idcode'] = $idcode;
        }
        if($wxpay_status){
            $where['a.wxpay_status'] = $wxpay_status;
        }
        if(!empty($openid)){
            $where['a.openid'] = $openid;
        }
//        if(!empty($hotel_name)){
//            $where['hotel.name'] = array('like',"%$hotel_name%");
//        }

        $start = ($pageNum-1)*$size;
        $orderby = 'a.id desc';
        $fields = 'a.id,a.openid,a.coupon_id,a.money,a.add_time,a.end_time,a.use_time,a.hotel_id,a.type,hotel.name as hotel_name,
        user.nickName as user_name,user.mobile as user_mobile,a.op_openid,a.idcode,activity.type as activity_type,a.ustatus,a.wxpay_status';
        $m_coupon = new \Admin\Model\Smallapp\UserCouponModel();
        $res_list = $m_coupon->getUserCouponList($fields,$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $all_status = C('COUPON_STATUS');
            $all_source = array('1'=>'售酒抽奖','2'=>'会员礼包');
            $m_staff = new \Admin\Model\Integral\StaffModel();
            foreach ($res_list['list'] as $v){
                $ufields = 'h.id as hotel_id,h.name as hotel_name,u.nickName';
                $uwhere = array('a.openid'=>$v['op_openid'],'a.status'=>1,'m.status'=>1);
                $res_user = $m_staff->getMerchantStaffUserList($ufields,$uwhere);
                $v['sale_name'] = $res_user[0]['nickname'];
                $v['use_hotel_id'] = $res_user[0]['hotel_id'];
                $v['use_hotel_name'] = $res_user[0]['hotel_name'];
                $source = $all_source[$v['type']];
                if($v['use_time']=='0000-00-00 00:00:00'){
                    $v['use_time'] = '';
                }
                $v['source'] = $source;
                $v['status_str'] = $all_status[$v['ustatus']];
                if($v['hotel_id']==0){
                    $v['hotel_name'] = '多酒楼使用';
                }

                $data_list[] = $v;
            }
        }
        $this->assign('ustatus',$ustatus);
        $this->assign('openid',$openid);
        $this->assign('idcode',$idcode);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->assign('wxpay_status',$wxpay_status);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->display();
    }

    public function paylog(){
        $coupon_uid = I('coupon_uid',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $start = ($pageNum-1)*$size;
        $where = array('coupon_user_id'=>$coupon_uid);
        $m_paylog = new \Admin\Model\Smallapp\PaylogModel();
        $res_list = $m_paylog->getDataList('*',$where,'id desc',$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_hotel = new \Admin\Model\HotelModel();
            $m_user = new \Admin\Model\Smallapp\UserModel();
            foreach ($res_list['list'] as $v){
                $pay_result = json_decode($v['pay_result'],true);
                $pay_result_str = '';
                foreach ($pay_result['wxresult'] as $pk=>$pv){
                    $pv_str = $pv;
                    if(is_array($pv)){
                        $pv_str = '';
                        foreach ($pv as $pvk=>$pvv){
                            $pv_str.="$pvk=$pvv ";
                        }
                    }
                    $pay_result_str.="$pk:$pv_str ";
                }
                $v['pay_result_str'] = $pay_result_str;
                $hotel_name = '';
                $username = '';
                if($v['hotel_id']){
                    $res_hotel = $m_hotel->getOne($v['hotel_id']);
                    $hotel_name = $res_hotel['name'];
                }
                if(!empty($v['openid'])){
                    $res_user = $m_user->getOne('nickName,mobile',array('openid'=>$v['openid']),'id desc');
                    $username = $res_user['nickname']."({$res_user['mobile']})";
                }
                $v['hotel_name'] = $hotel_name;
                $v['username'] = $username;
                $data_list[]=$v;
            }

        }
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->assign('coupon_uid',$coupon_uid);
        $this->display();

    }


}