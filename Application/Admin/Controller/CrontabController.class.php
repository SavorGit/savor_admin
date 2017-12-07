<?php
namespace Admin\Controller;
use Common\Lib\UmengApi;
use Think\Controller;
use Common\Lib\SimFile;

/**
 * @desc 定时任务
 *
 */
class CrontabController extends Controller
{
    public $copy_j = array();


    public function report(){
        //酒楼总数
        $m_hotel = new \Admin\Model\HotelModel();
        $where = '';
        /* $where['state'] = 1;
        $where['hotel_box_type'] = array('in','2,3'); */
        $where = " a.id not in(7,53)  and a.state=1 and a.flag =0 and a.hotel_box_type in(2,3) and b.mac_addr !='' and b.mac_addr !='000000000000'";
        $hotel_all_num = $m_hotel->getHotelCountNums($where);
    
        //正常酒楼 、异常酒楼
        $end_time = date('Y-m-d H:i:s',strtotime('-10 minutes'));
        $start_time = date('Y-m-d H:i:s',strtotime('-72 hours'));
        $m_heart_log = new \Admin\Model\HeartLogModel();
        $m_box = new \Admin\Model\BoxModel();
        $where = '';
    
        /* $where['state'] = 1;
        $where['flag'] = 0;
        $where['hotel_box_type'] = array('in','2,3'); */
        $where = " a.id not in(7,53)  and a.state=1 and a.flag =0 and a.hotel_box_type in(2,3) and b.mac_addr !='' and b.mac_addr !='000000000000'";
        $hotel_list = $m_hotel->getHotelLists($where,'','','a.id');
    
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
                //$where .=" 1 and room.hotel_id=".$v['id'].' and a.state !=2 and a.flag=0  and  room.flag=0 and room.state !=2';
                $where .=" 1 and room.hotel_id=".$v['id'].' and a.state=1 and a.flag=0  and  room.flag=0 and room.state =1';
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
                //$where .=" 1 and room.hotel_id=".$v['id'].' and a.state !=2 and a.flag=0  and  room.flag=0 and room.state !=2';
                $where .=" 1 and room.hotel_id=".$v['id'].' and a.state =1 and a.flag=0  and  room.flag=0 and room.state =1';
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
        /* $m_hote_ext = new \Admin\Model\HotelExtModel();
        $map = array();
        $map['mac_addr'] = '000000000000';
        
        
        $counts = $m_hote_ext->where($map)->count(); */
        $counts = 0;
        
        //机顶盒黑名单
        $m_black_list = new \Admin\Model\BlackListModel();
        $black_box_num = $m_black_list->countBlackBoxNum();
        
        $data['hotel_all_num']            = $hotel_all_num;               //酒楼总数
        $data['not_normal_hotel_num']     = $not_normal_hotel_num;        //异常酒楼
        $data['not_normal_smallplat_num'] = $not_normal_small_plat_num -$counts;   //异常小平台
        $data['not_normal_box_num']       = $not_normal_box_num -$black_box_num;          //异常机顶盒
        $m_hotel_error_report = new \Admin\Model\HotelErrorReportModel();
        $id = $m_hotel_error_report->addInfo($data);
        if($id){
            $ticker = '截止到'.date('m-d H点').','.$data['not_normal_hotel_num'].'家酒楼异常,其中'
                      .$data['not_normal_smallplat_num'].'个小平台失联超过72小时,'
                      .$data['not_normal_box_num'].'个机顶盒失联超过72小时';
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
        $adv_promote_num_arr = C('ADVE_OCCU');
        $adv_promote_num = $adv_promote_num_arr['num'];
        $base_location_arr = range(1, $adv_promote_num);
        //获取未执行插入位置的广告
        $m_pub_ads = new \Admin\Model\PubAdsModel(); 
        $m_pub_ads_box = new \Admin\Model\PubAdsBoxModel();
        $pub_ads_list = $m_pub_ads->getEmptyLocationList();
        
        foreach($pub_ads_list as $key=>$val){//循环每一个发布但未执行添加位置脚本的广告
            
            $pub_ads_box_arr = $m_pub_ads_box->getBoxArrByPubAdsId($val['id']);   //获取当前广告发布到盒子
            
            foreach($pub_ads_box_arr as $k=>$v){//循环该发布的广告对应的机顶盒
                
                $all_have_location_arr = array();
                //取出该机顶盒所有未填写位置的列表
                $all_empty_location_info = $m_pub_ads_box->getEmptyLocation('id',$val['id'],$v['box_id']);
                
                if(!empty($all_empty_location_info)){
                    //取出该机顶盒在该广告起止时间内所有的位置
                    $all_have_location_info = $m_pub_ads_box->getLocationList($v['box_id'],$val['start_date'],$val['end_date']);
                    
                    foreach($all_have_location_info as $hl){
                        $all_have_location_arr[] = $hl['location_id'];
                    }
                    
                    $diff_location_arr = array_diff($base_location_arr, $all_have_location_arr);
                    //如果还有未分配的位置
                    //print_r($diff_location_arr);exit;
                    if(!empty($diff_location_arr)){
                        //把未分配得位置负值给location_id =0 的记录
                        $count = count($all_empty_location_info);
                        //$count = 1;
                        if($count==1){
                            $rand_key = array_rand($diff_location_arr,$count);
                           
                            $now_location_arr = array($rand_key);
                        }else {
                            $now_location_arr = array_rand($diff_location_arr,$count);
                        }
                        //print_r($diff_location_arr);exit;
                        //print_r($now_location_arr);exit;
                        //print_r($all_empty_location_info);exit;
                        foreach($all_empty_location_info as $ek=>$ev){
                            $where['id'] = $ev['id'];
                            $data['location_id'] = $diff_location_arr[$now_location_arr[$ek]];
                            $data['update_time'] = date('Y-m-d H:i:s');
                            if(!empty($data['location_id'])){
                                $m_pub_ads_box->updateInfo($where,$data);
                            }
                            
                        } 
                    }  
                }
            }
            $m_pub_ads->updateInfo(array('id'=>$val['id']),array('state'=>1,'update_time'=>date('Y-m-d H:i:s')));
        
        }
        echo "OK";
    }


    public function getAllBox($hotel_id) {
        $where = ' ( 1=1 and sht.id='.$hotel_id.' and
        sht.flag=0
        and sht.hotel_box_type in (2,3) and room.flag=0 and box.flag=0)';
        $hotelModel = new \Admin\Model\HotelModel();
        $field = ' box.id bid,box.name bname,box.state bstate,room.id
        rid,room.name rname,room.state rstate,sht.id hid,sht.name
        hname,sht.state hstate ';
        $order = '';
        $box_arr = $hotelModel->getBoxOrderMacByHid($field, $where, $order);
        
        // $rs = $hotelModel->getLastSql();
        // file_put_contents(LOG_PATH.'baiyutao.log',$rs.PHP_EOL,  FILE_APPEND);
        $box_arr = assoc_unique($box_arr,'bid');
        return $box_arr;
    }


