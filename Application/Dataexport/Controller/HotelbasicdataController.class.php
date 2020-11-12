<?php
namespace Dataexport\Controller;

class HotelbasicdataController extends BaseController{

    public function basicdata(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $area_id = I('area_id',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');

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

        $fields = "area_id,area_name,hotel_id,hotel_name,hotel_box_type,is_4g,maintainer,sum(interact_standard_num+interact_mini_num+interact_game_num) as interact_num,
        sum(heart_num) as heart_num,avg(NULLIF(avg_down_speed,0)) as avg_speed,sum(scancode_num) as scancode_num,sum(user_num) as user_num,sum(user_lunch_zxhdnum) as user_lunch_zxhdnum,
        sum(lunch_zxhdnum) as lunch_zxhdnum,sum(user_dinner_zxhdnum) as user_dinner_zxhdnum,sum(dinner_zxhdnum) as dinner_zxhdnum,sum(user_lunch_interact_num) as user_lunch_interact_num,
        sum(user_dinner_interact_num) as user_dinner_interact_num,sum(interact_sale_num-interact_sale_signnum) as interact_sale_nosignnum";
        $groupby = 'hotel_id';
        $countfields = 'count(DISTINCT(hotel_id)) as tp_count';
        $result = $m_hotelbasicdata->getCustomeList($fields,$where,$groupby,'',$countfields,0,10000);
        $datalist = array();

        $all_hotel_types = C('heart_hotel_box_type');
        $m_box = new \Admin\Model\BoxModel();
        foreach ($result['list'] as $k=>$v){
            if($v['avg_speed']>0){
                $v['avg_speed'] = intval($v['avg_speed']).'kb/s';
            }else{
                $v['avg_speed'] = '';
            }
            $box_where = array('hotel.id'=>$v['hotel_id']);
            $box_where['box.state'] = array('in',array(1,2));
            $box_where['box.flag'] = 0;
            $res_box_num = $m_box->countNums($box_where);
            $v['box_num'] = $res_box_num;

            $user_lunch_cvr = $user_dinner_cvr = 0;
            if($v['user_lunch_zxhdnum'] && $v['lunch_zxhdnum']){
                $user_lunch_cvr = sprintf("%.2f",$v['user_lunch_zxhdnum']/$v['lunch_zxhdnum']);
                $user_lunch_cvr = $user_lunch_cvr*100 .'%';
            }
            if($v['user_dinner_zxhdnum'] && $v['dinner_zxhdnum']){
                $user_dinner_cvr = sprintf("%.2f",$v['user_dinner_zxhdnum']/$v['dinner_zxhdnum']);
                $user_dinner_cvr = $user_dinner_cvr*100 .'%';
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
            $v['hotel_box_type_str'] = $all_hotel_types[$v['hotel_box_type']];
            if($v['is_4g']==1){
                $v['network'] = '4G';
            }else{
                $v['network'] = 'wifi';
            }
            $datalist[]=$v;
        }
        $cell = array(
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('area_name','城市'),
            array('heart_num','心跳次数'),
            array('avg_speed','平均网速'),
            array('interact_num','互动次数'),
            array('scancode_num','扫码数'),
            array('user_num','独立用户数'),
            array('user_lunch_zxhdnum','午饭互动过的可互动版位'),
            array('lunch_zxhdnum','午饭在线可互动屏'),
            array('user_lunch_cvr','午饭饭局转化率'),
            array('user_dinner_zxhdnum','晚饭互动过的可互动版位'),
            array('dinner_zxhdnum','晚饭在线可互动屏'),
            array('user_dinner_cvr','晚饭饭局转化率'),
            array('user_lunch_interact_num','午饭互动次数'),
            array('lunch_unum','午饭平均饭局互动数'),
            array('user_dinner_interact_num','晚饭互动次数'),
            array('dinner_unum','晚饭平均饭局互动数'),
            array('scan_hdnum','单次扫码互动数'),
            array('box_num','版位数'),
            array('interact_sale_nosignnum','销售端非签到互动数'),
            array('hotel_box_type_str','酒楼设备类型'),
            array('network','上网方式(wifi/4g)'),
            array('maintainer','维护人'),
        );
        $filename = '酒楼数据统计';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
}