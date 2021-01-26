<?php
namespace Dataexport\Controller;

class ForscreenController extends BaseController{

    public function records(){
        $s_date = I('get.s_date');
        $e_date = I('get.e_date');
        $small_app_id = I('small_app_id',0,'intval');
        $is_valid = I('is_valid',1,'intval');
        $etype = I('etype',1,'intval');//1酒楼 2版位

        $spe_action = 2;//图片滑动,视频投屏
        $all_actions = C('all_forscreen_actions');
        foreach ($all_actions as $k=>$v){
            if(intval($k)==$spe_action){
                unset($all_actions[$k]);
            }
        }
        $where = "box.flag=0 and box.state=1 and a.mobile_brand !='devtools' ";
        if($s_date){
            $where .=" and a.create_time>='".$s_date." 00:00:00'";
        }
        if($e_date){
            $where .=" and a.create_time<='".$e_date." 23:59:59'";
        }
        switch ($etype){//1酒楼 2版位
            case 1:
                $group_by = "group by hotel.id";
                $filename = 'forsacreen_hotel';
                $cell = array(
                    array('region_name','城市'),
                    array('hotel_id','酒楼id'),
                    array('hotel_name','酒楼名称'),
                    array('all_count','互动总数'),
                    array('count0','图片投屏'),
                    array('slide_count','滑动'),
                    array('video_count','视频投屏'),
                    array('count4','多图投屏'),
                    array('count5','视频点播'),
                    array('count6','广告跳转'),
                    array('count7','点击互动游戏'),
                    array('count8','重投'),
                    array('count9','手机呼大码'),
                    array('count11','发现点播图片'),
                    array('count12','发现点播视频'),
                    array('count21','查看点播视频'),
                    array('count22','查看发现视频'),
                    array('count101','h5互动游戏'),
                    array('count120','发红包'),
                    array('count121','扫码抢红包'),
                );
                break;
            case 2:
                $group_by = "group by a.box_mac";
                $filename = 'forsacreen_box';
                $cell = array(
                    array('region_name','城市'),
                    array('hotel_id','酒楼id'),
                    array('hotel_name','酒楼名称'),
                    array('room_name','包间名称'),
                    array('mac','MAC地址'),
                    array('all_count','互动总数'),
                    array('count0','图片投屏'),
                    array('slide_count','滑动'),
                    array('video_count','视频投屏'),
                    array('count4','多图投屏'),
                    array('count5','视频点播'),
                    array('count6','广告跳转'),
                    array('count7','点击互动游戏'),
                    array('count8','重投'),
                    array('count9','手机呼大码'),
                    array('count11','发现点播图片'),
                    array('count12','发现点播视频'),
                    array('count21','查看点播视频'),
                    array('count22','查看发现视频'),
                    array('count30','投屏文件'),
                    array('count31','投屏文件图片'),
                    array('count101','h5互动游戏'),
                    array('count120','发红包'),
                    array('count121','扫码抢红包'),
                );
                break;
            default:
                $group_by = "";
                $filename = "";
                $cell = array();
        }
        $sql ="SELECT area.region_name, hotel.id hotel_id,hotel.name hotel_name,room.name room_name,box.mac  FROM `savor_smallapp_forscreen_record` a
               left join savor_box box on a.box_mac=box.mac left join savor_room room on box.room_id=room.id
               left join savor_hotel hotel on room.hotel_id=hotel.id left join savor_area_info area on hotel.area_id=area.id
               where {$where}  $group_by";
        $data = M()->query($sql);
        if($is_valid!=2){
            $where .=" and a.is_valid=$is_valid";
        }
        if($small_app_id){
            if($small_app_id==2){
                $where .=" and a.small_app_id in (2,3)";
            }else{
                $where .=" and a.small_app_id=$small_app_id";
            }
        }
        foreach ($data as $k=>$v){
            switch ($etype){//1酒楼 2版位
                case 1:
                    $hotel_id = $v['hotel_id'];
                    $filter_and = "and hotel.id=$hotel_id";
                    break;
                case 2:
                    $box_mac = $v['mac'];
                    $filter_and = "and a.box_mac='$box_mac'";
                    break;
                default:
                    $filter_and = "";
            }

            $sql ="select count(a.id) as num,a.action from `savor_smallapp_forscreen_record` a
                   left join savor_box box on a.box_mac=box.mac
                   left join savor_room room on box.room_id = room.id
                   left join savor_hotel hotel on room.hotel_id= hotel.id
                   where {$where} {$filter_and} group by a.action";
            $res_actionnums = M()->query($sql);
            $all_count = 0;
            $actionnums = array();
            if(!empty($res_actionnums)){
                foreach ($res_actionnums as $k1=>$v1){
                    $all_count+=$v1['num'];
                    $actionnums[$v1['action']] = $v1['num'];
                }
            }
            $data[$k]['all_count'] = $all_count;

            foreach ($all_actions as $k2=>$v2){
               $count_name = 'count'.$k2;
               if(isset($actionnums[$k2])){
                   $data[$k][$count_name] = $actionnums[$k2];
               }else{
                   $data[$k][$count_name] = 0;
               }
            }
            $data[$k]['slide_count'] = 0;//action:2,resource_type:1
            $data[$k]['video_count'] = 0;//action:2,resource_type:2

            $sql_2 ="select count(a.id) as num,a.resource_type from `savor_smallapp_forscreen_record` a
                   left join savor_box box on a.box_mac=box.mac
                   left join savor_room room on box.room_id = room.id
                   left join savor_hotel hotel on room.hotel_id= hotel.id
                   where {$where} and a.action={$spe_action} {$filter_and} group by a.resource_type";
            $res_actioninfo = M()->query($sql_2);
            if(!empty($res_actioninfo)){
                foreach ($res_actioninfo as $v3){
                    if($v3['resource_type']==1){
                        $data[$k]['slide_count'] = $v3['num'];
                    }
                    if($v3['resource_type']==2){
                        $data[$k]['video_count'] = $v3['num'];
                    }
                }
            }
        }
        sortArrByOneField($data, 'all_count',true);
        $this->exportToExcel($cell,$data,$filename,1);
    }