    /**
     * @desc 酒店所有机顶盒数据并插入pub_ads_box
     */
    public function recordAllboxByhotel(){
        //获取所有未执行广告id
        $now_date = date("Y-m-d H:i:s");
        $pub_adsModel = new \Admin\Model\PubAdsModel();
        $pub_adsboxModel = new \Admin\Model\PubAdsBoxModel();
        $pub_ads_hotel = new \Admin\Model\PubAdsHotelModel();
        $pub_ads_box_error = new \Admin\Model\PubAdsBoxErrorModel();
        $field = 'id, start_date, end_date, play_times';
        $where['state'] = 3;
        $pub_ads_list = $pub_adsModel->getWhere($where, $field);
        foreach($pub_ads_list as $pa=>$pb) {
            $dat = array (
                'start_time'=>$pb['start_date'],
                'end_time'=>$pb['end_date'],
                'play_times'=>$pb['play_times'],
            );
           $pub_hotel_list = $pub_ads_hotel->getAdsHotelId($pb['id']);
         
            if ( !empty($pub_hotel_list) ) {
                foreach($pub_hotel_list as $pc=>$pd) {
                    //获取当前酒店所有机顶盒
                    $box_arr = $this->getAllBox($pd['hotel_id']);
                    //var_dump($box_arr);
                   // var_dump($box_arr);
                    if (!empty($box_arr)) {
                        //筛选出可以用的机顶盒
                        foreach ($box_arr as $bk=> $bv) {
                            $tmpbox = array();
                            $tmpbox = array(
                                'bid'=>$bv['bid'],
                                'bname'=>$bv['bname'],
                                'rid'=>$bv['rid'],
                                'rname'=>$bv['rname'],
                                'hid'=>$bv['hid'],
                                'hname'=>$bv['hname'],
                                'pub_ads_id'=>  $pb['id'],
                            );
                            if($bv['hstate'] == 2) {
                                $tmpbox['error_type'] = 2;
                                $tmp_res = $pub_ads_box_error->addData($tmpbox);
                                continue;
                            } elseif($bv['hstate'] == 3) {
                                $tmpbox['error_type'] = 3;
                                $tmp_res = $pub_ads_box_error->addData($tmpbox);
                                continue;
                            } elseif($bv['rstate'] == 2) {
                                $tmpbox['error_type'] = 4;
                                $tmp_res = $pub_ads_box_error->addData($tmpbox);
                                continue;
                            } elseif($bv['rstate'] == 3) {
                                $tmpbox['error_type'] = 5;
                                $tmp_res = $pub_ads_box_error->addData($tmpbox);
                                continue;
                            } elseif($bv['bstate'] == 2) {
                                $tmpbox['error_type'] = 6;
                                $tmp_res = $pub_ads_box_error->addData($tmpbox);
                                continue;
                            } elseif($bv['bstate'] == 3) {
                                $tmpbox['error_type'] = 7;
                                $tmp_res = $pub_ads_box_error->addData($tmpbox);
                                continue;
                            } else {
                                $map = array();
                                $map['_string'] = " ( ads.`end_date` >= '".$dat['start_time']."' and ads.`start_date` <= '".$dat['end_time']."' ) ";
                                $map['ads_box.box_id'] = $bv['bid'];
                                $map['ads.state'] = array('neq', 2);
                                $field = 'COUNT(ads_box.location_id) AS lcount,ads.id,ads.start_date st,ads.end_date se';
                                $p_tiems = $dat['play_times'];
                                $group = 'ads.start_date,ads.end_date';
                                $ocu_arr = $pub_adsModel->getBoxPlayTimes($map, $field, $group);
                                $bool = false;
                                if( empty($ocu_arr) ) {
                                    $bool = true;
                                } else {

                                    $adv_promote_num_arr = C('ADVE_OCCU');
                                    $adv_promote_num = $adv_promote_num_arr['num'];
                                    //判断单个日期的所占用广告数
                                    $start = strtotime($dat['start_time']);
                                    $end = strtotime($dat['end_time']);
                                    $datearr = array();
                                    while($start <= $end){
                                        $datearr[] = date('Y-m-d',$end);//得到dataarr的日期数组。
                                        $end = $end - 86400;
                                    }

                                    $sum = array();
                                    foreach($datearr as $dk=>$dv) {
                                        $sum[$dv] = 0;
                                        foreach($ocu_arr as $ov) {
                                            if(strtotime($dv) >= strtotime($ov['st'])
                                                && strtotime($dv) <= strtotime($ov['se'])
                                            ) {
                                                $sum[$dv] = $sum[$dv] + $ov['lcount'];
                                            }
                                        }
                                    }
                                    //求出数组最大值
                                    $max = max($sum);
                                    $l_len = $adv_promote_num-$max-$dat['play_times'];
                                    if($l_len>=0) {
                                        //次数足
                                        $bool = true;
                                    }else {
                                        $bool = false;
                                    }
                                }
                                if ( $bool ) {
                                    $box_hotel_arr = array();
                                    for($i=0; $i<$dat['play_times']; $i++){
                                        $box_hotel_arr[] = array(
                                            'box_id'=>$bv['bid'],
                                            'pub_ads_id'=>$pb['id'],
                                            'location_id'=>0,
                                            'create_time'=>$now_date,
                                            'update_time'=>$now_date,
                                            'down_state'=>0,
                                        );
                                    }
                                    $tmp_res = $pub_adsboxModel->addAll($box_hotel_arr);
                                } else {
                                    $tmpbox['error_type'] = 1;
                                    $tmp_res = $pub_ads_box_error->addData($tmpbox);
                                    continue;
                                }
                            }
                        }
                    } else {
                        $tpp_b = array(
                            'bid'=>0,
                            'bname'=>'',
                            'rid'=>0,
                            'rname'=>'',
                            'hid'=>$pd['hotel_id'],
                            'hname'=>'',
                            'pub_ads_id'=>  $pb['id'],
                        );
                        $tpp_b['error_type'] = 8;

                        $tmp_res = $pub_ads_box_error->addData($tpp_b);
                    }
                }
            }
            //修改状态值为0
            $pub_adsModel->updateInfo(array('id'=>$pb['id']),array('state'=>0,'update_time'=>$now_date));
        }
        echo 'ok选择酒楼处理完成 ';
    }



