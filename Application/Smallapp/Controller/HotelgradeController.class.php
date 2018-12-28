<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序数据统计-概况
 *
 */
class HotelgradeController extends BaseController {

    public function index(){
        $day = I('day',0,'intval');
        $level = I('level',0,'intval');
        $type = I('type',8,'intval');//1转换率,2传播力,3屏幕在线率,4网络质量,5互动饭局数,6在线屏幕数,7互动次数,8酒楼评级
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        if($day || (!empty($start_date) && !empty($end_date))){
            $days = $m_statistics->getDays($day,$start_date,$end_date);
            $start_date = date('Y-m-d',strtotime($days[0]));
            $end_date = date('Y-m-d',strtotime(end($days)));
        }else{
            $start_date = date('Y-m-d',strtotime('-7 day'));
            $end_date = date('Y-m-d',strtotime('-1 day'));
            $days = $m_statistics->getDays(0,$start_date,$end_date);
        }

        //指标趋势
        $chart_list = array();
        foreach ($days as $k=>$v){
            $charts = $this->ratioChart($type,$v,$level);
            $chart_list['a'][] = $charts['a'];
            $chart_list['b'][] = $charts['b'];
            $chart_list['c'][] = $charts['c'];
        }
        //详细数据
        $m_static_hotel = new \Admin\Model\Smallapp\StaticHotelgradeModel();
        $fields = '*';
        $where = array('static_date'=>date('Ymd',strtotime($end_date)));
        if($level){
            $where['level'] = $level;
        }
        $order = 'id asc';
        $res_hotel = $m_static_hotel->getList($fields,$where,$order);
        $m_hotel = new \Admin\Model\HotelModel();
        $detail_list = array();
        $detail_breaknum = 20;
        $levels = array(1=>'A',2=>'B',3=>'C');
        foreach ($res_hotel as $k=>$v){
            if($k==$detail_breaknum){
                break;
            }
            $hotel_id = $v['hotel_id'];
            $resh = $m_hotel->getOne($hotel_id);
            $info = array('date'=>$v['static_date'],'name'=>$resh['name'],'level_str'=>$levels[$v['level']]);
            $ratenums = $m_statistics->getRatenum($v['static_date'],0,9,$hotel_id);
            $info['heart_log_nums'] = $ratenums['xtnum'];
            $info['avg_speed'] = $v['avg_speed'].'kb/s';
            $info['interact_num'] = $v['interact_num'];
            $where = array();
            $where['hotel_id'] = $v['hotel_id'];
            $where['static_date'] = $v['static_date'];
            $fields =" count(id) as nums";
            $ret = $m_statistics->getOne($fields, $where);
            $info['all_position'] = intval($ret['nums']);
            $info['online_screen_num'] = $v['online_screen_num'];
            $info['position_num'] = $v['position_num'];
            $cvr = $v['cvr']*100;
            $info['cvr'] = $cvr.'%';
            $avg_coverage = round($v['avg_coverage'],1);
            $info['avg_coverage'] = $avg_coverage.'%';
            $detail_list[] = $info;
        }

        $all_legend = C('LEGEND_CONFIG');
        $this->assign('legend',$all_legend[$type]);
        if($type==8 && $level>0){
            $this->assign('chart',json_encode($chart_list['a']));
        }else{
            $this->assign('chart_a',json_encode($chart_list['a']));
            $this->assign('chart_b',json_encode($chart_list['b']));
            $this->assign('chart_c',json_encode($chart_list['c']));
        }
        $this->assign('detail_list',$detail_list);
        $this->assign('alldays',json_encode($days));
        $this->assign('level',$level);
        $this->assign('type',$type);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->display();
    }

    /*
 * 比率图表
 * type 1转换率,2传播力,3屏幕在线率,4网络质量,5互动饭局数,6在线屏幕数,7互动次数,8酒楼评级
 */
    public function ratioChart($type,$date,$level=0){
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $m_static_hotel = new \Admin\Model\Smallapp\StaticHotelgradeModel();
        $hotel_ids = 0;
        if($level){
            $fields = 'hotel_id';
            $where = array('static_date'=>$date,'level'=>$level);
            $order = 'id desc';
            $resdata = $m_static_hotel->getList($fields,$where,$order);
            $hotel_ids = array();
            foreach ($resdata as $dv){
                $hotel_ids[] = $dv['hotel_id'];
            }
        }
        switch ($type){
            case 1:
            case 2:
            case 3:
            case 4:
                $nums = $m_statistics->getRatenum($date,1,$type,$hotel_ids);
                $b = $m_statistics->getRate($nums,$type)/100;

                $nums = $m_statistics->getRatenum($date,2,$type,$hotel_ids);
                $c = $m_statistics->getRate($nums,$type)/100;

                $a = ($b+$c)/2;
                break;
            case 5:
                $nums = $m_statistics->getRatenum($date,1,$type,$hotel_ids);
                $b = $nums['fjnum'];

                $nums = $m_statistics->getRatenum($date,2,$type,$hotel_ids);
                $c = $nums['fjnum'];

                $a = ($b+$c)/2;
                break;
            case 6:
                $nums = $m_statistics->getRatenum($date,1,$type,$hotel_ids);
                $b = $nums['zxnum'];

                $nums = $m_statistics->getRatenum($date,2,$type,$hotel_ids);
                $c = $nums['zxnum'];

                $a = ($b+$c)/2;
                break;
            case 7:
                $nums = $m_statistics->getRatenum($date,1,$type,$hotel_ids);
                $b = $nums['hdnum'];

                $nums = $m_statistics->getRatenum($date,2,$type,$hotel_ids);
                $c = $nums['hdnum'];
                $a = ($b+$c)/2;
                break;
            case 8:
                if($level==0){
                    $fields = 'hotel_id,level';
                    $where = array('static_date'=>$date);
                    $order = 'id desc';
                    $res_hotel = $m_static_hotel->getListnums($fields,$where,$order);
                    $a = $res_hotel['a'];
                    $b = $res_hotel['b'];
                    $c = $res_hotel['c'];
                }else{
                    $a = $m_static_hotel->getGradenums($date,$level);
                    $b = 0;
                    $c = 0;
                }
                break;
        }
        $chart = array('a'=>$a,'b'=>$b,'c'=>$c);
        return $chart;
    }


}