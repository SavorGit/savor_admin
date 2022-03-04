<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController;

/**
 * @desc 无效投屏
 *
 */
class InvalidforscreenController extends BaseController {

    public $all_smallapps = array();
    public $all_actions = array();
    public $source_types = array('0'=>'下载成功','1'=>'盒子存在资源','2'=>'下载失败','3'=>'盒子待回执');
    public $all_invalidtypes = array('1'=>'酒楼ID','2'=>'微信openID','3'=>'机顶盒mac','4'=>'红包黑名单用户');
    public $success_status = array('1'=>'成功','2'=>'打断','3'=>'退出','0'=>'失败');

    public function __construct() {
        parent::__construct();
        $this->all_smallapps = C('all_smallapps');
        $this->all_actions = C('all_forscreen_actions');
    }

    public function datalist(){
        $small_app_id = I('small_app_id',0,'intval');
        $is_valid = I('is_valid',1,'intval');
        $is_exist = I('is_exist',99,'intval');
        $action_type = I('action_type',999);
        $category_id = I('category_id',0,'intval');
        $scene_id = I('scene_id',0,'intval');
        $personattr_id = I('personattr_id',0,'intval');
        $dinnernature_id = I('dinnernature_id',0,'intval');
        $contentsoft_id = I('contentsoft_id',0,'intval');
        $spotstatus = I('spotstatus',0,'intval');
        $resource_type = I('resource_type',0,'intval');
        $area_id = I('area_id',0,'intval');
        $size_type = I('size_type',0,'intval');
        $size   = I('numPerPage',50);//显示每页记录数
        $pagenum = I('pageNum',1);
        $order = I('_order','a.id');
        $sort = I('_sort','desc');
        $orders = 'a.create_time desc';
        $start  = ( $pagenum-1 ) * $size;

        $where = array();
//	    $where['a.mobile_brand'] = array('neq','devtools');
        $where['a.mobile_brand'] = array('not in',array('devtools','dev4gtools'));
        if($is_valid!=2){
            $where['a.is_valid'] = $is_valid;
        }
        $create_time = I('create_time','','trim');
        $end_time    = I('end_time','','trim');

        if($create_time && $end_time){
            $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
            $this->assign('create_time',$create_time);
            $this->assign('end_time',$end_time);
        } else if($create_time && empty($end_time)){
            $end_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
            $this->assign('create_time',$create_time);
            $this->assign('end_time',date('Y-m-d'));
        }else if(empty($create_time) && !empty($end_time)){
            $create_time = '2018-07-23';
            $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
            $this->assign('create_time',$create_time);
            $this->assign('end_time',$end_time);
        }else{
            $create_time = date('Y-m-d');
            $end_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
            $this->assign('create_time',$create_time);
            $this->assign('end_time',$end_time);
        }
        $m_category = new \Admin\Model\CategoryModel();
        if($category_id){
            $where_category = array('trees'=>array('like',"%,$category_id,%"));
            $res = $m_category->getDataList('id',$where_category);
            if(count($res)>1){
                $category_ids = array();
                foreach ($res as $v){
                    $category_ids[] = $v['id'];
                }
                $where['a.category_id'] = array('in',$category_ids);
            }else{
                $where['a.category_id'] = $category_id;
            }
        }
        if($resource_type){
            $where['a.resource_type'] = $resource_type;
        }
        if($scene_id){
            $where['a.scene_id'] = $scene_id;
        }
        if($personattr_id){
            $where['_string']="FIND_IN_SET(".$personattr_id.",a.personattr_id)";
        }
        if($dinnernature_id){
            $where['a.dinnernature_id'] = $dinnernature_id;
        }
        if($contentsoft_id){
            $where['a.contentsoft_id'] = $contentsoft_id;
        }
        if($spotstatus){
            $where['a.spotstatus'] = $spotstatus;
        }
        if($size_type){
            $unit_size = 50*1021*1024;
            if($size_type==1){
                $where['a.resource_size'] = array('elt',$unit_size);
            }else{
                $where['a.resource_size'] = array('gt',$unit_size);
            }
        }
        if($is_exist!=99){
            $where['a.is_exist'] = $is_exist;
        }
        if($action_type!=999){
            $action_type_arr = explode('-',$action_type);
            if(count($action_type_arr)==1){
                $where['a.action'] = $action_type;
            }else{
                $where['a.action'] = $action_type_arr[0];
                $where['a.resource_type'] = $action_type_arr[1];
            }
        }
        $hotel_name = I('hotel_name','','trim');
        if($hotel_name){
            $where['a.hotel_name'] = array('like',"%$hotel_name%");
            $this->assign('hotel_name',$hotel_name);
        }
        if($area_id){
            $where['a.area_id'] = $area_id;
            $this->assign('area_id',$area_id);
        }
        if($small_app_id){
            if($small_app_id == 2){
                $where['a.small_app_id'] = array('in',array(2,3));
            }else{
                $where['a.small_app_id'] = $small_app_id;
            }
        }else{
            $where['a.small_app_id'] = array('in',array(1,2,3,11));
        }
        $box_mac    = I('box_mac','','trim');
        if($box_mac){
            $where['a.box_mac'] = $box_mac;
            $this->assign('box_mac',$box_mac);
        }
        $openid = I('openid','','trim');
        if($openid){
            $where['a.openid'] = $openid;
            $this->assign('openid',$openid);
        }

//        else{
//            $forscreen_openids = C('COLLECT_FORSCREEN_OPENIDS');
//            $openids = array_keys($forscreen_openids);
//            $where['a.openid'] = array('not in',$openids);
//        }

        $all_smallapps = $this->all_smallapps;
        $source_types = $this->source_types;
        $all_actions = $this->all_actions;
        $fields = 'user.avatarUrl,user.nickName,a.*';
        $m_smallapp_forscreen_record = new \Admin\Model\Smallapp\ForscreeninvalidrecordModel();
        $list = $m_smallapp_forscreen_record->getList($fields,$where,$orders,$start,$size);

        $m_forscreentrack = new \Admin\Model\Smallapp\ForscreenTrackModel();
        $track_start_time = '2020-01-13 10:20:00';
        $all_box_types = C('hotel_box_type');
        $quality_types = array('1'=>'标清','2'=>'高清','3'=>'原图','0'=>'');

        foreach ($list['list'] as $key=>$v){
            $list['list'][$key]['quality_typestr'] = $quality_types[$v['quality_type']];
            $box_type_str = '';
            if(isset($all_box_types[$v['box_type']])){
                $box_type_str = $all_box_types[$v['box_type']];
            }
            $list['list'][$key]['box_type_str'] = $box_type_str;
            if(isset($all_smallapps[$v['small_app_id']])){
                $list['list'][$key]['small_app'] = $all_smallapps[$v['small_app_id']];
            }else{
                $list['list'][$key]['small_app'] = '';
            }
            $list['list'][$key]['source_typestr'] = $source_types[$v['is_exist']];
            if(!empty($v['resource_size'])){
                $list['list'][$key]['resource_size'] = formatBytes($v['resource_size']);
            }else {
                $list['list'][$key]['resource_size'] = '';
            }
            if(!empty($v['res_sup_time'])){
                $list['list'][$key]['res_sup_time'] = date('Y-m-d H:i:s',intval($v['res_sup_time']/1000)) ;
            }else {
                $list['list'][$key]['res_sup_time'] = '';
            }
            if(!empty($v['res_sup_time']) && !empty($v['res_eup_time'])){
                $list['list'][$key]['res_eup_time'] = ($v['res_eup_time'] - $v['res_sup_time']) /1000 ;
            }else {
                $list['list'][$key]['res_eup_time'] = '';
            }
            if(!empty($v['box_res_sdown_time'])){
                $list['list'][$key]['box_res_sdown_time'] = date('Y-m-d H:i:s',intval($v['box_res_sdown_time']/1000)) ;
            }else {
                $list['list'][$key]['box_res_sdown_time'] = '';
            }
            if(!empty($v['box_res_sdown_time']) && !empty($v['box_res_edown_time'])){
                $list['list'][$key]['box_res_edown_time'] = ($v['box_res_edown_time'] - $v['box_res_sdown_time']) /1000;
            }else {
                $list['list'][$key]['box_res_edown_time'] = '';
            }
            $play_duration = '';
            if(!empty($v['box_play_stime']) && !empty($v['box_play_etime']) && $v['box_play_etime']>$v['box_play_stime']){
                $play_duration = round(($v['box_play_etime'] - $v['box_play_stime']) /1000,2);
                if($play_duration>$v['duration'] && $v['resource_type']!=1){
                    $play_duration = $v['duration'];
                }
            }
            $duration = '';
            if(!empty($v['duration']) && $v['duration']>0){
                $duration = $v['duration'];
            }
            $list['list'][$key]['duration'] = $duration;
            $list['list'][$key]['play_duration'] = $play_duration;

            $is_track = 0;
            if($v['small_app_id']==1 && !in_array($v['action'],array(13,14,21,50,101,120,121,42,43,44,45,52,54))){
                if($v['create_time']>=$track_start_time){
                    $is_track = 1;
                }
            }
            $is_success_str = '';
            $total_time = '';
            if($v['small_app_id']==1){
                $res_track = $m_forscreentrack->getRow('is_success,total_time',array('forscreen_record_id'=>$v['id']));
                if(!empty($res_track)){
                    $is_success = $res_track['is_success'];
                    $is_success_str = $this->success_status[$is_success];
                    $total_time = $res_track['total_time'];
                }
                if($v['is_cancel_forscreen']==1){
                    $is_track = 0;
                    $is_success_str = '取消投屏';
                }
            }elseif($v['small_app_id']==2){
                if(!empty($list['list'][$key]['res_eup_time'])){
                    $total_time = $list['list'][$key]['res_eup_time'];
                    $is_success_str = '成功';
                }
            }
            $list['list'][$key]['is_track'] = $is_track;
            $list['list'][$key]['is_success_str'] = $is_success_str;
            $list['list'][$key]['total_time'] = $total_time;
            $list['list'][$key]['imgs'] = json_decode(str_replace('\\', '', $v['imgs']),true);
            $nowaction_type = $v['action'];
            if($nowaction_type==2){
                $nowaction_type = $nowaction_type.'-'.$v['resource_type'];
            }
            $list['list'][$key]['action_name'] = $all_actions[$nowaction_type];
        }
        $groups = array(1,56);
        $sysuserInfo = session('sysUserInfo');
        $is_lablefiter = 0;
        if(in_array($sysuserInfo['groupid'],$groups)){
            $is_lablefiter = 1;
        }
        unset($all_smallapps[3]);
        $category = $m_category->getCategory($category_id);
        $scene = $m_category->getCategory($scene_id,1,2);
        $personattr = $m_category->getCategory($personattr_id,1,3);
        $dinnernature = $m_category->getCategory($dinnernature_id,1,4);
        $contentsoft = $m_category->getCategory($contentsoft_id,1,5);
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $this->assign('area', $area_arr);
        $this->assign('scene',$scene);
        $this->assign('category',$category);
        $this->assign('personattr',$personattr);
        $this->assign('dinnernature',$dinnernature);
        $this->assign('contentsoft',$contentsoft);
        $this->assign('spotstatus',$spotstatus);
        $this->assign('_sort',$sort);
        $this->assign('_order',$order);
        $this->assign('pageNum',$pagenum);
        $this->assign('numPerPage',$size);
        $this->assign('action_type',$action_type);
        $this->assign('is_exist',$is_exist);
        $this->assign('is_lablefiter',$is_lablefiter);
        $this->assign('all_actions',$all_actions);
        $this->assign('source_types',$source_types);
        $this->assign('resource_type',$resource_type);
        $this->assign('small_apps',$all_smallapps);
        $this->assign('small_app_id',$small_app_id);
        $this->assign('list',$list['list']);
        $this->assign('oss_host',C('OSS_HOST_NEW'));
        $this->assign('page',$list['page']);
        $this->assign('is_valid',$is_valid);
        $this->display('datalist');
    }


}