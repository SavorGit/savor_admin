<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序数据统计-设备概况
 *
 */
class DeviceoverviewController extends BaseController {

    public function index(){
        $area_id = I('get.area_id',0,'intval');
        $start_date = date('Ymd',strtotime('-31days'));
        $end_date = date('Ymd',strtotime('-1days'));

        $start_date = 20190301;
        $end_date = 20190331;
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $date = $m_statistics->getDates($start_date,$end_date,2);
        $m_area = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        $all_area = array();
        foreach ($res_area as $v){
            $all_area[$v['id']] = $v['region_name'];
        }

        $m_box = new \Admin\Model\BoxModel();
        $box_types = C('hotel_box_type');
        $fields = "count(box.id) as num";
        $where = array();
        $where['hotel.hotel_box_type'] = array('in',array_keys($box_types));
        $where['hotel.state'] = 1;
        $where['hotel.flag'] = 0;
        if($area_id){
            $where['hotel.area_id'] = $area_id;
        }
        $where['room.state'] = 1;
        $where['room.flag'] = 0;
        $where['box.state'] = 1;
        $where['box.flag'] = 0;
        $res_box = $m_box->getBoxByCondition($fields,$where);
        $all_boxnum = $res_box[0]['num'];

        $box_online = $this->box_online($date,$area_id);
        $screen_online = $this->screen_online($date,$area_id);
        $boot_time = $this->boot_time($date,$area_id,$all_area);
        $network = $this->network($date,$area_id,$box_types);
        $box_apk = $this->box_apk($area_id);
        $device_type = $this->device_type($area_id,$all_area);

        $this->assign('network_legendx',json_encode(array_keys($network)));
        $this->assign('network',json_encode(array_values($network)));
        $this->assign('devicetype_legendx',json_encode(array_values($box_types)));
        $this->assign('devicetype',json_encode($device_type));
        $this->assign('boxapk',json_encode(array_values($box_apk)));
        $this->assign('boxapk_legendx',json_encode(array_keys($box_apk)));
        $this->assign('boxapk',json_encode(array_values($box_apk)));
        $this->assign('boottime_legendx',json_encode(array_keys($boot_time)));
        $this->assign('boottime',json_encode(array_values($boot_time)));
        $this->assign('screenonline',json_encode($screen_online));
        $this->assign('boxonline_lunch',json_encode($box_online['lunch']));
        $this->assign('boxonline_dinner',json_encode($box_online['dinner']));
        $this->assign('boxonline_screen',json_encode($box_online['screen']));
        $this->assign('boxonline_rate',json_encode($box_online['rate']));
        $this->assign('alldate',json_encode($date));
        $this->assign('networkscreen',$box_online['screen'][0]);
        $this->assign('all_boxnum',$all_boxnum);
        $this->assign('all_area',$all_area);
        $this->assign('area_id',$area_id);
        $this->assign('s_date',date('Y-m-d',strtotime($start_date)));
        $this->assign('e_date',date('Y-m-d',strtotime($end_date)));
        $this->display();
    }

    public function networkconfig(){
        $type = 41;
        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        $res_config = $m_static_config->getOne('id,conf_data',array('type'=>$type));
        if(IS_POST){
            $min = I('post.min');
            $max = I('post.max');
            $data = array();
            foreach ($min as $k=>$v){
                $data[] = array('min'=>$v,'max'=>$max[$k]);
            }
            $config_data = json_encode($data);
            $add_data = array('conf_data'=>$config_data,'type'=>$type);
            if(!empty($res_config)){
                $m_static_config->updateInfo(array('id'=>$res_config['id']),$add_data);
            }else{
                $m_static_config->add($add_data);
            }
            $this->output('更新成功!', 'deviceoverview/index');
        }else{
            $data = array();
            if(!empty($res_config)){
                $data = json_decode($res_config['conf_data'],true);
            }
            $this->assign('data',$data);
            $this->display();
        }

    }

    public function networklist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $area_id = I('area_id',0,'intval');
        $start_time = I('s_date','');
        $end_time = I('e_date','');
        $speed = I('speed','');
        $tv_code = I('tvcode',0,'intval');
        $hotel_name = I('hotel_name','','trim');

        if(empty($start_date)){
            $start_date = date('Ymd',strtotime('-31days'));
        }
        if(empty($end_date)){
            $end_date = date('Ymd',strtotime('-1days'));
        }

