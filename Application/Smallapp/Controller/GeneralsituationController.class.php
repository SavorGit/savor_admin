<?php
namespace Smallapp\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序数据统计-概况
 *
 */
class GeneralsituationController extends BaseController {

    public function index(){
        ini_set("memory_limit","1024M");
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
            $yestime = date('Ymd',strtotime('-2 day'));
            $index_dates = array($yestime);
        }else{
            $index_dates = $days;
        }
        //关键指标
        $fjnum = $zxnum = $ktnum = $hdnum = $wlnum = 0;
        foreach ($index_dates as $v){
            $nums = $this->getRatenum($v,0,0,$m_statistics);
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
        $conversion = $this->getRate($nums,1);
        $transmissibility = $this->getRate($nums,2);
        $screens = $this->getRate($nums,3);
        $network = $this->getRate($nums,4);
        $index_rate = array('conversion'=>$conversion.'%','transmissibility'=>$transmissibility,
            'screens'=>$screens.'%','network'=>$network.'%');

        //指标趋势
        $chart_list = array();
        foreach ($days as $k=>$v){
            $charts = $this->ratioChart($type,$v,$m_statistics);
            $chart_list['a'][] = $charts['a'];
            $chart_list['b'][] = $charts['b'];
            $chart_list['c'][] = $charts['c'];
        }


        //详细数据
        $detail_list = array();
        $detail_breaknum = 4;
        foreach ($days as $k=>$v){
            $ratenums = $this->getRatenum($v,0,0,$m_statistics);
            $detail = array('fjnum'=>$ratenums['fjnum'],'zxnum'=>$ratenums['zxnum'],'hdnum'=>$ratenums['hdnum']);
            $detail['conversion'] = $this->getRate($ratenums,1);
            $detail['transmissibility'] = $this->getRate($ratenums,2);
            $detail['screens'] = $this->getRate($ratenums,3);
            $detail['network'] = $this->getRate($ratenums,4);
//            $detail['hotel_level'] = $hotellevel_c->getHotellevel($v);
            $detail_list[$v] = $detail;
            if($k==4){
                break;
            }
        }
        $legend = array('平均转化率','午饭转化率','晚饭转化率');
        if($type==8){
            $legend = array('A级酒楼','B级酒楼','C级酒楼');
        }
        $this->assign('legend',$legend);
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
    public function ratioChart($type,$date,$m_statistics=''){
        if(empty($m_statistics)){
            $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        }
        switch ($type){
            case 1:
            case 2:
            case 3:
            case 4:
                $nums = $this->getRatenum($date,1,$type,$m_statistics);
                $b = $this->getRate($nums,$type)/100;

                $nums = $this->getRatenum($date,2,$type,$m_statistics);
                $c = $this->getRate($nums,$type)/100;

                $a = ($b+$c)/2;
                break;
            case 5:
                $nums = $this->getRatenum($date,1,$type,$m_statistics);
                $b = $nums['fjnum'];

                $nums = $this->getRatenum($date,2,$type,$m_statistics);
                $c = $nums['fjnum'];

                $a = ($b+$c)/2;
                break;
            case 6:
                $nums = $this->getRatenum($date,1,$type,$m_statistics);
                $b = $nums['zxnum'];

                $nums = $this->getRatenum($date,2,$type,$m_statistics);
                $c = $nums['zxnum'];

                $a = ($b+$c)/2;
                break;
            case 7:
                $nums = $this->getRatenum($date,1,$type,$m_statistics);
                $b = $nums['hdnum'];

                $nums = $this->getRatenum($date,2,$type,$m_statistics);
                $c = $nums['hdnum'];

                $a = ($b+$c)/2;
                break;
            case 8:
                $hotellevel_c = A('Hotellevel');
                $nums = $hotellevel_c->getHotellevel($date);
                $a = $nums['a'];
                $b = $nums['b'];
                $c = $nums['c'];
                break;
        }
        $chart = array('a'=>$a,'b'=>$b,'c'=>$c);
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
    public function getRatenum($date,$static_fj=0,$type=0,$m_statistics=''){
        if(empty($m_statistics)){
            $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        }
        $nums = array();
        if(in_array($type,array(0,1,2,3,4,5))){
            //互动饭局数
            $where = array('static_date'=>$date);
            if($static_fj)  $where['static_fj'] = $static_fj;
            $where['all_interact_nums'] = array('GT',0);
            $fields = "count(box_mac) as fjnum";
            $ret = $m_statistics->getOne($fields, $where);
            $nums['fjnum'] = $ret['fjnum'];
        }
        if(in_array($type,array(0,1,2,3,4,6))){
            //在线屏幕数
            $where = array('static_date'=>$date);
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
            if($static_fj)  $where['static_fj'] = $static_fj;
            $fields = 'sum(all_interact_nums) as hdnum';
            $ret = $m_statistics->getOne($fields, $where);
            $nums['hdnum'] = $ret['hdnum'];
        }
        return $nums;
    }


}