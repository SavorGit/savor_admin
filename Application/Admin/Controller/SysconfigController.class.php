<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
use Common\Lib\SavorRedis;
/**
 * @desc 系统日志记录类
 *
 */
class SysconfigController extends BaseController {
    
     private $oss_host = '';
     private $payback_day_commission_conf = array(
         
         array('min'=>0,'max'=>7,'percent'=>''),
         array('min'=>8,'max'=>15,'percent'=>''),
         array('min'=>16,'max'=>30,'percent'=>''),
         array('min'=>31,'max'=>60,'percent'=>''),
         array('min'=>61,'max'=>90,'percent'=>''),
         array('min'=>91,'max'=>9999,'percent'=>''),
         
     );
	 public function __construct(){
	     parent::__construct();
	     $this->oss_host = get_oss_host();
	 }
    /**
     * @desc 电视设置页面
     */
    public function configData(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $switch_time_info = $m_sys_config->getOne('system_switch_time');
       
        $where = array();
        $volume_arr = $m_sys_config->getList($where);
       
        foreach($volume_arr as $key=>$v){
            if($v['config_key']=='system_award_time'){
                $info['award_time'] = json_decode($v['config_value'],true);
            }else{
                $info[$v['config_key']] = $v['config_value'];
            }
        }
        $info['mid'] = $info['award_time'][0];
        $info['aft'] = $info['award_time'][1];
        $info['system_switch_time']  = $switch_time_info['config_value'];
        $info['status'] = $switch_time_info['status'];
        
        //视频投屏loading图
        $loading_info = $m_sys_config->getOne('system_loading_image');
        if(!empty($loading_info['config_value'])){
            $m_media = new \Admin\Model\MediaModel();
            $map['id'] = $loading_info['config_value'];
            $media_info = $m_media->getWhere($map, 'oss_addr');
            
            $oss_addr = $this->oss_host.$media_info[0]['oss_addr'];
            $this->assign('oss_addr',$oss_addr);
            //getWhere
        }
        
        $this->assign('info',$info);
        $this->display('Sysconfig/configdata');
    }
    /**
     * @desc 修改设置
     */
    public function doConfigData(){
        //$system_default_volume = I('post.system_default_volume',0,'intval');
        $system_switch_time = I('post.system_switch_time',0,'intval');
        $m_sys_config = new \Admin\Model\SysConfigModel();
        //$volume_info = $m_sys_config->getOne('system_default_volume'); 
  
        /* if(empty($volume_info)){
            $data['config_key'] = 'system_default_volume';
            $data['config_value'] = $system_default_volume;;
            $rt = $m_sys_config->add($data);
        }else {
            $data['config_value'] = $system_default_volume;
            $rt = $m_sys_config->editData($data, 'system_default_volume');
        } */
 
        $switch_time_info = $m_sys_config->getOne('system_switch_time');
        $t_val = $switch_time_info['config_value'];
        if(empty($switch_time_info)){
            $map['config_key'] = 'system_switch_time';
            $map['config_value'] = $system_switch_time;
            $rts = $m_sys_config->add($map);
        }else {
            $map['config_value'] = $system_switch_time;
            if($t_val == $system_switch_time) {
                $this->output('操作成功','sysconfig/configData');
            }
            $rts = $m_sys_config->editData($map, 'system_switch_time',2);
        }
        
        if($rts){
            $sys_list = $m_sys_config->getList(array('status'=>1));
            $redis = SavorRedis::getInstance();
            $redis->select(12);
            $cache_key = C('SYSTEM_CONFIG');
            $redis->set($cache_key, json_encode($sys_list));
            $this->output('操作成功','sysconfig/configData');
        }else {
            $this->error('操作失败');
        }
    }
    /**
     * @desc 修改电视设置配置状态
     */
    public function editStatus(){
        $status = I('get.status',0,'intval');
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $map['status'] = $status;
        $rts = $m_sys_config->editData($map, 'system_switch_time');
        if($rts){
            $sys_list = $m_sys_config->getList(array('status'=>1));
            $redis = SavorRedis::getInstance();
            $redis->select(12);
            $cache_key = C('SYSTEM_CONFIG');
            $redis->set($cache_key, json_encode($sys_list));
            $this->output('操作成功!', 'sysconfig/configData',2);
        }else {
            $this->error('操作失败!');
        }
        
    }
    /**
     * @desc 音量设置
     */
    public function doConfigVolume(){
        $system_ad_volume = I('post.system_ad_volume','','trim');             //广告轮播音量
        $system_pro_screen_volume   = I('post.system_pro_screen_volume','','trim');    //投屏音量
        $system_demand_video_volume = I('post.system_demand_video_volume','','trim');   //点播音量
        $system_tv_volume           = I('post.system_tv_volume','','trim');             //电视音量
        $system_for_screen_volume   = I('post.system_for_screen_volume','','trim');    //夏新电视投屏音量

        $box_carousel_volume   = I('post.box_carousel_volume','','trim');    //机顶盒轮播音量
        $box_pro_demand_volume   = I('post.box_pro_demand_volume','','trim');    //机顶盒公司节目点播音量
        $box_content_demand_volume   = I('post.box_content_demand_volume','','trim');    //机顶盒用户内容点播音量
        $box_video_froscreen_volume   = I('post.box_video_froscreen_volume','','trim');    //机顶盒视频投屏音量
        $box_img_froscreen_volume   = I('post.box_img_froscreen_volume','','trim');    //机顶盒图片投屏音量
        $box_tv_volume   = I('post.box_tv_volume','','trim');    //机顶盒电视音量

        $tv_carousel_volume   = I('post.tv_carousel_volume','','trim');    //电视轮播音量
        $tv_pro_demand_volume   = I('post.tv_pro_demand_volume','','trim');    //电视公司节目点播音量
        $tv_content_demand_volume   = I('post.tv_content_demand_volume','','trim');    //电视用户内容点播音量
        $tv_video_froscreen_volume   = I('post.tv_video_froscreen_volume','','trim');    //电视视频投屏音量
        $tv_img_froscreen_volume   = I('post.tv_img_froscreen_volume','','trim');    //电视图片投屏音量

        $data = array();
        if(!empty($system_ad_volume))   $data['system_ad_volume'] = $system_ad_volume;
        if(!empty($system_pro_screen_volume))   $data['system_pro_screen_volume'] = $system_pro_screen_volume;
        if(!empty($system_demand_video_volume))   $data['system_demand_video_volume'] = $system_demand_video_volume;
        if(!empty($system_tv_volume))   $data['system_tv_volume'] = $system_tv_volume;
        if(!empty($system_for_screen_volume))   $data['system_for_screen_volume'] = $system_for_screen_volume;

        if(!empty($box_carousel_volume))   $data['box_carousel_volume'] = $box_carousel_volume;
        if(!empty($box_pro_demand_volume))   $data['box_pro_demand_volume'] = $box_pro_demand_volume;
        if(!empty($box_content_demand_volume))   $data['box_content_demand_volume'] = $box_content_demand_volume;
        if(!empty($box_video_froscreen_volume))   $data['box_video_froscreen_volume'] = $box_video_froscreen_volume;
        if(!empty($box_img_froscreen_volume))   $data['box_img_froscreen_volume'] = $box_img_froscreen_volume;
        if(!empty($box_tv_volume))   $data['box_tv_volume'] = $box_tv_volume;
        if(!empty($tv_carousel_volume))   $data['tv_carousel_volume'] = $tv_carousel_volume;
        if(!empty($tv_pro_demand_volume))   $data['tv_pro_demand_volume'] = $tv_pro_demand_volume;
        if(!empty($tv_content_demand_volume))   $data['tv_content_demand_volume'] = $tv_content_demand_volume;
        if(!empty($tv_video_froscreen_volume))   $data['tv_video_froscreen_volume'] = $tv_video_froscreen_volume;
        if(!empty($tv_img_froscreen_volume))   $data['tv_img_froscreen_volume'] = $tv_img_froscreen_volume;


        $m_sys_config = new \Admin\Model\SysConfigModel();
        $ret = $m_sys_config->updateInfo($data);
        
        if($ret){
            $sys_list = $m_sys_config->getList(array('status'=>1));
            $redis = SavorRedis::getInstance();
            $redis->select(12);
            $cache_key = C('SYSTEM_CONFIG');
            $redis->set($cache_key, json_encode($sys_list));
            $this->output('操作成功!', 'sysconfig/configData',2);
        }else {
            $this->error('操作失败3!');
        }
    }
    
