<?php
namespace Admin\Controller;
use Common\Lib\UmengApi;
use Think\Controller;

/**
 * @desc 定时任务
 *
 */
class CrontabController extends Controller
{
    public function report(){
        //酒楼总数
        $m_hotel = new \Admin\Model\HotelModel();
        $where = array();
        $where['state'] = 1;
        $where['hotel_box_type'] = array('in','2,3');
        $hotel_all_num = $m_hotel->getHotelCount($where);
    
        //正常酒楼 、异常酒楼
        $end_time = date('Y-m-d H:i:s',strtotime('-10 minutes'));
        $start_time = date('Y-m-d H:i:s',strtotime('-15 hours'));
        $m_heart_log = new \Admin\Model\HeartLogModel();
        $m_box = new \Admin\Model\BoxModel();
        $where = array();
    
        $where['state'] = 1;
        $where['flag'] = 0;
        $where['hotel_box_type'] = array('in','2,3');
        $hotel_list = $m_hotel->getHotelList($where,'','','id');
    
        $normal_hotel_num = 0;
        $not_normal_hotel_num = 0;
    
        $normal_small_plat_num = 0;
        $not_normal_small_plat_num = 0;
    
        $normal_box_num = 0;
        $not_normal_box_num = 0;
        $not_normal_hotel_arr = array();
    
        foreach($hotel_list as $key=>$v){
            $small_plat_status = 1;
            $crr_box_not_normal_num = 0;
            $box_last_report_time = $tmp_time = date('Y-m-d H:i:s');
            $box_heart_not_report = 0;
            
            $where = '';
            $where .=" 1 and hotel_id=".$v['id']." and type=1";
            $where .="  and last_heart_time>='".$start_time."'";
            $ret = $m_heart_log->getOnlineHotel($where,'hotel_id');
            if(!empty($ret)){//小平台有15小时内的心跳 判断机顶盒是否有心跳
    
                $flag = 0;
                //$normal_hotel_num +=1;
                $where = '';
                $where .=" 1 and room.hotel_id=".$v['id'].' and a.state !=2 and a.flag=0  and  room.flag=0 and room.state !=2';
                $box_list = $m_box->getListInfo( 'a.id, a.mac',$where);
                foreach($box_list as $ks=>$vs){
                    $where = '';
                    $where .=" 1 and hotel_id=".$v['id']." and type=2 and box_mac='".$vs['mac']."'";
                    $where .="  and last_heart_time>='".$start_time."'";
                     
                    $rets  = $m_heart_log->getOnlineHotel($where,'hotel_id');
                    if(empty($rets)){
                        $not_normal_box_num +=1;
                        $crr_box_not_normal_num +=1;
                        $flag = 1;
                        //$not_normal_hotel_num +=1;
                        //break;
                    }else {
                        $normal_box_num +=1;
                    }
                    $where = '';
                    $where .=" 1 and hotel_id=".$v['id']." and type=2 and box_mac='".$vs['mac']."'";
                    $rets  = $m_heart_log->getOnlineHotel($where,'last_heart_time');
                    $box_last_report_time = strtotime($box_last_report_time);
                    if(!empty($rets)){
                        $crr_box_report_time = strtotime($rets[0]['last_heart_time']);
                        if($crr_box_report_time<$box_last_report_time){
                            $box_last_report_time = $crr_box_report_time;
                        }
                    }else {
                        $box_heart_not_report = 1;
                    }
                    $box_last_report_time = date('Y-m-d H:i:s',$box_last_report_time);
                }
    
                if($flag ==1){
                    $not_normal_hotel_arr[] = $v['id'];
                    $not_normal_hotel_num +=1;
                }
            }else {//小平台没有15小时内的心跳 判断机顶盒是否有心跳
                $small_plat_status = 0;
                $flag = 0;
                $where = '';
                $where .=" 1 and room.hotel_id=".$v['id'].' and a.state !=2 and a.flag=0  and  room.flag=0 and room.state !=2';
                $box_list = $m_box->getListInfo( 'a.id, a.mac',$where);
                foreach($box_list as $ks=>$vs){
                    $where = '';
                    $where .=" 1 and hotel_id=".$v['id']." and type=2 and box_mac='".$vs['mac']."'";
                    $where .="  and last_heart_time>='".$start_time."'";
                     
                    $rets  = $m_heart_log->getOnlineHotel($where,'hotel_id');
                    if(empty($rets)){
                        $not_normal_box_num +=1;
                        $crr_box_not_normal_num +=1;
    
                    }else {
                        $normal_box_num +=1;
                    }
                    $where = '';
                    $where .=" 1 and hotel_id=".$v['id']." and type=2 and box_mac='".$vs['mac']."'";
                    $rets  = $m_heart_log->getOnlineHotel($where,'last_heart_time');
                    $box_last_report_time = strtotime($box_last_report_time);
                    if(!empty($rets)){
                        $crr_box_report_time = strtotime($rets[0]['last_heart_time']);
                        if($crr_box_report_time<$box_last_report_time){
                            $box_last_report_time = $crr_box_report_time;
                        }
                    }else {
                        $box_heart_not_report = 1;
                    }
                    $box_last_report_time = date('Y-m-d H:i:s',$box_last_report_time);
                }
    
                $not_normal_small_plat_num +=1;
                $not_normal_hotel_num +=1;
                $not_normal_hotel_arr[] = $v['id'];
            }
            $rets = $m_hotel->getStatisticalNumByHotelId($v['id'],'tv');
            $result[$key]['hotel_id'] = $v['id'];
            $result[$key]['tv_num'] = $rets['tv_num'];
            $result[$key]['small_plat_status'] = $small_plat_status;
            $where = array();
            $where['hotel_id'] = $v['id'];
            $where['type']  =1;
    
            $dt = $m_heart_log->getInfo('last_heart_time',$where);
            if(!empty($dt)){
                $result[$key]['small_plat_report_time'] = $dt['last_heart_time'];
            }else {
                $result[$key]['small_plat_report_time'] = '';
            }
            $result[$key]['not_normal_box_num'] = $crr_box_not_normal_num;
            if($box_last_report_time == $tmp_time || $box_heart_not_report==1){
                $box_last_report_time = '';
            }
            $result[$key]['box_report_time'] = $box_last_report_time;
            $result[$key]['create_time'] = date('Y-m-d H:i:s');
        }
    
        $data['hotel_all_num']            = $hotel_all_num;               //酒楼总数
        $data['not_normal_hotel_num']     = $not_normal_hotel_num;        //异常酒楼
        $data['not_normal_smallplat_num'] = $not_normal_small_plat_num;   //异常小平台
        $data['not_normal_box_num']       = $not_normal_box_num;          //异常机顶盒
        $m_hotel_error_report = new \Admin\Model\HotelErrorReportModel();
        $id = $m_hotel_error_report->addInfo($data);
        if($id){
            $ticker = '截止到'.date('m-d H点').','.$data['not_normal_hotel_num'].'家酒楼异常,其中'
                      .$data['not_normal_smallplat_num'].'个小平台失联超过15小时,'
                      .$data['not_normal_box_num'].'个机顶盒失联超过15小时';
            $title = '小热点异常报告';
            $desc  = '小热点异常报告';
            $m_hotel_error_report_detail = new \Admin\Model\HotelErrorReportDetailModel();
    
            foreach($result as $key=> $v){
                $result[$key]['error_id'] = $id;
    
            }
            $m_hotel_error_report_detail->addInfo($result,2);
            $umengApi = new UmengApi(); 
            $android_params = array();
            $ios_params = array();
            $android_params['type'] = 'broadcast';
            $android_params['display_type'] = 'notification';
            $android_params['ticker']  = $ticker;
            $android_params['title']   = $title;
            $android_params['text']    = $desc;
            $android_params['after_open'] = 'go_custom';
            $android_params['production_mode'] = "true";
            $ext_arr = array();
            $ext_arr = array('type'=>1,'params'=>json_encode(array('error_id'=>"{$id}")));
            
            $ret = $umengApi->umeng_api_android($android_params,$ext_arr);
            
            $ios_params['type'] = 'broadcast';
            $ios_params['display_type'] = 'notification';
            $ios_params['ticker']  = $ticker;
            $ios_params['title']   = $title;
            $ios_params['text']    = $desc; 
            $ios_params['after_open'] = 'go_custom';
            $ios_params['alert'] = $ticker;
            $ios_params['sound'] = 'default';
            $ios_params['device_tokens'] = '';
            $ios_params['production_mode'] = "true";
            $ext_arr = array();
            $ext_arr = array('type'=>1,'params'=>json_encode(array('error_id'=>"{$id}")));
            
            $res = $umengApi->umeng_api_ios($ios_params,$ext_arr);
            if($ret && $res){//安卓和ios都推送成功
                $m_hotel_error_report->where('id='.$id)->save(array('is_push'=>1));
            }else if($ret && !$res){ //安卓推送成功  ios推送失败
                $m_hotel_error_report->where('id='.$id)->save(array('is_push'=>2));
            }else if(!$ret && $res){//安卓推送失败  ios推送成功
                $m_hotel_error_report->where('id='.$id)->save(array('is_push'=>3));
            }
            echo 'OK';
        }else {
            echo 'NOT OK';
        }
    }
   /*  private function  umeng_api_android($info){
        $data = array();
        $ument_config = C('UMENT_API_CONFIG');
        $data['appkey'] = $ument_config['AppKey'];
        $data['timestamp'] = time();
        $data['type'] = 'broadcast';
        $data['payload']['display_type'] = 'notification';
        $data['payload']['body']['ticker'] = '提示文字';
        $data['payload']['body']['title']  = '通知标题';
        $data['payload']['body']['text']   = '通知文字描述';
        $data['payload']['body']['after_open'] = 'go_app';
        $curl = new Curl();
        $url = $ument_config['API_URL'];
        
        $curl->post($url, $data, $result);
        $result = json_decode($result);
    }
    private function  ument_api_ios($info){
        $data = array();
        $umeng_config = C('UMENT_API_CONFIG');
        $data['appkey'] = $umeng_config['AppKey'];
        $data['timestamp'] = time();
        $data['type'] = 'broadcast';
        $data['payload']['aps']['alert'] = '通知标题';
        $data['payload']['error_id'] = '1';
        $curl = new Curl();
        $url = $umeng_config['API_URL'];
        
        $curl->post($url, $data, $result);
        $result = json_decode($result);
        if($result['ret']=='SUCCESS'){
            return true;
        }else {
            return false;
        }
    }
    private function genMySin($ticker,$title,$text){
        $umeng_config = C('UMENT_API_CONFIG');
        $appkey = $umeng_config['AppKey'];
        $app_master_secret = $umeng_config['App_Master_Secret'];
        $timestamp  = time();
        $method = 'POST';
        $url = $umeng_config['API_URL'];
        $params = array();
        $params['appkey'] = $appkey;
        $params['timestamp'] = time();
        $params['device_tokens'] = '';
        $params['type'] = 'broadcast';
        $params['payload']['body']['ticker'] = $ticker;
        $params['payload']['body']['title'] = $title;
        $params['payload']['body']['text'] = $text;
        $params['payload']['body']['after_open'] = 'go_app';
        $params['payload']['display_type'] = 'notification';
        $post_body = json_encode($params);
        $sign = md5($method,$url,$post_body,$app_master_secret);
        return $sign;
    } 
    public function test(){
        $umengApi = new UmengApi();
        $android_params = array();
        $ios_params = array();
        $android_params['type'] = 'broadcast';
        $android_params['display_type'] = 'notification';
        $android_params['ticker']  = '提示文字';
        $android_params['title']   = '通知标题';
        $android_params['text']    = '通知文字描述';
        $android_params['after_open'] = 'go_custom';
        $android_params['production_mod'] = "false";
        $ext_arr = array();
        $ext_arr = array('type'=>1,'params'=>json_encode(array('error_id'=>"1")));
        $ret = $umengApi->umeng_api_android($android_params,$ext_arr);
        print_r($ret);
        /* $ios_params['type']   = 'broadcast';
        $ios_params['display_type'] = 'notification';
        $ios_params['alert']  = 'ios提示信息';
        $ios_params['ticker'] = '提示文字';
        $ios_params['title']  = '通知标题';
        $ios_params['text']   = '通知文字描述';
        $ios_params['after_open'] = 'go_app';
        
        $ext_arr = array();
        $ext_arr['error_id'] = 1;
        
        $res = $umengApi->umeng_api_ios($ios_params,$ext_arr);
         
    }
    public function testIos(){
        $umengApi = new UmengApi();
        $android_params = array();
        $ios_params = array();
        $android_params['type'] = 'unicast';
        $android_params['display_type'] = 'notification';
        $android_params['ticker']  = '提示文字';
        $android_params['title']   = '通知标题';
        $android_params['text']    = '通知文字描述';
        $android_params['after_open'] = 'go_custom';
        $android_params['alert'] = '提示文字';
        $android_params['device_tokens'] = 'eaed015fd5691b40cf91b7dfca924b4bc80c821949ae6cc2bc25a0e44de7a3cc';
        $android_params['production_mode'] = "false";
        $ext_arr = array();
        $ext_arr = array('type'=>1,'params'=>json_encode(array('error_id'=>"1")));
        $ret = $umengApi->umeng_api_ios($android_params,$ext_arr);
        print_r($ret);
        /* $ios_params['type']   = 'broadcast';
         $ios_params['display_type'] = 'notification';
         $ios_params['alert']  = 'ios提示信息';
         $ios_params['ticker'] = '提示文字';
         $ios_params['title']  = '通知标题';
         $ios_params['text']   = '通知文字描述';
         $ios_params['after_open'] = 'go_app';
    
         $ext_arr = array();
         $ext_arr['error_id'] = 1;
    
         $res = $umengApi->umeng_api_ios($ios_params,$ext_arr);
       
    }*/
    /**
     * @desc 随机生成广告的位置
     */
    public function recordAdsLocation(){
        
        $base_location_arr = array(1,2,3,4,5,6,7,8,9,10);
        //获取未执行插入位置的广告
        $m_pub_ads = new \Admin\Model\PubAdsModel(); 
        $m_pub_ads_box = new \Admin\Model\PubAdsBoxModel();
        $pub_ads_list = $m_pub_ads->getEmptyLocationList();
        
        foreach($pub_ads_list as $key=>$val){//循环每一个发布但未执行添加位置脚本的广告
            $pub_ads_box_arr = $m_pub_ads_box->getBoxArrByPubAdsId($val['id']);
            
            foreach($pub_ads_box_arr as $k=>$v){//循环该发布的广告对应的机顶盒
                $all_have_location_arr = array();
                //取出该机顶盒所有未填写位置的列表
                $all_empty_location_info = $m_pub_ads_box->getEmptyLocation($val['id'],$v['box_id']);
                if(!empty($all_empty_location_info)){
                    //取出该机顶盒在该广告起止时间内所有的位置
                    $all_have_location_info = $m_pub_ads_box->getLocationList($v['box_id'],$val['start_date'],$val['end_date']);
                    
                    foreach($all_have_location_info as $hl){
                        $all_have_location_arr[] = $hl['location_id'];
                    }
                    $diff_location_arr = array_diff($base_location_arr, $all_have_location_arr);
                    if(!empty($diff_location_arr)){
                        $count = count($all_empty_location_info);
                        $now_location_arr = array_rand($diff_location_arr,$count);
                        
                        foreach($all_empty_location_info as $ek=>$ev){
                            $where['id'] = $ev['id'];
                            $data['location_id'] = $diff_location_arr[$now_location_arr[$ek]];
                            $data['update_time'] = date('Y-m-d H:i:s');
                            $m_pub_ads_box->updateInfo($where,$data);
                        } 
                    }  
                }
            }
            $m_pub_ads->updateInfo(array('id'=>$val['id']),array('state'=>1,'update_time'=>date('Y-m-d H:i:s')));
        }
        echo "OK";
    }
}