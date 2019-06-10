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
        $start_time = $days[0];
        $end_time = end($days);
        //关键指标
        $fjnum = $zxnum = $ktnum = $hdnum = $wlnum = $mobilenum = 0;
        if($day==1){
            $nums = $m_statistics->getRatenum($yestime,0,0);
            $fjnum = $nums['fjnum'];
            $zxnum = $nums['zxnum'];
            $ktnum = $nums['ktnum'];
            $wlnum = $nums['wlnum'];
            $hdnum = $nums['hdnum'];
            $mobilenum = $nums['mobilenum'];
        }else{
            $between_time = array($start_time,$end_time);
            $nums = $m_statistics->getRatenum($between_time,0,0);
            foreach ($nums['fjnum'] as $v){
                $fjnum += $v['fjnum'];
            }
            foreach ($nums['zxnum'] as $v){
                $zxnum += $v['zxnum'];
            }
            foreach ($nums['ktnum'] as $v){
                $ktnum += $v['ktnum'];
            }
            foreach ($nums['wlnum'] as $v){
                $wlnum += $v['wlnum'];
            }
            foreach ($nums['hdnum'] as $v){
                $hdnum += $v['hdnum'];
            }
            foreach ($nums['mobilenum'] as $v){
                $mobilenum += $v['mobilenum'];
            }
        }

        $nums = array('fjnum'=>$fjnum,'zxnum'=>$zxnum,'ktnum'=>$ktnum,'wlnum'=>$wlnum,'hdnum'=>$hdnum,'mobilenum'=>$mobilenum);

        $conversion = $m_statistics->getRate($nums,1);
        $transmissibility = $m_statistics->getRate($nums,2);
        $screens = $m_statistics->getRate($nums,3);
        $network = $m_statistics->getRate($nums,4);
        $index_rate = array('conversion'=>$conversion.'%','transmissibility'=>$transmissibility,'screens'=>$screens.'%','network'=>$network.'%');

        //指标趋势
        $chart_list = array();
        if($type!=8){
            $between_time = array($start_time,$end_time);
            $charts = $this->ratioChart($type,$between_time);
            $chart_list['a'] = array_values($charts['a']);
            $chart_list['b'] = array_values($charts['b']);
            $chart_list['c'] = array_values($charts['c']);
        }else{
            foreach ($days as $k=>$v){
                $charts = $this->ratioChart($type,$v);
                $chart_list['a'][] = $charts['a'];
                $chart_list['b'][] = $charts['b'];
                $chart_list['c'][] = $charts['c'];
            }
        }

        //详细数据
        $detail_list = array();

        $chart_list['a'] = isset($chart_list['a'])?$chart_list['a']:array();
        $chart_list['b'] = isset($chart_list['b'])?$chart_list['b']:array();
        $chart_list['c'] = isset($chart_list['c'])?$chart_list['c']:array();

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

    public function ratioChartByDates($type,$date,$is_percent=0,$is_rate=0){
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $res_bnums = $m_statistics->getRatenum($date, 1, $type);
        $b_nums = array();
        foreach ($res_bnums as $v){
            foreach ($v as $kk => $vv) {
                $tmp_static_date = $vv['static_date'];
                unset($vv['static_date']);
                $b_nums[$tmp_static_date] = isset($b_nums[$tmp_static_date])?array_merge($b_nums[$tmp_static_date], $vv):$vv;
            }
        }
        $b = array();
        foreach ($b_nums as $k=>$v){
            if($is_rate){
                if($is_percent){
                    $b_num = $m_statistics->getRate($v,$type)/100;
                }else{
                    $b_num = $m_statistics->getRate($v,$type);
                }
            }else{
                $tmp_bnuminfo = array_values($v);
                $b_num = $tmp_bnuminfo[0];
            }
            $b[$k] = $b_num;
        }

        $res_cnums = $m_statistics->getRatenum($date,2,$type);
        $c_nums = array();
        foreach ($res_cnums as $v){
            foreach ($v as $kk => $vv) {
                $tmp_static_date = $vv['static_date'];
                unset($vv['static_date']);
                $c_nums[$tmp_static_date] = isset($c_nums[$tmp_static_date])?array_merge($c_nums[$tmp_static_date], $vv):$vv;
            }
        }
        $c = array();
        $a = array();
        foreach ($c_nums as $k=>$v){
            if($is_rate){
                if($is_percent){
                    $c_num = $m_statistics->getRate($v,$type)/100;
                }else{
                    $c_num = $m_statistics->getRate($v,$type);
                }
            }else{
                $tmp_cnuminfo = array_values($v);
                $c_num = $tmp_cnuminfo[0];
            }

            $c[$k] = $c_num;
            $a[$k] = ($b[$k]+$c[$k])/2;
        }
        return array('a'=>$a,'b'=>$b,'c'=>$c);
    }

    /*
     * 比率图表
     * type 1转换率,2传播力,3屏幕在线率,4网络质量,5互动饭局数,6在线屏幕数,7互动次数,8酒楼评级
     */
    public function ratioChart($type,$date){
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        switch ($type){
            case 1:
            case 3:
            case 4:
                if(is_array($date)) {
                    $res_nums = $this->ratioChartByDates($type,$date,1,1);
                    $b = $res_nums['b'];
                    $c = $res_nums['c'];
                    $a = $res_nums['a'];
                }else{
                    $nums = $m_statistics->getRatenum($date,1,$type);
                    $b = $m_statistics->getRate($nums,$type)/100;

                    $nums = $m_statistics->getRatenum($date,2,$type);
                    $c = $m_statistics->getRate($nums,$type)/100;

                    $a = ($b+$c)/2;
                }

                break;
            case 2:
                if(is_array($date)){
                    $res_nums = $this->ratioChartByDates($type,$date,0,1);
                    $b = $res_nums['b'];
                    $c = $res_nums['c'];
                    $a = $res_nums['a'];
                }else{
                    $nums = $m_statistics->getRatenum($date,1,$type);
                    $b = $m_statistics->getRate($nums,$type);

                    $nums = $m_statistics->getRatenum($date,2,$type);
                    $c = $m_statistics->getRate($nums,$type);

                    $a = ($b+$c)/2;
                }

                break;
            case 5:
                if(is_array($date)){
                    $res_nums = $this->ratioChartByDates($type,$date,0,0);
                    $b = $res_nums['b'];
                    $c = $res_nums['c'];
                    $a = $res_nums['a'];
                }else{
                    $nums = $m_statistics->getRatenum($date,1,$type);
                    $b = $nums['fjnum'];

                    $nums = $m_statistics->getRatenum($date,2,$type);
                    $c = $nums['fjnum'];

                    $a = ($b+$c)/2;
                }

                break;
            case 6:
                if(is_array($date)){
                    $res_nums = $this->ratioChartByDates($type,$date,0,0);
                    $b = $res_nums['b'];
                    $c = $res_nums['c'];
                    $a = $res_nums['a'];
                }else{
                    $nums = $m_statistics->getRatenum($date,1,$type);
                    $b = $nums['zxnum'];

                    $nums = $m_statistics->getRatenum($date,2,$type);
                    $c = $nums['zxnum'];

                    $a = ($b+$c)/2;
                }
                break;
            case 7:
                if(is_array($date)){
                    $res_nums = $this->ratioChartByDates($type,$date,0,0);
                    $b = $res_nums['b'];
                    $c = $res_nums['c'];
                    $a = $res_nums['a'];
                }else{
                    $nums = $m_statistics->getRatenum($date,1,$type);
                    $b = $nums['hdnum'];

                    $nums = $m_statistics->getRatenum($date,2,$type);
                    $c = $nums['hdnum'];

                    $a = ($b+$c)/2;
                }
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