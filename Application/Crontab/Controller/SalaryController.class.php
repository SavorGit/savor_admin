<?php
namespace Crontab\Controller;
use Think\Controller;

class SalaryController extends Controller{

    public function performanceconfig(){
        $nowdate = date('Ymd');
        $month_last_day = date('Ymt');
        if($nowdate!=$month_last_day){
            echo "time $nowdate!=$month_last_day error\r\n";
            exit;
        }
        $add_month = date('Ym');
        $m_sysconfig = new \Admin\Model\SysConfigModel();
        $cache_key = array('per_bottle_cost','per_botte_award','payback_day_commission',
            'person_award_coefficien','team_leader_award_coefficien');
        $where = array('config_key'=>array('in',$cache_key));
        $res_config = $m_sysconfig->getList($where);
        $add_data = array('add_month'=>$add_month);
        foreach ($res_config as $v){
            $value = $v['config_value'];
            if($v['config_key']=='payback_day_commission'){
                $value = json_decode($value,true);
                $value = json_encode($value);
            }
            $add_data[$v['config_key']] = $value;
        }
        $m_staff_config = new \Admin\Model\StaffPerformanceConfigModel();
        $m_staff_config->add($add_data);
        echo "$month_last_day data ok \r\n";
    }
}
