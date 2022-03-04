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
                    'start_time'=>$start_time,'end_time'=>$end_time,'lottery_time'=>$lottery_time,'status'=>1,'type'=>$type);
                $activity_id = $m_activity->add($add_activity_data);

                echo "ID:{$v['id']} activity_id:$activity_id ok\r\n";

                $bwhere = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
                $res_box = $m_box->getBoxByCondition('box.id as box_id,box.mac',$bwhere);
                if(!empty($res_box)){
                    $partakedish_img = $v['image_url'].'?x-oss-process=image/resize,m_mfit,h_200,w_300';
                    $dish_name_info = pathinfo($v['image_url']);
                    $now_time = time();
                    $lottery_countdown = strtotime($lottery_time) - $now_time;
                    $lottery_countdown = $lottery_countdown>0?$lottery_countdown:0;

                    $netty_msg = array(
                        'lottery_countdown'=>$lottery_countdown,'partake_img'=>$partakedish_img,'partake_filename'=>$dish_name_info['basename'],
                        'partake_name'=>$v['prize'],'activity_name'=>$v['name'],
                    );

                    if($v['type']==2){
                        $netty_msg['action'] = 158;
                    }else{
                        $netty_msg['lottery_time']=date('H:i',strtotime($lottery_time));
                        $netty_msg['action'] = 135;
                        $netty_msg['countdown'] = 180;
                    }

                    foreach ($res_box as $bv){
                        if($v['type']==2){
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
}