    /**
     * @desc 清理心跳历史数据
     */
    public function clearHeartLog(){
        $save_days = C('HEART_LOG_SAVE_DAYS');
        
        $node_date = date('Y-m-d H:i:s',strtotime("-$save_days days"));
        $m_heart_log = new \Admin\Model\HeartLogModel();
        $where =" last_heart_time<'".$node_date."'";
        $m_heart_log->where($where)->delete();
        echo '清除完毕';
        exit;
    }

    public function generateDir() {
        //获取需要执行的列表
        $pub_path     = '';
        $map          = array();
        $savor_path   = '';
        $gendir       = '';
        $single_list  = array();
        $pubic_path = dirname(APP_PATH).DIRECTORY_SEPARATOR.'Public';
        $pub_path = $pubic_path.DIRECTORY_SEPARATOR;
        $signle_Model = new \Admin\Model\SingleDriveListModel();
        $map['state'] = 0;
        $field='hotel_id_str, gendir';
        $single_list = $signle_Model->getWhere($map, $field);
        $smfileModel = new SimFile();
        if ($single_list) {
            foreach ($single_list as $sk=>$sv) {
                $this->copy_j = array();
                $gendir = $sv['gendir'];
                $po_th = $pub_path.$gendir;
                $savor_path = $po_th.DIRECTORY_SEPARATOR.'savor';
                if ( $smfileModel->create_dir($savor_path) ) {
                    echo '创建目录'.$savor_path.'成功'.PHP_EOL;
                    $hotel_id_arr = json_decode($sv['hotel_id_str'], true);
                    foreach ( $hotel_id_arr as $hv) {
                        $hotel_path = $savor_path.DIRECTORY_SEPARATOR.$hv;
                        if ( $smfileModel->create_dir($hotel_path) ) {
                            echo '创建目录'.$hotel_path.'成功'.PHP_EOL;
                            $adv_path = $hotel_path.DIRECTORY_SEPARATOR.'adv';
                            if ( $smfileModel->create_dir($adv_path) ) {
                                echo '创建目录'.$adv_path.'成功'.PHP_EOL;
                                //创建json文件
                                $play_file = $hotel_path.DIRECTORY_SEPARATOR.'play_list.json';
                                if ( $smfileModel->create_file($play_file, true) ) {
                                    $info = '';
                                    echo '创建JSON文件'.$play_file.'成功'.PHP_EOL;
                                    //获取酒楼对应节目单
                                    $info = $this->getHotelMedia($hv);
                                    if ( !empty($info) ) {
                                        if ( 0 == $info['jtype'] ) {
                                            //复制文件
                                            $oldpath = '';
                                            $old_hotel_id = $info['hotel_id'];
                                            $oldpath = $savor_path.DIRECTORY_SEPARATOR.$old_hotel_id;
                                            $old_playfile = $oldpath.DIRECTORY_SEPARATOR.'play_list.json';
                                            if ( $smfileModel->handle_file($old_playfile,
                                                $play_file, 'copy', true)) {
                                                echo '源文件'.$old_playfile.'复制到'.$play_file.'成功'.PHP_EOL;
                                            } else {
                                                echo '源文件'.$old_playfile.'复制到'.$play_file.'失败'.PHP_EOL;
                                            }
                                        } else {
                                            $smfileModel->write_file($play_file, $info['res']);
                                            $this->copy_j[$hv] = $info['menuid'];
                                        }
                                    }

                                } else {
                                    echo '创建JSON文件'.$play_file.'失败'.PHP_EOL;
                                }
                            } else {
                                echo '创建目录'.$adv_path.'失败'.PHP_EOL;
                            }
                        } else {
                            echo '创建目录'.$hotel_path.'失败'.PHP_EOL;
                        }
                    }

                } else {
                    echo '创建目录'.$savor_path.'失败'.PHP_EOL;
                }

                $zip=new \ZipArchive();
                $po_th = iconv("utf-8", "GB2312//IGNORE", $po_th);
                $pzip = $po_th.'.zip';
                $zflag = $zip->open($pzip, \ZipArchive::CREATE);
                if ($zflag) {
                   // $zip->addFile($po_th.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."tap.txt");
                    $this->addtoZip($po_th, $zip, $pubic_path);
                    //print_r($zip);
                    //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
                    $zip->close(); //关闭处理的zip文件
                    echo '创建压缩包'.$gendir.'成功'.PHP_EOL;
                } else {
                    var_export($zip);
                    echo '创建压缩包失败';
                }
                die;
            }
        } else {
            echo '数据已执行完毕';
        }

    }

