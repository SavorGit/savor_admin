<?php
namespace Admin\Controller;
use \Common\Lib\SavorRedis;

/**
 *@desc 跑马灯广告管理
 *
 */

class MarqueeadvController extends BaseController {

    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->oss_host = get_oss_host();
    }

    public function advlist(){
        $keywords = I('keywords','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数

        $where = array('marqueeads.state'=>array('in',array(1,2)));
        if (!empty($keywords)) {
            $where['ads.name'] = array('like',"%$keywords%");
        }
        $field = 'marqueeads.id,marqueeads.state,ads.name,ads.duration,ads.resource_type,marqueeads.ads_id,marqueeads.add_time,marqueeads.creator_id';
        $orders = 'marqueeads.id desc';
        $start  = ($page-1) * $size;

        $m_marquee = new \Admin\Model\MarqueeAdsModel();
        $result = $m_marquee->getList($field,$where,$orders,$start,$size);
        $m_user = new \Admin\Model\UserModel();
        $datalist = $result['list'];
        foreach ($datalist as $k=>$v){
            if($v['resource_type']==1){
                $v['resourcetypestr'] = '视频';
            }elseif($v['resource_type']==2){
                $v['resourcetypestr'] = '图片';
            }else{
                $v['resourcetypestr'] = '';
            }
            $userinfo = $m_user->getUserInfo($v['creator_id']);
            $v['username'] = $userinfo['remark'];
            $datalist[$k] = $v;
        }

        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('keywords',$keywords);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('advlist');
    }

    public function adddevilery(){
        $m_marquee = new \Admin\Model\MarqueeAdsModel();
        if(IS_POST){
            $h_b_arr = $_POST['hbarr'];
            $ads_id = I('post.marketid',0,'intval');
            $start_date = I('post.start_date', '');
            $end_date = I('post.end_date', '');
            $start_hour = I('post.start_hour', '');
            $end_hour = I('post.end_hour', '');
            $description = I('post.description','','trim');
            if (empty($ads_id)){
                $this->output('上传广告视频失败请重新上传', 'marqueeadv/advlist',2,0);
            }
            $now_date = date("Y-m-d H:i:s");
            $now_day = date("Y-m-d");
            if($start_date > $end_date){
                $this->output('投放开始时间必须小于等于结束时间', 'marqueeadv/advlist',2,0);
            }
            if($start_date < $now_day){
                $this->output('投放开始时间必须大于等于今天', 'marqueeadv/advlist',2,0);
            }
            $userInfo = session('sysUserInfo');

            $hotel_arr = json_decode($h_b_arr, true);
            $save_data = array('ads_id'=>$ads_id,'start_date'=>$start_date,'end_date'=>$end_date,'add_time'=>$now_date,
                'creator_id'=>$userInfo['id'],'state'=>1,'start_hour'=>$start_hour,'end_hour'=>$end_hour
            );
            $marquee_ads_id = $m_marquee->addData($save_data);
            if(!$marquee_ads_id){
                $this->output('添加失败','marqueeadv/advlist',2,0);
            }
            $m_ads = new \Admin\Model\AdsModel();
            $m_ads->updateData(array('id'=>$ads_id),array('description'=>$description));

            $m_marquee_adshotel = new \Admin\Model\MarqueeAdsHotelModel();
            $data_hotel = array();
            $tmp_hb = array();
            foreach ($hotel_arr as $k=>$v) {
                $hotel_id = $v['hotel_id'];
                if(array_key_exists($hotel_id, $tmp_hb)){
                    continue;
                }
                $tmp_hb[$hotel_id] = 1;
                $data_hotel[] = array('hotel_id'=>$hotel_id,'marquee_ads_id'=>$marquee_ads_id);
            }
            $res = $m_marquee_adshotel->addAll($data_hotel);
            if($res){
                $redis = SavorRedis::getInstance();
                $redis->select(12);
                $cache_key_pre = C('SMALLAPP_MARQUEE_ADS');
                foreach($hotel_arr as $key=>$v){
                    $period = getMillisecond();
                    $redis->set($cache_key_pre.$v['hotel_id'],$period,86400*14);
                }

                $this->output('添加成功','marqueeadv/advlist');
            }else {
                $this->output('添加失败','marqueeadv/advlist',2,0);
            }

        }else{
            //城市
            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();
            $hours = array();
            for($i=0;$i<24;$i++){
                $hours[]=str_pad($i,2,'0',STR_PAD_LEFT);
            }
            $this->assign('hours',$hours);
            $this->assign('is_city_search',1);
            $this->assign('areainfo', $area_arr);
            $this->display('adddevilery');
        }
    }

    public function advpreview(){
        $adsid = I('deliveryid','0','intval');
        $m_marquee = new \Admin\Model\MarqueeAdsModel();
        $field = ' marqueeads.id,marqueeads.start_date,marqueeads.end_date,marqueeads.start_hour,marqueeads.end_hour,marqueeads.state state,
        ads.name adname,ads.duration,med.oss_addr';
        $where = 'marqueeads.id = '.$adsid;
        $oss_host = $this->oss_host;
        $vinfo = $m_marquee->getAdsInfoByid($field, $where);

        $vinfo['oss_addr'] = $oss_host.$vinfo['oss_addr'];
        $vinfo['start_date'] = date("Y/m/d", strtotime($vinfo['start_date']));
        $vinfo['end_date'] = date("Y/m/d", strtotime($vinfo['end_date']));

        $m_marquee_ads_hotel = new \Admin\Model\MarqueeAdsHotelModel();
        $where = array('marquee_ads_id'=>$adsid);
        $hotel_count = $m_marquee_ads_hotel->getDataCount($where);
        $display_html = 'advpreviewhotel';
        $this->assign('hotel_count',$hotel_count);

        $this->assign('vinfo',$vinfo);
        $this->display($display_html);
    }

    public function showdetail() {
        $adsid = I('deliveryid','0','intval');
        $page = I('pageNum',1);
        $size = I('numPerPage',50);//显示每页记录数
        $order = I('_order','id');
        $sort = I('_sort','desc');

        $m_marquee_ads_hotel = new \Admin\Model\MarqueeAdsHotelModel();
        $field = 'adshotel.id,hotel.name as hotel_name';
        $where = array('adshotel.marquee_ads_id'=>$adsid);
        $start = ($page-1)*$size;
        $result = $m_marquee_ads_hotel->getList($field,$where,'id asc',$start,$size);
        $datalist = $result['list'];
        foreach ($datalist as $k=>$v){
            $datalist[$k]['msg'] = '酒楼：'.$v['hotel_name'];
        }

        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('_sort',$sort);
        $this->assign('_order',$order);
        $this->assign('deliveryid', $adsid);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->display('showdetail');
    }

    public function operateStatus(){
        $adsid = I('get.id',0,'intval');
        $status = I('get.status',3,'intval');
        $where = array('id'=>$adsid);
        $m_marquee = new \Admin\Model\MarqueeAdsModel();
        $m_marquee->updateData($where, array('state'=>$status));
        if($status==1){
            $message = '启用成功';
        }elseif($status==2){
            $message = '禁用成功';
        }else{
            $message = '操作成功';
        }
        $m_marquee_ads_hotel = new \Admin\Model\MarqueeAdsHotelModel();
        $field = 'hotel_id';
        $where = array('marquee_ads_id'=>$adsid);
        $res_hotel = $m_marquee_ads_hotel->getDataList($field,$where,'id asc');
        if(!empty($res_hotel)){
            $redis = SavorRedis::getInstance();
            $redis->select(12);
            $cache_key_pre = C('SMALLAPP_MARQUEE_ADS');
            foreach($res_hotel as $key=>$v){
                $period = getMillisecond();
                $redis->set($cache_key_pre.$v['hotel_id'],$period,86400*14);
            }
        }
        $this->output($message, 'marqueeadv/advlist',2);
    }

    public function deleteAds(){
        $adsid = I('get.id','0','intval');
        $where = array('id'=>$adsid);
        $m_marquee = new \Admin\Model\MarqueeAdsModel();
        $ret = $m_marquee->updateData($where, array('state'=>3));
        if($ret){
            $m_marquee_ads_hotel = new \Admin\Model\MarqueeAdsHotelModel();
            $field = 'hotel_id';
            $where = array('marquee_ads_id'=>$adsid);
            $res_hotel = $m_marquee_ads_hotel->getDataList($field,$where,'id asc');
            if(!empty($res_hotel)){
                $redis = SavorRedis::getInstance();
                $redis->select(12);
                $cache_key_pre = C('SMALLAPP_MARQUEE_ADS');
                foreach($res_hotel as $key=>$v){
                    $period = getMillisecond();
                    $redis->set($cache_key_pre.$v['hotel_id'],$period,86400*14);
                }
            }
            $this->output('删除成功', 'marqueeadv/advlist', 2);
        }else {
            $this->error('删除失败');
        }
    }

}
