<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 促销活动配置
 *
 */
class ActivityconfigController extends BaseController {


    public function configdata(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $where = " config_key in('activity_adv_playtype','activity_boot_integral','activity_interact_integral')";
        $volume_arr = $m_sys_config->getList($where);
        $info = array();
        foreach($volume_arr as $v){
            $info[$v['config_key']] = $v['config_value'];
        }
        $this->assign('info',$info);
        $this->display('activityconfig');
    }


    /**
     * @desc 修改设置
     */
    public function editconfig(){
        $activity_adv_playtype = I('post.activity_adv_playtype',0,'intval');
        $activity_boot_integral = I('post.activity_boot_integral',0,'intval');
        $activity_interact_integral = I('post.activity_interact_integral',0,'intval');

        $m_sys_config = new \Admin\Model\SysConfigModel();
        if($activity_adv_playtype){
            $data_adv_playtype = array('config_value'=>$activity_adv_playtype);
            $rts = $m_sys_config->editData($data_adv_playtype, 'activity_adv_playtype');
        }

        if($activity_boot_integral){
            $data_boot_integral = array('config_value'=>$activity_boot_integral);
            $rts = $m_sys_config->editData($data_boot_integral, 'activity_boot_integral');
        }
        if($activity_interact_integral){
            $data_interact_integral = array('config_value'=>$activity_interact_integral);
            $rts = $m_sys_config->editData($data_interact_integral, 'activity_interact_integral');
        }

        if($rts){
            $sys_list = $m_sys_config->getList(array('status'=>1));
            $redis  =  \Common\Lib\SavorRedis::getInstance();
            $redis->select(12);
            $cache_key = C('SYSTEM_CONFIG');
            $redis->set($cache_key, json_encode($sys_list));
            $this->output('操作成功','sysconfig/configData');
        }else {
            $this->error('操作失败');
        }
    }
}