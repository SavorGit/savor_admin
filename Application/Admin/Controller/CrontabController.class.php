<?php
namespace Admin\Controller;
use Common\Lib\Curl;
use Common\Lib\UmengApi;
use Think\Controller;
use Common\Lib\SimFile;
use \Common\Lib\SavorRedis;
use \Common\Lib\Aliyun;
use \Common\Lib\MailAuto;
/**
 * @desc 定时任务
 *
 */
class CrontabController extends Controller
{
    private $oss_host = '';
    public $copy_j = array();


    public function __construct(){
        $this->oss_host = get_oss_host();
    }

    public function insCurrentDetailRecopt(){
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $heart_loss_hours   = C('HEART_LOSS_HOURS');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        
        //获取所有酒楼
        $m_hotel = new \Admin\Model\HotelModel();
        //虚拟小平台也拿到
        //$where = " a.id not in(7,53)  and a.state=1 and a.flag =0 and a.hotel_box_type in($hotel_box_type_str) and b.mac_addr !=''";
        $where = "  a.state=1 and a.flag =0 and a.hotel_box_type in($hotel_box_type_str) ";

        $max_hour = 720;
        $hotel_list = $m_hotel->getHotelLists($where,'','','a.id,b.mac_addr');


        //$hotel_list = array_slice($hotel_list,0, 5);

        $hotel_unusual = new \Admin\Model\HotelUnusualModel();
        $m_heart_log = new \Admin\Model\HeartLogModel();
        $m_box = new \Admin\Model\BoxModel();
        //正常酒楼 、异常酒楼
        $start_time = date('Y-m-d H:i:s',strtotime('-'.$heart_loss_hours.' hours'));
        $now = date("Y-m-d H:i:s");
        $now_time = strtotime($now);
        foreach($hotel_list as $key=>$v){
            $data = array();
            $hotel_id = $v['id'];
            $box_not_normal_num = 0;
            $box_last_hour = 0;
            //判断表是否存在该
            $r_info = $hotel_unusual->getOneInfo('id', array('hotel_id'=>$hotel_id) );


            //判断酒楼小平台是否有心跳
            $where = '';
            $where .=" 1 and hotel_id=".$hotel_id." and type=1";
            $where .="  and last_heart_time>='".$start_time."'";
            $ret = $m_heart_log->getOnlineHotel($where,'hotel_id');
            $data['hotel_id'] = $hotel_id;
            $where = array();
            $where['hotel_id'] = $v['id'];
            $where['type']  =1;
            $dt = $m_heart_log->getInfo('last_heart_time',$where);
            //判断是否是虚拟小平台

            if($v['mac_addr'] == '000000000000') {
                //虚拟小平台标志
                $data['small_plat_status'] = 2;
                $data['small_plat_report_time'] = $now;
            } else {
                if(!empty($ret)){
                    $data['small_plat_status'] = 1;
                    $data['small_plat_report_time'] = $dt['last_heart_time'];
                    $data['pla_lost_hour'] = 0;
                } else {
                    if(!empty($dt)){
                        $p_last_time = $dt['last_heart_time'];
                        $data['small_plat_report_time'] = $p_last_time;
                        $l_hour = strtotime($p_last_time);
                        $data['pla_lost_hour'] = ceil( ($now_time-$l_hour)/3600);
                    }else {
                        $data['small_plat_report_time'] = '';
                        $data['pla_lost_hour'] = $max_hour;
                    }
                    $data['small_plat_status'] = 0;

                }
            }
            //机顶盒判断
            $where = '';
            $where .=" 1 and room.hotel_id=".$hotel_id.' and a.flag=0  and a.state=1 ';
            $box_list = $m_box->getListInfo( 'a.id, a.mac,a.state',$where);
            $data['box_num'] = count($box_list);
            foreach($box_list as $ks=>$vs){
                $where = '';
                $where .=" 1 and hotel_id=".$hotel_id." and type=2 and box_mac='".$vs['mac']."'";
                $where .="  and last_heart_time>='".$start_time."'";

                $rets  = $m_heart_log->getOnlineHotel($where,'hotel_id');
                
                if(empty($rets)){
                    $box_not_normal_num +=1;
                    $where = '';
                    $where .=" 1 and hotel_id=".$hotel_id." and type=2 and box_mac='".$vs['mac']."'";
                    $hea_online  = $m_heart_log->getOnlineHotel($where,'last_heart_time');
                    if( empty($hea_online) ) {

                    } else {
                        $box_report_time = strtotime($hea_online[0]['last_heart_time']);
                        if($hea_online[0]['last_heart_time'] == '0000-00-00 00:00:00') {

                        } else {
                            $diff_box_report_time = $now_time-$box_report_time;
                            $box_last_hour += $diff_box_report_time;
                        }
                    }
                }
            }
            $data['not_normal_box_num'] = $box_not_normal_num;
            $data['not_box_percent'] =  ceil(($box_not_normal_num/$data['box_num'])*100);
            $data['box_lost_hour'] = ceil($box_last_hour/3600);
            $data['update_time'] = $now;
            if ($r_info) {
                 //更新
                $map['id'] = $r_info['id'];;
               $bool = $hotel_unusual->saveData($data, $map);
            } else {
                //添加
                $data['create_time'] = $now;
                $bool = $hotel_unusual->addData($data);

            }
            if($bool) {
                echo $hotel_id.'执行成功\n'.'<br/>';
            }

        }

    }

