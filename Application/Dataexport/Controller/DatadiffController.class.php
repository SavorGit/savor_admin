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
            if($small_app_id == 1){
                $where['a.small_app_id'] = array('in',array(1,2,11));
            }else{
                $where['a.small_app_id'] = $small_app_id;
            }
        }

        $hotel_where = array('a.state'=>1,'a.flag'=>0);
        if($area_id){
            $where['a.area_id'] = $area_id;
            $hotel_where['a.area_id'] = $area_id;
        }

        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['a.hotel_box_type'] = $box_type;
            $hotel_where['a.hotel_box_type'] = $box_type;
        }else{
            $box_types = array_keys($hotel_box_types);
            $where['a.hotel_box_type'] = array('in',$box_types);
            $hotel_where['a.hotel_box_type'] = array('in',$box_types);
        }
        $where['a.mobile_brand'] = array('neq','devtools');
        if($is_4g){
            if($is_4g == 1){
                $where['a.is_4g'] = 1;
                $hotel_where['a.is_4g'] = 1;
            }else{
                $where['a.is_4g'] = 0;
                $hotel_where['a.is_4g'] = 0;
            }
        }
        if($maintainer_id){
            $where['hotelext.maintainer_id'] = $maintainer_id;
            $hotel_where['ext.maintainer_id'] = $maintainer_id;
        }
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $fields = 'a.area_name as region_name,a.hotel_id,a.hotel_name,count(a.id) as num,count(DISTINCT(a.box_mac)) as boxnum';
        $countfields = 'COUNT(DISTINCT(a.hotel_id)) AS tp_count';
        $result = $m_smallapp_forscreen_record->getAllDatas($fields,$where,'a.hotel_id','num desc',$countfields,0,10000);
        $datalist = $result['list'];
        $hotel_ids = array();
        foreach ($datalist as $v){
            $hotel_ids[]=$v['hotel_id'];
        }

        $res_ohotels = array();
        $ohotel_ids = array();
        if($result['total']>0){
            $hfields = 'a.hotel_id';
            $res_hotel = $m_smallapp_forscreen_record->getAllDatas($hfields,$where,'a.hotel_id','',$countfields,0,10000);
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
                }else{
                    $ohotel_ids[]=$v['hotel_id'];
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
        $fields = 'a.hotel_id,count(a.id) as num,count(DISTINCT (a.box_mac)) as boxnum';
        $where['a.hotel_id'] = array('in',$hotel_ids);
        $res_datalist_b = $m_smallapp_forscreen_record->getDatas($fields,$where,'','a.hotel_id');

        $datalist_b = array();
        foreach ($res_datalist_b as $v){
            $datalist_b[$v['hotel_id']] = $v;
        }

        $users = array();
        $m_sysuser = new \Admin\Model\UserModel();
        $m_hotelext = new \Admin\Model\HotelExtModel();
        $m_box = new \Admin\Model\BoxModel();
        foreach ($datalist as $k=>$v){
            $b_where = array('hotel.id'=>$v['hotel_id'],'box.flag'=>0,'box.state'=>1);
            $all_box = $m_box->countNums($b_where);

            $numb = $b_boxnums = 0;
            if(isset($datalist_b[$v['hotel_id']])){
                $numb = $datalist_b[$v['hotel_id']]['num'];
                $b_boxnums = $datalist_b[$v['hotel_id']]['boxnum'];
            }
            $datalist[$k]['numb'] = $numb;
            $datalist[$k]['b_boxnum'] = $b_boxnums;
            $datalist[$k]['b_coverage'] = sprintf("%0.2f",$b_boxnums/$all_box);
            //时间段A
            $a_boxnums = $v['boxnum'];
            $datalist[$k]['a_boxnum'] = $a_boxnums;
            $datalist[$k]['a_coverage'] = sprintf("%0.2f",$a_boxnums/$all_box);

            $res_hotelinfo = $m_hotelext->getInfo(array('hotel_id'=>$v['hotel_id']));
            $uid = $res_hotelinfo['maintainer_id'];

            if(array_key_exists($uid,$users)){
                $datalist[$k]['opname'] = $users[$uid]['remark'];
            }else{
                $res_uinfo = $m_sysuser->getUserInfo($uid);
                $users[$uid] = $res_uinfo;
                $datalist[$k]['opname'] = $res_uinfo['remark'];
            }
        }

        if(!empty($res_ohotels)){
            $fields = 'a.hotel_id,count(a.id) as num,count(DISTINCT (a.box_mac)) as boxnum';
            $where['a.hotel_id'] = array('in',$ohotel_ids);
            $res_ohters = $m_smallapp_forscreen_record->getDatas($fields,$where,'','a.hotel_id');
            $other_hotels = array();
            foreach ($res_ohters as $v){
                $other_hotels[$v['hotel_id']] = $v;
            }

            foreach ($res_ohotels['list'] as $v){
                $b_where = array('hotel.id'=>$v['hotel_id'],'box.flag'=>0,'box.state'=>1);
                $all_box = $m_box->countNums($b_where);

                $numb = $b_boxnums = 0;
                if(isset($other_hotels[$v['hotel_id']])){
                    $numb = $other_hotels[$v['hotel_id']]['num'];
                    $b_boxnums = $other_hotels[$v['hotel_id']]['boxnum'];
                }
                $v['numb'] = $numb;
                $v['num'] = 0;
                $v['a_boxnum'] = 0;
                $v['a_coverage'] = 0.00;
                $v['b_boxnum'] = $b_boxnums;
                $v['b_coverage'] = sprintf("%0.2f",$b_boxnums/$all_box);

                $res_hotelinfo = $m_hotelext->getInfo(array('hotel_id'=>$v['hotel_id']));
                $uid = $res_hotelinfo['maintainer_id'];
                if(array_key_exists($uid,$users)){
                    $v['opname'] = $users[$uid]['remark'];
                }else{
                    $res_uinfo = $m_sysuser->getUserInfo($uid);
                    $users[$uid] = $res_uinfo;
                    $v['opname'] = $res_uinfo['remark'];
                }
                $datalist[] = $v;
            }
        }
        $num_str = "时间段A {$start_time}-{$end_time}互动次数";
        $numb_str = "时间段B {$estart_time}-{$eend_time}互动次数";
        $cell = array(
            array('hotel_name','酒楼名称'),
            array('num',$num_str),
            array('a_coverage','时间段A覆盖率'),
            array('numb',$numb_str),
            array('b_coverage','时间段B覆盖率'),
            array('opname','维护人'),
        );
        $filename = 'smallapp_interactdiff';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

    public function box(){
        $hotel_id = I('hotel_id',0,'intval');
        $hotel_name = I('hotel_name','');
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $estart_time = I('estart_time','');
        $eend_time = I('eend_time','');
        $small_app_id = I('small_app_id',0,'intval');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');

        $where = array('a.hotel_id'=>$hotel_id);
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
        $where['a.mobile_brand'] = array('neq','devtools');
        if($small_app_id){
            if($small_app_id == 2){
                $where['a.small_app_id'] = array('in',array(2,3));
            }else{
                $where['a.small_app_id'] = $small_app_id;
            }
        }

        if($area_id){
            $where['a.area_id'] = $area_id;
        }
        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['a.hotel_box_type'] = $box_type;
        }else{
            $box_types = array_keys($hotel_box_types);
            $where['a.hotel_box_type'] = array('in',$box_types);
        }
        $box_condition = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
        if($is_4g){
            if($is_4g == 1){
                $where['a.is_4g'] = 1;
                $box_condition['box.is_4g'] = 1;
            }else{
                $where['a.is_4g'] = 0;
                $box_condition['box.is_4g'] = 0;
            }
        }
        $m_box = new \Admin\Model\BoxModel();
        $box_fields = 'hotel.id as hotel_id,hotel.name as hotel_name,box.mac as box_mac,box.name as box_name';
        $res_box = $m_box->getBoxByCondition($box_fields,$box_condition);
        $all_box = array();
        foreach ($res_box as $v){
            $all_box[$v['box_mac']] = $v;
        }

        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $fields = 'a.hotel_id,a.hotel_name,a.box_mac,a.box_name,count(a.id) as num';
        $countfields = 'COUNT(DISTINCT(a.box_mac)) AS tp_count';
        $result = $m_smallapp_forscreen_record->getAllDatas($fields,$where,'a.box_mac','num desc',$countfields,0,1000);
        $datalist = $result['list'];
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
        foreach ($datalist as $k=>$v){
            $fields = 'count(a.id) as num';
            $where['a.box_mac'] = $v['box_mac'];
            $res_info = $m_smallapp_forscreen_record->getForscreenInfo($fields,$where);
            $datalist[$k]['numb'] = $res_info[0]['num'];
            if(array_key_exists($v['box_mac'],$all_box)){
                unset($all_box[$v['box_mac']]);
            }
        }

        $m_invalid = new \Admin\Model\ForscreenInvalidlistModel();
        $res_invalidlist = $m_invalid->getDataList('invalidid',array('type'=>3),'id desc');
        $invalid_box = array();
        foreach ($res_invalidlist as $v){
            $invalid_box[] = $v['invalidid'];
        }

        foreach ($all_box as $v){
            if(in_array($v['box_mac'],$invalid_box)){
                continue;
            }
            $fields = 'count(a.id) as num';
            $where['a.box_mac'] = $v['box_mac'];
            $res_info = $m_smallapp_forscreen_record->getForscreenInfo($fields,$where);
            $v['numb'] = $res_info[0]['num'];
            $v['num'] = 0;
            $datalist[] = $v;
        }

        $num_str = "时间段A {$start_time}-{$end_time}互动次数";
        $numb_str = "时间段B {$estart_time}-{$eend_time}互动次数";
        $cell = array(
            array('box_name','版位名称'),
            array('box_mac','版位mac'),
            array('num',$num_str),
            array('numb',$numb_str),
        );
        $filename = 'smallapp_boxdiff';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }

}