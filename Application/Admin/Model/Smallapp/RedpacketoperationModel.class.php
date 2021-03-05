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
        $nowdate = date('Y-m-d');
        $nowtime = date('H:i');
        $nowdatetime = date("Y-m-d H:i:s");
        foreach ($res_list as $v){
            switch ($v['type']){//类型 1立即发送,2单次定时,3多次定时
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

                                $where_user = array('id'=>$op_userid);
                                $m_user->updateInfo($where_user,array('nickName'=>$user_info['nickName'],'avatarUrl'=>$user_info['avatarUrl']));

                                $message = array('action'=>121,'nickName'=>$user_info['nickName'],'content'=>'快，使用热点投屏，大屏分享更快乐！',
                                    'avatarUrl'=>$user_info['avatarUrl'],'codeUrl'=>$mpcode);
                                $m_netty->pushBox($redpacket['mac'],json_encode($message));

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
                        $m_netty->pushBox($redpacket['mac'],json_encode($message));

                        //发送范围 1全网餐厅电视,2当前餐厅所有电视,3当前包间电视 4区域红包
                        $scope = $redpacket['scope'];
                        if(in_array($scope,array(1,2))) {
                            //发全网红包
                            $all_box = $m_netty->getPushBox(2, $box_mac);
                            if (!empty($all_box)) {
                                foreach ($all_box as $v) {
                                    $qrinfo = $trade_no.'_'.$v;
                                    $mpcode = $http_host . '/h5/qrcode/mpQrcode?qrinfo=' . $qrinfo;
                                    $message = array('action' => 121, 'nickName' => $user_info['nickName'],
                                        'avatarUrl' => $user_info['avatarUrl'], 'codeUrl' => $mpcode);
                                    $m_netty->pushBox($v, json_encode($message));
                                }
                            }
                            if ($scope == 1) {
                                $key = C('SAPP_REDPACKET') . 'smallprogramcode';
                                $res_data = array('order_id' => $trade_no, 'box_list' => $all_box,'scope'=>1,
                                    'nickName' => $user_info['nickName'], 'avatarUrl' => $user_info['avatarUrl']);
                                $redis->set($key, json_encode($res_data));
                            }
                        }elseif($scope==4){
                            $all_box = $m_netty->getPushBox(4,$box_mac,$redpacket['area_id']);
                            $key = C('SAPP_REDPACKET') . 'smallprogramcode';
                            $res_data = array('order_id' => $trade_no, 'box_list' => $all_box,'scope'=>4,
                                'nickName' => $user_info['nickName'], 'avatarUrl' => $user_info['avatarUrl']);
                            $redis->set($key, json_encode($res_data));
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
}