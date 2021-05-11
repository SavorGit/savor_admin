<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class RedpacketoperationModel extends BaseModel{
	protected $tableName='smallapp_redpacketoperation';

    public function operationRedpacket($id=0){
        if($id){
            $data = $this->getInfo(array('id'=>$id));
            $res_list = array($data);
        }else{
            $orderby = 'id asc';
            $where = array('status'=>1);
            $res_list = $this->getDataList('*',$where,$orderby);
        }
        $all_senders = C('REDPACKET_SENDERS');
        $op_userid = C('REDPACKET_OPERATIONERID');
        unset($all_senders[0]);
        $m_redpacket = new \Admin\Model\Smallapp\RedpacketModel();
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_mac = new \Admin\Model\BoxModel();
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $m_media = new \Admin\Model\MediaModel();
        $rd_hotel = C('RD_TEST_HOTEL');
        $nowdate = date('Y-m-d');
        $nowtime = date('H:i');
        $nowdatetime = date("Y-m-d H:i:s");
        foreach ($res_list as $v){
            $redpacket_type = $v['type'];//类型 1立即发送,2单次定时,3多次定时
            switch ($redpacket_type){
                case 1:
                    $is_send = 1;
                    break;
                case 2:
                    $optime = date('H:i',strtotime($v['timing']));
                    if($v['start_date']==$nowdate && $nowtime==$optime){
                        $is_send = 1;
                    }else{
                        $is_send = 0;
                    }
                    break;
                case 3:
                    $optime = date('H:i',strtotime($v['timing']));
                    if($nowdate>=$v['start_date'] && $nowdate<=$v['end_date'] && $nowtime==$optime){
                        $is_send = 1;
                    }else{
                        $is_send = 0;
                    }
                    break;
                default:
                    $is_send = 0;
            }
            if($is_send==1){
                //发送范围 1全网餐厅电视,2当前餐厅所有电视,3当前包间电视 4区域红包 5运营红包
                $scope = $v['scope'];
                if($scope==5){
                    $fields = 'box.mac';
                    $where = array('hotel.id'=>$v['hotel_id'],'box.state'=>1,'box.flag'=>0);
                    $res_boxs = $m_mac->getBoxByCondition($fields,$where);
                    if(!empty($res_boxs)){
                        foreach ($res_boxs as $bv){
                            $redpacket = array('user_id'=>$op_userid,'total_fee'=>$v['total_fee'],'amount'=>$v['amount'],'surname'=>'小热点',
                                'sex'=>1,'bless_id'=>1,'scope'=>$v['scope'],'area_id'=>$v['area_id'],'mac'=>$bv['mac'],'pay_fee'=>$v['total_fee'],
                                'pay_time'=>date('Y-m-d H:i:s'),'pay_type'=>10,'status'=>4);
                            $trade_no = $m_redpacket->addData($redpacket);
                            if($trade_no){
                                if($v['type']==1 || $v['type']==2){
                                    $this->updateData(array('id'=>$v['id']),array('status'=>0));
                                }else{
                                    if($nowdate==$v['end_date'] && $nowtime==$optime){
                                        $this->updateData(array('id'=>$v['id']),array('status'=>0));
                                    }
                                }
                                //根据红包总金额和人数进行分配红包
                                $money = $redpacket['total_fee'];
                                $num = $redpacket['amount'];
                                $all_money = bonus_random($money,$num,0.3,$money);
                                $redis  =  \Common\Lib\SavorRedis::getInstance();
                                $redis->select(5);
                                $key = C('SAPP_REDPACKET').$trade_no.':bonus';
                                $all_moneys = array('unused'=>$all_money,'used'=>array());
                                $redis->set($key,json_encode($all_moneys),86400);

                                $key_queue = C('SAPP_REDPACKET').$trade_no.':bonusqueue';
                                foreach ($all_money as $mv){
                                    $redis->rpush($key_queue,$mv);
                                }
                                //end

                                //推送红包小程序码到电视
                                $http_host = 'https://mobile.littlehotspot.com';
                                $qrinfo =  'bonus'.$trade_no;
                                $mpcode = $http_host.'/h5/qrcode/bonusQrcode?qrinfo='.$qrinfo;

                                if($v['sender']){
                                    $user_info = $all_senders[$v['sender']];
                                }else{
                                    shuffle($all_senders);
                                    $user_info = $all_senders[0];//随机
                                }
                                $user_info['avatarUrl'] = 'http://oss.littlehotspot.com/WeChat/MiniProgram/LaunchScreen/source/images/avatar/'.$user_info['id'].'.jpg';

                                $user_info['nickName'] = '热点投屏';
                                $user_info['avatarUrl'] = 'http://oss.littlehotspot.com/media/resource/btCfRRhHkn.jpg';
                                $where_user = array('id'=>$op_userid);
                                $m_user->updateInfo($where_user,array('nickName'=>$user_info['nickName'],'avatarUrl'=>$user_info['avatarUrl']));

                                $message = array('action'=>121,'nickName'=>$user_info['nickName'],'content'=>'靓照上电视，大屏分享更快乐',
                                    'avatarUrl'=>$user_info['avatarUrl'],'codeUrl'=>$mpcode);
                                $message['headPic'] = base64_encode($user_info['avatarUrl']);
                                $m_netty->pushBox($redpacket['mac'],json_encode($message));
                                if($redpacket_type!=1){
                                    echo "redpacket_id: $trade_no send ok \r\n";
                                }
                            }
                        }
                    }
                }else{
                    $redpacket = array('user_id'=>$op_userid,'total_fee'=>$v['total_fee'],'amount'=>$v['amount'],'surname'=>'小热点',
                        'sex'=>1,'bless_id'=>1,'scope'=>$v['scope'],'area_id'=>$v['area_id'],'mac'=>$v['mac'],'pay_fee'=>$v['total_fee'],
                        'pay_time'=>date('Y-m-d H:i:s'),'pay_type'=>10,'status'=>4);
                    $trade_no = $m_redpacket->addData($redpacket);
                    if($trade_no){
                        if($v['type']==1 || $v['type']==2){
                            $this->updateData(array('id'=>$v['id']),array('status'=>0));
                        }else{
                            if($nowdate==$v['end_date'] && $nowtime==$optime){
                                $this->updateData(array('id'=>$v['id']),array('status'=>0));
                            }
                        }
                        //根据红包总金额和人数进行分配红包
                        $money = $redpacket['total_fee'];
                        $num = $redpacket['amount'];
                        $all_money = bonus_random($money,$num,0.3,$money);
                        $redis  =  \Common\Lib\SavorRedis::getInstance();
                        $redis->select(5);
                        $key = C('SAPP_REDPACKET').$trade_no.':bonus';
                        $all_moneys = array('unused'=>$all_money,'used'=>array());
                        $redis->set($key,json_encode($all_moneys),86400);
                        $key_queue = C('SAPP_REDPACKET').$trade_no.':bonusqueue';
                        foreach ($all_money as $mv){
                            $redis->rpush($key_queue,$mv);
                        }
                        //end

                        //推送红包小程序码到电视
                        $http_host = 'https://mobile.littlehotspot.com';
                        $box_mac = $redpacket['mac'];
                        $qrinfo =  $trade_no.'_'.$box_mac;
                        $mpcode = $http_host.'/h5/qrcode/mpQrcode?qrinfo='.$qrinfo;

                        if($v['sender']){
                            $user_info = $all_senders[$v['sender']];
                        }else{
                            shuffle($all_senders);
                            $user_info = $all_senders[0];//随机
                        }
                        $user_info['avatarUrl'] = 'http://oss.littlehotspot.com/WeChat/MiniProgram/LaunchScreen/source/images/avatar/'.$user_info['id'].'.jpg';

                        $where_user = array('id'=>$op_userid);
                        $m_user->updateInfo($where_user,array('nickName'=>$user_info['nickName'],'avatarUrl'=>$user_info['avatarUrl']));

                        $message = array('action'=>121,'nickName'=>$user_info['nickName'],
                            'avatarUrl'=>$user_info['avatarUrl'],'codeUrl'=>$mpcode);
                        $bwhere = array('box.mac'=>$box_mac,'box.state'=>1,'box.flag'=>0);
                        $res_box = $m_mac->getBoxByCondition('hotel.id as hotel_id',$bwhere);
                        $hotel_id = intval($res_box[0]['hotel_id']);
                        if(isset($rd_hotel[$hotel_id]) && in_array($redpacket['scope'],array(2,3))){
                            $message['rtype'] = 2;
                            $message['nickName'] = $rd_hotel[$hotel_id]['short_name'];
                            $res_hotel_ext = $m_hotel_ext->getInfo(array('hotel_id'=>$hotel_id));
                            $hotel_logo = '';
                            if($res_hotel_ext['hotel_cover_media_id']>0){
                                $res_media = $m_media->getMediaInfoById($res_hotel_ext['hotel_cover_media_id']);
                                $hotel_logo = $res_media['oss_addr'];
                            }
                            $message['avatarUrl'] = $hotel_logo;
                        }
                        $message['headPic'] = base64_encode($message['avatarUrl']);
                        $res_netty = $m_netty->pushBox($redpacket['mac'],json_encode($message));
                        if($redpacket_type!=1){
                            $netty_data = json_encode($res_netty);
                            echo "redpacket_id: $trade_no box:$box_mac message:".json_encode($message)."netty:$netty_data \r\n";
                        }

                        //发送范围 1全网餐厅电视,2当前餐厅所有电视,3当前包间电视 4区域红包
                        $scope = $redpacket['scope'];
                        if(in_array($scope,array(1,2))) {
                            //发全网红包
                            $all_box = $m_netty->getPushBox(2, $box_mac);
                            if (!empty($all_box)) {
                                foreach ($all_box as $v) {
                                    $qrinfo = $trade_no.'_'.$v;
                                    $mpcode = $http_host . '/h5/qrcode/mpQrcode?qrinfo=' . $qrinfo;
                                    $message['codeUrl'] = $mpcode;
                                    $res_netty_box = $m_netty->pushBox($v, json_encode($message));
                                    if($redpacket_type!=1){
                                        $netty_data = json_encode($res_netty_box);
                                        echo "redpacket_id: $trade_no box:$box_mac message:".json_encode($message)."netty:$netty_data \r\n";
                                    }
                                }
                            }
                            if ($scope == 1) {
                                $key = C('SAPP_REDPACKET') . 'smallprogramcode';
                                $res_data = array('order_id' => $trade_no, 'box_list' => $all_box,'scope'=>1,
                                    'nickName' => $user_info['nickName'], 'avatarUrl' => $user_info['avatarUrl']);
                                $res_data['headPic'] = base64_encode($res_data['avatarUrl']);
                                $redis->set($key, json_encode($res_data));
                            }
                        }elseif($scope==4){
                            $all_box = $m_netty->getPushBox(4,$box_mac,$redpacket['area_id']);
                            $key = C('SAPP_REDPACKET') . 'smallprogramcode';
                            $res_data = array('order_id' => $trade_no, 'box_list' => $all_box,'scope'=>4,
                                'nickName' => $user_info['nickName'], 'avatarUrl' => $user_info['avatarUrl']);
                            $res_data['headPic'] = base64_encode($res_data['avatarUrl']);
                            $redis->set($key, json_encode($res_data));
                        }
                        if($redpacket_type!=1){
                            echo "redpacket_id: $trade_no send ok \r\n";
                        }
                        //end

//                    $nowdatetime = date('Y-m-d H:i:s');
//                    $log_content = $nowdatetime.'[redpacket_id]'.$trade_no."\n";
//                    $log_file_name = '/application/logs/smallapp/'.'operationbonus_'.date("Ymd").".log";
//                    @file_put_contents($log_file_name, $log_content, FILE_APPEND);
                    }
                }

            }
        }
//        $nowdatetime = date('Y-m-d H:i:s');
//        $log_content = $nowdatetime.'[redpacket_list]'.json_encode($res_list)."\n";
//        $log_file_name = '/application/logs/smallapp/'.'operationbonus_'.date("Ymd").".log";
//        @file_put_contents($log_file_name, $log_content, FILE_APPEND);
    }

    public function againpush_redpacket(){
        $operation_uid = 42996;
        $m_order = new \Admin\Model\Smallapp\RedpacketModel();
        $where = array('user_id'=>$operation_uid,'status'=>array('in','4,6'),'scope'=>array('in','2,3'));
        $again_time = date('Y-m-d H:i:s',time()-3600);
        $where['add_time'] = array('egt',$again_time);
        $res_order = $m_order->getDataList('*',$where,'id asc');
        $nowdtime = date('Y-m-d H:i:s');
        if(empty($res_order)){
            echo $nowdtime.' no send redpacket'."\r\n";
            exit;
        }
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $m_media = new \Admin\Model\MediaModel();
        $rd_hotel = C('RD_TEST_HOTEL');
        foreach ($res_order as $v) {
            //推送红包小程序码到电视
            $trade_no = $v['id'];
            $one_again_time = strtotime($v['add_time'])+1800;
            $now_t = time();
            if($now_t>$one_again_time){
                echo "redpacket_id: $trade_no agagin finish \r\n";
                continue;
            }
            $bwhere = array('box.mac'=>$v['mac'],'box.state'=>1,'box.flag'=>0);
            $res_box = $m_box->getBoxByCondition('hotel.id as hotel_id',$bwhere);
            $hotel_id = intval($res_box[0]['hotel_id']);
            if(!isset($rd_hotel[$hotel_id])){
                echo "redpacket_id: $trade_no hotel_id:$hotel_id not rdtest hotel \r\n";
                continue;
            }
            $redpacket = $v;
            $http_host = 'https://mobile.littlehotspot.com';
            $box_mac = $redpacket['mac'];
            $qrinfo = $trade_no . '_' . $box_mac;
            $mpcode = $http_host . '/h5/qrcode/mpQrcode?qrinfo=' . $qrinfo;

            $res_hotel_ext = $m_hotel_ext->getInfo(array('hotel_id'=>$hotel_id));
            $hotel_logo = '';
            if($res_hotel_ext['hotel_cover_media_id']>0){
                $res_media = $m_media->getMediaInfoById($res_hotel_ext['hotel_cover_media_id']);
                $hotel_logo = $res_media['oss_addr'];
            }
            $user_info = array('nickName'=>$rd_hotel[$hotel_id]['short_name'],'avatarUrl'=>$hotel_logo);
            $message = array('action'=>121, 'nickName'=>$user_info['nickName'],
                'avatarUrl'=>$user_info['avatarUrl'], 'codeUrl'=>$mpcode,'rtype'=>2);
            $message['headPic'] = base64_encode($user_info['avatarUrl']);
            $res_netty = $m_netty->pushBox($redpacket['mac'], json_encode($message));
            $netty_data = json_encode($res_netty);
            echo "redpacket_id: $trade_no box:$box_mac message:" . json_encode($message) . "netty:$netty_data \r\n";

            //发送范围 1全网餐厅电视,2当前餐厅所有电视,3当前包间电视 4区域红包
            $scope = $redpacket['scope'];
            if($scope==2){
                $all_box = $m_netty->getPushBox(2, $box_mac);
                if(!empty($all_box)){
                    foreach ($all_box as $v){
                        $qrinfo = $trade_no . '_' . $v;
                        $mpcode = $http_host . '/h5/qrcode/mpQrcode?qrinfo=' . $qrinfo;
                        $message['codeUrl'] = $mpcode;
                        $res_netty_box = $m_netty->pushBox($v, json_encode($message));
                        $netty_data = json_encode($res_netty_box);
                        echo "redpacket_id: $trade_no box:$v message:" . json_encode($message) . "netty:$netty_data \r\n";
                    }
                }
            }
            $now_time = date('Y-m-d H:i:s');
            echo "redpacket_id: $trade_no sendok time:$now_time \r\n";
        }


    }
}