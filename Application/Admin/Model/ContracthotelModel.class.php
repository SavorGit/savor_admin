<?php
namespace Admin\Model;
use Common\Lib\Page;

class ContracthotelModel extends BaseModel{

    protected $tableName='finance_contract_hotel';

    public function getList($fields,$where,$orderby,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->alias('a')
                ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->limit($start,$size)
                ->select();
            $count = $this->alias('a')
                ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
                ->where($where)
                ->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->alias('a')
                ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
                ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->select();
        }
        return $data;
    }
}
