<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class HotelGoodsModel extends BaseModel{
	protected $tableName='smallapp_hotelgoods';

    public function getHotelgoodsList($fields,$where,$order, $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_hotel h on a.hotel_id=h.id','left')
            ->join('savor_area_info area on h.area_id=area.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $count = $this->alias('a')
            ->join('savor_hotel h on a.hotel_id=h.id','left')
            ->field('a.id')
            ->where($where)
            ->select();
        $count = count($count);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }


	public function chooseGoodsByHotelid($hotel_id=0){
        $hotel_goods = array();
	    if($hotel_id){
            $where = array('hotel_id'=>$hotel_id,'type'=>1);
            $res_hotelgoods = $this->where($where)->select();
            foreach ($res_hotelgoods as $v){
                $hotel_goods[] = $v['goods_id'];
            }
        }
        $m_goods = new \Admin\Model\Smallapp\GoodsModel();
	    $where = array('status'=>2);
	    $where['type'] = array('in',array(10,30,31));
	    $res_goods = $m_goods->field('id,name,type')->where($where)->select();
	    foreach ($res_goods as $k=>$v){
            $goods_id = $v['id'];
            switch ($v['type']){
                case 10:
                    $res_goods[$k]['name'] = '官方活动-'.$v['name'];
                    break;
                case 30:
                case 31:
                    $res_goods[$k]['name'] = '积分兑换-'.$v['name'];
                    break;
            }
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
            $where = array('hotel_id'=>$hotel_id,'type'=>1);
            $res_hotelgoods = $this->where($where)->limit(0,1)->order('id desc')->select();
            if(!empty($res_hotelgoods)){
                $this->delData($where);
                $is_issue = 1;
            }
        }else{
            foreach ($goods_ids as $v){
                $where = array('hotel_id'=>$hotel_id,'goods_id'=>$v,'openid'=>'','type'=>1);
                $res = $this->where($where)->find();
                if(empty($res)){
                    $this->add($where);
                    $is_issue = 1;
                }
            }
            $where = array('hotel_id'=>$hotel_id,'type'=>1,'openid'=>'');
            $where['goods_id'] = array('notin',$goods_ids);
            $res_del = $this->delData($where);
            if($res_del){
                $is_issue = 1;
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

    public function HandleGoodsperiod($goods_id=0){
        $redis = \Common\Lib\SavorRedis::getInstance();
        $redis->select(14);
        $cache_key = C('SAPP_SALE_ACTIVITYGOODS_PROGRAM');

        if($goods_id){
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
        }else{
            $keys = $redis->keys("$cache_key:*");
            foreach($keys as $key){
                $period = getMillisecond();
                $period_data = array('period'=>$period);
                $redis->set($key,json_encode($period_data));
            }
        }
        return true;
    }
    public function getGoodsList($fields,$where,$order,$limit,$group=''){
        $data = $this->alias('h')
        ->join('savor_hotel hotel on h.hotel_id=hotel.id','left')
        ->join('savor_smallapp_dishgoods g on h.goods_id=g.id','left')
        ->field($fields)
        ->where($where)
        ->group($group)
        ->order($order)
        ->limit($limit)
        ->select();
        return $data;
    }
}