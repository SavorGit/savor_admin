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
        $has_img_action = array(2,4,5,8,12,21,22,30,31);
        $other_action = array(9,11);
        if(in_array($forscreen['action'],$has_img_action)){
            $oss_addr = '';
            if(!empty($forscreen['imgs'])){
                $oss_info = json_decode($forscreen['imgs'],true);
                $oss_addr = $oss_info[0];
            }
            $serial_no = forscreen_serial($forscreen['openid'],$forscreen['forscreen_id'],$oss_addr);
        }elseif(in_array($forscreen['action'],$other_action)){
            $serial_no = forscreen_serial($forscreen['openid'],$forscreen['forscreen_id']);
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
                $this->add($data);
                $res_forscreentrack = $data;
            }else{
                $res_forscreentrack = array();
            }
        }
        return $res_forscreentrack;
    }

}