        if($start_date){
            $start_date = date('Ymd',strtotime($start_time));
        }
        if($end_date){
            $end_date = date('Ymd',strtotime($end_time));
        }

        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        $res_config = $m_static_config->getOne('id,conf_data',array('type'=>41));
        $config_data = json_decode($res_config['conf_data'],true);
        $all_speeds = array();
        foreach ($config_data as $v){
            $netkey = $v['min'].'-'.$v['max'];
            if($speed==$netkey){
                $selected = 'selected';
            }else{
                $selected = '';
            }
            $all_speeds[] = array('name'=>$netkey.'k/s','value'=>$netkey,'selected'=>$selected);
        }

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $date = $m_statistics->getDates($start_date,$end_date,2);
        $all_box_speed = $this->network_cache($area_id,$date);
        $start = ($page-1)*$size>0?($page-1) * $size:0;
        $res_data = $m_statistics->getBoxNetworkList($all_box_speed,$speed,$tv_code,$hotel_name,$start,$size);
        $alltv_code = array(
            1=>'标准版小程序码',
            2=>'极简版小程序码',
            3=>'标准版+极简版小程序码',
            4=>'标准版二维码',
            5=>'极简版二维码',
            6=>'标准版+极简版二维码',
        );
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('datalist', $res_data['list']);
        $this->assign('page',  $res_data['page']);
        $this->assign('all_speeds',$all_speeds);
        $this->assign('alltv_code',$alltv_code);
        $this->assign('tv_code',$tv_code);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('s_date',$start_time);
        $this->assign('e_date',$end_time);
        $this->display('networklist');
    }

    public function networkedit(){
        $box_id = I('box_id',0,'intval');
        $m_box = new \Admin\Model\BoxModel();
        if(IS_GET){
            $vinfo = $m_box->getInfo(array('id'=>$box_id));
            $this->assign('vinfo',$vinfo);
            $this->display();
        }else{
            $qrcode_type = I('post.qrcode_type',0,'intval');
            $is_sapp_forscreen = I('post.is_sapp_forscreen',0,'intval');
            $is_open_simple = I('post.is_open_simple',0,'intval');
            $data = array('qrcode_type'=>$qrcode_type,'is_sapp_forscreen'=>$is_sapp_forscreen,'is_open_simple'=>$is_open_simple);
            $res = $m_box->updateData(array('id'=>$box_id),$data);
            if($res){
                $redis = \Common\Lib\SavorRedis::getInstance();
                $redis->select(15);
                $cache_key =  C('DB_PREFIX').'box_'.$box_id;
                $vinfo = $m_box->getInfo(array('id'=>$box_id));
                $redis->set($cache_key,json_encode($vinfo));
                $this->output('操作成功!', 'deviceoverview/networklist');
            }else{
                $this->output('操作失败', 'deviceoverview/networklist',2,0);
            }
        }
    }


    private function network($date,$area_id,$box_types){
        $all_box_speed = $this->network_cache($area_id,$date);
        $type = 41;
        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        $res_config = $m_static_config->getOne('id,conf_data',array('type'=>$type));
        $config_data = json_decode($res_config['conf_data'],true);
        $all_data = array();
        foreach ($config_data as $k=>$v){
            $netkey = $v['min'].'-'.$v['max'].'k/s';
            $formatter = "{a} <br/>{b} : {c} ({d}%)<br/>";
            $info = array('value'=>0,'name'=>$netkey);
            if(!empty($all_box_speed)){
                $boxtype_data = array();
                foreach ($all_box_speed as $vv){
                    if($vv['avg_speed']>=$v['min'] && $vv['avg_speed']<=$v['max']){
                        $boxtype_data[$vv['hotel_box_type']][]=$vv['box_mac'];
                    }
                }
                foreach ($boxtype_data as $bk=>$bv) {
                    $boxtype_name = $box_types[$bk];
                    $boxtype_num = count($bv);
                    $info['value']+=$boxtype_num;
                    $formatter .= "$boxtype_name:{$boxtype_num} ";
                }
            }
            $info['tooltip'] = array('formatter'=>$formatter);
            $all_data[$netkey] = $info;
        }
        return $all_data;
    }


    private function box_online($date,$area_id){
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $static_fj = 1;//饭局1:午饭2:晚饭
        $type = 6;//6在线屏幕数
        $res_lunch = $m_statistics->getRatenum($date,$static_fj,$type,0,'',$area_id);

        $static_fj = 2;
        $res_dinner = $m_statistics->getRatenum($date,$static_fj,$type,0,'',$area_id);

        $m_box = new \Admin\Model\BoxModel();
        $box_types = C('heart_hotel_box_type');
        $fields = "count(box.id) as num";
        $where = array();
        $where['hotel.hotel_box_type'] = array('in',array_keys($box_types));
        $where['hotel.state'] = 1;
        $where['hotel.flag'] = 0;
        if($area_id){
            $where['hotel.area_id'] = $area_id;
        }
        $where['room.state'] = 1;
        $where['room.flag'] = 0;
        $where['box.state'] = 1;
        $where['box.flag'] = 0;
        $res_box = $m_box->getBoxByCondition($fields,$where);
        $box_num = $res_box[0]['num'];
        $lunch = array();
        $dinner = array();
        $screen = array();
        $rate = array();
        foreach ($res_lunch['zxnum'] as $k=>$v){
            $lunch[] = $v['zxnum'];
            $dinner[] = $res_dinner['zxnum'][$k]['zxnum'];
            $screen[] = $box_num;
            $rate[] = sprintf("%.2f",($v['zxnum']+$res_dinner['zxnum'][$k]['zxnum'])/$box_num*2);
        }
        $res = array('lunch'=>$lunch,'dinner'=>$dinner,'screen'=>$screen,'rate'=>$rate);
        return $res;
    }

    private function screen_online($date,$area_id){
        $m_heartalllog = new \Admin\Model\HeartAllLogModel();
        $where = array('type'=>2);
        $where['date'] = array('in',$date);
        if($area_id){
            $where['area_id'] = $area_id;
        }
        $res = array();
        for($i=1;$i<25;$i++){
            if($i==24){
                $hour_time = "hour0";
            }else{
                $hour_time = "hour$i";
            }
            $where[$hour_time] = array('gt',0);
            $count = $m_heartalllog->getCount($where);
            $res[] = round($count/30);
        }
        return $res;
    }

    private function boot_time($date,$area_id,$all_area){
        $data = array();
        $m_heartalllog = new \Admin\Model\HeartAllLogModel();

        $allhour = "sum(hour0+hour1+hour2+hour3+hour4+hour5+hour6+hour7+hour8+hour9+hour10+hour11+hour12+hour13+hour14+hour15+hour16+hour17+hour18+hour19+hour20+hour21+hour22+hour23) as totalhour";
        $data_num = count($date);
        $sub_field = "*,$allhour";
        $where = array('type'=>2);
        $where['date'] = array('in',$date);
        if($area_id){
            $where['area_id'] = $area_id;
        }

        $subQuery = $m_heartalllog->field($sub_field)->table('savor_heart_all_log')->where($where)->group('mac')->buildSql();

        $field='COUNT(a.id) AS tp_count,a.area_id';
        $group = 'a.area_id';
        $where_avg = "round(a.totalhour/$data_num,0)";

        $hour0 = 0;
        $where_string = "a.totalhour=$hour0";
        $res = $m_heartalllog->field($field)->table($subQuery.' a')->where($where_string)->group($group)->select();
        $data['0h'] = $res;

        $hour1 = 1*12;
        $where_string = "$where_avg>$hour0 and $where_avg<=$hour1";
        $res = $m_heartalllog->field($field)->table($subQuery.' a')->where($where_string)->group($group)->select();
        $data['0-1h'] = $res;

        $hour2 = 2*12;
        $where_string = "$where_avg>$hour1 and $where_avg<=$hour2";
        $res = $m_heartalllog->field($field)->table($subQuery.' a')->where($where_string)->group($group)->select();
        $data['1-2h'] = $res;

        $hour3 = 3*12;
        $where_string = "$where_avg>$hour2 and $where_avg<=$hour3";
        $res = $m_heartalllog->field($field)->table($subQuery.' a')->where($where_string)->group($group)->select();
        $data['2-3h'] = $res;

        $hour4 = 4*12;
        $where_string = "$where_avg>$hour3 and $where_avg<=$hour4";
        $res = $m_heartalllog->field($field)->table($subQuery.' a')->where($where_string)->group($group)->select();
        $data['3-4h'] = $res;

        $hour5 = 5*12;
        $where_string = "$where_avg>$hour4 and $where_avg<=$hour5";
        $res = $m_heartalllog->field($field)->table($subQuery.' a')->where($where_string)->group($group)->select();
        $data['4-5h'] = $res;

        $hour6 = 6*12;
        $where_string = "$where_avg>$hour5 and $where_avg<=$hour6";
        $res = $m_heartalllog->field($field)->table($subQuery.' a')->where($where_string)->group($group)->select();
        $data['5-6h'] = $res;

        $where_string = "$where_avg>$hour6";
        $res = $m_heartalllog->field($field)->table($subQuery.' a')->where($where_string)->group($group)->select();
        $data['6h以上'] = $res;

        $boot_time = array();
        foreach ($data as $k=>$v){
            $formatter = "{a} <br/>{b} : {c} ({d}%)<br/>";
            $info = array('value'=>0,'name'=>$k);
            if(!empty($v)){
                foreach ($v as $vv){
                    $area_count = $vv['tp_count'];
                    $area_name = $all_area[$vv['area_id']];
                    $info['value']+=$area_count;
                    $formatter.="$area_name:{$area_count} ";
                }
            }
            $info['tooltip'] = array('formatter'=>$formatter);
            $boot_time[$k] = $info;
        }
        return $boot_time;
    }

    private function box_apk($area_id){
        $fields = 'count(*) as num,apk_version';
        $where = array('type'=>2);
        if($area_id){
            $where['area_id'] = $area_id;
        }
        $group = 'apk_version';
        $m_heartlog = new \Admin\Model\HeartLogModel();
        $res_log = $m_heartlog->getHotelHeartBox($where,$fields,$group);
        $all_apk = array();
        foreach ($res_log as $v){
            $all_apk[$v['apk_version']] = array('value'=>$v['num'],'name'=>$v['apk_version']);
        }
        return $all_apk;
    }

    private function device_type($area_id,$all_area){
        $m_box = new \Admin\Model\BoxModel();
        $box_types = C('hotel_box_type');

        $all_device = array();
        foreach ($box_types as $k=>$v){
            $fields = "count(box.id) as num,hotel.area_id";
            $where = array();
            $where['hotel.hotel_box_type'] = $k;
            $where['hotel.state'] = 1;
            $where['hotel.flag'] = 0;
            if($area_id){
                $where['hotel.area_id'] = $area_id;
            }
            $where['room.state'] = 1;
            $where['room.flag'] = 0;
            $where['box.state'] = 1;
            $where['box.flag'] = 0;
            $group = 'hotel.area_id';
            $res_box = $m_box->getBoxByCondition($fields,$where,$group);

            $formatter = "{a} <br/>{b} : {c} ({d}%)<br/>";
            $info = array('value'=>0,'name'=>$v);
            if(!empty($res_box)){
                foreach ($res_box as $vv){
                    $area_count = $vv['num'];
                    $area_name = $all_area[$vv['area_id']];
                    $info['value']+=$area_count;
                    $formatter.="$area_name:{$area_count} ";
                }
            }
            $info['tooltip'] = array('formatter'=>$formatter);
            $all_device[] = $info;
        }
        return $all_device;
    }

    public function network_cache($area_id,$date){
        $s_date = $date[0];
        $e_date = end($date);
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $cache_key = C('STATS_CACHE_PRE').$area_id.$s_date.$e_date;
        $res_data = $redis->get($cache_key);
        if(empty($res_data)){
            $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
            $fields = "hotel_name,box_id,hotel_box_type,box_mac,sum(avg_down_speed) as total_avg_down_speed";
            $where = array('static_date'=>array('in',$date));
            if($area_id){
                $where['area_id'] = $area_id;
            }
            $res_sumspeed = $m_statistics->getWhere($fields,$where,'','','box_mac');

            $sub_field = "static_date,box_mac";
            $where['avg_down_speed'] = array('gt',0);
            $subQuery = $m_statistics->field($sub_field)->table('savor_smallapp_statistics')->where($where)->group('')->buildSql();

            $field = "a.box_mac,count(a.box_mac) as boxnum";
            $res_num = $m_statistics->field($field)->table($subQuery.' a')->where('')->group('a.box_mac')->select();
            $all_boxnum = array();
            foreach ($res_num as $v){
                $all_boxnum[$v['box_mac']] = $v['boxnum'];
            }

            $all_box_speed = array();
            foreach ($res_sumspeed as $v){
                $box_mac = $v['box_mac'];
                $total_avg_down_speed = $v['total_avg_down_speed'];
                $now_boxnum = isset($all_boxnum[$box_mac])?intval($all_boxnum[$box_mac]):0;
                if($total_avg_down_speed>0 && $now_boxnum){
                    $avg_speed = round($total_avg_down_speed/($now_boxnum*1024), 2);
                }else{
                    $avg_speed = 0;
                }
                $v['avg_speed'] = $avg_speed;
                unset($v['total_avg_down_speed']);
                $all_box_speed[] = $v;
            }
            if(!empty($all_box_speed)){
                $redis->set($cache_key,json_encode($all_box_speed),86400);
            }
        }else{
            $all_box_speed = json_decode($res_data,true);
        }
        return $all_box_speed;
    }
}