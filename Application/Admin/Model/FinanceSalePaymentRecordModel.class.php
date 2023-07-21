<?php
namespace Admin\Model;
class FinanceSalePaymentRecordModel extends BaseModel{
	protected $tableName='finance_sale_payment_record';

    public function getSalePaymentRecordList($fileds,$where,$group=''){
        $res_data = $this->alias('a')
            ->field($fileds)
            ->join('savor_finance_sale sale on a.sale_id=sale.id','left')
            ->join('savor_finance_stock_record record on sale.stock_record_id=record.id','left')
            ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
            ->where($where)
            ->group($group)
            ->select();
        return $res_data;
    }
}