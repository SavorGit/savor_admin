<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class SyslotteryModel extends BaseModel{
	protected $tableName='smallapp_syslottery';

    public function push_syslottery(){
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
        $host_name = C('HOST_NAME');
        $m_lotteryprize = new \Admin\Model\Smallapp\SyslotteryPrizeModel();
        $m_activity = new \Admin\Model\Smallapp\ActivityModel();
        $m_activityprize = new \Admin\Model\Smallapp\ActivityprizeModel();
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $m_media = new \Admin\Model\MediaModel();
        $nowtime = date('H:i');
        foreach ($res as $v){
            $hotel_id = $v['hotel_id'];
            $optime = date('H:i',strtotime($v['timing']));
            if($now_date>=$v['start_date'] && $now_date<=$v['end_date'] && $nowtime==$optime){
                $syslottery_id = $v['id'];
                $fileds = 'count(id) as num,sum(probability) as probability';
                $res_lottery_prize = $m_lotteryprize->getDataList($fileds,array('syslottery_id'=>$syslottery_id),'id desc');
                if($res_lottery_prize[0]['num']<3 || $res_lottery_prize[0]['probability']!=100){
                    echo "ID:{$v['id']} config error num:{$res_lottery_prize[0]['num']},probability:{$res_lottery_prize[0]['probability']} \r\n";
                    continue;
                }
                $start_time = date('Y-m-d H:i:s');
                $end_time = date('Y-m-d H:i:s',time()+7200);
                $add_activity_data = array('hotel_id'=>$hotel_id,'name'=>'系统抽奖','prize'=>$v['prize'],
                    'start_time'=>$start_time,'end_time'=>$end_time,'type'=>3);
                $activity_id = $m_activity->add($add_activity_data);
                $res_lottery_prize = $m_lotteryprize->getDataList('*',array('syslottery_id'=>$syslottery_id),'id desc');
                $prize_data = array();
                foreach ($res_lottery_prize as $pv){
                    $prize_data[]=array('activity_id'=>$activity_id,'name'=>$pv['name'],'money'=>$pv['money'],'image_url'=>$pv['image_url'],
                        'probability'=>$pv['probability'],'type'=>$pv['type'],'interact_num'=>$pv['interact_num'],
                        'demand_hotplay_num'=>$pv['demand_hotplay_num'],'demand_banner_num'=>$pv['demand_banner_num']
                    );
                }
                $m_activityprize->addAll($prize_data);
                echo "ID:{$v['id']} activity_id:$activity_id ok\r\n";

                $res_hotel_ext = $m_hotel_ext->getInfo(array('hotel_id'=>$hotel_id));
                $hotel_logo = '';
                if($res_hotel_ext['hotel_cover_media_id']>0){
                    $res_media = $m_media->getMediaInfoById($res_hotel_ext['hotel_cover_media_id']);
                    $hotel_logo = $res_media['oss_addr'];
                }
                $res_hotel = $m_hotel->getOne($hotel_id);

                $bwhere = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
                $res_box = $m_box->getBoxByCondition('box.id as box_id,box.mac',$bwhere);
                if(!empty($res_box)){
                    $headPic = base64_encode($hotel_logo);
                    $message = array('action'=>138,'countdown'=>120,'nickName'=>$res_hotel['name'],'headPic'=>$headPic);
                    foreach ($res_box as $bv){
                        $code_url = $host_name."/Smallapp46/qrcode/getBoxQrcode?box_id={$bv['box_id']}&box_mac={$bv['mac']}&data_id={$activity_id}&type=38";
                        $message['codeUrl']=$code_url;
                        $res_netty = $m_netty->pushBox($bv['mac'],json_encode($message));
                        $netty_data = json_encode($res_netty);
                        echo "activity_id:$activity_id box:{$bv['mac']} message:" . json_encode($message) . "netty:$netty_data \r\n";
                    }
                }

            }else{
                echo "ID:{$v['id']} not in time $optime \r\n";
            }
        }

    }
}