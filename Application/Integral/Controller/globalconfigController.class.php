<?php
namespace Integral\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 促销活动配置
 *
 */
class GlobalconfigController extends BaseController {


    public function configdata(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $where = " config_key in('integral_exchange_rate','red_packet_rate')";
        $volume_arr = $m_sys_config->getList($where);
        $info = array();
        foreach($volume_arr as $v){
            $info[$v['config_key']] = $v['config_value'];
        }
        $this->assign('info',$info);
        $this->display('configdata');
    }


    /**
     * @desc 修改设置
     */
    public function editconfig(){
        $integral_exchange_rate = I('post.integral_exchange_rate',0,'intval');
        $red_packet_rate = I('post.red_packet_rate');

        $m_sys_config = new \Admin\Model\SysConfigModel();
        if($integral_exchange_rate){
            $data = array('config_value'=>$integral_exchange_rate);
            $m_sys_config->editData($data, 'integral_exchange_rate');
        }

        if($red_packet_rate && $red_packet_rate>0.1){
            $this->output('红包汇率太大,请减小配置','globalconfig/configdata');
        }
        if($red_packet_rate){
            $data = array('config_value'=>$red_packet_rate);
            $m_sys_config->editData($data, 'red_packet_rate');
        }
        $sys_list = $m_sys_config->getList(array('status'=>1));
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(12);
        $cache_key = C('SYSTEM_CONFIG');
        $redis->set($cache_key, json_encode($sys_list));
        $this->output('操作成功','globalconfig/configdata');
    }
}