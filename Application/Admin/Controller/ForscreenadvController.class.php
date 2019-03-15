<?php
namespace Admin\Controller;

/**
 *@desc 投屏广告管理
 *
 */

class ForscreenadvController extends BaseController {

    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->oss_host = get_oss_host();
    }

    public function advlist(){
        $name = I('serachads','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $order = I('_order',' forscreenads.create_time ');
        $sort = I('_sort','desc');

        $where = "forscreenads.state!=4";//状态:0未执行,1执行中,2可用,3不可用,4已删除
        if ($name) {
            $this->assign('adsname', $name);
            $where .= " and ads.name like '%".$name."%' ";
        }
        $field = 'forscreenads.id,ads.name,ads.duration,ads.resource_type,forscreenads.ads_id,forscreenads.create_time,forscreenads.type,
        forscreenads.state,forscreenads.creator_id';
        $orders = $order.' '.$sort;
        $start  = ($page-1) * $size;

        $m_forscreen = new \Admin\Model\ForscreenAdsModel();
        $result = $m_forscreen->getList($field, $where, $orders,$start,$size);
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
        $this->assign('_sort',$sort);
        $this->assign('_order',$order);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('advlist');
    }

    public function adddevilery(){
        $m_forscreenads = new \Admin\Model\ForscreenAdsModel();
        $where = array('state'=>array('in','0,1'));//状态:0未执行,1执行中,2可用,3不可用,4已删除
        $res_forscreen = $m_forscreenads->getDataList('id',$where,'id desc',0,1);
        if($res_forscreen['total']){
            $this->output('当前有广告正在发布，暂时无法添加，请稍后再试', 'forscreenadv/advlist',2,0);
        }
        if(IS_POST){
            $h_b_arr = $_POST['hbarr'];
            $ads_id = I('post.marketid',0,'intval');
            $screen_type = I('post.screenadv_type','1');//投放类型1机顶盒2酒店
            $start_date = I('post.start_time', '');
            $end_date = I('post.end_time', '');
            $play_position = I('post.play_position',0,'intval');
            if (empty($ads_id)){
                $this->output('上传广告视频失败请重新上传', 'forscreenadv/advlist',2,0);
            }
            $now_date = date("Y-m-d H:i:s");
            $now_day = date("Y-m-d");
            if($start_date > $end_date){
                $this->output('投放开始时间必须小于等于结束时间', 'forscreenadv/advlist',2,0);
            }
            if($start_date < $now_day){
                $this->output('投放开始时间必须大于等于今天', 'forscreenadv/advlist',2,0);
            }
            $userInfo = session('sysUserInfo');

            $hotel_arr = json_decode($h_b_arr, true);
            $save_data = array();
            $save_data['ads_id'] = $ads_id;
            $save_data['start_date'] = $start_date;
            $save_data['end_date'] = $end_date;
            $save_data['create_time'] = $now_date;
            $save_data['update_time'] = $now_date;
            $save_data['creator_id'] = $userInfo['id'];
            $save_data['play_position'] = $play_position;
            $save_data['state'] = 1;
            $oneday_count = 86400;
            $save_data['end_date'] = date("Y-m-d H:i:s", strtotime($save_data['end_date']) + $oneday_count-1);
            $save_data['type'] = $screen_type;
            $save_data['cover_img_media_id'] = I('post.cover_img_media_id',0,'intval');

            $forscreen_ads_id = $m_forscreenads->addData($save_data);
            if(!$forscreen_ads_id){
                $this->output('添加失败','forscreenadv/advlist',2,0);
            }

            if($screen_type == 2){
                $m_forscreenhotel = new \Admin\Model\ForscreenAdsHotelModel();
                $data_hotel = array();
                $tmp_hb = array();
                foreach ($hotel_arr as $k=>$v) {
                    $hotel_id = $v['hotel_id'];
                    if(array_key_exists($hotel_id, $tmp_hb)){
                        continue;
                    }
                    $tmp_hb[$hotel_id] = 1;
                    $data_hotel[] = array('hotel_id'=>$hotel_id,'forscreen_ads_id'=>$forscreen_ads_id);
                }

                $res = $m_forscreenhotel->addAll($data_hotel);
                if($res){
                    $this->output('添加成功','forscreenadv/advlist');
                }else {
                    $this->output('添加失败','forscreenadv/advlist',2,0);
                }
            }else{
                //插入box表
                $this->output('按机顶盒发布暂不可用,请选择按酒楼进行发布','forscreenadv/advlist');
            }

        }else{
            //城市
            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();
            //城市
            $userinfo = session('sysUserInfo');
            $is_city_search = 0;
            if($userinfo['groupid'] == 1 || empty($userinfo['area_city'])){
                $is_city_search = 1;
                $this->assign('is_city_search',$is_city_search);
            }else {
                $this->assign('is_city_search',$is_city_search);
            }
            $this->assign('areainfo', $area_arr);
            $this->display('adddevilery');
        }
    }

    public function advpreview(){
        $adsid = I('deliveryid','0','intval');
        $m_forscreenads = new \Admin\Model\ForscreenAdsModel();
        $field = ' forscreenads.id,forscreenads.type,forscreenads.start_date,forscreenads.end_date,forscreenads.state state,
        ads.NAME adname,ads.duration,med.oss_addr';
        $where = 'forscreenads.id = '.$adsid;
        $oss_host = $this->oss_host;
        $vinfo = $m_forscreenads->getForscreenAdsInfoByid($field, $where);
        if($vinfo['state']==1){
            $this->output('广告正在发布中', 'forscreenadv/advlist',2,0);
        }

        $vinfo['oss_addr'] = $oss_host.$vinfo['oss_addr'];
        $vinfo['start_date'] = date("Y/m/d", strtotime($vinfo['start_date']));
        $vinfo['end_date'] = date("Y/m/d", strtotime($vinfo['end_date']));

        $m_forscreen_ads_hotel = new \Admin\Model\ForscreenAdsHotelModel();
        $m_forscreen_ads_box = new \Admin\Model\ForscreenAdsBoxModel();
        if($vinfo['type']==1){//版位预览
            $display_html = 'advpreviewbox';
        }elseif($vinfo['type']==2){
            $where = array('forscreen_ads_id'=>$adsid);
            $hotel_count = $m_forscreen_ads_hotel->getDataCount($where);
            $box_count = $m_forscreen_ads_box->getDataCount($where);
            $display_html = 'advpreviewhotel';
            $this->assign('hotel_count',$hotel_count);
            $this->assign('box_count',$box_count);
        }
        $this->assign('vinfo',$vinfo);
        $this->display($display_html);
    }

    public function showdetail() {
        $adsid = I('deliveryid','0','intval');
        $page = I('pageNum',1);
        $size = I('numPerPage',50);//显示每页记录数
        $order = I('_order','id');
        $sort = I('_sort','desc');

        $m_forscreenads = new \Admin\Model\ForscreenAdsModel();
        $res_ads = $m_forscreenads->getInfo(array('id'=>$adsid));
        if($res_ads['state']!=2){
            $this->output('广告正在发布中', 'forscreenadv/advlist',2,0);
        }
        $m_forscreen_adsbox = new \Admin\Model\ForscreenAdsBoxModel();
        $field = 'adsbox.id,hotel.name as hotel_name,room.name as room_name,box.name as box_name';
        $where = array('adsbox.forscreen_ads_id'=>$adsid);
        $start = ($page-1)*$size;
        $result = $m_forscreen_adsbox->getList($field,$where,'id asc',$start,$size);
        $datalist = $result['list'];
        foreach ($datalist as $k=>$v){
            $datalist[$k]['msg'] = '酒楼：'.$v['hotel_name'].' 包间：'.$v['room_name'] .' 机顶盒：'.$v['box_name'].'发送成功';
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
        $forscreenads_id = I('get.id',0,'intval');
        $status = I('get.status',3,'intval');
        $where = array('id'=>$forscreenads_id);
        $m_forscreen_ads = new \Admin\Model\ForscreenAdsModel();
        $res_ads = $m_forscreen_ads->getInfo($where);
        if($res_ads['state']==1){
            $this->output('广告版位正在生成中', 'forscreenadv/advlist',2,0);
        }
        $ret = $m_forscreen_ads->updateData(array('id'=>$forscreenads_id), array('state'=>$status));
        if($status==2){
            $message = '启用成功';
        }elseif($status==3){
            $message = '禁用成功';
        }else{
            $message = '操作成功';
        }
        $this->output($message, 'forscreenadv/advlist',2);
    }

    public function deleteAds(){
        $forscreenads_id = I('get.id','0','intval');
        $where = array('id'=>$forscreenads_id);
        $m_forscreen_ads = new \Admin\Model\ForscreenAdsModel();
        $res_ads = $m_forscreen_ads->getInfo($where);
        if($res_ads['state']==1){
            $this->output('广告版位正在生成中，不能删除，请稍后删除', 'forscreenadv/advlist',2,0);
        }
        $ret = $m_forscreen_ads->updateData(array('id'=>$forscreenads_id), array('state'=>4));
        if($ret){
            $redis = SavorRedis::getInstance();
            $redis->select(12);
            $cache_key_pre = C('SMALLAPP_FORSCREEN_ADS');

            $m_forscreenads_box = new \Admin\Model\ForscreenAdsBoxModel();
            $box_list = $m_forscreenads_box->getBoxArrByForscreenAdsId($forscreenads_id);
            foreach($box_list as $key=>$v){
                $redis->remove($cache_key_pre.$v['box_id']);
            }
            $this->output('删除成功', 'forscreenadv/advlist', 2);
        }else {
            $this->error('删除失败');
        }
    }

}
