<?php
namespace Dataexport\Controller;

class ResidentController extends BaseController{

    public function datalist(){
//        $static_month = date('Ym',strtotime('-1 month'));
        $static_month = date('Ym');
        $m_static_resident = new \Admin\Model\StaticResidentModel();
        $field = 'area_id,area_name,residenter_id,residenter_name,sum(num) as num,avg(task_demand_finish_rate) as task_demand_finish_rate,
        avg(task_invitation_finish_rate) as task_invitation_finish_rate,sum(sale_money) as sale_money';
        $datalist = $m_static_resident->getAllData($field,array('static_month'=>$static_month),'','signer_id');
        $cell = array(
            array('area_name','城市'),
            array('signer_name','驻店人'),
            array('num','销售量'),
            array('task_demand_finish_rate','点播任务完成率'),
            array('task_invitation_finish_rate','邀请函任务完成率'),
            array('sale_money','餐厅售卖回款额'),
        );
        $filename = '驻店数据统计';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}