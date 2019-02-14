<?php
namespace Admin\Controller;
use Think\Controller;
/**
 * @desc 通知
 *
 */
class NotifyController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function topicSubscribe(){
        $content = file_get_contents('php://input');
        $log_content = date("Y-m-d H:i:s").'[resp_result]'.$content.'[client_ip]'.get_client_ip()."\n";
        $log_file_name = APP_PATH.'Runtime/Logs/'.'hotel_subscribe_'.date("Ymd").".log";
        @file_put_contents($log_file_name, $log_content, FILE_APPEND);
        if(!empty($content)){
            $res = json_decode($content,true);
            if(!empty($res['Message'])){
                $message = base64_decode($res['Message']);
                $all_message = explode(',',$message);
                $hotel_list = array();
                foreach ($all_message as $v){
                    $hotel_list[] = array('hotel_id'=>$v);
                }
                $redis  =  \Common\Lib\SavorRedis::getInstance();
                $redis->select(12);
                $vm_hotel_key = C('VM_HOTEL_LIST');
                $redis->set($vm_hotel_key,json_encode($hotel_list));
            }
        }
    }


}