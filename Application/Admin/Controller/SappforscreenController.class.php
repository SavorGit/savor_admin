<?php
namespace Admin\Controller;

class SappforscreenController extends BaseController {

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
	/**
	 * @desc  首页
	 */
	public function index(){
	    $small_app_id = I('small_app_id',0,'intval');
	    $ajaxversion   = I('ajaxversion',0,'intval');//1 版本升级酒店列表
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
	    }else{
            $forscreen_openids = C('COLLECT_FORSCREEN_OPENIDS');
            $openids = array_keys($forscreen_openids);
            $where['a.openid'] = array('not in',$openids);
        }

        $all_smallapps = $this->all_smallapps;
	    $source_types = $this->source_types;
        $all_actions = $this->all_actions;
	    $fields = 'user.avatarUrl,user.nickName,a.*';
	    $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();  
	    $list = $m_smallapp_forscreen_record->getList($fields,$where,$orders,$start,$size);

	    $m_forscreentrack = new \Admin\Model\Smallapp\ForscreenTrackModel();
	    $track_start_time = '2020-01-13 10:20:00';
	    $all_box_types = C('hotel_box_type');
	    $quality_types = array('1'=>'标清','2'=>'高清','3'=>'原图','0'=>'');

	    foreach ($list['list'] as $key=>$v){

            $list['list'][$key]['quality_typestr'] = $quality_types[$v['quality_type']];
	        $is_track = 0;
	        if($v['small_app_id']==1 && !in_array($v['action'],array(13,14,21,50,101,120,121,42,43,44,45,52,54))){
	            if($v['create_time']>=$track_start_time){
                    $is_track = 1;
                }
            }
            $box_type_str = '';
            if(isset($all_box_types[$v['box_type']])){
                $box_type_str = $all_box_types[$v['box_type']];
            }
            $list['list'][$key]['box_type_str'] = $box_type_str;
            $list['list'][$key]['is_track'] = $is_track;
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
	        $is_success_str = '';
	        $total_time = '';
	        if($v['small_app_id']==1){
                $res_track = $m_forscreentrack->getRow('is_success,total_time',array('forscreen_record_id'=>$v['id']));
                if(!empty($res_track)){
                    $is_success = $res_track['is_success'];
                    $is_success_str = $this->success_status[$is_success];
                    $total_time = $res_track['total_time'];
                }
            }elseif($v['small_app_id']==2){
	            if(!empty($list['list'][$key]['res_eup_time'])){
                    $total_time = $list['list'][$key]['res_eup_time'];
                    $is_success_str = '成功';
                }
            }

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
	    $this->display('Report/sappforscreen');
	}

	public function recordedit(){
	    $id = I('id',0,'intval');
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
	    if(IS_POST){
	        $category_id = I('post.category_id',0,'intval');
	        $scene_id = I('post.scene_id',0,'intval');
	        $personattr_ids = I('post.personattr_ids','');
	        $dinnernature_id = I('post.dinnernature_id',0,'intval');
	        $contentsoft_id = I('post.contentsoft_id',0,'intval');
	        $spotstatus = I('post.spotstatus',0,'intval');
            $remark = I('post.remark','','trim');
            $condition = array('id'=>$id);
            $data = array('remark'=>$remark,'category_id'=>$category_id,'scene_id'=>$scene_id,
                'dinnernature_id'=>$dinnernature_id,'contentsoft_id'=>$contentsoft_id,
                'spotstatus'=>$spotstatus);
            if(!empty($personattr_ids)){
                $data['personattr_id'] = join(',',$personattr_ids);
            }
            $m_smallapp_forscreen_record->updateData($condition,$data);
            $this->output('操作成功!', 'Report/sappforscreen');
        }else{
            $all_smallapps = $this->all_smallapps;
            $source_types = $this->source_types;
            $all_actions = $this->all_actions;

            $fields = 'user.avatarUrl,user.nickName,area.region_name,hotel.name hotel_name,room.name room_name,a.*';
            $where = array('a.id'=>$id,'hotel.state'=>1,'hotel.flag'=>0,'box.state'=>1,'box.flag'=>0);
            $vinfo = $m_smallapp_forscreen_record->getInfo($fields,$where);
            $category_id = 0;
            if(!empty($vinfo)){
                $category_id = $vinfo['category_id'];
                $scene_id = $vinfo['scene_id'];
                $personattr_id = $vinfo['personattr_id'];
                $dinnernature_id = $vinfo['dinnernature_id'];
                $contentsoft_id = $vinfo['contentsoft_id'];

                if(isset($all_smallapps[$vinfo['small_app_id']])){
                    $vinfo['small_app'] = $all_smallapps[$vinfo['small_app_id']];
                }else{
                    $vinfo['small_app'] = '';
                }
                $vinfo['source_typestr'] = $source_types[$vinfo['is_exist']];
                if(!empty($vinfo['resource_size'])){
                    $vinfo['resource_size'] = formatBytes($vinfo['resource_size']);
                }else {
                    $vinfo['resource_size'] = '';
                }
                $res_sup_time = $vinfo['res_sup_time'];
                if(!empty($res_sup_time)){
                    $vinfo['res_sup_time'] = date('Y-m-d H:i:s',intval($res_sup_time/1000));
                }else {
                    $vinfo['res_sup_time'] = '';
                }
                if(!empty($res_sup_time) && !empty($vinfo['res_eup_time'])){
                    $vinfo['res_eup_time'] = ($vinfo['res_eup_time'] - $res_sup_time) /1000 ;
                }else {
                    $vinfo['res_eup_time'] = '';
                }
                $box_res_sdown_time = $vinfo['box_res_sdown_time'];
                if(!empty($box_res_sdown_time)){
                    $vinfo['box_res_sdown_time'] = date('Y-m-d H:i:s',intval($box_res_sdown_time/1000)) ;
                }else {
                    $vinfo['box_res_sdown_time'] = '';
                }
                if(!empty($box_res_sdown_time) && !empty($vinfo['box_res_edown_time'])){
                    $vinfo['box_res_edown_time'] = ($vinfo['box_res_edown_time'] - $box_res_sdown_time) /1000;
                }else {
                    $vinfo['box_res_edown_time'] = '';
                }

                $vinfo['imgs'] = json_decode(str_replace('\\', '', $vinfo['imgs']),true);
                $nowaction_type = $vinfo['action'];
                if($nowaction_type==2){
                    $nowaction_type = $nowaction_type.'-'.$vinfo['resource_type'];
                }
                $vinfo['action_name'] = $all_actions[$nowaction_type];
            }
            $m_category = new \Admin\Model\CategoryModel();
            $category = $m_category->getCategory($category_id);
            $scene = $m_category->getCategory($scene_id,1,2);
            $personattr = $m_category->getCategory($personattr_id,1,3);
            $dinnernature = $m_category->getCategory($dinnernature_id,1,4);
            $contentsoft = $m_category->getCategory($contentsoft_id,1,5);

            $this->assign('scene',$scene);
            $this->assign('category',$category);
            $this->assign('personattr',$personattr);
            $this->assign('dinnernature',$dinnernature);
            $this->assign('contentsoft',$contentsoft);
            $this->assign('oss_host',C('OSS_HOST_NEW'));
            $this->assign('vinfo',$vinfo);
            $this->display('Report/recordedit');
        }
    }

    public function invalidlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $type = I('type',0,'intval');

        $m_invalid = new \Admin\Model\ForscreenInvalidlistModel();
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $where = array();
        if($type){
            $where['type'] = $type;
        }
        $res_list = $m_invalid->getDataList('*',$where,$orderby,$start,$size);

        $m_hotel = new \Admin\Model\HotelModel();
        $m_box = new \Admin\Model\BoxModel();
        $m_smallapp_user = new \Admin\Model\Smallapp\UserModel();


