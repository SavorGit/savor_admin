<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class OrdergoodsModel extends BaseModel{
    protected $tableName='smallapp_ordergoods';

    public function getOrdergoodsList($fields,$where,$orderby,$start=0,$size=0){
        $data = $this->alias('og')
            ->join('savor_smallapp_dishgoods goods on og.goods_id=goods.id','left')
            ->field($fields)
            ->where($where)
            ->order($orderby)
            ->limit($start,$size)
            ->select();
        return $data;
    }

}