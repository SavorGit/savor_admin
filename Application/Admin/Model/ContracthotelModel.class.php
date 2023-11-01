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

    public function getContractTime(){
        $fields = 'a.contract_id,a.hotel_id,contract.sign_time,contract.archive_time';
        $where = array('ext.is_salehotel'=>1,'hotel.state'=>1,'hotel.flag'=>0,'contract.type'=>20);
        $group = 'a.hotel_id';
        $res_data = $this->alias('a')
            ->join('savor_finance_contract contract on a.contract_id=contract.id','left')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
            ->field($fields)
            ->where($where)
            ->group($group)
            ->select();
        $all_times = array();
        foreach ($res_data as $v){
            $all_times[$v['hotel_id']] = $v;
        }
        return $all_times;
    }
}