        foreach ($res_list['list'] as $k=>$v){
            $res_list['list'][$k]['type_str'] = $this->all_invalidtypes[$v['type']];
            switch ($v['type']){
                case 1:
                    $res_hotel = $m_hotel->getOne($v['invalidid']);
                    $name = $res_hotel['name'];
                    $image = '';
                    break;
                case 2:
                case 4:
                    $res_user = $m_smallapp_user->getOne('openid,avatarUrl,nickName',array('openid'=>$v['invalidid']),'');
                    $name = $res_user['nickname'];
                    $image = $res_user['avatarurl'];
                    break;
                case 3:
                    $res_mac = $m_box->getHotelInfoByBoxMac($v['invalidid']);
                    $name = $res_mac['hotel_name'].'-'.$res_mac['room_name'].'-'.$res_mac['box_name'];
                    $image = '';
                    break;
                default:
                    $name = '';
                    $image = '';
            }
            $res_list['list'][$k]['name'] = $name;
            $res_list['list'][$k]['image'] = $image;
        }
        $this->assign('type',$type);
        $this->assign('types',$this->all_invalidtypes);
        $this->assign('data',$res_list['list']);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display('Report/invalidlist');
    }

    public function invalidadd(){
        if(IS_POST){
            $invalidid = I('post.invalidid','','trim');
            $type = I('post.type',0,'intval');
            $m_invalid = new \Admin\Model\ForscreenInvalidlistModel();
            $condition = array('invalidid'=>$invalidid);
            if($type==4){
                $condition['type']=4;
            }
            $res = $m_invalid->getInfo($condition);
            if(!empty($res)){
                $this->output('数据已存在,请勿重复添加', 'sappforscreen/invalidlist',2,0);
            }

            $data = array('invalidid'=>$invalidid,'type'=>$type);
            $result = $m_invalid->addData($data);
            if($result){
                if($type==4){
                    $key = C('SAPP_REDPACKET').'invaliduser';
                    $redis  =  \Common\Lib\SavorRedis::getInstance();
                    $redis->select(5);
                    $redis->remove($key);
                }
                $this->output('操作成功!', 'sappforscreen/invalidlist');
            }else{
                $this->output('操作失败', 'sappforscreen/invalidlist',2,0);
            }
        }else{
            $this->assign('types',$this->all_invalidtypes);
            $this->display('Report/invalidadd');
        }
    }

    public function invaliddel(){
        $id = I('get.id',0,'intval');
        $type = I('get.type',0,'intval');
        $m_invalid = new \Admin\Model\ForscreenInvalidlistModel();
        $result = $m_invalid->delData(array('id'=>$id));
        if($result){
            if($type==4){
                $key = C('SAPP_REDPACKET').'invaliduser';
                $redis  =  \Common\Lib\SavorRedis::getInstance();
                $redis->select(5);
                $redis->remove($key);
            }
            $this->output('操作成功!', 'sappforscreen/invalidlist',2);
        }else{
            $this->output('操作失败', 'sappforscreen/invalidlist',2,0);
        }
    }

	/**
	 * @desc 删除测试数据
	 */
	public function delTestRecord(){
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $m_smallapp_forscreen_record->cleanTestdata();
	    $this->output('隔离成功', 'sappforscreen/index', 2);
	}


	/**
	 * @desc 互动游戏日志
	 */
	public function gameLog(){
	    $ajaxversion   = I('ajaxversion',0,'intval');//1 版本升级酒店列表
	    $size   = I('numPerPage',50);//显示每页记录数
	    $this->assign('numPerPage',$size);
	    $start = I('pageNum',1);
	    $this->assign('pageNum',$start);
	    $order = I('_order','a.id');
	    $this->assign('_order',$order);
	    $sort = I('_sort','desc');
	    $this->assign('_sort',$sort);
	    $orders = $order.' '.$sort;
	    $start  = ( $start-1 ) * $size;
	    $where = array();
	    $where['box.flag'] = 0;
	    $where['box.state'] =1;
	     
	     
	    $hotel_name = I('hotel_name','','trim');
	    if($hotel_name){
	        $where['hotel.name'] = array('like',"%$hotel_name%");
	        $this->assign('hotel_name',$hotel_name);
	    }
	    $box_mac    = I('box_mac','','trim');
	    if($box_mac){
	        $where['a.box_mac'] = $box_mac;
	        $this->assign('box_mac',$box_mac);
	    }
	    
	    $create_time = I('create_time','','trim');
	    if($create_time){
	        $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$create_time.' 23:59:59'));
	        $this->assign('create_time',$create_time);
	    }
	    
	    $m_turntable_log = new \Admin\Model\Smallapp\TurntableLogModel(); 
	    $fields = 'area.region_name,hotel.name hotel_name,room.name room_name,a.*';
	    
	    $list = $m_turntable_log->getList($fields,$where,$orders,$start,$size);
	    
	    $m_turntable_detail = new \Admin\Model\Smallapp\TurntableDetailModel();
	    foreach($list['list'] as $key=>$v){
	        if(!empty($v['orggame_time'])){
	            $list['list'][$key]['orggame_time'] = date('Y-m-d H:i:s',intval($v['orggame_time']/1000));
	        }else {
	            $list['list'][$key]['orggame_time'] = '';
	        }
	        if(!empty($v['orggame_time']) && !empty($v['box_orggame_time'])){
	            $list['list'][$key]['box_orggame_time'] = ($v['box_orggame_time'] - $v['orggame_time']) /1000;
	        }else {
	            $list['list'][$key]['box_orggame_time'] = '';
	        }
	        if(!empty($v['startgame_time'])){
	            $list['list'][$key]['startgame_time'] = date('Y-m-d H:i:s',intval($v['startgame_time']/1000));
	        }else {
	            $list['list'][$key]['startgame_time'] = '';
	        }
	        if(!empty($v['startgame_time']) && !empty($v['box_startgame_time'])){
	            $list['list'][$key]['box_startgame_time'] = ($v['box_startgame_time'] - $v['startgame_time']) /1000;
	        }else {
	            $list['list'][$key]['box_startgame_time'] = '';
	        }
	        
	        $nums = $m_turntable_detail->countNums(array('activity_id'=>$v['activity_id']));
	        $list['list'][$key]['nums']= $nums+1;
	    }
	    
	    $this->assign('list',$list['list']);
	    $this->assign('page',$list['page']);
	    $this->display('Report/turntablelog');
	}

	public function trackinfo(){
        $forscreen_record_id = I('get.id',0,'intval');
	    $m_track = new \Admin\Model\Smallapp\ForscreenTrackModel();
        $track_info = $m_track->getForscreenTrack($forscreen_record_id);
        if(!empty($track_info)){
            $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
            $res_forscreen = $m_forscreen->getInfo(array('id'=>$forscreen_record_id));

            if($track_info['oss_stime']){
                $oss_begintime = date('Y-m-d H:i:s',round($track_info['oss_stime']/1000));
                $oss_timeconsume = ($track_info['oss_etime']-$track_info['oss_stime'])/1000;
            }else{
                $oss_begintime = '无上传动作';
                $oss_timeconsume = '';
            }

            if($res_forscreen['action']==30){
                if($track_info['box_downstime']){
                    $box_downstime = date('Y-m-d H:i:s',round($track_info['box_downstime']/1000));
                }else{
                    $box_downstime = '';
                }
                if($track_info['box_downetime']){
                    $box_downetime = date('Y-m-d H:i:s',round($track_info['box_downetime']/1000));
                }else{
                    $box_downetime = '';
                }

                $track_info['status'] = $track_info['is_success']==1?'成功':'失败';
                $track_info['all_timeconsume'] = $track_info['total_time'];

                if($track_info['box_downstime'] && $track_info['box_downetime']){
                    $box_down_timeconsume = ($track_info['box_downetime']-$track_info['box_downstime'])/1000;
                }else{
                    $box_down_timeconsume = '';
                }
                $track_info['oss_begintime'] = $oss_begintime;
                $track_info['oss_timeconsume'] = $oss_timeconsume;
                $track_info['box_downstime'] = $box_downstime;
                $track_info['box_downetime'] = $box_downetime;
                $track_info['box_down_timeconsume'] = $box_down_timeconsume;

                $display_html = 'trackfile';
            }else{
                $netty_position_stime = date('Y-m-d H:i:s',round($track_info['position_nettystime']/1000));
                if($track_info['request_nettytime']){
                    $netty_position_timeconsume = ($track_info['request_nettytime']-$track_info['position_nettystime'])/1000;
                    $netty_stime = date('Y-m-d H:i:s',round($track_info['request_nettytime']/1000));
                }else{
                    $netty_position_timeconsume = '';
                    $netty_stime = '';
                }

                if($track_info['netty_receive_time']){
                    $netty_rtime = date('Y-m-d H:i:s',round($track_info['netty_receive_time']/1000));
                }else{
                    $netty_rtime = '';
                }

                if($track_info['netty_pushbox_time']){
                    $pushbox_time = date('Y-m-d H:i:s',round($track_info['netty_pushbox_time']/1000));
                    if(!empty($track_info['netty_pushbox_chaid'])){
                        $pushbox_time.=" 通道号：{$track_info['netty_pushbox_chaid']}";
                    }
                }else{
                    $pushbox_time = '';
                }
                if($track_info['netty_callback_time'] && $track_info['netty_receive_time']){
                    $netty_timeconsume = ($track_info['netty_callback_time']-$track_info['netty_receive_time'])/1000;
                }elseif($track_info['netty_receive_time'] && $track_info['netty_pushbox_time']){
                    $netty_timeconsume = ($track_info['netty_pushbox_time']-$track_info['netty_receive_time'])/1000;
                }else{
                    $netty_timeconsume = '';
                }

                if($track_info['box_receivetime']){
                    $box_rtime = date('Y-m-d H:i:s',round($track_info['box_receivetime']/1000));
                }else{
                    $box_rtime = '';
                }
                if($track_info['box_downstime']){
                    $box_downstime = date('Y-m-d H:i:s',round($track_info['box_downstime']/1000));
                }else{
                    $box_downstime = '';
                }
                if($track_info['box_downetime']){
                    $box_downetime = date('Y-m-d H:i:s',round($track_info['box_downetime']/1000));
                }else{
                    $box_downetime = '';
                }
                if($track_info['box_receivetime'] && $track_info['box_downstime'] && $track_info['box_downetime']){
                    $box_down_timeconsume = ($track_info['box_downetime']-$track_info['box_downstime'])/1000;
                }else{
                    $box_down_timeconsume = '';
                }
                if(!empty($track_info['netty_position_result'])){
                    $netty_position_result = json_decode($track_info['netty_position_result'],true);
                    $netty_position_result_str = '';
                    foreach ($netty_position_result as $k=>$v){
                        $netty_position_result_str.="$k:$v|";
                    }
                    $track_info['netty_position_result'] = rtrim($netty_position_result_str,'|');
                }

                if(!empty($track_info['netty_result'])){
                    $netty_result = json_decode($track_info['netty_result'],true);
                    $netty_result_str = '';
                    foreach ($netty_result as $k=>$v){
                        $netty_result_str.="$k:$v|";
                    }
                    $track_info['netty_result'] = rtrim($netty_result_str,'|');
                }
                if(!empty($track_info['netty_callback_result'])){
                    $netty_callback_result = json_decode($track_info['netty_callback_result'],true);
                    $netty_callback_result_str = '';
                    foreach ($netty_callback_result as $k=>$v){
                        if($k!='result'){
                            $netty_callback_result_str.="$k:$v|";
                        }
                    }
                    $track_info['netty_callback_result'] = rtrim($netty_callback_result_str,'|');
                }
                if($track_info['netty_callback_time']){
                    $netty_callback_time = date('Y-m-d H:i:s',round($track_info['netty_callback_time']/1000));
                    if(!empty($track_info['netty_callback_chaid'])){
                        $netty_callback_time.=" 通道号：{$track_info['netty_callback_chaid']}";
                    }
                }else{
                    $netty_callback_time = '';
                }
                $is_play = 0;
                /*
                if($track_info['resource_type']==1){
                    $m_hearlog = new \Admin\Model\HeartAllLogModel();
                    $date = date('Ymd');
                    $res = $m_hearlog->getOne($track_info['box_mac'],2,$date);
                    if(!empty($res) && $res['apk_version']>='2.1.0'){
                        $is_play = 1;
                    }
                }
                */

                $is_success = $track_info['is_success'];
                $track_info['status'] = $this->success_status[$is_success];
                $track_info['all_timeconsume'] = $track_info['total_time'];

                $track_info['netty_position_stime'] = $netty_position_stime;
                $track_info['netty_position_timeconsume'] = $netty_position_timeconsume;
                $track_info['netty_stime'] = $netty_stime;
                $track_info['netty_rtime'] = $netty_rtime;
                $track_info['pushbox_time'] = $pushbox_time;
                $track_info['netty_callback_time'] = $netty_callback_time;
                $track_info['netty_timeconsume'] = $netty_timeconsume;
                $track_info['box_rtime'] = $box_rtime;
                $track_info['box_downstime'] = $box_downstime;
                $track_info['oss_begintime'] = $oss_begintime;
                $track_info['oss_timeconsume'] = $oss_timeconsume;
                $track_info['box_downetime'] = $box_downetime;
                $track_info['box_down_timeconsume'] = $box_down_timeconsume;
                $track_info['box_mac'] = $res_forscreen['box_mac'];
                $track_info['box_play_time'] = $track_info['box_play_time'];
                $track_info['is_play'] = $is_play;

                $display_html = 'trackinfo';
            }
        }else{
            $display_html = 'trackinfo';
        }
        $this->assign('info',$track_info);
        $this->display("Report/$display_html");
    }

	/**
	 * @desc 查看参加游戏的详细数据
	 */
	public function detail(){
	    $activity_id = I('get.activity_id');
	    $m_turntable_log = new \Admin\Model\Smallapp\TurntableLogModel();
	    
	    $fields = "openid,mobile_brand,mobile_model,'发起人' as `person_type`, '0' as join_time, '0' as box_join_time";
	    $where = array();
	    $where['activity_id'] = $activity_id;
	    $fq_info = $m_turntable_log->getOne($fields, $where);
	    
	    $m_turntable_detail = new \Admin\Model\Smallapp\TurntableDetailModel();
	    $fields = "openid,mobile_brand,mobile_model,'参与人' as `person_type`,join_time,box_join_time";
	    $cy_info = $m_turntable_detail->getWhere($fields,$where);
	    
	    array_unshift($cy_info, $fq_info);
	    foreach($cy_info as $key=>$v){
	        if(!empty($v['join_time'])){
	            $cy_info[$key]['join_time'] = date('Y-m-d H:i:s',intval($v['join_time']/1000));
	        }else {
	            $cy_info[$key]['join_time'] = '';
	        }
	        if(!empty($v['join_time']) && !empty($v['box_join_time'])){
	            $cy_info[$key]['box_join_time'] = ($v['box_join_time'] - $v['join_time']) /1000;
	        }else {
	            $cy_info[$key]['box_join_time'] = '';
	        }
	    }
	    
	    $this->assign('list',$cy_info);
	    
	    $this->display('Report/turtbdetail');
	}
	
	
	/**
	 * @desc 删除永峰测试数据
	 */
	public function delTestGamelog(){
	    $hotel_id = array(7,791);
	    $fields = "a.box_mac";
	    $where = array();
	    $where['hotel.id'] = array('in',$hotel_id);
	    $group = 'a.box_mac';
	    $m_turntable_log = new \Admin\Model\Smallapp\TurntableLogModel(); 
	    $list = $m_turntable_log->getWhere($fields, $where,  $limit='', $group);
	    //echo $m_smallapp_forscreen_record->getLastSql();exit;
	    //print_r($list);exit;   
	    foreach($list as $key=>$v){
	        $where = array();
	        $where['box_mac'] = $v['box_mac'];
	        $m_turntable_log->delWhere($where, $order='', $limit='');
	        //$m_smallapp_forscreen_record->delWhere($where, $order='', $limit='');
	    }
	    $this->output('删除成功', 'sappforscreen/gamelog', 2);
	}
    /**
     * @desc 小程序码显示日志
     */
	public function suncodeLog(){
	    $ajaxversion   = I('ajaxversion',0,'intval');//1 版本升级酒店列表
	    $size   = I('numPerPage',50);//显示每页记录数
	    $this->assign('numPerPage',$size);
	    $start = I('pageNum',1);
	    $this->assign('pageNum',$start);
	    $order = I('_order','a.id');
	    $this->assign('_order',$order);
	    $sort = I('_sort','desc');
	    $this->assign('_sort',$sort);
	    $orders = $order.' '.$sort;
	    $start  = ( $start-1 ) * $size;
	    $where = array();
	    $where['box.flag'] = 0;
	    $where['box.state'] =1;
	     
	     
	    $hotel_name = I('hotel_name','','trim');
	    if($hotel_name){
	        $where['hotel.name'] = array('like',"%$hotel_name%");
	        $this->assign('hotel_name',$hotel_name);
	    }
	    $box_mac    = I('box_mac','','trim');
	    if($box_mac){
	        $where['a.box_mac'] = $box_mac;
	        $this->assign('box_mac',$box_mac);
	    }
	    
	    $create_time = I('create_time','','trim');
	    if($create_time){
	        $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$create_time.' 23:59:59'));
	        $this->assign('create_time',$create_time);
	    }
	    
	    $fields = 'area.region_name,hotel.name hotel_name,room.name room_name,media.name media_name,media.duration,a.*';
	    $m_suncode_log = new \Admin\Model\Smallapp\SuncodeLogModel();
	    $list = $m_suncode_log->getList($fields,$where,$orders,$start,$size);
	    
	    $this->assign('list',$list['list']);
	    $this->assign('page',$list['page']);
	    $this->display('Report/suncodelog');
	}
	public function delSuncodeLog(){
	    $hotel_id = array(7,791);
	    $fields = "a.box_mac";
	    $where = array();
	    $where['hotel.id'] = array('in',$hotel_id);
	    $group = 'a.box_mac';
	    $m_suncode_log = new \Admin\Model\Smallapp\SuncodeLogModel();
	    $list = $m_suncode_log->getWhere($fields, $where,  $limit='', $group);
	    
	    foreach($list as $key=>$v){
	        $where = array();
	        $where['box_mac'] = $v['box_mac'];
	        $m_suncode_log->delWhere($where, $order='', $limit='');
	        
	    }
	    $this->output('删除成功', 'sappforscreen/suncodeLog', 2);
	}
	/**
	 * @desc 小程序网络状况监测
	 */
	public function staticnet(){
	    $ajaxversion   = I('ajaxversion',0,'intval');//1 版本升级酒店列表
	    $size   = I('numPerPage',50);//显示每页记录数
	    $this->assign('numPerPage',$size);
	    $start = I('pageNum',1);
	    $this->assign('pageNum',$start);
	    $order = I('_order','a.hotel_id');
	    $this->assign('_order',$order);
	    $sort = I('_sort','asc');
	    $this->assign('_sort',$sort);
	    $orders = $order.' '.$sort;
	    $start  = ( $start-1 ) * $size;
	    
	    $m_static_net = new \Admin\Model\Smallapp\StaticNetModel();
	    $fields = "a.id,a.hotel_id,hotel.name hotel_name,area.region_name";
	    $where =array();
	    $yesterday = date('Y-m-d',strtotime('-1 day'));
	    $start_date = I('start_date','','trim') ? I('start_date','','trim') : $yesterday;
	    $hotel_name = I('hotel_name','','trim');
	    $end_date   = I('end_date','','trim') ? I('end_date','','trim') : $yesterday;
	    $is_4g      = I('is_4g','0','intval');
	    $where['a.static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
	    if($hotel_name) $where['hotel.name'] = array('like',"%$hotel_name%");
	    if(!empty($is_4g)){
	        $where['hotel.is_4g'] = $is_4g;
	        
	    }
	    $group = "a.hotel_id";
	    
	    $hotel_list = $m_static_net->getWhere($fields,$where,$orders,$group,$start,$size);

	    foreach($hotel_list['list'] as $key=>$v){
	        $map = array();
	        $map['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
	        $map['hotel_id'] = $v['hotel_id'];
	        $fields = 'sum(`box_donw_nums`) box_donw_nums,sum(`res_size`) res_size,
	                   sum(`order_times`) order_times,sum(`avg_down_speed`) avg_down_speed,
	                   sum(`avg_delay_time`) avg_delay_time,max(`max_down_speed`) max_down_speed,
	                   min(`min_down_speed`) min_down_speed,max(`max_delay_times`) max_delay_times,
	                   min(`min_delay_times`) min_delay_times';
	        $ret  = $m_static_net->searchList($fields, $map);
	        $nums = $m_static_net->countWhere($map);
	        $hotel_list['list'][$key]['box_down_nums'] = $ret[0]['box_donw_nums'];              //总下载次数
	        $hotel_list['list'][$key]['res_sizev']     = formatBytes($ret[0]['res_size']);      //总资源大小
	        $hotel_list['list'][$key]['order_times']   = $ret[0]['order_times'];                //总质量次数
	        
	        $hotel_list['list'][$key]['avg_down_speed']= formatBytes($ret[0]['avg_down_speed'] / $nums).'/S';
	        $hotel_list['list'][$key]['avg_delay_time']= $ret[0]['avg_delay_time'] /$nums;
	        $hotel_list['list'][$key]['max_down_speed']= formatBytes($ret[0]['max_down_speed']) .'/S';
	        $hotel_list['list'][$key]['min_down_speed']= formatBytes($ret[0]['min_down_speed']) .'/S';
	        $hotel_list['list'][$key]['max_delay_times']= $ret[0]['max_delay_times'];
	        $hotel_list['list'][$key]['min_delay_times']= $ret[0]['min_delay_times'];
	        
	        
	    }
	    $this->assign('list',$hotel_list['list']);
	    $this->assign('page',$hotel_list['page']);
	    $this->assign('start_date',$start_date);
	    $this->assign('end_date',$end_date);
	    $this->assign('hotel_name',$hotel_name);
	    $this->assign('is_4g',$is_4g);
	    $this->display('Report/staticnet');
	}
	/**
	 * @desc 小程序数据统计
	 */
	public function interactStatic(){
	    $size   = I('numPerPage',50);//显示每页记录数
	    $this->assign('numPerPage',$size);
	    
	    $start = I('pageNum',1);
	    $pageNum = $start;
	    $this->assign('pageNum',$start);
	    $order = I('_order','a.hotel_id');
	    $this->assign('_order',$order);
	    $sort = I('_sort','asc');
	    $this->assign('_sort',$sort);
	    $orders = $order.' '.$sort;
	    $start  = ( $start-1 ) * $size;
	    
	    $where = array();
	    $start_date = I('start_date') ? I('start_date')." 00:00:00" : date('Y-m-d 00:00:00',strtotime('-1 day'));
	    $end_date   = I('end_date')   ? I('end_date')." 23:59:59"   : date('Y-m-d 23:59:59',strtotime('-1 day'));
	    $area_id    = I('area_id',0,'intval');
	    if($area_id){
	        $where['area.id'] = $area_id;
	    }
	    
	    //获取酒楼
	    $m_forscreen_record = new \Admin\Model\Smallapp\ForscreenRecordModel(); 
	    $fileds = "hotel.id hotel_id,area.region_name ,hotel.name hotel_name,0 as `tstype`";
	    
	    
	    $where['a.create_time'] = array(array('EGT',$start_date),array('ELT',$end_date));
	    $where['hotel.state'] = 1;
	    $where['hotel.flag']  = 0;
	    $where['box.state']   = 1;
	    $where['box.flag']    = 0;
	    $where['a.mobile_brand'] = array('neq','devtools');
	    $group = "hotel.id";
	    
	    $list = $m_forscreen_record->getStaticList($fileds, $where, $order, $group, $start, $size,$pageNum,$area_id);
	    
	    
	    $m_box = new \Admin\Model\BoxModel();
	    $m_heart_all_log = new \Admin\Model\HeartAllLogModel();
	    $m_turntale_log = new \Admin\Model\Smallapp\TurntableLogModel();
	    $m_turntale_detail = new \Admin\Model\Smallapp\TurntableDetailModel();
	    $m_static_net = new \Admin\Model\Smallapp\StaticNetModel();
	    foreach($list['list'] as $key=>$v){
	        //总屏幕数
	        $map = array();
	        $map['hotel.id']    = $v['hotel_id'];
	        $map['hotel.state'] = 1;
	        $map['hotel.flag']  = 0;
	        $map['box.state']   = 1;
	        $map['box.flag']    = 0;
	        $box_nums = $m_box->countNums($map);
	        $list['list'][$key]['all_num'] = $box_nums;
	        
	        //获取当前酒楼所有的盒子
	        
	        $map =" 1 and h.id=".$v['hotel_id']." and h.state=1 and h.flag=0 and b.state=1 and b.flag=0"; 
	       
	        $box_list = $m_box->isHaveMac('b.mac box_mac', $map);
	        
	        //获取当前酒楼的平均网速
	        $map = array();
	        $map['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
	        $map['hotel_id'] = $v['hotel_id'];
	        $fields = 'sum(`avg_down_speed`) avg_down_speed';
	        $ret  = $m_static_net->searchList($fields, $map);
	        $nums = $m_static_net->countWhere($map);
	        $avg_speed = $ret[0]['avg_down_speed'] / 1000 / $nums;
	        
	        
	        $lunch_online_box_num = 0;     //上午在线屏幕数
	        $dinner_online_box_num = 0;    //下午在线屏幕数
	        
	        $lunch_can_forscreen_box_num = 0;  //上午可投屏总数
	        $dinner_can_forscreen_box_num = 0; //下午可投屏总数
	        $s_dt= date('Ymd',strtotime($start_date));
	        $e_dt = date('Ymd',strtotime($end_date));
	        foreach($box_list as $kk=>$vv){
	            $sql ="select sum(hour2+hour3+hour4+hour5+hour6+hour7+hour8+hour9+hour10+hour11+hour12+hour13+hour14) as lunch_heart_num
	                   from savor_heart_all_log where  date>='".$s_dt
	                   ."' and date<='".$e_dt."' and mac='".$vv['box_mac']."' and type=2";
	            $ret = M()->query($sql);
	            $nums = intval($ret[0]['lunch_heart_num']);
	            if(!empty($nums)) $lunch_online_box_num ++;                           //午饭在线屏幕数
	            if($avg_speed>200 && !empty($nums)) $lunch_can_forscreen_box_num ++;  //午饭可投屏总数
	            
	            
	            
	            $sql ="select sum(hour15+hour16+hour17+hour18+hour19+hour20+hour21+hour22+hour23+hour0+hour1) as dinner_heart_num
	                   from savor_heart_all_log where  date>='".$s_dt
	                   ."' and date<='".$e_dt."' and mac='".$vv['box_mac']."' and type=2";
	            $ret = M()->query($sql);
	            $nums = intval($ret[0]['dinner_heart_num']);
	            if(!empty($nums)) $dinner_online_box_num ++;                          //晚饭在线屏幕数
	            
	            if($avg_speed>200 &&!empty($nums)) $dinner_can_forscreen_box_num++;   //晚饭可投屏总数
	            
	            
	        }
	        if($v['tstype']==0){
	            $lunch_interact_mobile_nums  = 0;                                         //上午互动手机数
	            $dinner_interact_mobile_nums = 0;                                         //下午互动手机数
	             
	            //午饭互动手机
	            $sql = "select a.id,a.openid from savor_smallapp_forscreen_record a
	                left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15
	                and a.mobile_brand!='devtools' group by a.openid";
	            $ret_f = M()->query($sql);
	            //$lunch_interact_mobile_nums += count($ret);
	            $sql = "select a.id,a.openid from savor_smallapp_turntable_log a
                    left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15
	                group by a.openid";
	            $ret_t = M()->query($sql);
	            //$lunch_interact_mobile_nums += count($ret);
	            $sql ="select a.id,a.openid from savor_smallapp_turntable_detail a
                    left join savor_smallapp_turntable_log b on a.activity_id=b.activity_id
                    left join savor_box box on b.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15
	                group by openid";
	            $ret_td = M()->query($sql);
	            $ret = array_merge($ret_f,$ret_t,$ret_td);
	            if(empty($ret)){
	                $lunch_interact_mobile_nums = 0;
	            }else {
	                $ret = assoc_unique($ret, 'openid');
	                $lunch_interact_mobile_nums = count($ret);
	            }
	             
	             
	            //晚饭互动手机
	            $sql = "select a.id,a.openid from savor_smallapp_forscreen_record a
	                left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))
	                and a.mobile_brand!='devtools' group by a.openid";
	            $ret_f = M()->query($sql);
	            //$dinner_interact_mobile_nums += count($ret);
	            $sql = "select a.id,a.openid from savor_smallapp_turntable_log a
                    left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))
	                group by a.openid";
	            $ret_t = M()->query($sql);
	            //$dinner_interact_mobile_nums += count($ret);
	            $sql ="select a.id,a.openid from savor_smallapp_turntable_detail a
                    left join savor_smallapp_turntable_log b on a.activity_id=b.activity_id
                    left join savor_box box on b.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))
	                group by openid";
	            $ret_td = M()->query($sql);
	            $ret = array_merge($ret_f,$ret_t,$ret_td);
	            if(empty($ret)){
	                $dinner_interact_mobile_nums = 0;
	            }else {
	                $ret = assoc_unique($ret, 'openid');
	                $dinner_interact_mobile_nums = count($ret);
	            }
	             
	            //互动饭局数
	            $lunch_fanju_num = 0;   //午饭总饭局数
	            $dinner_fanju_num =0;   //晚饭总饭局数
	             
	            //总互动次数
	            $lunch_interact_num = 0 ;         //午饭总互动次数
	            $dinner_interact_num = 0;         //晚饭总互动次数
	             
	            $lunch_pic_num      = 0;
	            $lunch_video_num    = 0;
	            $lunch_db_num       = 0;
	            $lunch_birthday_num = 0;
	            $lunch_game_num     = 0;
	            
	            $dinner_pic_num      = 0;
	            $dinner_video_num    = 0;
	            $dinner_db_num       = 0;
	            $dinner_birthday_num = 0;
	            $dinner_game_num     = 0;
	             
	            $sql = "select a.action,a.resource_type,forscreen_char from savor_smallapp_forscreen_record a
	                left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15
	                and a.mobile_brand!='devtools'";
	            $ret = M()->query($sql);
	            
	             
	            foreach($ret as $rk=>$rv){
	                if(($rv['action']==2 && $rv['resource_type']==1) || $rv['action']==4 ){//图片
	                    $lunch_pic_num ++;
	                }else if($rv['action']==2 && $rv['resource_type']==2){//视频
	                    $lunch_video_num ++;
	                }else if($rv['action']==5 && $rv['forscreen_char']!='Happy Birthday'){
	                    $lunch_db_num ++;
	                }else if($rv['action']==5 && $rv['forscreen_char'] =='Happy Birthday'){
	                    $lunch_birthday_num ++;
	                }
	            }
	             
	            $lunch_interact_num += $lunch_pic_num +$lunch_video_num +$lunch_db_num+$lunch_birthday_num;
	             
	            $sql = "select count(a.id) as nums from savor_smallapp_turntable_log a
                    left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15";
	            $ret = M()->query($sql);
	            $lunch_game_num += $ret[0]['nums'];
	            $lunch_interact_num +=$ret[0]['nums'];
	             
	            $sql ="select count(a.id) as nums from savor_smallapp_turntable_detail a
                    left join savor_smallapp_turntable_log b on a.activity_id=b.activity_id
                    left join savor_box box on b.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15";
	            $ret = M()->query($sql);
	            $lunch_game_num +=$ret[0]['nums'];
	            $lunch_interact_num +=$ret[0]['nums'];
	            
	            //午饭投屏电视数
	            $lunch_forscreen_box_num = 0;
	            //晚饭投屏电视数
	            $dinner_forscreen_box_num= 0;
	            
	            $sql = "select a.id,a.box_mac from savor_smallapp_forscreen_record a
	                left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15
	                and a.mobile_brand !='devtools'
	                group by a.box_mac";
	            $ret_f = M()->query($sql);
	             
	            //$lunch_forscreen_box_num += count($ret);
	            $sql = "select a.id,a.box_mac from savor_smallapp_turntable_log a
                left join savor_box box on a.box_mac=box.mac
                left join savor_room room on box.room_id=room.id
                left join savor_hotel hotel on room.hotel_id=hotel.id
                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
                and SUBSTRING(a.create_time,12,2)>=2 and SUBSTRING(a.create_time,12,2)<15
                group by a.box_mac";
	            $ret_t = M()->query($sql);
	             
	            $ret = array_merge($ret_f,$ret_t);
	             
	            if(empty($ret)){
	                $lunch_forscreen_box_num =0;
	            }else {
	                 
	                $ret = assoc_unique($ret, 'box_mac');
	                $lunch_forscreen_box_num = count($ret);
	            }
	            $lunch_fanju_num = $lunch_forscreen_box_num;
	            
	            $sql = "select a.id,a.box_mac from savor_smallapp_forscreen_record a
	                left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))
	                and a.mobile_brand !='devtools'
	                group by a.box_mac";
	            $ret_f = M()->query($sql);
	             
	            $dinner_forscreen_box_num += count($ret);
	            $sql = "select a.id,a.box_mac from savor_smallapp_turntable_log a
                left join savor_box box on a.box_mac=box.mac
                left join savor_room room on box.room_id=room.id
                left join savor_hotel hotel on room.hotel_id=hotel.id
                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))
                group by a.box_mac";
	            $ret_t = M()->query($sql);
	            $ret = array_merge($ret_f,$ret_t);
	             
	            if(empty($ret)){
	                $dinner_forscreen_box_num = 0;
	            }else {
	                $ret = assoc_unique($ret, 'box_mac');
	                $dinner_forscreen_box_num = count($ret);
	            }
	            $dinner_fanju_num =  $dinner_forscreen_box_num;
	            $sql = "select a.action,a.resource_type,forscreen_char from savor_smallapp_forscreen_record a
	                left join savor_box box on a.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
	                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
	                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))
	                and a.mobile_brand!='devtools'";
	             
	            $ret = M()->query($sql);
	             
	            foreach($ret as $rk=>$rv){
	                if(($rv['action']==2 && $rv['resource_type']==1) || $rv['action']==4 ){//图片
	                    $dinner_pic_num ++;
	                }else if($rv['action']==2 && $rv['resource_type']==2){//视频
	                    $dinner_video_num ++;
	                }else if($rv['action']==5 && $rv['forscreen_char']!='Happy Birthday'){
	                    $dinner_db_num ++;
	                }else if($rv['action']==5 && $rv['forscreen_char'] =='Happy Birthday'){
	                    $dinner_birthday_num ++;
	                }
	            }
	            $dinner_interact_num += $dinner_pic_num+$dinner_video_num +$dinner_db_num+$dinner_birthday_num;
	             
	            $sql = "select count(a.id) as nums from savor_smallapp_turntable_log a
                left join savor_box box on a.box_mac=box.mac
                left join savor_room room on box.room_id=room.id
                left join savor_hotel hotel on room.hotel_id=hotel.id
                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
                and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
                and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))";
	            $ret = M()->query($sql);
	            $dinner_game_num +=$ret[0]['nums'];
	            $dinner_interact_num +=$ret[0]['nums'];
	             
	             
	            $sql ="select count(a.id) as nums from savor_smallapp_turntable_detail a
                    left join savor_smallapp_turntable_log b on a.activity_id=b.activity_id
                    left join savor_box box on b.box_mac=box.mac
	                left join savor_room room on box.room_id=room.id
	                left join savor_hotel hotel on room.hotel_id=hotel.id
	                where box.flag=0 and box.state= 1 and hotel.id=".$v['hotel_id']."
                    and a.create_time>='".$start_date."' and a.create_time<='".$end_date."'
                    and ((SUBSTRING(a.create_time,12,2)>=15 and SUBSTRING(a.create_time,12,2)<23) or (SUBSTRING(a.create_time,12,2)>=0 and SUBSTRING(a.create_time,12,2)<2))";
	            $ret = M()->query($sql);
	            $dinner_game_num +=$ret[0]['nums'];
	            $dinner_interact_num +=$ret[0]['nums'];
	             
	            //午饭/晚饭饭局转换率
	            if(empty($lunch_can_forscreen_box_num)) $lunch_fanju_rate = 0;
	            else $lunch_fanju_rate = round($lunch_fanju_num / $lunch_can_forscreen_box_num,2);
	             
	            if(empty($dinner_can_forscreen_box_num)) $dinner_fanju_rate = 0;
	            else $dinner_fanju_rate= round($dinner_fanju_num / $dinner_can_forscreen_box_num,2);
	            
	            //午饭/晚饭人员参与率
	            if(empty($lunch_can_forscreen_box_num)) $lunch_join_rate = 0;
	            else  $lunch_join_rate = round($lunch_interact_num /($lunch_can_forscreen_box_num*8),2);
	             
	            if(empty($dinner_can_forscreen_box_num)) $dinner_join_rate =0;
	            else $dinner_join_rate = round($dinner_interact_num /($dinner_can_forscreen_box_num*8),2);
	             
	            //午饭/晚饭牵引率
	            if(empty($lunch_forscreen_box_num))$lunch_pullability_rate = 0;
	            else $lunch_pullability_rate = round($lunch_interact_mobile_nums / $lunch_forscreen_box_num,2);
	             
	            if(empty($dinner_forscreen_box_num)) $dinner_pullability_rate=0;
	            else $dinner_pullability_rate= round($dinner_interact_mobile_nums / $dinner_forscreen_box_num,2);
	             
	             
	            //午饭单机互动数 晚饭单机互动数
	             
	            if(empty($lunch_interact_mobile_nums)) $lunch_alone_interact_num=0;
	            else  $lunch_alone_interact_num = round($lunch_interact_num/$lunch_interact_mobile_nums,2);
	             
	            if(empty($dinner_interact_mobile_nums)) $dinner_alone_interact_num = 0;
	            else  $dinner_alone_interact_num= round($dinner_interact_num/$dinner_interact_mobile_nums,2);
	             
	            //午饭单屏互动数 //晚饭单凭互动数
	            if(empty($lunch_fanju_num)) $lunch_box_interact_num =0;
	            else $lunch_box_interact_num = round($lunch_interact_num/$lunch_fanju_num,2);
	             
	            if(empty($dinner_fanju_num)) $dinner_box_interact_num = 0;
	            else $dinner_box_interact_num= round($dinner_interact_num/$dinner_fanju_num,2);
	             
	             
	            //午饭/晚饭投照片转换率
	            if(empty($lunch_can_forscreen_box_num)){
	                $lunch_pic_num_rate =0;
	                 
	                $lunch_video_num_rate =0 ;
	                $lunch_db_num_rate = 0;
	                $lunch_birthday_num_rate =0;
	                $lunch_game_num_rate = 0;
	            }
	            else{
	                $lunch_pic_num_rate = round($lunch_pic_num / $lunch_can_forscreen_box_num,2);
	                $lunch_video_num_rate = round($lunch_video_num / $lunch_can_forscreen_box_num,2);
	                $lunch_db_num_rate   = round($lunch_db_num / $lunch_can_forscreen_box_num,2);
	                $lunch_birthday_num_rate = round($lunch_birthday_num /$lunch_can_forscreen_box_num,2);
	                $lunch_game_num_rate = round($lunch_game_num /$lunch_can_forscreen_box_num,2);
	            }
	             
	            if(empty($dinner_can_forscreen_box_num)){
	                $dinner_pic_num_rate =0;
	                $dinner_video_num_rate = 0;
	                $dinner_db_num_rate =0;
	                $dinner_birthday_num_rate =0;
	                $dinner_game_num_rate =0;
	            }
	            else {
	                $dinner_pic_num_rate = round($dinner_pic_num / $dinner_can_forscreen_box_num,2);
	                $dinner_video_num_rate= round($dinner_video_num / $dinner_can_forscreen_box_num,2);
	                $dinner_db_num_rate  = round($dinner_db_num /$dinner_can_forscreen_box_num,2);
	                $dinner_birthday_num_rate= round($dinner_birthday_num/$dinner_can_forscreen_box_num,2);
	                $dinner_game_num_rate= round($dinner_game_num/$dinner_can_forscreen_box_num,2);
	            }
	            $list['list'][$key]['data_static'][] = array('box_num'=>$lunch_online_box_num,
	                'online_box_num'=>$lunch_online_box_num,
	                'interact_mobile_nums'=>$lunch_interact_mobile_nums,
	                'can_forscreen_box_num'=>$lunch_can_forscreen_box_num,
	                'fanju_num'=>$lunch_fanju_num,
	                'fanju_rate'=>$lunch_fanju_rate,
	                'interact_num'=>$lunch_interact_num,
	                'join_rate'  =>$lunch_join_rate,
	                'pullability_rate'=>$lunch_pullability_rate,
	                'alone_interact_num'=>$lunch_alone_interact_num,
	                'box_interact_num'=>$lunch_box_interact_num,
	                'pic_num_rate'=>$lunch_pic_num_rate,
	                'video_num_rate'=>$lunch_video_num_rate,
	                'db_num_rate'=>$lunch_db_num_rate,
	                'birthday_num_rate'=>$lunch_birthday_num_rate,
	                'game_num_rate'=>$lunch_game_num_rate
	            );
	             
	            $list['list'][$key]['data_static'][] = array('box_num'=>$dinner_online_box_num,
	                'online_box_num'=>$dinner_online_box_num,
	                'interact_mobile_nums'=>$dinner_interact_mobile_nums,
	                'can_forscreen_box_num'=>$dinner_can_forscreen_box_num,
	                'fanju_num'=>$dinner_fanju_num,
	                'fanju_rate'=>$dinner_fanju_rate,
	                'interact_num'=>$dinner_interact_num,
	                'join_rate'  =>$dinner_join_rate,
	                'pullability_rate'=>$dinner_pullability_rate,
	                'alone_interact_num'=>$dinner_alone_interact_num,
	                'box_interact_num'=>$dinner_box_interact_num,
	                'pic_num_rate'=>$dinner_pic_num_rate,
	                'video_num_rate'=>$dinner_video_num_rate,
	                'db_num_rate'=>$dinner_db_num_rate,
	                'birthday_num_rate'=>$dinner_birthday_num_rate,
	                'game_num_rate'=>$dinner_game_num_rate
	            );
	        }else {
	            $list['list'][$key]['data_static'][] = array('box_num'=>$lunch_online_box_num,
	                'online_box_num'=>$lunch_online_box_num,
	                'interact_mobile_nums'=>0,
	                'can_forscreen_box_num'=>0,
	                'fanju_num'=>0,
	                'fanju_rate'=>0,
	                'interact_num'=>0,
	                'join_rate'  =>0,
	                'pullability_rate'=>0,
	                'alone_interact_num'=>0,
	                'box_interact_num'=>0,
	                'pic_num_rate'=>0,
	                'video_num_rate'=>0,
	                'db_num_rate'=>0,
	                'birthday_num_rate'=>0,
	                'game_num_rate'=>0
	            );
	            
	            $list['list'][$key]['data_static'][] = array('box_num'=>$dinner_online_box_num,
	                'online_box_num'=>$dinner_online_box_num,
	                'interact_mobile_nums'=>0,
	                'can_forscreen_box_num'=>0,
	                'fanju_num'=>0,
	                'fanju_rate'=>0,
	                'interact_num'=>0,
	                'join_rate'  =>0,
	                'pullability_rate'=>0,
	                'alone_interact_num'=>0,
	                'box_interact_num'=>0,
	                'pic_num_rate'=>0,
	                'video_num_rate'=>0,
	                'db_num_rate'=>0,
	                'birthday_num_rate'=>0,
	                'game_num_rate'=>0
	            );
	        }
	        
	    }
	    
	    $m_area = new \Admin\Model\AreaModel();
	    $area_list = $m_area->getAllArea();
	    $this->assign('area_id',$area_id);
	    $this->assign('area_list',$area_list);
	    $this->assign('start_date',substr($start_date, 0,11));
	    $this->assign('end_date',substr($end_date, 0,11));
	    $this->assign('list',$list['list']);
	    $this->assign('page',$list['page']);
	    $this->display('Report/interact');
	}
	/**
	 * @用户公开信息审核
	 */
	public function publicCheck(){
	    $openid = I('openid','','trim');
	    $status = I('status',99,'intval');
        $is_recommend = I('is_recommend',99,'intval');
        $is_top = I('is_top',99,'intval');
        $res_type = I('res_type',0,'intval');
	    $size   = I('numPerPage',50);//显示每页记录数
	    $this->assign('numPerPage',$size);
	    $start = I('pageNum',1);
	    $this->assign('pageNum',$start);
	    $order = I('_order','a.id');
	    $this->assign('_order',$order);
	    $sort = I('_sort','desc');
	    $this->assign('_sort',$sort);
	    $orders = $order.' '.$sort;
	    $start  = ( $start-1 ) * $size;
	    $m_public = new \Admin\Model\Smallapp\PublicModel();
	    $fields = 'user.nickName,a.id,a.forscreen_id,a.openid,a.box_mac,a.res_type,a.is_pub_hotelinfo,a.create_time,a.status,a.is_recommend,a.create_time';
	    $where = array();
	    if($status!=99){
            $where['a.status'] = $status;
        }
        if($is_recommend!=99){
            $where['a.is_recommend'] = $is_recommend;
        }
        if($is_top!=99){
            $where['a.is_top'] = $is_top;
        }
        if($res_type){
            $where['a.res_type'] = $res_type;
        }
        if($openid){
            $where['a.openid'] = $openid;
        }
	    $list = $m_public->getList($fields,$where, $orders, $start,$size);
        $datalist = $list['list'];
        $all_status = C('PUBLIC_AUDIT_STATUS');
        $m_publicplay = new \Admin\Model\Smallapp\PublicplayModel();
        $m_publicplay_hotel = new \Admin\Model\Smallapp\PublicplayHotelModel();
        foreach ($datalist as $k=>$v){
            $datalist[$k]['status_str'] = $all_status[$v['status']];
            $hotel_num = 0;
            $publicplay_id = 0;
            if($v['status']==2){
                $res_play = $m_publicplay->getInfo(array('public_id'=>$v['id'],'status'=>1));
                if(!empty($res_play)){
                    $publicplay_id = $res_play['id'];
                    $hwhere = array('publicplay_id'=>$publicplay_id);
                    $res_play_hotel = $m_publicplay_hotel->getDataList('count(id) as num',$hwhere,'id desc');
                    if(!empty($res_play_hotel)){
                        $hotel_num = $res_play_hotel[0]['num'];
                    }
                }
            }
            $datalist[$k]['hotel_num'] = $hotel_num;
            $datalist[$k]['publicplay_id'] = $publicplay_id;
        }
        $this->assign('is_recommend',$is_recommend);
        $this->assign('is_top',$is_top);
        $this->assign('res_type',$res_type);
        $this->assign('status',$status);
        $this->assign('openid',$openid);
	    $this->assign('list',$datalist);
	    $this->assign('page',$list['page']);
	    $this->display('Report/sapppublic');
	}
	public function pubdetail(){
	    $oss_host = 'http://'. C('OSS_HOST_NEW').'/';
	    $forscreen_id = I('get.forscreen_id',0,'trim');
	    $res_type = I('get.res_type');
	    $m_pubdetail = new \Admin\Model\Smallapp\PubdetailModel();
	    $m_public    = new \Admin\Model\Smallapp\PublicModel();
	    
	    $info = $m_public->getOne('id,is_recommend,status,is_top', array('forscreen_id'=>$forscreen_id));
	    
	    $fields = "concat('".$oss_host."',`res_url`) res_url";
	    $where = array();
	    $where['forscreen_id'] = $forscreen_id;
	    $list = $m_pubdetail->getWhere($fields,$where);
	    $this->assign('res_type',$res_type);
	    $this->assign('list',$list);
	    $this->assign('info',$info); 
	    $this->assign('id',$info['id']);
	    $this->display('Report/pubdetail');
	}

    public function publicaudit(){
        if(IS_GET){
            $id = I('get.id',0,'trim');
            $forscreen_id = I('get.forscreen_id',0,'trim');
            $res_type = I('get.res_type');
            $m_pubdetail = new \Admin\Model\Smallapp\PubdetailModel();
            $m_public    = new \Admin\Model\Smallapp\PublicModel();

            $info = $m_public->getOne('*',array('id'=>$id));

            $oss_host = 'http://'. C('OSS_HOST_NEW').'/';
            $fields = "concat('".$oss_host."',`res_url`) res_url";
            $list = $m_pubdetail->getWhere($fields,array('forscreen_id'=>$forscreen_id));
            $hours = array();
            for($i=0;$i<24;$i++){
                $hours[]=str_pad($i,2,'0',STR_PAD_LEFT);
            }
            $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
            $res_f = $m_forscreen->getInfo(array('forscreen_id'=>$forscreen_id));
            if(!empty($res_f)){
                $info['hotel_name'] = $res_f['hotel_name'];
            }

            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();
            $now_frequency = array();
            $all_frequency = C('PUBLIC_PLAY_FREQUENCY');
            foreach ($all_frequency as $k=>$v){
                $f_str = $k.'次,分钟:';
                foreach ($v as $f){
                    $f_str.=$f.',';
                }
                $now_frequency[$k] = rtrim($f_str,',');
            }
            $m_publicplay = new \Admin\Model\Smallapp\PublicplayModel();
            $res_public_play = $m_publicplay->getInfo(array('public_id'=>$id));
            if(!empty($res_public_play)){
                $res_public_play['start_date'] = date('Y-m-d',strtotime($res_public_play['start_date']));
                $res_public_play['end_date'] = date('Y-m-d',strtotime($res_public_play['end_date']));
                $res_public_play['start_hour'] = str_pad($res_public_play['start_hour'],2,'0',STR_PAD_LEFT);
                $res_public_play['end_hour'] = str_pad($res_public_play['end_hour'],2,'0',STR_PAD_LEFT);
            }

            $this->assign('playinfo', $res_public_play);
            $this->assign('areainfo', $area_arr);
            $this->assign('hours',$hours);
            $this->assign('all_frequency',$now_frequency);
            $this->assign('res_type',$res_type);
            $this->assign('list',$list);
            $this->assign('info',$info);
            $this->display('Smallapppublic/publicaudit');
        }else{
            $public_id = I('post.public_id',0,'intval');
            $forscreen_id = I('post.forscreen_id',0,'intval');
            $h_b_arr = $_POST['hbarr'];
            $is_recommend = I('post.is_recommend',0,'intval');
            $status = I('post.status',0,'intval');
            $is_top = I('post.is_top',0,'intval');

            $start_date = I('post.start_date', '');
            $end_date = I('post.end_date', '');
            $start_hour = I('post.start_hour', 0,'intval');
            $end_hour = I('post.end_hour', 0,'intval');
            $frequency = I('post.frequency', 0,'intval');

            $m_public    = new \Admin\Model\Smallapp\PublicModel();
            $res_publicdata = $m_public->getOne('*',array('id'=>$public_id));
            $now_time = time();
            $public_time = strtotime($res_publicdata['create_time']);
            $is_has_notify_time = 0;
            if($now_time - $public_time<=7200){
                $is_has_notify_time = 1;
            }
            $is_audit = 0;
            if($is_has_notify_time && $res_publicdata['status']==1 && $status==2){
                $is_audit = 1;
            }

            if($res_publicdata['res_type']==2){
                $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
                $res_forscreen = $m_forscreen->getFileMd5($res_publicdata['forscreen_id']);
                if($res_forscreen['is_eq']==0 && ($res_forscreen['oss_size']==0 || empty($res_forscreen['md5_file']))){
                    $res_forscreen = $m_forscreen->getFileMd5($res_publicdata['forscreen_id']);
                }
                if($res_forscreen['is_eq']==0 && $res_forscreen['oss_size'] && $res_forscreen['md5_file']){
                    $where = array('forscreen_id'=>$res_publicdata['forscreen_id']);
                    $data = array('resource_size'=>$res_forscreen['oss_size'],'md5_file'=>$res_forscreen['md5_file']);
                    $m_forscreen->updateInfo($where,$data);
                }
            }
            if($status==2){
                $m_invalid = new \Admin\Model\ForscreenInvalidlistModel();
                $res_invalid = $m_invalid->getInfo(array('invalidid'=>$res_publicdata['openid'],'type'=>2));
                if(!empty($res_invalid)){
                    $m_public->updateInfo(array('id'=>$public_id), array('status'=>0));
                    $this->output('当前审核内容,用户为无效名单数据,不能审核通过', 'sappforscreen/publicaudit',2,0);
                }
            }

            $pdata = array('status'=>$status,'is_recommend'=>$is_recommend);
            if($status==2 && $is_recommend==1){
                $pdata['is_top'] = $is_top;
            }
            if($is_top){
                $all_topnum = 3;
                $res_public = $m_public->getWhere('id',array('status'=>2,'is_recommend'=>1,'is_top'=>1),'id asc','','');
                $last_topnum = count($res_public) - $all_topnum;
                if($last_topnum>=0){
                    $last_public = array_slice($res_public,0,$last_topnum+1);
                    $ids = array();
                    foreach ($last_public as $v){
                        $ids[] = $v['id'];
                    }
                    $upwhere = array('id'=>array('in',$ids));
                    $m_public->updateInfo($upwhere,array('is_top'=>0));
                }
            }
            $ret = $m_public->updateInfo(array('id'=>$public_id), $pdata);
            $key_findtop = C('SAPP_FIND_TOP');
            $redis  =  \Common\Lib\SavorRedis::getInstance();
            $redis->select(5);
            $res_public = $m_public->getWhere('id',array('status'=>2,'is_recommend'=>1,'is_top'=>1),'id asc','','');
            $top_ids = array();
            foreach ($res_public as $v){
                $top_ids[]=$v['id'];
            }
            $redis->set($key_findtop,json_encode($top_ids));
            $audit_key = C('SAPP_PUBLIC_AUDITNUM').$res_publicdata['openid'];
            $res_audit = $redis->get($audit_key);
            if(!empty($res_audit)){
                $audit_num = $res_audit;
            }else{
                $audit_num = 0;
            }
            if($status==2){
                $audit_num = $audit_num+1;
            }else{
                $audit_num = $audit_num-1>0?$audit_num-1:0;
            }
            $redis->set($audit_key,$audit_num,86400*30);

            $m_publicplay = new \Admin\Model\Smallapp\PublicplayModel();
            $res_public_play = $m_publicplay->getInfo(array('public_id'=>$public_id));
            $is_play = 0;
            $hotel_ids = array();
            if($status==2 && !empty($start_date) && !empty($end_date)){
                $now_day = date("Y-m-d");
                if($start_date > $end_date){
                    $this->output('投放开始时间必须小于等于结束时间', 'sappforscreen/publicaudit',2,0);
                }
                if($start_date < $now_day){
                    $this->output('投放开始时间必须大于等于今天', 'sappforscreen/publicaudit',2,0);
                }
                if(empty($start_hour) || empty($end_hour)){
                    $this->output('请选择播放时段', 'sappforscreen/publicaudit',2,0);
                }
                if(empty($frequency)){
                    $this->output('请选择播放频次', 'sappforscreen/publicaudit',2,0);
                }
                if($start_hour>$end_hour){
                    $this->output('投放开始时段必须小于等于结束时段', 'sappforscreen/publicaudit',2,0);
                }
                if($end_hour-$start_hour==0){
                    $this->output('投放时段间隔1小时以上', 'sappforscreen/publicaudit',2,0);
                }
                $sysuserInfo = session('sysUserInfo');
                $start_date_time = date('Y-m-d 00:00:00',strtotime($start_date));
                $end_date_time = date('Y-m-d 23:59:59',strtotime($end_date));
                $add_data = array('public_id'=>$public_id,'forscreen_id'=>$forscreen_id,
                    'start_date'=>$start_date_time,'end_date'=>$end_date_time,'start_hour'=>$start_hour,
                    'end_hour'=>$end_hour,'frequency'=>$frequency,'status'=>1,'sysuser_id'=>$sysuserInfo['id'],
                );

                if($is_has_notify_time && empty($res_public_play)){
                    $is_play = 1;
                }
                if(empty($res_public_play)){
                    $public_play_id = $m_publicplay->add($add_data);
                }else{
                    $add_data['update_time'] = date('Y-m-d H:i:s');
                    $m_publicplay->updateData(array('id'=>$res_public_play['id']),$add_data);
                    $public_play_id = $res_public_play['id'];
                }
                $m_publicplay_hotel = new \Admin\Model\Smallapp\PublicplayHotelModel();
                $res_hotel = $m_publicplay_hotel->getInfo(array('publicplay_id'=>$public_play_id));
                $hotel_arr = json_decode($h_b_arr, true);
                if(empty($res_hotel) && empty($hotel_arr)){
                    $this->output('请选择投放的酒楼', 'sappforscreen/publicaudit',2,0);
                }
                if(!empty($hotel_arr)){
                    $add_hotel_data = array();
                    foreach ($hotel_arr as $v){
                        $hotel_id = intval($v['hotel_id']);
                        if(!empty($hotel_id)){
                            $hotel_ids[]=$hotel_id;
                            if(!empty($res_hotel)){
                                $res_add_hotel = $m_publicplay_hotel->getInfo(array('publicplay_id'=>$public_play_id,'hotel_id'=>$hotel_id));
                                if(empty($res_add_hotel)){
                                    $add_hotel_data[]=array('hotel_id'=>$hotel_id,'publicplay_id'=>$public_play_id);
                                }
                            }else{
                                $add_hotel_data[]=array('hotel_id'=>$hotel_id,'publicplay_id'=>$public_play_id);
                            }
                        }
                    }
                    $m_publicplay_hotel->addAll($add_hotel_data);
                }
            }
            $m_netty = new \Admin\Model\Smallapp\NettyModel();
            $head_pic = 'http://oss.littlehotspot.com/media/resource/btCfRRhHkn.jpg';
            $now_barrages = array('nickName'=>'小热点','headPic'=>base64_encode($head_pic),'avatarUrl'=>$head_pic);
            if($is_play){
                $hotel_num = count($hotel_ids);
                $m_box = new \Admin\Model\BoxModel();
                $where = array('box.state'=>1,'box.flag'=>0);
                $where['hotel.id'] = array('in',$hotel_ids);
                $fields = 'count(box.id) as num';
                $res_box = $m_box->getBoxByCondition($fields,$where);
                $box_num = count($res_box[0]['num']);
                $date_time1 = new \DateTime($start_date);
                $date_time2 = new \DateTime($end_date);
                $interval = $date_time1->diff($date_time2);
                $day = $interval->format('%d');

                $day_num = $day+1;
                $hour_num = $end_hour-$start_hour;
                $all_frequency = C('PUBLIC_PLAY_FREQUENCY');
                $frequency_num = count($all_frequency[$frequency]);
                $play_num = $day_num * $hour_num * $frequency_num * $box_num;

                $now_barrages['barrage'] = "您的内容已经通过审核，即将在{$hotel_num}家酒楼进行播放，预计播放{$play_num}次";
                $user_barrages = array($now_barrages);
                $message = array('action'=>122,'userBarrages'=>$user_barrages);
                $res_netty = $m_netty->pushBox($res_publicdata['box_mac'],json_encode($message));

                $log_content = date("Y-m-d H:i:s").'[box_mac]'.$res_publicdata['box_mac'].'[message]'.json_encode($message).'[netty]'.json_encode($res_netty)."\r\n";
                $log_file_name = SITE_TP_PATH.'/Public/content/'.'publicaudit_'.date("Ymd").".log";
                @file_put_contents($log_file_name, $log_content, FILE_APPEND);
            }elseif($is_audit){
                $now_barrages['barrage'] = '您的内容已经通过审核，在小程序发现页面可以看到';
                $user_barrages = array($now_barrages);
                $message = array('action'=>122,'userBarrages'=>$user_barrages);
                $res_netty = $m_netty->pushBox($res_publicdata['box_mac'],json_encode($message));

                $log_content = date("Y-m-d H:i:s").'[box_mac]'.$res_publicdata['box_mac'].'[message]'.json_encode($message).'[netty]'.json_encode($res_netty)."\r\n";
                $log_file_name = SITE_TP_PATH.'/Public/content/'.'publicaudit_'.date("Ymd").".log";
                @file_put_contents($log_file_name, $log_content, FILE_APPEND);
            }

            if($status!=2 && !empty($res_public_play)){
                $sysuserInfo = session('sysUserInfo');
                $add_data = array('status'=>2,'sysuser_id'=>$sysuserInfo['id'],'update_time'=>date('Y-m-d H:i:s'));
                $m_publicplay->updateData(array('id'=>$res_public_play['id']),$add_data);
            }
            $this->output("操作成功{$now_barrages['barrage']}", 'sappforscreen/publiccheck');
        }
    }

    public function publichotellist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        $publicplay_id = I('publicplay_id',0,'intval');
        $keyword = I('keyword','','trim');

        $m_publicplay_hotel = new \Admin\Model\Smallapp\PublicplayHotelModel();
        $where = array('a.publicplay_id'=>$publicplay_id);
        if(!empty($keyword)){
            $where['h.name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $fields = 'a.id,a.add_time,h.id as hotel_id,h.name as hotel_name';
        $result = $m_publicplay_hotel->getHotelList($fields,$where,'a.id desc',$start,$size);
        $datalist = $result['list'];

        $this->assign('publicplay_id', $publicplay_id);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('Smallapppublic/publichotellist');
    }

    public function publichoteldel(){
        $id = I('get.id',0,'intval');
        $m_publicplay_hotel = new \Admin\Model\Smallapp\PublicplayHotelModel();
        $result = $m_publicplay_hotel->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'sappforscreen/publichotellist',2);
        }else{
            $this->output('操作失败', 'sappforscreen/publichotellist',2,0);
        }
    }

	/**
	 * @desc 审核通过
	 */
	public function operateStatus(){
	    $m_public = new \Admin\Model\Smallapp\PublicModel();
	    $id     = I('post.id',0,'intval');
	    $is_recommend = I('post.is_recommend',0,'intval');
	    $status = I('post.status',0,'intval');
	    $is_top = I('post.is_top',0,'intval');

        $res_publicdata = $m_public->getOne('res_type,forscreen_id',array('id'=>$id),'id desc');
        if($res_publicdata['res_type']==2){
            $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
            $res_forscreen = $m_forscreen->getFileMd5($res_publicdata['forscreen_id']);
            if($res_forscreen['is_eq']==0 && ($res_forscreen['oss_size']==0 || empty($res_forscreen['md5_file']))){
                $res_forscreen = $m_forscreen->getFileMd5($res_publicdata['forscreen_id']);
            }
            if($res_forscreen['is_eq']==0 && ($res_forscreen['oss_size']==0 || empty($res_forscreen['md5_file']))){
                die('0');
            }elseif($res_forscreen['is_eq']==0 && $res_forscreen['oss_size'] && $res_forscreen['md5_file']){
                $where = array('forscreen_id'=>$res_publicdata['forscreen_id']);
                $data = array('resource_size'=>$res_forscreen['oss_size'],'md5_file'=>$res_forscreen['md5_file']);
                $m_forscreen->updateInfo($where,$data);
            }
        }

	    $where = $data = array();
	    $where['id'] = $id;
	    if(is_numeric($status)){
	        $data['status'] = $status;
	    }
	    if(is_numeric($is_recommend)){
	        $data['is_recommend'] = $is_recommend;
	    }
	    if($status==2 && $is_recommend==1){
	        $data['is_top'] = $is_top;
        }
        if($is_top){
            $all_topnum = 3;
            $res_public = $m_public->getWhere('id',array('status'=>2,'is_recommend'=>1,'is_top'=>1),'id asc','','');
            $last_topnum = count($res_public) - $all_topnum;
            if($last_topnum>=0){
                $last_public = array_slice($res_public,0,$last_topnum+1);
                $ids = array();
                foreach ($last_public as $v){
                    $ids[] = $v['id'];
                }
                $upwhere = array('id'=>array('in',$ids));
                $m_public->updateInfo($upwhere,array('is_top'=>0));
            }
        }
	    $ret = $m_public->updateInfo($where, $data);

        $key_findtop = C('SAPP_FIND_TOP');
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(5);
        $res_public = $m_public->getWhere('id',array('status'=>2,'is_recommend'=>1,'is_top'=>1),'id asc','','');
        $top_ids = array();
        foreach ($res_public as $v){
            $top_ids[]=$v['id'];
        }
        $redis->set($key_findtop,json_encode($top_ids));

	    if($ret){
	        echo "1";
	    }else {
	        echo '0';
	    }
	    
	}
	public function operateRecommend(){
	    $id = I('id',0,'trim');
	    $is_recommend = I('is_recommend');
	    $callbacktype   = I('callbacktype') ? I('callbacktype') : 0;
	    
	    $m_public = new \Admin\Model\Smallapp\PublicModel();
	    $where = $data = array();
	    $where['id'] = $id;
	    $data['is_recommend'] = $is_recommend;
	    
	    $ret = $m_public->updateInfo($where, $data);
	    if(empty($callbacktype)){
	        $callback = 2;
	    }else {
	        $callback = 1;
	    }
	    if($ret){
	        $this->output('修改成功', 'sappforscreen/publiccheck',$callback);
	    }else {
	        $this->output('修改失败', 'sappforscreen/publiccheck',$callback);
	    }
	}

	public function delpublic(){
	   $id     = I('get.id',0,'intval');
	   $m_public = new \Admin\Model\Smallapp\PublicModel();
	   $where = $data = array();
	   $where['id'] = $id;
	   $data['status'] = 0;
	   $ret = $m_public->updateInfo($where, $data);
	   if($ret){
           $res_publicdata = $m_public->getOne('*',array('id'=>$id));
	       $redis = new \Common\Lib\SavorRedis();
	       $redis->select(5);
           $audit_key = C('SAPP_PUBLIC_AUDITNUM').$res_publicdata['openid'];
           $res_audit = $redis->get($audit_key);
           if(!empty($res_audit)){
               $audit_num = $res_audit-1>0?$res_audit-1:0;
               $redis->set($audit_key,$audit_num,86400*30);
           }
           $m_publicplay = new \Admin\Model\Smallapp\PublicplayModel();
           $res_public_play = $m_publicplay->getInfo(array('public_id'=>$id));
           if(!empty($res_public_play)){
               $sysuserInfo = session('sysUserInfo');
               $add_data = array('status'=>2,'sysuser_id'=>$sysuserInfo['id'],'update_time'=>date('Y-m-d H:i:s'));
               $m_publicplay->updateData(array('id'=>$res_public_play['id']),$add_data);
           }

	       $this->output('删除成功', 'sappforscreen/publiccheck',2);
	   }else {
	       $this->output('删除失败', 'sappforscreen/publiccheck',2);
	   }
	}
}