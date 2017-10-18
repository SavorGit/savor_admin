<?php

namespace Admin\Model;

use Admin\Model\BaseModel;


class OfferDeviceParamsModel extends BaseModel{
    protected $tableName  ='offer_device_params';
    public function getStandardList($device_id){
        $data = $this->alias('a')
             ->join('savor_offer_device b on a.device_id=b.id','left')
             ->field('a.id,a.standard,a.params,b.brand_name')
             ->where('a.device_id='.$device_id)
             ->order('a.id asc')
             ->select();
        return $data;
    }
    public function getStandardInfo($id){
        $info = $this->field('params')->where('id='.$id)->find();
        return $info;
    }
}