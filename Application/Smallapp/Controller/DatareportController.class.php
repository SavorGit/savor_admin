<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

/**
 * @desc 数据报表
 *
 */
class DatareportController extends BaseController {

    public function onlinerate(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');

        if(empty($start_time)){
            $start_time = date('Ymd');
        }else{
            $start_time = date('Ymd',strtotime($start_time));
        }
        if(empty($end_time)){
            $end_time = $start_time;
        }else{
            $end_time = date('Ymd',strtotime($end_time));
        }
        $where = array();
        $where['s.static_date'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $day = (strtotime($end_time) - strtotime($start_time))/86400;

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        if($area_id){
            $where['s.area_id'] = $area_id;
        }
        if($maintainer_id){
            $where['s.maintainer_id'] = $maintainer_id;
        }
        $where['b.state'] = 1;
        $where['b.flag'] = 0;
        if($is_4g){
            if($is_4g == 1){
                $where['b.is_4g'] = 1;
            }else{
                $where['b.is_4g'] = 0;
            }
        }
        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['s.hotel_box_type'] = $box_type;
            $where_wl = $where;
        }else{
            $where_wl = $where;
            $box_types = array_keys($hotel_box_types);
            $where['s.hotel_box_type'] = array('in',$box_types);
        }

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        //网络屏幕数
        $fields = "count(DISTINCT s.box_mac) as wlnum";
        $ret = $m_statistics->getOnlinnum($fields, $where_wl);
        $wlnum = intval($ret[0]['wlnum']);
        if($day){
            $wlnum = ($day+1)*$wlnum;
        }

        //在线屏幕数
//        $where['heart_log_meal_nums'] = array('GT',12);
        $where['s.heart_log_meal_nums'] = array('GT',5);
        $where['_string'] = 'case s.static_fj when 1 then (120 div s.heart_log_meal_nums)<10  else (180 div s.heart_log_meal_nums)<10 end';
        $fields = 'count(s.box_mac) as zxnum';
        $where['s.static_fj'] = 1;//1:午饭2:晚饭
        $ret = $m_statistics->getOnlinnum($fields, $where);
        $zxnum = intval($ret[0]['zxnum']);
        $nums = array('wlnum'=>$wlnum,'zxnum'=>$zxnum);
        $rate = $m_statistics->getRate($nums,3);
        $online = array('wlnum'=>$wlnum,'zxnum'=>$zxnum,'rate'=>$rate);
        $data_list = array('lunch'=>$online);

        $where['s.static_fj'] = 2;//1:午饭2:晚饭
        $ret = $m_statistics->getOnlinnum($fields, $where);
        $zxnum = intval($ret[0]['zxnum']);
        $nums = array('wlnum'=>$wlnum,'zxnum'=>$zxnum);
        $rate = $m_statistics->getRate($nums,3);
        $online = array('wlnum'=>$wlnum,'zxnum'=>$zxnum,'rate'=>$rate);
        $data_list['dinner'] = $online;

        $opusers = $this->getOpuser($maintainer_id);
        $this->assign('start_time',date('Y-m-d',strtotime($start_time)));
        $this->assign('end_time',date('Y-m-d',strtotime($end_time)));
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('area', $area_arr);
        $this->assign('opusers', $opusers);
        $this->assign('data_list',$data_list);
        $this->display();
    }

    public function interactnum(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $small_app_id = I('small_app_id',0,'intval');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');

        $where = array('a.is_valid'=>1);
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

        if($small_app_id){
            if($small_app_id == 2){
                $where['a.small_app_id'] = array('in',array(2,3));
            }else{
                $where['a.small_app_id'] = $small_app_id;
            }
        }
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        if($area_id){
            $where['area.id'] = $area_id;
        }
        $where['box.flag'] = 0;
        $where['box.state'] = 1;
        $where['a.mobile_brand'] = array('neq','devtools');

        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['hotel.hotel_box_type'] = $box_type;
        }else{
            $box_types = array_keys($hotel_box_types);
            $where['hotel.hotel_box_type'] = array('in',$box_types);
        }

        if($is_4g){
            if($is_4g == 1){
                $where['box.is_4g'] = 1;
            }else{
                $where['box.is_4g'] = 0;
            }
        }
        if($maintainer_id){
            $where['hotelext.maintainer_id'] = $maintainer_id;
        }
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $fields = 'count(a.id) as hdnum,count(DISTINCT(hotel.id)) as hotelnum,count(DISTINCT(a.box_mac)) as boxnum,count(DISTINCT(a.openid)) as usernum';
        $nums = $m_smallapp_forscreen_record->getInfo($fields,$where);
        $nums['total_boxnums'] = 0;
        $m_statis = new \Admin\Model\Smallapp\StatisticsModel();
        if($end_time>=$start_time){
            unset($where['a.create_time']);
            $where['_string'] = "DATE(a.create_time)>='$start_time' AND DATE(a.create_time)<='$end_time'";
            $fields = "DATE(a.create_time) as screen_createtime,count(DISTINCT (a.box_mac)) as boxnum";
            $group = 'screen_createtime';
            $res_nums = $m_smallapp_forscreen_record->getInfo($fields,$where,$group,0);
            $total_boxnums = 0;
            foreach ($res_nums as $v){
                $total_boxnums +=$v['boxnum'];
            }
            $nums['total_boxnums'] = $total_boxnums;
        }