    public function hotel(){
        $s_date = I('get.s_date');
        $e_date = I('get.e_date');
        $small_app_id = I('small_app_id',0,'intval');
        $is_valid = I('is_valid',1,'intval');

        $cache_key = 'cronscript:hotel'.$s_date.$e_date.$small_app_id.$is_valid;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $res = $redis->get($cache_key);
        if(!empty($res)){
            if($res == 1){
                $this->success('数据正在生成中,请稍后点击下载');
            }else{
                //下载
                $file_name = $res;
                $file_path = SITE_TP_PATH.$file_name;
                $file_size = filesize($file_path);
                header("Content-type:application/octet-tream");
                header('Content-Transfer-Encoding: binary');
                header("Content-Length:$file_size");
                header("Content-Disposition:attachment;filename=".$file_name);
                @readfile($file_path);
            }
        }else{
            $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php dataexport/forscreen/hotelscript/s_date/$s_date/e_date/$e_date/small_app_id/$small_app_id/is_valid/$is_valid > /tmp/null &";
            system($shell);
            $redis->set($cache_key,1,3600);
            $this->success('数据正在生成中,请稍后点击下载');
        }
    }

    public function hotelscript(){
        $s_date = I('s_date');
        $e_date = I('e_date');
        $small_app_id = I('small_app_id',0,'intval');
        $is_valid = I('is_valid',1,'intval');

        $spe_action = 2;//图片滑动,视频投屏
        $all_actions = C('all_forscreen_actions');
        foreach ($all_actions as $k=>$v){
            if(intval($k)==$spe_action){
                unset($all_actions[$k]);
            }
        }
        $all_boxtypes = C('hotel_box_type');
        $m_statis = new \Admin\Model\Smallapp\StatisticsModel();
        $all_dates = $m_statis->getDates($s_date,$e_date);
        if(empty($all_dates)){
            die('date error');
        }
        $end_date = end($all_dates);
        $all_data = array();

        //所有正常酒楼
        $sql ="SELECT area.region_name, hotel.id hotel_id,hotel.name hotel_name,hotel.hotel_box_type,sysuser.remark maintainer_name FROM savor_hotel hotel  left join savor_area_info area on hotel.area_id=area.id
               left join savor_hotel_ext hotelext on hotel.id=hotelext.hotel_id left join savor_sysuser sysuser on hotelext.maintainer_id=sysuser.id
               where hotel.flag=0 and state=1 order by area.id asc";
        $res_hotel = M()->query($sql);
        //鱼头泡饼酒楼 非正常
        $sql ="SELECT area.region_name, hotel.id hotel_id,hotel.name hotel_name,hotel.hotel_box_type,sysuser.remark maintainer_name FROM savor_hotel hotel  left join savor_area_info area on hotel.area_id=area.id
               left join savor_hotel_ext hotelext on hotel.id=hotelext.hotel_id left join savor_sysuser sysuser on hotelext.maintainer_id=sysuser.id
               where hotel.id in(886,867,470,464,463,461,460,435,434,433,431,243,209,208,207,206,205,204,199)";
        $nohotels = M()->query($sql);
        $hotel_data = array_merge($res_hotel,$nohotels);

        foreach ($all_dates as $date){
            $where = "box.flag=0 and box.state=1 and a.mobile_brand !='devtools' ";
            if($s_date){
                $where .=" and a.create_time>='".$date." 00:00:00'";
            }
            if($e_date){
                $where .=" and a.create_time<='".$date." 23:59:59'";
            }

            if($is_valid!=2){
                $where .=" and a.is_valid=$is_valid";
            }
            if($small_app_id){
                if($small_app_id==2){
                    $where .=" and a.small_app_id in (2,3)";
                }else{
                    $where .=" and a.small_app_id=$small_app_id";
                }
            }

            foreach ($hotel_data as $k=>$v){
                $v['date'] = date('Y/n/d',strtotime($date));
                $v['box_type'] = $all_boxtypes[$v['hotel_box_type']];

                $hotel_id = $v['hotel_id'];
                $sql ="select count(a.id) as num,a.action from `savor_smallapp_forscreen_record` a
                   left join savor_box box on a.box_mac=box.mac
                   left join savor_room room on box.room_id = room.id
                   left join savor_hotel hotel on room.hotel_id= hotel.id
                   where {$where} and hotel.id=$hotel_id group by a.action";
                $res_actionnums = M()->query($sql);
                $all_count = 0;
                $actionnums = array();
                if(!empty($res_actionnums)){
                    foreach ($res_actionnums as $k1=>$v1){
                        $all_count+=$v1['num'];
                        $actionnums[$v1['action']] = $v1['num'];
                    }
                }
                $v['all_count'] = $all_count;

                foreach ($all_actions as $k2=>$v2){
                    $count_name = 'count'.$k2;
                    if(isset($actionnums[$k2])){
                        $v[$count_name] = $actionnums[$k2];
                    }else{
                        $v[$count_name] = 0;
                    }
                }
                $v['slide_count'] = 0;//action:2,resource_type:1
                $v['video_count'] = 0;//action:2,resource_type:2

                $sql_2 ="select count(a.id) as num,a.resource_type from `savor_smallapp_forscreen_record` a
                   left join savor_box box on a.box_mac=box.mac
                   left join savor_room room on box.room_id = room.id
                   left join savor_hotel hotel on room.hotel_id= hotel.id
                   where {$where} and a.action={$spe_action} and hotel.id=$hotel_id group by a.resource_type";
                $res_actioninfo = M()->query($sql_2);
                if(!empty($res_actioninfo)){
                    foreach ($res_actioninfo as $v3){
                        if($v3['resource_type']==1){
                            $v['slide_count'] = $v3['num'];
                        }
                        if($v3['resource_type']==2){
                            $v['video_count'] = $v3['num'];
                        }
                    }
                }
                //版位数量
                $sql_box = "select count(box.id) as boxnum from savor_box as box left join savor_room as room on box.room_id=room.id where box.flag=0 and box.state=1 and room.hotel_id=$hotel_id";
                $res_box = M()->query($sql_box);
                if(!empty($res_box)){
                    $v['boxnum'] = $res_box[0]['boxnum'];
                }else{
                    $v['boxnum'] = 0;
                }
                //在线屏数
                $static_date = date('Ymd',strtotime($date));
                $sql_onlinescreen = "select count(box_mac) as zxnum from savor_smallapp_statistics where hotel_id=$hotel_id and static_date='$static_date' and heart_log_meal_nums>5 and (case static_fj when 1 then (120 div heart_log_meal_nums)<10  else (180 div heart_log_meal_nums)<10 end)";
                $res_onlinescreen = M()->query($sql_onlinescreen);
                if(!empty($res_onlinescreen)){
                    $v['onlinescreen'] = $res_onlinescreen[0]['zxnum'];
                }else{
                    $v['onlinescreen'] = 0;
                }
                //互动版位
                $sql_hd ="select count(DISTINCT(a.box_mac)) as hdnum from `savor_smallapp_forscreen_record` a
                   left join savor_box box on a.box_mac=box.mac
                   left join savor_room room on box.room_id = room.id
                   left join savor_hotel hotel on room.hotel_id= hotel.id
                   where {$where} and hotel.id=$hotel_id";
                $res_hd = M()->query($sql_hd);
                if(!empty($res_hd)){
                    $v['hdnum'] = $res_hd[0]['hdnum'];
                }else{
                    $v['hdnum'] = 0;
                }
                $all_data[] = $v;
            }
            print_r($date." ok\r\n");
        }
        $filename = 'forsacreen_hotel';
        $cell = array(
            array('date','日期'),
            array('region_name','城市'),
            array('hotel_id','酒楼id'),
            array('hotel_name','酒楼名称'),
            array('all_count','互动总数'),
            array('count0','图片投屏'),
            array('slide_count','滑动'),
            array('video_count','视频投屏'),
            array('count4','多图投屏'),
            array('count5','视频点播'),
            array('count6','广告跳转'),
            array('count7','点击互动游戏'),
            array('count8','重投'),
            array('count9','手机呼大码'),
            array('count11','发现点播图片'),
            array('count12','发现点播视频'),
            array('count21','查看点播视频'),
            array('count22','查看发现视频'),
            array('count30','投屏文件'),
            array('count31','投屏文件图片'),
            array('count101','h5互动游戏'),
            array('count120','发红包'),
            array('count121','扫码抢红包'),
            array('boxnum','版位数量'),
            array('onlinescreen','在线屏数'),
            array('hdnum','投屏互动版位'),
            array('maintainer_name','维护人'),
            array('box_type','设备类型'),
        );
        $path = $this->exportToExcel($cell,$all_data,$filename,2);
        $cache_key = 'cronscript:hotel'.$s_date.$e_date.$small_app_id.$is_valid;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $redis->set($cache_key,$path,3600);
    }


