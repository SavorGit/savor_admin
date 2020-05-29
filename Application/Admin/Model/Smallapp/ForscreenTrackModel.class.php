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
        $where = array();
        $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $where['small_app_id'] = array('in',array(1,2,3));
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
                        if(isset($data['netty_position_result'])){
                            $data['netty_position_result'] = json_encode($data['netty_position_result']);
                        }
                        if(isset($data['netty_result'])){
                            $data['netty_result'] = json_encode($data['netty_result']);
                        }
                        $data['netty_position_url'] = urldecode($data['netty_position_url']);
                        $data['netty_url'] = str_replace('\\', '',urldecode($data['netty_url']));
                        $data['oss_stime'] = intval($data['oss_stime']);
                        $data['oss_etime'] = intval($data['oss_etime']);
                        $data['forscreen_record_id'] = $v['id'];
                        $data['serial_number'] = $serial_no;

                        $result = $this->getTrackResult($v,$data);
                        $data['is_success'] = $result['is_success'];
                        $data['total_time'] = $result['total_time'];

                        $res_track = $this->field('id')->where(array('forscreen_record_id'=>$v['id']))->order('id desc')->find();
                        if(!empty($res_track)){
                            $id = $res_track['id'];
                            $this->where(array('id'=>$id))->save($data);
                        }else{
                            $this->add($data);
                        }
                    }
                }
            }
        }
    }

    public function getForscreenSerialNumber($forscreen){
	    $is_new_action = 0;
	    if($forscreen['create_time']>='2020-05-29 09:31:00'){
            $hotel_ids = array(7,883);
	        $m_box = new \Admin\Model\BoxModel();
	        $res_box = $m_box->getHotelInfoByBoxMac($forscreen['box_mac']);
	        if(!empty($res_box) && in_array($res_box['hotel_id'],$hotel_ids)){
                $is_new_action = 1;
            }
        }
        if($is_new_action){
            $has_img_action = array(2,5,12,21,22,30,31);
            $other_action = array(4,8,9,11);
        }else{
            $has_img_action = array(2,4,5,12,21,22,30,31);
            $other_action = array(8,9,11);
        }

        if(in_array($forscreen['action'],$has_img_action)){
            $oss_addr = '';
            if(!empty($forscreen['imgs'])){
                $oss_info = json_decode($forscreen['imgs'],true);
                $oss_addr = $oss_info[0];
            }
            if($forscreen['action']==31){
                if(!empty($forscreen['resource_id'])){
                    $forscreen['forscreen_id'] = $forscreen['resource_id'];
                }
            }

            $serial_no = forscreen_serial($forscreen['openid'],$forscreen['forscreen_id'],$oss_addr);
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
                if(isset($data['netty_position_result'])){
                    $data['netty_position_result'] = json_encode($data['netty_position_result']);
                }
                if(isset($data['netty_result'])){
                    $data['netty_result'] = json_encode($data['netty_result']);
                }
                $data['netty_position_url'] = urldecode($data['netty_position_url']);
                $data['netty_url'] = str_replace('\\', '',urldecode($data['netty_url']));
                $data['oss_stime'] = intval($data['oss_stime']);
                $data['oss_etime'] = intval($data['oss_etime']);
                $data['forscreen_record_id'] = $forscreen_record_id;
                $data['serial_number'] = $serial_no;

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
//                $total_time = ($end_time - $begin_time) / 1000;

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
            if($forscreen_info['action']==9){
                $forscreen_info['is_exist'] = 1;
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
                    $netty_timeconsume = $track_info['netty_pushbox_time']-$track_info['netty_receive_time'];
                }
                $box_down_timeconsume = 0;
                if($track_info['box_receivetime'] && $track_info['box_downstime'] && $track_info['box_downetime']){
                    $box_down_timeconsume = $track_info['box_downetime']-$track_info['box_downstime'];
                }
                $total_time = ($oss_timeconsume+$netty_position_timeconsume+$netty_timeconsume+$box_down_timeconsume)/1000;
            }else{
                $is_success = 0;
                $total_time = '';
            }
        }
        $result = array('is_success'=>$is_success,'total_time'=>$total_time);
        return $result;
    }

}