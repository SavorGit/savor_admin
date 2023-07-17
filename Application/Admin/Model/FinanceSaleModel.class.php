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

    public function handleExpireSale(){
        $diff_time = time() - 86400*7;
        $where = array('ptype'=>array('in','0,2'),'is_expire'=>0);
        $where['UNIX_TIMESTAMP(add_time)'] = array('elt',$diff_time);
        $this->updateData($where,array('is_expire'=>1));
    }
}