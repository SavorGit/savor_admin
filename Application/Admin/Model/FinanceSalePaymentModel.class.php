<?php
namespace Admin\Model;
class FinanceSalePaymentModel extends BaseModel{
	protected $tableName='finance_sale_payment';

    public function getSalePaymentList($fileds,$where,$group=''){
        $res_data = $this->alias('a')
            ->field($fileds)
            ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
            ->where($where)
            ->group($group)
            ->select();
        return $res_data;
    }
}