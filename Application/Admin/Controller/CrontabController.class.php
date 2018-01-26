<?php
namespace Admin\Controller;
use Common\Lib\UmengApi;
use Think\Controller;
use Common\Lib\SimFile;
use \Common\Lib\SavorRedis;
use \Common\Lib\Aliyun;
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
        //获取所有酒楼
        $m_hotel = new \Admin\Model\HotelModel();
        //虚拟小平台也拿到
        $where = " a.id not in(7,53)  and a.state=1 and a.flag =0 and a.hotel_box_type in(2,3) and b.mac_addr !=''";

        $max_hour = 720;
        $hotel_list = $m_hotel->getHotelLists($where,'','','a.id，b.mac_addr');

        //$hotel_list = array_slice($hotel_list,0, 5);

        $hotel_unusual = new \Admin\Model\HotelUnusualModel();
        $m_heart_log = new \Admin\Model\HeartLogModel();
        $m_box = new \Admin\Model\BoxModel();
        //正常酒楼 、异常酒楼
        $start_time = date('Y-m-d H:i:s',strtotime('-72 hours'));
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
            $where .=" 1 and room.hotel_id=".$hotel_id.' and a.flag=0  and  room.flag=0 and room.state =1';
            $box_list = $m_box->getListInfo( 'a.id, a.mac,a.state',$where);
            $data['box_num'] = count($box_list);
            foreach($box_list as $ks=>$vs){
                $where = '';
                $where .=" 1 and hotel_id=".$hotel_id." and type=2 and box_mac='".$vs['mac']."'";
                $where .="  and last_heart_time>='".$start_time."'";

                $rets  = $m_heart_log->getOnlineHotel($where,'hotel_id');
                if ( $vs['state'] == 1) {
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

                    }else {

                    }
                } else {
                    $box_not_normal_num +=1;
                    if(empty($rets)){
//获取失聪时长
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
                    } else {

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
                .$data['not_normal_smallplat_num'].'个小平台失联超过72小时,'
                .$data['not_normal_box_num'].'个机顶盒失联超过72小时';
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
                        $apk_info = $m_version_upgrade->getLastOneByDevice($field, $apk_device_type, $hid);
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
            return array();
        }

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
            $pro_arr = $adsModel->getproInfo($menuid);
            $redis->set($procache_key , json_encode($pro_arr), 120);
            $pro_arr = $this->changeadvList($pro_arr,1);

        }
        $ads_arr = $redis->get($adscache_key);
        if($ads_arr) {
            $ads_arr = json_decode($ads_arr, true);
            $ads_arr = $this->changeadvList($ads_arr,2);
        } else {
            $ads_arr = $adsModel->getadsInfo($menuid);
            $redis->set($adscache_key , json_encode($ads_arr), 120);
            $ads_arr = $this->changeadvList($ads_arr,2);
        }

        $adv_arr = $adsModel->getupanadvInfo($hotel_id, $menuid);
        $adv_arr = $this->changupaneadvList($adv_arr,1);
        $result['play_list'] = array_merge($pro_arr,
            $ads_arr,$adv_arr);
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
                'switch_time'   => ($vol['system_switch_time']<0)?(empty($rv['switch_time'])?$vol_default['system_switch_time']:$rv['switch_time']):$vol['system_switch_time'],
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
        $upgrade_info = $m_version_upgrade->getLastOneByDevice($field, $device_type, $hotel_id);
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
        $where = " 1=1 and state = 1 and is_remove=0 ";
        $where.=" AND end_date <'$now_date'";
        $pad_arr = $pubadsModel->getWhere($where, $field);
        var_export($pad_arr);

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
}