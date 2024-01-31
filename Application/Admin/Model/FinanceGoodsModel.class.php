<?php
namespace Admin\Model;
use Common\Lib\Page;
class FinanceGoodsModel extends BaseModel{

    protected $tableName='finance_goods';

    public function getList($fields,$where, $order){
        $list = $this->alias('goods')
            ->join('savor_finance_category cate on goods.category_id=cate.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->select();
        return $list;
    }
}
