<?php
namespace Admin\Controller;
use \Common\Lib\SavorRedis;

/**
 *@desc 本店有售商品广告管理
 *
 */

class StoresaleadvController extends BaseController {

    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->oss_host = get_oss_host();
    }

    public function advlist(){
        $keywords = I('keywords','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数

        $where = array('storesaleads.state'=>array('in',array(1,2)));
        if (!empty($keywords)) {
            $where['ads.name'] = array('like',"%$keywords%");
        }
        $field = 'storesaleads.id,storesaleads.state,ads.name,ads.duration,ads.resource_type,storesaleads.ads_id,storesaleads.add_time,storesaleads.creator_id';
        $orders = 'storesaleads.id desc';
        $start  = ($page-1) * $size;

        $m_storesaleads = new \Admin\Model\StoresaleAdsModel();
        $result = $m_storesaleads->getList($field,$where,$orders,$start,$size);
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
        $m_storesaleads = new \Admin\Model\StoresaleAdsModel();
        if(IS_POST){
            $h_b_arr = $_POST['hbarr'];
            $ads_id = I('post.marketid',0,'intval');
            $start_date = I('post.start_date', '');
            $end_date = I('post.end_date', '');
            $goods_id = I('post.goods_id',0,'intval');
            $is_price = I('post.is_price',0,'intval');
            $type = I('post.type',0,'intval');
            if (empty($ads_id)){
                $this->output('上传广告视频失败请重新上传', 'storesaleadv/advlist',2,0);
            }
            $now_date = date("Y-m-d H:i:s");
            $now_day = date("Y-m-d");
            if($start_date > $end_date){
                $this->output('投放开始时间必须小于等于结束时间', 'storesaleadv/advlist',2,0);
            }
            if($start_date < $now_day){
                $this->output('投放开始时间必须大于等于今天', 'storesaleadv/advlist',2,0);
            }
            $m_ads = new \Admin\Model\AdsModel();
            $m_ads->updateData(array('id'=>$ads_id),array('type'=>9));

            $userInfo = session('sysUserInfo');
            $hotel_arr = json_decode($h_b_arr, true);
            $start_datetime = date('Y-m-d 00:00:00',strtotime($start_date));
            $end_datetime = date('Y-m-d 23:59:59',strtotime($end_date));
            $save_data = array('ads_id'=>$ads_id,'start_date'=>$start_datetime,'end_date'=>$end_datetime,'add_time'=>$now_date,
                'creator_id'=>$userInfo['id'],'state'=>1,'goods_id'=>$goods_id,'type'=>$type,'is_price'=>$is_price
            );
            $sale_ads_id = $m_storesaleads->addData($save_data);
            if(!$sale_ads_id){
                $this->output('添加失败','storesaleadv/advlist',2,0);
            }

            $m_adshotel = new \Admin\Model\StoresaleAdsHotelModel();
            $data_hotel = array();
            $tmp_hb = array();
            foreach ($hotel_arr as $k=>$v) {
                $hotel_id = $v['hotel_id'];
                if(array_key_exists($hotel_id, $tmp_hb)){
                    continue;
                }
                $tmp_hb[$hotel_id] = 1;
                $data_hotel[] = array('hotel_id'=>$hotel_id,'storesale_ads_id'=>$sale_ads_id);
            }
            $res = $m_adshotel->addAll($data_hotel);
            if($res){
                $redis = SavorRedis::getInstance();
                $redis->select(12);
                $cache_key_pre = C('SMALLAPP_STORESALE_ADS');
                foreach($hotel_arr as $key=>$v){
                    $period = getMillisecond();
                    $redis->set($cache_key_pre.$v['hotel_id'],$period,86400*14);
                }
                $this->output('添加成功','storesaleadv/advlist');
            }else {
                $this->output('添加失败','storesaleadv/advlist',2,0);
            }

        }else{
            //城市
            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();

            $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
            $where = array('type'=>43,'status'=>1,'flag'=>2);
            $all_goods = $m_goods->getDataList('*',$where, 'id desc');

            $this->assign('all_goods',$all_goods);
            $this->assign('is_city_search',1);
            $this->assign('areainfo', $area_arr);
            $this->display('adddevilery');
        }
    }

    public function advpreview(){
        $adsid = I('deliveryid','0','intval');
        $m_storesaleads = new \Admin\Model\StoresaleAdsModel();
        $field = ' storesaleads.*,ads.name adname,ads.duration,med.oss_addr';
        $oss_host = $this->oss_host;
        $vinfo = $m_storesaleads->getAdsInfoByid($field, array('storesaleads.id'=>$adsid));
        $is_price_str = '否';
        if($vinfo['is_price']==1){
            $is_price_str = '是';
        }

        $m_goods = new \Admin\Model\Smallapp\DishgoodsModel();
        $res_goods = $m_goods->getInfo(array('id'=>$vinfo['goods_id']));
        $all_types = C('STORESALE_ADV_TYPES');
        $vinfo['goods_name'] = $res_goods['name'];
        $vinfo['is_price_str'] = $is_price_str;
        $vinfo['typestr'] = $all_types[$vinfo['type']];
        $vinfo['oss_addr'] = $oss_host.$vinfo['oss_addr'];
        $vinfo['start_date'] = date("Y/m/d", strtotime($vinfo['start_date']));
        $vinfo['end_date'] = date("Y/m/d", strtotime($vinfo['end_date']));

        $m_ads_hotel = new \Admin\Model\StoresaleAdsHotelModel();
        $where = array('storesale_ads_id'=>$adsid);
        $hotel_count = $m_ads_hotel->getDataCount($where);
        $this->assign('hotel_count',$hotel_count);

        $this->assign('vinfo',$vinfo);
        $this->display('advpreviewhotel');
    }

    public function showdetail() {
        $adsid = I('deliveryid','0','intval');
        $page = I('pageNum',1);
        $size = I('numPerPage',50);//显示每页记录数
        $order = I('_order','id');
        $sort = I('_sort','desc');

        $m_ads_hotel = new \Admin\Model\StoresaleAdsHotelModel();
        $field = 'adshotel.id,hotel.name as hotel_name';
        $where = array('adshotel.storesale_ads_id'=>$adsid);
        $start = ($page-1)*$size;
        $result = $m_ads_hotel->getList($field,$where,'id asc',$start,$size);
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
        $m_storesale = new \Admin\Model\StoresaleAdsModel();
        $m_storesale->updateData($where, array('state'=>$status));
        if($status==1){
            $message = '启用成功';
        }elseif($status==2){
            $message = '禁用成功';
        }else{
            $message = '操作成功';
        }
        $m_ads_hotel = new \Admin\Model\StoresaleAdsHotelModel();
        $field = 'hotel_id';
        $where = array('storesale_ads_id'=>$adsid);
        $res_hotel = $m_ads_hotel->getDataList($field,$where,'id asc');
        if(!empty($res_hotel)){
            $redis = SavorRedis::getInstance();
            $redis->select(12);
            $cache_key_pre = C('SMALLAPP_STORESALE_ADS');
            foreach($res_hotel as $key=>$v){
                $period = getMillisecond();
                $redis->set($cache_key_pre.$v['hotel_id'],$period,86400*14);
            }
        }
        $this->output($message, 'storesaleadv/advlist',2);
    }

    public function deleteAds(){
        $adsid = I('get.id','0','intval');
        $where = array('id'=>$adsid);
        $m_storesale = new \Admin\Model\StoresaleAdsModel();
        $ret = $m_storesale->updateData($where, array('state'=>3));
        if($ret){
            $m_ads_hotel = new \Admin\Model\StoresaleAdsHotelModel();
            $field = 'hotel_id';
            $where = array('storesale_ads_id'=>$adsid);
            $res_hotel = $m_ads_hotel->getDataList($field,$where,'id asc');
            if(!empty($res_hotel)){
                $redis = SavorRedis::getInstance();
                $redis->select(12);
                $cache_key_pre = C('SMALLAPP_STORESALE_ADS');
                foreach($res_hotel as $key=>$v){
                    $period = getMillisecond();
                    $redis->set($cache_key_pre.$v['hotel_id'],$period,86400*14);
                }
            }
            $this->output('删除成功', 'storesaleadv/advlist', 2);
        }else {
            $this->error('删除失败');
        }
    }

}
