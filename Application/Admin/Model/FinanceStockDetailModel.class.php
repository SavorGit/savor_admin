<?php
namespace Admin\Model;
class FinanceStockDetailModel extends BaseModel{

    protected $tableName='finance_stock_detail';

    public function getHotelStockGoods($fileds,$where,$group='',$start=0,$size=5){
        $limit = "";
        if($start>=0 && $size>0){
            $limit = "$start,$size";
        }
        $data = $this->alias('a')
            ->field($fileds)
            ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
            ->join('savor_hotel hotel on stock.hotel_id=hotel.id','left')
            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
            ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
            ->join('savor_finance_category cate on goods.category_id=cate.id','left')
            ->join('savor_finance_specification spec on goods.specification_id=spec.id','left')
            ->where($where)
            ->limit($limit)
            ->group($group)
            ->select();
        return $data;
    }
}
