<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class CollectforscreenModel extends BaseModel{
	protected $tableName='smallapp_collect_forscreen';

    public function getList($fields="a.id",$where, $order='a.id desc', $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_box box on a.box_mac=box.mac','left')
            ->join('savor_room room on room.id= box.room_id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->join('savor_area_info area on hotel.area_id=area.id','left')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('a')
            ->join('savor_box box on a.box_mac=box.mac','left')
            ->join('savor_room room on room.id= box.room_id','left')
            ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
            ->join('savor_area_info area on hotel.area_id=area.id','left')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->where($where)->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

    public function collectforscreen($is_refresh=0){
        $forscreen_openids = C('COLLECT_FORSCREEN_OPENIDS');
        $openids = array_keys($forscreen_openids);
        if($is_refresh){
            $start_time = date("Y-m-d 00:00:00");
            $end_time = date("Y-m-d H:i:s");
        }else{
            $hourtime = date("Y-m-d H", strtotime("-1 hour"));
            $start_time = "$hourtime:00:00";
            $end_time = "$hourtime:59:59";
        }

        $where = array('openid'=>array('in',$openids));
        $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $m_smallapp_forscreen = new \Admin\Model\SmallappForscreenRecordModel();
        $res_userdata = $m_smallapp_forscreen->field('*')->where($where)->select();
        foreach ($res_userdata as $v){
            $v['forscreen_record_id'] = $v['id'];
            unset($v['id'],$v['update_time'],$v['category_id'],$v['spotstatus'],$v['scene_id'],$v['contentsoft_id'],$v['dinnernature_id'],
                $v['personattr_id'],$v['remark'],$v['quality_type']);
            $this->addData($v);
        }
        $delcondition = array('openid'=>array('in',$openids));
        $m_smallapp_forscreen->where($delcondition)->delete();

        $where = array('openid'=>array('in',$openids));
        $where['create_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $result = $this->getDataList('*',$where,'id desc');
        if(!empty($result)){
            $redis = new \Common\Lib\SavorRedis();
            $redis->select(5);
            $cache_key = C('SAPP_FORSCREENTRACK');
            $m_forscreen_track = new \Admin\Model\Smallapp\ForscreenTrackModel();
            foreach ($result as $v){
                $collcet_id = $v['id'];
                $serial_no = $m_forscreen_track->getForscreenSerialNumber($v);
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
                        $data['oss_stime'] = intval($data['oss_stime']);
                        $data['oss_etime'] = intval($data['oss_etime']);
                        $data['forscreen_record_id'] = $v['id'];
                        $data['serial_number'] = $serial_no;
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
                        $res_track = $m_forscreen_track->getTrackResult($v,$data);
                        $udata = array('success_status'=>$res_track['is_success'],'total_time'=>$res_track['total_time']);
                        $this->updateData(array('id'=>$collcet_id),$udata);
                    }
                }
            }
        }
        return true;
    }
}