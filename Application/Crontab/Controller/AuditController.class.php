<?php
namespace Crontab\Controller;
use Think\Controller;

class AuditController extends Controller{

    public function writeoffdata(){
        $now_time = date('Y-m-d H:i:s');
        echo "writeoffdata start:$now_time \r\n";

        $start_time = '2023-10-08 00:00:00';
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $where = array('type'=>7,'wo_status'=>1,'dstatus'=>1);
        $where['add_time'] = array('egt',$start_time);
        $field = 'id,idcode,wo_reason_type,stock_id,goods_id,unit_id,op_openid';
        $res_data = $m_stock_record->getAllData($field,$where,'id asc');
        if(empty($res_data)){
            $now_time = date('Y-m-d H:i:s');
            echo "time:$now_time no data \r\n";
            exit;
        }

        $m_goodsconfig = new \Admin\Model\FinanceGoodsConfigModel();
        $m_unit = new \Admin\Model\FinanceUnitModel();
        $m_stock = new \Admin\Model\FinanceStockModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $m_staff = new \Admin\Model\Integral\StaffModel();
        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
        $m_integralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();

        foreach ($res_data as $recordv){
            $stock_record_id = $recordv['id'];
            $wo_reason_type = $recordv['wo_reason_type'];
            $idcode = $recordv['idcode'];
            $goods_id = $recordv['goods_id'];
            $stock_id = $recordv['stock_id'];

            $data = array('wo_status'=>2,'update_time'=>date('Y-m-d H:i:s'),
                'recycle_status'=>2,'recycle_time'=>date('Y-m-d H:i:s'));
            $m_stock_record->updateData(array('id'=>$stock_record_id), $data);

            $res_goodsintegral = $m_goodsconfig->getInfo(array('goods_id'=>$goods_id,'type'=>10));
            if(empty($res_goodsintegral) || $res_goodsintegral['integral']==0){
                echo "stock_record_id:$stock_record_id,goods_id:$goods_id,integral:0 \r\n";
                continue;
            }
            $res_stock = $m_stock->getInfo(array('id'=>$stock_id));
            if(empty($res_stock) || $res_stock['hotel_id']==0){
                echo "stock_record_id:$stock_record_id,stock_id:$stock_id error \r\n";
                continue;
            }
            if($wo_reason_type!=1){
                echo "stock_record_id:$stock_record_id,wo_reason_type:$wo_reason_type error \r\n";
                continue;
            }
            $now_integral = $res_goodsintegral['integral'];
            $res_unit = $m_unit->getInfo(array('id'=>$recordv['unit_id']));
            $unit_num = intval($res_unit['convert_type']);
            $now_integral = $now_integral*$unit_num;
            $integral_status = 1;

            $where = array('a.openid'=>$recordv['op_openid'],'a.status'=>1,'merchant.status'=>1);
            $field_staff = 'a.openid,a.level,merchant.type,merchant.id as merchant_id,merchant.is_integral,merchant.is_shareprofit,merchant.shareprofit_config';
            $res_staff = $m_staff->getMerchantStaff($field_staff,$where);
            $admin_integral = 0;
            $admin_openid = '';

            $adminwhere = array('merchant_id'=>$res_staff[0]['merchant_id'],'level'=>1,'status'=>1);
            $res_admin_staff = $m_staff->getAll('id,openid',$adminwhere,0,1,'id desc');
            $admin_openid = $res_admin_staff[0]['openid'];
            if($res_staff[0]['is_integral']==1){
                //开瓶费积分 增加分润
                if($res_staff[0]['is_shareprofit']==1 && $res_staff[0]['level']==2){
                    $shareprofit_config = json_decode($res_staff[0]['shareprofit_config'],true);
                    if(!empty($shareprofit_config['kpf'])){
                        $staff_integral = ($shareprofit_config['kpf'][1]/100)*$now_integral;
                        if($staff_integral>1){
                            $staff_integral = round($staff_integral);
                        }else{
                            $staff_integral = 1;
                        }
                        $admin_integral = $now_integral - $staff_integral;
                        $now_integral = $staff_integral;
                    }
                }
                $integralrecord_openid = $recordv['op_openid'];
                if($admin_integral>0){
                    if(!empty($res_admin_staff)){
                        $res_integral = $m_userintegral->getInfo(array('openid'=>$admin_openid));
                        if(!empty($res_integral)){
                            $userintegral = $res_integral['integral']+$admin_integral;
                            $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                        }else{
                            $m_userintegral->add(array('openid'=>$admin_openid,'integral'=>$admin_integral));
                        }
                    }
                }
                $res_integral = $m_userintegral->getInfo(array('openid'=>$recordv['op_openid']));
                if(!empty($res_integral)){
                    $userintegral = $res_integral['integral']+$now_integral;
                    $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                }else{
                    $m_userintegral->add(array('openid'=>$recordv['op_openid'],'integral'=>$now_integral));
                }
            }else{
                $integralrecord_openid = $res_stock['hotel_id'];
                $where = array('id'=>$res_staff[0]['merchant_id']);
                $m_merchant->where($where)->setInc('integral',$now_integral);
            }
            $field = 'hotel.id as hotel_id,hotel.name as hotel_name,hotel.hotel_box_type,area.id as area_id,area.region_name as area_name';
            $where = array('hotel.id'=>$res_stock['hotel_id']);
            $res_hotel = $m_hotel->getHotelById($field,$where);
            if($admin_integral>0 && !empty($admin_openid)){
                $integralrecord_data = array('openid'=>$admin_openid,'area_id'=>$res_hotel['area_id'],'area_name'=>$res_hotel['area_name'],
                    'hotel_id'=>$res_hotel['hotel_id'],'hotel_name'=>$res_hotel['hotel_name'],'hotel_box_type'=>$res_hotel['hotel_box_type'],
                    'integral'=>$admin_integral,'jdorder_id'=>$stock_record_id,'content'=>1,'status'=>$integral_status,
                    'type'=>17,'integral_time'=>date('Y-m-d H:i:s'),'source'=>4);
                $m_integralrecord->add($integralrecord_data);
            }
            $integralrecord_data = array('openid'=>$integralrecord_openid,'area_id'=>$res_hotel['area_id'],'area_name'=>$res_hotel['area_name'],
                'hotel_id'=>$res_hotel['hotel_id'],'hotel_name'=>$res_hotel['hotel_name'],'hotel_box_type'=>$res_hotel['hotel_box_type'],
                'integral'=>$now_integral,'jdorder_id'=>$stock_record_id,'content'=>1,'status'=>$integral_status,'type'=>17,
                'integral_time'=>date('Y-m-d H:i:s'));
            $m_integralrecord->add($integralrecord_data);
            //end

            //邀请新会员(优惠券任务) 审核通过后立即发放积分
            $res_recordinfo = $m_integralrecord->getAllData('*',array('jdorder_id'=>$idcode,'type'=>18,'status'=>2),'id desc');
            if(!empty($res_recordinfo)){
                $where = array('hotel_id'=>$res_recordinfo[0]['hotel_id'],'status'=>1);
                $field_merchant = 'id as merchant_id,is_integral,is_shareprofit,shareprofit_config';
                $res_merchant = $m_merchant->getRow($field_merchant,$where,'id desc');
                $is_integral = $res_merchant['is_integral'];
                foreach ($res_recordinfo as $v){
                    $m_integralrecord->updateData(array('id'=>$v['id']),array('status'=>1,'integral_time'=>date('Y-m-d H:i:s')));

                    $now_integral = $v['integral'];
                    if($is_integral==1){
                        $res_integral = $m_userintegral->getInfo(array('openid'=>$v['openid']));
                        if(!empty($res_integral)){
                            $userintegral = $res_integral['integral']+$now_integral;
                            $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                        }else{
                            $m_userintegral->add(array('openid'=>$v['openid'],'integral'=>$now_integral));
                        }
                    }else{
                        $where = array('id'=>$res_merchant['merchant_id']);
                        $m_merchant->where($where)->setInc('integral',$now_integral);
                    }
                }
            }
            //end
            //会员复购奖励 增加分润
            $res_recordinfo = $m_integralrecord->getAllData('*',array('jdorder_id'=>$idcode,'type'=>19,'status'=>2),'id desc');
            if(!empty($res_recordinfo)){
                $where = array('hotel_id'=>$res_recordinfo[0]['hotel_id'],'status'=>1);
                $field_merchant = 'id as merchant_id,is_integral,is_shareprofit,shareprofit_config';
                $res_merchant = $m_merchant->getRow($field_merchant,$where,'id desc');
                $is_integral = $res_merchant['is_integral'];
                foreach ($res_recordinfo as $v){
                    $m_integralrecord->updateData(array('id'=>$v['id']),array('status'=>1,'integral_time'=>date('Y-m-d H:i:s')));

                    $now_integral = $v['integral'];
                    if($is_integral==1){
                        $res_integral = $m_userintegral->getInfo(array('openid'=>$v['openid']));
                        if(!empty($res_integral)){
                            $userintegral = $res_integral['integral']+$now_integral;
                            $m_userintegral->updateData(array('id'=>$res_integral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                        }else{
                            $m_userintegral->add(array('openid'=>$v['openid'],'integral'=>$now_integral));
                        }
                    }else{
                        $where = array('id'=>$res_merchant['merchant_id']);
                        $m_merchant->where($where)->setInc('integral',$now_integral);
                    }
                }
            }
            echo "stock_record_id:$stock_record_id ok \r\n";
        }

        $now_time = date('Y-m-d H:i:s');
        echo "writeoffdata end:$now_time \r\n";
    }
}
