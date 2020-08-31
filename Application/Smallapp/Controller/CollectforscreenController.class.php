<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController;

/**
 * @desc 采集投屏
 *
 */
class CollectforscreenController extends BaseController {

    public $success_status = array('1'=>'成功','2'=>'打断','3'=>'退出','0'=>'失败');

    public function __construct() {
        parent::__construct();
        $this->all_smallapps = C('all_smallapps');
        $this->all_actions = C('all_forscreen_actions');
    }

    public function datalist(){
        $size   = I('numPerPage',50);//显示每页记录数
        $pagenum = I('pageNum',1);
        $small_app_id = I('small_app_id',0,'intval');
        $is_exist = I('is_exist',99,'intval');
        $action_type = I('action_type',999);
        $create_time = I('create_time','','trim');
        $end_time    = I('end_time','','trim');
        $hotel_name = I('hotel_name','','trim');
        $box_mac    = I('box_mac','','trim');
        $openid = I('openid','','trim');

        $where = array('box.state'=>1,'box.flag'=>0);
        if($create_time && $end_time){
            $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        }else{
            $create_time = date('Y-m-d');
            $end_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$create_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
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
        if($hotel_name){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        if($small_app_id){
            $where['a.small_app_id'] = $small_app_id;
        }
        if($box_mac){
            $where['a.box_mac'] = $box_mac;
        }
        if($openid){
            $where['a.openid'] = $openid;
        }
        $fields = 'user.avatarUrl,user.nickName,area.region_name,hotel.name hotel_name,room.name room_name,box.box_type,box.is_4g,a.*';
        $m_smallapp_forscreen_record = new \Admin\Model\Smallapp\CollectforscreenModel();
        $orders = 'a.create_time desc';
        $start = ($pagenum-1) * $size;
        $list = $m_smallapp_forscreen_record->getList($fields,$where,$orders,$start,$size);

        $all_smallapps = $this->all_smallapps;
        unset($all_smallapps[3],$all_smallapps[4],$all_smallapps[11]);
        $all_actions = $this->all_actions;
        $all_box_types = C('hotel_box_type');
        foreach ($list['list'] as $key=>$v){
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

            if($v['is_4g']==1){
                $is_4g_str = '是';
            }else{
                $is_4g_str = '否';
            }
            $list['list'][$key]['is_4g_str'] = $is_4g_str;
            $success_str = '';
            if($v['small_app_id']==1){
                $success_str = $this->success_status[$v['success_status']];
            }
            $list['list'][$key]['success_str'] = $success_str;
            $list['list'][$key]['total_time'] = $v['total_time'];
            $list['list'][$key]['imgs'] = json_decode(str_replace('\\', '', $v['imgs']),true);
            $nowaction_type = $v['action'];
            if($nowaction_type==2){
                $nowaction_type = $nowaction_type.'-'.$v['resource_type'];
            }
            $list['list'][$key]['action_name'] = $all_actions[$nowaction_type];
        }

        $this->assign('hotel_name',$hotel_name);
        $this->assign('box_mac',$box_mac);
        $this->assign('openid',$openid);
        $this->assign('create_time',$create_time);
        $this->assign('end_time',$end_time);
        $this->assign('pageNum',$pagenum);
        $this->assign('numPerPage',$size);
        $this->assign('action_type',$action_type);
        $this->assign('is_exist',$is_exist);
        $this->assign('all_actions',$all_actions);
        $this->assign('small_apps',$all_smallapps);
        $this->assign('small_app_id',$small_app_id);
        $this->assign('list',$list['list']);
        $this->assign('oss_host',C('OSS_HOST_NEW'));
        $this->assign('page',$list['page']);
        $this->display('datalist');
    }

    public function refreshdata(){
        $m_collectforscreen = new \Admin\Model\Smallapp\CollectforscreenModel();
        $m_collectforscreen->collectforscreen(1);
        $this->output('刷新数据成功', 'collectforscreen/datalist',2);
    }


}