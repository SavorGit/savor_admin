<?php

namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class OfferResultModel extends BaseModel{
    protected $tableName  ='offer_result';
    public function addInfo($data){
        $this->add($data);
        return $this->getLastInsID();
    }
    public function getList($where, $order='id desc', $start=0,$size=5){	
		 $list = $this->where($where)
					  ->order($order)
					  ->limit($start,$size)
					  ->select();
		$count = $this->where($where)
					  ->count();
		$objPage = new Page($count,$size);		  
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
        return $data;
	}
}