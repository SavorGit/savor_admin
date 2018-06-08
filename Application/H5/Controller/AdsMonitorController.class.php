<?php 
/**
 *@author zhang.yingtao
 *@desc 广告到达H5页面
 *@since 2018-05-18
 *
 */
namespace H5\Controller;
use Common\Lib\SavorRedis;
use Think\Controller;

class AdsMonitorController extends Controller {
    
    /**
     * @desc 广告到达情况
     */
    public function index(){
        
        $area_id = I('area_id',0,'intval');
        $hotel_name = I('hotel_name','','trim');
        $report_time = I('report_time','','trim');
        if(empty($report_time)){
            $report_time = date('Y-m-d',strtotime('-1 day'));  //广告到达时间
        }
        
        $m_box_media_arrive_summary = new \Admin\Model\Statisticses\BoxMediaArriveSummaryModel();
         
        $info = $m_box_media_arrive_summary->getOne('summary_data', array('date'=>$report_time));
        $info = json_decode($info['summary_data'],true);
        extract($info);  
        
        $arrive_date = date('Y-m-d',strtotime($report_time)+86400);   //统计时间
        $yesterday_end_time = date('Y-m-d 23:59:59',strtotime($report_time));
        $yesterday_start_time = date('Y-m-d 00:00:00',strtotime($report_time));
        
         //广告到达时间
        /* $arrive_date = date('Ymd',strtotime('-1 day'));
        
        //网络机顶盒数
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        $m_box = new \Admin\Model\BoxModel();
        $where = array();
        $where['hotel.state'] = 1;
        $where['hotel.flag']  = 0;
        $where['box.state']   = 1;
        $where['box.flag']   = 0;
        $where['hotel.hotel_box_type'] = array('in',$hotel_box_type_str);
        $net_box_nums = $m_box->countNums($where); 
        //在投广告数
        $m_pub_ads = new \Admin\Model\PubAdsModel();
        $where = array();
        $now_date = date('Y-m-d H:i:s');
        $yesterday_end_time = date('Y-m-d 23:59:59',strtotime('-1 day'));
        $yesterday_start_time = date('Y-m-d 00:00:00',strtotime('-1 day'));
        $where['a.start_date'] = array('lt',$yesterday_end_time);
        $where['a.end_date']   = array('gt',$yesterday_start_time);
        $where['a.state']      = array('neq',2);
        //$where['a.id']         = array('not in','115,116,117,118,119,120,121,122');
        $online_ads_nums = $m_pub_ads->countNums($where);
        
        //北上广深数据统计
        $m_area = new \Admin\Model\AreaModel();
        $area_list = $m_area->getHotelAreaList();
        $all_ads_arrive_rate = 0;
        $all_net_box_nums = 0;
        $jisuan_arrive_box_num = 0;
        $jisuan_all_area_pub_box_num = 0;
        
        foreach($area_list as $key=>$v){
            
           
            //在投广告个数
            $sql ="SELECT count(distinct pubbox.box_id) boxnum,pubbox.`pub_ads_id`  
                   FROM savor_pub_ads_box pubbox 
                   LEFT JOIN savor_pub_ads ads ON pubbox.`pub_ads_id`=ads.`id` 
                   LEFT JOIN savor_box box ON pubbox.`box_id`=box.`id` 
                   LEFT JOIN savor_room room ON box.`room_id`=room.`id` 
                   LEFT JOIN savor_hotel hotel ON hotel.`id`=room.`hotel_id` 
                   LEFT JOIN savor_area_info AS areainfo ON hotel.`area_id`=areainfo.`id` 
                   WHERE ads.start_date<'".$yesterday_end_time."' AND ads.`end_date`>'".$yesterday_start_time."' AND ads.`state`!=2 
                   AND hotel.`area_id`=".$v['id']." and hotel.state=1 and hotel.flag=0 and box.state=1 
                   and box.flag=0  GROUP by pubbox.`pub_ads_id` ";
            $tmp1 = M()->query($sql);
            
            //在投广告个数
            $sql ="SELECT count(distinct pubbox.box_id) boxnum,pubbox.`pub_ads_id`  
                   FROM savor_pub_ads_box_history pubbox 
                   LEFT JOIN savor_pub_ads ads ON pubbox.`pub_ads_id`=ads.`id` 
                   LEFT JOIN savor_box box ON pubbox.`box_id`=box.`id` 
                   LEFT JOIN savor_room room ON box.`room_id`=room.`id` 
                   LEFT JOIN savor_hotel hotel ON hotel.`id`=room.`hotel_id` 
                   LEFT JOIN savor_area_info AS areainfo ON hotel.`area_id`=areainfo.`id` 
                   WHERE ads.start_date<'".$yesterday_end_time."' AND ads.`end_date`>'".$yesterday_start_time."' AND ads.`state`!=2 
                   AND hotel.`area_id`=".$v['id']." and hotel.state=1 and hotel.flag=0 and box.state=1 
                   and box.flag=0  GROUP by pubbox.`pub_ads_id` ";
            $tmp2 = M()->query($sql);
            if(!empty($tmp1)){
                $tmp = array_merge($tmp1,$tmp2);
            }else {
                $tmp = $tmp2;
            }
            $all_area_pub_box_num = 0;
            foreach($tmp as $kk=>$vv){
                $all_area_pub_box_num +=$vv['boxnum'];
            }
            $area_online_ads_nums = count($tmp);
            $area_list[$key]['area_online_ads_nums'] = $area_online_ads_nums;
            //网络机顶盒数
            $where = array();
            $where['hotel.state']   = 1;
            $where['hotel.flag']    = 0;
            $where['box.state']     = 1;
            $where['box.flag']      = 0;
            $where['hotel.area_id'] = $v['id'];
            $where['hotel.id'] = array('not in',array(7,53,791,747,508));
            $where['hotel.hotel_box_type'] = array('in',$hotel_box_type_str);
            
            $area_net_box_nums = $m_box->countNums($where);
            $all_net_box_nums +=$area_net_box_nums;
            $area_list[$key]['area_net_box_nums'] = $area_net_box_nums;
            //广告到达率
            $m_statistics_box_media_arrive = new \Admin\Model\Statisticses\BoxMediaArriveModel();
            $where = array();
            $where['area_id'] = $v['id'];
            $where['media_id'] = array('neq','-10000');
            $arrive_box_num = $m_statistics_box_media_arrive->getCount($where);
            
           
            $jisuan_arrive_box_num +=$arrive_box_num; 
            $jisuan_all_area_pub_box_num += $all_area_pub_box_num;
            $ads_arrive_rate = sprintf("%1.2f",$arrive_box_num / $all_area_pub_box_num *100) ; 
            
            $all_ads_arrive_rate +=$ads_arrive_rate;
            $area_list[$key]['ads_arrive_rate'] = $ads_arrive_rate;
        }
        //$counts = count($area_list);
        //$all_ads_arrive_rate = sprintf("%1.2f",$all_ads_arrive_rate/$counts);
        $all_ads_arrive_rate = sprintf("%1.2f",$jisuan_arrive_box_num/$jisuan_all_area_pub_box_num*100);
          */
        
        //酒楼明细
        $page = I('page',0,'intval') ? I('page',0,'intval') : 1;
        $pageSize = 15;
        $start = ($page-1) * $pageSize;
        
        $m_box_media_arrive_ratio_history = new \Admin\Model\Statisticses\BoxMediaArriveRatioHistroyModel(); 
        
        $fields = 'a.hotel_id,hotel.name hotel_name,a.arrive_ratio,ext.mac_addr';
        $where  = array();
        $where['a.media_id'] = '-10000';
        $where['a.hotel_id'] = array('gt',0);
        $where['hotel.state'] = 1;
        $where['hotel.flag']  = 0;
        $where['hotel.id'] = array('not in',array(7,53,791,747,508));
        $order = 'a.arrive_ratio asc';
        
        if($area_id){
            $where['a.area_id'] = $area_id;
        }
        if($hotel_name){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        $where['statistics_time'] = $arrive_date.' 00:00:00';
        
        $list = $m_box_media_arrive_ratio_history->getList($fields, $where, $order, $start, $pageSize);
        
        $total_nums = $m_box_media_arrive_ratio_history->getCount($where);
        //获取再投的广告列表
        $m_pub_ads = new \Admin\Model\PubAdsModel();
        
        $where = array();
        $now_date = date('Y-m-d H:i:s');
        $where['pads.start_date'] = array('lt',$yesterday_end_time);
        $where['pads.end_date']   = array('gt',$yesterday_start_time);
        $where['pads.state']      = array('neq',2);
        //$where['pads.id']         = array('not in','115,116,117,118,119,120,121,122');
        $fields = 'pads.id as pub_ads_id,med.id as media_id ,ads.name';
        $order = 'pads.create_time asc';
        $pub_ads_list = $m_pub_ads->getPubAdsList($fields, $where,$order);
        
        foreach($list as $key=>$v){
            $ads_list = array();
            foreach($pub_ads_list as $kk=>$vv){
                
                $where = array();
                $where['media_id'] = $vv['media_id'];
                $where['hotel_id'] = $v['hotel_id'];
                $where['media_type'] = 'ads';
                
                $where['statistics_time'] = $arrive_date.' 00:00:00';
                
                $tmp = $m_box_media_arrive_ratio_history->getOne('arrive_ratio',$where);
                if(!empty($tmp)){
                    $rates = $tmp['arrive_ratio']*100;
                    $rates .='%';
                }else {
                    $sql ="select pabox.id from savor_pub_ads_box pabox 
                           left join savor_pub_ads pads on pabox.pub_ads_id=pads.id
                           left join savor_box box on pabox.box_id=box.id
                           left join savor_room room on box.room_id=room.id
                           left join savor_hotel hotel on room.hotel_id=hotel.id
                           where pabox.pub_ads_id=".$vv['pub_ads_id']." and hotel.id=".$v['hotel_id'];
                    $rets1 = M()->query($sql);
                    
                    $sql ="select pabox.id from savor_pub_ads_box_history pabox
                           left join savor_pub_ads pads on pabox.pub_ads_id=pads.id
                           left join savor_box box on pabox.box_id=box.id
                           left join savor_room room on box.room_id=room.id
                           left join savor_hotel hotel on room.hotel_id=hotel.id
                           where pabox.pub_ads_id=".$vv['pub_ads_id']." and hotel.id=".$v['hotel_id'];
                    $rets2 = M()->query($sql);
                    
                    if(!empty($rets1)){
                        $rets = array_merge($rets1,$rets2);
                    }else {
                        $rets = $rets2;
                    }
                    
                    if(!empty($rets)){
                        $rates = '0%';
                    }else {
                        $rates = '-';
                    }
                    
                    
                }
                
                $ads_list[$kk] = $rates;
            }
            $list[$key]['ads_rate_list'] = $ads_list;
            
            $all_rates = $v['arrive_ratio']*100;
            $all_rates = $all_rates."%";
            $list[$key]['arrive_ratio'] = $all_rates;
            if(!empty($v['mac_addr'])){
                if($v['mac_addr']=='000000000000'){
                    $list[$key]['small_plat_type'] = '虚拟';
                }else {
                    $list[$key]['small_plat_type'] = '实体';
                }
            }else{
                $list[$key]['small_plat_type'] = 'mac为空';
            }
            
            
        }
        if($page==1){
            
            $nums = ceil($total_nums / $pageSize);
            
            if($page <$nums){
                $is_next_page = 1;
                $nex_page = $page+1;
            }else {
                $is_next_page = 0;
            }
            
            $is_last_page =0;
            $nex_page = $page+1;
        }else if($page>1){
            
            $is_last_page = 1;
            $last_page = $page -1;
            $last_page = $page-1;
            
            $nums = ceil($total_nums / $pageSize);
            if($page <$nums){
                $is_next_page = 1;
                $nex_page = $page+1;
            }else {
                $is_next_page = 0;
            }
            
        }
        $this->assign('report_time',$report_time);
        $this->assign('page_nums',$nums);
        $this->assign('all_net_box_nums',$all_net_box_nums);
        $this->assign('online_ads_nums',$online_ads_nums);
        $this->assign('all_ads_arrive_rate',$all_ads_arrive_rate);
        $this->assign('area_id',$area_id);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('area_list',$area_list);
        $this->assign('last_page',$last_page);
        $this->assign('next_page',$nex_page);
        $this->assign('is_last_page',$is_last_page);
        $this->assign('is_next_page',$is_next_page);
        $this->assign('pub_ads_list',$pub_ads_list);
        $this->assign('hotel_list',$list);
        $this->assign('arrive_date',$arrive_date);
        $this->display('index');
        
    }
    /**
     * @desc 某个酒楼的在投广告到达明细
     */
    public function hotelAdsArriveDetail(){
        $report_time = I('get.report_time') ? I('get.report_time') : date('Y-m-d',strtotime('-1 day'));
        $hotel_id = I('get.hotel_id');
        //获取酒楼名称
        $m_hotel = new \Admin\Model\HotelModel();
        $where = array();
        $where['id'] = $hotel_id;
        $hotel_info = $m_hotel->field('name hotel_name')->where($where)->find();
        if(empty($hotel_info)){
            echo "<script>alert('该酒楼不存在');window.history.go(-1);</script>";
        }
        //获取酒楼正常机顶盒列表
        $m_box = new \Admin\Model\BoxModel();
        $fields = 'b.id box_id,mac box_mac,b.name box_name';
        $where = ' 1 and h.id='.$hotel_id.' and h.state=1 and h.flag=0 and b.state=1 and b.flag=0';
       
        $box_list = $m_box->isHaveMac($fields, $where);
        
        
        //获取再投的广告列表
        $m_pub_ads = new \Admin\Model\PubAdsModel();
        
        $where = array();
        $now_date = date('Y-m-d H:i:s');
        
        
        $yesterday_end_time = date('Y-m-d 23:59:59',strtotime($report_time));
        $yesterday_start_time = date('Y-m-d 00:00:00',strtotime($report_time));
        
        $where['pads.start_date'] = array('lt',$yesterday_end_time);
        $where['pads.end_date']   = array('gt',$yesterday_start_time);
        $where['pads.state']      = array('neq',2);
        $where['pads.id']         = array('not in','115,116,117,118,119,120,121,122');
        $fields = 'pads.id as pub_ads_id,med.id as media_id ,ads.name';
        $order = 'pads.create_time asc';
        $pub_ads_list = $m_pub_ads->getPubAdsList($fields, $where,$order);
        
        $m_box_media_arrive = new \Admin\Model\Statisticses\BoxMediaArriveModel();
        $m_pub_ads_box = new \Admin\Model\PubAdsBoxModel();
        $m_pub_ads_box_history = new \Admin\Model\PubAdsBoxHistoryModel();
        $redis = SavorRedis::getInstance();
        $time = time();
        $m_heart_log = new \Admin\Model\HeartLogModel();
        foreach($box_list as $key=>$v){
            
            foreach($pub_ads_list as $kk=>$vv){
                
                $where = array();
                $where['media_id'] = $vv['media_id'];
                $where['media_type'] = 'ads';
                $where['box_mac'] = $v['box_mac'];
                $where['report_date'] = array('ELT',$report_time.' 23:59:59') ;
                //print_r($where);exit;
                $nums = $m_box_media_arrive->getCount($where);
                if(!empty($nums)){//已下载
                    $ads_list[$kk] = 1;
                }else {
                    $where = array();
                    $where['pub_ads_id'] = $vv['pub_ads_id'];
                    $where['box_id']     = $v['box_id'];
                    
                    $nums1 = $m_pub_ads_box->getDataCount($where);
                    $nums2 = $m_pub_ads_box_history->getDataCount($where);
                    $nums = $nums1+$nums2;
                    
                    if(empty($nums)){ //未发布
                        $ads_list[$kk] =0;
                    }else {//发布未下载
                        $ads_list[$kk] = 2;
                    }
                }
            }
            $box_list[$key]['ads_list'] = $ads_list;
            //获取机顶盒的心跳时间
            $redis->select(13);
            $heart_info = $redis->get('heartbeat:2:'.$v['box_mac']);
            $heart_info = json_decode($heart_info,true);
            if(!empty($heart_info)){
                $d_time = strtotime($heart_info['date']);
                $diff = $time - $d_time;
                if($diff< 3600) {
                    $last_heart_time = floor($diff/60).'分';
                     
                }else if ($diff >= 3600 && $diff <= 86400) {
                    $hour = floor($diff/3600);
                    $min = floor($diff%3600/60);
                    $last_heart_time = $hour.'小时'.$min.'分';
                }else if ($diff > 86400) {
                    $day = floor($diff/86400);
                    $hour = floor($diff%86400/3600);
                    $last_heart_time = $day.'天'.$hour.'小时';
                }
            }else {
                $heart_info = $m_heart_log->getInfo('last_heart_time', array('box_id'=>$v['box_id']));
                if(!empty($heart_info)){
                    $d_time = strtotime($heart_info['last_heart_time']);
                    $diff = $time - $d_time;
                    if($diff< 3600) {
                        $last_heart_time = floor($diff/60).'分';
                         
                    }else if ($diff >= 3600 && $diff <= 86400) {
                        $hour = floor($diff/3600);
                        $min = floor($diff%3600/60);
                        $last_heart_time = $hour.'小时'.$min.'分';
                    }else if ($diff > 86400) {
                        $day = floor($diff/86400);
                        $hour = floor($diff%86400/3600);
                        $last_heart_time = $day.'天'.$hour.'小时';
                    }
                }else {
                    $last_heart_time='30天';
                }
                
            }
            
            
            $box_list[$key]['heart_time'] = $last_heart_time.'前';
            
        }
        $this->assign('hotel_name',$hotel_info['hotel_name']);
        $this->assign('pub_ads_list',$pub_ads_list);
        $this->assign('box_list',$box_list);
        $this->display('detail');
    }
}