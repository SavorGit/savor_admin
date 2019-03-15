<?php
namespace Admin\Controller;

use Common\Lib\Page;

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
        $start = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $order = I('_order',' forscreenads.create_time ');
        $sort = I('_sort','desc');
        $tou_state = I('tou_state',0,'intval');

        $tou_arr = C('TOU_STATE');
        unset($tou_arr[3]);
        $now_date = date("Y-m-d");
        $dap = array(
            'now'=>$now_date,
            'tou_st'=>$tou_state,
        );
        
        $where = "forscreenads.state != 2 and forscreenads.is_remove=0";
        if ($name) {
            $this->assign('adsname', $name);
            $where .= " and ads.name like '%".$name."%' ";
        }
        switch ($tou_state){
            case 0:
                $where.=" AND (( forscreenads.end_date >= '$now_date' AND sbox.box_id > 0) or (forscreenads.end_date >= '$now_date' AND forscreenads.type=2))";
                break;
            case 1:
                $where.=" AND forscreenads.start_date >'$now_date' AND sbox.box_id > 0";
                break;
            case 2:
                $where.= " AND forscreenads.start_date <= '$now_date' AND forscreenads.end_date >= '$now_date' AND sbox.box_id > 0";
                break;
            case 3:
                $where.=" AND forscreenads.end_date < '$now_date' AND sbox.box_id > 0";
                break;
            case 4:
                $where .=" AND (sbox.box_id IS NULL OR  sbox.box_id = 0) ";
                break;
        }

        $field = 'ads.name,forscreenads.is_remove,forscreenads.id,forscreenads.ads_id,forscreenads.start_date,forscreenads.end_date, forscreenads.type type,forscreenads.state stap';
        $group = 'forscreenads.id';

        $orders = $order.' '.$sort;
        $start  = ($start-1) * $size;

        $m_forscreen = new \Admin\Model\ForscreenAdsModel();
        $result = $m_forscreen->getList($field, $where,$group, $orders,$start,$size);

        array_walk($result['list'], function(&$v, $k)use($dap){
            $now_date = strtotime( $dap['now']);
            $v['start_date'] = strtotime( $v['start_date'] );
            $v['end_date'] = strtotime( $v['end_date'] );
            $tou_state = $dap['tou_st'];
            if($v['type']==1){
                $v['pub'] = '按版位发布';
                switch ($tou_state){
                    case 0:
                        if($now_date >= $v['start_date'] && $now_date <=$v['end_date']){
                            $v['tp'] = 2;
                            $v['state'] = '投放中';
                        }elseif($now_date < $v['start_date']){
                            $v['tp'] = 1;
                            $v['state'] = '未到投放时间';
                        }else{
                            $v['tp'] = 3;
                            $v['state'] = '投放完毕';
                        }
                        break;
                    case 1:
                        $v['tp'] = 1;
                        $v['state'] = '未到投放时间';
                        break;
                    case 2:
                        $v['tp'] = 2;
                        $v['state'] = '投放中';
                        break;
                    case 3:
                        $v['tp'] = 3;
                        $v['state'] = '投放完毕';
                        break;
                }
                $v['stap'] = '';
            }

            if($v['type']==2){
                $v['pub'] = '按酒楼发布';
                if($v['stap'] == 3){
                    $v['stap'] = '版位计算中';
                    $v['state'] = '';
                }elseif($v['stap'] == 0 || $v['stap'] == 1){
                    $v['stap'] = '可投放';
                    if($tou_state == 2){
                        $v['tp'] = 2;
                        $v['state'] = '投放中';
                    }
                    if($tou_state == 1){
                        $v['tp'] = 1;
                        $v['state'] = '未到投放时间';
                    }
                    if($tou_state == 3){
                        $v['tp'] = 3;
                        $v['state'] = '投放完毕';
                    }
                    if($tou_state == 4){
                        $v['tp'] = 4;
                        $v['stap'] = '不可投放';
                        $where = 'forscreen_ads_id='.$v['id'];
                        $m_forscreen_adsbox = new \Admin\Model\ForscreenAdsBoxModel();
                        $count = $m_forscreen_adsbox->getDataCount($where);
                        if($count <= 0) {
                            $v['stap'] = '不可投放';
                        }
                    }
                    if($tou_state == 0){
                        $where = 'forscreen_ads_id='.$v['id'];
                        $m_forscreen_adsbox = new \Admin\Model\ForscreenAdsBoxModel();
                        $count = $m_forscreen_adsbox->getDataCount($where);
                        if($count <= 0) {
                            $v['stap'] = '不可投放';
                        }elseif( $now_date >= $v['start_date'] && $now_date <=$v['end_date']) {
                            $v['tp'] = 2;
                            $v['state'] = '投放中';
                        } else if ($now_date < $v['start_date'] ) {
                            $v['tp'] = 1;
                            $v['state'] = '未到投放时间';
                        }
                    }
                }
            }
        });
        $retp = $result['list'];
        //判断是否数组分页
        $this->assign('list', $retp);
        $this->assign('page',  $result['page']);
        $this->assign('_sort',$sort);
        $this->assign('_order',$order);
        $this->assign('pageNum',$start);
        $this->assign('to_ar', $tou_arr);
        $this->assign('to_state', $tou_state);
        $this->assign('numPerPage',$size);
        $this->display('advlist');
    }

    public function adddevilery(){
        $m_forscreenads = new \Admin\Model\ForscreenAdsModel();
        if(IS_POST){

            $map['state'] = array(array('eq',3),array('eq',0), 'or') ;
            $field = 'type,state';
            $p_data = $m_forscreenads->getWhere($map,$field);
            if($p_data) {
                foreach( $p_data as $pk=>$pv) {
                    if($pv['state']== 3 && $pv['type'] == 2) {
                        $this->error('当前有广告正在发布，暂时无法添加，请稍后再试');
                    }
                    if($pv['state']== 0 && $pv['type'] == 1) {
                        //判断版位是否不足
                        $this->error('当前有广告正在发布，暂时无法添加，请稍后再试');
                    }
                }
            }
            $h_b_arr = $_POST['hbarr'];
            $h_b_arr = json_decode($h_b_arr, true);

            $now_date = date("Y-m-d H:i:s");
            $now_day = date("Y-m-d");
            $save_data = array();
            $save_data['ads_id'] = I('post.marketid',0,'intval');
            if (empty($save_data['ads_id'])){
                $msg = '上传广告视频失败请重新上传';
                $this->error($msg);
            }
            $save_data['start_date'] = I('post.start_time', '');
            $save_data['end_date'] = I('post.end_time', '');
            $save_data['play_times'] = I('post.play_times', '');
            if($save_data['start_date'] > $save_data['end_date']){
                $msg = '投放开始时间必须小于等于结束时间';
                $this->error($msg);
            }
            if($save_data['start_date'] < $now_day){
                $msg = '投放开始时间必须大于等于今天';
                $this->error($msg);
            }

            //投放类型1机顶盒2酒店
            $screen_type = I('post.screenadv_type','1');
            $del_hall    = I('post.del_hall');  //是否剔除大厅版位
            $userInfo = session('sysUserInfo');
            $save_data['create_time'] = $now_date;
            $save_data['update_time'] = $now_date;
            $save_data['creator_id'] = $userInfo['id'];
            $save_data['state'] = 0;
            $save_data['del_hall'] = $del_hall;
            $oneday_count = 86400;
            //明天
            $save_data['end_date'] = date("Y-m-d H:i:s", strtotime($save_data['end_date']) + $oneday_count-1);
            $save_data['type'] = 1;
            //插入pub_ads表
            $m_forscreenads->startTrans();
            if($screen_type == 2){
                $save_data['state'] = 3;
                $save_data['type'] = 2;
            }
            $save_data['cover_img_media_id'] = I('post.cover_img_media_id',0,'intval');
            $res = $m_forscreenads->addData($save_data);

            $tmp[] = array();
            if($res) {
                if($screen_type == 2) {
                    //插入hotel表
                    $forscreen_ads_id = $m_forscreenads->getLastInsID();
                    $m_forscreenhotel = new \Admin\Model\ForscreenAdsHotelModel();
                    $datp = array();
                    $tmp_hb = array();
                    foreach ($h_b_arr as $k=>$v) {
                        if(array_key_exists($v['hotel_id'], $tmp_hb)){
                            continue;
                        }
                        $tmp_hb[$v['hotel_id']] = 1;
                        $datp[] = array('hotel_id'=>$v['hotel_id'],'forscreen_ads_id'=>$forscreen_ads_id);
                    }
                    $res = $m_forscreenhotel->addAll($datp);
                    if($res) {
                        $m_forscreenads->commit();
                        $this->output('添加成功','advdelivery/getlist');
                    }else {
                        $m_forscreenads->rollback();
                        $this->error('添加失败');
                    }
                } else {
                    //插入box表
                    $forscreen_ads_id = $m_forscreenads->getLastInsID();
                    $m_forscreenbox = new \Admin\Model\ForscreenAdsBoxModel();
                    $tmp_hb = array();
                    foreach($h_b_arr as $k=>$v){
                        if(array_key_exists($v['hotel_id'], $tmp_hb)){
                            continue;
                        }
                        $tmp_hb[$v['hotel_id']] = 1;
                        foreach($v['box_str'] as $rv){
                            if(in_array($rv, $tmp)){
                                continue;
                            }else{
                                $tmp[] = $rv;
                                for($i=0;$i<$save_data['play_times'];$i++) {
                                    $data[] = array(
                                        'create_time'=>$now_date,
                                        'update_time'=>$now_date,
                                        'box_id'=>$rv,
                                        'forscreen_ads_id'=>$forscreen_ads_id,
                                    );
                                }
                            }
                        }
                    }
                    $res = $m_forscreenbox->addAll($data);
                    if($res) {
                        $m_forscreenads->commit();
                        $this->output('添加成功','advdelivery/getlist');
                    }else {
                        $m_forscreenads->rollback();
                        $this->error('添加失败');
                    }
                }
            } else {
                $m_forscreenads->rollback();
                $this->error('添加失败');
            }
        }else{

            $where = array();
            $where['state'] = array(array('eq',3),array('eq',0), 'or') ;
            $field = 'type,state';
            $pb_data = $m_forscreenads->getWhere($where,$field);
            if($pb_data) {
                foreach( $pb_data as $pk=>$pv) {
                    if($pv['state']== 3) {
                        echo '<script>$.pdialog.closeCurrent();  alertMsg.error("有版位在计算中");</script>';
                    }
                }
            }
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
        forscreenads.play_times,ads.NAME adname,ads.duration,med.oss_addr';
        $where = '1=1 and forscreenads.id = '.$adsid;
        $oss_host = $this->oss_host;
        $vinfo = $m_forscreenads->getForscreenAdsInfoByid($field, $where);

        $vinfo['oss_addr'] = $oss_host.$vinfo['oss_addr'];
        $vinfo['start_date'] = date("Y/m/d", strtotime($vinfo['start_date']));
        $vinfo['end_date'] = date("Y/m/d", strtotime($vinfo['end_date']));

        if($vinfo['type']==1){//版位预览
            //获取当前广告选择版位
            $m_forscreen_ads_box = new \Admin\Model\ForscreenAdsBoxModel();
            $map['adbox.forscreen_ads_id'] = $adsid;
            $field = 'sht.id hid,sht.name hname,box.name bname,adbox.box_id';
            $group = 'adbox.box_id';
            $hotel_box_arr = $m_forscreen_ads_box->getCurrentBox($field, $map, $group);
            if ($hotel_box_arr) {
                $hotel_num_arr = array_column($hotel_box_arr,'hid');
                //所有酒店
                $hotel_num = count(array_unique($hotel_num_arr));
                $box_num_arr = array_column($hotel_box_arr,'box_id');
                //所有机顶盒数
                $box_num = count(array_unique($box_num_arr));
            }
            $position_arr = $this->array_group_by($hotel_box_arr, 'hid');

            $this->assign('hottotal',$hotel_num);
            $this->assign('boxtotal',$box_num);
            $this->assign('vinfo',$vinfo);
            $this->assign('pos_ar',$position_arr);
            $this->assign('action_url','advert/editAds');
            $this->display('advpreviewbox');
        }elseif($vinfo['type']==2){//酒楼
            if($vinfo['state'] == 3) {
                //state置为0时就可以不显示发布中
                $this->error('广告正在发布中');
            }
            //获取当前广告发布选择酒楼
            $where = 'adbox.forscreen_ads_id='.$adsid.' and  sht.flag=0 and box.flag=0 and room.flag=0 ';
            $field = 'sht.id hid,box.id bid';
            $m_forscreen_ads_box = new \Admin\Model\ForscreenAdsBoxModel();
            $group = 'adbox.box_id';
            $normal_arr = $m_forscreen_ads_box->getCurrentBox($field, $where, $group);
            $normal_hotel_arr = array_column($normal_arr, 'hid');
            $normal_hotel_arr = array_unique($normal_hotel_arr);
            $normal_hotel_num = count($normal_hotel_arr);
            $normal_box_arr = array_column($normal_arr, 'bid');
            $normal_box_arr = array_unique($normal_box_arr);
            $normal_box_num = count($normal_box_arr);

            $not_hotel_arr = $not_box_arr = array();

            $hotel_arr = array_merge($not_hotel_arr,$normal_hotel_arr);
            $hotel_arr = array_unique($hotel_arr);
            $hotel_num = count($hotel_arr);
            $not_box_num = array_sum($not_box_arr);
            $box_num = $normal_box_num+$not_box_num;
            $not_hotel_num =  $hotel_num - $normal_hotel_num;
            $this->assign('hottotal', $hotel_num);
            $this->assign('boxtotal', $box_num);
            $this->assign('nothotnum', $not_hotel_num);
            $this->assign('notboxnum', $not_box_num);
            $this->assign('normal_hotel', $normal_hotel_num);
            $this->assign('normal_box',$normal_box_num);
            $this->assign('vinfo',$vinfo);
            $this->display('advpreviewhotel');
        }
    }

    public function deleteAds(){
        $forscreenads_id = I('get.id','0','intval');

        $where = array('id'=>$forscreenads_id);
        $where['state'] = array('neq',2);
        $field = 'id,type,state';
        $m_forscreen_ads = new \Admin\Model\ForscreenAdsModel();
        $infos = $m_forscreen_ads->getWhere($where,$field);
        if(empty($infos)){
            $this->error('该广告不存在');
        }
        $info = $infos[0];
        if($info['state'] !=1){
            $this->error('广告版位正在生成中，不能删除，请稍后删除');
        }
        $ret = $m_forscreen_ads->updateData(array('id'=>$forscreenads_id), array('state'=>2));
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

    public function showdetail() {
        $start = I('pageNum',1);
        $size = I('numPerPage',50);//显示每页记录数
        $order = I('_order','id');
        $sort = I('_sort','desc');
        $send_state = I('sendadv_state', '0');
        if(IS_POST) {
            $adsid = I('post.pubhotelid','0','intval');
        } else {
            $adsid = I('deliveryid','0','intval');
        }
        $m_forscreenads = new \Admin\Model\ForscreenAdsModel();
        $field = ' forscreenads.id,forscreenads.start_date,forscreenads.end_date,forscreenads.state state,
        forscreenads.play_times,ads.NAME adname,ads.duration,med.oss_addr';
        $where = 'and forscreenads.id = '.$adsid;
        $oss_host = $this->oss_host;
        $vinfo = $m_forscreenads->getForscreenAdsInfoByid($field,$where);
        if($vinfo['state'] == 3) {
            //state置为0时就可以不显示发布中
            $this->error('广告正在发布中');
        }
        $start  = ( $start-1 ) * $size;
        if($send_state == 0){
            //获取总条数
            $where = 'adhotel.forscreen_ads_id='.$adsid.' and sht.flag=0 and box.flag=0 and room.flag=0 ';
            $field = 'COUNT(DISTINCT box.id) total';
            $pub_ads_hotel_Model = new \Admin\Model\ForscreenAdsHotelModel();
            $group = '';
            $total_arr = $pub_ads_hotel_Model->getCurrentBox($field,$where,$group);
            $count = $total_arr[0]['total'];

        }elseif($send_state == 1){
            //成功
            $field = "sht.name hname,sht.id hid,room.name rname,room.id
            rid, box.id bid,box.name bname, 0 error_type,count(adbox.box_id) boxnum  ";
            $where = 'forscreen_ads_id='.$adsid;
            $order='adbox.id desc';
            $group = 'adbox.box_id';
            $m_forscreen_adsbox = new \Admin\Model\ForscreenAdsBoxModel();
            $normal_box_arr = $m_forscreen_adsbox->getBoxInfoBySize($field, $where, $order,$group, $start, $size);

            if(empty($normal_box_arr['list'])) {
                $count = 0;
            } else {
                $field = " count(DISTINCT box_id) as bnum";
                $where = 'forscreen_ads_id='.$adsid;
                $count = $m_forscreen_adsbox->getWhere($where, $field);
                $count = $count[0]['bnum'];
            }
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $result['list'] = $normal_box_arr['list'];
        }
        $ind = $start+1;
        foreach($result['list'] as &$rv) {
            $rv['ind'] = $ind;
            $rv['error_msg'] = '酒楼：'.$rv['hname'].' 包间：'.$rv['rname'] .' 机顶盒：'.$rv['bname'].'发送成功';
            $ind++;
        }
        $pub_ads_state = array(
            0=>'全部',
            1=>'成功',
            2=>'失败',
        );
        $result['page'] = $show;

        $this->assign('pageNum',$start);
        $this->assign('numPerPage',$size);
        $this->assign('_sort',$sort);
        $this->assign('_order',$order);
        $this->assign('pubadsid', $adsid);
        $this->assign('sendone', $send_state);
        $this->assign('pubhotelstate', $pub_ads_state);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('showdetail');
    }


    public static function array_group_by($arr, $key){
        $grouped = [];
        foreach ($arr as $value) {
            $grouped[$value[$key]][] = $value;
        }
        if (func_num_args() > 2) {
            $args = func_get_args();
            foreach ($grouped as $key => $value) {
                $parms = array_merge([$value], array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func(array('AdvdeliveryController','array_group_by'), $parms);
            }
        }
        return $grouped;
    }



}
