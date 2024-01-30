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

    public function upcompanystock(){
        $content = file_get_contents('php://input');
        $orders = array();
        if(!empty($content)) {
            $res = json_decode($content, true);
            if (!empty($res['Message'])) {
                $message = base64_decode($res['Message']);
                $orders = json_decode($message,true);
            }
        }
        $log_content = date("Y-m-d H:i:s").'[resp_result]'.$content.'[params]'.json_encode($orders).'[client_ip]'.get_client_ip()."\n";
        $log_file_name = APP_PATH.'Runtime/Logs/'.'companystock_'.date("Ymd").".log";
        @file_put_contents($log_file_name, $log_content, FILE_APPEND);
        if(empty($orders[0]['order_id'])){
            return true;
        }
        $area_id = intval($orders[0]['order_id']);//1 北京,9 上海,236 广州,248 佛山,246 深圳
        switch ($area_id){
            case 1:
                $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php crontab/companystock/stockbj > /tmp/null &";
                system($shell);
                break;
            case 9:
                $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php crontab/companystock/stocksh > /tmp/null &";
                system($shell);
                break;
            case 236:
                $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php crontab/companystock/stockgz > /tmp/null &";
                system($shell);
                break;
            case 248:
                $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php crontab/companystock/stockfs > /tmp/null &";
                system($shell);
                break;
        }
        echo 'success';
    }

    public function auditwriteoff(){
        return true;
        $content = file_get_contents('php://input');
        $orders = array();
        if(!empty($content)) {
            $res = json_decode($content, true);
            if (!empty($res['Message'])) {
                $message = base64_decode($res['Message']);
                $orders = json_decode($message,true);
            }
        }
        $log_content = date("Y-m-d H:i:s").'[resp_result]'.$content.'[params]'.json_encode($orders).'[client_ip]'.get_client_ip()."\n";
        $log_file_name = APP_PATH.'Runtime/Logs/'.'writeoffdata_'.date("Ymd").".log";
        @file_put_contents($log_file_name, $log_content, FILE_APPEND);
        if(empty($orders[0]['order_id'])){
            return true;
        }
        $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php crontab/audit/writeoffdata > /tmp/null &";
        system($shell);

        echo 'success';
    }

}