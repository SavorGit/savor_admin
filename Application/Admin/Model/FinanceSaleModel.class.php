<?php
namespace Admin\Model;
class FinanceSaleModel extends BaseModel{
	protected $tableName='finance_sale';

    public function getSaleStockRecordList($fileds,$where,$order,$limit,$group=''){
        $res = $this->alias('a')
            ->field($fileds)
            ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
            ->join('savor_finance_stock stock on record.stock_id=stock.id','left')
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->group($group)
            ->select();
        return $res;
    }

}