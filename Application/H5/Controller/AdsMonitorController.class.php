<?php 
/**
 *@author zhang.yingtao
 *@desc 广告到达H5页面
 *@since 2018-05-18
 *
 */
namespace H5\Controller;

use Think\Controller;

class AdsMonitorController extends Controller {
    
    /**
     * @desc 广告到达情况
     */
    public function index(){
        
        $area_id = I('post.area_id',0,'intval');
        $hotel_name = I('post.hotel_name','','trim');
        
        
        //广告到达时间
        $arrive_date = date('Ymd',strtotime('-1 day'));
        
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
        $where['a.start_date'] = array('elt',$now_date);
        $where['a.end_date']   = array('gt',$now_date);
        $where['a.state']      = array('neq',2);
        $online_ads_nums = $m_pub_ads->countNums($where);
        
        //北上广深数据统计
        $m_area = new \Admin\Model\AreaModel();
        $area_list = $m_area->getHotelAreaList();
        $all_ads_arrive_rate = 0;
        $all_net_box_nums = 0;
        foreach($area_list as $key=>$v){
            
           
            //在投广告个数
            $sql ="SELECT count(distinct pubbox.box_id) boxnum,pubbox.`pub_ads_id`  
                   FROM savor_pub_ads_box pubbox 
                   LEFT JOIN savor_pub_ads ads ON pubbox.`pub_ads_id`=ads.`id` 
                   LEFT JOIN savor_box box ON pubbox.`box_id`=box.`id` 
                   LEFT JOIN savor_room room ON box.`room_id`=room.`id` 
                   LEFT JOIN savor_hotel hotel ON hotel.`id`=room.`hotel_id` 
                   LEFT JOIN savor_area_info AS areainfo ON hotel.`area_id`=areainfo.`id` 
                   WHERE ads.start_date<=NOW() AND ads.`end_date`>NOW() AND ads.`state`!=2 
                   AND hotel.`area_id`=".$v['id']." and hotel.state=1 and hotel.flag=0 and box.state=1 
                   and box.flag=0 GROUP by pubbox.`pub_ads_id` ";
            $tmp = M()->query($sql);
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
            
           
            
            $ads_arrive_rate = sprintf("%1.2f",$arrive_box_num / $all_area_pub_box_num *100) ; 
            
            $all_ads_arrive_rate +=$ads_arrive_rate;
            $area_list[$key]['ads_arrive_rate'] = $ads_arrive_rate;
        }
        $counts = count($area_list);
        $all_ads_arrive_rate = sprintf("%1.2f",$all_ads_arrive_rate/$counts);
        
        //酒楼明细
        $page = I('get.page',0,'intval') ? I('get.page',0,'intval') : 1;
        $pageSize = 15;
        $start = ($page-1) * $pageSize;
        
        $m_box_media_arrive_ratio = new \Admin\Model\Statisticses\BoxMediaArriveRatioModel();
        
        $fields = 'a.hotel_id,hotel.name hotel_name,a.arrive_ratio';
        $where  = array();
        $where['a.media_id'] = '-10000';
        $where['a.hotel_id'] = array('gt',0);
        $order = 'a.arrive_ratio asc';
        
        if($area_id){
            $where['a.area_id'] = $area_id;
        }
        if($hotel_name){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        
        $list = $m_box_media_arrive_ratio->getList($fields, $where, $order, $start, $pageSize);
        //获取再投的广告列表
        $m_pub_ads = new \Admin\Model\PubAdsModel();
        
        $where = array();
        $now_date = date('Y-m-d H:i:s');
        $where['pads.start_date'] = array('elt',$now_date);
        $where['pads.end_date']   = array('gt',$now_date);
        $where['pads.state']      = array('neq',2);
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
                $tmp = $m_box_media_arrive_ratio->getOne('arrive_ratio',$where);
                if(!empty($tmp)){
                    $rates = $tmp['arrive_ratio']*100;
                    $rates .='%';
                }else {
                    $rates = '';
                }
                
                $ads_list[$kk] = $rates;
            }
            $list[$key]['ads_rate_list'] = $ads_list;
            
            $all_rates = $v['arrive_ratio']*100;
            $all_rates = $all_rates."%";
            $list[$key]['arrive_ratio'] = $all_rates;
            
        }
        if($page==1){
            $is_next_page = 1;
            $is_last_page =0;
            $nex_page = $page+1;
        }else if($page>1){
            
            $is_last_page = 1;
            $last_page = $page -1;
            $last_page = $page-1;
            $nums = count($list);
            ceil($nums / $pageSize);
            if($page <$nums){
                $is_next_page = 1;
                $nex_page = $page+1;
            }else {
                $is_next_page = 0;
            }
            
        }
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
        $where['pads.start_date'] = array('elt',$now_date);
        $where['pads.end_date']   = array('gt',$now_date);
        $where['pads.state']      = array('neq',2);
        $fields = 'pads.id as pub_ads_id,med.id as media_id ,ads.name';
        $order = 'pads.create_time asc';
        $pub_ads_list = $m_pub_ads->getPubAdsList($fields, $where,$order);
        
        $m_box_media_arrive = new \Admin\Model\Statisticses\BoxMediaArriveModel();
        $m_pub_ads_box = new \Admin\Model\PubAdsBoxModel();
        foreach($box_list as $key=>$v){
            
            foreach($pub_ads_list as $kk=>$vv){
                
                $where = array();
                $where['media_id'] = $vv['media_id'];
                $where['media_type'] = 'ads';
                $where['box_mac'] = $v['box_mac'];
                //print_r($where);exit;
                $nums = $m_box_media_arrive->getCount($where);
                if(!empty($nums)){//已下载
                    $ads_list[$kk] = 1;
                }else {
                    $where = array();
                    $where['pub_ads_id'] = $vv['pub_ads_id'];
                    $where['box_id']     = $v['box_id'];
                    
                    $nums = $m_pub_ads_box->getDataCount($where);
                    if(empty($nums)){ //未发布
                        $ads_list[$kk] =0;
                    }else {//发布未下载
                        $ads_list[$kk] = 2;
                    }
                }
            }
            $box_list[$key]['ads_list'] = $ads_list;
            
        }
        $this->assign('hotel_name',$hotel_info['hotel_name']);
        $this->assign('pub_ads_list',$pub_ads_list);
        $this->assign('box_list',$box_list);
        $this->display('detail');
    }
}