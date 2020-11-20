<?php
namespace Dataexport\Controller;

class IntegralController extends BaseController{

    public function hotelrecord(){
        $m_integral = new \Admin\Model\Smallapp\UserIntegralModel();
        $sql = "select hotel.id as hotel_id,hotel.name as hotel_name,merchant.id as merchant_id,hotel.area_id from savor_smallapp_user_integral as a left join savor_integral_merchant_staff as staff on a.openid=staff.openid 
left join savor_integral_merchant as merchant on staff.merchant_id=merchant.id left join savor_hotel as hotel on merchant.hotel_id=hotel.id
where merchant.status=1 and hotel.id not in(7,883) group by hotel.id ";
        $res_integral = $m_integral->query($sql);
        $datalist = array();
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        $all_area = array();
        foreach ($area_arr as $v){
            $all_area[$v['id']] = $v['region_name'];
        }
        $m_box = new \Admin\Model\BoxModel();
        $m_staff = new \Admin\Model\Integral\StaffModel();
        $m_integralrecord = new \Admin\Model\Smallapp\UserIntegralrecordModel();
        $m_exchange = new \Admin\Model\Smallapp\ExchangeModel();
        $datalist = array();
        foreach ($res_integral as $v){
            $box_fields = 'count(box.id) as num';
            $bwhere = array('hotel.id'=>$v['hotel_id'],'box.state'=>1,'box.flag'=>0);
            $res_box = $m_box->getBoxByCondition($box_fields,$bwhere);
            $box_num = 0;
            if(!empty($res_box)){
                $box_num = intval($res_box[0]['num']);
            }
            $res_staff = $m_staff->getDataList('openid',array('merchant_id'=>$v['merchant_id']),'id desc');
            $openids = array();
            foreach ($res_staff as $sv){
                $openids[]=$sv['openid'];
            }
            $res_now_integral = $m_integral->getDataList('sum(integral) as integral',array('openid'=>array('in',$openids)));
            $now_integral = 0;
            if(!empty($res_now_integral)){
                $now_integral = intval($res_now_integral[0]['integral']);
            }
            $sql_exchange = "select sum(goods.rebate_integral) as integral from savor_smallapp_exchange as a left join savor_smallapp_goods as goods on a.goods_id=goods.id where a.hotel_id={$v['hotel_id']} and a.type=1 and a.status=21";
            $res_exchange = $m_exchange->query($sql_exchange);
            $exchange_integral = 0;
            if(!empty($res_exchange)){
                $exchange_integral = intval($res_exchange[0]['integral']);
            }
            $total_integral = $now_integral+$exchange_integral;


            /*
            $i_fields = 'sum(integral) as integral';
            $i_where = array('openid'=>array('in',$openids));
            $i_where['type'] = array('in',array(1,2,6,7,8));
            $all_integral = $m_integralrecord->getDataList($i_fields,$i_where,'id desc');
            $total_integral = 0;
            if(!empty($all_integral)){
                $total_integral = $all_integral[0]['integral'];
            }
            $i_where['type'] = 4;
            $all_exchange_integral = $m_integralrecord->getDataList($i_fields,$i_where,'id desc');
            $exchange_integral = 0;
            if(!empty($all_exchange_integral)){
                $exchange_integral = abs($all_exchange_integral[0]['integral']);
            }
            */


            if($total_integral>0){
                $datalist[] = array('hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],'area_name'=>$all_area[$v['area_id']],
                    'box_num'=>$box_num,'total_integral'=>$total_integral,'exchange_integral'=>$exchange_integral,
                    'now_integral'=>$now_integral);
            }
        }
        $cell = array(
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('area_name','城市'),
            array('box_num','设备数量'),
            array('total_integral','累计产生积分'),
            array('exchange_integral','已兑换积分'),
            array('now_integral','未兑换积分'),

        );
        $filename = '积分餐厅名录';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}