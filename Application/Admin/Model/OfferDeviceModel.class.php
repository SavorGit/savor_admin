<?php

namespace Admin\Model;

use Admin\Model\BaseModel;


class OfferDeviceModel extends BaseModel{
    protected $tableName  ='offer_device';
    public function getAllDevice(){
        $where['state'] = 1;
        $data = $this->field('id,device_name')->where($where)->order('id asc')->select();    
        return $data;
    }
}