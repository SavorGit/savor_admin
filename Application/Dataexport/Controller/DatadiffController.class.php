<?php
namespace Dataexport\Controller;

class DatadiffController extends BaseController{

    public function hotel(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $estart_time = I('estart_time','');
        $eend_time = I('eend_time','');
        $small_app_id = I('small_app_id',0,'intval');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');

        $where = array();
        if($start_time && $end_time){
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        } else if($start_time && empty($end_time)){
            $end_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        }else if(empty($start_time) && !empty($end_time)){
            $start_time = '2018-07-23';
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        }else{
            $start_time = date('Y-m-d');
            $end_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        }
        $where['a.is_valid'] = 1;

        if($small_app_id){
            if($small_app_id == 2){
                $where['a.small_app_id'] = array('in',array(2,3));
            }else{
                $where['a.small_app_id'] = $small_app_id;
            }
        }

        $hotel_where = array('a.state'=>1,'a.flag'=>0);
        if($area_id){
            $where['area.id'] = $area_id;
            $hotel_where['a.area_id'] = $area_id;
        }

        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['hotel.hotel_box_type'] = $box_type;
            $hotel_where['a.hotel_box_type'] = $box_type;
        }else{
            $box_types = array_keys($hotel_box_types);
            $where['hotel.hotel_box_type'] = array('in',$box_types);
            $hotel_where['a.hotel_box_type'] = array('in',$box_types);
        }
        $where['box.flag'] = 0;
        $where['box.state'] = 1;
        $where['a.mobile_brand'] = array('neq','devtools');
        if($is_4g){
            if($is_4g == 1){
                $where['box.is_4g'] = 1;
                $hotel_where['a.is_4g'] = 1;
            }else{
                $where['box.is_4g'] = 0;
                $hotel_where['a.is_4g'] = 0;
            }
        }
        if($maintainer_id){
            $where['hotelext.maintainer_id'] = $maintainer_id;
            $hotel_where['ext.maintainer_id'] = $maintainer_id;
        }
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $fields = 'area.region_name,hotel.id as hotel_id,hotel.name as hotel_name,count(a.id) as num';
        $countfields = 'COUNT(DISTINCT(hotel.id)) AS tp_count';
        $result = $m_smallapp_forscreen_record->getCustomeList($fields,$where,$groupby='hotel.id',$order='num desc',$countfields,0,10000);
        $datalist = $result['list'];

        $res_ohotels = array();
        if($result['total']>0){
            $hfields = 'hotel.id as hotel_id';
            $res_hotel = $m_smallapp_forscreen_record->getCustomeList($hfields,$where,$groupby='hotel.id',$order='',$countfields,0,10000);
            $now_hotels = array();
            foreach ($res_hotel['list'] as $v){
                $now_hotels[] = $v['hotel_id'];
            }
            $m_invalid = new \Admin\Model\ForscreenInvalidlistModel();
            $res_invalidlist = $m_invalid->getDataList('invalidid',array('type'=>1),'id desc');
            foreach ($res_invalidlist as $v){
                $now_hotels[] = $v['invalidid'];
            }

            $ahfields = 'area.region_name,a.id as hotel_id,a.name as hotel_name';
            $m_hotel = new \Admin\Model\HotelModel();
            $res_ohotels = $m_hotel->getListExt($hotel_where,'a.id desc',0,10000,$ahfields);
            foreach ($res_ohotels['list'] as $k=>$v){
                if(in_array($v['hotel_id'],$now_hotels)){
                    unset($res_ohotels['list'][$k]);
                }
            }
        }

        if($estart_time && $eend_time){
            $where['a.create_time'] = array(array('EGT',$estart_time.' 00:00:00'),array('ELT',$eend_time.' 23:59:59'));
        } else if($estart_time && empty($eend_time)){
            $eend_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$estart_time.' 00:00:00'),array('ELT',$eend_time.' 23:59:59'));
        }else if(empty($estart_time) && !empty($eend_time)){
            $estart_time = '2018-07-23';
            $where['a.create_time'] = array(array('EGT',$estart_time.' 00:00:00'),array('ELT',$eend_time.' 23:59:59'));
        }else{
            $estart_time = date('Y-m-d');
            $eend_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$estart_time.' 00:00:00'),array('ELT',$eend_time.' 23:59:59'));
        }
        $users = array();
        $m_sysuser = new \Admin\Model\UserModel();
        $m_hotelext = new \Admin\Model\HotelExtModel();
        foreach ($datalist as $k=>$v){
            $fields = 'count(a.id) as num,hotel.id as hotel_id,hotelext.maintainer_id';
            $where['hotel.id'] = $v['hotel_id'];
            $res_info = $m_smallapp_forscreen_record->getInfo($fields,$where);

            $uid = $res_info['maintainer_id'];
            if(empty($uid)){
                $res_hotelinfo = $m_hotelext->getInfo(array('hotel_id'=>$v['hotel_id']));
                $uid = $res_hotelinfo['maintainer_id'];
            }
            if(array_key_exists($uid,$users)){
                $datalist[$k]['opname'] = $users[$uid]['remark'];
            }else{
                $res_uinfo = $m_sysuser->getUserInfo($uid);
                $users[$uid] = $res_uinfo;
                $datalist[$k]['opname'] = $res_uinfo['remark'];
            }
            $datalist[$k]['numb'] = $res_info['num'];
        }
        if(!empty($res_ohotels)){
            foreach ($res_ohotels['list'] as $v){
                $fields = 'count(a.id) as num,hotelext.maintainer_id';
                $where['hotel.id'] = $v['hotel_id'];
                $res_info = $m_smallapp_forscreen_record->getInfo($fields,$where);
                $uid = $res_info['maintainer_id'];
                if(empty($uid)){
                    $res_hotelinfo = $m_hotelext->getInfo(array('hotel_id'=>$v['hotel_id']));
                    $uid = $res_hotelinfo['maintainer_id'];
                }

                if(array_key_exists($uid,$users)){
                    $v['opname'] = $users[$uid]['remark'];
                }else{
                    $res_uinfo = $m_sysuser->getUserInfo($uid);
                    $users[$uid] = $res_uinfo;
                    $v['opname'] = $res_uinfo['remark'];
                }
                $v['numb'] = $res_info['num'];
                $v['num'] = 0;
                $datalist[] = $v;
            }
        }
        $num_str = "时间段A {$start_time}-{$end_time}互动次数";
        $numb_str = "时间段B {$estart_time}-{$eend_time}互动次数";
        $cell = array(
            array('hotel_name','酒楼名称'),
            array('num',$num_str),
            array('numb',$numb_str),
            array('opname','维护人'),
        );
        $filename = 'smallapp_interactdiff';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}