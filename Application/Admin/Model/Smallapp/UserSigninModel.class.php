<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class UserSigninModel extends BaseModel{
	protected $tableName='smallapp_user_signin';

	public function userintegral(){
        $nowtime = strtotime('-1 days');
        $now_date = date('Y-m-d',$nowtime);
        $begin_time = $now_date." 00:00:00";
        $end_time = $now_date." 23:59:59";

        $where = array();
        $where['add_time'] = array(array('egt',$begin_time),array('elt',$end_time), 'and');
        echo "$begin_time-$end_time \r\n";
        $res_data = $this->getDataList('*',$where,'id asc');
        if(empty($res_data)){
            echo "user_signin empty \r\n";
        }

        $m_hearlog = new \Admin\Model\HeartAllLogModel();
        $m_sysconfig = new \Admin\Model\SysConfigModel();
        $res_config = $m_sysconfig->getAllconfig();
        $m_box = new \Admin\Model\BoxModel();
        $m_order = new \Admin\Model\Smallapp\OrderModel();
        $m_goods = new \Admin\Model\Smallapp\GoodsModel();
        $m_forscreenrecord = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $m_userintegral = new \Admin\Model\Smallapp\UserIntegralModel();
        $m_userintegralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
        foreach ($res_data as $v){
            echo "ID: {$v['id']}  openid: {$v['openid']} begin \r\n";
            $signinfo = $this->checkSigninTime(strtotime($v['signin_time']));
            if($v['signout_time']=='0000-00-00 00:00:00'){
                if($signinfo['is_signin']){
                    $this->updateData(array('id'=>$v['id']),array('signout_time'=>$signinfo['signout_time']));
                    $v['signout_time'] = $signinfo['signout_time'];
                }
            }
            if($v['signout_time']=='0000-00-00 00:00:00'){
                continue;
            }
            $tmp_signout_time = strtotime($v['signout_time']);
            $tmp_signin_time = strtotime($signinfo['signin_time']);
            $diff_time =  $tmp_signout_time - $tmp_signin_time;
            if($diff_time<3600){
                continue;
            }

            $tmp_date = date('Ymd',$tmp_signin_time);
            $tmp_singin_h = date('H',$tmp_signin_time);
//            $tmp_signin_i = date('i',$tmp_signin_time);
//            $tmp_signin_diff_i = ceil(60-$tmp_signin_i/5);
            $tmp_signout_h = date('H',$tmp_signout_time);
            $res_logdate = $m_hearlog->getOne($v['box_mac'],2,$tmp_date);
            $online_hour = 0;
            for ($i=$tmp_singin_h;$i<=$tmp_signout_h;$i++){
                if($res_logdate["hour$i"]>=10){
                    $online_hour+=1;
                }
            }
            $now_integral = 0;
            $activity_boot_integral = $res_config['activity_boot_integral'];
            $box_info = $m_box->getHotelInfoByBoxMac($v['box_mac']);
            //开机积分
            if($online_hour){
                $boot_integral = $online_hour*$activity_boot_integral;
                $integralrecord_data = array('openid'=>$v['openid'],'area_id'=>$box_info['area_id'],
                    'area_name'=>$box_info['area_name'],'hotel_id'=>$box_info['hotel_id'],'hotel_name'=>$box_info['hotel_name'],
                    'hotel_box_type'=>$box_info['hotel_box_type'],'room_id'=>$box_info['room_id'],'room_name'=>$box_info['room_name'],
                    'box_id'=>$box_info['box_id'],'box_mac'=>$v['box_mac'],'box_type'=>$box_info['box_type'],
                    'integral'=>$boot_integral,'content'=>$online_hour,'type'=>1,'integral_time'=>$v['signout_time']);
                $m_userintegralrecord->add($integralrecord_data);
                $now_integral+=$boot_integral;
            }
            //互动积分
            $hd_begin_time = date('Y-m-d H:i:s',$tmp_signin_time);
            $hd_end_time = date('Y-m-d H:i:s',$tmp_signout_time);
            $activity_interact_integral = $res_config['activity_interact_integral'];
            $where = array('a.box_mac'=>$v['box_mac']);
            $where['a.create_time'] = array(array('EGT',$hd_begin_time),array('ELT',$hd_end_time));
            $where['a.mobile_brand'] = array('neq','devtools');
            $where['a.is_valid'] = 1;
            $integral_usernum = $m_forscreenrecord->countHdintegralUserNum($where);
            if($integral_usernum){
                $interact_integral = $integral_usernum*$activity_interact_integral;
                $integralrecord_data = array('openid'=>$v['openid'],'area_id'=>$box_info['area_id'],
                    'area_name'=>$box_info['area_name'],'hotel_id'=>$box_info['hotel_id'],'hotel_name'=>$box_info['hotel_name'],
                    'hotel_box_type'=>$box_info['hotel_box_type'],'room_id'=>$box_info['room_id'],'room_name'=>$box_info['room_name'],
                    'box_id'=>$box_info['box_id'],'box_mac'=>$v['box_mac'],'box_type'=>$box_info['box_type'],
                    'integral'=>$interact_integral,'content'=>$integral_usernum,'type'=>2,'integral_time'=>$v['signout_time']);
                $m_userintegralrecord->add($integralrecord_data);
                $now_integral+=$interact_integral;
            }
            //商品销售积分
            /*
            $sale_where = array('box_mac'=>$v['box_mac'],'otype'=>1);
            $res_order = $m_order->getDataList('goods_id',$sale_where,'id desc');
            if(!empty($res_order)){
                foreach ($res_order as $ov){
                    $goods_id = $ov['goods_id'];
                    $res_goods = $m_goods->getInfo(array('id'=>$goods_id));
                    if($res_goods['status']==2 && $res_goods['rebate_integral']){
                        $goods_integral = $res_goods['rebate_integral'];
                        $integralrecord_data = array('openid'=>$v['openid'],'area_id'=>$box_info['area_id'],
                            'area_name'=>$box_info['area_name'],'hotel_id'=>$box_info['hotel_id'],'hotel_name'=>$box_info['hotel_name'],
                            'hotel_box_type'=>$box_info['hotel_box_type'],'room_id'=>$box_info['room_id'],'room_name'=>$box_info['room_name'],
                            'box_id'=>$box_info['box_id'],'box_mac'=>$v['box_mac'],'box_type'=>$box_info['box_type'],
                            'integral'=>$goods_integral,'content'=>$goods_id,'type'=>3,'integral_time'=>$v['signout_time']);
                        $m_userintegralrecord->add($integralrecord_data);
                        $now_integral+=$goods_integral;
                    }
                }
            }
            */
            if($now_integral){
                $res_userintegral = $m_userintegral->getInfo(array('openid'=>$v['openid']));
                if(!empty($res_userintegral)){
                    $userintegral = $res_userintegral['integral']+$now_integral;
                    $m_userintegral->updateData(array('id'=>$res_userintegral['id']),array('integral'=>$userintegral,'update_time'=>date('Y-m-d H:i:s')));
                }else{
                    $integraldata = array('openid'=>$v['openid'],'integral'=>$now_integral,'update_time'=>date('Y-m-d H:i:s'));
                    $m_userintegral->add($integraldata);
                }
            }

            echo "ID: {$v['id']}  openid: {$v['openid']} ok \r\n";
        }

    }

    private function checkSigninTime($signin_time){
        $is_signin = 0;
        $feast_time = C('FEAST_TIME');

        $pre_time = date('Y-m-d H:i',$signin_time);
        $pre_date = date('Y-m-d',$signin_time);

        $now_time = date('Y-m-d H:i');
        $lunch_stime = $pre_date.' '.$feast_time['lunch'][0];
        $lunch_etime = $pre_date.' '.$feast_time['lunch'][1];

        $dinner_stime = $pre_date.' '.$feast_time['dinner'][0];
        $dinner_etime = $pre_date.' '.$feast_time['dinner'][1];

        $type = 0;//1午饭 2晚饭
        if($pre_time<$lunch_stime){
            $type = 1;
            $begin_time = $lunch_stime;
            $over_time = $lunch_etime;
        }elseif($pre_time>=$lunch_stime && $pre_time<=$lunch_etime){
            $type = 1;
            $begin_time = $pre_time;
            $over_time = $lunch_etime;
        }elseif($pre_time>$lunch_etime){
            $type = 2;
            $begin_time = $dinner_stime;
            $over_time = $dinner_etime;
        }else{
            $type = 2;
            $begin_time = $dinner_stime;
            $over_time = $dinner_etime;
        }
        if($now_time > $over_time){
            $is_signin = 1;
        }
        $res = array('is_signin'=>$is_signin,'signin_time'=>$begin_time,'signout_time'=>$over_time,'type'=>$type);
        return $res;
    }
}