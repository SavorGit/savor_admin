<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
/**
 * @desc 系统日志记录类
 *
 */
class SysconfigController extends BaseController {
    
     private $oss_host = '';
	 public function __construct(){
	     parent::__construct();
	     $this->oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
	 }
    /**
     * @desc 电视设置页面
     */
    public function configData(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        //$volume_info = $m_sys_config->getOne('system_default_volume');
        $switch_time_info = $m_sys_config->getOne('system_switch_time');
        
        $condition['config_key'] = 'system_default_volume';
        $condition['config_key'] = 'system_switch_time';
        $condition['config_key'] = 'system_demand_video_volume';
        $condition['config_key'] = 'system_tv_volume';
        $condition['_logic'] = 'OR';
        
        $volume_arr = $m_sys_config->getList($condition);
        
        //$info['system_default_volume'] = $volume_info['config_value'];
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
        if(empty($switch_time_info)){
            $map['config_key'] = 'system_switch_time';
            $map['config_value'] = $system_switch_time;
            $rts = $m_sys_config->add($map);
        }else {
            $map['config_value'] = $system_switch_time;
            $rts = $m_sys_config->editData($map, 'system_switch_time',2);
        }
        
        if($rts){
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
        $tranDb = M();
        $tranDb->startTrans();
        $data['status'] = $status;
        $rt = $m_sys_config->editData($data, 'system_default_volume');
        
        $map['status'] = $status;
        $rts = $m_sys_config->editData($map, 'system_switch_time');
        if($rt && $rts){
            $tranDb->commit();
            $this->output('操作成功!', 'sysconfig/configData',2);
        }else {
            $tranDb->rollback();
            $this->error('操作失败!');
        }
        
    }
    /**
     * @desc 音量设置
     */
    public function doConfigVolume(){
        $data = array();
        $data['system_ad_volume'] = I('post.system_ad_volume',0,'intval');             //广告轮播音量
        
        $data['system_pro_screen_volume']   = I('post.system_pro_screen_volume',0,'intval');    //投屏音量
        $data['system_demand_video_volume'] = I('post.system_demand_video_volume',0,'intval');   //点播音量
        $data['system_tv_volume']           = I('post.system_tv_volume',0,'intval');             //电视音量
        
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $ret = $m_sys_config->updateInfo($data);
        
        if($ret){
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
            $this->output('操作成功', 'Sysconfig/configData', 1);
        }else {
            $this->output('操作失败', 'Sysconfig/configData', 1);
        }
    }
}