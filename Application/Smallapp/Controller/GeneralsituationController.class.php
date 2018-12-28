<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序数据统计-概况
 *
 */
class GeneralsituationController extends BaseController {

    public function index(){
        $day = I('get.day',0,'intval');
        $type = I('type',1,'intval');//1转换率,2传播力,3屏幕在线率,4网络质量,5互动饭局数,6在线屏幕数,7互动次数,8酒楼评级
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $days = $m_statistics->getDays($day,$start_date,$end_date);
        if(empty($start_date) || empty($end_date)){
            $day = $day>0?$day:1;
        }
        if($day == 1){
            $yestime = date('Ymd',strtotime('-1 day'));
            $index_dates = array($yestime);
        }else{
            $index_dates = $days;
        }
        //关键指标
        $fjnum = $zxnum = $ktnum = $hdnum = $wlnum = 0;
        foreach ($index_dates as $v){
            $nums = $m_statistics->getRatenum($v,0,0);
            //互动饭局数
            $fjnum += $nums['fjnum'];

            //在线屏幕数
            $zxnum += $nums['zxnum'];

            //可投屏数
            $ktnum += $nums['ktnum'];

            //网络屏幕数
            $wlnum += $nums['wlnum'];

            //互动次数
            $hdnum += $nums['hdnum'];
        }
        $nums = array('fjnum'=>$fjnum,'zxnum'=>$zxnum,'ktnum'=>$ktnum,'wlnum'=>$wlnum,'hdnum'=>$hdnum);
        $conversion = $m_statistics->getRate($nums,1);
        $transmissibility = $m_statistics->getRate($nums,2);
        $screens = $m_statistics->getRate($nums,3);
        $network = $m_statistics->getRate($nums,4);
        $index_rate = array('conversion'=>$conversion.'%','transmissibility'=>$transmissibility,
            'screens'=>$screens.'%','network'=>$network.'%');

        //指标趋势
        $chart_list = array();
        foreach ($days as $k=>$v){
            $charts = $this->ratioChart($type,$v);
            $chart_list['a'][] = $charts['a'];
            $chart_list['b'][] = $charts['b'];
            $chart_list['c'][] = $charts['c'];
        }
        //详细数据
        $detail_list = array();
        $detail_breaknum = 4;
        foreach ($days as $k=>$v){
            $ratenums = $m_statistics->getRatenum($v,0,0);
            $detail = array('fjnum'=>$ratenums['fjnum'],'zxnum'=>$ratenums['zxnum'],'hdnum'=>$ratenums['hdnum']);
            $detail['conversion'] = $m_statistics->getRate($ratenums,1);
            $detail['transmissibility'] = $m_statistics->getRate($ratenums,2);
            $detail['screens'] = $m_statistics->getRate($ratenums,3);
            $detail['network'] = $m_statistics->getRate($ratenums,4);
            $detail_list[$v] = $detail;
            if($k==$detail_breaknum){
                break;
            }
        }
        $all_legend = C('LEGEND_CONFIG');
        $this->assign('legend',$all_legend[$type]);
        $this->assign('chart_a',json_encode($chart_list['a']));
        $this->assign('chart_b',json_encode($chart_list['b']));
        $this->assign('chart_c',json_encode($chart_list['c']));
        $this->assign('detail_list',$detail_list);
        $this->assign('alldays',json_encode($days));
        $this->assign('day',$day);
        $this->assign('type',$type);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('index_rate',$index_rate);
        $this->display();
    }

    /*
     * 比率图表
     * type 1转换率,2传播力,3屏幕在线率,4网络质量,5互动饭局数,6在线屏幕数,7互动次数,8酒楼评级
     */
    public function ratioChart($type,$date){
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        switch ($type){
            case 1:
            case 2:
            case 3:
            case 4:
                $nums = $m_statistics->getRatenum($date,1,$type);
                $b = $m_statistics->getRate($nums,$type)/100;

                $nums = $m_statistics->getRatenum($date,2,$type);
                $c = $m_statistics->getRate($nums,$type)/100;

                $a = ($b+$c)/2;
                break;
            case 5:
                $nums = $m_statistics->getRatenum($date,1,$type);
                $b = $nums['fjnum'];

                $nums = $m_statistics->getRatenum($date,2,$type);
                $c = $nums['fjnum'];

                $a = ($b+$c)/2;
                break;
            case 6:
                $nums = $m_statistics->getRatenum($date,1,$type);
                $b = $nums['zxnum'];

                $nums = $m_statistics->getRatenum($date,2,$type);
                $c = $nums['zxnum'];

                $a = ($b+$c)/2;
                break;
            case 7:
                $nums = $m_statistics->getRatenum($date,1,$type);
                $b = $nums['hdnum'];

                $nums = $m_statistics->getRatenum($date,2,$type);
                $c = $nums['hdnum'];

                $a = ($b+$c)/2;
                break;
            case 8:
                /*
                $hotellevel_c = A('Hotellevel');
                $nums = $hotellevel_c->getHotellevel($date);
                $a = $nums['a'];
                $b = $nums['b'];
                $c = $nums['c'];
                */
                $fields = 'hotel_id,level';
                $where = array('static_date'=>$date);
                $order = 'id desc';
                $m_static_hotel = new \Admin\Model\Smallapp\StaticHotelgradeModel();
                $res_hotel = $m_static_hotel->getListnums($fields,$where,$order);
                $a = $res_hotel['a'];
                $b = $res_hotel['b'];
                $c = $res_hotel['c'];
                break;
        }
        $chart = array('a'=>$a,'b'=>$b,'c'=>$c);
        return $chart;
    }

}