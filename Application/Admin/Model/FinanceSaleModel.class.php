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
        $now_date = date('Y-m-d');
        $sql_bill_day = "select a.bill_days,h.hotel_id,GROUP_CONCAT(a.id) as contract_ids,count(a.id) as num from savor_finance_contract as a 
        left join savor_finance_contract_hotel as h on a.id=h.contract_id where a.type=20 and a.status=1 and a.contract_etime>='$now_date' 
        group by h.hotel_id order by a.id desc";
        $res_bill_day = $this->query($sql_bill_day);
        foreach ($res_bill_day as $v){
            $hotel_id = intval($v['hotel_id']);
            $contract_num = intval($v['num']);
            if($hotel_id==0 || $contract_num==0){
                continue;
            }
            $bill_days = intval($v['bill_days']);
            if($contract_num>1){
                $sql_one_billday = "select bill_days from savor_finance_contract where id in({$v['contract_ids']}) order by id desc limit 0,1";
                $res_one_billday = $this->query($sql_one_billday);
                $bill_days = intval($res_one_billday[0]['bill_days']);
            }
            echo "hotel_id:$hotel_id,now_date:$now_date,bill_days:$bill_days \r\n";
            if($bill_days==0){
                continue;
            }
            $diff_time = time() - 86400*$bill_days;
            $where = array('hotel_id'=>$hotel_id,'ptype'=>array('in','0,2'),'is_expire'=>0);
            $where['goods_id'] = array('not in',C('DATA_GOODS_IDS'));
            $where['UNIX_TIMESTAMP(add_time)'] = array('elt',$diff_time);
            $this->updateData($where,array('is_expire'=>1));
        }
    }

    public function getqkmoney($hotel_id,$is_qk_money=1,$is_cqqk_money=1,$stime='',$etime=''){
        $qksale_where = array('a.hotel_id'=>$hotel_id,'record.wo_reason_type'=>1,'record.wo_status'=>2);
        if(!empty($stime) && !empty($etime)){
            $qksale_where['a.add_time'] = array(array('egt',$stime),array('elt',$etime));
        }
        $qksale_where['a.ptype'] = array('in','0,2');
        $qksale_where['a.goods_id'] = array('not in',C('DATA_GOODS_IDS'));
        $qk_money = $cqqk_money = 0;
        if($is_qk_money){
            $res_sale_qk = $this->alias('a')
                ->field('sum(a.settlement_price-a.pay_money) as money')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->where($qksale_where)
                ->select();
            $qk_money = $res_sale_qk[0]['money']>0?$res_sale_qk[0]['money']:0;
        }
        if($is_cqqk_money){
            $qksale_where['a.is_expire'] = 1;
            $res_sale_qk = $this->alias('a')
                ->field('sum(a.settlement_price-a.pay_money) as money')
                ->join('savor_finance_stock_record record on a.stock_record_id=record.id','left')
                ->where($qksale_where)
                ->select();
            $cqqk_money = $res_sale_qk[0]['money']>0?$res_sale_qk[0]['money']:0;
        }
        return array('qk_money'=>$qk_money,'cqqk_money'=>$cqqk_money);
    }
}