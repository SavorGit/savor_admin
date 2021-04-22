<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class ForscreenTrackModel extends BaseModel{
	protected $tableName='smallapp_forscreen_track';

	public function handle_forscreen_track(){
        $hourtime = date("Y-m-d H", strtotime("-1 hour"));
        $start_time = "$hourtime:00:00";
        $end_time = "$hourtime:59:59";

        $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $m_smallapp_forscreen_invalidrecord = new \Admin\Model\Smallapp\ForscreeninvalidrecordModel();

        $where = array();
        $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $where['small_app_id'] = array('in',array(1,2));
        $result = $m_forscreen->getDataList('*',$where,'id desc');
        if(!empty($result)){
            $m_heart_log = new \Admin\Model\HeartLogModel();
            $redis = new \Common\Lib\SavorRedis();
            $redis->select(5);
            $cache_key = C('SAPP_FORSCREENTRACK');
            foreach ($result as $v){
                $serial_no = $this->getForscreenSerialNumber($v);
                if(!empty($serial_no)){
                    $res_cache = $redis->get($cache_key.$serial_no);
                    if(!empty($res_cache)){
                        $data = json_decode($res_cache,true);
                        unset($data['action']);
                        if(isset($data['netty_position_result'])){
                            $data['netty_position_result'] = json_encode($data['netty_position_result']);
                        }
                        if(isset($data['netty_result'])){
                            $data['netty_result'] = json_encode($data['netty_result']);
                        }
                        $data['netty_position_url'] = urldecode($data['netty_position_url']);
                        $data['netty_url'] = str_replace('\\', '',urldecode($data['netty_url']));
                        $data['forscreen_record_id'] = $v['id'];
                        $data['serial_number'] = $serial_no;
                        if(isset($data['box_play_time'])){
                            $data['box_play_time'] = $data['box_play_time'];
                        }
                        if(isset($data['netty_callback_result'])){
                            if(!empty($data['netty_callback_result'])){
                                $data['netty_callback_result'] = json_encode($data['netty_callback_result']);
                            }
                            $data['netty_callback_time'] = intval($data['netty_callback_time']);
                        }
                        $netty_key = $cache_key.$serial_no.'netty_time';
                        if(empty($data['netty_receive_time'])){
                            $res_nettycache = $redis->hget($netty_key,'netty_receive_time');
                            if(!empty($res_nettycache)){
                                $data['netty_receive_time'] = $res_nettycache;
                            }
                        }
                        if(empty($data['netty_pushbox_time'])){
                            $res_nettycache = $redis->hget($netty_key,'netty_pushbox_time');
                            if(!empty($res_nettycache)){
                                $data['netty_pushbox_time'] = $res_nettycache;
                            }
                        }
                        if($v['action']==4 || $v['action']==11){
                            $res_heart = $m_heart_log->getInfo('*',array('box_id'=>$v['box_id']),'');
                            if(!empty($res_heart) && $res_heart['type']==2 && $res_heart['apk_version']>'2.2.6'){
                                $data['oss_stime'] = $v['res_sup_time'];
                                $data['oss_etime'] = $v['res_eup_time'];

                                $req_id = $serial_no.'subdata:'.$v['forscreen_id'].'-'.$v['resource_id'];
                                $res_cache = $redis->get($cache_key.$req_id);
                                if(!empty($res_cache)){
                                    $cache_data = json_decode($res_cache,true);
                                    if(isset($cache_data['box_play_time'])){
                                        $data['box_play_time'] = $cache_data['box_play_time'];
                                    }
                                    if(isset($cache_data['box_finish_downtime'])){
                                        $data['box_finish_downtime'] = $cache_data['box_finish_downtime'];
                                    }
                                    if(isset($cache_data['box_downstime']) && isset($cache_data['box_downetime'])){
                                        $data['box_downstime'] = $cache_data['box_downstime'];
                                        $data['box_downetime'] = $cache_data['box_downetime'];
                                    }
                                }
                            }

                        }
                        $data['oss_stime'] = intval($data['oss_stime']);
                        $data['oss_etime'] = intval($data['oss_etime']);

                        $result = $this->getTrackResult($v,$data);
                        $data['is_success'] = $result['is_success'];
                        $data['total_time'] = $result['total_time'];

                        $is_del = 0;
                        if(isset($data['netty_position_result'])){
                            $netty_position_result = json_decode($data['netty_position_result'],true);
                            if($netty_position_result['code']==10008){
                                $is_del = 1;
                            }
                        }else{
                            if(isset($data['netty_result'])){
                                $netty_result = json_decode($data['netty_result'],true);
                                if($netty_result['code']==10008){
                                    $is_del = 1;
                                }
                            }
                        }
                        $res_track = $this->field('id')->where(array('forscreen_record_id'=>$v['id']))->order('id desc')->find();
                        if(!empty($res_track)){
                            $id = $res_track['id'];
                            $this->where(array('id'=>$id))->save($data);
                        }else{
                            $this->add($data);
                        }
                        if($is_del && $v['mobile_brand']!='dev4gtools'){
                            $v['forscreen_record_id'] = $v['id'];
                            unset($v['id'],$v['category_id'],$v['spotstatus'],$v['scene_id'],$v['contentsoft_id'],$v['dinnernature_id'],
                                $v['personattr_id'],$v['remark'],$v['resource_name'],$v['md5_file'],$v['save_type'],$v['file_conversion_status'],
                                $v['box_finish_downtime'],$v['serial_number'],$v['quality_type'],$v['box_play_time']);
                            $res_invalid = $m_smallapp_forscreen_invalidrecord->addData($v);
                            if($res_invalid){
                                $m_forscreen->delData(array('id'=>$v['forscreen_record_id']));
                            }
                        }
                    }
                }
            }
        }
    }

    public function handle_noforscreen_track(){
        $start_time = date("Y-m-d 00:00:00");
        $hourtime = date("Y-m-d H", strtotime("-2 hour"));
        $end_time = "$hourtime:59:59";

        $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $m_heart_log = new \Admin\Model\HeartLogModel();
        $m_smallapp_forscreen_invalidrecord = new \Admin\Model\Smallapp\ForscreeninvalidrecordModel();

        $where = array();
        $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $where['small_app_id'] = array('in',array(1,2));
        $where['mobile_brand'] = array('neq','dev4gtools');
        $result = $m_forscreen->getDataList('*',$where,'id desc');
        if(!empty($result)){
            $redis = new \Common\Lib\SavorRedis();
            $redis->select(5);
            $cache_key = C('SAPP_FORSCREENTRACK');
            foreach ($result as $v){
                $serial_no = $this->getForscreenSerialNumber($v);
                if(!empty($serial_no)){
                    $res_cache = $redis->get($cache_key.$serial_no);
                    if(!empty($res_cache)){
                        $data = json_decode($res_cache,true);
                        unset($data['action']);
                        if(isset($data['netty_position_result'])){
                            $data['netty_position_result'] = json_encode($data['netty_position_result']);
                        }
                        if(isset($data['netty_result'])){
                            $data['netty_result'] = json_encode($data['netty_result']);
                        }
                        $data['netty_position_url'] = urldecode($data['netty_position_url']);
                        $data['netty_url'] = str_replace('\\', '',urldecode($data['netty_url']));
                        $data['forscreen_record_id'] = $v['id'];
                        $data['serial_number'] = $serial_no;
                        if(isset($data['box_play_time'])){
                            $data['box_play_time'] = $data['box_play_time'];
                        }
                        if(isset($data['netty_callback_result'])){
                            if(!empty($data['netty_callback_result'])){
                                $data['netty_callback_result'] = json_encode($data['netty_callback_result']);
                            }
                            $data['netty_callback_time'] = intval($data['netty_callback_time']);
                        }
                        $netty_key = $cache_key.$serial_no.'netty_time';
                        if(empty($data['netty_receive_time'])){
                            $res_nettycache = $redis->hget($netty_key,'netty_receive_time');
                            if(!empty($res_nettycache)){
                                $data['netty_receive_time'] = $res_nettycache;
                            }
                        }
                        if(empty($data['netty_pushbox_time'])){
                            $res_nettycache = $redis->hget($netty_key,'netty_pushbox_time');
                            if(!empty($res_nettycache)){
                                $data['netty_pushbox_time'] = $res_nettycache;
                            }
                        }
                        if($v['action']==4 || $v['action']==11){
                            $res_heart = $m_heart_log->getInfo('*',array('box_id'=>$v['box_id']),'');
                            if(!empty($res_heart) && $res_heart['type']==2 && $res_heart['apk_version']>'2.2.6'){
                                $data['oss_stime'] = $v['res_sup_time'];
                                $data['oss_etime'] = $v['res_eup_time'];

                                $req_id = $serial_no.'subdata:'.$v['forscreen_id'].'-'.$v['resource_id'];
                                $res_cache = $redis->get($cache_key.$req_id);
                                if(!empty($res_cache)){
                                    $cache_data = json_decode($res_cache,true);
                                    if(isset($cache_data['box_play_time'])){
                                        $data['box_play_time'] = $cache_data['box_play_time'];
                                    }
                                    if(isset($cache_data['box_finish_downtime'])){
                                        $data['box_finish_downtime'] = $cache_data['box_finish_downtime'];
                                    }
                                    if(isset($cache_data['box_downstime']) && isset($cache_data['box_downetime'])){
                                        $data['box_downstime'] = $cache_data['box_downstime'];
                                        $data['box_downetime'] = $cache_data['box_downetime'];
                                    }
                                }
                            }

                        }
                        $data['oss_stime'] = intval($data['oss_stime']);
                        $data['oss_etime'] = intval($data['oss_etime']);

                        $result = $this->getTrackResult($v,$data);
                        $data['is_success'] = $result['is_success'];
                        $data['total_time'] = $result['total_time'];
                        $res_track = $this->field('id')->where(array('forscreen_record_id'=>$v['id']))->order('id desc')->find();
                        if(empty($res_track)){
                            $this->add($data);
                            echo "ID: {$v['id']} time:{$v['create_time']} track is_success:{$result['is_success']} ok \r\n";
                        }
                        /*if(!empty($res_track)){
                            $id = $res_track['id'];
                            $this->where(array('id'=>$id))->save($data);
                        }else{
                            $this->add($data);
                        }*/
                        $is_del = 0;
                        if(isset($data['netty_position_result'])){
                            $netty_position_result = json_decode($data['netty_position_result'],true);
                            if($netty_position_result['code']==10008){
                                $is_del = 1;
                            }
                        }else{
                            if(isset($data['netty_result'])){
                                $netty_result = json_decode($data['netty_result'],true);
                                if($netty_result['code']==10008){
                                    $is_del = 1;
                                }
                            }
                        }
                        if($is_del && $v['mobile_brand']!='dev4gtools'){
                            $v['forscreen_record_id'] = $v['id'];
                            unset($v['id'],$v['category_id'],$v['spotstatus'],$v['scene_id'],$v['contentsoft_id'],$v['dinnernature_id'],
                                $v['personattr_id'],$v['remark'],$v['resource_name'],$v['md5_file'],$v['save_type'],$v['file_conversion_status'],
                                $v['box_finish_downtime'],$v['serial_number'],$v['quality_type'],$v['box_play_time']);
                            $res_invalid = $m_smallapp_forscreen_invalidrecord->addData($v);
                            if($res_invalid){
                                $m_forscreen->delData(array('id'=>$v['forscreen_record_id']));
                            }
                        }
                    }
                }
            }
        }
    }

    public function getForscreenSerialNumber($forscreen){
	    $map_action = array(46=>4,48=>4,47=>2,49=>2);
	    if(isset($map_action[$forscreen['action']])){
            $forscreen['action'] = $map_action[$forscreen['action']];
        }
        $has_img_action = array(2,4,5,12,13,21,22,30,31,32);
        $other_action = array(8,9,11);

        if(in_array($forscreen['action'],$has_img_action)){
            $oss_addr = '';
            if(!empty($forscreen['imgs'])){
                $oss_info = json_decode($forscreen['imgs'],true);
                $oss_addr = $oss_info[0];
            }
            if($forscreen['action']==31 || $forscreen['action']==32){
                if(!empty($forscreen['resource_id'])){
                    $forscreen['forscreen_id'] = $forscreen['resource_id'];
                }
            }
            $serial_no = forscreen_serial($forscreen['openid'],$forscreen['forscreen_id'],$oss_addr);
            if($forscreen['action']==4 || $forscreen['action']==2){
                $redis = new \Common\Lib\SavorRedis();
                $redis->select(5);
                $cache_key = C('SAPP_FORSCREENTRACK');
                $res_cache = $redis->get($cache_key.$serial_no);
                if(empty($res_cache)){
                    $serial_no = forscreen_serial($forscreen['openid'],$forscreen['forscreen_id']);
                }
            }
            if($forscreen['action']==30){
                $serial_no = $forscreen['id'];
            }
        }elseif(in_array($forscreen['action'],$other_action)){
            if($forscreen['action']==8){
                if($forscreen['resource_type']==2){
                    $oss_addr = '';
                    if(!empty($forscreen['imgs'])){
                        $oss_info = json_decode($forscreen['imgs'],true);
                        $oss_addr = $oss_info[0];
                    }
                    $serial_no = forscreen_serial($forscreen['openid'],$forscreen['forscreen_id'],$oss_addr);
                }else{
                    $serial_no = forscreen_serial($forscreen['openid'],$forscreen['forscreen_id']);
                }
            }else{
                $serial_no = forscreen_serial($forscreen['openid'],$forscreen['forscreen_id']);
            }
        }else{
            $serial_no = '';
        }
        return $serial_no;
    }

    public function getForscreenTrack($forscreen_record_id){
	    $res_forscreentrack = $this->getInfo(array('forscreen_record_id'=>$forscreen_record_id));
	    if(empty($res_forscreentrack)){
            $m_heart_log = new \Admin\Model\HeartLogModel();
            $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
            $res_forscreen = $m_forscreen->getInfo(array('id'=>$forscreen_record_id));
            if($res_forscreen['action']==30){
                $serial_no = $res_forscreen['id'];
            }else{
                $serial_no = $this->getForscreenSerialNumber($res_forscreen);
            }
            $redis = new \Common\Lib\SavorRedis();
            $redis->select(5);
            $cache_key = C('SAPP_FORSCREENTRACK');
            $res_cache = $redis->get($cache_key.$serial_no);
            if(!empty($res_cache)){
                $data = json_decode($res_cache,true);
                unset($data['action']);
                if(isset($data['netty_position_result'])){
                    $data['netty_position_result'] = json_encode($data['netty_position_result']);
                }
                if(isset($data['netty_result'])){
                    $data['netty_result'] = json_encode($data['netty_result']);
                }
                $data['netty_position_url'] = urldecode($data['netty_position_url']);
                $data['netty_url'] = str_replace('\\', '',urldecode($data['netty_url']));
                $data['forscreen_record_id'] = $forscreen_record_id;
                $data['serial_number'] = $serial_no;
                if(isset($data['netty_callback_result'])){
                    $data['netty_callback_result'] = json_encode($data['netty_callback_result']);
                    $data['netty_callback_time'] = intval($data['netty_callback_time']);
                }
                $netty_key = $cache_key.$serial_no.'netty_time';
                if(empty($data['netty_receive_time'])){
                    $res_nettycache = $redis->hget($netty_key,'netty_receive_time');
                    if(!empty($res_nettycache)){
                        $data['netty_receive_time'] = $res_nettycache;
                    }
                }
                if(empty($data['netty_pushbox_time'])){
                    $res_nettycache = $redis->hget($netty_key,'netty_pushbox_time');
                    if(!empty($res_nettycache)){
                        $data['netty_pushbox_time'] = $res_nettycache;
                    }
                }
                if($res_forscreen['action']==4 || $res_forscreen['action']==11){
                    $res_heart = $m_heart_log->getInfo('*',array('box_id'=>$res_forscreen['box_id']),'');
                    if(!empty($res_heart) && $res_heart['type']==2 && $res_heart['apk_version']>'2.2.6'){
                        $data['oss_stime'] = $res_forscreen['res_sup_time'];
                        $data['oss_etime'] = $res_forscreen['res_eup_time'];

                        $req_id = $serial_no.'subdata:'.$res_forscreen['forscreen_id'].'-'.$res_forscreen['resource_id'];
                        $res_cache = $redis->get($cache_key.$req_id);
                        if(!empty($res_cache)){
                            $cache_data = json_decode($res_cache,true);
                            if(isset($cache_data['box_play_time'])){
                                $data['box_play_time'] = $cache_data['box_play_time'];
                            }
                            if(isset($cache_data['box_finish_downtime'])){
                                $data['box_finish_downtime'] = $cache_data['box_finish_downtime'];
                            }
                            if(isset($cache_data['box_downstime']) && isset($cache_data['box_downetime'])){
                                $data['box_downstime'] = $cache_data['box_downstime'];
                                $data['box_downetime'] = $cache_data['box_downetime'];
                            }
                        }
                    }

                }
                $data['oss_stime'] = intval($data['oss_stime']);
                $data['oss_etime'] = intval($data['oss_etime']);
                $result = $this->getTrackResult($res_forscreen,$data);
                $data['is_success'] = $result['is_success'];
                $data['total_time'] = $result['total_time'];
                $this->add($data);
                $res_forscreentrack = $data;
            }else{
                $res_forscreentrack = array();
            }
        }
        return $res_forscreentrack;
    }

    public function getTrackResult($forscreen_info,$track_info){
        if($forscreen_info['action']==30){
            $begin_time = $track_info['oss_stime'];
            if ($track_info['box_downstime'] == 0 && $track_info['box_downetime'] == 0) {
                $end_time = $track_info['oss_etime'];
            } else {
                $end_time = $track_info['box_downetime'];
            }
            if ($begin_time && $end_time) {
                $is_success = 1;
                $oss_time = $track_info['oss_etime']-$track_info['oss_stime'];
                $box_time = 0;
                if ($track_info['box_downstime'] && $track_info['box_downetime']){
                    $box_time = $track_info['box_downetime'] - $track_info['box_downstime'];
                }
                $total_time = ($oss_time+$box_time)/1000;
            } else {
                $is_success = 0;
                $total_time = '';
            }
        }else{
            if($forscreen_info['action']==5 && $forscreen_info['forscreen_char']=='Happy Birthday'){
                $forscreen_info['is_exist'] = 1;
            }

            if(in_array($forscreen_info['action'],array(9,32,46,47,48,49))){
                $forscreen_info['is_exist'] = 1;
            }
            if($forscreen_info['action']==4){
                $forscreen_info['is_exist'] = 0;
            }
            if($forscreen_info['is_exist']==1){
                $begin_time = $track_info['position_nettystime'];
                $end_time = $track_info['box_receivetime'];
            }else{
                if($track_info['oss_stime'] && $track_info['oss_etime']){
                    $begin_time = $track_info['oss_stime'];
                }else{
                    $begin_time = $track_info['position_nettystime'];
                }
                $end_time = $track_info['box_downetime'];
            }
//            if($forscreen_info['resource_type']==1 && !in_array($forscreen_info['action'],array(32,46,47,48,49))){
//                $m_hearlog = new \Admin\Model\HeartAllLogModel();
//                $date = date('Ymd');
//                $res = $m_hearlog->getOne($forscreen_info['box_mac'],2,$date);
//                if(!empty($res) && $res['apk_version']>='2.1.0'){
//                    if(empty($track_info['box_play_time'])){
//                        $begin_time = $end_time = '';
//                    }
//                }
//            }
            if($begin_time && $end_time){
                $is_success = 1;
//                $total_time = ($end_time-$begin_time)/1000;

                $oss_timeconsume = $track_info['oss_etime']-$track_info['oss_stime'];
                $netty_position_timeconsume = 0;
                if($track_info['request_nettytime']){
                    $netty_position_timeconsume = $track_info['request_nettytime']-$track_info['position_nettystime'];
                }
                $netty_timeconsume = 0;
                if($track_info['netty_receive_time'] && $track_info['netty_pushbox_time']){
                    if($track_info['netty_callback_time']){
                        $netty_timeconsume = $track_info['netty_callback_time']-$track_info['netty_receive_time'];
                    }else{
                        $netty_timeconsume = $track_info['netty_pushbox_time']-$track_info['netty_receive_time'];
                    }
                }
                if(isset($track_info['netty_callback_result'])){
                    $netty_callback_result = json_decode($track_info['netty_callback_result'],true);
                    if(in_array($netty_callback_result['code'],array(10706,10006))){
                        $is_success = 0;
                    }
                }
                $box_down_timeconsume = 0;
                if($track_info['box_downstime'] && $track_info['box_downetime']){
                    $box_down_timeconsume = $track_info['box_downetime']-$track_info['box_downstime'];
                }
                $total_time = ($oss_timeconsume+$netty_position_timeconsume+$netty_timeconsume+$box_down_timeconsume)/1000;
            }else{
                $is_success = 0;
                $total_time = '';
            }
        }

        if(isset($forscreen_info['is_break']) && $forscreen_info['is_break']==1){
            $is_success = 2;
        }
        if((isset($forscreen_info['is_exit']) && $forscreen_info['is_exit']==1)){
            $is_success = 3;
        }
        $result = array('is_success'=>$is_success,'total_time'=>$total_time);
        return $result;
    }

    public function getWhere($fields,$where,$limit,$group){
        $data = $this->alias('a')
            ->join('savor_smallapp_forscreen_record forscreen on a.forscreen_record_id=forscreen.id','left')
            ->join('savor_box box on forscreen.box_mac=box.mac','left')
            ->join('savor_room room on box.room_id=room.id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->field($fields)
            ->where($where)->limit($limit)->group($group)->select();
        return $data;
    }

}