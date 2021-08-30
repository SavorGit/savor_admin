<?php
namespace Dataexport\Controller;

class BoxController extends BaseController{

    public function nousewechat(){
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(20);
        $cache_wechat_key = 'box_use_wechat';
        $res_wdata = $redis->get($cache_wechat_key);
        $result = json_decode($res_wdata,true);

        $datalist = array();
        if(!empty($result)){
            $m_hotel = new \Admin\Model\HotelModel();
            foreach ($result as $k=>$v){
                $hotel_id = $v['hotel_id'];
                $res_hotel = $m_hotel->getInfo(array('id'=>$hotel_id));
                $hotel_name = $res_hotel['name'];
                $apk_version = $v['apk_version'];
                $add_time = $v['add_time'];
                $datalist[]=array('hotel_id'=>$hotel_id,'hotel_name'=>$hotel_name,
                    'box_mac'=>$k,'apk_version'=>$apk_version,'add_time'=>$add_time);
            }
        }
        $cell = array(
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('box_mac','机顶盒MAC'),
            array('add_time','上报时间'),
        );
        $filename = '不可用微信机顶盒';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}