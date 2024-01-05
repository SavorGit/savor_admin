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
                $res_hotel = $m_hotel->getOne($hotel_id);
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
    //三代网络-互联网电视版位数据明细
    public function getNetBoxList(){
        
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(13);
        
        
        $heart_hotel_box_type = C('heart_hotel_box_type');
        $sql = "SELECT hotel.id hotel_id,hotel.name hotel_name,area.region_name ,hotel.addr ,
                room.name room_name,box.mac box_mac,box.box_type ,user.remark,box.`is_4g`,
                ext.is_salehotel,hotel.state hotel_state,box.state box_state 
                
                FROM savor_box box
                LEFT JOIN savor_room room ON box.room_id=room.id
                LEFT JOIN savor_hotel hotel ON room.hotel_id = hotel.id
                LEFT JOIN savor_area_info `area` ON hotel.area_id = area.id 
                LEFT JOIN savor_hotel_ext ext ON hotel.id = ext.hotel_id  
                LEFT JOIN savor_sysuser `user` ON ext.maintainer_id = user.id
                
                WHERE box.box_type IN(6,7) AND box.flag=0 AND box.state IN(1,2) AND hotel.flag=0 AND hotel.state IN(1,2)";
        $list = M()->query($sql);
        foreach($list as $key=>$v){
            $list[$key]['box_type_str'] = $heart_hotel_box_type[$v['box_type']];
            
            $list[$key]['is_4g'] = $v['is_4g']== 1 ? '是' : '否';
            
            $list[$key]['is_salehotel'] = $v['is_salehotel']== 1 ? '是' : '否';
            
            if($v['state']==2){
                $list[$key]['box_state'] = '冻结';
            }else {
                $list[$key]['box_state'] = $v['box_state']==1 ?'正常': '冻结'; 
            }
            
            $cache_key = 'heartbeat:2:'.$v['box_mac'];
            $cache_info = $redis->get($cache_key);
            $heart_info = json_decode($cache_info,true);
            if(!empty($heart_info)){
                
                $heart_time = strtotime($heart_info['date']);
                $now_time = time();
                $diff_time = floor(($now_time - $heart_time) / 86400);
                $list[$key]['last_heart_time_str'] = $diff_time ;
                
            }else {
                $list[$key]['last_heart_time_str'] = '30';
            }
            
        }
        $cell = array(
            array('hotel_id','酒楼ID'),
            array('region_name','城市'),
            array('hotel_name','酒楼名称'),
            array('addr','酒楼地址'),
            array('room_name','包间名称'),
            array('box_mac','机顶盒MAC'),
            array('box_type_str','设备类型'),
            array('remark','维护人'),
            array('is_4g','是否4G'),
            array('is_salehotel','售酒餐厅'),
            array('last_heart_time_str','失联天数'),
            array('box_state','版位状态'),
        );
        $filename = '三代网络版-互联网电视版位数据明细';
        $this->exportToExcel($cell,$list,$filename,1);
    }
}