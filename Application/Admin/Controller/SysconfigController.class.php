<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
/**
 * @desc 系统日志记录类
 *
 */
class SysconfigController extends BaseController {
    
    /**
     * @desc 电视设置页面
     */
    public function configData(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $volume_info = $m_sys_config->getOne('system_default_volume');
        $switch_time_info = $m_sys_config->getOne('system_switch_time');
        $info['system_default_volume'] = $volume_info['config_value'];
        $info['system_switch_time']  = $switch_time_info['config_value'];
        $info['status'] = $volume_info['status'];
        $this->assign('info',$info);
        $this->display('Sysconfig/configdata');
    }
    /**
     * @desc 修改设置
     */
    public function doConfigData(){
        $system_default_volume = I('post.system_default_volume',0,'intval');
        $system_switch_time = I('post.system_switch_time',0,'intval');
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $volume_info = $m_sys_config->getOne('system_default_volume'); 
  
        if(empty($volume_info)){
            $data['config_key'] = 'system_default_volume';
            $data['config_value'] = $system_default_volume;;
            $rt = $m_sys_config->add($data);
        }else {
            $data['config_value'] = $system_default_volume;
            $rt = $m_sys_config->editData($data, 'system_default_volume');
        }
 
            $switch_time_info = $m_sys_config->getOne('system_switch_time');
            if(empty($switch_time_info)){
                $map['config_key'] = 'system_switch_time';
                $map['config_value'] = $system_switch_time;
                $rts = $m_sys_config->add($map);
            }else {
                $map['config_value'] = $system_switch_time;
                $rts = $m_sys_config->editData($map, 'system_switch_time',2);
            }
        
        if($rts || $rt){
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
            $this->error('操作失败3!');
        }
        
    }
}