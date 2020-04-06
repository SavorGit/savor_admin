<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 配置管理
 *
 */
class GlobalconfigController extends BaseController {

    public function configdata(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $where = " config_key in('distribution_profit')";
        $volume_arr = $m_sys_config->getList($where);
        $info = array();
        foreach($volume_arr as $v){
            $info[$v['config_key']] = $v['config_value'];
        }
        $this->assign('info',$info);
        $this->display('configdata');
    }

    public function editconfig(){
        $distribution_profit = I('post.distribution_profit',0);

        $m_sys_config = new \Admin\Model\SysConfigModel();
        $data_distribution_profit = array('config_value'=>$distribution_profit);
        $rts = $m_sys_config->editData($data_distribution_profit, 'distribution_profit');

        $sys_list = $m_sys_config->getList(array('status'=>1));
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(12);
        $cache_key = C('SYSTEM_CONFIG');
        $redis->set($cache_key, json_encode($sys_list));
        $this->output('操作成功','globalconfig/configdata');
    }

}