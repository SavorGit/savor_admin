<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class ActivityModel extends BaseModel{
	protected $tableName='smallapp_activity';


    public function getList($fields,$where,$orderby,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->alias('a')
                ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->limit($start,$size)
                ->select();
            $count = $this->alias('a')
                ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                ->where($where)
                ->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->alias('a')
                ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->select();
        }
        return $data;
    }

    public function pushBoxDishActivity(){
        /*
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
        */
        $start_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 23:59:59');
        $where = array('status'=>array('in',array('1','0')),'type'=>array('in',array(1,4)));
        $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $res = $this->getDataList('*',$where,'id asc');
        if(empty($res)){
            echo "no activity \r\n";
            exit;
        }
        $hotel_dishs = array();
        $now_date = date('Y-m-d H:i:00');
        foreach ($res as $v){
            if($v['status']==1 && $v['end_time']>$now_date){
                $hotel_dishs[$v['hotel_id']] = $v;
            }elseif($v['status']==0 && $v['start_time']==$now_date){
                $hotel_dishs[$v['hotel_id']] = $v;
                $this->updateData(array('id'=>$v['id']),array('status'=>1));
            }
        }

        $all_hotel_ids = array_keys($hotel_dishs);
        $m_box = new \Admin\Model\BoxModel();
        $fields = 'box.mac,hotel.id as hotel_id';
        $where = array('box.state'=>1,'box.flag'=>0);
        $all_boxs = array();
        if(!empty($all_hotel_ids)){
            $where['hotel.id'] = array('in',$all_hotel_ids);
            $res_bdata = $m_box->getBoxByCondition($fields,$where,'');
            foreach ($res_bdata as $v){
                $all_boxs[$v['mac']]=$hotel_dishs[$v['hotel_id']];
            }
        }
        if(empty($all_boxs)){
            echo "no boxs \r\n";
            exit;
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
                        $partakedish_img = $activity_info['image_url'].'?x-oss-process=image/resize,m_mfit,h_200,w_300';
                        $dish_name_info = pathinfo($activity_info['image_url']);

                        $netty_data = array('action'=>135,'countdown'=>180,'lottery_time'=>date('H:i',strtotime($activity_info['lottery_time'])),
                            'lottery_countdown'=>$lottery_countdown,'partake_img'=>$partakedish_img,'partake_filename'=>$dish_name_info['basename'],
                            'partake_name'=>$activity_info['prize'],'activity_name'=>$activity_info['name'],
                        );

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
        $where = array('status'=>1,'type'=>array('in',array(1,4)));
        $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $res = $this->getDataList('*',$where,'id desc');
        if(empty($res)){
            echo "no activity \r\n";
            exit;
        }
        $m_activityapply = new \Admin\Model\Smallapp\ActivityapplyModel();
        unset($where['type']);
        $where['status']=2;
        $res_lotteryuser = $m_activityapply->getDataList('openid',$where,'id desc');
        $now_lottery_openids = array();
        if(!empty($res_lotteryuser)){
            foreach ($res_lotteryuser as $v){
                $now_lottery_openids[]=$v['openid'];
            }
        }
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $res_config = $m_sys_config->getAllconfig();
        $hotellottery_people_num = $res_config['hotellottery_people_num'];

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
                    /*
                    $last_user_num = 40;
                    $start = rand(1000,2000);
                    $limit = "$start,$last_user_num";
                    $uwhere = array('nickName'=>array('neq',''));
                    $res_user = $m_user->getWhere('openid,avatarUrl,nickName',$uwhere,'id desc',$limit,'');
                    $lottery_openid = $res_user[0]['openid'];

                    $partake_user = array();
                    foreach ($res_user as $uv){
                        $is_lottery = 0;
                        if($uv['openid']==$lottery_openid){
                            $is_lottery = 1;
                        }
                        $partake_user[] = array('avatarUrl'=>base64_encode($uv['avatarurl']),'nickName'=>$uv['nickname'],'is_lottery'=>$is_lottery);
                    }
                    */
                }else{
                    $all_lottery_openid = array();
                    foreach ($res_apply_user as $ak=>$av){
                        $all_lottery_openid[]=$av['openid'];
                        if(in_array($av['openid'],$now_lottery_openids)){
                            unset($res_apply_user[$ak]);
                        }
                    }
                    if(count($all_lottery_openid)<$hotellottery_people_num){
                        $lottery_user_num =  count($all_lottery_openid);
                        $this->updateData(array('id'=>$activity_id),array('status'=>2));
                        echo "activity_id:{$v['id']} lottery_user_num:$lottery_user_num lt $hotellottery_people_num \r\n";
                        continue;
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
            }
        }

    }


    public function pushLotteryToWeixin(){
        $now_endtime = time() - 300;
        $now_endtime = date('Y-m-d H:i:s',$now_endtime);
        $where = array('status'=>2,'type'=>array('in',array(1,4)));
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

    public function pushBoxLotteryActivity(){
        $start_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 23:59:59');
        $where = array('status'=>0,'type'=>8);
        $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $res = $this->getDataList('*',$where,'id asc');
        if(empty($res)){
            echo "no activity \r\n";
            exit;
        }
        $now_date = date('Y-m-d H:i:00');
        $m_box = new \Admin\Model\BoxModel();
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $host_name = 'http://'.C('SAVOR_API_URL');
        foreach ($res as $v){
            $activity_id = $v['id'];
            if($v['status']==0 && $v['start_time']==$now_date){
                $activity_info = $v;
                $now_time = time();
                $lottery_countdown = strtotime($activity_info['lottery_time']) - $now_time;
                $lottery_countdown = $lottery_countdown>0?$lottery_countdown:0;

                $image_url = $activity_info['tv_image_url'];
                $name_info = pathinfo($image_url);
                $netty_data = array('action'=>155,'countdown'=>180,'lottery_time'=>date('H:i',strtotime($activity_info['lottery_time'])),
                    'lottery_countdown'=>$lottery_countdown,'activity_name'=>$activity_info['name'],
                    'url'=>$image_url,'filename'=>$name_info['basename'],
                );
                $fields = 'box.id as box_id,box.mac as box_mac,hotel.id as hotel_id';
                $where = array('box.state'=>1,'box.flag'=>0);
                $where['hotel.id'] = $v['hotel_id'];
                if($v['scope']==2){
                    $where['room.type'] = 1;
                }
                $res_bdata = $m_box->getBoxByCondition($fields,$where,'');
                foreach ($res_bdata as $bv){
                    $netty_data['qrcode_url'] = $host_name."/smallapp46/qrcode/getBoxQrcode?box_mac={$bv['box_mac']}&box_id={$bv['box_id']}&data_id=$activity_id&type=42";
                    $message = json_encode($netty_data);
                    echo $message."\r\n";

                    $ret = $m_netty->pushBox($bv['box_mac'],$message);
                    if(isset($ret['error_code'])){
                        $ret_str = json_encode($ret);
                        echo "box_mac:{$bv['mac']} push error $ret_str \r\n";
                    }else{
                        echo "box_mac:{$bv['mac']} push ok \r\n";
                    }
                }
                $this->updateData(array('id'=>$activity_id),array('status'=>1));
            }
        }
    }

    public function pushBoxTaskLottery(){
        $start_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 23:59:59');
        $where = array('status'=>1,'type'=>8);
        $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $res = $this->getDataList('*',$where,'id desc');
        if(empty($res)){
            echo "no activity \r\n";
            exit;
        }
        $m_activityapply = new \Admin\Model\Smallapp\ActivityapplyModel();
        $m_activityprize = new \Admin\Model\Smallapp\ActivityprizeModel();
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_box = new \Admin\Model\BoxModel();
        $netty_cmd = C('SAPP_CALL_NETY_CMD');
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $now_time = date('Y-m-d H:i:00');
        foreach ($res as $v){
            $activity_id = $v['id'];
            $lottery_time = strtotime($v['lottery_time']);
            if($v['lottery_time']==$now_time){
                $where = array('activity_id'=>$v['id']);
                $res_apply_user = $m_activityapply->getDataList('*',$where,'id asc');
                if(empty($res_apply_user)){
                    $this->updateData(array('id'=>$activity_id),array('status'=>2));
                    echo "activity_id:{$v['id']} no lottery user \r\n";
                }else{
                    $all_lottery_openid = array();
                    foreach ($res_apply_user as $ak=>$av){
                        $all_lottery_openid[]=$av['openid'];
                    }
                    if(count($all_lottery_openid)<$v['people_num']){
                        $lottery_user_num =  count($all_lottery_openid);
                        $this->updateData(array('id'=>$activity_id),array('status'=>2));
                        echo "activity_id:{$v['id']} lottery_user_num:$lottery_user_num lt {$v['people_num']} \r\n";
                        continue;
                    }
                    $user_num = count($res_apply_user) - 1;
                    $res_prize = $m_activityprize->getDataList('*',array('activity_id'=>$activity_id),'level desc');
                    $prize_users = array();
                    $prize_apply_ids = array();
                    foreach ($res_prize as $pv){
                        if($pv['amount']==1){
                            $lottery_rand = mt_rand(0,$user_num);
                            $res_apply_user = array_values($res_apply_user);
                            $lottery_apply_id = $res_apply_user[$lottery_rand]['id'];
                            $lottery_openid = $res_apply_user[$lottery_rand]['openid'];
                            $room_name = $res_apply_user[$lottery_rand]['box_name'];
                            $prize_users[$lottery_openid]=array('openid'=>$lottery_openid,'dish_name'=>$pv['name'],'dish_image'=>$pv['image_url'],'level'=>$pv['level'],'room_name'=>$room_name);
                            $prize_apply_ids[]=$lottery_apply_id;
                            unset($res_apply_user[$lottery_rand]);
                            $user_num--;
                            $expire_time = date('Y-m-d H:i:s',$lottery_time+10800);
                            $adata = array('status'=>2,'expire_time'=>$expire_time,'prize_id'=>$pv['id']);
                            $m_activityapply->updateData(array('id'=>$lottery_apply_id),$adata);
                        }else{
                            for ($i=0;$i<$pv['amount'];$i++){
                                $lottery_rand = mt_rand(0,$user_num);
                                $res_apply_user = array_values($res_apply_user);
                                $lottery_apply_id = $res_apply_user[$lottery_rand]['id'];
                                $lottery_openid = $res_apply_user[$lottery_rand]['openid'];
                                $room_name = $res_apply_user[$lottery_rand]['box_name'];
                                $prize_users[$lottery_openid]=array('openid'=>$lottery_openid,'dish_name'=>$pv['name'],'dish_image'=>$pv['image_url'],'level'=>$pv['level'],'room_name'=>$room_name);
                                $prize_apply_ids[]=$lottery_apply_id;
                                unset($res_apply_user[$lottery_rand]);
                                $user_num--;
                                $expire_time = date('Y-m-d H:i:s',$lottery_time+10800);
                                $adata = array('status'=>2,'expire_time'=>$expire_time,'prize_id'=>$pv['id']);
                                $m_activityapply->updateData(array('id'=>$lottery_apply_id),$adata);
                            }
                        }
                    }

                    if(!empty($prize_apply_ids)){
                        $awhere = array('activity_id'=>$v['id']);
                        $awhere['id'] = array('not in',$prize_apply_ids);
                        $m_activityapply->updateData($awhere,array('status'=>3));
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

                    $fields = 'box.mac,hotel.id as hotel_id';
                    $where = array('box.state'=>1,'box.flag'=>0);
                    $where['hotel.id'] = $v['hotel_id'];
                    if($v['scope']==2){
                        $where['room.type'] = 1;
                    }
                    $res_bdata = $m_box->getBoxByCondition($fields,$where,'');
                    foreach ($res_bdata as $bv){
                        $ret = $m_netty->pushBox($bv['mac'],$message);
                        if(isset($ret['error_code'])){
                            $ret_str = json_encode($ret);
                            echo "box_mac:{$bv['mac']} push error $ret_str \r\n";
                        }else{
                            echo "box_mac:{$bv['mac']} push ok \r\n";
                        }
                    }
                    $this->updateData(array('id'=>$activity_id),array('status'=>2));
                }
            }
        }
    }

    public function pushTaskLotteryToMobile(){
        $now_endtime = time() - 300;
        $now_endtime = date('Y-m-d H:i:s',$now_endtime);
        $where = array('status'=>2,'type'=>8);
        $where['lottery_time'] = array('egt',$now_endtime);
        $res = $this->getDataList('*',$where,'id desc');
        if(empty($res)){
            echo "no activity \r\n";
            exit;
        }
        $ucconfig = C('ALIYUN_SMS_CONFIG');
        $alisms = new \Common\Lib\AliyunSms();
        $all_prizes = array('1'=>'一等奖','2'=>'二等奖','3'=>'三等奖');
        $m_activityapply = new \Admin\Model\Smallapp\ActivityapplyModel();
        $m_taskuser = new \Admin\Model\Integral\TaskUserModel();
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_account_sms_log = new \Admin\Model\AccountMsgLogModel();
        $now_hour = date('Y-m-d H:i:00');
        foreach ($res as $v){
            $lottery_time = strtotime($v['lottery_time'])+60;
            $lottery_time = date('Y-m-d H:i:00',$lottery_time);
            if($lottery_time==$now_hour){
                $activity_id = $v['id'];
                $where = array('a.activity_id'=>$activity_id,'a.status'=>2);
                $fields = 'a.id,a.activity_id,a.hotel_id,a.hotel_name,a.box_mac,a.box_name,a.openid,a.mobile,a.prize_id,prize.name as prize_name,prize.level';
                $res_apply_user = $m_activityapply->alias('a')
                    ->join('savor_smallapp_activity_prize prize on a.prize_id=prize.id','left')
                    ->field($fields)
                    ->where($where)
                    ->order('prize.level asc')
                    ->select();
                if(empty($res_apply_user)){
                    echo "activity_id:$activity_id no applylottery user \r\n";
                    continue;
                }
                $res_taskuser = $m_taskuser->getInfo(array('id'=>$v['task_user_id']));
                $res_user = $m_user->getOne('mobile',array('openid'=>$res_taskuser['openid'],'status'=>1),'id desc');
                $staff_mobile = $res_user['mobile'];

                $user_sms = array();
                $staff_sms = array();
                foreach ($res_apply_user as $uv){
                    $user_sms[] = array('mobile'=>$uv['mobile'],'content'=>"{$all_prizes[$uv['level']]}（{$uv['prize_name']}）");
                    $staff_sms[$uv['level']][]=array('content'=>"{$uv['box_name']}包间（{$uv['prize_name']}）");
                }

                $staff_content = '';
                foreach ($staff_sms as $sk=>$sv){
                    $staff_content.=$all_prizes[$sk].'：';
                    $lottery_content = '';
                    foreach ($sv as $cv){
                        $lottery_content.=$cv['content'].'、';
                    }
                    $lottery_content = rtrim($lottery_content,'、');
                    $staff_content.=$lottery_content.'；';
                }
                $staff_content = rtrim($staff_content,'；');

                $params = array('name'=>$staff_content);
                $template_code = $ucconfig['send_tasklottery_sponsor_templateid'];
                $res_data = $alisms::sendSms($staff_mobile,$params,$template_code);
                $data = array('type'=>14,'status'=>1,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s'),
                    'url'=>join(',',$params),'tel'=>$staff_mobile,'resp_code'=>$res_data->Code,'msg_type'=>3
                );
                $m_account_sms_log->addData($data);

                foreach ($user_sms as $uv){
                    $uparams = array('name'=>$uv['content']);
                    $template_code = $ucconfig['send_tasklottery_user_templateid'];
                    $res_data = $alisms::sendSms($uv['mobile'],$uparams,$template_code);
                    $data = array('type'=>14,'status'=>1,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s'),
                        'url'=>join(',',$uparams),'tel'=>$staff_mobile,'resp_code'=>$res_data->Code,'msg_type'=>3
                    );
                    $m_account_sms_log->addData($data);
                }

            }
        }
    }
}