    /**
     * @desc 修改视频投屏loading图
     */
    public function addinfo(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        //视频投屏loading图
        $loading_info = $m_sys_config->getOne('system_loading_image');
        
        if(!empty($loading_info['config_value'])){
            $m_media = new \Admin\Model\MediaModel();
            $map['id'] = $loading_info['config_value'];
            $media_info = $m_media->getWhere($map, 'oss_addr');
        
            $oss_addr = $this->oss_host.$media_info[0]['oss_addr'];
            $this->assign('media_id',$map['id']);
            $this->assign('oss_addr',$oss_addr);
            //getWhere
        }
        $this->display('addinfo');
    }
    public function doAddLoadingImg(){
        $media_id = I('post.media_id',0,'intval');
        $m_sys_config = new \Admin\Model\SysConfigModel();
        //视频投屏loading图
        $loading_info = $m_sys_config->getOne('system_loading_image');
        $data = array();
        $is_sendtopic = 0;
        if(empty($loading_info)){
            $data['config_key'] = 'system_loading_image'; 
            $data['config_value'] = $media_id;
            $rt = $m_sys_config->add($data);
            if(!empty($media_id)){
                $is_sendtopic = 1;
            }
        }else {
            $data['config_value'] = $media_id;
            $rt = $m_sys_config->editData($data, 'system_loading_image');
            if(!empty($media_id) && $media_id!=$loading_info['']){
                $is_sendtopic = 1;
            }
        }
        if($rt){
            $sys_list = $m_sys_config->getList(array('status'=>1));
            $redis = SavorRedis::getInstance();
            $redis->select(12);
            $cache_key = C('SYSTEM_CONFIG');
            $redis->set($cache_key, json_encode($sys_list));
            if($is_sendtopic){
                $hotel_ids = getVsmallHotelList();
                sendTopicMessage($hotel_ids,14);
            }
            $this->output('操作成功', 'Sysconfig/configData');
        }else {
            $this->error('操作失败');
        }
    }
    public function delload(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $data['config_value'] = '';
        $config_key = 'system_loading_image';
        $ret = $m_sys_config->editData($data, $config_key);
        if($ret){
            $sys_list = $m_sys_config->getList(array('status'=>1));
            $redis = SavorRedis::getInstance();
            $redis->select(12);
            $cache_key = C('SYSTEM_CONFIG');
            $redis->set($cache_key, json_encode($sys_list));
            
            $hotel_ids = getVsmallHotelList();
            sendTopicMessage($hotel_ids,14);

            $this->output('删除成功', 'Sysconfig/configData',2);
        }else {
            $this->error('删除失败');
        }
    }


