<?php
namespace Admin\Model;
class FinanceStockRecordModel extends BaseModel{

    protected $tableName='finance_stock_record';

    public function getStockRecordList($fileds,$where,$order,$limit,$group=''){
        $res = $this->alias('a')
            ->field($fileds)
            ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
            ->join('savor_finance_goods goods on a.goods_id=goods.id','left')
            ->join('savor_finance_unit unit on a.unit_id=unit.id','left')
            ->join('savor_finance_category cate on goods.category_id=cate.id','left')
            ->join('savor_finance_specification spec on goods.specification_id=spec.id','left')
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->group($group)
            ->select();
        return $res;
    }

    public function getSellIndateHotels(){
        $fileds = 'stock.hotel_id,MIN(a.add_time) as hotel_in_time';
        $where = array('a.type'=>4);
        $res = $this->alias('a')
            ->field($fileds)
            ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
            ->where($where)
            ->group('stock.hotel_id')
            ->select();
        $datalist = array();
        foreach ($res as $v){
            $datalist[$v['hotel_id']]=$v['hotel_in_time'];
        }
        return $datalist;
    }

    public function getSellDateHotels(){
        $fileds = 'stock.hotel_id,MIN(a.add_time) as sell_time';
        $where = array('a.type'=>7,'a.wo_reason_type'=>1,'a.wo_status'=>array('in','1,2,4'));
        $res = $this->alias('a')
            ->field($fileds)
            ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
            ->where($where)
            ->group('stock.hotel_id')
            ->select();
        $datalist = array();
        foreach ($res as $v){
            $datalist[$v['hotel_id']]=$v['sell_time'];
        }
        return $datalist;
    }

    public function getHotelSellwineNums($start_time,$end_time,$hotel_id=0){
        $start_time = date('Y-m-d 00:00:00',strtotime($start_time));
        $end_time = date('Y-m-d 23:59:59',strtotime($end_time));
        $fileds = 'stock.hotel_id,sum(a.total_amount) as total_amount';
        $where = array('a.type'=>7,'a.wo_reason_type'=>1,'a.wo_status'=>array('in','1,2,4'),'a.add_time'=>array(array('egt',$start_time),array('elt',$end_time)));
        if($hotel_id){
            $where['stock.hotel_id'] = $hotel_id;
        }
        $res = $this->alias('a')
            ->field($fileds)
            ->join('savor_finance_stock stock on a.stock_id=stock.id','left')
            ->where($where)
            ->group('stock.hotel_id')
            ->select();
        $datalist = array();
        foreach ($res as $v){
            $datalist[$v['hotel_id']]=abs($v['total_amount']);
        }
        return $datalist;
    }
}
