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

	public function forscreen_4gbox(){
        $yestoday = date('Ymd',strtotime("-1 day"));
        $now_date = date('Ymd');
        $now_hour = date('G');

        $yestoday_hour = 0;
        if($now_hour>3){
            $sql_time = " log.date=$now_date and log.type=2";
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
            $sql_time = " log.date=$now_date and log.type=2";
            for ($i=0;$i<$now_hour;$i++){
                $sql_time.=" and log.hour{$i}>0";
            }
        }
        $sql = "SELECT log.hotel_id,log.hotel_name,log.room_id,log.room_name,log.box_id,log.mac from savor_heart_all_log as log left join savor_box as box on log.mac=box.mac where $sql_time and box.is_4g=1";
        echo "sql:$sql \r\n";
        $res_box = $this->query($sql);
        $boxs = array();
        if(!empty($res_box)){
            foreach ($res_box as $v){
                $box_mac = $v['mac'];
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
            $redis = new \Common\Lib\SavorRedis();
            foreach ($boxs as $b){
                $now_timestamps = getMillisecond();
                $message_data['forscreen_id'] = $now_timestamps;
                $message_data['box_mac'] = $b;
                $message_data['resource_id'] = $now_timestamps;
                $message_data['res_sup_time'] = $now_timestamps;
                $message_data['res_eup_time'] = $now_timestamps;
                $message_data['create_time'] = date('Y-m-d H:i:s');

                $netty_data = array('action'=>2,'resource_type'=>2,'url'=>'forscreen/resource/15368043845967.mp4','filename'=>"15368043845967.mp4",
                    'openid'=>'ofYZG417MIHCyVZkq-RbiIddn_8s','video_id'=>$now_timestamps,'forscreen_id'=>$now_timestamps);
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