    public function statichotelmealuser(){
        $start_time = '2019-10-01 00:00:00';
        $end_time = '2020-11-30 23:59:59';
        $user_sql = "SELECT openid FROM savor_smallapp_forscreen_record 
        WHERE create_time >= '$start_time' AND create_time <= '$end_time'
        and mobile_brand!='devtools' AND is_valid = 1 AND small_app_id in(1,2) AND openid not in (
        select u.openid from (
        (select openid from savor_smallapp_user where unionId in(
        select unionId from savor_smallapp_user where openid in(select openid from savor_integral_merchant_staff group by openid) 
        and unionId!='' group by unionId
        ) and small_app_id=1) union (select invalidid as openid from savor_smallapp_forscreen_invalidlist where type=2)
        ) as u
        ) 
        group by openid";
        $model = M();
        $res_user = $model->query($user_sql);

        $meal_user = array();
        foreach ($res_user as $v){
            $openid = $v['openid'];
            $forscreen_sql = "SELECT DISTINCT hotel_id FROM savor_smallapp_forscreen_record 
            WHERE create_time >= '$start_time' AND create_time <= '$end_time' and openid='$openid' AND small_app_id in(1,2)";
            $res_forscreen_hotel = $model->query($forscreen_sql);

            foreach ($res_forscreen_hotel as $hv){
                $meal_num = 0;
                $forscreen_hotel_id = $hv['hotel_id'];

                $forscreen_sql = "SELECT DISTINCT DATE(create_time) as forscreen_date FROM savor_smallapp_forscreen_record 
            WHERE create_time >= '$start_time' AND create_time <= '$end_time' and openid='$openid' and hotel_id={$forscreen_hotel_id} AND small_app_id in(1,2)";
                $res_forscreen_date = $model->query($forscreen_sql);
                foreach ($res_forscreen_date as $dv){
                    $forscreen_date = $dv['forscreen_date'];

                    $lunch_start_time = date("$forscreen_date 10:00:00");
                    $lunch_end_time = date("$forscreen_date 14:59:59");
                    $sql_lunch = "SELECT id,box_mac,hotel_id,hotel_name,create_time FROM savor_smallapp_forscreen_record 
                WHERE create_time >= '$lunch_start_time' AND create_time <= '$lunch_end_time' and openid='$openid' and hotel_id={$forscreen_hotel_id} AND small_app_id in(1,2)";
                    $res_lunch = $model->query($sql_lunch);
                    if(!empty($res_lunch)){
                        $meal_num++;
                    }
                    $dinner_start_time = date("$forscreen_date 17:00:00");
                    $dinner_end_time = date("$forscreen_date 23:59:59");
                    $sql_lunch = "SELECT id,box_mac,hotel_id,hotel_name,create_time FROM savor_smallapp_forscreen_record 
                WHERE create_time >= '$dinner_start_time' AND create_time <= '$dinner_end_time' and openid='$openid' and hotel_id={$forscreen_hotel_id} AND small_app_id in(1,2)";
                    $res_lunch = $model->query($sql_lunch);
                    if(!empty($res_lunch)){
                        $meal_num++;
                    }
                }
                if($meal_num>=2){
                    $meal_user[$openid][] = array('hotel_id'=>$forscreen_hotel_id,'meal_num'=>$meal_num);
                    echo "openid: $openid  num: $meal_num \n";
                }
            }

        }
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $data = array();
        foreach ($meal_user as $k=>$v){
            $res_user = $m_user->getOne('nickName',array('openid'=>$k),'id desc');
            $meal_num = 0;
            foreach ($v as $mv){
                $meal_num = $mv['meal_num'] + $meal_num;
            }
            $data[] = array('openid'=>$k,'user_name'=>$res_user['nickname'],'meal_num'=>$meal_num);

        }

        $cell = array(
            array('openid','用户openid'),
            array('user_name','昵称'),
            array('meal_num','使用次数'),
        );
        $this->exportToExcel($cell,$data,'同一个酒楼使用过两次以上(两顿饭以上)的用户',2);
    }


