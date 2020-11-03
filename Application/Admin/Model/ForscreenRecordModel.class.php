<?php
namespace Admin\Model;

class ForscreenRecordModel extends BaseModel{
	protected $tableName='smallapp_forscreen_record';
	
    public function addInfo($data,$type=1){
	    if($type==1){
	        $ret = $this->add($data);
	        
	    }else {
	        $ret = $this->addAll($data);
	    }
	    return $ret;
	}

	public function updateInfo($where,$data){
	    $ret = $this->where($where)->save($data);
	    return $ret;
	}

	public function getWhere($fields,$where,$order,$limit,$group){
	    $data = $this->alias('a')
	                 ->join('savor_box box on a.box_mac=box.mac','left')
	                 ->join('savor_room room on box.room_id=room.id','left')
	                 ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	                 ->field($fields)->where($where)
	                 ->order($order)->limit($limit)
	                 ->group($group)->select();
	    return $data;
	}

	public function getOne($fields,$where){
	    $data = $this->field($fields)->where($where)->find();
	    return $data;
	}

	public function countWhere($where){
	    $nums = $this->alias('a')
	                 ->join('savor_box box on a.box_mac=box.mac','left')
	                 ->join('savor_room room on box.room_id=room.id','left')
	                 ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
	                 ->where($where)->count();
	    return $nums;
	}

	public function syncForscreendata(){
        ini_set("memory_limit","2024M");
        $where = array();
        $where['create_time'] = array(array('EGT','2020-01-01 00:00:00'),array('ELT','2020-02-31 23:59:59'));

//        $where['create_time'] = array(array('EGT','2020-05-01 00:00:00'),array('ELT','2020-06-31 23:59:59'));


        $res = $this->getDataList('*',$where,'id asc');
        foreach ($res as $v){
            $id = $v['id'];
            $box_mac = $v['box_mac'];
            $sql_box = "select * from savor_box where mac='{$box_mac}' and state=1 and flag=0";
            $res_box = $this->query($sql_box);
            if(!empty($res_box)){
                $box_info = $res_box[0];
            }else{
                $sql_box = "select * from savor_box where mac='{$box_mac}' order by id desc limit 0,1";
                $res_box = $this->query($sql_box);
                $box_info = $res_box[0];
            }
            if(!empty($box_info)){
                $box_id = $box_info['id'];
                $box_name = $box_info['name'];
                $is_4g = $box_info['is_4g'];
                $box_type = $box_info['box_type'];
                $room_id = $box_info['room_id'];
                $sql_room = "select * from savor_room where id={$room_id}";
                $res_room = $this->query($sql_room);
                if(!empty($res_room)){
                    $room_name = $res_room[0]['name'];
                    $hotel_id = $res_room[0]['hotel_id'];

                    $sql_hotel = "select * from savor_hotel where id={$hotel_id}";
                    $res_hotel = $this->query($sql_hotel);
                    if(!empty($res_hotel)){
                        $hotel_name = $res_hotel[0]['name'];
                        $area_id = $res_hotel[0]['area_id'];
                        $hotel_box_type = $res_hotel[0]['hotel_box_type'];
                        $hotel_is_4g = $res_hotel[0]['is_4g'];
                        $sql_area = "select * from savor_area_info where id={$area_id}";
                        $res_area = $this->query($sql_area);
                        $area_name = '';
                        if(!empty($res_area)){
                            $area_name = $res_area[0]['region_name'];
                        }
                        $data = array('area_id'=>$area_id,'area_name'=>$area_name,'hotel_id'=>$hotel_id,'hotel_name'=>$hotel_name,
                            'room_id'=>$room_id,'room_name'=>$room_name,'box_id'=>$box_id,'box_name'=>$box_name,'is_4g'=>$is_4g,'box_type'=>$box_type,
                            'hotel_box_type'=>$hotel_box_type,'hotel_is_4g'=>$hotel_is_4g);
                        $this->updateData(array('id'=>$id),$data);
                    }
                }
                echo "ID: $id {$v['create_time']} ok \r\n";
            }else{
                echo "ID: $id {$v['create_time']} error \r\n";
            }


        }

    }

