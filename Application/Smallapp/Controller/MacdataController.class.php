<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序数据统计-版位数据
 *
 */
class MacdataController extends BaseController {

    public function index(){
        $box_mac = I('box_mac','','trim');
        $day = I('day',7,'intval');
        $type = I('type',7,'intval');//2传播力,4网络质量,5互动饭局数,7互动次数,8评分,9心跳
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $days = $m_statistics->getDays($day,$start_date,$end_date);
        $box_info = $m_statistics->getOne();

        $where = array('box_mac'=>$box_mac);
        $fields = "hotel_name,room_name";
        $ret = $m_statistics->getOne($fields, $where,'id desc');
        $box_name = $ret['hotel_name'].'-'.$ret['room_name'].'-'.$box_mac;

        $chart_list = array();
        if($box_mac){
            foreach ($days as $k=>$v){
                $charts = $this->ratioChart($box_mac,$type,$v);
                $chart_list[] = $charts;
            }
        }
        //详细数据
        $detail_list = array();
        /*
        if($box_mac){
            $hotellevel_c = A('Hotellevel');
            $detail_breaknum = 4;
            foreach ($days as $k=>$v){
                $ratenums = $m_statistics->getRatenum($v,0,0,0,$box_mac);
                $detail = array('fjnum'=>$ratenums['fjnum'],'hdnum'=>$ratenums['hdnum']);
                $detail['transmissibility'] = $m_statistics->getRate($ratenums,2);
                $detail['screens'] = $m_statistics->getRate($ratenums,3);
                $detail['network'] = $m_statistics->getRate($ratenums,4);
                $nums = $hotellevel_c->getMacscore($v,$box_mac);
                $detail['score'] = $nums['score'];
                $detail_list[$v] = $detail;
                if($k==$detail_breaknum){
                    break;
                }
            }
        }
        */

        $this->assign('chart',json_encode($chart_list));
        $this->assign('detail_list',$detail_list);
        $this->assign('alldays',json_encode($days));
        $this->assign('day',$day);
        $this->assign('box_mac',$box_mac);
        $this->assign('box_name',$box_name);
        $this->assign('type',$type);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->display();
    }

    /*
     * 比率图表
     * type 1转换率,2传播力,3屏幕在线率,4网络质量,5互动饭局数,6在线屏幕数,7互动次数,8评分,9心跳
     */
    public function ratioChart($box_mac,$type,$date){
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        switch ($type){
            case 1:
            case 3:
            case 4:
                $nums = $m_statistics->getRatenum($date,0,$type,0,$box_mac);
                $chart = $m_statistics->getRate($nums,$type)/100;
                break;
            case 2:
                $nums = $m_statistics->getRatenum($date,0,$type,0,$box_mac);
                $chart = $m_statistics->getRate($nums,$type);
                break;
            case 5:
                $nums = $m_statistics->getRatenum($date,0,$type,0,$box_mac);
                $chart = $nums['fjnum'];
                break;
            case 6:
                $nums = $m_statistics->getRatenum($date,0,$type,0,$box_mac);
                $chart = $nums['zxnum'];
                break;
            case 7:
                $nums = $m_statistics->getRatenum($date,0,$type,0,$box_mac);
                $chart = $nums['hdnum'];
                break;
            case 8:
                $hotellevel_c = A('Hotellevel');
                $nums = $hotellevel_c->getMacscore($date,$box_mac);
                $chart = $nums['score'];
                break;
            case 9:
                $nums = $m_statistics->getRatenum($date,0,$type,0,$box_mac);
                $chart = $nums['xtnum'];
                break;
        }
        return $chart;
    }
}