    public function boxinteractnum(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');
        $area_id = I('area_id',0,'intval');

        $where = array();
        if($start_time && $end_time){
            $where['static_date'] = array(array('EGT',$start_time),array('ELT',$end_time));
        } else if($start_time && empty($end_time)){
            $end_time = date('Y-m-d');
            $where['static_date'] = array(array('EGT',$start_time),array('ELT',$end_time));
        }else if(empty($start_time) && !empty($end_time)){
            $start_time = '2021-01-01';
            $where['static_date'] = array(array('EGT',$start_time),array('ELT',$end_time));
        }else{
            $start_time = date('Y-m-d',strtotime('-1day'));
            $end_time = date('Y-m-d',strtotime('-1day'));
            $where['static_date'] = array(array('EGT',$start_time),array('ELT',$end_time));
        }
        if($area_id){
            $where['area_id'] = $area_id;
        }
        $m_staticboxdata = new \Admin\Model\Smallapp\StaticBoxdataModel();
        $fields = 'hotel_id,hotel_name,area_name,box_name,box_mac,user_lunch_interact_num,user_dinner_interact_num,static_date';
        $data = $m_staticboxdata->getCustomDataList($fields,$where,'hotel_id desc','');

        $cell = array(
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('area_name','城市名称'),
            array('box_name','版位名称'),
            array('box_mac','版位MAC'),
            array('user_lunch_interact_num','午饭互动量'),
            array('user_dinner_interact_num','晚饭互动量'),
            array('static_date','投屏日期'),
        );
        $this->exportToExcel($cell,$data,'版位午饭晚饭互动量统计',1);
    }

