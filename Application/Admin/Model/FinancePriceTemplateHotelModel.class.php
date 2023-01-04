<?php
namespace Admin\Model;
class FinancePriceTemplateHotelModel extends BaseModel{

    protected $tableName='finance_price_template_hotel';

    public function handle_goods_settlement_price(){
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(9);
        $cache_key = C('FINANCE_HOTELGOODS_PRICE');
        $m_financegoods = new \Admin\Model\FinanceGoodsModel();
        $all_goods = $m_financegoods->getDataList('id,name',array('status'=>1),'id desc');
        $m_hotel = new \Admin\Model\HotelModel();
        $where = array('hotel.state'=>1,'hotel.flag'=>0,'ext.is_salehotel'=>1);
        $field = 'hotel.id as hotel_id,hotel.name as hotel_name';
        $res_hotels = $m_hotel->getHotelDatas($field,$where,'hotel.id asc');
        foreach ($res_hotels as $v){
            $hotel_id = $v['hotel_id'];
            $cache_hotel_key = $cache_key.":$hotel_id";
            $cache_hotel_data = array();
            foreach ($all_goods as $gv){
                $goods_id = $gv['id'];
                $settlement_price = $this->getHotelGoodsPrice($hotel_id,$goods_id);
                $cache_hotel_data[$goods_id] = $settlement_price;
            }
            $redis->set($cache_hotel_key,json_encode($cache_hotel_data));
        }

    }

    public function getHotelGoodsPrice($hotel_id,$goods_id){
        $where = array('a.hotel_id'=>array('in',"$hotel_id,0"),'a.goods_id'=>$goods_id,'t.status'=>1);
        $result = $this->alias('a')
            ->join('savor_finance_price_template t on a.template_id=t.id','left')
            ->field('a.template_id')
            ->where($where)
            ->order('t.id desc')
            ->limit(0,1)
            ->find();
        $settlement_price = 0;
        if(!empty($result)){
            $template_id = $result['template_id'];
            $m_pricegoods = new \Admin\Model\FinancePriceTemplateGoodsModel();
            $field = 'settlement_price';
            $res_pgoods = $m_pricegoods->getAll($field,array('template_id'=>$template_id,'goods_id'=>$goods_id),0,1,'id desc');
            if(!empty($res_pgoods[0]['settlement_price'])){
                $settlement_price = $res_pgoods[0]['settlement_price'];
            }
        }
        return $settlement_price;
    }
}
