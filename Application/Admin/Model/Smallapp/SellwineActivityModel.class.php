<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class SellwineActivityModel extends BaseModel{
	protected $tableName='sellwine_activity';

	public function pushsellwineactivity(){
        $now_time = date('Y-m-d H:i:s');
        $where = array('status'=>1);
        $where['start_date'] = array('elt',$now_time);
        $where['end_date'] = array('egt',$now_time);
	    $res_data = $this->getDataList('*',$where,'id asc');
        $m_media = new \Admin\Model\MediaModel();
        $m_activity_goods = new \Admin\Model\Smallapp\SellwineActivityGoodsModel();
        $m_activity_hotel = new \Admin\Model\Smallapp\SellwineActivityHotelModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $sellwine_config = C('SELLWINE_ACTIVITY');
	    foreach ($res_data as $v){
	        $activity_id = $v['id'];
	        $res_activity_goods = $m_activity_goods->getDataList('money',array('activity_id'=>$activity_id,'status'=>1),'money asc');
	        if(empty($res_activity_goods)){
	            echo "activity_id:$activity_id no goods \r\n";
	            continue;
            }
	        $res_hotels = $m_activity_hotel->getDataList('*',array('activity_id'=>$activity_id,'status'=>1),'id asc');
	        if(empty($res_hotels)){
                echo "activity_id:$activity_id no hotel \r\n";
                continue;
            }
	        $min_money = intval($res_activity_goods[0]['money']);
	        if(count($res_activity_goods)==1){
	            $content = "{$min_money}元红包";
            }else{
	            $last_num = count($res_activity_goods) - 1;
	            $max_money = intval($res_activity_goods[$last_num]['money']);
                $content = "{$min_money}-{$max_money}元红包";
            }

            $lunch_stime = date("Y-m-d {$v['lunch_start_time']}");
            $lunch_etime = date("Y-m-d {$v['lunch_end_time']}");
            $dinner_stime = date("Y-m-d {$v['dinner_start_time']}");
            $dinner_etime = date("Y-m-d {$v['dinner_end_time']}");
            $meal_type = '';
            $meal_stime = $meal_etime = '';
            if($now_time>=$lunch_stime && $now_time<=$lunch_etime){
                $meal_type = 'lunch';
                $meal_stime = $lunch_stime;
                $meal_etime = $lunch_etime;
            }elseif($now_time>=$dinner_stime && $now_time<=$dinner_etime){
                $meal_type = 'dinner';
                $meal_stime = $dinner_stime;
                $meal_etime = $dinner_etime;
            }
            if(empty($meal_type)){
                echo "activity_id:$activity_id no in mealtime \r\n";
                continue;
            }
            $now_hour_time = strtotime(date('Y-m-d H:i:00'));
            $diff_time = $now_hour_time-strtotime($meal_stime);
            $interval_time = $v['interval_time']*60;
            $interval_num = $diff_time%$interval_time;
            $is_push = 0;
            if($diff_time==0 || $interval_num==0){
                $is_push = 1;
            }
            if($is_push==0){
                echo "activity_id:$activity_id no in pushtime \r\n";
                continue;
            }

            $now_strtime = time();
            $meal_stretime = strtotime($meal_etime);
            $countdown = $meal_stretime-$now_strtime>0?$meal_stretime-$now_strtime:0;
            $res_media = $m_media->getMediaInfoById($v['tvleftmedia_id']);
            $img_path = $res_media['oss_path'];
            $netty_data = array('action'=>163,'img_path'=>$img_path,'video_path'=>$sellwine_config['url'],
                'filename'=>$sellwine_config['filename'],'name'=>'每瓶酒可奖励','content'=>$content,'countdown'=>$countdown);
            $message = json_encode($netty_data);

            foreach ($res_hotels as $hv){
                $hotel_id = $hv['hotel_id'];
                $fields = 'box.mac';
                $where = array('box.state'=>1,'box.flag'=>0,'hotel.id'=>$hotel_id);
                $res_bdata = $m_box->getBoxByCondition($fields,$where,'');
                foreach ($res_bdata as $bv){
                    $ret = $m_netty->pushBox($bv['mac'],$message);
                    if(isset($ret['error_code'])){
                        $ret_str = json_encode($ret);
                        echo "activity_id:{$v['id']},hotel_id:$hotel_id,box_mac:{$bv['mac']} push error $ret_str \r\n";
                    }else{
                        echo "activity_id:{$v['id']},hotel_id:$hotel_id,box_mac:{$bv['mac']} push ok \r\n";
                    }
                }
                echo "activity_id:{$v['id']},hotel_id:$hotel_id ok \r\n";
            }

        }
    }
}