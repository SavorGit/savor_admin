<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序数据统计-酒楼数据
 *
 */
class HoteldataController extends BaseController {

    public function index(){
        $hotel_id = I('hotel_id',0,'intval');
        $day = I('day',7,'intval');
        $type = I('type',1,'intval');//1转换率,2传播力,3屏幕在线率,4网络质量,5互动饭局数,6在线屏幕数,7互动次数,8评分
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $days = $m_statistics->getDays($day,$start_date,$end_date);
        $hotels = $m_statistics->getHotels();
        $hotel_name = '';
        foreach ($hotels as $k=>$v){
            if($v['hotel_id']==$hotel_id){
                $v['is_select'] = 'selected';
                $hotel_name = $v['hotel_name'];
            }else{
                $v['is_select'] = '';
            }
        }
        $chart_list = array();
        if($hotel_id){
            foreach ($days as $k=>$v){
                $charts = $this->ratioChart($hotel_id,$type,$v,$m_statistics);
                $chart_list[] = $charts;
            }
        }

        //详细数据
        $detail_list = array();
        if($hotel_id){
            $hotellevel_c = A('Hotellevel');
            $detail_breaknum = 4;
            foreach ($days as $k=>$v){
                $ratenums = $this->getRatenum($v,0,0,$hotel_id,$m_statistics);
                $detail = array('fjnum'=>$ratenums['fjnum'],'zxnum'=>$ratenums['zxnum'],'hdnum'=>$ratenums['hdnum']);
                $detail['conversion'] = $this->getRate($ratenums,1);
                $detail['transmissibility'] = $this->getRate($ratenums,2);
                $detail['screens'] = $this->getRate($ratenums,3);
                $detail['network'] = $this->getRate($ratenums,4);
                $hotel_info = $hotellevel_c->getHotellevel($v,$hotel_id,1);
                $detail['level'] = $hotel_info['level'];
                $detail['score'] = $hotel_info['score'];
                $detail['net_score'] = $hotel_info['net_score'];
                $detail['wake_score'] = $hotel_info['wake_score'];
                $detail['hd_score'] = $hotel_info['hd_score'];
                $detail_list[$v] = $detail;
                if($k==$detail_breaknum){
                    break;
                }
            }
        }

        $this->assign('chart',json_encode($chart_list));
        $this->assign('detail_list',$detail_list);
        $this->assign('hotels',$hotels);
        $this->assign('alldays',json_encode($days));
        $this->assign('day',$day);
        $this->assign('hotel_id',$hotel_id);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('type',$type);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->display();
    }

    /*
     * 比率图表
     * type 1转换率,2传播力,3屏幕在线率,4网络质量,5互动饭局数,6在线屏幕数,7互动次数,8酒楼评级
     */
    public function ratioChart($hotel_id,$type,$date,$m_statistics=''){
        if(empty($m_statistics)){
            $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        }
        switch ($type){
            case 1:
            case 2:
            case 3:
            case 4:
                $nums = $this->getRatenum($date,0,$type,$hotel_id,$m_statistics);
                $chart = $this->getRate($nums,$type)/100;
                break;
            case 5:
                $nums = $this->getRatenum($date,0,$type,$hotel_id,$m_statistics);
                $chart = $nums['fjnum'];
                break;
            case 6:
                $nums = $this->getRatenum($date,0,$type,$hotel_id,$m_statistics);
                $chart = $nums['zxnum'];
                break;
            case 7:
                $nums = $this->getRatenum($date,0,$type,$hotel_id,$m_statistics);
                $chart = $nums['hdnum'];
                break;
            case 8:
                $hotellevel_c = A('Hotellevel');
                $nums = $hotellevel_c->getHotellevel($date,$hotel_id);
                $chart = $nums['score'];
                break;
        }
        return $chart;
    }

    /*
     * 获取比率
     * type 1转换率 2传播率 3屏幕在线率 4 网络质量
     */
    public function getRate($nums,$type){
        switch ($type){
            case 1:
                $rate = sprintf("%.2f", $nums['fjnum']/$nums['zxnum']) * 100;
                break;
            case 2:
                $rate = 0;
//                $rate = sprintf("%.2f", 互动手机数/$nums['fjnum']) * 100;
                break;
            case 3:
                $rate = sprintf("%.2f", $nums['zxnum']/$nums['wlnum']) * 100;
                break;
            case 4:
                $rate = sprintf("%.2f", $nums['ktnum']/$nums['zxnum']) * 100;
                break;
            default:
                $rate = 0;
        }
        return $rate;
    }

    /* 获取比率对应数
     * type 0所有 1转换率 2传播率 3屏幕在线率 4网络质量 5互动饭局数,6在线屏幕数,7互动次数,8酒楼评级
     */
    public function getRatenum($date,$static_fj=0,$type=0,$hotel_id=0,$m_statistics=''){
        if(empty($m_statistics)){
            $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        }
        $nums = array();
        if(in_array($type,array(0,1,2,3,4,5))){
            //互动饭局数
            $where = array('static_date'=>$date);
            if($hotel_id)   $where['hotel_id'] = $hotel_id;
            if($static_fj)  $where['static_fj'] = $static_fj;
            $where['all_interact_nums'] = array('GT',0);
            $fields = "count(box_mac) as fjnum";
            $ret = $m_statistics->getOne($fields, $where);
            $nums['fjnum'] = $ret['fjnum'];
        }
        if(in_array($type,array(0,1,2,3,4,6))){
            //在线屏幕数
            $where = array('static_date'=>$date);
            if($hotel_id)   $where['hotel_id'] = $hotel_id;
            if($static_fj)  $where['static_fj'] = $static_fj;
            $where['heart_log_meal_nums'] = array('GT',12);
            $where['_string'] = 'case static_fj when 1 then (120 div heart_log_meal_nums)<10  else (180 div heart_log_meal_nums)<10 end';
            $fields = 'count(box_mac) as zxnum';
            $ret = $m_statistics->getOne($fields, $where);
            $nums['zxnum'] = $ret['zxnum'];
        }
        if($type==0 || $type==4){
            //可投屏数
            $where = array('static_date'=>$date);
            if($hotel_id)   $where['hotel_id'] = $hotel_id;
            if($static_fj)  $where['static_fj'] = $static_fj;
            $where['heart_log_meal_nums'] = array('GT',0);
            $where['_string'] = '(avg_down_speed div 1024)>200';
            $fields = 'count(box_mac) as ktnum';
            $ret = $m_statistics->getOne($fields, $where);
            $nums['ktnum'] = $ret['ktnum'];
        }

        if($type==0 || $type==3){
            //网络屏幕数
            $where = array('static_date'=>$date);
            if($hotel_id)   $where['hotel_id'] = $hotel_id;
            if($static_fj){
                $where['static_fj'] = $static_fj;
            }else{
                $where['static_fj'] = array('eq',1);
            }
            $fields = "count(id) as wlnum";
            $ret = $m_statistics->getOne($fields, $where);
            $nums['wlnum'] = $ret['wlnum'];
        }
        if($type==0 || $type==7){
            //互动次数
            $where = array('static_date'=>$date);
            if($hotel_id)   $where['hotel_id'] = $hotel_id;
            if($static_fj)  $where['static_fj'] = $static_fj;
            $fields = 'sum(all_interact_nums) as hdnum';
            $ret = $m_statistics->getOne($fields, $where);
            $nums['hdnum'] = $ret['hdnum'];
        }
        return $nums;
    }


}