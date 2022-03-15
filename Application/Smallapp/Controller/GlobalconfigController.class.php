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
        $where = " config_key in('distribution_profit','hotellottery_people_num','seckill_roll_content')";
        $volume_arr = $m_sys_config->getList($where);
        $info = array();
        $now_roll_content = array();
        foreach($volume_arr as $v){
            $config_value = $v['config_value'];
            if($v['config_key']=='seckill_roll_content'){
                $d_roll_content = json_decode($config_value,true);
                foreach ($d_roll_content as $ck=>$cv){
                    if(!empty($cv)){
                        $now_roll_content[$ck+1] = $cv;
                    }
                }
                $config_value = $now_roll_content;
            }
            $info[$v['config_key']] = $config_value;
        }
        $roll_contents = array();
        $roll_num = 3;
        for($i=1;$i<=$roll_num;$i++){
            $rinfo = array('id'=>$i,'content'=>'');
            if(isset($now_roll_content[$i])){
                $rinfo['content'] = $now_roll_content[$i];
            }
            $roll_contents[] = $rinfo;
        }
        $info['seckill_roll_content'] = $roll_contents;

        $this->assign('info',$info);
        $this->display('configdata');
    }

    public function editconfig(){
        $distribution_profit = I('post.distribution_profit',0);
        $hotellottery_people_num = I('post.hotellottery_people_num',0);
        $seckill_roll_content = I('post.seckill_roll_content','');

        $m_sys_config = new \Admin\Model\SysConfigModel();
        $data_distribution_profit = array('config_value'=>$distribution_profit);
        $rts = $m_sys_config->editData($data_distribution_profit, 'distribution_profit');

        $data_hotellottery_people_num = array('config_value'=>$hotellottery_people_num);
        $rts = $m_sys_config->editData($data_hotellottery_people_num, 'hotellottery_people_num');

        $now_seckill_roll_content = array();
        if(!empty($seckill_roll_content)){
            foreach ($seckill_roll_content as $v){
                if(!empty($v)){
                    $now_seckill_roll_content[]=trim($v);
                }
            }
        }
        $data_seckill_roll_content = array('config_value'=>json_encode($now_seckill_roll_content));
        $rts = $m_sys_config->editData($data_seckill_roll_content, 'seckill_roll_content');

        $sys_list = $m_sys_config->getList(array('status'=>1));
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(12);
        $cache_key = C('SYSTEM_CONFIG');
        $redis->set($cache_key, json_encode($sys_list));
        $this->output('操作成功','globalconfig/configdata');
    }

}