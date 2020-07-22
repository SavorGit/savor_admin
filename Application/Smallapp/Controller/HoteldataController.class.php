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


    public function grade(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');

        $where = array();
        if(empty($start_time)){
            $start_time = date('Ymd',strtotime("-7 day"));
        }else{
            $start_time = date('Ymd',strtotime($start_time));
        }
        if(empty($end_time)){
            $end_time = date('Ymd',strtotime("-1 day"));
        }else{
            $end_time = date('Ymd',strtotime($end_time));
        }
        $where['date'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        if($area_id)    $where['area_id'] = $area_id;
        if($box_type)   $where['hotel_box_type'] = $box_type;
        if($is_4g)      $where['is_4g'] = $is_4g;

        $m_hotelgrade = new \Admin\Model\HotelGradeModel();
        $fields = 'area_id,area_name,hotel_id,hotel_name,is_4g,hotel_box_type,avg(total_score) as score';
        $order = 'score desc';
        $group = 'hotel_id';
        $start  = ($page-1) * $size;
        $res_data = $m_hotelgrade->getDatas($fields,$where,$order,$group,$start,$size);
        $datalist = $res_data['list'];
        if($res_data['total']){
            $hotel_box_types = C('heart_hotel_box_type');
            foreach ($datalist as $k=>$v){
                if($v['is_4g']==1){
                    $is_4g_str = '是';
                }else{
                    $is_4g_str = '否';
                }
                $score = 0;
                if($v['score']>0){
                    $score = sprintf("%.1f",$v['score']);
                }
                $datalist[$k]['score'] = $score;
                $datalist[$k]['box_type_str'] = $hotel_box_types[$v['hotel_box_type']];
                $datalist[$k]['is_4g_str'] = $is_4g_str;
                $trees = $this->get_box_grades($v['hotel_id'],$start_time,$end_time);
                $datalist[$k]['trees'] = $trees;
            }
        }

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $this->assign('area', $area_arr);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('page',$res_data['page']);
        $this->assign('datalist',$datalist);
        $this->assign('start_time',date('Y-m-d',strtotime($start_time)));
        $this->assign('end_time',date('Y-m-d',strtotime($end_time)));
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->display();
    }


    public function get_box_grades($hotel_id,$start_time,$end_time){
        $m_boxgarade = new \Admin\Model\BoxGradeModel();
        $field = 'mac,is_4g,box_type,avg(total_score) as total_score,avg(mini_total_score) as mini_total_score';
        $where = array();
        $where['date'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $where['hotel_id'] = $hotel_id;
        $group = 'mac';
        $res_boxgarade = $m_boxgarade->getDatas($field,$where,'',$group);
        $datas = array();
        if(!empty($res_boxgarade)){
            $html = '<em></em>';
            $hotel_box_types = C('heart_hotel_box_type');
            $m_boxchange = new \Admin\Model\BoxChangeforscreenModel();
            $m_box = new \Admin\Model\BoxModel();
            $box_change_fields = 'is_sapp_forscreen,is_open_simple,add_time';
            foreach ($res_boxgarade as $k=>$v){
                if($v['is_4g']==1){
                    $is_4g_str = '是';
                }else{
                    $is_4g_str = '否';
                }
                $box_type_str = '';
                if(isset($hotel_box_types[$v['box_type']])){
                    $box_type_str = $hotel_box_types[$v['box_type']];
                }
                $total_score = $mini_total_score = 0;
                if($v['total_score']>0){
                    $total_score = sprintf("%.1f",$v['total_score']);
                }
                if($v['mini_total_score']>0){
                    $mini_total_score = sprintf("%.1f",$v['mini_total_score']);
                }
                $change_where = array('mac'=>$v['mac']);
                $res_box = $m_boxchange->getAll($box_change_fields,$change_where,0,1,'id desc','');
                $update_time = '';
                if(!empty($res_box)){
                    $update_time = $res_box[0]['add_time'];
                }else{
                    $bwhere = array('mac'=>$v['mac'],'state'=>1,'flag'=>0);
                    $res_box = $m_box->getInfo('*',$bwhere,'id desc','0,1');
                }

                $box_forscreen = "{$res_box[0]['is_sapp_forscreen']}-{$res_box[0]['is_open_simple']}";
                switch ($box_forscreen){
                    case '1-0':
                        $forscreen_type = '标准版';
                        break;
                    case '0-1':
                        $forscreen_type = '极简版';
                        break;
                    case '1-1':
                        $forscreen_type = '极简版';
                        break;
                    case '0-0':
                        $forscreen_type = '未开启';
                        break;
                    default:
                        $forscreen_type = '';
                }

                $dinfo = array('mac'=>$v['mac'],'html'=>$html,'is_4g_str'=>$is_4g_str,'total_score'=>$total_score,'forscreen_type'=>$forscreen_type,
                'mini_total_score'=>$mini_total_score,'box_type_str'=>$box_type_str,'update_time'=>$update_time);
                $datas[] = $dinfo;
            }
        }
        return $datas;
    }
}