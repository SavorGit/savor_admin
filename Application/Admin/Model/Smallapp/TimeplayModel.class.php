<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class TimeplayModel extends BaseModel{
	protected $tableName='smallapp_timeplay';

	public function handle_timeplay(){
	    $now_time = date('Y-m-d H:i:00');
        $where = array('status'=>1,'timing'=>$now_time);
        $res_timeplay = $this->getDataList('*',$where,'id asc');
        if(empty($res_timeplay)){
            $now_time = date('Y-m-d H:i:s');
            echo "$now_time timeplay empty \r\n";
            exit;
        }
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $m_ads = new \Admin\Model\AdsModel();
        $m_media = new \Admin\Model\MediaModel();
        $m_usertask_record = new \Admin\Model\Smallapp\UsertaskrecordModel();
        $ads_info = array();
        $user_info = array();
        $screen_cache_key = C('SAPP_SCRREN');
        $redis = new \Common\Lib\SavorRedis();
        foreach ($res_timeplay as $v){
            $box_mac = $v['box_mac'];
            if(isset($ads_info[$v['ads_id']])){
                $url = $ads_info[$v['ads_id']]['url'];
                $filename = $ads_info[$v['ads_id']]['filename'];
                $resource_type = $ads_info[$v['ads_id']]['resource_type'];
                $duration = $ads_info[$v['ads_id']]['duration'];
                $resource_size = $ads_info[$v['ads_id']]['resource_size'];
            }else{
                $res_ads = $m_ads->getInfo(array('id'=>$v['ads_id']));
                $media_info = $m_media->getMediaInfoById($res_ads[0]['media_id']);
                $url = $media_info['oss_path'];
                $oss_path_info = pathinfo($url);
                $filename = $oss_path_info['basename'];
                $resource_type = 2;
                $duration = $media_info['duration'];
                $resource_size = $media_info['oss_filesize'];
                $ads_info[$v['ads_id']]=array('url'=>$url,'filename'=>$filename,'resource_type'=>$resource_type,
                    'duration'=>$duration,'resource_size'=>$resource_size);
            }
            if(isset($user_info[$v['openid']])){
                $avatarUrl = $user_info[$v['openid']]['avatarurl'];
                $nickName = $user_info[$v['openid']]['nickname'];
            }else{
                $res_user = $m_user->getOne('avatarUrl,nickName',array('openid'=>$v['openid']),'');
                $user_info[$v['openid']] = $res_user;
                $avatarUrl = $res_user['avatarurl'];
                $nickName = $res_user['nickname'];
            }
            $nowtime = getMillisecond();
            $message = array('action'=>5,'url'=>$url,'filename'=>$filename,'openid'=>$v['openid'],'resource_type'=>$resource_type,
                'avatarUrl'=>$avatarUrl,'nickName'=>$nickName,'forscreen_id'=>$nowtime,
                'resource_size'=>$resource_size);
            $res_netty = $m_netty->pushBox($box_mac,json_encode($message));
            if(isset($res_netty['error_code'])){
                $netty_code = $res_netty['netty_data']['code'];
                $netty_result = $res_netty['netty_data'];
                $updata = array('status'=>2,'play_time'=>date('Y-m-d H:i:s'),'netty_code'=>$netty_code,'netty_result'=>$netty_result);
                $this->updateData(array('id'=>$v['id']),$updata);
                echo "id:{$v['id']} nettycode:$netty_code \r\n";
                continue;
            }else{
                $netty_code = 10000;
                $netty_result = $res_netty;
                $updata = array('status'=>2,'play_time'=>date('Y-m-d H:i:s'),'netty_code'=>$netty_code,'netty_result'=>$netty_result);
                $this->updateData(array('id'=>$v['id']),$updata);
                echo "id:{$v['id']} nettycode:$netty_code \r\n";
            }
            $imgs = array($url);
            $data = array('action'=>59,'box_mac'=>$box_mac,'duration'=>$duration,'forscreen_char'=>'','forscreen_id'=>$nowtime,
                'imgs'=>json_encode($imgs),'mobile_brand'=>$v['mobile_brand'],'mobile_model'=>$v['mobile_model'],
                'openid'=>$v['openid'],'resource_id'=>$nowtime,'resource_size'=>$resource_size,'create_time'=>date('Y-m-d H:i:s'),
                'small_app_id'=>5);
            $redis->select(5);
            $cache_key = $screen_cache_key.":".$box_mac;
            $redis->rpush($cache_key, json_encode($data));

            $usertask_record = array('openid'=>$v['openid'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                'room_id'=>$v['room_id'],'room_name'=>$v['room_name'],'box_id'=>$v['box_id'],'box_name'=>$v['box_name'],
                'box_mac'=>$box_mac,'usertask_id'=>$v['usertask_id'],'task_id'=>$v['task_id'],'task_type'=>25,
                'timeplay_id'=>$v['id'],'type'=>1
            );
            $record_id = $m_usertask_record->add($usertask_record);
            echo "id:{$v['id']} intaskrecord:$record_id \r\n";
        }
    }
}