        unset($where['a.is_valid'],$where['a.create_time'],$where['a.small_app_id'],$where['a.mobile_brand'],$where['hotelext.maintainer_id']);
        if($maintainer_id){
            $where['a.maintainer_id'] = $maintainer_id;
        }
        $where['a.static_date'] = array(array('EGT',date('Ymd',strtotime($start_time))),array('ELT',date('Ymd',strtotime($end_time))));
        $where['a.all_interact_nums'] = array('GT',0);
        $fields = "count(a.box_mac) as fjnum";
        $ret = $m_statis->getFeast($fields, $where);
        if($ret){
            $nums['fjnum'] = $ret['fjnum'];
        }else{
            $nums['fjnum'] = 0;
        }
        $all_smallapps = C('all_smallapps');
        unset($all_smallapps[3]);
        $opusers = $this->getOpuser($maintainer_id);
        $this->assign('opusers', $opusers);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('small_app_id',$small_app_id);
        $this->assign('area', $area_arr);
        $this->assign('all_smallapps', $all_smallapps);
        $this->assign('nums',$nums);
        $this->display('interactnum');
    }

    public function interactdiff(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
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
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
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
        $start  = ($page-1) * $size;
        $result = $m_smallapp_forscreen_record->getCustomeList($fields,$where,$groupby='hotel.id',$order='num desc',$countfields,$start,$size);
        $datalist = $result['list'];

        $res_ohotels = array();
        if($result['total']>0 && $page*$size>=$result['total']){
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
        foreach ($datalist as $k=>$v){
            $fields = 'count(a.id) as num';
            $where['hotel.id'] = $v['hotel_id'];
            $res_info = $m_smallapp_forscreen_record->getInfo($fields,$where);
            $datalist[$k]['numb'] = $res_info['num'];

            unset($where['a.create_time']);
            $where['_string'] = "DATE(a.create_time)>='$start_time' AND DATE(a.create_time)<='$end_time'";
            $fields = "DATE(a.create_time) as screen_createtime,count(DISTINCT (a.box_mac)) as boxnum";
            $group = 'screen_createtime';
            $res_nums = $m_smallapp_forscreen_record->getInfo($fields,$where,$group,0);
            $a_boxnums = 0;
            foreach ($res_nums as $vn){
                $a_boxnums +=$vn['boxnum'];
            }
            $datalist[$k]['a_boxnum'] = $a_boxnums;
            $datalist[$k]['a_coverage'] = sprintf("%0.2f",$datalist[$k]['num']/$a_boxnums);
            $where['_string'] = "DATE(a.create_time)>='$estart_time' AND DATE(a.create_time)<='$eend_time'";
            $fields = "DATE(a.create_time) as screen_createtime,count(DISTINCT (a.box_mac)) as boxnum";
            $group = 'screen_createtime';
            $res_nums = $m_smallapp_forscreen_record->getInfo($fields,$where,$group,0);
            $b_boxnums = 0;
            foreach ($res_nums as $vn){
                $b_boxnums +=$vn['boxnum'];
            }
            $datalist[$k]['b_boxnum'] = $b_boxnums;
            $datalist[$k]['b_coverage'] = sprintf("%0.2f",$datalist[$k]['numb']/$b_boxnums);
        }

        if(!empty($res_ohotels)){
            foreach ($res_ohotels['list'] as $v){
                $fields = 'count(a.id) as num';
                $where['hotel.id'] = $v['hotel_id'];
                $res_info = $m_smallapp_forscreen_record->getInfo($fields,$where);
                $v['numb'] = $res_info['num'];
                $v['num'] = 0;
                $v['a_boxnum'] = 0;
                $v['a_coverage'] = 0.00;

                unset($where['a.create_time']);
                $where['_string'] = "DATE(a.create_time)>='$estart_time' AND DATE(a.create_time)<='$eend_time'";
                $fields = "DATE(a.create_time) as screen_createtime,count(DISTINCT (a.box_mac)) as boxnum";
                $group = 'screen_createtime';
                $res_nums = $m_smallapp_forscreen_record->getInfo($fields,$where,$group,0);
                $b_boxnums = 0;
                foreach ($res_nums as $vn){
                    $b_boxnums +=$vn['boxnum'];
                }
                $v['b_boxnum'] = $b_boxnums;
                $v['b_coverage'] = sprintf("%0.2f",$v['numb']/$b_boxnums);
                $datalist[] = $v;
            }
        }

        $all_smallapps = C('all_smallapps');
        unset($all_smallapps[3]);
        $opusers = $this->getOpuser($maintainer_id);
        $this->assign('opusers', $opusers);
        $this->assign('maintainer_id', $maintainer_id);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('estart_time',$estart_time);
        $this->assign('eend_time',$eend_time);
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('small_app_id',$small_app_id);
        $this->assign('area', $area_arr);
        $this->assign('all_smallapps', $all_smallapps);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('interactdiff');
    }


    public function boxdiff(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',200);//显示每页记录数
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

        $where = array('hotel.id'=>$hotel_id);
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
        $where['box.flag'] = 0;
        $where['box.state'] = 1;
        $where['a.mobile_brand'] = array('neq','devtools');
        if($small_app_id){
            if($small_app_id == 2){
                $where['a.small_app_id'] = array('in',array(2,3));
            }else{
                $where['a.small_app_id'] = $small_app_id;
            }
        }

        if($area_id){
            $where['area.id'] = $area_id;
        }
        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['hotel.hotel_box_type'] = $box_type;
        }else{
            $box_types = array_keys($hotel_box_types);
            $where['hotel.hotel_box_type'] = array('in',$box_types);
        }
        $box_condition = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
        if($is_4g){
            if($is_4g == 1){
                $where['box.is_4g'] = 1;
                $box_condition['box.is_4g'] = 1;
            }else{
                $where['box.is_4g'] = 0;
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
        $fields = 'hotel.id as hotel_id,hotel.name as hotel_name,a.box_mac,box.name as box_name,count(a.id) as num';
        $countfields = 'COUNT(DISTINCT(box_mac)) AS tp_count';
        $start  = ($page-1) * $size;
        $result = $m_smallapp_forscreen_record->getCustomeList($fields,$where,$groupby='a.box_mac',$order='num desc',$countfields,$start,$size);
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
            $res_info = $m_smallapp_forscreen_record->getInfo($fields,$where);
            $datalist[$k]['numb'] = $res_info['num'];
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
            $res_info = $m_smallapp_forscreen_record->getInfo($fields,$where);
            $v['numb'] = $res_info['num'];
            $v['num'] = 0;
            $datalist[] = $v;
        }
        $this->assign('hotel_id',$hotel_id);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('estart_time',$estart_time);
        $this->assign('eend_time',$eend_time);
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('small_app_id',$small_app_id);
        $this->assign('datalist', $datalist);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('boxdiff');
    }

    public function hotel(){
        $qrcode_types = array(0=>'所有',1=>'全部打开',2=>'全部关闭',3=>'只开普通',4=>'只开极简');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');
        $qrtype = I('qrtype',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $where = array('box.flag'=>0,'box.state'=>1);
        if($area_id){
            $where['hotel.area_id'] = $area_id;
        }
        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['hotel.hotel_box_type'] = $box_type;
        }else{
            $box_types = array_keys($hotel_box_types);
            $where['hotel.hotel_box_type'] = array('in',$box_types);
        }
        if($is_4g){
            if($is_4g == 1){
                $where['box.is_4g'] = 1;
            }else{
                $where['box.is_4g'] = 0;
            }
        }
        if($maintainer_id){
            $where['hotelext.maintainer_id'] = $maintainer_id;
        }
        if($qrtype){
            switch ($qrtype){
                case 1:
                    $where['box.is_sapp_forscreen'] = 1;
                    $where['box.is_open_simple'] = 1;
                    break;
                case 2:
                    $where['box.is_sapp_forscreen'] = 0;
                    $where['box.is_open_simple'] = 0;
                    break;
                case 3:
                    $where['box.is_sapp_forscreen'] = 1;
                    $where['box.is_open_simple'] = 0;
                    break;
                case 4:
                    $where['box.is_sapp_forscreen'] = 0;
                    $where['box.is_open_simple'] = 1;
                    break;
            }
        }

        $fields = 'count(DISTINCT(hotel.id)) as hotelnum,count(DISTINCT(box.id)) as boxnum';
        $m_box = new \Admin\Model\BoxModel();
        $nums = $m_box->getInfoByCondition($fields,$where);
        $opusers = $this->getOpuser($maintainer_id);
        $this->assign('opusers', $opusers);
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('area', $area_arr);
        $this->assign('qrcode_types', $qrcode_types);
        $this->assign('qrtype', $qrtype);
        $this->assign('nums',$nums);
        $this->display();
    }

    public function getOpuser($op_uid=0){
        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $fields = 'a.user_id uid,user.remark ';
        $where = array('state'=>1,'role_id'=>1);
        $res_users = $m_opuser_role->getAllRole($fields,$where,'' );

        $opusers = array();
        foreach($res_users as $v){
            $uid = $v['uid'];
            $remark = $v['remark'];
            if($uid==$op_uid){
                $select = 'selected';
            }else{
                $select = '';
            }
            $firstCharter = getFirstCharter(cut_str($remark, 1));
            $opusers[$firstCharter][] = array('uid'=>$uid,'remark'=>$remark,'select'=>$select);
        }
        ksort($opusers);
        return $opusers;
    }

}