    public function addtoZip($path, $zip, $pubic_path) {
        print_r($path);
        echo '<hr/>';
        $handler=opendir($path);
        while ( ($filename=readdir($handler))!==false ) {
            if($filename != "." && $filename != ".."){

                $real_filename = $path.DIRECTORY_SEPARATOR.$filename;
                var_dump($filename);
                var_dump($real_filename);
                echo '<hr/><hr/>';
                if(is_dir($real_filename)){
                    if ( count(scandir($real_filename)) ==2 ){
                        //是空目录
                        $rpname = str_replace($pubic_path.DIRECTORY_SEPARATOR, '', $real_filename);
                        $zip->addEmptyDir($rpname);
                    } else {
                        $this->addtoZip($real_filename, $zip, $pubic_path);
                    }

                }else{
                    //将文件加入zip对象
                    $real_filename = iconv("utf-8", "GB2312//IGNORE", $real_filename);
                    $zip->addFile($real_filename);
                    $rpname = str_replace($pubic_path.DIRECTORY_SEPARATOR, '', $real_filename);
                    $zip->renameName($real_filename, $rpname);
                }
            }
        }
        @closedir($path);
    }


    public function getHotelMedia($hotel_id) {
        //jtype 0已存在json文件,1需要添加
        $result = array();
        $menuhotelModel = new \Admin\Model\MenuHotelModel();
        $adsModel = new \Admin\Model\AdsModel();
        //获取广告期号
        $per_arr = $menuhotelModel->getadsPeriod($hotel_id);
        //var_export($per_arr);
        if(empty($per_arr)){
            return $result;
        }
        $menuid = $per_arr[0]['menuid'];
        $rdata = $this->copy_j;
        $hda = array_search($menuid, $rdata);
        if ( $hda ) {
            $rp['hotel_id']= $hda;
            $rp['jtype']= 0;
            return $rp;
        }
        $perid = $per_arr[0]['period'];
        //获取节目单的节目数据start
        $result['playbill_list'][0]['version'] = array(
            'label'=>'节目期号',
            'type'=>'pro',
            'version'=>$perid,
        );
        $pro_arr = $adsModel->getproInfo($menuid);
        $pro_arr = $this->changeadvList($pro_arr,1);
        $result['playbill_list'][0]['media_lib'] = $pro_arr;


        //获取节目单的广告数据start
        $result['playbill_list'][1]['version'] = array(
            'label'=>'广告期号',
            'type'=>'ads',
            'version'=>$perid,
        );
        $ads_arr = $adsModel->getadsInfo($menuid);
        $ads_arr = $this->changeadvList($ads_arr,2);
        $result['playbill_list'][1]['media_lib'] = $ads_arr;
        //获取节目单的广告数据end

        //获取节目单的宣传片start
        $result['playbill_list'][2]['version'] = array(
            'label'=>'宣传片期号',
            'type'=>'adv',
            'version'=>$perid,
        );
        $adv_arr = $adsModel->getadvInfo($hotel_id, $menuid);
        $adv_arr = $this->changeadvList($adv_arr,1);
        $result['playbill_list'][2]['media_lib'] = $adv_arr;

        //获取酒楼信息
        $hotelModel = new \Admin\Model\HotelModel();
        $ho_arr = $hotelModel->getHotelMacInfo($hotel_id);
        $data = array();
        $data= $ho_arr[0];
        foreach($data as $dk=>$dv){
            $data['hotel_id'] = intval($data['hotel_id']);
            $data['area_id'] = intval($data['area_id']);
            $data['key_point'] = intval($data['key_point']);
            $data['state'] = intval($data['state']);
            $data['state_reason'] = intval($data['state_reason']);
            $data['flag'] = intval($data['flag']);
            $data['hotel_box_type'] = intval($data['hotel_box_type']);
        }
        $result['boite'] = $data;

        //获取包间信息
        $field = "  id AS room_id,name as room_name,
        hotel_id,type as room_type,state,flag,remark,
        create_time,
        update_time";
        $room['hotel_id'] = $hotel_id;
        $room['flag'] = 0;
        $room['state'] = 1;
        $romModel = new \Admin\Model\RoomModel();
        $room_arr = $romModel->getInfo($field, $room);
        $room_arr =  $this->changeroomList($room_arr);
        $result['room_info'] = $room_arr;
        //获取机顶盒信息
        $boxModel = new \Admin\Model\BoxModel();
        $sysconfigModel = new \Admin\Model\SysConfigModel();
        $field = "  box.id AS box_id,box.room_id,box.name as box_name,
        room.hotel_id,box.mac as box_mac,box.state,box.flag,box.switch_time,box.volum as volume ";
        $where = ' and box.state=1 and box.flag=0';
        $box_arr = $boxModel->getInfoByHotelid($hotel_id, $field, $where);
        $where = " 'system_ad_volume','system_switch_time'";
        $sys_arr = $sysconfigModel->getInfo($where);
        $sys_arr = $this->changesysconfigList($sys_arr);
        if(!empty($box_arr)){
            $box_arr = $this->changeBoxList($box_arr, $sys_arr);
            $result['box_info'] = $box_arr;
        }
        $rp['res'] = json_encode($result);
        $rp['menuid']= $menuid;
        $rp['jtype']= 1;
        return $rp;
    }


