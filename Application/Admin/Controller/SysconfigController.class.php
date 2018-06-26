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
	 public function __construct(){
	     parent::__construct();
	     $this->oss_host = get_oss_host();
	 }
    /**
     * @desc 电视设置页面
     */
    public function configData(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        //$volume_info = $m_sys_config->getOne('system_default_volume');
        $switch_time_info = $m_sys_config->getOne('system_switch_time');
       
        $where = " config_key in('system_ad_volume','system_pro_screen_volume','system_demand_video_volume','system_tv_volume','system_award_time')";
        $volume_arr = $m_sys_config->getList($where);
       
        foreach($volume_arr as $key=>$v){
            if($v['config_key']=='system_ad_volume'){
                $info['system_ad_volume'] = $v['config_value'];
            }else if($v['config_key']=='system_pro_screen_volume'){
                $info['system_pro_screen_volume'] = $v['config_value'];
            }else if($v['config_key']=='system_demand_video_volume'){
                $info['system_demand_video_volume'] = $v['config_value'];
            }else if($v['config_key']=='system_tv_volume'){
                $info['system_tv_volume'] = $v['config_value'];
            }else if($v['config_key']=='system_award_time'){
                $info['award_time'] = json_decode($v['config_value'],true);
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
        $data = array();
        $data['system_ad_volume'] = I('post.system_ad_volume','','trim');             //广告轮播音量
        
        $data['system_pro_screen_volume']   = I('post.system_pro_screen_volume','','trim');    //投屏音量
        $data['system_demand_video_volume'] = I('post.system_demand_video_volume','','trim');   //点播音量
        $data['system_tv_volume']           = I('post.system_tv_volume','','trim');             //电视音量
        
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
        $m_sys_config = new \Admin\Model\SysConfigModel();
        //视频投屏loading图
        $loading_info = $m_sys_config->getOne('system_loading_image');
        $data = array();
        if(empty($loading_info)){
            $data['config_key'] = 'system_loading_image'; 
            $data['config_value'] = I('post.media_id',0,'intval');
            $rt = $m_sys_config->add($data);
        }else {
            $data['config_value'] = I('post.media_id',0,'intval');
            $rt = $m_sys_config->editData($data, 'system_loading_image');
        }
        if($rt){
            $sys_list = $m_sys_config->getList(array('status'=>1));
            $redis = SavorRedis::getInstance();
            $redis->select(12);
            $cache_key = C('SYSTEM_CONFIG');
            $redis->set($cache_key, json_encode($sys_list));
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

}