<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class StoreModel extends BaseModel{
	protected $tableName='smallapp_store';

    public function getStoreAdsList($fields="a.id",$where, $order='a.id desc'){
        $list = $this->alias('a')
            ->join('savor_ads ads on a.ads_id=ads.id', 'left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->select();
        return $list;
    }
}