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
        $m_hotelext = new \Admin\Model\HotelExtModel();
        $hotel_maintainers = array();
        foreach ($data as $k=>$v){
            $hotel_id = $v['hotel_id'];
            if(!isset($hotel_maintainers[$hotel_id])){
                $res_main = $m_hotelext->getHotelMaintainer('user.id as user_id,user.remark',array('a.hotel_id'=>$hotel_id));
                $hotel_maintainers[$hotel_id]= array('user_id'=>$res_main[0]['user_id'],'username'=>$res_main[0]['remark']);
            }
        }
        foreach ($data as $k=>$v){
            $hotel_id = $v['hotel_id'];
            if(isset($hotel_maintainers[$hotel_id])){
                $data[$k]['maintainer'] = $hotel_maintainers[$hotel_id]['username'];
            }
        }
        $cell = array(
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('area_name','城市名称'),
            array('box_name','版位名称'),
            array('box_mac','版位MAC'),
            array('user_lunch_interact_num','午饭互动量'),
            array('user_dinner_interact_num','晚饭互动量'),
            array('maintainer','维护人'),
            array('static_date','投屏日期'),
        );
        $this->exportToExcel($cell,$data,'版位午饭晚饭互动量统计',1);
    }

    public function userdata(){
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        $where = array('a.static_date'=>array(array('EGT',$start_time),array('ELT',$end_time)));
        $m_userdata = new \Admin\Model\Smallapp\StaticUserdataModel();
        $fields = 'a.openid,a.static_date,sum(a.box_num) as box_num,sum(a.meal_num) as meal_num,
        count(DISTINCT a.hotel_id) as hotel_num,GROUP_CONCAT(DISTINCT hotel_name) as hotel_names,
        user.avatarUrl,user.nickName';
        $order = 'hotel_num desc';
        $groupby = 'a.openid';
        $data = $m_userdata->getCustomeList($fields,$where,$groupby,$order,'',0,0);

        $cell = array(
            array('openid','openid'),
            array('nickname','用户昵称'),
            array('box_num','投屏版位数'),
            array('meal_num','投屏饭局数'),
            array('hotel_num','投屏酒楼数'),
        );
        $this->exportToExcel($cell,$data,'用户投屏统计',1);
    }

    public function taguser(){
        $start_date = I('start_time','');
        $end_date = I('end_time','');
        $type = I('type',0,'intval');//1重度 2多餐厅 3销售用户
        if($start_date<'2020-10-01'){
            $start_date = '2020-10-01';
        }
        /*
        $start_date = '2020-10-01';
        $end_date = '2021-02-28';
        $type = 3;
        $personattr_id = 115;//112重度 113多餐厅 115销售
        */

        $start_time = date('Y-m-d 00:00:00',strtotime($start_date));
        $end_time = date('Y-m-d 23:59:59',strtotime($end_date));
        if(in_array($type,array(1,2,3))){
            if(in_array($type,array(1,2))){
                $static_sdate = date('Y-m-d',strtotime($start_time));
                $static_edate = date('Y-m-d',strtotime($end_time));
                $where = array('a.static_date'=>array(array('EGT',$static_sdate),array('ELT',$static_edate)));
                $m_userdata = new \Admin\Model\Smallapp\StaticUserdataModel();
                $fields = 'a.openid,a.static_date,sum(a.box_num) as box_num,sum(a.meal_num) as meal_num,
                count(DISTINCT a.hotel_id) as hotel_num,GROUP_CONCAT(DISTINCT hotel_name) as hotel_names,
                user.avatarUrl,user.nickName';
                $order = 'hotel_num desc';
                $groupby = 'a.openid';
                $data = $m_userdata->getCustomeList($fields,$where,$groupby,$order,'',0,0);
                if($type==1){
                    $file_name = '重度用户统计';
                }else{
                    $file_name = '多餐厅用户统计';
                }
                $resp_data = array();
                foreach ($data as $v){
                    if($type==1 && $v['meal_num']>=2){
                        $resp_data[]=$v;
                    }elseif($type==2 && $v['hotel_num']>=2){
                        $resp_data[]=$v;
                    }
                }
            }else{
                $model = M();
                $time_condition = "a.create_time>='{$start_time}' and a.create_time<='{$end_time}'";
                $sql = "select a.openid,user.avatarUrl,user.nickName from savor_smallapp_forscreen_record as a left join savor_smallapp_user as user on a.openid=user.openid where {$time_condition} 
                  and a.small_app_id in(1,2) and a.action in(30,31,32) group by a.openid";
                $resp_data = $model->query($sql);
                $file_name = '销售用户统计';
            }
            /*
            $model = M();
            $time_condition = "create_time>='{$start_time}' and create_time<='{$end_time}' and small_app_id in(1,2)";
            foreach ($resp_data as $v){
                $openid = $v['openid'];
                $cron_sql = "select id,personattr_id from savor_smallapp_forscreen_record where {$time_condition} and openid='{$openid}'";
                if($type==3){
                    $cron_sql.=" and action in(30,31,32)";
                }
                $res_udata = $model->query($cron_sql);
                if(!empty($res_udata)){
                    echo "openid: $openid bengin \r\n";
                    foreach ($res_udata as $uv){
                        if(!empty($uv['personattr_id'])){
                            $now_personattr_id = $uv['personattr_id'].','.$personattr_id;
                        }else{
                            $now_personattr_id = $personattr_id;
                        }
                        $sql_up = "UPDATE savor_smallapp_forscreen_record SET personattr_id='{$now_personattr_id}' WHERE id={$uv['id']}";
                        $res = $model->execute($sql_up);
                        if($res){
                            echo "openid: $openid-{$uv['id']} upok \r\n";
                        }
                    }
                }
                echo "openid: $openid ok \r\n";
            }
            */
            $cell = array(
                array('openid','openid'),
                array('nickname','用户昵称'),
            );
            $this->exportToExcel($cell,$resp_data,$file_name,1);
        }

    }

    public function boxforscreen(){
        ini_set("memory_limit","2048M");
        $sql = "SELECT * FROM `savor_smallapp_forscreen_record` where create_time>='2021-01-25 00:00:00' and create_time<='2021-01-30 23:59:59' 
        and (action in(4,30,31) or (action=2 and resource_type=2))";
        $model = M();
        $res = $model->query($sql);
        $data = array();
        $all_box_type = C('hotel_box_type');
        $all_actions = array(
            '2-2'=>'视频',
            '4'=>'图片',
            '30'=>'文件',
            '31'=>'文件图片',
        );
        $all_forscreen_status = array('0'=>'失败','1'=>'成功','2'=>'打断','3'=>'退出');
        foreach ($res as $v){
            $aciton = $v['action'];
            if($aciton==2){
                $aciton = $v['action'].'-'.$v['resource_type'];
            }
            $sql_track = "select * FROM `savor_smallapp_forscreen_track` where forscreen_record_id={$v['id']} order by id asc limit 1";
            $res_track = $model->query($sql_track);
            if(!empty($res_track)){
                $track_info = $res_track[0];
                $box_mac = $v['box_mac'];

                $info = array('area_name'=>$v['area_name'],'hotel_name'=>$v['hotel_name'],'box_name'=>$v['box_name'],'box_mac'=>$v['box_mac']);
                if($v['is_4g']==1){
                    $info['is_4gstr'] = '4G';
                }else{
                    $info['is_4gstr'] = 'wifi';
                }
                $info['box_type_str'] = $all_box_type[$v['box_type']];
                if(!empty($v['resource_size'])){
                    $info['resource_size'] = formatBytes($v['resource_size']);
                }else {
                    $info['resource_size'] = '';
                }
                $info['action_str'] = $all_actions[$aciton];
                $info['success_str'] = $all_forscreen_status[$track_info['is_success']];
                $info['oss_time'] = '';
                if(!empty($track_info['oss_stime']) && !empty($track_info['oss_etime'])){
                    $info['oss_time'] = ($track_info['oss_etime'] - $track_info['oss_stime']) /1000 ;
                }
                $info['total_time'] = $track_info['total_time'];
                $sql_12 = "SELECT count(*) as num FROM `savor_smallapp_forscreen_record` where create_time>='2020-12-01 00:00:00' and create_time<='2020-12-31 23:59:59' 
                and box_mac='{$box_mac}'";
                $num12 = 0;
                $res_nums = $model->query($sql_12);
                if(!empty($res_nums)){
                    $num12 = intval($res_nums[0]['num']);
                }
                $info['num12'] = $num12;
                $sql_11 = "SELECT count(*) as num FROM `savor_smallapp_forscreen_record` where create_time>='2020-11-01 00:00:00' and create_time<='2020-11-31 23:59:59' 
                and box_mac='{$box_mac}'";
                $num11 = 0;
                $res_nums = $model->query($sql_11);
                if(!empty($res_nums)){
                    $num11 = intval($res_nums[0]['num']);
                }
                $info['num11'] = $num11;
                $data[] = $info;
            }
        }
        $cell = array(
            array('area_name','地区'),
            array('hotel_name','酒楼名称'),
            array('box_name','版位名称'),
            array('box_mac','版位MAC'),
            array('is_4gstr','版位属性'),
            array('box_type_str','版位类型'),
            array('action_str','投屏资源类型'),
            array('resource_size','文件大小'),
            array('success_str','是否失败'),
            array('oss_time','上传用时'),
            array('total_time','总体用时'),
            array('num12','12月互动数'),
            array('num11','11月互动数'),
        );
        $this->exportToExcel($cell,$data,'投屏数据统计',2);
    }

    public function forscreenbox(){
        $file_path = SITE_TP_PATH.'/Public/content/副本实验组不可投屏投屏版位.xlsx';
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");

        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $m_smallapp_boxdata = new \Admin\Model\Smallapp\StaticBoxdataModel();
        $data = array();
        for ($row = 2; $row <= $highestRow; $row++){
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            if(!empty($rowData[0][0])){
                $row_info = $rowData[0];
                $info = array('id'=>$row_info[0],'status'=>$row_info[1],'box_mac'=>$row_info[2],
                    'box_name'=>$row_info[3],'tv_status'=>$row_info[4],'hotel_name'=>$row_info[5],
                    'tv_type'=>$row_info[6]
                    );

                $fields = 'count(a.id) as num';
                $start_time = '2021-01-01 00:00:00';
                $end_time = '2021-01-31 23:59:59';
                $where = array('a.create_time'=>array(array('EGT',$start_time),array('ELT',$end_time)),'a.box_mac'=>$info['box_mac']);
                $where['a.small_app_id'] = array('in',array(1,2,11));
                $where['a.is_valid'] = 1;
                $where['a.mobile_brand'] = array('neq','devtools');
                $res_forscreen = $m_smallapp_forscreen_record->getDatas($fields,$where,'','');
                $hd_num = 0;
                if(!empty($res_forscreen)){
                    $hd_num = intval($res_forscreen[0]['num']);
                }
                $fj_lunch_num = $fj_dinner_num = 0;
                $fj_fields = 'count(id) as num';
                $start_date = '2021-01-01';
                $end_date = '2021-01-31';
                $fj_lunch_where = array('box_mac'=>$info['box_mac'],'static_date'=>array(array('EGT',$start_date),array('ELT',$end_date)),
                    'user_lunch_interact_num'=>array('gt',0));
                $res_lunchfj = $m_smallapp_boxdata->getDataList($fj_fields,$fj_lunch_where,'id desc');
                if(!empty($res_lunchfj)){
                    $fj_lunch_num = intval($res_lunchfj[0]['num']);
                }

                $fj_dinner_where = array('box_mac'=>$info['box_mac'],'static_date'=>array(array('EGT',$start_date),array('ELT',$end_date)),
                    'user_dinner_interact_num'=>array('gt',0));
                $res_dinnerfj = $m_smallapp_boxdata->getDataList($fj_fields,$fj_dinner_where,'id desc');
                if(!empty($res_dinnerfj)){
                    $fj_dinner_num = intval($res_dinnerfj[0]['num']);
                }
                $fj_num = $fj_lunch_num + $fj_dinner_num;
                $info['hd_num'] = $hd_num;
                $info['fj_num'] = $fj_num;
                $data[]=$info;
            }
        }

        $cell = array(
            array('id','ID'),
            array('status','状态'),
            array('box_mac','mac地址'),
            array('box_name','版位名称'),
            array('tv_status','电视状态'),
            array('hotel_name','酒楼名称'),
            array('tv_type','屏幕类型'),
            array('hd_num','1月互动数'),
            array('fj_num','1月互动饭局数'),
        );
        $this->exportToExcel($cell,$data,'副本实验组不可投屏投屏版位',1);

    }

    public function boxforscreennum(){
        ini_set("memory_limit","2048M");
        $start_time = '2020-08-21 00:00:00';
        $end_time = '2021-02-21 23:59:59';

        $all_hotel_types = C('heart_hotel_box_type');
        $m_hotel = new \Admin\Model\HotelModel();
        $field = 'a.id as hotel_id,a.name as hotel_name,area.id as area_id,area.region_name as area_name,a.hotel_box_type,a.level as hotel_level,
	    a.is_4g,ext.trainer_id,ext.train_date,ext.maintainer_id,a.tech_maintainer';
        $where = array('a.state'=>1,'a.flag'=>0,'a.type'=>1);
        $where['a.hotel_box_type'] = array('in',array_keys($all_hotel_types));
        $res_hotel = $m_hotel->getHotels($field,$where);
        $model = M();
        $data = array();
        foreach ($res_hotel as $v){
            $hotel_id = $v['hotel_id'];
            if(in_array($hotel_id,array(7,883))){
                continue;
            }
            $hotel_name = $v['hotel_name'];
            $area_name = $v['area_name'];
            $maintainer_id = $v['maintainer_id'];

            $sql_maintainer = "select remark as uname from savor_sysuser where id={$maintainer_id}";
            $res_maintainer = $model->query($sql_maintainer);
            $maintainer_name = '';
            if(!empty($res_maintainer)){
                $maintainer_name = $res_maintainer[0]['uname'];
            }
            $sql_box = "select box_mac,box_name,count(*) as num,count(DISTINCT DATE(create_time)) as date_num from savor_smallapp_forscreen_record where hotel_id={$hotel_id} and create_time>='{$start_time}' and create_time<='{$end_time}'
            and small_app_id in(1,2,11) and mobile_brand!='devtools' group by box_mac";
            $res_boxs = $model->query($sql_box);
            $box_nums = array();
            if(!empty($res_boxs)){
                foreach ($res_boxs as $bv){
                    $box_nums[$bv['box_mac']] = array('box_name'=>$bv['box_name'],'box_mac'=>$bv['box_mac'],'forscreen_num'=>$bv['num'],'forscreen_date_num'=>$bv['date_num']);
                }
            }
            $box_where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
            $m_box = new \Admin\Model\BoxModel();
            $res_box = $m_box->getBoxByCondition('box.*',$box_where,'');
            foreach ($res_box as $box){
                $info = array('area_name'=>$area_name,'hotel_name'=>$hotel_name,'maintainer_name'=>$maintainer_name,'box_name'=>$box['name'],
                    'box_mac'=>$box['mac'],'forscreen_num'=>0,'forscreen_date_num'=>0);
                if(isset($box_nums[$box['mac']])){
                    $info['forscreen_num'] = $box_nums[$box['mac']]['forscreen_num'];
                    $info['forscreen_date_num'] = $box_nums[$box['mac']]['forscreen_date_num'];
                }
                $data[]=$info;
            }
            echo "hotel_id: $hotel_id ok \r\n";

        }

        $cell = array(
            array('area_name','地区'),
            array('hotel_name','酒楼名称'),
            array('box_name','版位名称'),
            array('box_mac','版位MAC'),
            array('forscreen_num','互动量'),
            array('forscreen_date_num','互动天数'),
            array('maintainer_name','维护人'),
        );
        $this->exportToExcel($cell,$data,'投屏版位数据统计',2);
    }

    public function staticboxforscreenheart(){
        $s_date = I('sdate','');
        $e_date = I('edate','');

        $cache_key = 'cronscript:staticboxforscreenheart'.$s_date.$e_date;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $res = $redis->get($cache_key);
        if(!empty($res)){
            if($res == 1){
                $this->success('数据正在生成中,请稍后','',10);
            }else{
                //下载
                $file_name = $res;
                $file_path = SITE_TP_PATH.$file_name;
                $file_size = filesize($file_path);
                header("Content-type:application/octet-tream");
                header('Content-Transfer-Encoding: binary');
                header("Content-Length:$file_size");
                $file_name = '投屏版位心跳数据统计'.date('YmdHis').'.xls';
                header("Content-Disposition:attachment;filename=".$file_name);
                @readfile($file_path);
            }
        }else{
            $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php dataexport/forscreen/staticboxforscreenheartscript/sdate/$s_date/edate/$e_date > /tmp/null &";
            system($shell);
            $redis->set($cache_key,1,3600);
            $this->success('数据正在生成中,请稍后','',10);
        }
    }

    public function staticboxforscreenheartscript(){
        ini_set("memory_limit","1048M");
        $start_date = I('sdate','');
        $end_date = I('edate','');
        $start_time = date('Y-m-d 00:00:00',strtotime($start_date));
        $end_time = date('Y-m-d 23:59:59',strtotime($end_date));

        $all_hotel_types = C('heart_hotel_box_type');
        unset($all_hotel_types['2'],$all_hotel_types['3']);

        $m_hotel = new \Admin\Model\HotelModel();
        $field = 'a.id as hotel_id,a.name as hotel_name,area.id as area_id,area.region_name as area_name,a.hotel_box_type,a.level as hotel_level,
	    a.is_4g,ext.trainer_id,ext.train_date,ext.maintainer_id,a.tech_maintainer';
        $where = array('a.state'=>1,'a.flag'=>0,'a.type'=>1);
        $where['a.hotel_box_type'] = array('in',array_keys($all_hotel_types));
        $res_hotel = $m_hotel->getHotels($field,$where);
        $model = M();
        $data = array();
        $m_box = new \Admin\Model\BoxModel();
        $m_heratlog = new \Admin\Model\HeartLogModel();
        $m_heartalllog = new \Admin\Model\HeartAllLogModel();
        foreach ($res_hotel as $v){
            $hotel_id = $v['hotel_id'];
            if(in_array($hotel_id,array(7,883))){
                continue;
            }
            $hotel_name = $v['hotel_name'];
            $area_name = $v['area_name'];
            $maintainer_id = $v['maintainer_id'];
            $sql_maintainer = "select remark as uname from savor_sysuser where id={$maintainer_id}";
            $res_maintainer = $model->query($sql_maintainer);
            $maintainer_name = '';
            if(!empty($res_maintainer)){
                $maintainer_name = $res_maintainer[0]['uname'];
            }
            $sql_box = "select box_mac,box_name,count(*) as num from savor_smallapp_forscreen_record where hotel_id={$hotel_id} and create_time>='{$start_time}' and create_time<='{$end_time}'
            and small_app_id=1 and mobile_brand!='devtools' group by box_mac";
            $res_boxs = $model->query($sql_box);
            $standard_box_nums = array();
            if(!empty($res_boxs)){
                foreach ($res_boxs as $bv){
                    $standard_box_nums[$bv['box_mac']] = array('box_name'=>$bv['box_name'],'box_mac'=>$bv['box_mac'],'forscreen_num'=>$bv['num'],'forscreen_date_num'=>$bv['date_num']);
                }
            }

            $sql_box = "select box_mac,box_name,count(*) as num from savor_smallapp_forscreen_record where hotel_id={$hotel_id} and create_time>='{$start_time}' and create_time<='{$end_time}'
            and small_app_id=2 and mobile_brand!='devtools' group by box_mac";
            $res_boxs = $model->query($sql_box);
            $mini_box_nums = array();
            if(!empty($res_boxs)){
                foreach ($res_boxs as $bv){
                    $mini_box_nums[$bv['box_mac']] = array('box_name'=>$bv['box_name'],'box_mac'=>$bv['box_mac'],'forscreen_num'=>$bv['num'],'forscreen_date_num'=>$bv['date_num']);
                }
            }

            $box_where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
            $res_box = $m_box->getBoxByCondition('box.*',$box_where,'');
            foreach ($res_box as $box){
                $info = array('area_name'=>$area_name,'hotel_name'=>$hotel_name,'box_name'=>$box['name'],'box_mac'=>$box['mac'],
                    'is_4g'=>$box['is_4g'],'is_open_simple'=>$box['is_open_simple'],'maintainer_name'=>$maintainer_name,
                    'standard_box_num'=>0,'mini_box_num'=>0,'heart_num'=>0,'apk_version'=>'',
                    'box_type_str'=>$all_hotel_types[$box['box_type']],'wifi_name'=>$box['wifi_name']);
                if($info['is_4g']==1){
                    $info['is_4g_str'] = '是';
                }else{
                    $info['is_4g_str'] = '否';
                }
                if($info['is_open_simple']==1){
                    $info['is_open_simple_str'] = '是';
                }else{
                    $info['is_open_simple_str'] = '否';
                }
                if(isset($standard_box_nums[$box['mac']])){
                    $info['standard_box_num'] = $standard_box_nums[$box['mac']]['forscreen_num'];
                }
                if(isset($mini_box_nums[$box['mac']])){
                    $info['mini_box_num'] = $mini_box_nums[$box['mac']]['forscreen_num'];
                }
                $apk_where = array('hotel_id'=>$hotel_id,'box_mac'=>$box['mac']);
                $res_apk = $m_heratlog->getInfo('apk_version',$apk_where,'');
                if(!empty($res_apk)){
                    $info['apk_version'] = $res_apk['apk_version'];
                }
                $h_fields = 'sum(hour0+hour1+hour2+hour3+hour4+hour5+hour6+hour7+hour8+hour9+hour10+hour11+hour12+hour13+hour14+hour15+hour16+hour17+hour18+hour19+hour20+hour21+hour22+hour23) as heart_num';
                $h_where = array('date'=>array(array('EGT',$start_date),array('ELT',$end_date)),'mac'=>$box['mac'],'type'=>2);
                $res_heart = $m_heartalllog->getDataList($h_fields,$h_where,'');
                if(!empty($res_heart)){
                    $info['heart_num'] = intval($res_heart[0]['heart_num']);
                }else{
                    $info['heart_num'] = 0;
                }
                $data[]=$info;
            }
            echo "hotel_id: $hotel_id ok \r\n";

        }

        $cell = array(
            array('area_name','地区'),
            array('hotel_name','酒楼名称'),
            array('box_name','版位名称'),
            array('box_mac','版位MAC'),
            array('box_type_str','设备类型'),
            array('apk_version','apk版本号'),
            array('is_4g_str','是否是4G'),
            array('is_open_simple_str','是否开启极简'),
            array('wifi_name','内网WIFI名称'),
            array('mini_box_num','极简版互动数'),
            array('standard_box_num','普通版互动数'),
            array('heart_num','心跳数'),
            array('maintainer_name','维护人'),
        );
        $path = $this->exportToExcel($cell,$data,'投屏版位心跳数据统计',2);
        $cache_key = 'cronscript:staticboxforscreenheart'.$start_date.$end_date;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $redis->set($cache_key,$path,3600);
    }

    public function saledemandads(){
        $start_date = I('sdate','');
        $end_date = I('edate','');
        $start_time = date('Y-m-d 00:00:00',strtotime($start_date));
        $end_time = date('Y-m-d 23:59:59',strtotime($end_date));
        $fields = 'a.id,a.area_name,a.hotel_name,a.room_name,a.box_mac,a.box_type,a.openid,user.avatarUrl,user.nickName,a.mobile_brand,a.mobile_model,
        a.action,a.imgs,a.resource_size,a.duration,a.create_time';
        $sql = "select $fields from savor_smallapp_forscreen_record as a left join savor_smallapp_user as user on a.openid=user.openid
            where a.small_app_id=5 and a.create_time>='$start_time' 
            and a.create_time<='$end_time' and a.action=59 order by a.id desc ";
        $model = M();
        $res = $model->query($sql);
        $data = array();
        $all_box_types = C('hotel_box_type');
        foreach ($res as $v){
            $box_type_str = '';
            if(isset($all_box_types[$v['box_type']])){
                $box_type_str = $all_box_types[$v['box_type']];
            }
            $v['box_type_str'] = $box_type_str;
            $resource_size = '';
            if(!empty($v['resource_size'])){
                $resource_size = formatBytes($v['resource_size']);
            }
            $v['resource_size'] = $resource_size;
            $v['action']='广告点播任务';
            $data[] = $v;
        }
        $cell = array(
            array('id','序号'),
            array('area_name','城市'),
            array('hotel_name','酒楼名称'),
            array('room_name','包间名称'),
            array('box_mac','MAC地址'),
            array('box_type_str','机顶盒类型'),
            array('openid','openid'),
            array('nickName','用户昵称'),
            array('mobile_brand','手机品牌'),
            array('mobile_model','手机型号'),
            array('action','投屏动作'),
            array('resource_size','资源大小'),
            array('duration','资源时长'),
            array('create_time','投屏时间'),
        );
        $this->exportToExcel($cell,$data,'销售端广告点播投屏数据',1);
    }

}