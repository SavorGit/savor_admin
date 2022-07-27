<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class HotellotteryModel extends BaseModel{
	protected $tableName='smallapp_hotellottery';

    public function push_hotellottery(){
        $now_date = date('Y-m-d');
        $where = array('status'=>1);
        $where['start_date'] = array('ELT',$now_date);
        $where['end_date'] = array('EGT',$now_date);
        $res = $this->getDataList('*',$where,'id asc');
        if(empty($res)){
            $now_time = date('Y-m-d H:i:s');
            echo "time:$now_time no lottery \r\n";
            exit;
        }
        $m_activity = new \Admin\Model\Smallapp\ActivityModel();
        $m_hotellottery_prize = new \Admin\Model\Smallapp\HotellotteryPrizeModel();
        $m_activity_prize = new \Admin\Model\Smallapp\ActivityprizeModel();
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $m_box = new \Admin\Model\BoxModel();
        $host_name = 'https://mobile.littlehotspot.com';
        $nowtime = date('H:i');
        foreach ($res as $v){
            $hotel_id = $v['hotel_id'];
            $optime = date('H:i',strtotime($v['timing']));
            if($now_date>=$v['start_date'] && $now_date<=$v['end_date'] && $nowtime==$optime){
                $now_time_stamp = time();
                $start_time = date('Y-m-d H:i:00');
                if($v['type']==2){
                    $type = 10;
                    $end_time = date('Y-m-d H:i:00',$now_time_stamp + ($v['wait_time']*60));
                    $lottery_time = $end_time;
                }else{
                    $end_time = date('Y-m-d H:i:00',$now_time_stamp + (($v['wait_time']-5)*60));
                    $lottery_time = date('Y-m-d H:i:00',$now_time_stamp + ($v['wait_time']*60));
                    $type = 4;
                }

                $add_activity_data = array('hotel_id'=>$hotel_id,'name'=>$v['name'],'prize'=>$v['prize'],'image_url'=>$v['image_url'],
                    'start_time'=>$start_time,'end_time'=>$end_time,'lottery_time'=>$lottery_time,'people_num'=>$v['people_num'],'status'=>1,
                    'type'=>$type,'syslottery_id'=>$v['id']);
                $activity_id = $m_activity->add($add_activity_data);
                $partake_name = $partakedish_img = '';
                if($type==10){
                    $fields = 'a.prizepool_prize_id,a.amount,a.level,p.name,p.image_url,p.type';
                    $pwhere = array('a.hotellottery_id'=>$v['id'],'a.status'=>1);
                    $res_prize = $m_hotellottery_prize->getHotelpoolprizeList($fields,$pwhere,'a.id desc', 0,0);
                    $p_data = array();
                    foreach ($res_prize as $pv){
                        $info = array('activity_id'=>$activity_id,'name'=>$pv['name'],'image_url'=>$pv['image_url'],'amount'=>$pv['amount'],
                            'level'=>$pv['level'],'prizepool_prize_id'=>$pv['prizepool_prize_id'],'type'=>$pv['type']
                            );
                        $p_data[]=$info;
                        if($pv['level']==1){
                            $partake_name = $pv['name'];
                            $partakedish_img = $pv['image_url'];
                        }
                    }
                    $m_activity_prize->addAll($p_data);
                }else{
                    $partake_name = $v['prize'];
                    $partakedish_img = $v['image_url'];
                }

                echo "ID:{$v['id']} activity_id:$activity_id ok\r\n";

                $bwhere = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
                $res_box = $m_box->getBoxByCondition('box.id as box_id,box.mac',$bwhere);
                if(!empty($res_box)){
                    $partake_filename = '';
                    if(!empty($partakedish_img)){
                        $dish_name_info = pathinfo($partakedish_img);
                        $partake_filename = $dish_name_info['basename'];
                        $partakedish_img = $partakedish_img.'?x-oss-process=image/resize,m_mfit,h_200,w_300';
                    }
                    $now_time = time();
                    $lottery_countdown = strtotime($lottery_time) - $now_time;
                    $lottery_countdown = $lottery_countdown>0?$lottery_countdown:0;

                    $netty_msg = array(
                        'lottery_countdown'=>$lottery_countdown,'partake_img'=>$partakedish_img,'partake_filename'=>$partake_filename,
                        'partake_name'=>$partake_name,'activity_name'=>$v['name'],
                    );

                    if($type==10){
                        $netty_msg['action'] = 158;
                    }else{
                        $netty_msg['lottery_time']=date('H:i',strtotime($lottery_time));
                        $netty_msg['action'] = 135;
                        $netty_msg['countdown'] = 180;
                    }

                    foreach ($res_box as $bv){
                        if($type==10){
                            $code_url = $host_name."/Smallapp46/qrcode/getBoxQrcode?box_id={$bv['box_id']}&box_mac={$bv['mac']}&data_id={$activity_id}&type=45";
                            $netty_msg['codeUrl']=$code_url;
                        }
                        $res_netty = $m_netty->pushBox($bv['mac'],json_encode($netty_msg));
                        $netty_data = json_encode($res_netty);
                        echo "activity_id:$activity_id box:{$bv['mac']} message:" . json_encode($netty_msg) . "netty:$netty_data \r\n";
                    }
                }
                if($now_date==$v['end_date']){
                    $this->updateData(array('id'=>$v['id']),array('status'=>2));
                }
            }else{
                echo "ID:{$v['id']} not in time $optime \r\n";
            }
        }

    }


    public function pushBoxPoolLottery(){
        $start_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 23:59:59');
        $where = array('status'=>1,'type'=>10);
        $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $m_activity = new \Admin\Model\Smallapp\ActivityModel();
        $res = $m_activity->getDataList('*',$where,'id desc');
        if(empty($res)){
            echo "no activity \r\n";
            exit;
        }
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $res_config = $m_sys_config->getAllconfig();
        $hotellottery_people_num = $res_config['hotellottery_people_num'];

        $m_activityapply = new \Admin\Model\Smallapp\ActivityapplyModel();
        $m_activityprize = new \Admin\Model\Smallapp\ActivityprizeModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_prizepool = new \Admin\Model\Smallapp\PrizepoolprizeModel();
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(1);
        $key_pool = C('SAPP_PRIZEPOOL');
        $now_time = date('Y-m-d H:i:00');
        foreach ($res as $v){
            $activity_id = $v['id'];
            $lottery_time = strtotime($v['lottery_time']);
            $activity_people_num = $v['people_num'];
            if($v['lottery_time']==$now_time){
                $where = array('activity_id'=>$v['id']);
                $res_apply_user = $m_activityapply->getDataList('*',$where,'id asc');
                $is_send_prize = 1;
                $all_lottery_openid = array();
                $all_lottery_box_openid = array();
                if(empty($res_apply_user)){
                    $is_send_prize = 0;
                    $m_activity->updateData(array('id'=>$activity_id),array('status'=>2));
                    echo "activity_id:{$v['id']} no lottery user \r\n";
                }else{
                    $lottery_user_num = 0;
                    foreach ($res_apply_user as $ak=>$av){
                        $lottery_user_num++;
                        $all_lottery_box_openid[$av['box_mac']][]=$av['openid'];
                        $all_lottery_openid[]=$av['openid'];
                    }
                    $pwhere = array('box.state'=>1,'box.flag'=>0);
                    $pwhere['hotel.id'] = $v['hotel_id'];
                    $res_pdata = $m_box->getBoxByCondition('box.mac,hotel.id as hotel_id',$pwhere,'');

                    if($activity_people_num>0){
                        $hotellottery_people_num = $activity_people_num;
                    }
                    if($lottery_user_num<$hotellottery_people_num){
                        $is_send_prize = 0;
                        $m_activity->updateData(array('id'=>$activity_id),array('status'=>2));

                        $netty_data = array('action'=>157,'content'=>'参与人数不足，无法开奖');
                        $message = json_encode($netty_data);
                        foreach ($res_pdata as $ppv){
                            $ret = $m_netty->pushBox($ppv['mac'],$message);
                            if(isset($ret['error_code'])){
                                $ret_str = json_encode($ret);
                                echo "box_mac:{$ppv['mac']} push error $ret_str \r\n";
                            }else{
                                echo "box_mac:{$ppv['mac']} push ok \r\n";
                            }
                        }
                        echo "activity_id:{$v['id']} lottery_user_num:$lottery_user_num lt {$v['people_num']} \r\n";
                    }
                }

                $res_prize = $m_activityprize->getDataList('*',array('activity_id'=>$activity_id),'level asc');
                if($is_send_prize==0){
                    foreach ($res_prize as $prv){
                        $amount=$prv['amount'];
                        $prizepool_prize_id = $prv['prizepool_prize_id'];
                        $lucky_pool_key = $key_pool.$prizepool_prize_id;
                        $res_cachepool = $redis->get($lucky_pool_key);
                        $prizepool_data = array();
                        if(!empty($res_cachepool)){
                            $prizepool_data = json_decode($res_cachepool,true);
                        }
                        for ($i=1;$i<=$amount;$i++){
                            $p_key = $v['hotel_id'].$v['syslottery_id'].$i;
                            unset($prizepool_data[$p_key]);
                        }
                        $redis->set($lucky_pool_key,json_encode($prizepool_data));
                    }
                    continue;
                }
                $all_prizes = array();
                $first_num = $other_num = 0;
                foreach ($res_prize as $pzv){
                    $all_prizes[$pzv['level']]=$pzv;
                    if($pzv['level']==1){
                        $first_num=$pzv['amount'];
                    }else{
                        $other_num+=$pzv['amount'];
                    }
                }

                $lottery_user_openids = array();
                $box_num = count($all_lottery_box_openid);
                if($box_num>1){
                    $boxs = array_keys($all_lottery_box_openid);
                    shuffle($boxs);
                    $now_prize_id = $res_prize[0]['id'];
                    unset($res_prize[0]);
                    $now_boxs = $boxs[0];
                    $now_openids = $all_lottery_box_openid[$now_boxs];
                    shuffle($now_openids);
                    $lottery_user_openids[$now_openids[0]]=array('level'=>1,'prize_id'=>$now_prize_id);
                    unset($now_openids[0]);

                    $last_box_openids = array();
                    foreach ($all_lottery_box_openid as $bk=>$bv){
                        if($bk!=$now_boxs){
                            $last_box_openids = array_merge($last_box_openids,$bv);
                        }
                    }
                    shuffle($last_box_openids);
                    $lottery_no = 0;
                    $last_prizes = array();
                    foreach ($res_prize as $pv){
                        $amount=$pv['amount'];
                        for ($i=0;$i<$pv['amount'];$i++){
                            if(empty($last_box_openids[$lottery_no])){
                                $last_prizes[$pv['level']]=array('amount'=>$amount,'prize_id'=>$pv['id']);
                                break;
                            }
                            $l_openid = $last_box_openids[$lottery_no];
                            $lottery_user_openids[$l_openid]=array('level'=>$pv['level'],'prize_id'=>$pv['id']);
                            $lottery_no++;
                            $amount--;
                        }
                    }
                    if(!empty($last_prizes)){
                        $lottery_no = 1;
                        foreach ($last_prizes as $lk=>$la){
                            $amount=$la['amount'];
                            for ($i=1;$i<=$amount;$i++){
                                $l_openid = $now_openids[$lottery_no];
                                $lottery_user_openids[$l_openid]=array('level'=>$lk,'prize_id'=>$la['prize_id']);
                                $lottery_no++;
                            }
                        }
                    }
                }else{
                    $lottery_no = 0;
                    shuffle($all_lottery_openid);
                    foreach ($res_prize as $pv){
                        $amount=$pv['amount'];
                        for ($i=0;$i<$amount;$i++){
                            $l_openid = $all_lottery_openid[$lottery_no];
                            $lottery_user_openids[$l_openid]=array('level'=>$pv['level'],'prize_id'=>$pv['id']);
                            $lottery_no++;
                        }
                    }
                }
                $prize_users = array();
                $prize_apply_ids = array();
                foreach ($res_apply_user as $uv){
                    if(isset($lottery_user_openids[$uv['openid']])){
                        $lp_info = $lottery_user_openids[$uv['openid']];
                        $prize_info = $all_prizes[$lp_info['level']];

                        $info = array('openid'=>$uv['openid'],'dish_name'=>$prize_info['name'],'dish_image'=>$prize_info['image_url'],
                            'level'=>$lp_info['level'],'room_name'=>$uv['box_name'],'box_mac'=>$uv['box_mac']);
                        $prize_users[$uv['openid']]=$info;

                        $prize_apply_ids[]=$uv['id'];
                        $expire_time = date('Y-m-d H:i:s',$lottery_time+10800);
                        $adata = array('status'=>2,'expire_time'=>$expire_time,'prize_id'=>$lp_info['prize_id']);
                        $m_activityapply->updateData(array('id'=>$uv['id']),$adata);
                        switch ($prize_info['type']){
                            case 1:
                                $message_oid = $prize_info['prizepool_prize_id'].'_'.$uv['id'];
                                sendSmallappTopicMessage($message_oid,50);
                                break;
                            case 4:
                                $res_prizepool = $m_prizepool->getInfo(array('id'=>$prize_info['prizepool_prize_id']));
                                $coupon_id = intval($res_prizepool['coupon_id']);
                                $m_coupon = new \Admin\Model\Smallapp\CouponModel();
                                $res_coupon = $m_coupon->getInfo(array('id'=>$coupon_id));
                                if($res_coupon['start_hour']>0){
                                    $now_stime = time()+($res_coupon['start_hour']*3600);
                                    $start_time = date('Y-m-d H:i:s',$now_stime);
                                }else{
                                    $start_time = $res_coupon['start_time'];
                                }
                                $coupon_data = array('openid'=>$uv['openid'],'coupon_id'=>$coupon_id,'money'=>$res_coupon['money'],'hotel_id'=>$uv['hotel_id'],
                                    'min_price'=>$res_coupon['min_price'],'max_price'=>$res_coupon['max_price'],'activity_id'=>$activity_id,
                                    'start_time'=>$start_time,'end_time'=>$res_coupon['end_time'],'ustatus'=>1);
                                $m_user_coupon = new \Admin\Model\Smallapp\UserCouponModel();
                                $m_user_coupon->add($coupon_data);
                                break;
                        }
                    }
                }

                if(!empty($prize_apply_ids)){
                    $awhere = array('activity_id'=>$activity_id);
                    $awhere['id'] = array('not in',$prize_apply_ids);
                    $m_activityapply->updateData($awhere,array('status'=>3));
                }

                //更新奖池
                foreach ($res_prize as $prv){
                    $prizepool_prize_id = $prv['prizepool_prize_id'];
                    $m_prizepool->where(array('id'=>$prizepool_prize_id))->setInc('send_amount',$prv['amount']);
                }

                $lwhere = array('openid'=>array('in',$all_lottery_openid));
                $users = $m_user->getWhere('openid,avatarUrl,nickName',$lwhere,'id desc','','');
                $partake_user = array();
                foreach ($users as $uv){
                    $is_lottery = 0;
                    if(isset($prize_users[$uv['openid']])){
                        $is_lottery = 1;
                    }
                    $partake_user[] = array('openid'=>$uv['openid'],'avatarUrl'=>base64_encode($uv['avatarurl']),'nickName'=>$uv['nickname'],'is_lottery'=>$is_lottery);
                }

                $netty_data = array('action'=>156,'partake_user'=>$partake_user,'lottery'=>array_values($prize_users));
                $message = json_encode($netty_data);
                echo "box message $message \r\n";

                foreach ($res_pdata as $bv){
                    $ret = $m_netty->pushBox($bv['mac'],$message);
                    $ret_str = json_encode($ret);
                    echo "box_mac:{$bv['mac']} push $ret_str \r\n";
                }
                $m_activity->updateData(array('id'=>$activity_id),array('status'=>2));
            }
        }
    }
}