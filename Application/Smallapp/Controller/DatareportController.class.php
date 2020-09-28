<?php
namespace Smallapp\Controller; 
use Admin\Controller\BaseController ;

/**
 * @desc 数据报表
 *
 */
class DatareportController extends BaseController {

    public function onlinerate(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');
        $is_train = I('is_train',99,'intval');
        $trainer_id = I('trainer_id',0,'intval');
        $fault_status = I('fault_status',0,'intval');

        if(empty($start_time)){
            $start_time = date('Ymd');
        }else{
            $start_time = date('Ymd',strtotime($start_time));
        }
        if(empty($end_time)){
            $end_time = $start_time;
        }else{
            $end_time = date('Ymd',strtotime($end_time));
        }
        $where = array();
        $where['s.static_date'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        $day = (strtotime($end_time) - strtotime($start_time))/86400;

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        if($area_id){
            $where['s.area_id'] = $area_id;
        }
        if($maintainer_id){
            $where['s.maintainer_id'] = $maintainer_id;
        }
        $where['b.state'] = 1;
        $where['b.flag'] = 0;
        if($is_4g){
            if($is_4g == 1){
                $where['b.is_4g'] = 1;
            }else{
                $where['b.is_4g'] = 0;
            }
        }
        if($fault_status){
            $where['b.fault_status'] = $fault_status;
        }
        if($is_train!=99){
            $where['hext.is_train'] = $is_train;
        }
        if($trainer_id){
            $where['hext.trainer_id'] = $trainer_id;
        }

        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['s.hotel_box_type'] = $box_type;
            $where_wl = $where;
        }else{
            $where_wl = $where;
//            $box_types = array_keys($hotel_box_types);
//            $where['s.hotel_box_type'] = array('in',$box_types);
        }
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        //网络屏幕数
        $fields = "count(DISTINCT s.box_mac) as wlnum";
        $ret = $m_statistics->getOnlinnum($fields, $where_wl);
        $wlnum = intval($ret[0]['wlnum']);
        if($day){
            $wlnum = ($day+1)*$wlnum;
        }

        //故障网络屏幕数
        $fault_wlnum = 0;
        if($fault_status==0){
            $fields = "count(DISTINCT s.box_mac) as faultwlnum";
            $where_wl['b.fault_status'] = 2;
            $ret = $m_statistics->getOnlinnum($fields, $where_wl);
            $fault_wlnum = intval($ret[0]['faultwlnum']);
            if($day){
                $fault_wlnum = ($day+1)*$fault_wlnum;
            }
        }

        //在线屏幕数
//        $where['heart_log_meal_nums'] = array('GT',12);
        $where['s.heart_log_meal_nums'] = array('GT',5);
        $where['_string'] = 'case s.static_fj when 1 then (120 div s.heart_log_meal_nums)<10  else (180 div s.heart_log_meal_nums)<10 end';
        $fields = 'count(s.box_mac) as zxnum';
        $where['s.static_fj'] = 1;//1:午饭2:晚饭
        $ret = $m_statistics->getOnlinnum($fields, $where);
        $zxnum = intval($ret[0]['zxnum']);
        $nums = array('wlnum'=>$wlnum,'zxnum'=>$zxnum);
        $rate = $m_statistics->getRate($nums,3);
        $online = array('wlnum'=>$wlnum,'zxnum'=>$zxnum,'rate'=>$rate);
        $data_list = array('lunch'=>$online);

        $where['s.static_fj'] = 2;//1:午饭2:晚饭
        $ret = $m_statistics->getOnlinnum($fields, $where);
        $zxnum = intval($ret[0]['zxnum']);
        $nums = array('wlnum'=>$wlnum,'zxnum'=>$zxnum);
        $rate = $m_statistics->getRate($nums,3);
        $online = array('wlnum'=>$wlnum,'zxnum'=>$zxnum,'rate'=>$rate);
        $data_list['dinner'] = $online;

        $opusers = $this->getOpuser($maintainer_id);
        $users = $this->getUsers($trainer_id);

        $this->assign('start_time',date('Y-m-d',strtotime($start_time)));
        $this->assign('end_time',date('Y-m-d',strtotime($end_time)));
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('is_train',$is_train);
        $this->assign('fault_status',$fault_status);
        $this->assign('fault_wlnum',$fault_wlnum);
        $this->assign('area', $area_arr);
        $this->assign('users', $users);
        $this->assign('opusers', $opusers);
        $this->assign('data_list',$data_list);
        $this->display();
    }

