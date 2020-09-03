<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class ActivityModel extends BaseModel{
	protected $tableName='smallapp_activity';

    public function pushBoxDishActivity(){
        $hour = date('YmdHi');
        $activity_hotels = C('ACTIVITY_KINGMEAL');
        $start_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 23:59:59');
        $where = array('status'=>1);
        $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');

        $hotel_dishs = array();
        if(isset($activity_hotels[$hour])){
            $hotels = $activity_hotels[$hour];
            $all_hotel_ids = array();
            foreach ($hotels as $v){
                $all_hotel_ids[]=$v['hotel_id'];
            }
            $where['hotel_id'] = array('in',$all_hotel_ids);
            $res = $this->getDataList('*',$where,'id desc');
            if(empty($res)){
                foreach ($hotels as $v){
                    $dish = array('name'=>'霸王菜','hotel_id'=>$v['hotel_id'],'prize'=>$v['dish'],'image_url'=>$v['dish_img'],'status'=>1,
                        'start_time'=>$v['start_time'],'end_time'=>$v['end_time'],'lottery_time'=>$v['lottery_time']);
                    $row_id = $this->add($dish);
                    $dish['id'] = $row_id;
                    $hotel_dishs[$v['hotel_id']] = $dish;
                }
            }else{
                foreach ($res as $v){
                    $hotel_dishs[$v['hotel_id']] = $v;
                }
            }
        }else{
            $res = $this->getDataList('*',$where,'id asc');
            $now_date = date('Y-m-d H:i:s');
            foreach ($res as $v){
                if($v['end_time']>$now_date){
                    $hotel_dishs[$v['hotel_id']] = $v;
                }
            }
        }
        if(empty($hotel_dishs)){
            echo "no activity \r\n";
            exit;
        }
        $all_hotel_ids = array_keys($hotel_dishs);
        $m_box = new \Admin\Model\BoxModel();
        $fields = 'box.mac,hotel.id as hotel_id';
        $where = array('box.state'=>1,'box.flag'=>0);
        $where['hotel.id'] = array('in',$all_hotel_ids);
        $res_bdata = $m_box->getBoxByCondition($fields,$where,'');
        $all_boxs = array();
        foreach ($res_bdata as $v){
            $all_boxs[$v['mac']]=$hotel_dishs[$v['hotel_id']];
        }

        $url = 'https://api-nzb.littlehotspot.com/netty/box/connections';
        $curl = new \Common\Lib\Curl();
        $res_netty = '';
        $curl::get($url,$res_netty,10);
        $res_box = json_decode($res_netty,true);
        if(empty($res_box) || !is_array($res_box) || $res_box['code']!=10000){
            $curl::get($url,$res_netty,10);
            $res_box = json_decode($res_netty,true);
        }
        if(empty($res_box) || !is_array($res_box) || $res_box['code']!=10000){
            echo "netty connections api error \r\n";
            exit;
        }

        if(!empty($res_box['result'])){
            $netty_cmd = C('SAPP_CALL_NETY_CMD');
            $m_netty = new \Admin\Model\Smallapp\NettyModel();
            foreach ($res_box['result'] as $k=>$v){
                if($v['totalConn']>0){
                    foreach ($v['connDetail'] as $cv){
                        $box_mac = $cv['box_mac'];
                        if(!isset($all_boxs[$box_mac])){
                            continue;
                        }
                        $activity_info = $all_boxs[$box_mac];
                        $now_time = time();

                        $lottery_countdown = strtotime($activity_info['lottery_time']) - $now_time;
                        $lottery_countdown = $lottery_countdown>0?$lottery_countdown:0;
                        $dish_name_info = pathinfo($activity_info['image_url']);
                        $partakedish_img = $dish_name_info['dirname'].'/'.$dish_name_info['filename'].'_partake.jpg';
                        $netty_data = array('action'=>135,'countdown'=>180,'lottery_time'=>date('H:i',strtotime($activity_info['lottery_time'])),
                            'lottery_countdown'=>$lottery_countdown,'partakedish_img'=>$partakedish_img
                        );

                        $name_info = pathinfo($netty_data['partakedish_img']);
                        $netty_data['partakedish_filename'] = $name_info['basename'];
                        $message = json_encode($netty_data);

                        echo "message $message \r\n";

                        $push_url = 'http://'.$cv['http_host'].':'.$cv['http_port'].'/push/box';
                        $req_id  = getMillisecond();
                        $box_params = array('box_mac'=>$box_mac,'msg'=>$message,'req_id'=>$req_id,'cmd'=>$netty_cmd);
                        $post_data = http_build_query($box_params);
                        $ret = $m_netty->curlPost($push_url,$post_data);
                        $res_push = json_decode($ret,true);
                        if($res_push['code']==10000){
                            echo "box_mac:$box_mac push ok \r\n";
                        }else{
                            echo "box_mac:$box_mac push error $ret  \r\n";
                        }
                    }
                }
            }
        }
    }

    //提前5分钟不能进行参与，脚本执行开始开奖
    public function pushBoxDishLottery(){
        $close_time = 300;
        $all_user_num = 40;

        $start_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 23:59:59');
        $where = array('status'=>1);
        $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $res = $this->getDataList('*',$where,'id desc');
        if(empty($res)){
            echo "no activity \r\n";
            exit;
        }
        $m_activityapply = new \Admin\Model\Smallapp\ActivityapplyModel();
        $where['status']=2;
        $res_lotteryuser = $m_activityapply->getDataList('openid',$where,'id desc');
        $now_lottery_openids = array();
        if(!empty($res_lotteryuser)){
            foreach ($res_lotteryuser as $v){
                $now_lottery_openids[]=$v['openid'];
            }
        }

        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_box = new \Admin\Model\BoxModel();
        $netty_cmd = C('SAPP_CALL_NETY_CMD');
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $now_time = date('Y-m-d H:i:00');
        foreach ($res as $v){
            $activity_id = $v['id'];
            $lottery_time = strtotime($v['lottery_time']);
//            $advance_time = date('Y-m-d H:i:00',$lottery_time - $close_time);
            if($v['lottery_time']==$now_time){
                $where = array('activity_id'=>$v['id']);
                $res_apply_user = $m_activityapply->getDataList('*',$where,'id asc');
                if(empty($res_apply_user)){
                    $this->updateData(array('id'=>$activity_id),array('status'=>2));
                    echo "activity_id:{$v['id']} no lottery user \r\n";
                    continue;
                }
                $all_lottery_openid = array();
                foreach ($res_apply_user as $ak=>$av){
                    $all_lottery_openid[]=$av['openid'];
                    if(in_array($av['openid'],$now_lottery_openids)){
                        unset($res_apply_user[$ak]);
                    }
                }
                if(!empty($res_apply_user)){
                    $res_apply_user = array_values($res_apply_user);
                    $user_num = count($res_apply_user) - 1;
                    $lottery_rand = mt_rand(0,$user_num);
                    $lottery_apply_id = $res_apply_user[$lottery_rand]['id'];
                    $lottery_openid = $res_apply_user[$lottery_rand]['openid'];
                }else{
                    $lottery_apply_id = 0;
                    $lottery_openid = 0;
                }
                if($lottery_apply_id){
                    $expire_time = date('Y-m-d H:i:s',$lottery_time+10800);
                    $adata = array('status'=>2,'expire_time'=>$expire_time);
                    $m_activityapply->updateData(array('id'=>$lottery_apply_id),$adata);
                }

                $awhere = array('activity_id'=>$v['id']);
                if($lottery_apply_id){
                    $awhere['id'] = array('neq',$lottery_apply_id);
                }
                $m_activityapply->updateData($awhere,array('status'=>3));

                $lwhere = array('openid'=>array('in',$all_lottery_openid));
                $users = $m_user->getWhere('openid,avatarUrl,nickName',$lwhere,'id desc','','');
                $last_user_num = $all_user_num - count($all_lottery_openid);
                if($last_user_num>0){
                    $start = rand(1000,2000);
                    $limit = "$start,$last_user_num";
                    $uwhere = array('nickName'=>array('neq',''));
                    $uwhere['openid'] = array('not in',$all_lottery_openid);
                    $res_user = $m_user->getWhere('openid,avatarUrl,nickName',$uwhere,'id desc',$limit,'');
                    if($lottery_apply_id==0){
                        $lottery_openid = $res_user[0]['openid'];
                    }
                    $users = array_merge($users,$res_user);
                }
                $partake_user = array();
                foreach ($users as $uv){
                    $is_lottery = 0;
                    if($uv['openid']==$lottery_openid){
                        $is_lottery = 1;
                    }
                    $partake_user[] = array('avatarUrl'=>base64_encode($uv['avatarurl']),'nickName'=>$uv['nickname'],'is_lottery'=>$is_lottery);
                }
                $lottery = array('dish_name'=>$v['prize'],'dish_image'=>$v['image_url']);

                $netty_data = array('action'=>136,'partake_user'=>$partake_user,'lottery'=>$lottery);
                $message = json_encode($netty_data);
                echo "message $message \r\n";

                $fields = 'box.mac,hotel.id as hotel_id';
                $where = array('box.state'=>1,'box.flag'=>0);
                $where['hotel.id'] = $v['hotel_id'];
                $res_bdata = $m_box->getBoxByCondition($fields,$where,'');
                $all_boxs = array();
                foreach ($res_bdata as $bv){
                    $all_boxs[]=$bv['mac'];
                }
                $url = 'https://api-nzb.littlehotspot.com/netty/box/connections';
                $curl = new \Common\Lib\Curl();
                $res_netty = '';
                $curl::get($url,$res_netty,10);
                $res_box = json_decode($res_netty,true);
                if(empty($res_box) || !is_array($res_box) || $res_box['code']!=10000){
                    $curl::get($url,$res_netty,10);
                    $res_box = json_decode($res_netty,true);
                }
                if(empty($res_box) || !is_array($res_box) || $res_box['code']!=10000){
                    echo "netty connections api error \r\n";
                    continue;
                }
                if(!empty($res_box['result'])){
                    foreach ($res_box['result'] as $k=>$rbv){
                        if($rbv['totalConn']>0){
                            foreach ($rbv['connDetail'] as $cv){
                                $box_mac = $cv['box_mac'];
                                if(!in_array($box_mac,$all_boxs)){
                                    continue;
                                }
                                $push_url = 'http://'.$cv['http_host'].':'.$cv['http_port'].'/push/box';
                                $req_id  = getMillisecond();
                                $box_params = array('box_mac'=>$box_mac,'msg'=>$message,'req_id'=>$req_id,'cmd'=>$netty_cmd);
                                $post_data = http_build_query($box_params);
                                $ret = $m_netty->curlPost($push_url,$post_data);
                                $res_push = json_decode($ret,true);
                                if($res_push['code']==10000){
                                    echo "box_mac:$box_mac push ok \r\n";
                                }else{
                                    echo "box_mac:$box_mac push error $ret  \r\n";
                                }
                            }
                        }
                    }
                }
                $this->updateData(array('id'=>$activity_id),array('status'=>2));

                /*
                $now_time = date('Y-m-d H:i:s');
                $res_apply_user = $m_activityapply->getDataList('*',array('activity_id'=>$activity_id),'id asc');
                if(!empty($res_apply_user)){
                    $this->updateData(array('id'=>$activity_id),array('status'=>2));
                    sleep(30);
                    $config = C('SMALLAPP_CONFIG');
                    $tempalte_id = 'HqNYdceqH7MAQk6dl4Gn54yZObVRNG0FJk40OIwa9x4';
                    $miniprogram_state = 'formal';//developer为开发版；trial为体验版；formal为正式版
                    $push_wxurl = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token";
                    $page_dish = "games/pages/activity/din_dash";
                    $page_index = "pages/index/index";
                    $token = getWxAccessToken($config);
                    $curl = new \Common\Lib\Curl();
                    foreach ($res_apply_user as $auv){
                        if($auv['openid']==$lottery_openid){
                            $prize = $v['prize'];
                            $desc = '已中奖';
                            $tips = '请3小时内找餐厅服务员领取';
                            $page_url = "$page_dish?openid={$auv['openid']}&box_mac={$auv['box_mac']}&activity_id=$activity_id";
                        }else{
                            $prize = '未中奖';
                            $desc = '未中奖';
                            $tips = '请等待下一轮抽奖';
                            $page_url = $page_index;
                        }
                        $url = "$push_wxurl=$token";
                        $data=array(
                            'date2'  => array('value'=>$v['lottery_time']),
                            'thing4'  => array('value'=>$desc),
                            'thing1'  => array('value'=>$prize),
                            'thing3'  => array('value'=>$tips)
                        );
                        $template = array(
                            'touser' => $auv['openid'],
                            'template_id' => $tempalte_id,
                            'page' => $page_url,
                            'miniprogram_state'=>$miniprogram_state,
                            'lang'=>'zh_CN',
                            'data' => $data
                        );
                        $template =  json_encode($template);
                        $res_data = '';
                        $curl::post($url,$template,$res_data);
                        echo "activity_id|$activity_id|openid|{$auv['openid']}|wxres|$res_data \r\n";
                    }
                }
                $now_time = date('Y-m-d H:i:s');
                echo "pushlotterytowx end:$now_time \r\n";
                */
            }
        }

    }


    public function pushLotteryToWeixin(){
        $now_endtime = time() - 300;
        $now_endtime = date('Y-m-d H:i:s',$now_endtime);
        $where = array('status'=>2);
        $where['lottery_time'] = array('egt',$now_endtime);
        $res = $this->getDataList('*',$where,'id desc');
        if(empty($res)){
            echo "no activity \r\n";
            exit;
        }
        $m_activityapply = new \Admin\Model\Smallapp\ActivityapplyModel();
        $config = C('SMALLAPP_CONFIG');
        $tempalte_id = 'HqNYdceqH7MAQk6dl4Gn54yZObVRNG0FJk40OIwa9x4';
        $curl = new \Common\Lib\Curl();
        $miniprogram_state = 'formal';//developer为开发版；trial为体验版；formal为正式版
        $now_hour = date('Y-m-d H:i:00');
        foreach ($res as $v){
            $lottery_time = strtotime($v['lottery_time'])+60;
            $lottery_time = date('Y-m-d H:i:00',$lottery_time);
            if($lottery_time==$now_hour){
                $activity_id = $v['id'];
                $where = array('activity_id'=>$activity_id);
                $res_apply_user = $m_activityapply->getDataList('*',$where,'id desc');
                if(empty($res_apply_user)){
                    echo "activity_id:$activity_id no applylottery user \r\n";
                    continue;
                }
                $push_wxurl = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token";
                $page_dish = "games/pages/activity/din_dash";
                $page_index = "pages/index/index?message";
                $token = getWxAccessToken($config);
                foreach ($res_apply_user as $uv){
                    if(in_array($uv['status'],array(2,3))){

                        if($uv['status']==2){
                            $prize = $v['prize'];
                            $desc = '已中奖';
                            $tips = '请3小时内找餐厅服务员领取';
                            $page_url = "$page_dish?openid={$uv['openid']}&box_mac={$uv['box_mac']}&activity_id=$activity_id";
                        }else{
                            $prize = '未中奖';
                            $desc = '未中奖';
                            $tips = '请等待下一轮抽奖';
                            $page_url = $page_index;
                        }

                        $url = "$push_wxurl=$token";
                        $data=array(
                            'date2'  => array('value'=>$lottery_time),
                            'thing4'  => array('value'=>$desc),
                            'thing1'  => array('value'=>$prize),
                            'thing3'  => array('value'=>$tips)
                        );

                        $template = array(
                            'touser' => $uv['openid'],
                            'template_id' => $tempalte_id,
                            'page' => $page_url,
                            'miniprogram_state'=>$miniprogram_state,
                            'lang'=>'zh_CN',
                            'data' => $data
                        );
                        $template =  json_encode($template);
                        $res_data = '';
                        $curl::post($url,$template,$res_data);
                        echo "activity_id|$activity_id|openid|{$uv['openid']}|wxres|$res_data \r\n";
                    }

                }


            }
        }
    }
}