<?php

namespace Admin\Model;

use Admin\Model\BaseModel;


class OfferResultDetailModel extends BaseModel{
    protected $tableName  ='offer_result_detail';
    public function addInfo($data){
        $ret = $this->add($data);
        return $ret;
    }
    //获取设备报价列表
    public function getEquipList($result_id,$type){
        $sql ="select b.device_name,b.brand_name,c.standard,c.params,a.nums,a.market_price,a.our_price 
               from savor_offer_result_detail a
               left join savor_offer_device b on a.device_id=b.id
               left join savor_offer_device_params c on a.params_id = c.id
               where a.result_id = $result_id and a.type=$type
               order by a.id asc";
        $data = $this->query($sql);
        return $data;
    }
    public function getOtherList($result_id,$type){
        $sql ="select nums,market_price,our_price 
               from savor_offer_result_detail 
               where result_id=$result_id and type=$type  
               order by id asc";
        $data = $this->query($sql);
        return $data;
    }
}