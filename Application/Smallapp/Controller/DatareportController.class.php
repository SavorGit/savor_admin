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

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        //网络屏幕数
        $fields = "count(DISTINCT s.box_mac) as wlnum";
        $ret = $m_statistics->getOnlinnum($fields, $where);
        $wlnum = intval($ret[0]['wlnum']);

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        if($area_id){
            $where['s.area_id'] = $area_id;
        }
        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['s.box_type'] = $box_type;
        }else{
            $box_types = array_keys($hotel_box_types);
            $where['s.box_type'] = array('in',$box_types);
        }
        if($is_4g){
            if($is_4g == 1){
                $where['b.is_4g'] = 1;
            }else{
                $where['b.is_4g'] = 0;
            }
        }
        //在线屏幕数
//        $where['heart_log_meal_nums'] = array('GT',12);
        $where['s.heart_log_meal_nums'] = array('GT',5);
        $where['_string'] = 'case s.static_fj when 1 then (120 div s.heart_log_meal_nums)<10  else (180 div s.heart_log_meal_nums)<10 end';
        $fields = 'count(s.box_mac) as zxnum';
        $ret = $m_statistics->getOnlinnum($fields, $where);
        $zxnum = intval($ret[0]['zxnum']);

        $nums = array('wlnum'=>$wlnum,'zxnum'=>$zxnum);
        $rate = $m_statistics->getRate($nums,3);

        $this->assign('start_time',date('Y-m-d',strtotime($start_time)));
        $this->assign('end_time',date('Y-m-d',strtotime($end_time)));
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('area', $area_arr);
        $this->assign('rate',$rate);
        $this->assign('nums',$nums);
        $this->display();
    }

    public function interactnum(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $small_app_id = I('small_app_id',0,'intval');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');

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

        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['box.box_type'] = $box_type;
        }else{
            $box_types = array_keys($hotel_box_types);
            $where['box.box_type'] = array('in',$box_types);
        }

        if($is_4g){
            if($is_4g == 1){
                $where['box.is_4g'] = 1;
            }else{
                $where['box.is_4g'] = 0;
            }
        }
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $fields = 'count(a.id) as hdnum,count(DISTINCT(hotel.id)) as hotelnum,count(DISTINCT(a.box_mac)) as boxnum,count(DISTINCT(a.openid)) as usernum';
        $nums = $m_smallapp_forscreen_record->getInfo($fields,$where);
        $all_smallapps = C('all_smallapps');
        unset($all_smallapps[3]);
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

    public function hotel(){
        $qrcode_types = array(0=>'所有',1=>'全部打开',2=>'全部关闭',3=>'只开普通',4=>'只开极简');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');
        $qrtype = I('qrtype',0,'intval');

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $where = array('box.flag'=>0,'box.state'=>1);
        if($area_id){
            $where['hotel.area_id'] = $area_id;
        }
        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['box.box_type'] = $box_type;
        }else{
            $box_types = array_keys($hotel_box_types);
            $where['box.box_type'] = array('in',$box_types);
        }
        if($is_4g){
            if($is_4g == 1){
                $where['box.is_4g'] = 1;
            }else{
                $where['box.is_4g'] = 0;
            }
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

        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('area', $area_arr);
        $this->assign('qrcode_types', $qrcode_types);
        $this->assign('qrtype', $qrtype);
        $this->assign('nums',$nums);
        $this->display();
    }

}