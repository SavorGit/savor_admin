<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class OrderinvoiceModel extends BaseModel{
	protected $tableName='smallapp_orderinvoice';

    public function getOrderInvoiceList($fields,$where,$order, $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_smallapp_order o on a.order_id=o.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $count = $this->alias('a')
            ->join('savor_smallapp_order o on a.order_id=o.id','left')
            ->field('a.id')
            ->where($where)
            ->select();
        $count = count($count);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

}