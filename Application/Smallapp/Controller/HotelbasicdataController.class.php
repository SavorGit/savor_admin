<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序酒楼数据统计
 *
 */
class HotelbasicdataController extends BaseController {

    public function datalist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $area_id = I('area_id',0,'intval');
        $hotel_box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');
        $keyword = I('keyword','','trim');

        if(empty($start_time)){
            $start_time = date('Y-m-d',strtotime('-1 day'));
        }else{
            $start_time = date('Y-m-d',strtotime($start_time));
        }
        if(empty($end_time)){
            $end_time = $start_time;
        }else{
            $end_time = date('Y-m-d',strtotime($end_time));
        }
        $m_hotelbasicdata = new \Admin\Model\Smallapp\StaticHotelbasicdataModel();
        $where = array('static_date'=>array(array('EGT',$start_time),array('ELT',$end_time)));
        if($area_id)    $where['area_id'] = $area_id;
        if($maintainer_id)    $where['maintainer_id'] = $maintainer_id;
        if($is_4g){
            if($is_4g == 1){
                $where['is_4g'] = 1;
            }else{
                $where['is_4g'] = 0;
            }
        }
        if(!empty($keyword)){
            $where['hotel_name'] = array('like',"%$keyword%");
        }

        $start  = ($page-1) * $size;
        $fields = "area_id,area_name,hotel_id,hotel_name,hotel_box_type,is_4g,maintainer,sum(interact_standard_num+interact_mini_num+interact_game_num) as interact_num,
        sum(scancode_num) as scancode_num,sum(user_num) as user_num,sum(user_lunch_zxhdnum) as user_lunch_zxhdnum,sum(lunch_zxhdnum) as lunch_zxhdnum,
        sum(user_dinner_zxhdnum) as user_dinner_zxhdnum,sum(dinner_zxhdnum) as dinner_zxhdnum,sum(user_lunch_interact_num) as user_lunch_interact_num,
        sum(user_dinner_interact_num) as user_dinner_interact_num";
        $groupby = 'hotel_id';
        $countfields = 'count(DISTINCT(hotel_id)) as tp_count';
        $result = $m_hotelbasicdata->getCustomeList($fields,$where,$groupby,'',$countfields,$start,$size);
        $datalist = array();
        $m_heart_log = new \Admin\Model\HeartAllLogModel();
        $m_forscreen = new \Admin\Model\SmallappForscreenRecordModel();

        $all_hotel_types = C('heart_hotel_box_type');
        $data_start_time = strtotime($start_time);
        $data_end_time = strtotime($end_time);
        foreach ($result['list'] as $k=>$v){
            $hotel_id = $v['hotel_id'];
            $hdate = array(date('Ymd',$data_start_time),date('Ymd',$data_end_time));
            $v['heart_num'] = $m_heart_log->getHotelAllHeart($hdate,$hotel_id);
            $v['avg_speed'] = $m_forscreen->getAvgspeedByHotelId($hotel_id,array($data_start_time,$data_end_time));

            $user_lunch_cvr = $user_dinner_cvr = 0;
            if($v['user_lunch_zxhdnum'] && $v['lunch_zxhdnum']){
                $user_lunch_cvr = sprintf("%.2f",$v['user_lunch_zxhdnum']/$v['lunch_zxhdnum']);
                $user_lunch_cvr = $user_lunch_cvr.'%';
            }
            if($v['user_dinner_zxhdnum'] && $v['dinner_zxhdnum']){
                $user_dinner_cvr = sprintf("%.2f",$v['user_dinner_zxhdnum']/$v['dinner_zxhdnum']);
                $user_dinner_cvr = $user_dinner_cvr.'%';
            }
            $v['user_lunch_cvr'] = $user_lunch_cvr;
            $v['user_dinner_cvr'] = $user_dinner_cvr;
            $lunch_unum = $dinner_unum = 0;
            if($v['user_lunch_interact_num'] && $v['user_lunch_zxhdnum']){
                $lunch_unum = intval($v['user_lunch_interact_num']/$v['user_lunch_zxhdnum']);
            }
            if($v['user_dinner_interact_num'] && $v['user_dinner_zxhdnum']){
                $dinner_unum = intval($v['user_dinner_interact_num']/$v['user_dinner_zxhdnum']);
            }
            $v['lunch_unum'] = $lunch_unum;
            $v['dinner_unum'] = $dinner_unum;
            $scan_hdnum = 0;
            if($v['interact_num'] && $v['scancode_num']){
                $scan_hdnum = intval($v['interact_num']/$v['scancode_num']);
            }
            $v['scan_hdnum'] = $scan_hdnum;
            $v['hote_box_type_str'] = $all_hotel_types[$v['hotel_box_type']];
            if($v['is_4g']==1){
                $v['network'] = '4G';
            }else{
                $v['network'] = 'wifi';
            }
            $datalist[]=$v;
        }
        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $opusers = $m_opuser_role->getOpuser($maintainer_id);
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $this->assign('start_time',date('Y-m-d',strtotime($start_time)));
        $this->assign('end_time',date('Y-m-d',strtotime($end_time)));
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$hotel_box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('area', $area_arr);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('opusers',$opusers);
        $this->display();
    }




}