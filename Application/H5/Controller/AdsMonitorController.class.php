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
use Common\Lib\PHPlot\PHPlot;
use Common\Lib\PHPlot\PHPlot_truecolor; 
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
        
         
        
        //酒楼明细
        $page = I('page',0,'intval') ? I('page',0,'intval') : 1;
        $pageSize = 15;
        $start = ($page-1) * $pageSize;
        
        $hotel_box_type_arr = C('heart_hotel_box_type');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        $m_box_media_arrive_ratio_history = new \Admin\Model\Statisticses\BoxMediaArriveRatioHistroyModel(); 
        
        $fields = 'a.hotel_id,hotel.name hotel_name,a.arrive_ratio,ext.mac_addr';
        $where  = array();
        $where['a.media_id'] = '-10000';
        $where['a.hotel_id'] = array('gt',0);
        $where['hotel.state'] = 1;
        $where['hotel.flag']  = 0;
        $where['hotel.id'] = array('not in',array(7,53,791,747,508));
        $where['hotel.hotel_box_type'] = array('in',$hotel_box_type_str);
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
        $fields = 'pads.id as pub_ads_id,med.id as media_id ,ads.name,med.oss_addr';
        $order = 'pads.create_time asc';
        $pub_ads_list = $m_pub_ads->getPubAdsList($fields, $where,$order);
        
        $m_box = new \Admin\Model\BoxModel();
        $redis = SavorRedis::getInstance();
        $time = time();
        $m_heart_log = new \Admin\Model\HeartLogModel();
        foreach($list as $key=>$v){
            $ads_list = array();
            
            //获取酒楼正常机顶盒列表
            
            $fields = 'b.id box_id,mac box_mac,b.name box_name';
            $where = ' 1 and h.id='.$v['hotel_id'].' and h.state=1 and h.flag=0 and b.state=1 and b.flag=0';
           
            $box_list = $m_box->isHaveMac($fields, $where);
            $diff = 0;
            foreach($box_list as $kk=>$vv){
                //获取机顶盒的心跳时间
                $redis->select(13);
                $heart_info = $redis->get('heartbeat:2:'.$vv['box_mac']);
                $heart_info = json_decode($heart_info,true);
                if(!empty($heart_info)){
                    $d_time = strtotime($heart_info['date']);
                    $diff += $time - $d_time;
                    
                }else {
                    $heart_info = $m_heart_log->getInfo('last_heart_time', array('box_id'=>$v['box_id']));
                    if(!empty($heart_info)){
                        $d_time = strtotime($heart_info['last_heart_time']);
                        $diff += $time - $d_time;
                        
                    }else {
                        $diff += '2592000';
                    }
                
                } 
            }
            $diff = floor($diff / count($box_list)); 
            if($diff< 3600) {
                $loss_time = floor($diff/60).'分';
                 
            }else if ($diff >= 3600 && $diff <= 86400) {
                $hour = floor($diff/3600);
                $min = floor($diff%3600/60);
                $loss_time = $hour.'小时'.$min.'分';
            }else if ($diff > 86400) {
                $day = floor($diff/86400);
                $hour = floor($diff%86400/3600);
                $loss_time = $day.'天'.$hour.'小时';
            }
            
            $list[$key]['loss_time'] = $loss_time;
            
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
        $fields = 'pads.id as pub_ads_id,med.id as media_id ,ads.name,med.oss_addr';
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
                $heart_info = $m_heart_log->getInfo('last_heart_time,apk_version as apk', array('box_id'=>$v['box_id']));
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
            
            $box_list[$key]['apk']        = $heart_info['apk'];
            $box_list[$key]['heart_time'] = $last_heart_time.'前';
            
        }
        $this->assign('hotel_name',$hotel_info['hotel_name']);
        $this->assign('pub_ads_list',$pub_ads_list);
        $this->assign('box_list',$box_list);
        $this->display('detail');
    }
    /**
     * @desc 心跳以及日志文件统计图表
     * 
     */
    public function heartLogGraph(){
        $box_mac = I('box_mac');
        //echo "fdafasd";exit;
        $m_heart_all_log = new \Admin\Model\HeartAllLogModel();
        $m_oss_box_log_detail = new \Admin\Model\Oss\BoxLogDetailModel();
        $result = array();
        for($i=14;$i>0;$i--){
           $tmp = array();
           $date_time_str =  strtotime("-$i days");
           //$heart_start_time = date('Y-m-d 00:00:00',$date_time_str);
           //$heart_end_time   = date('Y-m-d 23:59:59', $date_time_str);
           $log_date = date('Ymd',$date_time_str);
           //心跳统计
           $sum_str = '';
           for($j=0;$j<24;$j++){
               $sum_str .= $space ."sum(hour$j)";
               $space    = '+';
           }
           $sql = "select $sum_str as nums from savor_heart_all_log where mac='".$box_mac."'
                   and type=2 and date='".$log_date."'";
           $ret = $m_heart_all_log->query($sql);
           
           //日志统计
           $rets = $m_oss_box_log_detail->where(array('box_mac'=>$box_mac,'log_create_date'=>$log_date))->count();
           $tmp = array($log_date,intval($ret[0]['nums']),intval($rets));
           $result[] = $tmp;
           
        }
        $p = new PHPlot(1500, 800);
        
        $p->SetDefaultTTFont('/Public/admin/assets/Fonts/simhei.ttf'); //设置字体，还是支持中文的吧
        $p->SetTitle(iconv_arr('机顶盒心跳-日志统计')); //设置标题，还是用iconv_arr来解决中文
        
        # Select the data array representation and store the data:
        $p->SetDataType('text-data'); //设置使用的数据类型，在这个里面可以使用多种类型。
        $p->SetDataValues($result); //把一个数组$data赋给类的一个变量$this->data_values.要开始作图之前调用。
        $p->SetPlotType('linepoints'); //选择图表类型为线性.可以是bars,lines,linepoints,area,points,pie等。
        
        $p->SetPlotAreaWorld(0, 0, 14, 200);  //设置图表边距
        
        # Select an overall image background color and another color under the plot:
        $p->SetBackgroundColor('#ffffcc'); //设置整个图象的背景颜色。
        $p->SetDrawPlotAreaBackground(True); //设置节点区域的背景
        $p->SetPlotBgColor('#ffffff'); //设置使用SetPlotAreaPixels()函数设定的区域的颜色。
        $p->SetLineWidth(3);  //线条宽度
        # Draw lines on all 4 sides of the plot:
        $p->SetPlotBorderType('full');  //设置线条类型
        
        # Set a 3 line legend, and position it in the upper left corner:
        $p->SetLegend(iconv_arr(array('心跳总数', '日志总数'))); //显示在一个图列框中的说明
        $p->SetLegendWorld(0.1, 180); //设定这个文本框位置
        
        # Generate and output the graph now:
        $dd = $p->DrawGraph();
        
    }
}