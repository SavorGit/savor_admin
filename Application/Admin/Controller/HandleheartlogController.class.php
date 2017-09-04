<?php
namespace Admin\Controller;

use Think\Controller;
use \Common\Lib\SavorRedis;
// use Common\Lib\SavorRedis;
/**
 * @desc 功能测试类
 *
 */
class HandleheartlogController extends Controller
{
    /**
     * @desc 统计历史心跳上报数据
     */
    public function countHeartLog(){
        $redis = SavorRedis::getInstance();
        $redis->select(13);
        $keys = $redis->keys('heartlog_*');
        $m_heart_all_log = new \Admin\Model\HeartAllLogModel();
        $m_hotel = new \Admin\Model\HotelModel();
        $m_box   = new \Admin\Model\BoxModel();
        foreach($keys as $v){
            $data = array();
            $info = $redis->get($v);
            if(!empty($info)){
                $info = json_decode($info,true);
                if(empty($info['mac']) || empty($info['clientid']) || empty($info['date'])){
                    $redis->remove($v);
                    continue;
                }
                $date = substr($info['date'],0,8);
                $loginfo = $m_heart_all_log->getOne($info['mac'], $info['clientid'], $date);
                $hour = intval(substr($info['date'], 8,2));
    
                if(empty($loginfo)){
                    if($info['clientid'] ==1){
                        $hotelInfo = $m_hotel->getHotelInfoByMac($info['mac']);
                        if($hotelInfo){
                            $data['area_id']    = $hotelInfo['area_id'];
                            $data['area_name']  = $hotelInfo['area_name'];
                            $data['hotel_id']   = $info['hotelId'];
                            $data['hotel_name'] = $hotelInfo['hotel_name'];
                            $data['mac']        = $info['mac'];
                            $data['type']       = $info['clientid'];
                            $data['date']       = $date;
                            $data['hour'.$hour] = 1;
                            $ret = $m_heart_all_log->addInfo($data);
                        }
    
                    }else if($info['clientid'] ==2){
                        $hotelInfo =  $m_box->getHotelInfoByBoxMac($info['mac']);
                        if($hotelInfo){
                            $data['area_id']    = $hotelInfo['area_id'];
                            $data['area_name']  = $hotelInfo['area_name'];
                            $data['hotel_id']   = $info['hotelId'];
                            $data['hotel_name'] = $hotelInfo['hotel_name'];
                            $data['room_id']    = $hotelInfo['room_id'];
                            $data['room_name']  = $hotelInfo['room_name'];
                            $data['box_id']     = $hotelInfo['box_id'];
                            $data['mac']        = $info['mac'];
                            $data['type']       = $info['clientid'];
                            $data['date']       = $date;
                            $data['hour'.$hour] = 1;
                            $ret = $m_heart_all_log->addInfo($data);
                        }
                    }
                }else {
                    $where = array();
                    if($info['clientid'] ==1){
                        $where['mac'] = $info['mac'];
                        $where['type']= $info['clientid'];
                        $where['date']= $date;
                        $ret = $m_heart_all_log->updateInfo($where['mac'], $where['type'], $where['date'], $filed = "hour{$hour}");
                       
                    }else if($info['clientid'] ==2){
                        $where['mac'] = $info['mac'];
                        $where['type']= $info['clientid'];
                        $where['date']= $date;
                        $ret = $m_heart_all_log->updateInfo($where['mac'], $where['type'], $where['date'], $filed = "hour{$hour}");
                        
                    }
                }
                if($ret){
                    $redis->remove($v);
                }
            }
        }
        echo 'OK';
    }
    
}