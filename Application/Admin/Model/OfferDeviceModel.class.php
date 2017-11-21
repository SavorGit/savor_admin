<?php

namespace Admin\Model;

use Admin\Model\BaseModel;


class OfferDeviceModel extends BaseModel{
    protected $tableName  ='offer_device';
    public function getAllDevice($device_group){
        $where['state'] = 1;
        $where['device_group'] = $device_group;
        $data = $this->field('id,device_name')->where($where)->order('id asc')->select();    
        return $data;
    }
    public function getDeviceList($fields,$where,$order){
        $data = $this->alias('a')
             ->join('savor_offer_device_params b on a.id=b.device_id','left')
             ->field($fields)
             ->where($where)
             ->order($order)
             ->select();
        return $data;
    }
}