    public function boxforscreenerror(){
        $sql = "SELECT * FROM savor_smallapp_forscreen_record WHERE 
          create_time >= '2021-01-25 00:00:00' AND create_time <= '2021-01-25 23:59:59'";

        $model = M();
        $res = $model->query($sql);
        $data = array();
        $all_box_type = C('hotel_box_type');
        $error_num = array();
        $no_error_num = array();
        $fail_error_num = array();
        $fail_error_wifi_num = array();
        foreach ($res as $v){
            $sql_track = "select * FROM `savor_smallapp_forscreen_track` where forscreen_record_id={$v['id']} order by id asc ";
            $res_track = $model->query($sql_track);
            if(!empty($res_track) && $res_track[0]['is_success']==0){
                $error_num[]=$v['id'];
                $track_info = $res_track[0];

                if($track_info['position_nettystime']>0 && $track_info['position_nettystime']>0 && $track_info['request_nettytime']>0 && $track_info['netty_receive_time']>0
                    && $track_info['netty_pushbox_time']>0 && $track_info['box_receivetime']>0 && $track_info['box_downstime']>0 && $track_info['box_downetime']>0) {
                    $netty_result = json_decode($track_info['netty_result'], true);
                    if ($netty_result['code'] == 10000) {
                        $no_error_num[] = $v['id'];
                    }else{
                        $fail_error_num[]=$v['id'];
                        if($v['is_4g']==0){
                            $fail_error_wifi_num[$v['box_mac']][]=$v['id'];
                        }
                    }
//                $info = array('hotel_name'=>$v['hotel_name'],'area_name'=>$v['area_name'],
//                    'box_name'=>$v['box_name'],'box_mac'=>$v['box_mac']);
//                if($v['is_4g']==1){
//                    $info['is_4gstr'] = '是';
//                }else{
//                    $info['is_4gstr'] = '否';
//                }
//                $info['box_type_str'] = $all_box_type[$v['box_type']];
//                $data[$v['box_mac']] = $info;
                }else{
                    $fail_error_num[]=$v['id'];
                    if($v['is_4g']==0){
//                        $fail_error_wifi_num[]=$v['id'];
                        $fail_error_wifi_num[$v['box_mac']][]=$v['id'];
                    }
                }
            }
        }
        echo count($error_num);
        echo '====';
        echo count($no_error_num);
        echo '====';
        echo count($fail_error_wifi_num);
        echo '====';
        print_r($fail_error_wifi_num);
        exit;
//        $data = array_values($data);
//        $cell = array(
//            array('hotel_name','酒楼名称'),
//            array('area_name','城市名称'),
//            array('box_name','版位名称'),
//            array('box_mac','版位MAC'),
//            array('is_4gstr','是否4G'),
//            array('box_type_str','版位类型')
//        );
//        $this->exportToExcel($cell,$data,'投屏错误版位统计',1);
    }


}