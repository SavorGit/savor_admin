<?php
namespace Dataexport\Controller;

class SigndataController extends BaseController{

    public function datalist(){
        $static_month = date('Ym',strtotime('-1 month'));
        $m_static_signdata = new \Admin\Model\StaticSigndataModel();
        $field = 'area_id,area_name,signer_id,signer_name,sum(num) as num,sum(day30_num) as day30_num,sum(new_num) as new_num,
        sum(old_num) as old_num';
        $datalist = $m_static_signdata->getAllData($field,array('static_month'=>$static_month),'','signer_id');
        $cell = array(
            array('area_name','城市'),
            array('signer_name','签约人'),
            array('num','签约家数'),
            array('day30_num','30天内酒水首次进店且售酒大于等于3瓶的家数'),
            array('old_num','老店家数'),
            array('new_num','新店家数'),
        );
        $filename = '签约数据统计';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}