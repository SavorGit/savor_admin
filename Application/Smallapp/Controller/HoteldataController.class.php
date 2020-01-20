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
        $m_hotel = new \Admin\Model\HotelModel();
        $field = 'id as hotel_id,name as hotel_name';
        $where = array('state'=>1,'flag'=>0);
        $hotels = $m_hotel->getWhereorderData($where, $field,'id desc');
        $hotel_name = '';
        foreach ($hotels as $k=>$v){
            if($v['hotel_id']==$hotel_id){
                $v['is_select'] = 'selected';
                $hotel_name = $v['hotel_name'];
            }else{
                $v['is_select'] = '';
            }
            $hotels[$k] = $v;
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
        /*
        if($hotel_id){
            $hotellevel_c = A('Hotellevel');
            $detail_breaknum = 4;
            foreach ($days as $k=>$v){
                $ratenums = $m_statistics->getRatenum($v,0,0,$hotel_id);
                $detail = array('fjnum'=>$ratenums['fjnum'],'zxnum'=>$ratenums['zxnum'],'hdnum'=>$ratenums['hdnum']);
                $detail['conversion'] = $m_statistics->getRate($ratenums,1);
                $detail['transmissibility'] = $m_statistics->getRate($ratenums,2);
                $detail['screens'] = $m_statistics->getRate($ratenums,3);
                $detail['network'] = $m_statistics->getRate($ratenums,4);
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
        */
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
    public function ratioChart($hotel_id,$type,$date){
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        switch ($type){
            case 1:
            case 3:
            case 4:
                $nums = $m_statistics->getRatenum($date,0,$type,$hotel_id);
                $chart = $m_statistics->getRate($nums,$type)/100;
                break;
            case 2:
                $nums = $m_statistics->getRatenum($date,0,$type,$hotel_id);
                $chart = $m_statistics->getRate($nums,$type);
                break;
            case 5:
                $nums = $m_statistics->getRatenum($date,0,$type,$hotel_id);
                $chart = $nums['fjnum'];
                break;
            case 6:
                $nums = $m_statistics->getRatenum($date,0,$type,$hotel_id);
                $chart = $nums['zxnum'];
                break;
            case 7:
                $nums = $m_statistics->getRatenum($date,0,$type,$hotel_id);
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

}