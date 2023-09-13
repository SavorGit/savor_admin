<?php
namespace Admin\Model;

use Common\Lib\Page;

class OpsstaffModel extends BaseModel{
    protected $tableName='ops_staff';

    public function getCustomList($fields,$where,$order,$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_sysuser u on a.sysuser_id=u.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('a')
            ->join('savor_sysuser u on a.sysuser_id=u.id','left')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

    public function handle_stats_hotel_data(){
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getHotelAreaList();
        $areas = $area_arr;
        $tmp_area = array('id'=>0,'region_name'=>'全国');
        array_unshift($area_arr,$tmp_area);

        $m_box = new \Admin\Model\BoxModel();
        $fileds = 'box.id as box_id,box.mac,ext.hotel_id,ext.mac_addr,ext.maintainer_id';
        $hotel_box_types = array_keys(C('HEART_HOTEL_BOX_TYPE'));
        $where = array('box.state'=>1,'box.flag'=>0,'hotel.state'=>1,'hotel.flag'=>0);
        $where['hotel.hotel_box_type'] = array('in',$hotel_box_types);
        $area_staff_where = $where;
        $staff_where = $where;
        foreach ($area_arr as $v){
            if($v['id']>0){
                $where['hotel.area_id'] = $v['id'];
            }
            $res_box = $m_box->getBoxByCondition($fileds,$where);
            $this->stats_boxandplatform($res_box,1,$v['id'],0);
        }
        $now_time = date('Y-m-d H:i:s');
        echo "hotel stat area ok $now_time \r\n";
        $res_staff = $this->getDataList('sysuser_id',array('status'=>1),'id asc');
        foreach ($areas as $v){
            $area_id = $v['id'];
            foreach ($res_staff as $sv){
                $staff_id = $sv['sysuser_id'];
                $area_staff_where['hotel.area_id'] = $area_id;
                $area_staff_where['ext.maintainer_id'] = $staff_id;
                $res_box = $m_box->getBoxByCondition($fileds,$area_staff_where);
                $this->stats_boxandplatform($res_box,2,$area_id,$staff_id);
            }
        }
        $now_time = date('Y-m-d H:i:s');
        echo "hotel stat area_staff ok $now_time \r\n";
        foreach ($res_staff as $sv){
            $staff_id = $sv['sysuser_id'];
            $staff_where['ext.maintainer_id'] = $staff_id;
            $res_box = $m_box->getBoxByCondition($fileds,$staff_where);
            $this->stats_boxandplatform($res_box,3,0,$staff_id);
        }
        $now_time = date('Y-m-d H:i:s');
        echo "hotel stat staff ok $now_time \r\n";
    }

    public function handle_stats_versionup_data(){
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getHotelAreaList();
        $areas = $area_arr;
        $tmp_area = array('id'=>0,'region_name'=>'全国');
        array_unshift($area_arr,$tmp_area);

        $m_box = new \Admin\Model\BoxModel();
        $fileds = 'box.id as box_id,box.mac,ext.hotel_id,ext.mac_addr,ext.maintainer_id';
        $where = array('box.state'=>1,'box.flag'=>0,'hotel.state'=>1,'hotel.flag'=>0);
        $hotel_box_types = array_keys(C('HEART_HOTEL_BOX_TYPE'));
        $where['hotel.hotel_box_type'] = array('in',$hotel_box_types);
        $area_staff_where = $where;
        $staff_where = $where;
        foreach ($area_arr as $v){
            if($v['id']>0){
                $where['hotel.area_id'] = $v['id'];
            }
            $res_box = $m_box->getBoxByCondition($fileds,$where);
            $this->stats_versionupgrade($res_box,1,$v['id'],0);
        }
        $now_time = date('Y-m-d H:i:s');
        echo "versionup stat area ok $now_time \r\n";
        $res_staff = $this->getDataList('sysuser_id',array('status'=>1),'id asc');
        foreach ($areas as $v){
            $area_id = $v['id'];
            foreach ($res_staff as $sv){
                $staff_id = $sv['sysuser_id'];
                $area_staff_where['hotel.area_id'] = $area_id;
                $area_staff_where['ext.maintainer_id'] = $staff_id;
                $res_box = $m_box->getBoxByCondition($fileds,$area_staff_where);

                $this->stats_versionupgrade($res_box,2,$area_id,$staff_id);
            }
        }
        $now_time = date('Y-m-d H:i:s');
        echo "versionup stat area_staff ok $now_time \r\n";
        foreach ($res_staff as $sv){
            $staff_id = $sv['sysuser_id'];
            $staff_where['ext.maintainer_id'] = $staff_id;
            $res_box = $m_box->getBoxByCondition($fileds,$staff_where);
            $this->stats_versionupgrade($res_box,3,0,$staff_id);
        }
        $now_time = date('Y-m-d H:i:s');
        echo "versionup stat staff ok $now_time \r\n";
    }

    public function handle_stats_resourceup_data(){
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getHotelAreaList();
        $areas = $area_arr;
        $tmp_area = array('id'=>0,'region_name'=>'全国');
        array_unshift($area_arr,$tmp_area);

        $m_box = new \Admin\Model\BoxModel();
        $fileds = 'box.id as box_id,box.mac,ext.hotel_id,ext.mac_addr,ext.maintainer_id';
        $where = array('box.state'=>1,'box.flag'=>0,'hotel.state'=>1,'hotel.flag'=>0);
        $hotel_box_types = array_keys(C('HEART_HOTEL_BOX_TYPE'));
        $where['hotel.hotel_box_type'] = array('in',$hotel_box_types);
        $area_staff_where = $where;
        $staff_where = $where;
        foreach ($area_arr as $v){
            if($v['id']>0){
                $where['hotel.area_id'] = $v['id'];
            }
            $res_box = $m_box->getBoxByCondition($fileds,$where);
            $this->stats_resourceupdate($res_box,1,$v['id'],0);
        }
        $now_time = date('Y-m-d H:i:s');
        echo "resourceup stat area ok $now_time \r\n";
        $res_staff = $this->getDataList('sysuser_id',array('status'=>1),'id asc');
        foreach ($areas as $v){
            $area_id = $v['id'];
            foreach ($res_staff as $sv){
                $staff_id = $sv['sysuser_id'];
                $area_staff_where['hotel.area_id'] = $area_id;
                $area_staff_where['ext.maintainer_id'] = $staff_id;
                $res_box = $m_box->getBoxByCondition($fileds,$area_staff_where);

                $this->stats_resourceupdate($res_box,2,$area_id,$staff_id);
            }
        }
        $now_time = date('Y-m-d H:i:s');
        echo "resourceup stat area_staff ok $now_time \r\n";
        foreach ($res_staff as $sv){
            $staff_id = $sv['sysuser_id'];
            $staff_where['ext.maintainer_id'] = $staff_id;
            $res_box = $m_box->getBoxByCondition($fileds,$staff_where);
            $this->stats_resourceupdate($res_box,3,0,$staff_id);
        }
        $now_time = date('Y-m-d H:i:s');
        echo "resourceup stat staff ok $now_time \r\n";
    }

    public function handle_stats_device_data(){
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getHotelAreaList();
        $areas = $area_arr;
        $tmp_area = array('id'=>0,'region_name'=>'全国');
        array_unshift($area_arr,$tmp_area);

        $m_box = new \Admin\Model\BoxModel();
        $fileds = 'box.id as box_id,box.mac,ext.hotel_id,ext.mac_addr,ext.maintainer_id';
        $where = array('box.state'=>1,'box.flag'=>0,'hotel.state'=>1,'hotel.flag'=>0);
        $hotel_box_types = array_keys(C('HEART_HOTEL_BOX_TYPE'));
        $where['hotel.hotel_box_type'] = array('in',$hotel_box_types);
        $area_staff_where = $where;
        $staff_where = $where;
        foreach ($area_arr as $v){
            if($v['id']>0){
                $where['hotel.area_id'] = $v['id'];
            }
            $res_box = $m_box->getBoxByCondition($fileds,$where);
            $this->stats_device($res_box,1,$v['id'],0);
        }
        $now_time = date('Y-m-d H:i:s');
        echo "device stat area ok $now_time \r\n";
        $res_staff = $this->getDataList('sysuser_id',array('status'=>1),'id asc');
        foreach ($areas as $v){
            $area_id = $v['id'];
            foreach ($res_staff as $sv){
                $staff_id = $sv['sysuser_id'];
                $area_staff_where['hotel.area_id'] = $area_id;
                $area_staff_where['ext.maintainer_id'] = $staff_id;
                $res_box = $m_box->getBoxByCondition($fileds,$area_staff_where);

                $this->stats_device($res_box,2,$area_id,$staff_id);
            }
        }
        $now_time = date('Y-m-d H:i:s');
        echo "device stat area_staff ok $now_time \r\n";
        foreach ($res_staff as $sv){
            $staff_id = $sv['sysuser_id'];
            $staff_where['ext.maintainer_id'] = $staff_id;
            $res_box = $m_box->getBoxByCondition($fileds,$staff_where);
            $this->stats_device($res_box,3,0,$staff_id);
        }
        $now_time = date('Y-m-d H:i:s');
        echo "device stat staff ok $now_time \r\n";
    }

    private function stats_device($res_box,$type,$area_id,$staff_id){
        $box_num = $box_normal_num = $box_abnormal_num = 0;
        $abnormal_hotels = array();
        $m_sdkerror = new \Admin\Model\SdkErrorModel();
        foreach ($res_box as $v){
            $box_num++;
            $res_sdkerror = $m_sdkerror->getInfo(array('box_id'=>$v['box_id']));
            if(!empty($res_sdkerror) && $res_sdkerror['full_report_date']>$res_sdkerror['clean_report_date']){
                $box_abnormal_num++;
                $abnormal_hotels[$v['hotel_id']][]=$v['box_id'];
            }
        }
        $box_normal_num = $box_num-$box_abnormal_num>0?$box_num-$box_abnormal_num:0;

        $res_data = array('up_time'=>date('Y-m-d H:i:s'),'box_num'=>$box_num,
            'box_normal_num'=>$box_normal_num,'box_abnormal_num'=>$box_abnormal_num,
            'abnormal_hotels'=>$abnormal_hotels
        );
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(22);
        switch ($type){
            case 1:
                $cache_key = C('SAPP_OPS').'stat:device:area:'.$area_id;
                $redis->set($cache_key,json_encode($res_data),86400*7);
                break;
            case 2:
                $cache_key = C('SAPP_OPS').'stat:device:area_staff:'.$staff_id.':'.$area_id;
                $redis->set($cache_key,json_encode($res_data),86400*7);
                break;
            case 3:
                $cache_key = C('SAPP_OPS').'stat:device:staff:'.$staff_id;
                $redis->set($cache_key,json_encode($res_data),86400*7);
                break;
        }
        return true;
    }

    private function stats_resourceupdate($res_box,$type,$area_id,$staff_id){
        $redis = new \Common\Lib\SavorRedis();

        $hotel_versions = array();
        $hotels = array();
        foreach ($res_box as $v){
            $hotels[$v['hotel_id']] = $v['mac_addr'];
        }
        $m_new_menu_hotel = new \Admin\Model\ProgramMenuHotelModel();
        foreach ($hotels as $k=>$m){
            $hotel_id = $k;

            $sql_adv_version = "select max(update_time) as max_update_time from savor_ads where hotel_id={$hotel_id} and type=3";
            $res_adv_version = $this->query($sql_adv_version);
            $adv = '20190101000000';//宣传片期号
            if(!empty($res_adv_version)){
                $adv = date('YmdHis',strtotime($res_adv_version[0]['max_update_time']));
            }
            $menu_info = $m_new_menu_hotel->getLatestMenuid($hotel_id);//获取最新的一期节目单
            $menu_num= $menu_info['menu_num'];

            $hotel_versions[$k] = array('adv'=>$adv,'menu_num'=>$menu_num);
        }

        $box_num = 0;
        $adv_up_num = $pro_up_num = $ads_up_num = 0;
        $adv_notup_hotels = $pro_notup_hotels = $ads_notup_hotels = array();
        $max_adv_location = C('MAX_ADS_LOCATION_NUMS');
        $program_ads_menu_num_key = C('PROGRAM_ADS_MENU_NUM');
        $program_ads_key = C('PROGRAM_ADS_CACHE_PRE');
        foreach ($res_box as $v){
            $box_num++;
            $ckey = 'heartbeat:2:'.$v['mac'];
            $redis->select(13);
            $res_cache = $redis->get($ckey);
            if(!empty($res_cache)){
                $cache_data = json_decode($res_cache,true);
                if($hotel_versions[$v['hotel_id']]['adv'].$hotel_versions[$v['hotel_id']]['menu_num']==$cache_data['adv_period']){
                    $adv_up_num++;
                }else{
                    $adv_notup_hotels[$v['hotel_id']][]=$v['box_id'];
                }
                if($hotel_versions[$v['hotel_id']]['menu_num']==$cache_data['pro_period']){
                    $pro_up_num++;
                }else{
                    $pro_notup_hotels[$v['hotel_id']][]=$v['box_id'];
                }

                //获取机顶盒的广告期号
                if($v['mac_addr']=='000000000000'){//虚拟小平台
                    $redis->select(10);
                    $cache_key = 'vsmall:ads:'.$v['hotel_id'].":".$v['mac'];
                    $cache_info = $redis->get($cache_key);
                    $ads_info = json_decode($cache_info,true);
                    if(!empty($ads_info['media_lib'])){
                        $ads_proid = $ads_info['menu_num'];
                    }else{
                        $ads_proid = '';
                    }
                }else{//实体小平台
                    $redis->select(12);
                    $cache_key = $program_ads_key.$v['box_id'];
                    $cache_value = $redis->get($cache_key);
                    $ads_info = json_decode($cache_value,true);
                    $ads_proid = $ads_info['menu_num'];
                }
                if(empty($ads_proid)){
                    $m_pub_ads_box = new \Admin\Model\PubAdsBoxModel();
                    $now_date = date('Y-m-d H:i:s');
                    $ads_num_arr = array();
                    $ads_time_arr = array();

                    $box_had_ads_status = 1;
                    $tmp_stat_box_ads_cache_key = 'tmp_stat_box_ads_'.$v['box_id'];
                    $redis->select(12);
                    $res_stat_box_ads = $redis->get($tmp_stat_box_ads_cache_key);
                    if(!empty($res_stat_box_ads)){
                        if($res_stat_box_ads==2){
                            $ads_num_arr = 2;
                        }
                    }else{
                        for($i=1;$i<=$max_adv_location;$i++){
                            $adv_arr = $m_pub_ads_box->getAdsList($v['box_id'],$i);  //获取当前机顶盒得某一个位置得广告
                            $adv_arr = $this->changeadvList($adv_arr);
                            if(!empty($adv_arr)){
                                $flag =0;
                                foreach($adv_arr as $ak=>$av){
                                    if($av['start_date']>$now_date){
                                        $flag ++;
                                    }
                                    if($flag==2){
                                        unset($adv_arr[$ak]);
                                        break;
                                    }
                                    $ads_arr['create_time'] = $av['create_time'];
                                    $ads_num_arr[] = $ads_arr;
                                    $ads_time_arr[] = $av['create_time'];
                                    unset($av['pub_ads_id']);
                                    unset($av['create_time']);
                                }
                            }
                            if(!empty($ads_num_arr)){
                                $box_had_ads_status = 2;
                                break;
                            }
                        }
                        $redis->set($tmp_stat_box_ads_cache_key,$box_had_ads_status,900);
                    }

                    if(!empty($ads_num_arr)){//如果该机顶盒下广告位不为空
//                        $ads_time_str = max($ads_time_arr);
//                        $ads_proid = date('YmdHis',strtotime($ads_time_str));
                        $redis->select(12);
                        $program_ads_menu_num = $redis->get($program_ads_menu_num_key);
                        $ads_proid = $program_ads_menu_num;
                    }
                }
                //end

                if($ads_proid==$cache_data['period']){
                    $ads_up_num++;
                }else{
                    $ads_notup_hotels[$v['hotel_id']][]=$v['box_id'];
                }
            }else{
                $adv_notup_hotels[$v['hotel_id']][]=$v['box_id'];
                $pro_notup_hotels[$v['hotel_id']][]=$v['box_id'];
                $ads_notup_hotels[$v['hotel_id']][]=$v['box_id'];
            }
        }
        $adv_notup_num = $box_num-$adv_up_num>0?$box_num-$adv_up_num:0;
        $pro_notup_num = $box_num-$pro_up_num>0?$box_num-$pro_up_num:0;
        $ads_notup_num = $box_num-$ads_up_num>0?$box_num-$ads_up_num:0;

        $res_data = array('up_time'=>date('Y-m-d H:i:s'),'box_num'=>$box_num,
            'adv_up_num'=>$adv_up_num,'adv_notup_num'=>$adv_notup_num,
            'pro_up_num'=>$pro_up_num,'pro_notup_num'=>$pro_notup_num,
            'ads_up_num'=>$ads_up_num,'ads_notup_num'=>$ads_notup_num,
            'adv_notup_hotels'=>$adv_notup_hotels,'pro_notup_hotels'=>$pro_notup_hotels,'ads_notup_hotels'=>$ads_notup_hotels
        );
        $redis->select(22);
        switch ($type){
            case 1:
                $cache_key = C('SAPP_OPS').'stat:resourceup:area:'.$area_id;
                $redis->set($cache_key,json_encode($res_data),86400*7);
                break;
            case 2:
                $cache_key = C('SAPP_OPS').'stat:resourceup:area_staff:'.$staff_id.':'.$area_id;
                $redis->set($cache_key,json_encode($res_data),86400*7);
                break;
            case 3:
                $cache_key = C('SAPP_OPS').'stat:resourceup:staff:'.$staff_id;
                $redis->set($cache_key,json_encode($res_data),86400*7);
                break;
        }
        return true;
    }

    private function stats_versionupgrade($res_box,$type,$area_id,$staff_id){
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(13);
        $small_platform_num = $box_num = 0;
        $small_platform_up_num = $box_up_num = 0;
        $hotel_versions = array();
        $hotels = array();
        foreach ($res_box as $v){
            $hotels[$v['hotel_id']] = $v['mac_addr'];
        }
        $small_platform_notup_hotels=array();
        foreach ($hotels as $k=>$m){
            $sql_hotel_version = "select du.id,du.version,du.update_type,dv.version_name from savor_device_upgrade du
            left join savor_device_version dv on du.version=dv.version_code
            where du.device_type=2 and dv.device_type=2 and (du.hotel_id LIKE '%,{$k},%' OR du.hotel_id IS NULL) and du.state=1 
            order by du.id desc  limit 0,1";
            $res_hotel_version = $this->query($sql_hotel_version);
            if(!empty($res_hotel_version)){
                $hotel_versions[$k] = $res_hotel_version[0]['version_name'];
            }
            if($m!='000000000000'){
                $small_platform_num++;
                $ckey = 'heartbeat:1:'.$m;
                $res_cache = $redis->get($ckey);
                if(!empty($res_cache)){
                    $cache_data = json_decode($res_cache,true);
                    $sql_version = "select id,version,update_type from savor_device_upgrade where device_type=1 and (hotel_id LIKE '%,{$k},%' OR hotel_id IS NULL) order by id desc limit 0,1";
                    $res_version = $this->query($sql_version);
                    if($cache_data['war']==$res_version[0]['version']){
                        $small_platform_up_num++;
                    }else{
                        $small_platform_notup_hotels[$k]=$k;
                    }
                }else{
                    $small_platform_notup_hotels[$k]=$k;
                }
            }
        }
        $box_notup_hotels=array();
        foreach ($res_box as $v){
            $box_num++;
            $ckey = 'heartbeat:2:'.$v['mac'];
            $res_cache = $redis->get($ckey);
            if(!empty($res_cache)){
                $cache_data = json_decode($res_cache,true);
                if(isset($hotel_versions[$v['hotel_id']]) && $cache_data['apk']==$hotel_versions[$v['hotel_id']]){
                    $box_up_num++;
                }else{
                    $box_notup_hotels[$v['hotel_id']][]=$v['box_id'];
                }
            }else{
                $box_notup_hotels[$v['hotel_id']][]=$v['box_id'];
            }
        }
        $small_platform_notup_num = $small_platform_num-$small_platform_up_num>0?$small_platform_num-$small_platform_up_num:0;
        $box_notup_num = $box_num-$box_up_num>0?$box_num-$box_up_num:0;
        $res_data = array('up_time'=>date('Y-m-d H:i:s'),
            'small_platform_num'=>$small_platform_num,'small_platform_up_num'=>$small_platform_up_num,'small_platform_notup_num'=>$small_platform_notup_num,
            'box_num'=>$box_num,'box_up_num'=>$box_up_num,'box_notup_num'=>$box_notup_num,
            'small_platform_notup_hotels'=>$small_platform_notup_hotels,'box_notup_hotels'=>$box_notup_hotels,
        );
        $redis->select(22);
        switch ($type){
            case 1:
                $cache_key = C('SAPP_OPS').'stat:versionup:area:'.$area_id;
                $redis->set($cache_key,json_encode($res_data),86400*7);
                break;
            case 2:
                $cache_key = C('SAPP_OPS').'stat:versionup:area_staff:'.$staff_id.':'.$area_id;
                $redis->set($cache_key,json_encode($res_data),86400*7);
                break;
            case 3:
                $cache_key = C('SAPP_OPS').'stat:versionup:staff:'.$staff_id;
                $redis->set($cache_key,json_encode($res_data),86400*7);
                break;
        }
        return true;
    }

    private function stats_boxandplatform($res_box,$type,$area_id,$staff_id){
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(13);
        $hotel_ids = array();
        $small_platform_num = $box_num = 0;
        $box_online_num=$box_24_num=$box_24_7_num=$box_7day_num=$box_30day_num=0;

        $now_time = time();
        $online_time = 900;
        $boot24_time = 86400;
        $day7_time = 7*86400;
        $day30_time = 30*86400;
        $box_online_hotels=$box_24_hotels=$box_24_7_hotels=$box_7day_hotels=$box_30day_hotels=array();
        foreach ($res_box as $v){
            $box_num++;
            $hotel_ids[$v['hotel_id']] = $v['mac_addr'];
            $ckey = 'heartbeat:2:'.$v['mac'];
            $res_cache = $redis->get($ckey);
            if(empty($res_cache)){
                $box_30day_num++;
                $box_30day_hotels[$v['hotel_id']][]=$v['box_id'];
            }else{
                $cache_data = json_decode($res_cache,true);
                $report_time = strtotime($cache_data['date']);
                $diff_time = $now_time - $report_time;
                if($diff_time<=$online_time){
                    $box_online_num++;
                    $box_online_hotels[$v['hotel_id']][]=$v['box_id'];
                }elseif($diff_time<=$boot24_time){
                    $box_24_num++;
                    $box_24_hotels[$v['hotel_id']][]=$v['box_id'];
                }elseif($diff_time>$boot24_time && $diff_time<=$day7_time){
                    $box_24_7_num++;
                    $box_24_7_hotels[$v['hotel_id']][]=$v['box_id'];
                }elseif($diff_time>$day7_time && $diff_time<$day30_time){
                    $box_7day_num++;
                    $box_7day_hotels[$v['hotel_id']][]=$v['box_id'];
                }else{
                    $box_30day_num++;
                    $box_30day_hotels[$v['hotel_id']][]=$v['box_id'];
                }
            }
        }
        $small_platform_online_num=$small_platform_24_num=$small_platform_24_7_num=$small_platform_7day_num=$small_platform_30day_num=0;
        $small_platform_online_hotels=$small_platform_24_hotels=$small_platform_24_7_hotels=$small_platform_7day_hotels=$small_platform_30day_hotels=array();
        foreach ($hotel_ids as $k=>$m){
            if($m!='000000000000'){
                $small_platform_num++;
                $ckey = 'heartbeat:1:'.$m;
                $res_cache = $redis->get($ckey);
                if(empty($res_cache)){
                    $small_platform_30day_num++;
                    $small_platform_30day_hotels[$k]=$k;
                }else{
                    $cache_data = json_decode($res_cache,true);
                    $report_time = strtotime($cache_data['date']);
                    $diff_time = $now_time - $report_time;
                    if($diff_time<=$online_time){
                        $small_platform_online_num++;
                        $small_platform_online_hotels[$k]=$k;
                    }elseif($diff_time<=$boot24_time){
                        $small_platform_24_num++;
                        $small_platform_24_hotels[$k]=$k;
                    }elseif($diff_time>$boot24_time && $diff_time<=$day7_time){
                        $small_platform_24_7_num++;
                        $small_platform_24_7_hotels[$k]=$k;
                    }elseif($diff_time>$day7_time && $diff_time<$day30_time){
                        $small_platform_7day_num++;
                        $small_platform_7day_hotels[$k]=$k;
                    }else{
                        $small_platform_30day_num++;
                        $small_platform_30day_hotels[$k]=$k;
                    }
                }
            }
        }
        $res_data = array('up_time'=>date('Y-m-d H:i:s'),'hotel_nums'=>count($hotel_ids),
            'small_platform_num'=>$small_platform_num,'small_platform_online_num'=>$small_platform_online_num,'small_platform_24_num'=>$small_platform_24_num,
            'small_platform_24_7_num'=>$small_platform_24_7_num,'small_platform_7day_num'=>$small_platform_7day_num,'small_platform_30day_num'=>$small_platform_30day_num,
            'box_num'=>$box_num,'box_online_num'=>$box_online_num,'box_24_num'=>$box_24_num,'box_24_7_num'=>$box_24_7_num,'box_7day_num'=>$box_7day_num,'box_30day_num'=>$box_30day_num,
            'small_platform_online_hotels'=>$small_platform_online_hotels,'small_platform_24_hotels'=>$small_platform_24_hotels,'small_platform_24_7_hotels'=>$small_platform_24_7_hotels,
            'small_platform_7day_hotels'=>$small_platform_7day_hotels,'small_platform_30day_hotels'=>$small_platform_30day_hotels,
            'box_online_hotels'=>$box_online_hotels,'box_24_hotels'=>$box_24_hotels,'box_24_7_hotels'=>$box_24_7_hotels,'box_7day_hotels'=>$box_7day_hotels,'box_30day_hotels'=>$box_30day_hotels
        );
        $redis->select(22);
        switch ($type){
            case 1:
                $cache_key = C('SAPP_OPS').'stat:hotel:area:'.$area_id;
                $redis->set($cache_key,json_encode($res_data),86400*7);
                break;
            case 2:
                $cache_key = C('SAPP_OPS').'stat:hotel:area_staff:'.$staff_id.':'.$area_id;
                $redis->set($cache_key,json_encode($res_data),86400*7);
                break;
            case 3:
                $cache_key = C('SAPP_OPS').'stat:hotel:staff:'.$staff_id;
                $redis->set($cache_key,json_encode($res_data),86400*7);
                break;
        }
        return true;
    }

    private function changeadvList($res,$type=1){
        if($res){
            foreach ($res as $vk=>$val) {
                if(!empty($val['sortNum'])){
                    if($type==1){
                        $res[$vk]['order'] =  $res[$vk]['sortNum'];
                    }else {
                        $res[$vk]['location_id'] = $res[$vk]['sortNum'];
                    }

                    unset($res[$vk]['sortNum']);
                }

                if(!empty($val['name'])){
                    $ttp = explode('/', $val['name']);
                    $res[$vk]['name'] = $ttp[2];
                }
                if($val['media_type']==2){
                    $res[$vk]['md5_type'] = 'fullMd5';
                }
                $res[$vk]['is_sapp_qrcode'] = intval($val['is_sapp_qrcode']);
            }

        }
        return $res;
    }
}