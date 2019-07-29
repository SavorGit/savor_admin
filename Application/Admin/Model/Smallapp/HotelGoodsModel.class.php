<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class HotelGoodsModel extends BaseModel{
	protected $tableName='smallapp_hotelgoods';


	public function chooseGoodsByHotelid($hotel_id=0){
        $hotel_goods = array();
	    if($hotel_id){
            $where = array('hotel_id'=>$hotel_id);
            $res_hotelgoods = $this->where($where)->select();
            foreach ($res_hotelgoods as $v){
                $hotel_goods[] = $v['goods_id'];
            }
        }
        $m_goods = new \Admin\Model\Smallapp\GoodsModel();
	    $where = array('type'=>10,'status'=>2);
	    $res_goods = $m_goods->field('id,name')->where($where)->select();
	    foreach ($res_goods as $k=>$v){
            $goods_id = $v['id'];
            if(in_array($goods_id,$hotel_goods)){
                $select = "selected='selected'";
            }else{
                $select='';
            }
            $res_goods[$k]['select'] = $select;
        }
        return $res_goods;
    }

    public function HandleHotelGoods($hotel_id,$goods_ids){
	    $is_issue = 0;
	    if(empty($goods_ids)){
            $where = array('hotel_id'=>$hotel_id);
            $res_hotelgoods = $this->where($where)->limit(0,1)->order('id desc')->select();
            if(!empty($res_hotelgoods)){
                $this->delData($where);
                $is_issue = 1;
            }
        }else{
            foreach ($goods_ids as $v){
                $where = array('hotel_id'=>$hotel_id,'goods_id'=>$v);
                $res = $this->where($where)->find();
                if(empty($res)){
                    $this->add($where);
                    $is_issue = 1;
                }
            }
        }
        if($is_issue==1){
            $redis = \Common\Lib\SavorRedis::getInstance();
            $redis->select(14);
            $program_key = C('SAPP_SALE_ACTIVITYGOODS_PROGRAM').":$hotel_id";
            $period = getMillisecond();
            $period_data = array('period'=>$period);
            $redis->set($program_key,json_encode($period_data));
        }
        return true;
    }

    public function HandleGoodsperiod($goods_id){
        $redis = \Common\Lib\SavorRedis::getInstance();
        $redis->select(14);
        $cache_key = C('SAPP_SALE_ACTIVITYGOODS_PROGRAM');

        $where = array('goods_id'=>$goods_id);
        $group = 'hotel_id';
        $res_hotels = $this->where($where)->group($group)->select();
        if(!empty($res_hotels)){
            foreach ($res_hotels as $v){
                $hotel_id = $v['hotel_id'];

                $program_key = $cache_key.":$hotel_id";
                $period = getMillisecond();
                $period_data = array('period'=>$period);
                $redis->set($program_key,json_encode($period_data));
            }
        }
        return true;
    }
}