    public function doConfigBanner(){
        $data = array();
        $mid_start = I('post.mid_start');
        $mid_end = I('post.mid_end');
        $after_start = I('post.after_start');
        $after_end = I('post.after_end');
        $errmsc = '日期格式必须要按插件格式且时为00-23,分为00-59';
     //   ([0-5][0-9])
        $pattern = "/^((0[0-9]{1})|(1[0-9]{1})|(2[0-3]{1})):([0-5]{1}[0-9]{1})$/";
        if (!preg_match ($pattern,$mid_start, $matches)){
            $mid = '中午开始时间';
            $this->error($errmsc);
        }
        if (!preg_match ($pattern, $mid_end, $matches)){
            $mid = '中午结束时间';
            $this->error($errmsc);
        }
        if (!preg_match ($pattern, $after_start, $matches)){
            $mid = '下午开始时间';
            $this->error($errmsc);
        }
        if (!preg_match ($pattern, $after_end, $matches)){
            $mid = '下午结束时间';
            $this->error($errmsc);
        }
        $m_s = str_replace(':','',$mid_start);
        $m_e = str_replace(':','',$mid_end);
        $a_s = str_replace(':','',$after_start);
        $a_e = str_replace(':','',$after_end);
        $m_s = intval($m_s);
        $m_e = intval($m_e);
        $a_s = intval($a_s);
        $a_e = intval($a_e);
        if($m_s>$m_e){
            $this->error('中午开始时间不得大于结束时间');
        }
        if($a_s>$a_e){
            $this->error('下午开始时间不得大于结束时间');
        }
        if($m_e>=$a_s){
            $this->error('中午结束时间不得大于等于下午开始时间');
        }
        if($a_e == 0){
            $this->error('下午结束最大时间为23:59');
        }



        $arr = array(
            0=>array('start_time'=>$mid_start,
                'end_time'=>$mid_end),
            1=>array('start_time'=>$after_start,
                'end_time'=>$after_end),
        );

        $data['system_award_time'] = json_encode($arr);

        $m_sys_config = new \Admin\Model\SysConfigModel();
        $ret = $m_sys_config->updateInfo($data);
        if($ret){
            $sys_list = $m_sys_config->getList(array('status'=>1));
            $redis = SavorRedis::getInstance();
            $redis->select(12);
            $cache_key = C('SYSTEM_CONFIG');
            $redis->set($cache_key, json_encode($sys_list));
            $this->output('操作成功!', 'sysconfig/configData',2);
        }else {
            $this->error('操作失败3!');
        }
    }
    public function userkpi(){
        $m_sysconfig = new \Admin\Model\SysConfigModel();
        //per_bottle_cost    per_botte_award payback_day_commission
        $where = [];
        $where['status'] = 1;
        $where['config_key'] = array('in',array('per_bottle_cost','per_botte_award','person_award_coefficien','team_leader_award_coefficien','payback_day_commission'));
        $config_list = $m_sysconfig->where($where)->select();
        
        $per_bottle_cost = '';
        $per_botte_award = '';
        $person_award_coefficien = '';
        $team_leader_award_coefficien = '';
        $payback_day_commission = $this->payback_day_commission_conf;
        foreach($config_list as $key=>$v){
            if($v['config_key']=='per_bottle_cost'){
                $per_bottle_cost = $v['config_value'];
            }
            if($v['config_key']=='per_botte_award'){
                $per_botte_award = $v['config_value'];
            }
            if($v['config_key'] =='person_award_coefficien'){
                $person_award_coefficien = $v['config_value'];
            }
            if($v['config_key'] =='team_leader_award_coefficien'){
                $team_leader_award_coefficien = $v['config_value'];
            }
            if($v['config_key'] =='payback_day_commission' && !empty($v['config_value'])){
                $payback_day_commission = json_decode($v['config_value'],true);
            }
        }
        $config_info = [];
        $config_info['per_bottle_cost']              = $per_bottle_cost;
        $config_info['per_botte_award']              = $per_botte_award;
        $config_info['person_award_coefficien']      = $person_award_coefficien;
        $config_info['team_leader_award_coefficien'] = $team_leader_award_coefficien;
        $config_info['payback_day_commission'] = $payback_day_commission;
        //print_r($config_info);
        //echo  json_encode($payback_day_commission);
        
        $this->assign('config_info',$config_info);
        $this->display('userkpi');
    }
    public function updateUserkpi(){
        
        $per_bottle_cost              = I('post.per_bottle_cost');
        $per_botte_award              = I('post.per_botte_award');
        $person_award_coefficien      = I('post.person_award_coefficien');
        $team_leader_award_coefficien = I('post.team_leader_award_coefficien');
        $min                          = I('post.min');
        $max                          = I('post.max');
        $percent                      = I('post.percent');
        //print_r($percent);exit;
        $payback_day_commission_conf = $this->payback_day_commission_conf;
        //print_r($payback_day_commission_conf);exit;
        foreach($payback_day_commission_conf as $key=>$v){
            
            $payback_day_commission_conf[$key]['min']     = $min[$key];
            $payback_day_commission_conf[$key]['max']     = $max[$key];
            $payback_day_commission_conf[$key]['percent'] = $percent[$key];
        }
        $m_sysconfig = new \Admin\Model\SysConfigModel();
        
        $data = [];
        $data['per_bottle_cost']              = $per_bottle_cost;
        $data['per_botte_award']              = $per_botte_award;
        $data['person_award_coefficien']      = $person_award_coefficien;
        $data['team_leader_award_coefficien'] = $team_leader_award_coefficien;
        $data['payback_day_commission']       = json_encode($payback_day_commission_conf);
        
        $ret = $m_sysconfig->updateInfo($data);
        if($ret){
            $this->output('操作成功!', 'sysconfig/configData',2);
        }else {
            $this->error('操作失败!');
        }
    }

}