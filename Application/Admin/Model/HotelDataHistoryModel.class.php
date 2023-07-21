<?php
namespace Admin\Model;
use Common\Lib\Page;

class HotelDataHistoryModel extends BaseModel{
    protected $tableName='hotel_data_history';

    public function recordhistory(){
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $start = date('Y-m-d',strtotime('-1day'));
        $end = date('Y-m-d',strtotime('-1day'));
        $all_dates = $m_statistics->getDates($start,$end);
        $m_hotel_ext = new \Admin\Model\HotelExtModel();

        foreach ($all_dates as $v){
            $add_data = array();
            $static_date = $v;

            $sql_ext = "select hotel_id,maintainer_id,signer_id,residenter_id from savor_hotel_ext where maintainer_id+signer_id+residenter_id>0 order by id desc ";
            $res_hotel = $m_hotel_ext->query($sql_ext);
            foreach ($res_hotel as $hv){
                $add_data[]=array('hotel_id'=>$hv['hotel_id'],'maintainer_id'=>$hv['maintainer_id'],'signer_id'=>$hv['signer_id'],'residenter_id'=>$hv['residenter_id'],'static_date'=>$static_date);
            }
            $this->addAll($add_data);
        }
    }
}