    /**
     * changeadvList  将已经数组修改字段名称
     * @access public
     * @param $res
     * @return array
     */
    private function changesysconfigList($res){
        $vol_arr = C('CONFIG_VOLUME');
        if($res){
            foreach ($res as $vk=>$val) {
                foreach($vol_arr as $k=>$v){
                    if($k == $val['config_key']){
                        $res[$vk]['label']  = $v;                                   }
                }
                $res[$vk]['configKey'] =  $res[$vk]['config_key'];
                $res[$vk]['configValue'] =  $res[$vk]['config_value'];
                unset($res[$vk]['config_key']);
                unset($res[$vk]['config_value']);
                unset($res[$vk]['status']);
            }

        }
        return $res;
        //如果是空
    }

    /**
     * changeBoxList  将已经数组修改字段名称
     * @access public
     * @param $res 机顶盒数组
     * * @param $sys_arr 系统数组
     * @return array
     */
    private function changeBoxList($res, $sys_arr){        $da = array();

        foreach ($sys_arr as $vk=>$val) {
            foreach($val as $sk=>$sv){
                if($sv == 'system_ad_volume') {
                    if(empty($val['configValue'])){
                        $da['volume'] = 0;
                    }else{
                        $da['volume'] = $val['configValue'];
                    }
                }
                if($sv == 'system_switch_time') {
                    if(empty($val['configValue'])){
                        $da['switch_time'] = 0;
                    }else{
                        $da['switch_time'] = $val['configValue'];
                    }
                    break;
                }
            }

        }
        if($res){
            foreach ($res as $vk=>$val) {
                if (empty($da['volume'])) {
                    $res[$vk]['volume'] = empty($val['volume'])?'':$val['volume'];
                } else {
                    $res[$vk]['volume'] = $da['volume'];
                }
                if (empty($da['switch_time'])) {
                    $res[$vk]['switch_time'] =  empty($val['switch_time'])?'':$val['switch_time'];
                    $val['switch_time'];
                } else {
                    $res[$vk]['switch_time'] = $da['switch_time'];
                }

                foreach($val as $rk=>$rv){
                    if(is_numeric($rv)){
                        $res[$vk][$rk] = intval($rv);
                    }
                    if($res[$vk][$rk] === null){
                        $res[$vk][$rk] = '';
                    }
                }
            }
        }


        return $res;
        //如果是空
    }


    private function changeroomList($res){
        $ro_type = C('ROOM_TYPE');

        if($res){
            foreach ($res as $vk=>$val) {
                foreach($ro_type as $k=>$v){
                    if($k == $val['room_type']){
                        $res[$vk]['room_type']  = $v;                                   }
                }
                foreach($val as $rk=>$rv){
                    if(is_numeric($res[$vk][$rk])){
                        $res[$vk][$rk] = intval($rv);
                    }
                    if($res[$vk][$rk] === null){
                        $res[$vk][$rk] = '';
                    }
                }

            }

        }

        return $res;
        //如果是空
    }

    /**
     * changeadvList  将已经数组修改字段名称
     * @access public
     * @param $res
     * @return array
     */
    private function changeadvList($res,$type){
        if($res){
            foreach ($res as $vk=>$val) {
                if($type==1){
                    $res[$vk]['order'] =  $res[$vk]['sortnum'];
                    unset($res[$vk]['sortnum']);
                }

                if(!empty($val['name'])){
                    $ttp = explode('/', $val['name']);
                    $res[$vk]['name'] = $ttp[2];
                }
            }

        }
        return $res;
        //如果是空
    }
}