	public function forscreen_4gbox(){
        $yestoday = date('Ymd',strtotime("-1 day"));
        $now_date = date('Ymd');
        $now_hour = date('G');
        $redis = new \Common\Lib\SavorRedis();

        $yestoday_hour = 0;
        if($now_hour>3){
            $sql_time = " log.date=$now_date and log.type=2 and log.hour{$now_hour}>0 ";
            $begin_hour = $now_hour - 4;
            for ($i=$begin_hour;$i<$now_hour;$i++){
                $sql_time.=" and log.hour{$i}>0";
            }
        }elseif($now_hour==0){
            $sql_time = " log.date=$yestoday and log.type=2";
            $now_hour = 24;
            $begin_hour = $now_hour - 4;
            for ($i=$begin_hour;$i<$now_hour;$i++){
                $sql_time.=" and log.hour{$i}>0";
            }
        }else{
            $yestoday_hour = 4 - $now_hour;
            $sql_time = " log.date=$now_date and log.type=2 and log.hour{$now_hour}>0 ";
            for ($i=0;$i<$now_hour;$i++){
                $sql_time.=" and log.hour{$i}>0";
            }
        }
        $sql = "SELECT log.hotel_id,log.hotel_name,log.room_id,log.room_name,log.box_id,log.mac from savor_heart_all_log as log left join savor_box as box on log.mac=box.mac where $sql_time and box.state=1 and box.flag=0 and box.is_4g=1";
        echo "sql:$sql \r\n";
        $res_box = $this->query($sql);
        $boxs = array();
        if(!empty($res_box)){
            foreach ($res_box as $v){
                $box_mac = $v['mac'];

                $is_heart = 0;
                $redis->select(13);
                $heartkey = "heartbeat:2:$box_mac";
                $res_heart = $redis->get($heartkey);
                if(!empty($res_heart)){
                    $res_heart = json_decode($res_heart,true);
                    $heart_time = $res_heart['date'];
                    $now_time = date('YmdH0000');
                    if($heart_time>$now_time){
                        $is_heart = 1;
                    }
                }
                if($is_heart==0){
                    continue;
                }

                if($yestoday_hour){
                    $sql_time = " date=$yestoday and mac='{$box_mac}' and type=2";
                    $now_hour = 24;
                    $begin_hour = $now_hour - $yestoday_hour;
                    for ($i=$begin_hour;$i<$now_hour;$i++){
                        $sql_time.=" and hour{$i}>0";
                    }
                    $sql_box = "SELECT id from savor_heart_all_log where $sql_time";
                    $res_binfo = $this->query($sql_box);
                    if(!empty($res_binfo)){
                        $boxs[]=$box_mac;
                    }
                }else{
                    $boxs[]=$box_mac;
                }
            }
        }

        $message_data = array('openid'=>'ofYZG417MIHCyVZkq-RbiIddn_8s','action'=>2,'resource_type'=>2,'forscreen_char'=>'',
            'mobile_brand'=>'dev4gtools','mobile_model'=>'dev4gtools','resource_size'=>1149039,'imgs'=>'["forscreen/resource/15368043845967.mp4"]');
        echo 'boxs:'.json_encode($boxs)."\r\n";

        $push_boxs = array();
        if(!empty($boxs)){
            foreach ($boxs as $b){
                $now_timestamps = getMillisecond();
                $message_data['forscreen_id'] = $now_timestamps;
                $message_data['box_mac'] = $b;
                $message_data['resource_id'] = $now_timestamps;
                $message_data['res_sup_time'] = $now_timestamps;
                $message_data['res_eup_time'] = $now_timestamps;
                $message_data['create_time'] = date('Y-m-d H:i:s');

                $netty_data = array('action'=>2,'resource_type'=>2,'url'=>'forscreen/resource/15368043845967.mp4','filename'=>"$now_timestamps.mp4",
                    'openid'=>'ofYZG417MIHCyVZkq-RbiIddn_8s','video_id'=>$now_timestamps,'forscreen_id'=>$now_timestamps
                    );
                $msg = json_encode($netty_data);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://mobile.littlehotspot.com/Netty/index/pushnetty",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => array('box_mac'=>$b,'msg'=>"$msg"),
                ));
                $response = curl_exec($curl);
                curl_close($curl);
                $res = json_decode($response,true);
                if(is_array($res) && isset($res['code'])){
                    $push_boxs[]=$b;
                    $cache_key = 'smallapp:forscreen:'.$b;
                    $redis->select(5);
                    $redis->rpush($cache_key, json_encode($message_data));
                }
            }
        }
        echo 'push box:'.json_encode($push_boxs)."\r\n";
    }
}