    public function reportNew(){
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $heart_loss_hours   = C('HEART_LOSS_HOURS');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        
        //酒楼总数
        $m_hotel = new \Admin\Model\HotelModel();
        $where = '';
        /* $where['state'] = 1;
        $where['hotel_box_type'] = array('in','2,3'); */
        $where = " a.id not in(7,53)  and a.state=1 and a.flag =0 and a.hotel_box_type in($hotel_box_type_str) and b.mac_addr !='' and b.mac_addr !='000000000000'";
        $hotel_all_num = $m_hotel->getHotelCountNums($where);

        //正常酒楼 、异常酒楼
        $end_time = date('Y-m-d H:i:s',strtotime('-10 minutes'));
        $start_time = date('Y-m-d H:i:s',strtotime('-'.$heart_loss_hours.' hours'));
        $m_heart_log = new \Admin\Model\HeartLogModel();
        $m_box = new \Admin\Model\BoxModel();
        $where = '';

        /* $where['state'] = 1;
        $where['flag'] = 0;
        $where['hotel_box_type'] = array('in','2,3'); */
        $where = " a.id not in(7,53)  and a.state=1 and a.flag =0 and a.hotel_box_type in($hotel_box_type_str) and b.mac_addr !='' and b.mac_addr !='000000000000'";
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
        $real_not_normal_box_num = $not_normal_box_num -$black_box_num;
        if($real_not_normal_box_num<0){
            $real_not_normal_box_num = 0;
        }
        //$data['not_normal_box_num']       = $not_normal_box_num -$black_box_num;          //异常机顶盒
        $data['not_normal_box_num']         = $real_not_normal_box_num;
        $m_hotel_error_report = new \Admin\Model\HotelErrorReportModel();
        $id = $m_hotel_error_report->addInfo($data);
        if($id){
            $ticker = '截止到'.date('m-d H点').','.$data['not_normal_hotel_num'].'家酒楼异常,其中'
                .$data['not_normal_smallplat_num'].'个小平台失联超过'.$heart_loss_hours.'小时,'
                .$data['not_normal_box_num'].'个机顶盒失联超过'.$heart_loss_hours.'小时';
            $title = '小热点异常报告';
            $desc  = '小热点异常报告';
        /*    $m_hotel_error_report_detail = new \Admin\Model\HotelErrorReportDetailModel();

            foreach($result as $key=> $v){
                $result[$key]['error_id'] = $id;

            }
            $m_hotel_error_report_detail->addInfo($result,2);*/
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

    public function report(){
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $heart_loss_hours   = C('HEART_LOSS_HOURS');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        
        //酒楼总数
        $m_hotel = new \Admin\Model\HotelModel();
        $where = '';
        /* $where['state'] = 1;
        $where['hotel_box_type'] = array('in','2,3'); */
        $where = " a.id not in(7,53)  and a.state=1 and a.flag =0 and a.hotel_box_type in($hotel_box_type_str) and b.mac_addr !='' and b.mac_addr !='000000000000'";
        $hotel_all_num = $m_hotel->getHotelCountNums($where);
    
        //正常酒楼 、异常酒楼
        $end_time = date('Y-m-d H:i:s',strtotime('-10 minutes'));
        $start_time = date('Y-m-d H:i:s',strtotime('-'.$heart_loss_hours.' hours'));
        $m_heart_log = new \Admin\Model\HeartLogModel();
        $m_box = new \Admin\Model\BoxModel();
        $where = '';
    
        /* $where['state'] = 1;
        $where['flag'] = 0;
        $where['hotel_box_type'] = array('in','2,3'); */
        $where = " a.id not in(7,53)  and a.state=1 and a.flag =0 and a.hotel_box_type in($hotel_box_type_str) and b.mac_addr !='' and b.mac_addr !='000000000000'";
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
        $real_not_normal_box_num = $not_normal_box_num -$black_box_num;
        if($real_not_normal_box_num<0){
            $real_not_normal_box_num = 0;
        }
        //$data['not_normal_box_num']       = $not_normal_box_num -$black_box_num;          //异常机顶盒
        $data['not_normal_box_num']         = $real_not_normal_box_num;
        $m_hotel_error_report = new \Admin\Model\HotelErrorReportModel();
        $id = $m_hotel_error_report->addInfo($data);
        if($id){
            $ticker = '截止到'.date('m-d H点').','.$data['not_normal_hotel_num'].'家酒楼异常,其中'
                      .$data['not_normal_smallplat_num'].'个小平台失联超过'.$heart_loss_hours.'小时,'
                      .$data['not_normal_box_num'].'个机顶盒失联超过'.$heart_loss_hours.'小时';
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

    public function getLocIdNum($total_num, $have, $remain, $times) {

        sort($have);
        sort($remain);
        /*var_export($have);
        var_export($remain);*/
        $flag = 0;
        $blank = intval(floor($total_num/$times)) ;
        $pos = $remain[0];
        $fp_arr = array();
        $is_direct =  intval(floor(count($remain)/$times));
        if ($is_direct == 1) {
            $fp_arr = array_slice($remain, 0,$times);
        } else  {
            while($flag < $times) {

                if($flag == 0) {
                    $fp_arr[] = $pos;
                    array_unshift($have, $pos);
                    sort($have);
                    unset($remain[0]);
                } else {

                    $next_pos_num = $pos + $blank;
                    if(in_array($next_pos_num, $remain)) {
                        $pos = $next_pos_num;
                        array_unshift($have, $pos);
                        $key = array_search($pos, $remain);
                        unset($remain[$key]);
                        $fp_arr[] = $pos;
                    } else {
                        if($next_pos_num > $total_num) {
                            $pos = $next_pos_num - $total_num;
                            if(in_array($pos, $remain)) {
                                array_unshift($have, $pos);
                                $key = array_search($pos, $remain);
                                unset($remain[$key]);
                                $fp_arr[] = $pos;
                            } else {

                                $tmp = $remain;
                                array_unshift($tmp, $pos);
                                sort($tmp);
                                $key = array_search($pos, $tmp);
                                $pos = $tmp[$key+1];
                                array_unshift($have, $pos);
                                $key = array_search($remain, $pos);
                                unset($remain[$key]);
                                $fp_arr[] = $pos;

                            }
                        } else {
                            $nex_dat = range($next_pos_num, $total_num);
                            $a_dif = array_diff($nex_dat, $have);

                            if(empty($a_dif)) {
                              sort($remain);
                              array_unshift($have, $remain[0]);
                              sort($have);
                              $fp_arr[] = $remain[0];
                                $pos = $remain[0];
                                unset($remain[0]);


                           } else {
                              $tmp = $remain;
                              array_unshift($tmp, $next_pos_num);
                              sort($tmp);
                              $key = array_search($next_pos_num, $tmp);
                              $pos = $tmp[$key+1];
                              array_unshift($have, $pos);
                              $key = array_search($pos, $remain);
                              unset($remain[$key]);
                              $fp_arr[] = $pos;
                          }


                        }
                    }

                }
                $flag++;
            }

        }
        sort($fp_arr);
       /* var_export($fp_arr);*/
        return $fp_arr;

    }


    /**
     * @desc 随机生成广告的位置
     */
    public function recordAdsLocation_back(){
        exit();
        $adv_promote_num_arr = C('ADVE_OCCU');
        $adv_promote_num = $adv_promote_num_arr['num'];
        $base_location_arr = range(1, $adv_promote_num);
        //获取未执行插入位置的广告
        $m_pub_ads = new \Admin\Model\PubAdsModel();
        $m_pub_ads_box = new \Admin\Model\PubAdsBoxModel();
        $pub_ads_list = $m_pub_ads->getEmptyLocationList();
        foreach($pub_ads_list as $key=>$val){//循环每一个发布但未执行添加位置脚本的广告

            $pub_ads_box_arr = $m_pub_ads_box->getBoxArrByPubAdsId($val['id']);   //获取当前广告发布到的盒子ID
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
        
        //获取虚拟小平台配置的酒楼id 如果该酒楼在虚拟小平台 通知更新虚拟小平台该酒楼的A类广告
        $tmp_hotel_arr = getVsmallHotelList();
        $redis = new SavorRedis();
        $redis->select(12);
        
        foreach($pub_ads_list as $key=>$val){//循环每一个发布但未执行添加位置脚本的广告
            
            $pub_ads_box_arr = $m_pub_ads_box->getBoxArrByPubAdsId($val['id']);   //获取当前广告发布到的盒子ID
            
            foreach($pub_ads_box_arr as $k=>$v){//循环该发布的广告对应的机顶盒
                
                $all_have_location_arr = array();
                //取出该机顶盒所有未填写位置的列表
                $all_empty_location_info = $m_pub_ads_box->getEmptyLocation('id',$val['id'],$v['box_id']);
                if(!empty($all_empty_location_info)){
                    //取出该机顶盒在该广告起止时间内所有的位置
                    $all_have_location_info = $m_pub_ads_box->getLocationList($v['box_id'],$val['start_date'],$val['end_date']);
                   // print_r($m_pub_ads_box->getLastSql());

                    foreach($all_have_location_info as $hl){
                        $all_have_location_arr[] = $hl['location_id'];
                    }


                    $diff_location_arr = array_diff($base_location_arr, $all_have_location_arr);
                    //如果还有未分配的位置
                    if(!empty($diff_location_arr)){
                        //把未分配得位置负值给location_id =0 的记录
                        $count = count($all_empty_location_info);
                        $now_location_arr = $this->getLocIdNum($adv_promote_num, $all_have_location_arr, $diff_location_arr, $count);
                        foreach($all_empty_location_info as $ek=>$ev){
                            $where['id'] = $ev['id'];
                            $data['location_id'] = $now_location_arr[$ek];
                            $data['update_time'] = date('Y-m-d H:i:s');
                            if(!empty($data['location_id'])){
                                $m_pub_ads_box->updateInfo($where,$data);
                            }
                            
                        } 
                    }
                    ///删除该盒子的广告缓存  
                    $cache_key = C('PROGRAM_ADS_CACHE_PRE').$v['box_id'];
                    $redis->remove($cache_key);
                }
            }
            $pub_ads_hotel_arr = $m_pub_ads_box->getHotelArrByPubAdsId($val['id']);//获取当前广告发布的酒楼id

            /*
            $vm_hotel_arr = array();
            //新虚拟小平台接口
            $redis->select(10);
            $v_hotel_list_key = C('VSMALL_HOTELLIST');
            $redis_result = $redis->get($v_hotel_list_key);
            $v_hotel_list = json_decode($redis_result,true);
            $v_hotel_arr = array_column($v_hotel_list, 'hotel_id');  //虚拟小平台酒楼id
            $v_ads_key = C('VSMALL_ADS');
            foreach($pub_ads_hotel_arr as $tk=>$tv){
                if(in_array($tv['hotel_id'], $tmp_hotel_arr)){
                    $vm_hotel_arr[] = $tv['hotel_id'];
                    
                }
                if(in_array($tv['hotel_id'], $v_hotel_arr)){
                    $keys_arr = $redis->keys($v_ads_key.$tv['hotel_id']."*");
                    foreach($keys_arr as $vv){
                        $redis->remove($vv);
                    }
                }
            }
            sendTopicMessage($vm_hotel_arr, 8);
            */
            $v_ads_key = C('VSMALL_ADS');
            foreach($pub_ads_hotel_arr as $tk=>$tv){
                $keys_arr = $redis->keys($v_ads_key.$tv['hotel_id']."*");
                foreach($keys_arr as $vv){
                    $redis->remove($vv);
                }
            }
            
            $m_pub_ads->updateInfo(array('id'=>$val['id']),array('state'=>1,'update_time'=>date('Y-m-d H:i:s')));
        
        }
        echo "OK";
    }


    public function getAllBox($hotel_id,$type='0') {
        
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        if($type==0){
            $where = ' ( 1=1 and sht.id='.$hotel_id.' and
                        sht.flag=0
                        and sht.hotel_box_type in ('.$hotel_box_type_str.') and room.flag=0 and box.flag=0)';
        }else if($type==1) {//去除非包间版位
            $where = ' ( 1=1 and sht.id='.$hotel_id.' and
                        sht.flag=0
                        and sht.hotel_box_type in ('.$hotel_box_type_str.') and room.flag=0 and box.flag=0 and room.type=1)';
        }
        
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


    public function recordAllboxByhotel(){
        //获取所有未执行广告id
        $now_date = date("Y-m-d H:i:s");
        $pub_adsModel = new \Admin\Model\PubAdsModel();
        $pub_adsboxModel = new \Admin\Model\PubAdsBoxModel();
        $pub_ads_hotel = new \Admin\Model\PubAdsHotelModel();
        $pub_ads_box_error = new \Admin\Model\PubAdsBoxErrorModel();
        $field = 'id, start_date, end_date, play_times,del_hall';
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
                    if($pb['del_hall']==1){
                        $box_arr = $this->getAllBox($pd['hotel_id'],$type=1);
                    }else if($pb['del_hall']==0){
                        $box_arr = $this->getAllBox($pd['hotel_id']);
                    }
                    
                    //var_dump($box_arr);
                    // var_dump($box_arr);
                    if (!empty($box_arr)) {
                        //筛选出可以用的机顶盒
                        $map = array();
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
                                $map['_string'] = "('".$dat['start_time'] ."'
                            <=ads.end_date and '".$dat['end_time']."' >=
                            ads.start_date )";
                                $map['ads_box.box_id'] = $bv['bid'];
                                $map['ads.state'] = array('neq', 2);
                                $mfield = 'ads_box.location_id as lid';
                                //被占用的数组
                                $ocu_arr = $pub_adsModel->getBoxPlayTimes($map,
                                    $mfield);
                                $ocu_len = count($ocu_arr);
                                $bool = false;
                                if( empty($ocu_arr) ) {
                                    $bool = true;
                                } else {
                                    $adv_promote_num_arr = C('ADVE_OCCU');
                                    $adv_promote_num = $adv_promote_num_arr['num'];
                                    $l_len = $adv_promote_num-$ocu_len-$dat['play_times'];
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
     * @desc 酒店所有机顶盒数据并插入pub_ads_box
     */
    public function recordAllboxByhoteltttttttt(){
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
                    sleep(10);
                }
            }
            //修改状态值为0
            sleep(10);
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




        set_time_limit(0);
        ini_set("memory_limit", "1024M");
        //获取需要执行的列表
        $map          = array();
        $pubic_path = dirname(APP_PATH).DIRECTORY_SEPARATOR.'Public/udriverpath';
        $pub_path = $pubic_path.DIRECTORY_SEPARATOR;
        $signle_Model = new \Admin\Model\SingleDriveListModel();
        $map['state'] = 0;
        $field='hotel_id_str, gendir, id, up_cfg';
        $order = ' id asc ';
        $limit = 1;
        $single_list = $signle_Model->getOrderOne($field, $map, $order, $limit);
        $smfileModel = new SimFile();
        $now_date = date("Y-m-d H:i:s");
        $hotelModel = new \Admin\Model\HotelModel();
        $update_config_cfg = C('UPD_STR');
        $accessKeyId = C('OSS_ACCESS_ID');
        $accessKeySecret = C('OSS_ACCESS_KEY');
        $endpoint = C('OSS_HOST');
        $bucket = C('OSS_BUCKET');
        $pic_err_log = LOG_PATH.'upan_error_'.date("Y-m-d").'log';
        //获取系统默认音量值
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $whereconfig = " config_key in('system_ad_volume','system_pro_screen_volume','system_demand_video_volume','system_tv_volume','system_switch_time')  ";

        $volume_arr = $m_sys_config->getList($whereconfig);
        $vol = array();
        $vol_default = C('CONFIG_VOLUME_VAL');
        if($volume_arr) {
            foreach($volume_arr as $k=>$v) {
                if($v['config_key']=='system_ad_volume'){
                    //广告轮播音量
                    if( $v['config_value'] === '') {
                        $vol['system_ad_volume'] = $vol_default['system_ad_volume'];
                    } else {
                        $vol['system_ad_volume'] = intval($v['config_value']);
                    }

                }else if($v['config_key']=='system_pro_screen_volume'){
                    //投屏音量
                    if( $v['config_value'] === '') {
                        $vol['system_pro_screen_volume'] = $vol_default['system_pro_screen_volume'];
                    } else {
                        $vol['system_pro_screen_volume'] = intval($v['config_value']);
                    }

                }else if($v['config_key']=='system_demand_video_volume'){
                    //点播音量
                    if( $v['config_value'] === '') {
                        $vol['system_demand_video_volume'] = $vol_default['system_demand_video_volume'];
                    } else {
                        $vol['system_demand_video_volume'] = intval($v['config_value']);
                    }

                }else if($v['config_key']=='system_tv_volume'){
                    //电视音量
                    if( $v['config_value'] === '') {
                        $vol['system_tv_volume'] = $vol_default['system_tv_volume'];
                    } else {
                        $vol['system_tv_volume'] = intval($v['config_value']);
                    }

                }else if($v['config_key']=='system_switch_time' ){

                    //电视音量
                    if($v['status'] == 1) {
                        if( empty($v['config_value']) ) {
                            $vol['system_switch_time'] = $vol_default['system_switch_time'];
                        } else {
                            $vol['system_switch_time'] = intval($v['config_value']);
                        }
                    } else {
                        $vol['system_switch_time'] = -8;
                    }
                }
            }
        }

        if ($single_list) {
            foreach ($single_list as $sk=>$sv) {

                $po_th = '';
                $gid = $sv['id'];
                $gendir = $sv['gendir'];
                $upcfg = $sv['up_cfg'];
                if($upcfg) {
                    $upcfg = explode(',', $upcfg);
                } else {
                    $upcfg = array();
                }
                $po_th = $pub_path.$gendir;
                $savor_path = $po_th.DIRECTORY_SEPARATOR.'savor';
                $savor_me = $po_th.DIRECTORY_SEPARATOR.'media';
                $savor_log = $po_th.DIRECTORY_SEPARATOR.'log';
                $delfile = $po_th;
                $delzipfile = $po_th.'.zip';
                $del_res = $smfileModel->remove_dir($delfile, true);
                $del_res = $smfileModel->unlink_file($delzipfile);
                if ( $smfileModel->create_dir($savor_path)
                    && $smfileModel->create_dir($savor_me)
                    && $smfileModel->create_dir($savor_log)
                ) {
                    //创建hotel.json文件
                    $hotel_file = $po_th.DIRECTORY_SEPARATOR.'hotel.json';
                    if ( $smfileModel->create_file($hotel_file, true) ) {
                      //  echo '创建hotel.json成功'.PHP_EOL;
                        //写入hotel.json
                        $hwhere = array();
                        $hwhere['hotel_box_type'] =  array('in', array('1','4','5') );
                        $hwhere['state'] = 1;
                        $hwhere['flag'] = 0;
                        $hotel_arr = $hotelModel->getInfo('id hotel_id, name hotel_name', $hwhere);
                        if ($hotel_arr) {
                            $hotel_info = json_encode($hotel_arr);
                        } else {
                            $hotel_info = '';
                        }
                        $smfileModel->write_file($hotel_file, $hotel_info);
                    }
                   // echo '创建目录'.$savor_path.'成功'.PHP_EOL;

                    $hotel_id_arr = json_decode($sv['hotel_id_str'], true);
                    $adsModel = new \Admin\Model\AdsModel();
                    $menuhotelModel = new \Admin\Model\MenuHotelModel();
                    $xuan_hotel_st = '';
                    $start_time = microtime(true);
                    $oss_host = get_oss_host();
                    $rs_hotel = array();
                    foreach ( $hotel_id_arr as $hav) {
                        $per_arr = $menuhotelModel->getadsPeriod($hav);
                        if($per_arr) {
                            //获取宣传片
                            $menupid = $per_arr[0]['menuid'];
                            $resa = $adsModel->getuAdvname($hav, $menupid);
                            if($resa) {
                                //获取酒楼下宣传片
                                foreach($resa as $ras=>$rks) {
                                    $rs_hotel[$rks['hname']][$rks['ads_id']] = array(
                                        'name'=>$rks['adname'],
                                        'url'=>$oss_host.$rks['oss_addr'],
                                        'hname'=>$rks['hname'],
                                    );
                                }
                            }
                        } else {
                            continue;
                        }
                    }

                    foreach($rs_hotel as $arh=>$ahv) {
                        $xuan_hotel_st .= $arh."\r\n";
                        foreach($ahv as $bk=>$bv) {
                            $xuan_hotel_st .= $bv['name']."\t".$bv['url']."\r\n";
                        }
                    }

                    $adv_file = $po_th.DIRECTORY_SEPARATOR.'hotel.txt';
                    $smfileModel->write_file($adv_file, $xuan_hotel_st);
                    $m_version_upgrade = new \Admin\Model\UpgradeModel();
                    $aliyun = new Aliyun($accessKeyId, $accessKeySecret, $endpoint);
                    $aliyun->setBucket($bucket);
                    foreach ($hotel_id_arr as $hid) {
                        $field = 'sdv.oss_addr apurl,sdv.md5 md5 ';
                        $apk_device_type = 2;
                        $apk_info =$m_version_upgrade->getLastOneByDeviceNew($field, $apk_device_type, $hid);
                        //$apk_info = $m_version_upgrade->getLastOneByDevice($field, $apk_device_type, $hid);
                        if($apk_info) {
                            $flag = 0;
                            $apk_filename = $gendir.'.apk';
                            $afilename = $savor_path .DIRECTORY_SEPARATOR. $apk_filename;
                            for($ai=0;$ai<2;$ai++) {
                                $aliyun->getObjectToLocalFile($apk_info['apurl'], $afilename);
                                ob_start();
                                readfile($afilename);
                                $md5file= ob_get_contents();
                                ob_end_clean();
                                $down_md5 = md5($md5file);

                                if($down_md5 == $apk_info['md5']) {
                                    $flag = 1;
                                    break;
                                } else {
                                    $tpic = '创建' . $afilename . '失败' . PHP_EOL;
                                    error_log($tpic, 3, $pic_err_log);
                                    if (file_exists($afilename))
                                    {

                                        unlink($afilename);
                                    }

                                    continue;
                                }
                            }
                            if($flag == 1) {
                                break;
                            }

                        } else {
                            continue;
                        }

                    }




                    $end_time = microtime(true);
                    echo '循环执行时间为：'.($end_time-$start_time).' s';

                    $start_time = microtime(true);
                    foreach ( $hotel_id_arr as $hv) {
                        $hotel_path = $savor_path.DIRECTORY_SEPARATOR.$hv;
                        if ( $smfileModel->create_dir($hotel_path) ) {
                            //echo '创建目录'.$hotel_path.'成功'.PHP_EOL;
                            $adv_path = $hotel_path.DIRECTORY_SEPARATOR.'adv';
                            if ( $smfileModel->create_dir($adv_path) ) {
                                //  echo '创建目录'.$adv_path.'成功'.PHP_EOL;
                                //创建json文件
                                $play_file = $hotel_path.DIRECTORY_SEPARATOR.'play_list.json';
                                if ( $smfileModel->create_file($play_file, true) ) {
                                    $info = '';
                                    // echo '创建JSON文件'.$play_file.'成功'.PHP_EOL;
                                    //获取酒楼对应节目单

                                    $info = $this->getHotelMedia($hv, $gendir, $vol);

                                    if ( !empty($info) ) {

                                        if(!empty($info['logourl'])) {
                                            //写入酒店目录图片
                                            $img_url = $info['logourl'];
                                            $img_filename = $info['logo_name'];
                                            $img_path = $savor_path.DIRECTORY_SEPARATOR.$hv;

                                            $new_img = $img_path.DIRECTORY_SEPARATOR.$img_filename;
                                            $oldmd5 = $info['lomd5'];
                                            for($ai=0;$ai<2;$ai++) {
                                                $aliyun->getObjectToLocalFile($img_url, $new_img);
                                                $md5file = file_get_contents($new_img);
                                                $down_md5 = md5($md5file);
                                                if ($down_md5 == $oldmd5) {
                                                    break;

                                                } else {
                                                    if (file_exists($new_img)) {
                                                        $tpic = '创建' . $gid . '的图片' . $new_img . '失败' . PHP_EOL;
                                                        error_log($tpic, 3, $pic_err_log);

                                                        unlink($new_img);
                                                    }
                                                    continue;
                                                }
                                            }

                                        }
                                        $smfileModel->write_file($play_file, $info['res']);


                                    }

                                    //写入update.cfg
                                    $update_path = $hotel_path.DIRECTORY_SEPARATOR.'update.cfg';
                                    $upd_str = '';

                                    foreach($update_config_cfg as $cfgk=>$cfgv) {

                                        if( in_array($cfgk, $upcfg) ) {
                                            $upd_str .= $cfgv['ename']."\n";
                                        } else {
                                            $upd_str .= '#'.$cfgv['ename']."\n";
                                        }
                                    }

                                    $smfileModel->write_file($update_path, $upd_str);

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
                //ob_clean();

                $zip=new \ZipArchive();
                //var_export($zip);
                $po_th = iconv("utf-8", "GB2312//IGNORE", $po_th);
                $pzip = $po_th.'.zip';
                $zflag = $zip->open($pzip, \ZipArchive::CREATE);
                if ($zflag) {
                    $this->addtoZip($po_th, $zip, $pubic_path);
                    $zip->close(); //关闭处理的zip文件
                   // echo '创建压缩包'.$gendir.'成功'.PHP_EOL;
                    //修改状态值为0
                    $signle_Model->updateInfo(array('id'=>$sv['id']), array('state'=>1,'update_time'=>$now_date));
                     echo '状态值'.$gendir.'修改成功'.PHP_EOL;
                } else {
                   // var_export($zip);
                    echo '创建压缩包失败';
                }
               // $end_time = microtime(true);
                //echo '循环执行时间为：'.($end_time-$start_time).' s';


            }
        } else {
            echo '数据已执行完毕';
        }

    }

    public function addtoZip($path, $zip, $pubic_path) {
        //print_r($path);
       // echo '<hr/>';
        $handler=opendir($path);
        while ( ($filename=readdir($handler))!==false ) {
            if($filename != "." && $filename != ".."){

                $real_filename = $path.DIRECTORY_SEPARATOR.$filename;
                //var_dump($filename);
               // var_dump($real_filename);
                //echo '<hr/><hr/>';
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


    public function getHotelMedia($hotel_id, $gendir, $vol) {
        //jtype 0已存在json文件,1需要添加
        $result = array();
        $menuhotelModel = new \Admin\Model\MenuHotelModel();
        $adsModel = new \Admin\Model\AdsModel();
        //获取广告期号
        $per_arr = $menuhotelModel->getadsPeriod($hotel_id);



        if (empty($per_arr)) {
            $emp_play_str = array();
            $result['play_list'] = $emp_play_str;
        }else{
            $menuid = $per_arr[0]['menuid'];
            $perid = $per_arr[0]['period'];
            $result['period'] = $perid;

            $redis = SavorRedis::getInstance();
            $redis->select(13);
            $procache_key = 'udriverpan_pro_'.$menuid;
            $adscache_key = 'udriverpan_ads_'.$menuid;
            $pro_arr = $redis->get($procache_key);
            if($pro_arr) {
                $pro_arr = json_decode($pro_arr, true);
                $pro_arr = $this->changeadvList($pro_arr,1);
            } else {
                $pro_arr = $adsModel->getproInfoNew($menuid);
                $redis->set($procache_key , json_encode($pro_arr), 120);
                $pro_arr = $this->changeadvList($pro_arr,1);

            }
            $ads_arr = $redis->get($adscache_key);
            if($ads_arr) {
                $ads_arr = json_decode($ads_arr, true);
                $ads_arr = $this->changeadvList($ads_arr,2);
            } else {
                $ads_arr = $adsModel->getadsInfoNew($menuid);
                $redis->set($adscache_key , json_encode($ads_arr), 120);
                $ads_arr = $this->changeadvList($ads_arr,2);
            }

            $adv_arr = $adsModel->getupanadvInfoNew($hotel_id, $menuid);
            $adv_arr = $this->changupaneadvList($adv_arr,1);
            $result['play_list'] = array_merge($pro_arr,
                $ads_arr,$adv_arr);
        }


        //获取酒楼信息
        $hotelModel = new \Admin\Model\HotelModel();
        $ar['sht.id'] = $hotel_id;
        $field = ' sht.id hotel_id, sht.name AS hotel_name,sht.area_id AS area_id,
        sht.addr AS address ';
        $ho_arr = $hotelModel->getHotelidByArea($ar, $field);
        $result['boite'] = $ho_arr[0];
        //获取包间信息
        $field = "  rom.id room_id,rom.NAME room_name,rom.TYPE room_type,sbox.id
        box_id, sbox.mac box_mac,sbox.name box_name,sbox.switch_time,sbox.volum volume ";
        $room['rom.hotel_id'] = $hotel_id;
        $room['rom.flag'] = 0;
        $room['rom.state'] = 1;
        $room['sbox.flag'] = 0;
        $room['sbox.state'] = 1;
        $romModel = new \Admin\Model\RoomModel();
        $room_arr = $romModel->getRoomBox($field, $room);
        $room_arr =  $this->changeroomList($room_arr);
        $rp = array();
        $bk = array();
        $vol_default = C('CONFIG_VOLUME_VAL');
        foreach ($room_arr as $rk=>$rv) {
            $bk[$rv['room_id']][] = array(
                'box_id'    => $rv['box_id'],
                'box_mac'   => $rv['box_mac'],
                'box_name'   => $rv['box_name'],
                'switch_time'   => ($vol['system_switch_time']<0)?(($rv['switch_time']==='')?$vol_default['system_switch_time']:$rv['switch_time']):$vol['system_switch_time'],
                'volume'   => $rv['volume'],
                'room_id'   => $rv['room_id'],
                'ads_volume'=> $vol['system_ad_volume'],
                'project_volume'=> $vol['system_pro_screen_volume'],
                'demand_volume'=> $vol['system_demand_video_volume'],
                'tv_volume'=> $vol['system_tv_volume'],
            );
            $rp[$rv['room_id']] = array(
                'room_id'   => $rv['room_id'],
                'room_name' => $rv['room_name'],
                'room_type' => $rv['room_type'],
                'box_list' => $bk[$rv['room_id']],
            );
        }
        $rp = array_values($rp);
        $result['room_info'] = $rp;

        //获取版本信息
        $m_version_upgrade = new \Admin\Model\UpgradeModel();
        $device_type = 2;
        $field = ' sdv.md5 apkmd,sdv.version_code vername,sdv.oss_addr apurl ';
        $upgrade_info = array();
        //$upgrade_info = $m_version_upgrade->getLastOneByDevice($field, $device_type, $hotel_id);
        $upgrade_info = $m_version_upgrade->getLastOneByDeviceNew($field, $device_type, $hotel_id);
        if(empty($upgrade_info)) {
            $apk_md = '';
            $apk_name = '';
            $apk_url = '';
            $ave = '';
        } else {
            $apk_md = $upgrade_info['apkmd'];
            $apk_name = $upgrade_info['vername'];
            $apk_url = $this->oss_host.$upgrade_info['apurl'];
            $ave = $gendir.'.apk';
        }
        //获取logomd5
        $logo_arr = $hotelModel->gethotellogoInfo($hotel_id);
        if(empty($logo_arr)) {
            $logo_md = '';
            $logo_url = '';
            $logo_name = '';
            $logo_version = '';
            $logo_urld = '';
        } else {
            $logo_md  = $logo_arr[0]['logo_md5'];
            $logo_urld = $logo_arr[0]['lourl'];
            $logo_url = $this->oss_host.$logo_arr[0]['lourl'];
            $logo_name = substr($logo_url,strripos($logo_url,"/")+1);
            $logo_version = $logo_arr[0]['id'];;

        }


        $result['version'] = array(
            'apkMd5'            => $apk_md,
            'newestApkVersion'  => $apk_name,
            'apk_name'          => $ave,
            'logo_name'         => $logo_name,
            'logo_url'          => $logo_url,
            'logo_md5'          => $logo_md,
            'logo_version'      => $logo_version,
        );
        $rp['res'] = json_encode($result);
        //$rp['menuid']= $menuid;
        $rp['jtype']= 1;
        $rp['logourl']= $logo_urld;
        $rp['lomd5']= $logo_md;
        $rp['apk_url'] = $apk_url;
        $rp['logo_name'] = $logo_name;
        return $rp;
    }
    /**
     * @desc 餐厅端日志上报
     */
    public function  syncHotelLog(){
        $redis = SavorRedis::getInstance();
        $key = 'dinnertoupinglog';
        $redis->select(13);
        $count = $redis->lsize($key);
        $num = 0;
        $rool_back = array();
        $size = 100;
        $dinner_hall_Model = new \Admin\Model\DinnerHallLogModel(); 
        $insert_arr = array();
        while($data = $redis->lpop($key)) {
            $data = json_decode($data, true);
            if(!empty($data)){
                $bool = $dinner_hall_Model->add($data);
            }
            /* $insert_arr[] = $data;
            $num++;
            if($num%$size == 0) {
    
                $bool = $dinner_hall_Model->addAll($insert_arr);
                if($bool) {
                    $page = $num/$size;
                    echo '第'.$page.'页完毕'.PHP_EOL;
                    //sleep(2);
                    $insert_arr = array();
                } else {
                    $rool_back[] = $insert_arr;
                }
            } */
    
        }
        echo "ok";
        /* if($insert_arr) {
            $bool = $dinner_hall_Model->addAll($insert_arr);
            if($bool) {
                $page = $page+1;
                echo '23i第'.$page.'页完毕'.PHP_EOL;
            } else {
                $rool_back[] = $insert_arr;
            }
        }
        if($rool_back) {
            foreach ($rool_back as $k=>$v) {
                foreach ($v as $ks=>$vs) {
                    $bool = $redis->rpush('dinnertoupinglog', json_encode($vs));
                }
                
            }
        } else {
            echo '处理成功';
        } */
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

    /**
     * changeadvList  将已经数组修改字段名称
     * @access public
     * @param $res
     * @return array
     */
    private function changupaneadvList($res,$type){
        if($res){
            foreach ($res as $vk=>$val) {
                if($type==1){
                    $res[$vk]['order'] =  $res[$vk]['sortnum'];
                    unset($res[$vk]['sortnum']);
                }

                if(!empty($val['name'])){
                    $ttp = explode('/', $val['name']);
                    $res[$vk]['name'] = $ttp[2];
                }else{
                    unset($res[$vk]);
                }
            }

        }
        return $res;
        //如果是空
    }

    public function clearUdriver() {
        //获取目录
        $pubic_path = dirname(APP_PATH).DIRECTORY_SEPARATOR.
        'Public/udriverpath';
        var_export($pubic_path);
        $pub_path = $pubic_path.DIRECTORY_SEPARATOR;
        $signle_Model = new \Admin\Model\SingleDriveListModel();
        $yestoday = strtotime(date("Y-m-d", strtotime("-1 day")));
        //$yestoday = time();
        $smfileModel = new SimFile();
        if (is_dir($pub_path)) {
            $path_arr = scandir($pub_path);
            print_r($path_arr);
            foreach ($path_arr as $k=>$v) {
                if($v != '.' && $v != '..') {
                    $detail_path = $pub_path.$v;
                    $v = str_replace('udriver_','',$v);
                    $time = substr($v,0, 10);
                    var_export($time);
                    if ($time < $yestoday) {
                        //删除文件及压缩包
                        print_r($detail_path);
                        $smfileModel->remove_dir($detail_path, true);
                    }
                }
                //获取时间戳文件
            }
        } else {

        }
        //删除前天

    }

    public function removeCurAdsBoxToHis(){
        //获取所有过期广告
        $now_date = date("Y-m-d");
        $pubadsModel = new \Admin\Model\PubAdsModel();
        $pubox = new \Admin\Model\PubAdsBoxModel();
        $pubHis = new \Admin\Model\PubAdsBoxHistoryModel();
        $field = 'id,type';
        //找执行完毕的广告
        $where = " 1=1 and ((state = 1 and is_remove=0 ) or (state=2 and is_remove=0))";
        $where.=" AND end_date <'$now_date'";
        $pad_arr = $pubadsModel->getWhere($where, $field);
        //var_export($pad_arr);exit;
        if($pad_arr) {
            foreach($pad_arr as $pk=>$pv) {
                
                $pa_id = $pv['id'];
                $p_ads = array();
                $map = array();
                $p_ads['id'] = $pa_id;
                if($pv['type'] == 1) {
                    //版位选
                    //从box表移动数据到history
                    $oldfield = 'box_id, pub_ads_id, location_id, create_time';
                    $insfield = 'box_id, pub_ads_id, location_id, ctime';
                    $map['pub_ads_id'] = $pa_id;
                    $newtable = 'savor_pub_ads_box_history';
                    $bool = $pubox->removeToNew($insfield, $oldfield, $map,$newtable);
                   /* print_r($pubox->getLastSql());*/
                    if($bool) {
                        //删除box表数据
                        $del_box = $pubox->deleteInfo($map);
                        if($del_box) {

                        } else {
                            //删除history数据
                            $pubHis->deleteInfo($map);
                            continue;
                        }
                    } else {
                        continue;
                    }
                }
                //酒楼选
                if($pv['type'] == 2) {
                    $puberrorModel = new \Admin\Model\PubAdsBoxErrorModel();
                    $bwhere['pub_ads_id'] = $pa_id;
                    $bnum = $pubox->getDataCount($bwhere);
                    //判断box是否为空
                    if($bnum >0 ) {
                        //从box表移动数据到box_history
                        $oldfield = 'box_id, pub_ads_id, location_id, create_time';
                        $insfield = 'box_id, pub_ads_id, location_id, ctime';
                        $map['pub_ads_id'] = $pa_id;
                        $newtable = 'savor_pub_ads_box_history';
                        $bool = $pubox->removeToNew($insfield, $oldfield, $map,$newtable);
                        if($bool) {
                            $oldfield = 'box_id, pub_ads_id, location_id, create_time';
                            $insfield = 'box_id, pub_ads_id, location_id, ctime';
                            $map['pub_ads_id'] = $pa_id;
                            $newtable = 'savor_pub_ads_box_history';
                            $err_count = $puberrorModel->getDataCount($map);
                            if($err_count > 0) {
                                //$bool = $pubox->removeToNew($insfield, $oldfield, $map,$newtable);
                                $bool = true;
                                if($bool) {
                                    //从box_error表移动数据到error_history
                                    $oldfield = 'bid, bname, rid, rname, hid, hname,pub_ads_id, error_type';
                                    $insfield = 'bid, bname, rid, rname, hid, hname,pub_ads_id, error_type';
                                    $newtable = 'savor_pub_ads_box_error_history';
                                    $error_remove = $puberrorModel->removeToNew($insfield, $oldfield, $map,$newtable);
                                    if($error_remove) {
                                        //删除box_error表数据
                                        $puberrorModel->deleteInfo($map);
                                        //删除box表数据
                                        $pubox->deleteInfo($map);

                                    } else {
                                        //删除box_history表数据
                                        $pubHis->deleteInfo($map);
                                        continue;
                                    }
                                } else {

                                    //删除box_history
                                    $pubHis->deleteInfo($map);
                                    continue;
                                }
                            } else {
                                //删除box表数据
                                $pubox->deleteInfo($map);
                            }
                        } else {
                            continue;
                        }




                    } else {
                        //判断error
                        $oldfield = 'box_id, pub_ads_id, location_id, create_time';
                        $insfield = 'box_id, pub_ads_id, location_id, ctime';
                        $map['pub_ads_id'] = $pa_id;
                        $newtable = 'savor_pub_ads_box_history';
                        $err_count = $puberrorModel->getDataCount($map);
                        if($err_count > 0) {
                            $bool = $pubox->removeToNew($insfield, $oldfield, $map,$newtable);
                            if($bool) {
                                //从box_error表移动数据到error_history
                                $oldfield = 'bid, bname, rid, rname, hid, hname,pub_ads_id, error_type';
                                $insfield = 'bid, bname, rid, rname, hid, hname,pub_ads_id, error_type';
                                $newtable = 'savor_pub_ads_box_error_history';
                                $error_remove = $puberrorModel->removeToNew($insfield, $oldfield, $map,$newtable);
                                if($error_remove) {
                                    //删除box_error表数据
                                    $puberrorModel->deleteInfo($map);
                                    //删除box表数据
                                    $pubox->deleteInfo($map);

                                } else {
                                    //删除box_history表数据
                                    $pubHis->deleteInfo($map);
                                    continue;
                                }
                            } else {

                                //删除box_history
                                $pubHis->deleteInfo($map);
                                continue;
                            }
                        } else {
                            //删除box表数据
                            $pubox->deleteInfo($map);
                        }
                    }

                }
                //更改状态值
                $save['is_remove'] = 1;
                $pubadsModel->updateInfo($p_ads, $save);
                echo '更新广告ID'.$pa_id."\n";


            }
        } else {
            echo '数据处理完毕';
        }

    }
    /**
     * @desc 大屏数据监控定时发送邮件
     */
    public function mailBigScreenData(){
        /* $s_key = I('get.s_key');
        if(empty($s_key)){
            exit('您没有权限');
        }
        if($s_key !='322f8f5580740efeec8abfdfdaf1e040'){
            exit('您没有权限');
        } */
        //判断今天是否已经发过
        $redis = SavorRedis::getInstance();
        $redis->select(13);
        $now_date = date('Y-m-d');
        $time_key = 'statistics_hotel_time_flag';
        $time = time();
        $d_val = $redis->get($time_key);
        if($d_val) {
            if(date("Y-m-d", $d_val) == $now_date) {
                //已经发过
                echo '已经发过';
                exit;
            }else {
                $keyt = 'statistics_hotel_time';
                $t_val = $redis->get($keyt);
                if(  empty($t_val) || (date("Y-m-d", $t_val) != $now_date)) {
                    echo '发送失败日期非今天';
                    exit;
                } 
            }
        } else {
            $keyt = 'statistics_hotel_time';
            $t_val = $redis->get($keyt);
            if(  empty($t_val) || (date("Y-m-d", $t_val) != $now_date)) {
                echo '发送失败日期非今天';
                exit;
            }
        }
        
        
        

        $body = '<!DOCTYPE html>
                    <html>
                	<head>
                		<meta charset="UTF-8">
                		<title>小热点系统状态日报</title>
                	</head>
                	<body>
                		<h2 align="left">【小热点系统状态日报】'.date('Y-m-d').'</h2>
                		
                		<table align="left" style="text-align: left;font-size: 20px;">
                			<tr>
                				<td>统计时间:'.date('Y-m-d H:i:s').'</td>
                			</tr>
                			<tr>
                				<td style="font-weight: 700;">----酒楼合作部网络版位统计汇总----</td>
                			</tr>';
        
        
        
        $m_heart_log = new \Admin\Model\HeartLogModel();
        $where = array();
        $fields = "box_id";
        $heart_time = date('Y-m-d H:i:s',strtotime('-5 minutes'));
        $where['type'] = 2;
        $where['last_heart_time'] = array('egt',$heart_time);
         
        $online_box = $m_heart_log->getHotelHeartBox($where,$fields);
        $online_box_num = count($online_box);
         
        $m_box = new \Admin\Model\BoxModel();
         
        $fields = 'b.id';
        $where = '1';
        $where .= ' and h.flag  = 0';
        $where .= ' and h.state = 1';
        $where .= ' and b.flag  = 0';
        $where .= ' and b.state = 1';
        $box_list = $m_box->isHaveMac($fields,$where);
        $normal_box_nums = count($box_list);
        
        //运维任务统计
        $m_option_task =  new \Admin\Model\OptiontaskModel();
        //当月第一天
        $month_start_time = date('Y-m-01 H:i:s',strtotime(date('Y-m-d')));
        $where = array();
        $where['flag']  = 0;
        $where['state'] = array('in',array('4'));
        $where['complete_time'] = array('egt',$month_start_time);
        $complete_task_num = $m_option_task->countNums($where);  //本月已处理任务
         
        $where = array();
        $where['flag'] = 0;
        $where['state'] = array('in',array('1','2','3'));
        $where['create_time'] = array('egt',$month_start_time);
        $not_complete_task_num = $m_option_task->countNums($where); //本月待处理的任务
         
        
        
        
        
        $m_area_info = new \Admin\Model\AreaModel();
        $area_list = $m_area_info->getHotelAreaList();
        $m_box = new \Admin\Model\BoxModel();
        $m_valid_online_monitor = new \Admin\Model\Statisticses\ValidOnlineMonitorModel();
         
        $report_time = date('Ymd',strtotime('-1 days'));
        $type = 2;
        $ttps = $m_valid_online_monitor->countNums(array('report_date'=>$report_time,'type'=>2));
        
        if(empty($ttps)){//昨天该表数据为空不发邮件
            exit('有效屏数据不能为0');
        }
        $heart_hotel_box_type = C('heart_hotel_box_type');
        $net_box_arr = array_keys($heart_hotel_box_type);

        $flag = 0;
        foreach($area_list as $key=>$v){
            $area_list[$key]['region_name'] = str_replace('市', '', $v['region_name']);
            $map   = array();
            $where = array();
            $where['area_id'] = $v['id'];
            $where['type'] = $type;
            $where['report_date'] = $report_time;
            $all_box_nums = $m_valid_online_monitor->countNums($where);
            $where['state'] = 0;
            $not_valid_nums = $m_valid_online_monitor->countNums($where);
            $where['state'] = 1;
            $valid_nums = $m_valid_online_monitor->countNums($where);
            if(empty($valid_nums)){
                $flag ++;
            }
            $body .= '<tr>
				        <td>'.$area_list[$key]['region_name'].'</td>
			             </tr>
			             <tr>
				         <td>有效屏:'.$valid_nums.' 无效屏:'.$not_valid_nums.'</td>
			           </tr>';
        }
        if($flag>0){
            exit('有效屏数据不能为0');
        }
        $body .='<tr>
				<td style="font-weight: 700;">----市场部广告到达统计汇总----</td>
			</tr>';
        
        
       
        
        //广告明细
        $m_program_ads = new \Admin\Model\PubAdsModel();
        $fields = 'med.id,ads.name,pads.start_date,pads.end_date,pads.id pub_ads_id';
        $now_date =  date('Y-m-d H:i:s',strtotime('-1 day'));
        $sort_day = date('j'); //本月第几天 没有前导0
        $all_days = date('t'); //本月一共多少天
	$where = array();
        $where['pads.start_date'] = array('elt',$now_date);
        $where['pads.end_date']   = array('egt',$now_date);
        $where['pads.state']      = 1;
         
        $media_list = $m_program_ads->getPubAdsList($fields,$where);
        $data =  array();
         
        $m_media_monitor = new \Admin\Model\Statisticses\MediaMonitorModel();
        $m_pub_ads_box = new \Admin\Model\PubAdsBoxModel();
        $type = 'ads';
        $yesterday = date('Y-m-d 00:00:00',strtotime('-1 days'));
        
        $ttps = $m_media_monitor->countNums(array('media_type'=>$type,'report_date'=>$yesterday));
        if(empty($ttps)){
            exit('广告到达数据不能为0');
        }
        $heart_type_str = getHeartBoXtypeIds(2);
        foreach($media_list as $key=>$v){
            $media_list[$key]['start_date'] = date('Y-m-d H:i',strtotime($v['start_date']));
            //$pub_ads_count = $m_pub_ads_box->getDataCount(array('pub_ads_id'=>$v['pub_ads_id']),'box_id');
            //$sql ="SELECT COUNT(t.counts) nums FROM  (SELECT COUNT(*) counts FROM savor_pub_ads_box t WHERE `pub_ads_id` = ".$v['pub_ads_id']." GROUP BY box_id) t";
            //$pub_ads_count = $m_program_ads->query($sql);
            
            //echo $m_pub_ads_box->getLastSql();exit;
            if($sort_day==1){
                $sql ="select abox.*,hotel.name,hotel.hotel_box_type
    	               from savor_pub_ads_box_history abox
    	               left join savor_box box on abox.box_id=box.id
    	               left join savor_room room on box.room_id=room.id
    	               left join savor_hotel hotel on room.hotel_id=hotel.id
    	               where abox.pub_ads_id=".$v['pub_ads_id']." and hotel.hotel_box_type in($heart_type_str)
    	               and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0
    	               group by abox.box_id";
            }else {
                $sql ="select abox.*,hotel.name,hotel.hotel_box_type
    	               from savor_pub_ads_box abox
    	               left join savor_box box on abox.box_id=box.id
    	               left join savor_room room on box.room_id=room.id
    	               left join savor_hotel hotel on room.hotel_id=hotel.id
    	               where abox.pub_ads_id=".$v['pub_ads_id']." and hotel.hotel_box_type in($heart_type_str)
                    	               and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0
                    	               group by abox.box_id";
            }
	    $rtss = M()->query($sql);
            $pub_ads_count = count($rtss);
            
            $where = array();
            $where['media_id'] = $v['id'];
            $where['media_type'] = $type;
            $where['report_date'] = $yesterday;
            $valid_nums = $m_media_monitor->countNums($where);
            $media_list[$key]['valid_nums'] = $valid_nums;
            $not_valid_nums = $pub_ads_count-$valid_nums;
            $not_valid_nums = $not_valid_nums>0 ? $not_valid_nums :0;
            $media_list[$key]['not_valid_nums'] = $not_valid_nums;
            
            $body .='<tr>
				        <td>广告名称:'.$v['name'].'</td>
			         </tr>
			         <tr>
				        <td>发布周期:'.$v['start_date'].'至'.$v['end_date'].'</td>
			         </tr>
			         <tr>
				        <td>到达数量:'.$media_list[$key]['valid_nums'].'</td>
			         </tr>
        			 <tr>
        				<td>未到达数量:'.$media_list[$key]['not_valid_nums'].'</td>
        			 </tr>';
        }
        
        $body .='<tr>
				    <td style="font-weight: 700;">----内容部内容到达统计汇总----</td>
			     </tr>';
        //内容到达明细
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        //获取所有酒楼
        $m_hotel = new \Admin\Model\HotelModel();        
        $where = "  a.state=1 and a.flag =0 and a.hotel_box_type in($hotel_box_type_str) ";
        $hotel_list = $m_hotel->getHotelLists($where,'','','a.id,b.mac_addr');
        $m_programmenu_hotel = new \Admin\Model\ProgramMenuHotelModel();
        $program_list = array();
        $mult_arr = array();
        foreach($hotel_list as $key=>$v){
            $fields = 'pl.id,pl.hotel_num,pl.menu_name,pl.create_time,pl.menu_num';
            $order = 'pl.create_time desc';
            $limit = ' 1';
            
            $ret = $m_programmenu_hotel->getProgramByHotelId($v['id'], $fields, $order, $limit);
            $program_list[] = $ret[0];
            $mult_arr[] = $ret[0]['hotel_num'];
        }
        sortArrByOneField($program_list, 'hotel_num',true);
        assoc_unique_new($program_list,'id');
        $program_list = array_slice($program_list, 0,7);

        $m_program_list = new \Admin\Model\ProgramMenuListModel();
        $where = array();
        $menu_id_arr = array();
        foreach($program_list as $v){
            $menu_id_arr[] = $v['id'];
        }
        
        $where['id'] = array('not in',$menu_id_arr);
        $where['hotel_num']= array('gt',0);
        $fields = 'id,hotel_num,menu_name,create_time,menu_num';
        $order  = ' id desc';
        $limit  = ' 13';
        $more_program_list = $m_program_list->getWhere($where,$fields,$order,$limit);
        $program_list = array_merge($program_list,$more_program_list);
        
        $m_program_hotel = new \Admin\Model\ProgramMenuHotelModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_version_monitor = new \Admin\Model\Statisticses\VersionMonitorModel();
        $type = 'pro_down';
        $yesterday = date('Y-m-d 00:00:00',strtotime('-1 days'));
        
        $ttps = $m_version_monitor->countNums(array('version_type'=>$type,'report_date'=>$yesterday));
        
        if(empty($ttps)){
            exit('内容到达数据不能为0'); 
        }
        foreach($program_list as $key=>$v){
            $program_list[$key]['create_time'] = date('Y-m-d H:i',strtotime($v['create_time']));
            $where = array();
            $where['menu_id'] = $v['id'];
            $fields = 'hotel_id';
            $hotel_list = $m_program_hotel->getWhere($where,'',$fields);
            $box_all_nums = 0;
            foreach($hotel_list as $k=>$kv){
                $map = array();
                $map['hotel.id'] = $kv['hotel_id'];
                $map['hotel.flag'] = 0;
                $map['hotel.state'] = 1;
                $map['box.flag'] = 0;
                $map['box.state'] =1;
                $box_nums = $m_box->countNums($map);
                $box_all_nums +=$box_nums;
            }
            $where = array();
            $where['version_code'] = $v['menu_num'];
            $where['version_type'] = $type;
            $where['report_date'] = $yesterday;
            $valid_nums = $m_version_monitor->countNums($where);
            
            $not_valid_nums = $box_all_nums - $valid_nums;
            $not_valid_nums = $not_valid_nums>0 ?$not_valid_nums:0;
            //$not_valid_nums = $box_all_nums - $valid_nums;
            
            $body .='<tr>
				        <td>'.$v['menu_name'].'(到达数量:'.$valid_nums.',未到达数量:'.$not_valid_nums.')</td>
			         </tr>';
        }
        
        $body .='<tr>
				    <td style="font-weight: 700;">----运维部任务统计汇总----</td>
    			</tr>
    			<tr>
    				<td>本月已处理任务:'.$complete_task_num.'</td>
    			</tr>
    			<tr>
    				<td>待处理任务:'.$not_complete_task_num.'</td>
    			</tr>';
        
        $body .= '  </table>
	               </body>
                </html>'; 
        
        $mail_config =  C('SEND_MAIL_CONF');
        $mail_config =  $mail_config['littlehotspot'];
        $mail_config =  C('SEND_MAIL_CONF');
        $mail_config =  $mail_config['littlehotspot'];
        $ma_auto = new MailAuto();
        $mail = new \Mail\PHPMailer();
        $mail->CharSet = "UTF-8";
        $mail->IsSMTP(); // 使用SMTP方式发送
        $mail->Host = $mail_config['host']; // 您的企业邮局域名
        $mail->SMTPAuth = true; // 启用SMTP验证功能
        $mail->Username = $mail_config['username']; // 邮局用户名(请填写完整的email地址)
        $mail->Password = $mail_config['password']; // 邮局密码
        $mail->Port=25;
        $mail->From = $mail_config['username']; //邮件发送者email地址
        $mail->FromName = "小热点系统状态日报";
        
        foreach($mail_config['tomail'] as $v){
            $mail->AddAddress("$v");//收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
        }
        //$mail->AddReplyTo("", "");
        //$mail->AddAttachment("./aa.xls"); // 添加附件
        $mail->IsHTML(true); // set email format to HTML //是否使用HTML格式
        
        $mail->Subject = "小热点系统状态日报".date('Y-m-d H:i:s'); //邮件标题
        $mail->Body = $body;
        if(!$mail->Send())
        {
            echo "邮件发送失败. <p>";
            echo "错误原因: " . $mail->ErrorInfo;
            exit;
        }else {
            echo '邮件发送成功';
            $redis->set($time_key, $time);
        }
    }
    public function importBoxLog(){
        $password = I('password');
        if(empty($password) || $password!='fklj'){
            exit('你的非法行为已被记录');
        }
        $m_oss_box_log = new \Admin\Model\Oss\BoxLogModel();
        
        $yesterday_start = date('Y-m-d 00:00:00',strtotime('-1 day'));
        $yesterday_end   = date('Y-m-d 23:59:59',strtotime('-1 day'));
        $fields = "*";
        $where = array();
        $where['create_time'] =array(array('EGT',$yesterday_start),array('ELT',$yesterday_end));
        $where['flag']        = array('in','16,18');
        $data = $m_oss_box_log->getInfo($fields, $where,'id asc');
        $result = array();
        $m_oss_box_log_detail = new \Admin\Model\Oss\BoxLogDetailModel();
        if(!empty($data)){
            $flag = 0;
            foreach($data as $key=>$v){
                $oss_key = $v['oss_key'];
                if(!empty($oss_key)){
                    $oss_key_arr = explode('/', $oss_key);
                    $v['log_create_date'] = $oss_key_arr[3];
                }
                $v['box_log_id'] = $v['id'];
                unset($v['id']);
                $result[$flag] = $v;
                $flag ++;
                if($flag%100==0){
                    //添加到数据库
                    $ret = $m_oss_box_log_detail->addAll($result);
                    //置空添加到数据库的数组
                    $result = array();
                    $flag = 0;
                }  
            }
            if(!empty($result)){
                //添加到数据库
                $ret = $m_oss_box_log_detail->addAll($result);
            } 
        }
        echo "昨天日志数据导入成功";
    }
    /**
     * @desc 将百度聚屏广告缓存数据导入到数据库
     */
    public function recordBaiduPolyPlayInfo(){
        $redis = SavorRedis::getInstance();
        $redis->select(4);
        $keys = $redis->keys("*");
        $m_box = new \Admin\Model\BoxModel();
        $field = 'h.id hotel_id,r.id room_id,b.id box_id';
        foreach($keys as $k){
            $data = $where = $map =  array();
            $keys_arr = explode(':', $k);
            $info = $m_box->isHaveMac($field, "b.mac='".$keys_arr[0]."' and b.state=1 and b.flag=0 and h.flag=0 and h.state=1");
	        if(!empty($info)){
                $info = $info[0];
                $data['hotel_id'] = $info['hotel_id'];
                $data['room_id']  = $info['room_id'];
                $data['box_id']   = $info['box_id'];
                $data['box_mac']  = $keys_arr[0];
                $data['play_date']= date('Ymd',intval($keys_arr[1]/1000));
                
                $cache_data = $redis->get($k);
                $media_id   = $cache_data['media_id'];
                
                if($media_id>0){
                    $data['media_id']  = $media_id;
                    $data['tpmedia_id']= $cache_data['tpmedia_id'];
                    $data['media_name'] = $cache_data['media_name'];
                    $data['chinese_name'] = $cache_data['chinese_name'];
                    $data['media_md5'] = $cache_data['media_md5'];
                    
                    $map['box_mac'] = $keys_arr[0];
                    $map['media_id']= $data['media_id'];
                    $map['play_date'] = $data['play_date'];
                    //判断该机顶盒当前天是否播过此广告
                    
                    $m_baidu_poly_play_record = new \Admin\Model\BaiduPolyPlayRecordModel();
                    $nums = $m_baidu_poly_play_record->countRows($map);
                    if(empty($nums)){//没有播放记录
                        $data['play_times'] = 1;
                        $ret = $m_baidu_poly_play_record->addInfo($data,1);
                    }else {//已有播放记录 更新  (播放次数+1)
                        $update_time = date('Y-m-d H:i:s',intval($keys_arr[1]/1000));
                        $sql_d = "  `play_times`= `play_times`+1,`update_time`=' ".$update_time."'";
                        $where = " 1 and  box_mac='".$map['box_mac']."' and media_id=".$map['media_id']." and play_date=".$map['play_date'];
                        $ret = $m_baidu_poly_play_record->modifyInfo($sql_d,$where);
                    }
                }else {
                    $cache_data_arr = json_decode($cache_data,true);
                    $data['media_id'] = $cache_data_arr['media_id'];
                    $data['media_name'] = $cache_data_arr['media_name'];
                    $data['media_md5']  = $cache_data_arr['media_md5'];
                    $data['chinese_name'] = $cache_data_arr['chinese_name'];
                    $data['tpmedia_id'] = $cache_data_arr['tpmedia_id'];
                    
                    $map['box_mac'] = $keys_arr[0];
                    $map['media_md5']= $data['media_md5'];
                    $map['play_date'] = $data['play_date'];
                    //判断该机顶盒当前天是否播过此广告
                    
                    $m_baidu_poly_play_record = new \Admin\Model\BaiduPolyPlayRecordModel();
                    $nums = $m_baidu_poly_play_record->countRows($map);
                    if(empty($nums)){//没有播放记录
                        $data['play_times'] = 1;
                        $ret = $m_baidu_poly_play_record->addInfo($data,1);
                    }else {//已有播放记录 更新  (播放次数+1)
                        $update_time = date('Y-m-d H:i:s',intval($keys_arr[1]/1000));
                        $sql_d = "  `play_times`= `play_times`+1,`update_time`=' ".$update_time."'";
                        $where = " 1 and  box_mac='".$map['box_mac']."' and media_md5='".$map['media_md5']."' and play_date=".$map['play_date'];
                        $ret = $m_baidu_poly_play_record->modifyInfo($sql_d,$where);
                    }
                }
                
                
                
                
            }
            $redis->remove($k); //删除缓存
        }
        echo "OK";
    }
    public function recordForScreenPics(){
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $cache_key = C('SAPP_SCRREN')."*";
        $keys = $redis->keys($cache_key);
        $m_smallapp_forscreen_record = new \Admin\Model\ForscreenRecordModel();  
        foreach($keys as $k){
            $data = $redis->lgetrange($k,0,-1);
            foreach($data as $v){
                $forscreen_info = json_decode($v,true);
                
                $ret = $m_smallapp_forscreen_record->addInfo($forscreen_info,1);
                if($ret) $redis->lpop($k);
            }
            $data = $redis->lgetrange($k,0,-1);
            if(empty($data)) $redis->remove($k);
        }
	$cache_key = C('SAPP_UPRES_FORSCREEN')."*";
        $keys = $redis->keys($cache_key);
        foreach($keys as $k){
            $data = $redis->lgetrange($k, 0, -1);
            foreach($data as $v){
                $upresource = json_decode($v,true);
                $where = $data = array();
                if(!empty($upresource['resource_id']) && !empty($upresource['openid'])){
                    $where['action'] = array('neq',8);
		    $where['resource_id'] = $upresource['resource_id'];
                    $where['openid'] = $upresource['openid'];
                    if(!empty($upresource['box_res_sdown_time'])){
                        $data['box_res_sdown_time'] = $upresource['box_res_sdown_time'];
                    }else if(!empty($upresource['box_res_edown_time'])){
                        $data['box_res_edown_time'] = $upresource['box_res_edown_time'];
                    }
                    $ret = $m_smallapp_forscreen_record->updateInfo($where, $data);
                    if($ret) $redis->lpop($k);
                } 
            }
            $ret = $redis->lgetrange($k,0,-1);
            if(empty($ret)) $redis->remove($k);
        }
	$cache_key = C('SAPP_UPDOWN_FORSCREEN')."*";
        $keys = $redis->keys($cache_key);
        foreach($keys as $k){
            $data = $redis->lgetrange($k, 0, -1);
            foreach($data as $v){
                $upresource = json_decode($v,true);
                $where = $data = array();
                $where['forscreen_id'] = $upresource['forscreen_id'];
                $where['resource_id']  = $upresource['resource_id'];
                $where['openid']       = $upresource['openid'];
                $where['box_mac']      = $upresource['box_mac'];
                $nums = $m_smallapp_forscreen_record->where($where)->count();
                if($nums){
                    $data['box_res_sdown_time'] = $upresource['box_res_sdown_time'];
                    $data['box_res_edown_time'] = $upresource['box_res_edown_time'];
                    $ret = $m_smallapp_forscreen_record->updateInfo($where, $data);
                    if($ret) $redis->lpop($k);
                }else {
                    $redis->lpop($k);
                }
		
            }
            $ret = $redis->lgetrange($k,0,-1);
            if(empty($ret)) $redis->remove($k);
        }
	$cache_key = C('SAPP_BOX_FORSCREEN_NET')."*";
        $keys = $redis->keys($cache_key);
        foreach($keys as $k){
            
            $data = $redis->lgetrange($k, 0, -1);
            
            foreach($data as $v){
                $netresource = json_decode($v,true);
                
                $search = array();
                $search['forscreen_id'] = $netresource['forscreen_id'];
                $search['resource_id']  = $netresource['resource_id'];
                $tmp = $m_smallapp_forscreen_record->getOne('id', $search);
                
                if(!empty($tmp)){
                    if($netresource['is_exist']==0){//资源不存在
                        if(!empty($netresource['resource_id']) && !empty($netresource['openid'])){
                            $where = array();
                            $dt = array();
                            //$where['action'] = array('neq',8);
                            $where['forscreen_id'] = $netresource['forscreen_id'];
                            $where['resource_id'] = $netresource['resource_id'];
                            $where['openid'] = $netresource['openid'];
                            if(!empty($netresource['box_res_sdown_time'])){
                                $dt['box_res_sdown_time'] = $netresource['box_res_sdown_time'];
                            }
                            if(!empty($netresource['box_res_edown_time'])){
                                $dt['box_res_edown_time'] = $netresource['box_res_edown_time'];
                            }
                            $dt['is_break'] = $netresource['is_break'];
                            $dt['is_exist'] = $netresource['is_exist'];
                            $dt['update_time'] = date('Y-m-d H:i:s');
                            $ret = $m_smallapp_forscreen_record->updateInfo($where, $dt);
                            $redis->lpop($k);
                        }
                    }else if($netresource['is_exist']==1 || $netresource['is_exist']==2){//资源存在 //资源下载失败
                        $where = $dt = array();
                        //$where['action'] = array('neq',8);
                        $where['forscreen_id'] = $netresource['forscreen_id'];
                        $where['resource_id'] = $netresource['resource_id'];
                        $where['openid'] = $netresource['openid'];
                        $dt['is_break'] = $netresource['is_break'];
                        $dt['is_exist'] = $netresource['is_exist'];
                        $dt['update_time'] = date('Y-m-d H:i:s');
                        $ret = $m_smallapp_forscreen_record->updateInfo($where, $dt);
                        
                        $tt = $redis->lpop($k);
                    }
                }else {
                    $redis->lpop($k);
                }
                $ret = $redis->lgetrange($k,0,-1);
                if(empty($ret)) $redis->remove($k);
            }
        }
        echo "OK";
        
    }
    public function recordPlayGameTime(){
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $cache_key = C('SAPP_PLAY_GAME')."*";
        $keys = $redis->keys($cache_key);
	$m_turntab_log = new \Admin\Model\Smallapp\TurntableLogModel();
        $m_turntab_detail = new \Admin\Model\Smallapp\TurntableDetailModel();
        foreach($keys as $k){
            $ca_arr = explode(':', $k);
            $ac_id = $ca_arr[2];
            $rets = $m_turntab_log->getOne('id', array('activity_id'=>$ac_id));
            if(empty($rets)){
                $redis->remove($k);
                continue;
            }
	    
	    $data = $redis->lgetrange($k, 0, -1);
            foreach($data as $v){
                $play_game_info = json_decode($v,true);
                $where = $data = array();
		if(!empty($play_game_info['activity_id'])){
                    if(!empty($play_game_info['box_orggame_time'])){
                        $where['activity_id'] = $play_game_info['activity_id'];
                        $data['box_orggame_time'] = $play_game_info['box_orggame_time'];
                        
                        $ret = $m_turntab_log->updateInfo($where, $data);
                        if($ret) $redis->lpop($k);
                    }else if(!empty($play_game_info['box_startgame_time'])){
                        $where['activity_id'] = $play_game_info['activity_id'];
                        $data['box_startgame_time'] = $play_game_info['box_startgame_time'];
                        
                        $ret = $m_turntab_log->updateInfo($where, $data);
                        if($ret) $redis->lpop($k);
                    }else if(!empty($play_game_info['box_join_time'])){
                        $where['activity_id'] = $play_game_info['activity_id'];
                        $where['openid'] = $play_game_info['openid'];
                        $data['box_join_time'] = $play_game_info['box_join_time'];
                        $ret = $m_turntab_detail->updateInfo($where, $data);
                        if($ret) $redis->lpop($k);
                    }
                }else {
                    $redis->lpop($k);
                }
            }
            $ret = $redis->lgetrange($k,0,-1);
            if(empty($ret)) $redis->remove($k);
        }
	echo "OK";
    }
    /**
     * @desc 记录想要玩游戏的用户信息
     */
    public function recordWantgame(){
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $cache_key = C('SAPP_WANT_GAME')."*";
        $keys = $redis->keys($cache_key);
        $m_smallapp_forscreen_record = new \Admin\Model\ForscreenRecordModel();
        foreach($keys as $k){
            $data = $redis->lgetrange($k,0,-1);
            foreach($data as $v){
                $wantgame_info = json_decode($v,true);
        
                $ret = $m_smallapp_forscreen_record->addInfo($wantgame_info,1);
                if($ret) $redis->lpop($k);
            }
            $data = $redis->lgetrange($k,0,-1);
            if(empty($data)) $redis->remove($k);
        }
        echo "OK";
    }
    /**
     * @desc 记录电视显示小程序码日志
     */
    public function recordSuncodeLog(){
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $cache_key = C('SAPP_SUNCODE_LOG')."*";
        $keys = $redis->keys($cache_key);
        $keys = array_slice($keys, 0,5);
	$m_suncode_log = new \Admin\Model\Smallapp\SuncodeLogModel();
        foreach($keys as $k){
            $data = $redis->lgetrange($k, 0, -1);
            foreach($data as $v){
                $suncode_log = json_decode($v,true);
                if(!empty($suncode_log['start_time'])){
                    $ret = $m_suncode_log->addInfo($suncode_log);
                    if($ret) $redis->lpop($k);
                }else if(!empty($suncode_log['end_time'])){
                    $map = array();
                    $map['log_id'] = $suncode_log['log_id'];
                    $map['box_mac']= $suncode_log['box_mac'];
                    $nums = $m_suncode_log->countNums($map);
                    if(!empty($nums)){
                        $updata = array();
                        $updata['end_time'] = $suncode_log['end_time'];
                        $ret = $m_suncode_log->updateInfo($map, $updata);
                        if($ret) $redis->lpop($k);
                    }else {
                       $ret = $m_suncode_log->addInfo($suncode_log);
                       if($ret) $redis->lpop($k);
                    }
			
                }
            }
            $data = $redis->lgetrange($k,0,-1);
            if(empty($data)) $redis->remove($k);
        }
        echo 'OK';
    }
    /**
     * @desc 按照天统计投屏网络状况
     */
    public function staticSappNet(){
        $jstime = I('get.jstime');
        $strtime =  strtotime('-1 day');
        $start_time = $jstime ? $jstime." 00:00:00" : date('Y-m-d 00:00:00',$strtime) ;
        $end_time   = $jstime ? $jstime." 23:59:59" : date('Y-m-d 23:59:59',$strtime) ;
        
        $m_forscreen_log = new \Admin\Model\ForscreenRecordModel();
        $fields = 'hotel.id hotel_id,hotel.name hotel_name';
        $where = array();
        $where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
	$where['a.box_res_sdown_time'] = array('neq', 0);
        $where['a.box_res_edown_time'] = array('neq', 0);
        $where['hotel.flag'] = 0;
        $where['hotel.state']= 1; 
	$where["_string"] = "  a.box_res_sdown_time>a.res_eup_time and a.box_res_edown_time >a.box_res_sdown_time";
	$hotel_list = $m_forscreen_log->getWhere($fields, $where, $order='', $limit='', $group="hotel.id");
        $m_static_net =  new \Admin\Model\Smallapp\StaticNetModel();
        foreach($hotel_list as $key=>$v){
            $where = $data =  array();
            $where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
            $where['hotel.id'] = $v['hotel_id'];
	    $where['a.box_res_sdown_time'] = array('neq', 0);
            $where['a.box_res_edown_time'] = array('neq', 0);
	    $where['hotel.flag'] = 0;
            $where['hotel.state']= 1; 
            $where["_string"] = "  a.box_res_sdown_time>a.res_eup_time and a.box_res_edown_time >a.box_res_sdown_time";
	    $data['box_donw_nums'] = $m_forscreen_log->countWhere($where);         //机顶盒总下载次数
            
            $fields = "sum(`resource_size`) as all_res_size";
            $ret = $m_forscreen_log->getWhere($fields, $where);                    //机顶盒总下载大小
            //$data['res_size'] = $ret[0]['all_res_size'];
            $data['res_size'] = $ret[0]['all_res_size'] ?  $ret[0]['all_res_size'] :0;
	    
            $fields = "sum(`box_res_edown_time` - `box_res_sdown_time`) as down_times";  
            $ret = $m_forscreen_log->getWhere($fields, $where);
            $down_times = $ret[0]['down_times'];                       
            
            $data['avg_down_speed'] = round($data['res_size'] / ($down_times/1000),2) ;       //平均下载速度
            
            
            $fields = " max(`resource_size` / (`box_res_edown_time`-`box_res_sdown_time`)) as max_down_speed";
            $ret = $m_forscreen_log->getWhere($fields, $where);
            $data['max_down_speed'] = round($ret[0]['max_down_speed']*1000 , 2) ;                //最快下载速度
                 
            
            $fields = " min(`resource_size` / (`box_res_edown_time`-`box_res_sdown_time`)) as min_down_speed";
	    $ret = $m_forscreen_log->getWhere($fields, $where);
	    $data['min_down_speed'] = round($ret[0]['min_down_speed']*1000 , 2);                //最慢下载速度
                   
            
            $where = array();
            $where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
            $where['hotel.id'] = $v['hotel_id'];
            $where['a.res_eup_time'] = array('neq',0);
            $where['a.box_res_sdown_time'] = array('neq',0);
	    $where['hotel.flag'] = 0;
            $where['hotel.state']= 1; 
	    $where["_string"] = "  a.box_res_sdown_time>a.res_eup_time and a.box_res_edown_time >a.box_res_sdown_time"; 
            $data['order_times'] = $m_forscreen_log->countWhere($where);            //总指令次数
            
            $fields = "sum(`box_res_sdown_time` - `res_eup_time`) as all_delay_times";
            $ret = $m_forscreen_log->getWhere($fields, $where);
            $all_delay_times = $ret[0]['all_delay_times'];
            
            $data['avg_delay_time'] = round(($all_delay_times / 1000) / $data['order_times'] ,2) ;  //平均指令延时
            
            $fields = "max(`box_res_sdown_time` - `res_eup_time`) max_delay_times";
            $ret = $m_forscreen_log->getWhere($fields, $where);
            $data['max_delay_times'] = round($ret[0]['max_delay_times'] / 1000  ,2) ;           //最高延时
            
            $fields = "min(`box_res_sdown_time` - `res_eup_time`) min_delay_times";
            $ret = $m_forscreen_log->getWhere($fields, $where);
            $data['min_delay_times'] = round($ret[0]['min_delay_times'] / 1000 ,2) ;           //最低延时
            
            $data['hotel_id'] = $v['hotel_id'];
            $data['static_date'] = date('Y-m-d',strtotime($start_time));
            
            $m_static_net->addInfo($data,1);
        }
        echo "OK";
    }
    /**
     * @desc 模拟投屏
     * @author zhang.yingtao
     * @since  2018-09-13
     */
    public function simulateForscreen(){
        echo "废弃" ;exit; 
        //获取目前链接netty的机顶盒
        $box_list =  file_get_contents('https://netty-push.littlehotspot.com/netty/box/connections');
        $box_list = json_decode($box_list,true);
        $box_list = $box_list['result'];
        
        //上线关闭
        /*$box_list = array();
        $box_list[] = array('box_mac'=>'00226D5845CE'); 
        $box_list[] = array('box_mac'=>'FCD5D900B57E');
        $box_list[] = array('box_mac'=>'00226D583D0E');
        $box_list[] = array('box_mac'=>'00226D2FB21D');*/ 
        foreach($box_list as $key=>$v){
            $timestamp = getMillisecond();
            $url = 'http://mobile.littlehotspot.com/Smallapp/index/recordForScreenPics'; //调用接口的平台服务地址
            $post_string = array('openid'=>'ofYZG4yZJHaV2h3lJHG5wOB9MzxE','box_mac'=>$v['box_mac'],
                                 'action'=>2,'resource_type'=>2,'mobile_brand'=>'devtools',
                                 'mobile_model'=>'devtools','forscreen_char'=>'',
                                 'imgs'=>'["forscreen/resource/15368043845967.mp4"]',
                                 'resource_id'=>$timestamp,'res_sup_time' =>$timestamp,
                                 'res_eup_time'=>$timestamp , 'resource_size'=>'1149039',
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $result = curl_exec($ch);
            
            if($result){
                $result = json_decode($result,true);
                if($result['code'] == 10000){
                    
                    $msg = array('action'=>999,'url'=>'forscreen/resource/15368043845967.mp4',
                                 'filename'=>'15368043845967.mp4','openid'=>'ofYZG4yZJHaV2h3lJHG5wOB9MzxE',
                                 'resource_type'=>2,'video_id'=>$timestamp
                    );
                    $msg = json_encode($msg);
                    $msg = str_replace('\\', '', $msg);
                    $url = 'https://netty-push.littlehotspot.com/push/box'; //调用接口的平台服务地址
                    $post_string = array('box_mac'=>$v['box_mac'],'cmd'=>'call-mini-program',
                                          'msg'=>$msg,'req_id'=>$timestamp
                    ); 
                     
                    $post_string = http_build_query($post_string);
                    
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS,$post_string);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        continue;
                        
                    }
                    curl_close($ch);
                   /*  $result = json_decode($result,true);
                    if($result['code']==10000){
                        echo "aaaa";
                    } */
                }
            }
            echo "ok";
        }
    }
    /**
     * @desc 处理小程序公开投屏资源
     */
    public function recForscreenPub(){
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $cache_key = C('SAPP_SCRREN_SHARE')."*";
        $keys = $redis->keys($cache_key);
        $m_pub = new \Admin\Model\Smallapp\PublicModel(); 
        $m_pubdetail = new \Admin\Model\Smallapp\PubdetailModel();
        foreach($keys as $k){
            $data = $redis->lgetrange($k,0,-1);
            $infos = json_decode($data[0],true);
            $k_arr = explode(':', $k);
            $map = array();
            $map['box_mac'] = $k_arr[3];
            $map['openid']  = $k_arr[4];
            $map['forscreen_id'] = $k_arr[5];
            $map['public_text']  = $infos['public_text'];
            $map['public_text']  = $infos['public_text']? $infos['public_text']:'';
            $map['forscreen_char']   = $infos['forscreen_char'] ? $infos['forscreen_char'] :'';
	        $map['res_type'] = $infos['res_type'];
            $map['res_nums'] = count($data);
            $map['status']   = 1;
            $m_pub->addInfo($map,1);
            $ret = array();
            foreach($data as $kk=>$vv){
                $vv = json_decode($vv,true);
                $ret[$kk]['forscreen_id'] = $vv['forscreen_id'];
                $ret[$kk]['resource_id']  = $vv['resource_id'];
                $ret[$kk]['res_url']      = $vv['res_url'];
	        $ret[$kk]['duration']     = $infos['duration'] ?$infos['duration'] : '0.00';
	        $ret[$kk]['resource_size']= $infos['resource_size'] ? $infos['resource_size'] :0;
	    }
            $m_pubdetail->addInfo($ret,2);
            $redis->remove($k);
        }
        echo "ok";
    
    }
    //生成好友关系
    public function smallappFriends(){
        $hour = date('H');
        //$hour =14;
        if($hour==14){
            $start_time = date('Y-m-d')." 11:00:00";
            $end_time   = date('Y-m-d')." 14:00:00";
        }else{
            $start_time = date('Y-m-d')." 17:00:00";
            $end_time   = date('Y-m-d')." 23:00:00";
        }
        $sql = "select box_mac from savor_smallapp_forscreen_record where create_time>='".$start_time."'
                and create_time<='".$end_time."' and mobile_brand !='devtools' group by box_mac";
        $forscreen_box_arr = M()->query($sql);
        $sql ="select box_mac from savor_smallapp_turntable_log where create_time>='".$start_time."'
                and create_time<='".$end_time."' group by box_mac";
        $turntable_box_arr = M()->query($sql);
        $box_list = array_merge($forscreen_box_arr,$turntable_box_arr);
        $ret = assoc_unique($box_list, 'box_mac');
        $box_list = array_keys($ret);
        $m_user = new \Admin\Model\Smallapp\UserModel();
        foreach($box_list as $v){
            $sql ="select openid from savor_smallapp_forscreen_record where box_mac='".$v."' 
                   and  create_time>='".$start_time."'
                   and create_time<='".$end_time."' and mobile_brand !='devtools' 
                   group by openid";
            //echo $sql;exit;
            $forscreen_openid_arr = M()->query($sql);  //投屏用户
            $sql ="select openid from savor_smallapp_turntable_log where box_mac='".$v."'
                   and  create_time>='".$start_time."'
                   and create_time<='".$end_time."' group by openid";
            $turntab_openid_arr = M()->query($sql);
            $sql ="select a.openid from savor_smallapp_turntable_detail a 
                   left join savor_smallapp_turntable_log b on a.activity_id = b.id
                   where b.box_mac='".$v."'
                   and  a.create_time>='".$start_time."'and a.create_time<='".$end_time."'
                   group by openid";
            $turntab_detail_openid_arr = M()->query($sql);
            $openid_list = array_merge($forscreen_openid_arr,$turntab_openid_arr,$turntab_detail_openid_arr);
            
            $nums = count($openid_list);
            if($nums<=1) continue;
            $openid_list = assoc_unique($openid_list, 'openid');
            $openid_list = array_keys($openid_list);
            if(count($openid_list)<=1) continue;
            $m_friend = new \Admin\Model\Smallapp\FriendModel();
            $f_arr = array();
            $flag=0;
            foreach($openid_list as $ov){
                $sql ="select count(id) as nums from savor_smallapp_user where openid='".$ov."' limit 1";
                $ret = M()->query($sql);
                $user_nums = $ret[0]['nums'];
                if(empty($user_nums)){
                    $sql =" insert into savor_smallapp_user(`openid`) values('".$ov."')";
                    M()->execute($sql);
                }
                foreach($openid_list as $fv){
                    if($ov!=$fv){
                        $sql ="select status from savor_smallapp_friend 
                               where openid='".$ov."' and f_openid='".$fv."'";
                        $ret = M()->query($sql);
                        if(empty($ret)){
                            
                            $f_arr[$flag]['openid'] = $ov;
                            $f_arr[$flag]['f_openid'] = $fv;
                            $f_arr[$flag]['type'] = 1;
                            $f_arr[$flag]['status'] = 1;
                            $flag++;
                            
                        }else {
                            if($ret[0]['status'] ==0){
                                $where = array();
                                $where['openid'] = $ov;
                                $where['f_openid']= $fv;
                                $m_friend->updateInfo($where, array('status'=>1));
                            }
                        }
                    }
                }
            }
            $m_friend->addInfo($f_arr,2);
        }
        echo "ok";
    }
    //清除投屏历史
    public function removeHistoryForscreen(){
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $cache_key = C('SAPP_HISTORY_SCREEN')."*";
        $keys = $redis->keys($cache_key);
        foreach($keys as $v){
            $redis->remove($v);
        }
        echo "ok";
    }
    /**
     * @desc 记录用户切换页面日志
     */
    public function recordPageViewLog(){
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $cache_key = C('SAPP_PAGEVIEW_LOG')."*";
        $keys = $redis->keys($cache_key);
        $m_pageview_log = new \Admin\Model\Smallapp\PageviewLogModel();
        foreach($keys as $k){
            $data = $redis->lgetrange($k,0,-1);
            $map = array();
	    foreach($data as $ks=> $v){
                $infos = json_decode($v,true);
		$map[$ks]['openid'] = $infos['openid'];
                $map[$ks]['page_id']= $infos['page_id'];
                $map[$ks]['create_time'] = $infos['create_time'];
		$redis->lpop($k);
	    }
	    $tmp = $redis->lgetrange($k,0,-1);
            if(empty($tmp)) $redis->remove($k);
            $m_pageview_log->addInfo($map,2);
	 }
        echo "OK";
    }
    /**
     * @desc 按照盒子每小时互动生成网络情况
     */
    public function staticBoxNet(){
         
        $strtime    =  strtotime('-1 hours');  //*********上线改为一小时
        $start_time =  date('Y-m-d H:00:00',$strtime) ;
        $end_time   =  date('Y-m-d H:59:59',$strtime) ;
    
    
    
        $m_forscreen_log = new \Admin\Model\ForscreenRecordModel();
        $fields = 'box.id box_id,box.mac box_mac';
        $where = array();
        $where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
        $where['a.box_res_sdown_time'] = array('neq', 0);
        $where['a.box_res_edown_time'] = array('neq', 0);
        $where['hotel.flag'] = 0;
        $where['hotel.state']= 1;
        $where['box.flag']   = 0;
        $where['box.state']  = 1;
        $where["_string"] = "  a.box_res_edown_time >a.box_res_sdown_time";
    
        $box_list = $m_forscreen_log->getWhere($fields, $where, $order='', $limit='', $group="box.mac");
        //print_r($box_list);exit;
        $m_static_boxnet =  new \Admin\Model\Smallapp\StaticBoxnetModel();
        foreach($box_list as $key=>$v){
            $where = $data =  array();
            $where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
            $where['box.id'] = $v['box_id'];
            $where['a.box_res_sdown_time'] = array('neq', 0);
            $where['a.box_res_edown_time'] = array('neq', 0);
            $where['hotel.flag'] = 0;
            $where['hotel.state']= 1;
            $where['box.flag']   = 0;
            $where['box.state']  = 1;
            $where["_string"] = "  a.box_res_edown_time >a.box_res_sdown_time";
    
            $data['box_donw_nums'] = $m_forscreen_log->countWhere($where);         //机顶盒总下载次数
    
            $fields = "sum(`resource_size`) as all_res_size,sum(`box_res_edown_time` - `box_res_sdown_time`) as down_times,
                       max(`resource_size` / (`box_res_edown_time`-`box_res_sdown_time`)) as max_down_speed,
                       min(`resource_size` / (`box_res_edown_time`-`box_res_sdown_time`)) as min_down_speed";
            $ret = $m_forscreen_log->getWhere($fields, $where);                    //机顶盒总下载大小
    
            $data['res_size'] = $ret[0]['all_res_size'] ?  $ret[0]['all_res_size'] :0;
    
            $down_times = $ret[0]['down_times'];
    
            $data['avg_down_speed'] = round($data['res_size'] / ($down_times/1000),2) ;       //平均下载速度
            $data['max_down_speed'] = round($ret[0]['max_down_speed']*1000 , 2) ;                //最快下载速度
            $data['min_down_speed'] = round($ret[0]['min_down_speed']*1000 , 2);                //最慢下载速度
            //print_r($data);exit;
    
    
            $where = array();
            $where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
            $where['box.id'] = $v['box_id'];
            $where['a.res_eup_time'] = array('neq',0);
            $where['a.box_res_sdown_time'] = array('neq',0);
            $where['hotel.flag'] = 0;
            $where['hotel.state']= 1;
            $where['box.flag']   = 0;
            $where['box.state']  = 1;
            $where["_string"] = "  a.box_res_sdown_time>a.res_eup_time and a.box_res_edown_time >a.box_res_sdown_time";
            $data['order_times'] = $m_forscreen_log->countWhere($where);            //总指令次数
    
            $fields = "sum(`box_res_sdown_time` - `res_eup_time`) as all_delay_times,
                       max(`box_res_sdown_time` - `res_eup_time`) max_delay_times,
                       min(`box_res_sdown_time` - `res_eup_time`) min_delay_times";
            $ret = $m_forscreen_log->getWhere($fields, $where);
            $all_delay_times = $ret[0]['all_delay_times'];
    
            $data['avg_delay_time'] = round(($all_delay_times / 1000) / $data['order_times'] ,2) ;  //平均指令延时
            $data['max_delay_times'] = round($ret[0]['max_delay_times'] / 1000  ,2) ;               //最高延时
            $data['min_delay_times'] = round($ret[0]['min_delay_times'] / 1000 ,2) ;                //最低延时
    
            $data['box_mac'] = $v['box_mac'];
            $data['static_date'] = date('YmdH',strtotime($start_time));
            $m_static_boxnet->addInfo($data,1);
        }
        echo "OK";
    }
    //随机每天增加发现页的点赞数
    public function recCollectCount(){
        $m_public = new \Admin\Model\Smallapp\PublicModel();
        $m_collect_count = new \Admin\Model\Smallapp\CollectCountModel();
        
        $fields = "forscreen_id";
        $where['status'] = 2;
        $limit  = '0,1000';
        $list = $m_public->getWhere($fields, $where,'id desc', $limit);
        
        foreach($list as $key=>$v){
            $where = array();
            $where['res_id'] = $v['forscreen_id'];
            $nums = $m_collect_count->countNum($where);
            if($nums){
                $rand_nums = rand(1, 10);
                $m_collect_count->where($where)->setInc('nums',$rand_nums);
            }else {
                $data = array();
                $data['res_id'] = $v['forscreen_id'];
                $data['type']   = 2;
                $rand_nums = rand(1, 10);
                $data['nums']   = $rand_nums;
		$m_collect_count->addInfo($data);
            }
        }
        echo 'ok';
    }
    /**
     * @desc 把昨天小游戏的数据写入投屏日志中
     */
    public function h5gameToForscreenLog(){
        $yesterday = strtotime('-1 day');
        $yesterday_start = date('Y-m-d 00:00:00',$yesterday);
        $yesterday_end   = date('Y-m-d 23:59:59',$yesterday);
        
        $where = array();
        $where['b.is_start'] = 1;
        $where['a.create_time'] = array(array('EGT',$yesterday_start),array('ELT',$yesterday_end));
        
        $m_game_tree = new \Admin\Model\Smallapp\GameClimbtreeModel();
        $list = $m_game_tree->alias('a')
        ->join('savor_smallapp_game_interact b on a.activity_id=b.id','left')
        ->field('b.box_mac,a.openid,a.create_time')
        ->where($where)
        ->select();
        
        $m_forscreen_log = new \Admin\Model\Smallapp\ForscreenRecordModel(); 
        $flag = 0;
        foreach($list as $key=>$v){
            $data = array();
            $data['openid'] = $v['openid'];
            $data['box_mac']= $v['box_mac'];
            $data['action'] = 101;
            $data['small_app_id'] = 11;
            $data['imgs'] = '[]';
            $data['forscreen_id'] = 0;
            $data['resource_id'] = 0;
            $data['resource_size'] = 0;
            $data['create_time'] = $v['create_time'];
            $ret =$m_forscreen_log->addInfo($data,1);
            if($ret){
                $flag++;
            }
        }
        echo $flag;
    }

    public function forscreenAds(){
        $m_forscreen = new \Admin\Model\ForscreenAdsModel();
        $res_ads = $m_forscreen->getDataList('id,type',array('state'=>1),'id asc',0,1);
        if($res_ads['total']>0){
            //state 状态:0未执行,1执行中,2可用,3不可用,4已删除
            $forscreen_ads_id = $res_ads['list'][0]['id'];
            $type = $res_ads['list'][0]['type'];//1版位2酒楼
            if($type==1){

            }elseif($type==2){
                $m_forscreen_hotel = new \Admin\Model\ForscreenAdsHotelModel();
                $m_forscreendasbox = new \Admin\Model\ForscreenAdsBoxModel();
                $where = array('forscreen_ads_id'=>$forscreen_ads_id);
                $res_hotel = $m_forscreen_hotel->getDataList('hotel_id',$where,'id asc');

                $redis = SavorRedis::getInstance();
                $redis->select(12);
                $cache_key_pre = C('SMALLAPP_FORSCREEN_ADS');

                foreach ($res_hotel as $v){
                    $hotel_id = $v['hotel_id'];
                    $m_box = new \Admin\Model\BoxModel();
                    $field = 'b.id as box_id';
                    $where = "h.id=$hotel_id and b.state=1 and b.flag=0 and h.state=1 and h.flag=0";
                    $res_mac = $m_box->isHaveMac($field,$where);
                    $data_box = array();
                    foreach ($res_mac as $bv){
                        $box_id = $bv['box_id'];
                        $redis->remove($cache_key_pre.$box_id);
                        $data_box[] = array('forscreen_ads_id'=>$forscreen_ads_id,'box_id'=>$box_id);
                    }
                    $nowtime = date('Y-m-d H:i:s');
                    if(!empty($data_box)){
                        $m_forscreendasbox->addAll($data_box);
                        echo "$nowtime forscreen_ads_id:".$forscreen_ads_id.' hotel_id:'.$hotel_id.' execute finish'."\r\n";
                    }else{
                        echo "$nowtime forscreen_ads_id:".$forscreen_ads_id.' hotel_id:'.$hotel_id.' execute 0 finish'."\r\n";
                    }
                }
                $condition = array('id'=>$forscreen_ads_id);
                $data = array('state'=>2);
                $m_forscreen->updateData($condition,$data);
                $nowtime = date('Y-m-d H:i:s');
                echo "$nowtime $forscreen_ads_id forscreen ads finish \r\n";
            }else{
                $nowtime = date('Y-m-d H:i:s');
                echo "$nowtime $forscreen_ads_id forscreen ads type error \r\n";
            }
        }else{
            $nowtime = date('Y-m-d H:i:s');
            echo "$nowtime forscreen ads over \r\n";
        }

    }
    /**
     * @desc   删除前天以前（包括前天不包括昨天）的模拟投屏数据   
     *         昨天的模拟投屏数据会生成一个酒楼的网络状况统计
     * @author zhang.yingtao
     * @since  20190322
     */
    public function removeSimuForscreenData(){
        $date_time = date('Y-m-d 23:59:59',strtotime('-2 days')); //**************上线用这个
        
        $where ="WHERE mobile_brand='devtools' and imgs='[\"forscreen/resource/15368043845967.mp4\"]' 
               and openid='ofYZG4yZJHaV2h3lJHG5wOB9MzxE' and create_time<'".$date_time."'";
        
        $sql ="select * from `savor_smallapp_forscreen_record` ".$where;
        
        $data = M()->query($sql);
        $count = count($data);
        $map =  array();
        $fk = 1 ;
        //print_r($data);exit;
        $m_simu_forscreen_log = new \Admin\Model\Smallapp\SimuForscreenLogModel();
        foreach($data as $key=>$v){
            
            if($fk % 100 ==0 ){
                //print_r($map);exit;      
                $m_simu_forscreen_log->addInfo($map,2);
                $map = array();
            }else {
                $map[] =  $v;
                
            }
            $fk++;
        }
        if(!empty($map)){
            $m_simu_forscreen_log->addInfo($map,2);
        }

        $sql ="delete FROM `savor_smallapp_forscreen_record` ".$where." limit ".$count;
               
        $rt = M()->execute($sql);
        if($rt){
            echo date('Y-m-d H:i:s').'OK';
        }else {
            echo date('Y-m-d H:i:s').'ERROR';
        }
    }

    public function operationRedpacket($id=0){
        $m_redpacketoperation = new \Admin\Model\Smallapp\RedpacketoperationModel();
        if($id){
            $data = $m_redpacketoperation->getInfo(array('id'=>$id));
            $res_list = array($data);
        }else{
            $orderby = 'id asc';
            $where = array('status'=>1);
            $res_list = $m_redpacketoperation->getDataList('*',$where,$orderby);
        }
        $all_senders = C('REDPACKET_SENDERS');
        $op_userid = C('REDPACKET_OPERATIONERID');
        unset($all_senders[0]);
        $m_redpacket = new \Admin\Model\Smallapp\RedpacketModel();
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $redis  =  \Common\Lib\SavorRedis::getInstance();

        $nowdate = date('Y-m-d');
        $nowtime = date('H:i');
        $nowdatetime = date("Y-m-d H:i:s");
        foreach ($res_list as $v){
            switch ($v['type']){//类型 1立即发送,2单次定时,3多次定时
                case 1:
                    $is_send = 1;
                    break;
                case 2:
                    $optime = date('H:i',strtotime($v['timing']));
                    if($v['start_date']==$nowdate && $nowtime==$optime){
                        $is_send = 1;
                    }else{
                        $is_send = 0;
                    }
                    break;
                case 3:
                    $optime = date('H:i',strtotime($v['timing']));
                    if($nowdate>=$v['start_date'] && $nowdate<=$v['end_date'] && $nowtime==$optime){
                        $is_send = 1;
                    }else{
                        $is_send = 0;
                    }
                    break;
                default:
                    $is_send = 0;
            }
            if($is_send==1){
                $redpacket = array('user_id'=>$op_userid,'total_fee'=>$v['total_fee'],'amount'=>$v['amount'],'surname'=>'小热点',
                    'sex'=>1,'bless_id'=>1,'scope'=>$v['scope'],'mac'=>$v['mac'],'pay_fee'=>$v['total_fee'],
                    'pay_time'=>date('Y-m-d H:i:s'),'pay_type'=>10,'status'=>4);
                $trade_no = $m_redpacket->addData($redpacket);
                if($trade_no){
                    if($v['type']==1 || $v['type']==2){
                        $m_redpacketoperation->updateData(array('id'=>$v['id']),array('status'=>0));
                    }else{
                        if($nowdate==$v['end_date'] && $nowtime==$optime){
                            $m_redpacketoperation->updateData(array('id'=>$v['id']),array('status'=>0));
                        }
                    }
                    //根据红包总金额和人数进行分配红包
                    $money = $redpacket['total_fee'];
                    $num = $redpacket['amount'];
                    $all_money = bonus_random($money,$num,0.3,$money);
                    $redis->select(5);
                    $key = C('SAPP_REDPACKET').$trade_no.':bonus';
                    $all_moneys = array('unused'=>$all_money,'used'=>array());
                    $redis->set($key,json_encode($all_moneys),86400);
                    //end

                    //推送红包小程序码到电视
                    $http_host = 'https://mobile.littlehotspot.com';

                    $box_mac = $redpacket['mac'];
                    $qrinfo =  $trade_no.'_'.$box_mac;
                    $mpcode = $http_host.'/h5/qrcode/mpQrcode?qrinfo='.$qrinfo;

                    if($v['sender']){
                        $user_info = $all_senders[$v['sender']];
                    }else{
                        shuffle($all_senders);
                        $user_info = $all_senders[0];//随机
                    }
                    $user_info['avatarUrl'] = 'http://oss.littlehotspot.com/WeChat/MiniProgram/LaunchScreen/source/images/avatar/'.$user_info['id'].'.jpg';

                    $where_user = array('id'=>$op_userid);
                    $m_user->updateInfo($where_user,array('nickName'=>$user_info['nickName'],'avatarUrl'=>$user_info['avatarUrl']));

                    $message = array('action'=>121,'nickName'=>$user_info['nickName'],
                        'avatarUrl'=>$user_info['avatarUrl'],'codeUrl'=>$mpcode);
                    $m_netty->pushBox($redpacket['mac'],json_encode($message));

                    //发送范围 1全网餐厅电视,2当前餐厅所有电视,3当前包间电视
                    $scope = $redpacket['scope'];
                    if(in_array($scope,array(1,2))) {
                        //发全网红包
                        $all_box = $m_netty->getPushBox(2, $box_mac);
                        if (!empty($all_box)) {
                            foreach ($all_box as $v) {
                                $qrinfo = $trade_no.'_'.$v;
                                $mpcode = $http_host . '/h5/qrcode/mpQrcode?qrinfo=' . $qrinfo;
                                $message = array('action' => 121, 'nickName' => $user_info['nickName'],
                                    'avatarUrl' => $user_info['avatarUrl'], 'codeUrl' => $mpcode);
                                $m_netty->pushBox($v, json_encode($message));
                            }
                        }
                        if ($scope == 1) {
                            $key = C('SAPP_REDPACKET') . 'smallprogramcode';
                            $res_data = array('order_id' => $trade_no, 'box_list' => $all_box,
                                'nickName' => $user_info['nickName'], 'avatarUrl' => $user_info['avatarUrl']);
                            $redis->set($key, json_encode($res_data));
                        }
                    }
                    //end

//                    $nowdatetime = date('Y-m-d H:i:s');
//                    $log_content = $nowdatetime.'[redpacket_id]'.$trade_no."\n";
//                    $log_fil1 `e_name = '/application/logs/smallapp/'.'operationbonus_'.date("Ymd").".log";
//                    @file_put_contents($log_file_name, $log_content, FILE_APPEND);

                }

            }
        }
//        $nowdatetime = date('Y-m-d H:i:s');
//        $log_content = $nowdatetime.'[redpacket_list]'.json_encode($res_list)."\n";
//        $log_file_name = '/application/logs/smallapp/'.'operationbonus_'.date("Ymd").".log";
//        @file_put_contents($log_file_name, $log_content, FILE_APPEND);
    }

    public function userintegral(){
        $m_usersignin = new \Admin\Model\Smallapp\UserSigninModel();
        $m_usersignin->userintegral();
    }

    public function forscreenPublicnums(){
        $m_public = new \Admin\Model\Smallapp\PublicModel();
        $m_public->cronforscreenPublicnums();
    }

    public function sendredpacket(){
        $operation_uid = 42996;
        $m_order = new \Admin\Model\Smallapp\RedpacketModel();
        $where = array('status'=>array('in','4,6'),'scope'=>1);
        $where['user_id'] = array('neq',$operation_uid);
        $where['add_time'] = array('egt','2019-08-29 15:00:00');
        $res_order = $m_order->getDataList('id,user_id,pay_fee,add_time',$where,'id asc');
        $nowdtime = date('Y-m-d H:i:s');
        if(empty($res_order)){
            echo $nowdtime.' no send redpacket'."\r\n";
            exit;
        }
        //大于15分钟未发完的红包
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(5);
        $sapp_redpacket_key = C('SAPP_REDPACKET');

        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_redpacketreceive = new \Admin\Model\Smallapp\RedpacketReceiveModel();
        $nowtime = time();
        foreach ($res_order as $v){
            $trade_no = $v['id'];
            $add_time = strtotime($v['add_time']);
            if($nowtime-$add_time>=900){
                $key = $sapp_redpacket_key.$trade_no.':bonus';
                $res_money = $redis->get($key);
                if($res_money){
                    $all_money = json_decode($res_money,true);
                    if(!empty($all_money['unused']) && count($all_money['unused'])>0){
                        $m_order->updateData(array('id'=>$trade_no),array('status'=>5,'operate_type'=>1));
                        $res_receive = $m_redpacketreceive->getDataList('user_id',array('redpacket_id'=>$trade_no),'id desc');
                        $has_getuser = array();
                        foreach ($res_receive as $rv){
                            $has_getuser[] = $rv['user_id'];
                        }
                        $unused_money = $all_money['unused'];
                        $unused_num = count($unused_money);
                        $size = $unused_num*2;
                        $limit_arr = array();
                        $tmp_start = 0;
                        for ($i=0;$i<10;$i++){
                            $tmp_start = $size*10+500+$tmp_start;
                            $limit_arr[]="$tmp_start,$size";
                        }
                        shuffle($limit_arr);
                        $limit = $limit_arr[0];
                        $where_user = array('small_app_id'=>1,'is_wx_auth'=>3);
                        $res_user = $m_user->getWhere('id',$where_user,'id asc',$limit);
                        foreach ($res_user as $uv){
                            if(empty($unused_money)){
                                break;
                            }
                            $money = array_shift($unused_money);
                            $add_data = array('redpacket_id'=>$trade_no,'user_id'=>$uv['id'],'money'=>$money,'barrage'=>'happy birthday',
                                'status'=>1,'receive_time'=>date('Y-m-d H:i:s'),'operate_type'=>1);
                            $m_redpacketreceive->add($add_data);
                        }
                        echo $nowdtime.' redpacket_id: '.$trade_no.' send finish'."\r\n";
                    }
                }
            }

        }
    }


    public function wxpush(){
        $push_key = C('SAPP_SELECTCONTENT_PUSH').':ontv';
        $push_key = C('SAPP_SELECTCONTENT_PUSH').':playtv';

        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(5);
        $smallapp_config = C('SMALLAPP_CONFIG');

        $key_token = $smallapp_config['cache_key'];
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $token = $redis->get($key_token);
        if(empty($token)){
            $appid = $smallapp_config['appid'];
            $appsecret = $smallapp_config['appsecret'];
            $url = $this->url_access_token."?grant_type=client_credential&appid=$appid&secret=$appsecret";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $re = curl_exec($ch);

            curl_close($ch);
            $result = json_decode($re,true);
            if(isset($result['access_token'])){
                $redis->set($key_token,$result['access_token'],3600);
                $token = $result['access_token'];
            }
        }

        $formid = '6f93059ff6a84eb68e32708f1fefd162';
        $openid = 'ofYZG4yZJHaV2h3lJHG5wOB9MzxE';

        $tempalte_id = 'rW3_5Q4EXeBis-bQmxiv-9E2R1UDoIFEnG8sNchw9Tk';
        $date_time = date('Y-m-d H:i:s');
        $data=array(
            'keyword1'  => array('value'=>20),
            'keyword2'  => array('value'=>'小热点'),
            'keyword3'  => array('value'=>$date_time),
            'keyword4'  => array('value'=>'详细信息请点击查看详情'),
        );
        $template = array(
            'touser' => $openid,
            'template_id' => $tempalte_id,
            'page' => 'pages/demand/index',
            'form_id'=>$formid,
            'data' => $data
        );
        $curl = new Curl();
        $access_token = '24_ox5XvSR_TQ4prB40k1UcTg1KH5mxQpYFFgpEa38oZfvdn9CyCRPeXgx4JGzT11AO0POGyO71q3YJklbE8x1f321UMfcslQr0fvnE6FDKx-x4g6EMBH4zxqNbpFyGZLeUX8CDMqzNygI_a6oZEOThAFAYIN';
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=$access_token";
        $template =  json_encode($template);
        $res_data = array();
        $curl::post($url,$template,$res_data);
        print_r($res_data);
        exit;


    }
    /**
     * @desc 机顶盒极简版更新投屏日志上报
     */
    public function updateSimpleUpload(){
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $cache_key = C('SAPP_SIMPLE_UPLOAD_RESOUCE')."*";
        $keys = $redis->keys($cache_key);
        $m_forscreen_record = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $m_forscreen_invalid_record      = new \Admin\Model\Smallapp\ForscreeninvalidrecordModel();
        foreach($keys as $k){
            
            $rets = $redis->lgetrange($k,0,-1);
            foreach($rets as $v){
                $map = $data = array();
                $simple_resource = json_decode($v,true);
                $map['forscreen_id'] = intval($simple_resource['forscreen_id']);
                $map['resource_id']  = intval($simple_resource['resource_id']);
                $map['box_mac']      = $simple_resource['box_mac'];
                $data['imgs']        = $simple_resource['imgs'];
                $data['update_time'] = date('Y-m-d H:i:s');
                $f_info = $m_forscreen_record->where($map)->select();
                if($f_info){
                    $ret = $m_forscreen_record->updateInfo($map, $data);
                    if($ret) $redis->lpop($k);
                }else {
                    $f_i_info = $m_forscreen_invalid_record->where($map)->select();
                    
                    if($f_i_info){
                        $ret = $m_forscreen_invalid_record->updateData($map, $data);
                        if($ret) $redis->lpop($k);
                        
                    }
                }
                
            }
            $list = $redis->lgetrange($k,0,-1);
            if(empty($list)){
                $redis->remove($k);
            }
            
        }
        echo   date('Y-m-d H:i:s'). "数据处理完成";
    }
    
}