    public function trainonlinerate(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');
        $is_train = I('is_train',99,'intval');
        $trainer_id = I('trainer_id',0,'intval');
        $fault_status = I('fault_status',0,'intval');

        if(empty($start_time)){
            $start_time = date('Ymd');
        }else{
            $start_time = date('Ymd',strtotime($start_time));
        }
        if(empty($end_time)){
            $end_time = $start_time;
        }else{
            $end_time = date('Ymd',strtotime($end_time));
        }
        $where = array('a.state'=>1,'a.flag'=>0,'a.type'=>1);
        if($area_id){
            $where['a.area_id'] = $area_id;
        }
        if($box_type){
            $where['a.hotel_box_type'] = $box_type;
        }
        if($is_train!=99){
            $where['ext.is_train'] = $is_train;
        }
        if($trainer_id){
            $where['ext.trainer_id'] = $trainer_id;
        }
        $m_hotel = new \Admin\Model\HotelModel();
        $start  = ($page-1) * $size;
        $fields = 'a.id as hotel_id,a.name as hotel_name,area.region_name as area_name,ext.is_train,ext.trainer_id,ext.train_date,ext.train_desc';
        $result = $m_hotel->getListExt($where, 'a.id desc', $start,$size,$fields);
        $datalist = $result['list'];
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $hotel_datas = array();
        foreach ($datalist as $k=>$v){
            $hotel_id = $v['hotel_id'];
            $where_hotel = array('s.hotel_id'=>$hotel_id);
            $where_hotel['s.static_date'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
            $where_hotel['b.state'] = 1;
            $where_hotel['b.flag'] = 0;
            if($is_4g){
                if($is_4g == 1){
                    $where_hotel['b.is_4g'] = 1;
                }else{
                    $where_hotel['b.is_4g'] = 0;
                }
            }
            if($fault_status){
                $where_hotel['b.fault_status'] = $fault_status;
            }
            if($is_train!=99){
                $where_hotel['hext.is_train'] = $is_train;
            }
            if($trainer_id){
                $where_hotel['hext.trainer_id'] = $trainer_id;
            }
            if($box_type){
                $where_hotel['s.hotel_box_type'] = $box_type;
                $where_wl = $where_hotel;
            }else{
                $where_wl = $where_hotel;
            }
            $day = (strtotime($end_time) - strtotime($start_time))/86400;
            //网络屏幕数
            $fields = "count(DISTINCT s.box_mac) as wlnum";
            $ret = $m_statistics->getOnlinnum($fields, $where_wl);
            $wlnum = intval($ret[0]['wlnum']);
            if($day){
                $wlnum = ($day+1)*$wlnum;
            }

            //故障网络屏幕数
            $fault_wlnum = 0;
            if($fault_status==0){
                $fields = "count(DISTINCT s.box_mac) as faultwlnum";
                $where_wl['b.fault_status'] = 2;
                $ret_fault = $m_statistics->getOnlinnum($fields, $where_wl);
                $fault_wlnum = intval($ret_fault[0]['faultwlnum']);
                if($day){
                    $fault_wlnum = ($day+1)*$fault_wlnum;
                }
            }

            $v['wlnum'] = $wlnum;
            $v['fault_wlnum'] = $fault_wlnum;
            //在线屏幕数
            $where_hotel['s.heart_log_meal_nums'] = array('GT',5);
            $where_hotel['_string'] = 'case s.static_fj when 1 then (120 div s.heart_log_meal_nums)<10  else (180 div s.heart_log_meal_nums)<10 end';
            $fields = 'count(s.box_mac) as zxnum,s.static_fj';
            $res_online = $m_statistics->getOnlinnum($fields, $where_hotel,'s.static_fj');
            foreach ($res_online as $ov){
                if($ov['static_fj']==1){
                    $v['lunch_zxnum'] = $ov['zxnum'];
                    $nums = array('wlnum'=>$wlnum,'zxnum'=>$ov['zxnum']);
                    $v['lunch_rate'] = $m_statistics->getRate($nums,3);
                }
                if($ov['static_fj']==2){
                    $v['dinner_zxnum'] = $ov['zxnum'];
                    $nums = array('wlnum'=>$wlnum,'zxnum'=>$ov['zxnum']);
                    $v['dinner_rate'] = $m_statistics->getRate($nums,3);
                }
            }
            if($v['lunch_zxnum']){
                $v['lunch_rate'] = $v['lunch_rate'].'%';
            }else{
                $v['lunch_rate'] = '';
            }
            if($v['dinner_zxnum']){
                $v['dinner_rate'] = $v['dinner_rate'].'%';
            }else{
                $v['dinner_rate'] = '';
            }
            if($v['train_date']=='0000-00-00'){
                $v['train_date'] = '';
            }
            $hotel_datas[]=$v;
        }

        $users = $this->getUsers($trainer_id);
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $this->assign('start_time',date('Y-m-d',strtotime($start_time)));
        $this->assign('end_time',date('Y-m-d',strtotime($end_time)));
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('is_train',$is_train);
        $this->assign('fault_status',$fault_status);
        $this->assign('area', $area_arr);
        $this->assign('users', $users);
        $this->assign('datalist', $hotel_datas);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function hotelassess(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $hotel_level = I('level','');
        $hotel_team = I('team','');
        $keyword = I('keyword','','trim');
        $all_assess = I('all_assess',0,'intval');
        $operation_assess = I('operation_assess',0,'intval');
        $channel_assess = I('channel_assess',0,'intval');
        $data_assess = I('data_assess',0,'intval');
        $saledata_assess = I('saledata_assess',0,'intval');
        $is_train = I('is_train',99,'intval');

        if(empty($start_time)){
            $start_time = date('Ymd',strtotime('-1 day'));
        }else{
            $start_time = date('Ymd',strtotime($start_time));
        }
        if(empty($end_time)){
            $end_time = $start_time;
        }else{
            $end_time = date('Ymd',strtotime($end_time));
        }
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $all_dates = $m_statistics->getDates($start_time,$end_time,2);
        $where = array('a.date'=>array('in',$all_dates));
        if($hotel_level){
            $where['a.hotel_level'] = $hotel_level;
        }
        $teams = array(
            'Oiyoboy'=>array('吴琳','朱宇杰','欧懿'),
            '勇者队'=>array('曾峰','陈远程','甘顺山'),
            '超凡队'=>array('王习宗','熊静怡','何永锐'),
        );
        $team = $tmember = array();
        foreach ($teams as $k=>$v){
            $is_select = 0;
            if($k==$hotel_team){
                $is_select = 1;
            }
            $team[] = array('name'=>$k,'is_select'=>$is_select);
            foreach ($v as $uv){
                $tmember[$uv]=$k;
            }
        }
        if($hotel_team){
            $where['a.team_name'] = array('in',$teams[$hotel_team]);
        }
        if($is_train!=99){
            $where['ext.is_train'] = $is_train;
        }
        if($keyword){
            $where['a.hotel_name'] = array('like',"%$keyword%");
        }
        $m_staticassess = new \Admin\Model\Smallapp\StaticHotelassessModel();
        $start  = ($page-1) * $size;
        $fields = 'a.hotel_id,a.hotel_name,a.hotel_level,a.team_name,ext.is_train,
        avg(a.box_num) as box_num,avg(a.lostbox_num) as lostbox_num,avg(a.fault_rate) as fault_rate,avg(a.zxrate) as zxrate,avg(a.fjrate) as fjrate,avg(a.fjsalerate) as fjsalerate';
        $countfields = 'count(DISTINCT(a.hotel_id)) as tp_count';
        $groupby = 'a.hotel_id';
        $order = 'a.hotel_level asc';

        $is_all = 0;
        if($all_assess || $operation_assess || $channel_assess || $data_assess ||$saledata_assess){
            $is_all = 1;
            $start = 0;
            $size = 1000;
        }
        $is_all = 1;
        $start = 0;
        $size = 1000;
        $result = $m_staticassess->getCustomeList($fields,$where,$groupby,$order,$countfields,$start,$size);
        $datalist = $result['list'];
        $pagestyle = $result['page'];
        if($is_all){
            $res_data = array();
            foreach ($datalist as $k=>$v){
                $assess = $m_staticassess->getHotelassessResult($v);
                $is_del = 0;
                if($all_assess && $assess['all_assess']!=$all_assess){
                    $is_del = 1;
                    unset($datalist[$k]);
                }
                if($operation_assess && $assess['operation_assess']!=$operation_assess){
                    $is_del = 1;
                    unset($datalist[$k]);
                }
                if($channel_assess && $assess['channel_assess']!=$channel_assess){
                    $is_del = 1;
                    unset($datalist[$k]);
                }
                if($data_assess && $assess['data_assess']!=$data_assess){
                    $is_del = 1;
                    unset($datalist[$k]);
                }
                if($saledata_assess && $assess['saledata_assess']!=$saledata_assess){
                    $is_del = 1;
                    unset($datalist[$k]);
                }
                if($is_del==0){
                    $res_data[]=$assess;
                }
            }
            $datalist = $res_data;
            $total_count = count($datalist);
            $objPage = new \Common\Lib\Page($total_count,$size);
            $pagestyle = $objPage->admin_page();

            $start  = ($page-1) * $size;
            $datalist = array_slice($datalist,$start,$size);
        }


        foreach ($datalist as $k=>$v){
            $datalist[$k]['team'] = $tmember[$v['team_name']];
            $datalist[$k]['box_num'] = intval($v['box_num']);
            $datalist[$k]['lostbox_num'] = intval($v['lostbox_num']);
            $datalist[$k]['fault_rate'] = sprintf('%.2f',$v['fault_rate']);
            $datalist[$k]['zxrate'] = sprintf('%.2f',$v['zxrate']);
            $datalist[$k]['fjrate'] = sprintf('%.2f',$v['fjrate']);
            $datalist[$k]['fjsalerate'] = sprintf('%.2f',$v['fjsalerate']);
            if($v['is_train']==0){
                $datalist[$k]['data_assess'] = 3;
                $datalist[$k]['saledata_assess'] = 3;
                if($v['operation_assess']==1 && $v['channel_assess']==1){
                    $datalist[$k]['all_assess'] = 1;
                }else{
                    $datalist[$k]['all_assess'] = 2;
                }
            }
        }

        $avg_where = array('a.date'=>array('in',$all_dates));
        if($hotel_team){
            $avg_where['a.team_name'] = array('in',$teams[$hotel_team]);
        }
        if($is_train!=99){
            $avg_where['ext.is_train'] = $is_train;
        }
        $fields = 'avg(a.fault_rate) as fault_rate,avg(a.zxrate) as zxrate,avg(a.fjrate) as fjrate,avg(a.fjsalerate) as fjsalerate,a.hotel_level';
        $groupby = 'a.hotel_level';
        $order = '';
        $res_avgdata = $m_staticassess->getHotelassess($fields,$avg_where,$groupby,$order,0,10);
        $avg_data = array();
        foreach ($res_avgdata as $v){
            $fault_rate = sprintf("%.2f",$v['fault_rate']);
            $fault_rate = $fault_rate*100;
            $zxrate = sprintf("%.2f",$v['zxrate']);
            $zxrate = $zxrate*100;
            $fjrate = sprintf("%.2f",$v['fjrate']);
            $fjrate = $fjrate*100;
            $fjsalerate = sprintf("%.2f",$v['fjsalerate']);
            $fjsalerate = $fjsalerate*100;

            $avg_data[]=array('hotel_level'=>$v['hotel_level'],'fault_rate'=>$fault_rate.'%','zxrate'=>$zxrate.'%','fjrate'=>$fjrate.'%','fjsalerate'=>$fjsalerate.'%');
        }

        $this->assign('start_time',date('Y-m-d',strtotime($start_time)));
        $this->assign('end_time',date('Y-m-d',strtotime($end_time)));
        $this->assign('avg_data',$avg_data);
        $this->assign('level',$hotel_level);
        $this->assign('is_train',$is_train);
        $this->assign('keyword',$keyword);
        $this->assign('all_assess',$all_assess);
        $this->assign('operation_assess',$operation_assess);
        $this->assign('channel_assess',$channel_assess);
        $this->assign('data_assess',$data_assess);
        $this->assign('saledata_assess',$saledata_assess);
        $this->assign('hotel_team', $hotel_team);
        $this->assign('team', $team);
        $this->assign('datalist',$datalist);
        $this->assign('page',$pagestyle);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function assesschart(){
        $hotel_id = I('hotel_id',0,'intval');

        $begin_lastweek_time = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
        $end_lastweek_time = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
        $begin_lastweek_date = date('Y-m-d',$begin_lastweek_time);
        $end_lastweek_date = date('Y-m-d',$end_lastweek_time);

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $all_dates = $m_statistics->getDates($begin_lastweek_date,$end_lastweek_date,2);
        $where = array('hotel_id'=>$hotel_id,'date'=>array('in',$all_dates));
        $m_staticassess = new \Admin\Model\Smallapp\StaticHotelassessModel();
        $res_data = $m_staticassess->getDataList('*',$where,'date asc');
        $lastweek_fault = $lastweek_zx = $lastweek_fj = $lastweek_fjsale =array();
        foreach ($res_data as $v){
            $lastweek_fault[]=$v['fault_rate'];
            $lastweek_zx[]=$v['zxrate'];
            $lastweek_fj[]=$v['fjrate'];
            $lastweek_fjsale[]=$v['fjsalerate'];
        }

        $default_date = date("Y-m-d");
        $first=1;
        $w = date('w');
        $week_start=date('Y-m-d', strtotime("$default_date -".($w ? $w - $first : 6).' days'));
        $week_end=date('Y-m-d',strtotime("$week_start +6 days"));
        $all_dates = $m_statistics->getDates($week_start,$week_end,2);
        $where = array('hotel_id'=>$hotel_id,'date'=>array('in',$all_dates));
        $m_staticassess = new \Admin\Model\Smallapp\StaticHotelassessModel();
        $res_data = $m_staticassess->getDataList('*',$where,'date asc');
        $week_fault = $week_zx = $week_fj = $week_fjsale =array();
        foreach ($res_data as $v){
            $week_fault[]=$v['fault_rate'];
            $week_zx[]=$v['zxrate'];
            $week_fj[]=$v['fjrate'];
            $week_fjsale[]=$v['fjsalerate'];
        }

        $legend = array('故障率(上周)','故障率(本周)','在线率(上周)','在线率(本周)','饭局转化率(上周)','饭局转化率(本周)','销售端转化率(上周)','销售端转化率(本周)');
        $week_day = array('周一','周二','周三','周四','周五','周六','周日');
        $this->assign('legend',$legend);
        $this->assign('week_day',json_encode($week_day));
        $this->assign('lastweek_fault',json_encode($lastweek_fault));
        $this->assign('lastweek_zx',json_encode($lastweek_zx));
        $this->assign('lastweek_fj',json_encode($lastweek_fj));
        $this->assign('lastweek_fjsale',json_encode($lastweek_fjsale));
        $this->assign('week_fault',json_encode($week_fault));
        $this->assign('week_zx',json_encode($week_zx));
        $this->assign('week_fj',json_encode($week_fj));
        $this->assign('week_fjsale',json_encode($week_fjsale));

        $this->display('assesschart');

    }

    public function assesswholechart(){
        $begin_lastweek_time = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
        $end_lastweek_time = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
        $begin_lastweek_date = date('Y-m-d',$begin_lastweek_time);
        $end_lastweek_date = date('Y-m-d',$end_lastweek_time);

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $all_dates = $m_statistics->getDates($begin_lastweek_date,$end_lastweek_date,2);
        $where = array('date'=>array('in',$all_dates));
        $m_staticassess = new \Admin\Model\Smallapp\StaticHotelassessModel();
        $fields = 'avg(fault_rate) as fault_rate,avg(zxrate) as zxrate';
        $res_data = $m_staticassess->getAll($fields,$where,0,1000,'','date');
        $lastweek_fault = $lastweek_zx = $lastweek_fj = $lastweek_fjsale =array();
        foreach ($res_data as $v){
            $lastweek_fault[] = sprintf("%.2f",$v['fault_rate']);
            $lastweek_zx[] = sprintf("%.2f",$v['zxrate']);
        }

        $default_date = date("Y-m-d");
        $first=1;
        $w = date('w');
        $week_start=date('Y-m-d', strtotime("$default_date -".($w ? $w - $first : 6).' days'));
        $week_end=date('Y-m-d',strtotime("$week_start +6 days"));
        $all_dates = $m_statistics->getDates($week_start,$week_end,2);
        $where = array('date'=>array('in',$all_dates));
        $m_staticassess = new \Admin\Model\Smallapp\StaticHotelassessModel();
        $fields = 'avg(fault_rate) as fault_rate,avg(zxrate) as zxrate';
        $res_data = $m_staticassess->getAll($fields,$where,0,1000,'','date');
        $week_fault = $week_zx = $week_fj = $week_fjsale =array();
        foreach ($res_data as $v){
            $week_fault[] = sprintf("%.2f",$v['fault_rate']);
            $week_zx[] = sprintf("%.2f",$v['zxrate']);
        }

        $legend = array('故障率(上周)','故障率(本周)','在线率(上周)','在线率(本周)');
        $week_day = array('周一','周二','周三','周四','周五','周六','周日');
        $this->assign('legend',$legend);
        $this->assign('week_day',json_encode($week_day));
        $this->assign('lastweek_fault',json_encode($lastweek_fault));
        $this->assign('lastweek_zx',json_encode($lastweek_zx));
        $this->assign('week_fault',json_encode($week_fault));
        $this->assign('week_zx',json_encode($week_zx));

        $this->display('assesswholechart');

    }

    public function assessgroupchart(){
        $begin_lastweek_time = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
        $end_lastweek_time = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
        $begin_lastweek_date = date('Y-m-d',$begin_lastweek_time);
        $end_lastweek_date = date('Y-m-d',$end_lastweek_time);

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $all_dates = $m_statistics->getDates($begin_lastweek_date,$end_lastweek_date,2);
        $where = array('date'=>array('in',$all_dates));
        $m_staticassess = new \Admin\Model\Smallapp\StaticHotelassessModel();
        $where['team_name'] = '吴琳';
        $fields = 'avg(fault_rate) as fault_rate,avg(zxrate) as zxrate';
        $res_data = $m_staticassess->getAll($fields,$where,0,1000,'','date');
        $lastweeka_fault = $lastweeka_zx = array();
        foreach ($res_data as $v){
            $lastweeka_fault[] = sprintf("%.2f",$v['fault_rate']);
            $lastweeka_zx[] = sprintf("%.2f",$v['zxrate']);
        }
        $where['team_name'] = '王习宗';
        $fields = 'avg(fault_rate) as fault_rate,avg(zxrate) as zxrate';
        $res_data = $m_staticassess->getAll($fields,$where,0,1000,'','date');
        $lastweekb_fault = $lastweekb_zx = array();
        foreach ($res_data as $v){
            $lastweekb_fault[] = sprintf("%.2f",$v['fault_rate']);
            $lastweekb_zx[] = sprintf("%.2f",$v['zxrate']);
        }
        $where['team_name'] = '曾峰';
        $fields = 'avg(fault_rate) as fault_rate,avg(zxrate) as zxrate';
        $res_data = $m_staticassess->getAll($fields,$where,0,1000,'','date');
        $lastweekc_fault = $lastweekc_zx = array();
        foreach ($res_data as $v){
            $lastweekc_fault[] = sprintf("%.2f",$v['fault_rate']);
            $lastweekc_zx[] = sprintf("%.2f",$v['zxrate']);
        }

        $default_date = date("Y-m-d");
        $first=1;
        $w = date('w');
        $week_start=date('Y-m-d', strtotime("$default_date -".($w ? $w - $first : 6).' days'));
        $week_end=date('Y-m-d',strtotime("$week_start +6 days"));
        $all_dates = $m_statistics->getDates($week_start,$week_end,2);
        $where = array('date'=>array('in',$all_dates));
        $where['team_name'] = '吴琳';
        $m_staticassess = new \Admin\Model\Smallapp\StaticHotelassessModel();
        $fields = 'avg(fault_rate) as fault_rate,avg(zxrate) as zxrate';
        $res_data = $m_staticassess->getAll($fields,$where,0,1000,'','date');
        $weeka_fault = $weeka_zx = array();
        foreach ($res_data as $v){
            $weeka_fault[] = sprintf("%.2f",$v['fault_rate']);
            $weeka_zx[] = sprintf("%.2f",$v['zxrate']);
        }
        $where['team_name'] = '王习宗';
        $m_staticassess = new \Admin\Model\Smallapp\StaticHotelassessModel();
        $fields = 'avg(fault_rate) as fault_rate,avg(zxrate) as zxrate';
        $res_data = $m_staticassess->getAll($fields,$where,0,1000,'','date');
        $weekb_fault = $weekb_zx = array();
        foreach ($res_data as $v){
            $weekb_fault[] = sprintf("%.2f",$v['fault_rate']);
            $weekb_zx[] = sprintf("%.2f",$v['zxrate']);
        }
        $where['team_name'] = '曾峰';
        $m_staticassess = new \Admin\Model\Smallapp\StaticHotelassessModel();
        $fields = 'avg(fault_rate) as fault_rate,avg(zxrate) as zxrate';
        $res_data = $m_staticassess->getAll($fields,$where,0,1000,'','date');
        $weekc_fault = $weekc_zx = array();
        foreach ($res_data as $v){
            $weekc_fault[] = sprintf("%.2f",$v['fault_rate']);
            $weekc_zx[] = sprintf("%.2f",$v['zxrate']);
        }


        $legend = array('吴琳组-故障率(上周)','吴琳组-故障率(本周)','吴琳组-组A在线率(上周)','吴琳组-组A在线率(本周)',
            '王习宗组-故障率(上周)','王习宗组-故障率(本周)','王习宗组-在线率(上周)','王习宗组-在线率(本周)',
            '曾峰组-故障率(上周)','曾峰组-故障率(本周)','曾峰组-在线率(上周)','曾峰组-在线率(本周)',
            );
        $week_day = array('周一','周二','周三','周四','周五','周六','周日');
        $this->assign('legend',$legend);
        $this->assign('week_day',json_encode($week_day));
        $this->assign('lastweeka_fault',json_encode($lastweeka_fault));
        $this->assign('lastweeka_zx',json_encode($lastweeka_zx));
        $this->assign('weeka_fault',json_encode($weeka_fault));
        $this->assign('weeka_zx',json_encode($weeka_zx));

        $this->assign('lastweekb_fault',json_encode($lastweekb_fault));
        $this->assign('lastweekb_zx',json_encode($lastweekb_zx));
        $this->assign('weekb_fault',json_encode($weekb_fault));
        $this->assign('weekb_zx',json_encode($weekb_zx));

        $this->assign('lastweekc_fault',json_encode($lastweekc_fault));
        $this->assign('lastweekc_zx',json_encode($lastweekc_zx));
        $this->assign('weekc_fault',json_encode($weekc_fault));
        $this->assign('weekc_zx',json_encode($weekc_zx));

        $this->display('assessgroupchart');

    }

    public function assessdetailchart(){
        $begin_date = '2020-08-24';
        $end_date = date('Y-m-d',strtotime('-1 day'));

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $all_dates = $m_statistics->getDates($begin_date,$end_date,2);
        $where = array('date'=>array('in',$all_dates));
        $m_staticassess = new \Admin\Model\Smallapp\StaticHotelassessModel();
        $where['team_name'] = '吴琳';
        $fields = 'avg(fault_rate) as fault_rate,avg(zxrate) as zxrate,avg(fjrate) as fjrate,avg(fjsalerate) as fjsalerate';
        $res_data = $m_staticassess->getAll($fields,$where,0,1000,'','date');
        $wl_fault = $wl_zx = $wl_fj = $wl_fjsale = array();
        foreach ($res_data as $v){
            $wl_fault[] = sprintf("%.2f",$v['fault_rate']);
            $wl_zx[] = sprintf("%.2f",$v['zxrate']);
            $wl_fj[] = sprintf("%.2f",$v['fjrate']);
            $wl_fjsale[] = sprintf("%.2f",$v['fjsalerate']);
        }


        $where['team_name'] = '王习宗';
        $res_data = $m_staticassess->getAll($fields,$where,0,1000,'','date');
        $wxz_fault = $wxz_zx = $wxz_fj = $wxz_fjsale = array();
        foreach ($res_data as $v){
            $wxz_fault[] = sprintf("%.2f",$v['fault_rate']);
            $wxz_zx[] = sprintf("%.2f",$v['zxrate']);
            $wxz_fj[] = sprintf("%.2f",$v['fjrate']);
            $wxz_fjsale[] = sprintf("%.2f",$v['fjsalerate']);
        }

        $where['team_name'] = '曾峰';
        $res_data = $m_staticassess->getAll($fields,$where,0,1000,'','date');
        $zf_fault = $zf_zx = $zf_fj = $zf_fjsale = array();
        foreach ($res_data as $v){
            $zf_fault[] = sprintf("%.2f",$v['fault_rate']);
            $zf_zx[] = sprintf("%.2f",$v['zxrate']);
            $zf_fj[] = sprintf("%.2f",$v['fjrate']);
            $zf_fjsale[] = sprintf("%.2f",$v['fjsalerate']);
        }

        $where = array('date'=>array('in',$all_dates));
        $m_staticassess = new \Admin\Model\Smallapp\StaticHotelassessModel();
        $fields = 'avg(fault_rate) as fault_rate,avg(zxrate) as zxrate,avg(fjrate) as fjrate,avg(fjsalerate) as fjsalerate';
        $res_data = $m_staticassess->getAll($fields,$where,0,1000,'','date');
        $fault = $zx = $fj = $fjsale = array();
        foreach ($res_data as $v){
            $fault[] = sprintf("%.2f",$v['fault_rate']);
            $zx[] = sprintf("%.2f",$v['zxrate']);
            $fj[] = sprintf("%.2f",$v['fjrate']);
            $fjsale[] = sprintf("%.2f",$v['fjsalerate']);
        }

        $legend = array(
            '整体-故障率','吴琳组-故障率','王习宗组-故障率','曾峰组-故障率',
            '整体-在线率','吴琳组-在线率','王习宗组-在线率','曾峰组-在线率',
            '整体-饭局转化率','吴琳组-饭局转化率','王习宗组-饭局转化率','曾峰组-饭局转化率',
            '整体-销售端饭局转化率','吴琳组-销售端饭局转化率','王习宗组-销售端饭局转化率','曾峰组-销售端饭局转化率',

        );

        $this->assign('legend',$legend);
        $this->assign('week_day',json_encode($all_dates));

        $this->assign('fault',json_encode($fault));
        $this->assign('wl_fault',json_encode($wl_fault));
        $this->assign('wxz_fault',json_encode($wxz_fault));
        $this->assign('zf_fault',json_encode($zf_fault));

        $this->assign('zx',json_encode($zx));
        $this->assign('wl_zx',json_encode($wl_zx));
        $this->assign('wxz_zx',json_encode($wxz_zx));
        $this->assign('zf_zx',json_encode($zf_zx));

        $this->assign('fj',json_encode($fj));
        $this->assign('wl_fj',json_encode($wl_fj));
        $this->assign('wxz_fj',json_encode($wxz_fj));
        $this->assign('zf_fj',json_encode($zf_fj));

        $this->assign('fjsale',json_encode($fjsale));
        $this->assign('wl_fjsale',json_encode($wl_fjsale));
        $this->assign('wxz_fjsale',json_encode($wxz_fjsale));
        $this->assign('zf_fjsale',json_encode($zf_fjsale));


        $this->display('assessdetailchart');

    }

    public function interactnum(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $small_app_id = I('small_app_id',0,'intval');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');

        $where = array('a.is_valid'=>1);
        if($start_time && $end_time){
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        } else if($start_time && empty($end_time)){
            $end_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        }else if(empty($start_time) && !empty($end_time)){
            $start_time = '2018-07-23';
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        }else{
            $start_time = date('Y-m-d');
            $end_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        }

        if($small_app_id){
            if($small_app_id == 2){
                $where['a.small_app_id'] = array('in',array(2,3));
            }else{
                $where['a.small_app_id'] = $small_app_id;
            }
        }
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        if($area_id){
            $where['area.id'] = $area_id;
        }
        $where['box.state'] = 1;
        $where['box.flag'] = 0;
        $where['a.mobile_brand'] = array('neq','devtools');

        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['hotel.hotel_box_type'] = $box_type;
        }else{
//            $box_types = array_keys($hotel_box_types);
//            $where['hotel.hotel_box_type'] = array('in',$box_types);
        }

        if($is_4g){
            if($is_4g == 1){
                $where['box.is_4g'] = 1;
            }else{
                $where['box.is_4g'] = 0;
            }
        }
        if($maintainer_id){
            $where['hotelext.maintainer_id'] = $maintainer_id;
        }
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $fields = 'count(a.id) as hdnum,count(DISTINCT(hotel.id)) as hotelnum,count(DISTINCT(a.box_mac)) as boxnum,count(DISTINCT(a.openid)) as usernum';
        $nums = $m_smallapp_forscreen_record->getInfo($fields,$where);
        $nums['total_boxnums'] = 0;
        $m_statis = new \Admin\Model\Smallapp\StatisticsModel();
        if($end_time>=$start_time){
            unset($where['a.create_time']);
            $where['_string'] = "DATE(a.create_time)>='$start_time' AND DATE(a.create_time)<='$end_time'";
            $fields = "DATE(a.create_time) as screen_createtime,count(DISTINCT (a.box_mac)) as boxnum";
            $group = 'screen_createtime';
            $res_nums = $m_smallapp_forscreen_record->getInfo($fields,$where,$group,0);
            $total_boxnums = 0;
            foreach ($res_nums as $v){
                $total_boxnums +=$v['boxnum'];
            }
            $nums['total_boxnums'] = $total_boxnums;
        }

        unset($where['a.is_valid'],$where['a.create_time'],$where['a.small_app_id'],$where['a.mobile_brand'],$where['hotelext.maintainer_id'],$where['_string']);
        if($maintainer_id){
            $where['a.maintainer_id'] = $maintainer_id;
        }

        $where['a.static_date'] = array(array('EGT',date('Ymd',strtotime($start_time))),array('ELT',date('Ymd',strtotime($end_time))));
        $where['a.all_interact_nums'] = array('GT',0);
        $fields = "count(a.box_mac) as fjnum";
        $ret = $m_statis->getFeast($fields, $where);
        if($ret){
            $nums['fjnum'] = $ret['fjnum'];
        }else{
            $nums['fjnum'] = 0;
        }
        $all_smallapps = C('all_smallapps');
        unset($all_smallapps[3]);
        $opusers = $this->getOpuser($maintainer_id);
        $this->assign('opusers', $opusers);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('small_app_id',$small_app_id);
        $this->assign('area', $area_arr);
        $this->assign('all_smallapps', $all_smallapps);
        $this->assign('nums',$nums);
        $this->display('interactnum');
    }

    public function interactdiff(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $estart_time = I('estart_time','');
        $eend_time = I('eend_time','');
        $small_app_id = I('small_app_id',0,'intval');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');

        $where = array();
        if($start_time && $end_time){
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        } else if($start_time && empty($end_time)){
            $end_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        }else if(empty($start_time) && !empty($end_time)){
            $start_time = '2018-07-23';
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        }else{
            $start_time = date('Y-m-d');
            $end_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        }
        $where['a.is_valid'] = 1;

        if($small_app_id){
            if($small_app_id == 2){
                $where['a.small_app_id'] = array('in',array(2,3));
            }else{
                $where['a.small_app_id'] = $small_app_id;
            }
        }

        $hotel_where = array('a.state'=>1,'a.flag'=>0);
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        if($area_id){
            $where['area.id'] = $area_id;
            $hotel_where['a.area_id'] = $area_id;
        }

        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['hotel.hotel_box_type'] = $box_type;
            $hotel_where['a.hotel_box_type'] = $box_type;
        }else{
//            $box_types = array_keys($hotel_box_types);
//            $where['hotel.hotel_box_type'] = array('in',$box_types);
//            $hotel_where['a.hotel_box_type'] = array('in',$box_types);
        }
        $where['box.state'] = 1;
        $where['box.flag'] = 0;
        $where['a.mobile_brand'] = array('neq','devtools');
        if($is_4g){
            if($is_4g == 1){
                $where['box.is_4g'] = 1;
                $hotel_where['a.is_4g'] = 1;
            }else{
                $where['box.is_4g'] = 0;
                $hotel_where['a.is_4g'] = 0;
            }
        }
        if($maintainer_id){
            $where['hotelext.maintainer_id'] = $maintainer_id;
            $hotel_where['ext.maintainer_id'] = $maintainer_id;
        }
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $fields = 'area.region_name,hotel.id as hotel_id,hotel.name as hotel_name,count(a.id) as num,count(DISTINCT(a.box_mac)) as boxnum';
        $countfields = 'COUNT(DISTINCT(hotel.id)) AS tp_count';
        $start  = ($page-1) * $size;
        $result = $m_smallapp_forscreen_record->getCustomeList($fields,$where,$groupby='hotel.id',$order='num desc',$countfields,$start,$size);
        $datalist = $result['list'];

        $res_ohotels = array();
        if($result['total']>0 && $page*$size>=$result['total']){
            $hfields = 'hotel.id as hotel_id';
            $res_hotel = $m_smallapp_forscreen_record->getCustomeList($hfields,$where,$groupby='hotel.id',$order='',$countfields,0,10000);
            $now_hotels = array();
            foreach ($res_hotel['list'] as $v){
                $now_hotels[] = $v['hotel_id'];
            }
            $m_invalid = new \Admin\Model\ForscreenInvalidlistModel();
            $res_invalidlist = $m_invalid->getDataList('invalidid',array('type'=>1),'id desc');
            foreach ($res_invalidlist as $v){
                $now_hotels[] = $v['invalidid'];
            }

            $ahfields = 'area.region_name,a.id as hotel_id,a.name as hotel_name';
            $m_hotel = new \Admin\Model\HotelModel();
            $res_ohotels = $m_hotel->getListExt($hotel_where,'a.id desc',0,10000,$ahfields);
            foreach ($res_ohotels['list'] as $k=>$v){
                if(in_array($v['hotel_id'],$now_hotels)){
                    unset($res_ohotels['list'][$k]);
                }
            }
        }

        if($estart_time && $eend_time){
            $where['a.create_time'] = array(array('EGT',$estart_time.' 00:00:00'),array('ELT',$eend_time.' 23:59:59'));
        } else if($estart_time && empty($eend_time)){
            $eend_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$estart_time.' 00:00:00'),array('ELT',$eend_time.' 23:59:59'));
        }else if(empty($estart_time) && !empty($eend_time)){
            $estart_time = '2018-07-23';
            $where['a.create_time'] = array(array('EGT',$estart_time.' 00:00:00'),array('ELT',$eend_time.' 23:59:59'));
        }else{
            $estart_time = date('Y-m-d');
            $eend_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$estart_time.' 00:00:00'),array('ELT',$eend_time.' 23:59:59'));
        }
        $m_box = new \Admin\Model\BoxModel();
        foreach ($datalist as $k=>$v){
            //计算时间段B
            $fields = 'count(a.id) as num';
            $where['hotel.id'] = $v['hotel_id'];
            $res_info = $m_smallapp_forscreen_record->getInfo($fields,$where);
            $datalist[$k]['numb'] = $res_info['num'];

//            unset($where['a.create_time']);
//            $where['_string'] = "DATE(a.create_time)>='$start_time' AND DATE(a.create_time)<='$end_time'";
//            $fields = "DATE(a.create_time) as screen_createtime,count(DISTINCT (a.box_mac)) as boxnum";
//            $group = 'screen_createtime';
//            $res_nums = $m_smallapp_forscreen_record->getInfo($fields,$where,$group,0);
//            $a_boxnums = 0;
//            foreach ($res_nums as $vn){
//                $a_boxnums +=$vn['boxnum'];
//            }

            $b_where = array('hotel.id'=>$v['hotel_id'],'box.state'=>1,'box.flag'=>0);
            $all_box = $m_box->countNums($b_where);

            $fields = "count(DISTINCT (a.box_mac)) as boxnum";
            $res_nums = $m_smallapp_forscreen_record->getInfo($fields,$where);
            $b_boxnums = $res_nums['boxnum'];
            $datalist[$k]['b_boxnum'] = $b_boxnums;
            $datalist[$k]['b_coverage'] = sprintf("%0.2f",$b_boxnums/$all_box);

            //时间段A
            $a_boxnums = $v['boxnum'];
            $datalist[$k]['a_boxnum'] = $a_boxnums;
            $datalist[$k]['a_coverage'] = sprintf("%0.2f",$a_boxnums/$all_box);
        }

        if(!empty($res_ohotels)){
            foreach ($res_ohotels['list'] as $v){
                $fields = 'count(a.id) as num';
                $where['hotel.id'] = $v['hotel_id'];
                $res_info = $m_smallapp_forscreen_record->getInfo($fields,$where);
                $v['numb'] = $res_info['num'];
                $v['num'] = 0;
                $v['a_boxnum'] = 0;
                $v['a_coverage'] = 0.00;

                $b_where = array('hotel.id'=>$v['hotel_id'],'box.state'=>1,'box.flag'=>0);
                $all_box = $m_box->countNums($b_where);

                $fields = "count(DISTINCT (a.box_mac)) as boxnum";
                $res_nums = $m_smallapp_forscreen_record->getInfo($fields,$where);
                $b_boxnums = $res_nums['boxnum'];
                $v['b_boxnum'] = $b_boxnums;
                $v['b_coverage'] = sprintf("%0.2f",$b_boxnums/$all_box);
                $datalist[] = $v;
            }
        }

        $all_smallapps = C('all_smallapps');
        unset($all_smallapps[3]);
        $opusers = $this->getOpuser($maintainer_id);
        $this->assign('opusers', $opusers);
        $this->assign('maintainer_id', $maintainer_id);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('estart_time',$estart_time);
        $this->assign('eend_time',$eend_time);
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('small_app_id',$small_app_id);
        $this->assign('area', $area_arr);
        $this->assign('all_smallapps', $all_smallapps);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('interactdiff');
    }


    public function boxdiff(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',200);//显示每页记录数
        $hotel_id = I('hotel_id',0,'intval');
        $hotel_name = I('hotel_name','');
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $estart_time = I('estart_time','');
        $eend_time = I('eend_time','');
        $small_app_id = I('small_app_id',0,'intval');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');

        $where = array('hotel.id'=>$hotel_id);
        if($start_time && $end_time){
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        } else if($start_time && empty($end_time)){
            $end_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        }else if(empty($start_time) && !empty($end_time)){
            $start_time = '2018-07-23';
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        }else{
            $start_time = date('Y-m-d');
            $end_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$start_time.' 00:00:00'),array('ELT',$end_time.' 23:59:59'));
        }
        $where['a.is_valid'] = 1;
        $where['box.state'] = 1;
        $where['box.flag'] = 0;
        $where['a.mobile_brand'] = array('neq','devtools');
        if($small_app_id){
            if($small_app_id == 2){
                $where['a.small_app_id'] = array('in',array(2,3));
            }else{
                $where['a.small_app_id'] = $small_app_id;
            }
        }

        if($area_id){
            $where['area.id'] = $area_id;
        }
        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['hotel.hotel_box_type'] = $box_type;
        }else{
//            $box_types = array_keys($hotel_box_types);
//            $where['hotel.hotel_box_type'] = array('in',$box_types);
        }
        $box_condition = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
        if($is_4g){
            if($is_4g == 1){
                $where['box.is_4g'] = 1;
                $box_condition['box.is_4g'] = 1;
            }else{
                $where['box.is_4g'] = 0;
                $box_condition['box.is_4g'] = 0;
            }
        }
        $m_box = new \Admin\Model\BoxModel();
        $box_fields = 'hotel.id as hotel_id,hotel.name as hotel_name,box.mac as box_mac,box.name as box_name';
        $res_box = $m_box->getBoxByCondition($box_fields,$box_condition);
        $all_box = array();
        foreach ($res_box as $v){
            $all_box[$v['box_mac']] = $v;
        }

        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $fields = 'hotel.id as hotel_id,hotel.name as hotel_name,a.box_mac,box.name as box_name,count(a.id) as num';
        $countfields = 'COUNT(DISTINCT(box_mac)) AS tp_count';
        $start  = ($page-1) * $size;
        $result = $m_smallapp_forscreen_record->getCustomeList($fields,$where,$groupby='a.box_mac',$order='num desc',$countfields,$start,$size);
        $datalist = $result['list'];
        if($estart_time && $eend_time){
            $where['a.create_time'] = array(array('EGT',$estart_time.' 00:00:00'),array('ELT',$eend_time.' 23:59:59'));
        } else if($estart_time && empty($eend_time)){
            $eend_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$estart_time.' 00:00:00'),array('ELT',$eend_time.' 23:59:59'));
        }else if(empty($estart_time) && !empty($eend_time)){
            $estart_time = '2018-07-23';
            $where['a.create_time'] = array(array('EGT',$estart_time.' 00:00:00'),array('ELT',$eend_time.' 23:59:59'));
        }else{
            $estart_time = date('Y-m-d');
            $eend_time = date('Y-m-d');
            $where['a.create_time'] = array(array('EGT',$estart_time.' 00:00:00'),array('ELT',$eend_time.' 23:59:59'));
        }
        foreach ($datalist as $k=>$v){
            $fields = 'count(a.id) as num';
            $where['a.box_mac'] = $v['box_mac'];
            $res_info = $m_smallapp_forscreen_record->getInfo($fields,$where);
            $datalist[$k]['numb'] = $res_info['num'];
            if(array_key_exists($v['box_mac'],$all_box)){
                unset($all_box[$v['box_mac']]);
            }
        }

        $m_invalid = new \Admin\Model\ForscreenInvalidlistModel();
        $res_invalidlist = $m_invalid->getDataList('invalidid',array('type'=>3),'id desc');
        $invalid_box = array();
        foreach ($res_invalidlist as $v){
            $invalid_box[] = $v['invalidid'];
        }

        foreach ($all_box as $v){
            if(in_array($v['box_mac'],$invalid_box)){
                continue;
            }
            $fields = 'count(a.id) as num';
            $where['a.box_mac'] = $v['box_mac'];
            $res_info = $m_smallapp_forscreen_record->getInfo($fields,$where);
            $v['numb'] = $res_info['num'];
            $v['num'] = 0;
            $datalist[] = $v;
        }
        $this->assign('hotel_id',$hotel_id);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('estart_time',$estart_time);
        $this->assign('eend_time',$eend_time);
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('small_app_id',$small_app_id);
        $this->assign('datalist', $datalist);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('boxdiff');
    }

    public function hotel(){
        $qrcode_types = array(0=>'所有',1=>'全部打开',2=>'全部关闭',3=>'只开普通',4=>'只开极简');
        $area_id = I('area_id',0,'intval');
        $box_type = I('box_type',0,'intval');
        $is_4g = I('is_4g',0,'intval');
        $qrtype = I('qrtype',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $where = array('box.state'=>1,'box.flag'=>0);
        if($area_id){
            $where['hotel.area_id'] = $area_id;
        }
        $hotel_box_types = C('heart_hotel_box_type');
        if($box_type){
            $where['hotel.hotel_box_type'] = $box_type;
        }else{
            $box_types = array_keys($hotel_box_types);
            $where['hotel.hotel_box_type'] = array('in',$box_types);
        }
        if($is_4g){
            if($is_4g == 1){
                $where['box.is_4g'] = 1;
            }else{
                $where['box.is_4g'] = 0;
            }
        }
        if($maintainer_id){
            $where['hotelext.maintainer_id'] = $maintainer_id;
        }
        if($qrtype){
            switch ($qrtype){
                case 1:
                    $where['box.is_sapp_forscreen'] = 1;
                    $where['box.is_open_simple'] = 1;
                    break;
                case 2:
                    $where['box.is_sapp_forscreen'] = 0;
                    $where['box.is_open_simple'] = 0;
                    break;
                case 3:
                    $where['box.is_sapp_forscreen'] = 1;
                    $where['box.is_open_simple'] = 0;
                    break;
                case 4:
                    $where['box.is_sapp_forscreen'] = 0;
                    $where['box.is_open_simple'] = 1;
                    break;
            }
        }

        $fields = 'count(DISTINCT(hotel.id)) as hotelnum,count(DISTINCT(box.id)) as boxnum';
        $m_box = new \Admin\Model\BoxModel();
        $nums = $m_box->getInfoByCondition($fields,$where);
        $opusers = $this->getOpuser($maintainer_id);
        $this->assign('opusers', $opusers);
        $this->assign('area_id',$area_id);
        $this->assign('box_type',$box_type);
        $this->assign('is_4g',$is_4g);
        $this->assign('area', $area_arr);
        $this->assign('qrcode_types', $qrcode_types);
        $this->assign('qrtype', $qrtype);
        $this->assign('nums',$nums);
        $this->display();
    }

    public function sampledata(){
        $hotel_id = I('hotel_id',418,'intval');
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $sample_hotel_ids = C('SAMPLE_HOTEL');
        $hotel_ids = $sample_hotel_ids[236];

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        if(empty($start_date) || empty($end_date)){
            $end_date = date('Y-m-d',strtotime('-1 day'));
            $start_date = date('Y-m-d',strtotime("-7 day"));
        }
        $days = $m_statistics->getDates($start_date,$end_date,2);

        //在线时长
        $m_box = new \Admin\Model\BoxModel();
        $box_fields = "count(box.id) as num";
        $box_where = array('box.state'=>1,'box.flag'=>0);
        if($hotel_id==99999){
            $hotel_id = 0;
        }
        if($hotel_id){
            $box_where['hotel.id'] = $hotel_id;
        }else{
            $box_where['hotel.id'] = array('in',$hotel_ids);
        }
        $res_boxs = $m_box->getBoxByCondition($box_fields,$box_where);
        $box_mormal_num = intval($res_boxs[0]['num']);

        $online_where = array();
        $online_where['static_date'] = array('in',$days);
        if($hotel_id){
            $online_where['hotel_id'] = $hotel_id;
        }else{
            $online_where['hotel_id'] = array('in',$hotel_ids);
        }
        $online_where['heart_log_meal_nums'] = array('GT',5);
        $online_where['_string'] = 'case static_fj when 1 then (120 div heart_log_meal_nums)<10  else (180 div heart_log_meal_nums)<10 end';
        $fields = 'sum(heart_log_meal_nums) as heart_nums,static_date';
        $res_online = $m_statistics->getWhere($fields,$online_where,'','','static_date');
        $tmp_onlinetimes = array();
        foreach ($res_online as $v){
            $hotel_online_time = intval($v['heart_nums'])*5;
            $tmp_hotel_online_time = $hotel_online_time/60;
            if($hotel_online_time){
//                $hotel_online_time = sprintf("%01.1f",$tmp_hotel_online_time/$box_mormal_num);
                $hotel_online_time = sprintf("%01.1f",$tmp_hotel_online_time);
            }else{
                $hotel_online_time = 0;
            }
            $tmp_onlinetimes[$v['static_date']]=$hotel_online_time;
        }

        $fields = 'count(id) as num,static_date';
        $online_where['static_fj'] = 1;
        $res_online = $m_statistics->getWhere($fields,$online_where,'','','static_date');
        $tmp_online_lunchboxmac = array();
        foreach ($res_online as $v){
            $tmp_online_lunchboxmac[$v['static_date']]=$v['num'];
        }

        $fields = 'count(id) as num,static_date';
        $online_where['static_fj'] = 2;
        $res_online = $m_statistics->getWhere($fields,$online_where,'','','static_date');
        $tmp_online_dinnerboxmac = array();
        foreach ($res_online as $v){
            $tmp_online_dinnerboxmac[$v['static_date']]=$v['num'];
        }

        $m_qrcodelog = new \Admin\Model\Smallapp\QrcodeLogModel();

        $fields = "count(a.id) as num,DATE_FORMAT(a.create_time,'%Y%m%d') as qdate";
        $qrcode_where = array("DATE_FORMAT(a.create_time,'%Y%m%d')"=>array('in',$days));
        $qrcode_where['a.type'] = array('in',array(8,12,13,16,29,30));
        $qrcode_where['box.state'] = 1;
        $qrcode_where['box.flag'] = 0;
        if($hotel_id){
            $qrcode_where['hotel.id'] = $hotel_id;
        }else{
            $qrcode_where['hotel.id'] = array('in',$hotel_ids);
        }
        $res_qrcode = $m_qrcodelog->getScanqrcodeNum($fields,$qrcode_where,'qdate');
        $tmp_scancode_nums = array();
        foreach ($res_qrcode as $v){
            $tmp_scancode_nums[$v['qdate']] = intval($v['num']);
        }

        $forscreen_where = array("DATE_FORMAT(a.create_time,'%Y%m%d')"=>array('in',$days));
        if($hotel_id){
            $forscreen_where['hotel.id'] = $hotel_id;
        }else{
            $forscreen_where['hotel.id'] = array('in',$hotel_ids);
        }
        $forscreen_where['a.is_valid'] = 1;
        //'1普通版,2极简版,5销售端'
        $forscreen_where['box.state'] = 1;
        $forscreen_where['box.flag'] = 0;
        $forscreen_where['a.mobile_brand'] = array('neq','devtools');
        $forscreen_where['a.small_app_id'] = 1;
        $fields = "count(a.id) as fnum,DATE_FORMAT(a.create_time,'%Y%m%d') as fdate";

        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $res_forscreen = $m_smallapp_forscreen_record->getWhere($fields,$forscreen_where,'','fdate');
        $tmp_standard_forscreen_nums = array();
        foreach ($res_forscreen as $v){
            $tmp_standard_forscreen_nums[$v['fdate']] = intval($v['fnum']);
        }

        $forscreen_where['a.small_app_id'] = 2;
        $fields = "count(a.id) as fnum,DATE_FORMAT(a.create_time,'%Y%m%d') as fdate";
        $res_forscreen = $m_smallapp_forscreen_record->getWhere($fields,$forscreen_where,'','fdate');
        $tmp_mini_forscreen_nums = array();
        foreach ($res_forscreen as $v){
            $tmp_mini_forscreen_nums[$v['fdate']] = intval($v['fnum']);
        }

        $forscreen_where['a.small_app_id'] = 5;
//        $forscreen_where['a.action'] = array('neq',5);
        $fields = "count(a.id) as fnum,DATE_FORMAT(a.create_time,'%Y%m%d') as fdate";
        $res_forscreen = $m_smallapp_forscreen_record->getWhere($fields,$forscreen_where,'','fdate');
        $tmp_sale_forscreen_nums = array();
        foreach ($res_forscreen as $v){
            $tmp_sale_forscreen_nums[$v['fdate']] = intval($v['fnum']);
        }

        $m_forscreen_track = new \Admin\Model\Smallapp\ForscreenTrackModel();
        $track_where = array("DATE_FORMAT(a.add_time,'%Y%m%d')"=>array('in',$days));
        if($hotel_id){
            $track_where['hotel.id'] = $hotel_id;
        }else{
            $track_where['hotel.id'] = array('in',$hotel_ids);
        }
        $fields = "count(a.id) as fnum,DATE_FORMAT(a.add_time,'%Y%m%d') as fdate";
        $res_forscreentrack = $m_forscreen_track->getWhere($fields,$track_where,'','fdate');
        $tmp_forscreentrack = array();
        foreach ($res_forscreentrack as $v){
            $tmp_forscreentrack[$v['fdate']] = intval($v['fnum']);
        }
        $fields = "count(a.id) as fnum,DATE_FORMAT(a.add_time,'%Y%m%d') as fdate";
        $track_where['a.is_success'] = 0;
        $res_forscreentrack_fail = $m_forscreen_track->getWhere($fields,$track_where,'','fdate');
        $tmp_forscreentrackfail = array();
        foreach ($res_forscreentrack_fail as $v){
            $tmp_forscreentrackfail[$v['fdate']] = intval($v['fnum']);
        }

        $onlinetimes = array();
        $normal_boxmac = array();
        $online_lunch_boxmac = array();
        $online_dinner_boxmac = array();
        $scancode_nums = array();
        $forscreen_nums = array();
        $standard_forscreen_nums = array();
        $mini_forscreen_nums = array();
        $sale_forscreen_nums = array();
        $forscreen_rate = array();
        foreach ($days as $v){
            if(isset($tmp_onlinetimes[$v])){
                $onlinetimes[]=$tmp_onlinetimes[$v];
            }else{
                $onlinetimes[] = 0;
            }
            if(isset($tmp_online_lunchboxmac[$v])){
                $online_lunch_boxmac[]=$tmp_online_lunchboxmac[$v];
            }else{
                $online_lunch_boxmac[] = 0;
            }
            if(isset($tmp_online_dinnerboxmac[$v])){
                $online_dinner_boxmac[]=$tmp_online_dinnerboxmac[$v];
            }else{
                $online_dinner_boxmac[] = 0;
            }

            $normal_boxmac[]=$box_mormal_num;

            if(isset($tmp_scancode_nums[$v])){
                $scancode_nums[]=$tmp_scancode_nums[$v];
            }else{
                $scancode_nums[] = 0;
            }
            $standard_forscreen_num = 0;
            if(isset($tmp_standard_forscreen_nums[$v])){
                $standard_forscreen_num = $tmp_standard_forscreen_nums[$v];
            }
            $standard_forscreen_nums[] = $standard_forscreen_num;
            $mini_forscreen_num = 0;
            if(isset($tmp_mini_forscreen_nums[$v])){
                $mini_forscreen_num = $tmp_mini_forscreen_nums[$v];
            }
            $mini_forscreen_nums[] = $mini_forscreen_num;
            $sale_forscreen_num = 0;
            if(isset($tmp_sale_forscreen_nums[$v])){
                $sale_forscreen_num = $tmp_sale_forscreen_nums[$v];
            }
            $sale_forscreen_nums[] = $sale_forscreen_num;

            $forscreen_nums[] = $standard_forscreen_num+$mini_forscreen_num+$sale_forscreen_num;

            if(isset($tmp_forscreentrack[$v])){
                $all_forscreentrack = $tmp_forscreentrack[$v];
                if(isset($tmp_forscreentrackfail[$v])){
                    $success_forscreen = $all_forscreentrack - $tmp_forscreentrackfail[$v];
                    $forscreen_rate[]= sprintf("%.2f",$success_forscreen/$all_forscreentrack);
                }else{
                    $forscreen_rate[]= 1;
                }
            }else{
                $forscreen_rate[]=0;
            }
        }

        $m_hotel = new \Admin\Model\HotelModel();
        $field = 'id as hotel_id,name as hotel_name';
        $where = array('state'=>1,'flag'=>0);
        $where['id'] = array('in',$hotel_ids);
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

        $this->assign('hotel_name',$hotel_name);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('hotels',$hotels);
        $this->assign('alldays',json_encode($days));
        $this->assign('onlinetimes',json_encode($onlinetimes));
        $this->assign('online_lunch_boxmac',json_encode($online_lunch_boxmac));
        $this->assign('online_dinner_boxmac',json_encode($online_dinner_boxmac));
        $this->assign('normal_boxmac',json_encode($normal_boxmac));
        $this->assign('scancode_nums',json_encode($scancode_nums));
        $this->assign('forscreen_nums',json_encode($forscreen_nums));
        $this->assign('standard_forscreen_nums',json_encode($standard_forscreen_nums));
        $this->assign('mini_forscreen_nums',json_encode($mini_forscreen_nums));
        $this->assign('sale_forscreen_nums',json_encode($sale_forscreen_nums));
        $this->assign('forscreen_rate',json_encode($forscreen_rate));
        $this->display();
    }



    public function boxfault(){
        $m_box = new \Admin\Model\BoxModel();
        $where = array('state'=>1,'flag'=>0,'fault_status'=>2);
        $res_box = $m_box->getDataList('id,mac',$where,'id desc');
        if(!empty($res_box)){
            $all_date = array();
            $now_hour = date('G');
            if($now_hour>14){
                $all_date[]=date("Ymd");
                $all_date[]=date("Ymd", strtotime("-1 day"));
                $all_date[]=date("Ymd", strtotime("-2 day"));
            }else{
                $all_date[]=date("Ymd", strtotime("-1 day"));
                $all_date[]=date("Ymd", strtotime("-2 day"));
                $all_date[]=date("Ymd", strtotime("-3 day"));
            }
            $m_heartlog = new \Admin\Model\HeartAllLogModel();
            foreach ($res_box as $v){
                $box_id = $v['id'];
                $box_mac = $v['mac'];
                $is_ok = 1;
                foreach ($all_date as $dv){
                    $date = $dv;
                    $field = 'hour11+hour12+hour13+hour14+hour19+hour20 as totalheartnum';
                    $filter = array('date'=>$date,'mac'=>$box_mac,'type'=>2);
                    $res_heart = $m_heartlog->getAll($field,$filter,0,1,'id desc');
                    if(!empty($res_heart)){
                        $heart_num = intval($res_heart[0]['totalheartnum']);
                        if($heart_num<=0){
                            $is_ok = 0;
                        }
                    }else{
                        $is_ok = 0;
                    }
                    if($is_ok==0){
                        break;
                    }
                }
                if($is_ok){
                    $m_box->updateData(array('id'=>$box_id),array('fault_status'=>1,'fault_desc'=>''));
                }
            }
        }
        $this->output('刷新成功', 'datareport/onlinerate', 2);
    }

    public function getOpuser($op_uid=0){
        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $fields = 'a.user_id uid,user.remark ';
        $where = array('state'=>1,'role_id'=>1);
        $res_users = $m_opuser_role->getAllRole($fields,$where,'' );

        $opusers = array();
        foreach($res_users as $v){
            $uid = $v['uid'];
            $remark = $v['remark'];
            if($uid==$op_uid){
                $select = 'selected';
            }else{
                $select = '';
            }
            $firstCharter = getFirstCharter(cut_str($remark, 1));
            $opusers[$firstCharter][] = array('uid'=>$uid,'remark'=>$remark,'select'=>$select);
        }
        ksort($opusers);
        return $opusers;
    }

    public function getUsers($trainer_uid=0){
        $m_sysuser = new \Admin\Model\UserModel();
        $uwhere = 'and id!=1';
        $res_users = $m_sysuser->getUser($uwhere);

        $users = array();
        foreach($res_users as $v){
            $uid = $v['id'];
            $remark = $v['remark'];
            if($uid==$trainer_uid){
                $select = 'selected';
            }else{
                $select = '';
            }
            $users[] = array('uid'=>$uid,'remark'=>$remark,'select'=>$select);
        }
        return $users;
    }

}