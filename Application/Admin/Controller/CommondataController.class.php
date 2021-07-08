<?php
namespace Admin\Controller;
use Think\Controller;

class CommondataController extends Controller {

    public function getHotels(){
        $area_id = I('area_id',0,'intval');
        $hotel_id = I('hotel_id',0,'intval');
        $hotel_name = I('hotel_name','','trim');

        $m_hotel = new \Admin\Model\HotelModel();
        $field = 'a.id as hotel_id,a.name as hotel_name,a.gps';
        $where = array('a.state'=>1,'a.flag'=>0,'a.type'=>1);
        $all_hotel_types = C('heart_hotel_box_type');
        $hotel_box_type_arr = array_keys($all_hotel_types);
        $where['a.hotel_box_type'] = array('in',$hotel_box_type_arr);
        if(!empty($hotel_name)){
            $where['a.name'] = array('like',"%{$hotel_name}%");
        }
        $all_km = C('HOTEL_KM');
        if($area_id>0){
            if(isset($all_km[$area_id])){
                $res_hotel = $m_hotel->getInfo('*',array('id'=>$hotel_id),'','');
                $where['a.area_id'] = $res_hotel[0]['area_id'];
            }else{
                $where['a.area_id'] = $area_id;
            }
        }
        $res_hotels = $m_hotel->getHotels($field,$where);
        $result = array();
        if(isset($all_km[$area_id])){
            $now_km = $all_km[$area_id]['km']*1000;
            $gps_arr = explode(',',$res_hotel[0]['gps']);
            $longitude = $gps_arr[0];
            $latitude = $gps_arr[1];
            if($longitude>0 && $latitude>0){
                foreach($res_hotels as $key=>$v){
                    $res_hotels[$key]['dis'] = '';
                    if($v['gps']!='' && $longitude>0 && $latitude>0){
                        $gps_arr = explode(',',$v['gps']);
                        $dis_com = geo_distance($latitude,$longitude,$gps_arr[1],$gps_arr[0]);
                        $res_hotels[$key]['dis_com'] = $dis_com;
                        if($dis_com>1000){
                            $tmp_dis = $dis_com/1000;
                            $dis = sprintf('%0.2f',$tmp_dis);
                            $dis = $dis.'km';
                        }else{
                            $dis = intval($dis_com);
                            $dis = $dis.'m';
                        }
                        $res_hotels[$key]['dis'] = $dis;
                        if($dis_com<=$now_km){
                            $result[]=$res_hotels[$key];
                        }
                    }else {
                        $res_hotels[$key]['dis'] = '';
                    }
                }
                sortArrByOneField($result,'dis_com');
                foreach ($result as $k=>$v){
                    $result[$k]['hotel_name'] = $v['hotel_name']."({$v['dis']})";
                }
            }
        }else{
            $result = $res_hotels;
        }

        $this->assign('area_id',$area_id);
        $this->assign('hotel_name',$hotel_name);
        $msg = '';
        $res = array('code'=>1,'msg'=>$msg,'data'=>$result);
        echo json_encode($res);
    }

}