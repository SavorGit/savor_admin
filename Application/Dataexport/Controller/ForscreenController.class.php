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
        $all_data = array();
        foreach ($all_dates as $date){
            $where = "box.flag=0 and box.state=1 and a.mobile_brand !='devtools' ";
            if($s_date){
                $where .=" and a.create_time>='".$date." 00:00:00'";
            }
            if($e_date){
                $where .=" and a.create_time<='".$date." 23:59:59'";
            }

            $sql ="SELECT area.region_name, hotel.id hotel_id,hotel.name hotel_name,hotel.hotel_box_type,sysuser.remark maintainer_name FROM `savor_smallapp_forscreen_record` a
               left join savor_box box on a.box_mac=box.mac left join savor_room room on box.room_id=room.id
               left join savor_hotel hotel on room.hotel_id=hotel.id left join savor_area_info area on hotel.area_id=area.id
               left join savor_hotel_ext hotelext on hotel.id=hotelext.hotel_id left join savor_sysuser sysuser on hotelext.maintainer_id=sysuser.id
               where {$where} group by hotel.id";
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
                $v['date'] = date('Y/m/d',strtotime($date));
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
            array('count101','h5互动游戏'),
            array('count120','发红包'),
            array('count121','扫码抢红包'),
            array('boxnum','版位数量'),
            array('onlinescreen','在线屏数'),
            array('hdnum','互动版位'),
            array('maintainer_name','维护人'),
            array('box_type','设备类型'),
        );
        $this->exportToExcel($cell,$all_data,$filename,1);
    }
}