<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class StaticHotelbasicdataModel extends BaseModel{

    protected $tableName='smallapp_static_hotelbasicdata';

    public function getCustomeList($fields="*",$where,$groupby='',$order='',$countfields='',$start=0,$size=5){
        $list = $this->field($fields)
            ->where($where)
            ->group($groupby)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $res_count = $this->field($countfields)
            ->where($where)->select();
        $count = $res_count[0]['tp_count'];
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        return $data;
    }

    public function handle_hotel_basicdata(){
        $scan_qrcode_types = C('SCAN_QRCODE_TYPES');
        $all_hotel_types = C('heart_hotel_box_type');

        $m_hotel = new \Admin\Model\HotelModel();
        $field = 'a.id as hotel_id,a.name as hotel_name,area.id as area_id,area.region_name as area_name,a.hotel_box_type,a.level as hotel_level,
	    a.is_4g,a.is_5g,ext.trainer_id,ext.train_date,ext.maintainer_id,a.tech_maintainer';
        $where = array('a.state'=>1,'a.flag'=>0,'a.type'=>1);
        $where['a.hotel_box_type'] = array('in',array_keys($all_hotel_types));
        $res_hotel = $m_hotel->getHotels($field,$where);

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $start = date('Y-m-d',strtotime('-1day'));
        $end = date('Y-m-d',strtotime('-1day'));

        $all_dates = $m_statistics->getDates($start,$end);

        $m_box = new \Admin\Model\BoxModel();
        $m_qrcodelog = new \Admin\Model\Smallapp\QrcodeLogModel();
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $m_smallapp_iforscreen_record = new \Admin\Model\Smallapp\ForscreeninvalidrecordModel();
        $m_heartlog = new \Admin\Model\HeartAllLogModel();

        $m_sysuser = new \Admin\Model\UserModel();
        foreach ($all_dates as $v){
            $static_date = $v;
            $time_date = strtotime($v);
            $date = date('Ymd',$time_date);
            $start_time = date('Y-m-d 00:00:00',$time_date);
            $end_time = date('Y-m-d 23:59:59',$time_date);
            foreach ($res_hotel as $hv){
                $hotel_id = $hv['hotel_id'];
                $box_where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
                $res_box = $m_box->getBoxByCondition('*',$box_where,'');
                $box_num = count($res_box);
                $faultbox_num = $normalbox_num = 0;
                $wlnum = $wl_hdnum = 0;
                if($box_num){
                    foreach ($res_box as $bv){
                        if($bv['fault_status']==1){
                            $normalbox_num++;
                        }elseif($bv['fault_status']==2){
                            $faultbox_num++;
                        }
                        if(isset($all_hotel_types[$bv['box_type']])){
                            $wlnum++;
                            if($bv['is_interact']==1){
                                $wl_hdnum++;
                            }
                        }
                    }
                }
                $fault_rate = 0;
                if($box_num && $faultbox_num){
                    $fault_rate = sprintf("%.2f",$faultbox_num/$box_num);
                }
                $lostfault_rate = 0;
                $lost_boxnum = $m_heartlog->getLostBoxNum($hotel_id);
                if($lost_boxnum){
                    $lostfault_rate = sprintf("%.2f",$lost_boxnum/$box_num);
                }
                $heart_num = $m_heartlog->getHotelAllHeart($date,$hotel_id);
                $meal_heart_num = $m_heartlog->getHotelMealHeart($date,$hotel_id);
                $room_heart_num = $m_heartlog->getHotelAllHeart($date,$hotel_id,1);
                $room_meal_heart_num = $m_heartlog->getHotelMealHeart($date,$hotel_id,1);
//                $avg_down_speed = $m_smallapp_forscreen_record->getAvgspeedByHotelId($hotel_id,$time_date);
                $avg_down_speed = $m_smallapp_forscreen_record->getAvgspeedByStaticHotelId($hotel_id,$date);


                $lunch_zxhdnum = $m_heartlog->getHotelOnlineBoxnum($date,$hotel_id,1,1);
                $dinner_zxhdnum = $m_heartlog->getHotelOnlineBoxnum($date,$hotel_id,2,1);

                $user_lunch_zxhdnum = $user_dinner_zxhdnum = 0;
                $res_forscreen_box = $m_smallapp_forscreen_record->getFeastInteractBoxByHotelId($hotel_id,$time_date,1,1);
                if(!empty($res_forscreen_box)){
                    $user_lunch_zxhdnum = count($res_forscreen_box);
                }
                $res_forscreen_box = $m_smallapp_forscreen_record->getFeastInteractBoxByHotelId($hotel_id,$time_date,2,1);
                if(!empty($res_forscreen_box)){
                    $user_dinner_zxhdnum = count($res_forscreen_box);
                }
                $user_lunch_cvr = $user_dinner_cvr = 0;
                if($user_lunch_zxhdnum && $lunch_zxhdnum){
                    $user_lunch_cvr = sprintf("%.2f",$user_lunch_zxhdnum/$lunch_zxhdnum);
                }
                if($user_dinner_zxhdnum && $dinner_zxhdnum){
                    $user_dinner_cvr = sprintf("%.2f",$user_dinner_zxhdnum/$dinner_zxhdnum);
                }

                $sale_lunch_zxhdnum = $sale_dinner_zxhdnum = 0;
                $res_forscreen_box = $m_smallapp_forscreen_record->getFeastInteractBoxByHotelId($hotel_id,$time_date,1,5);
                if(!empty($res_forscreen_box)){
                    $sale_lunch_zxhdnum = count($res_forscreen_box);
                }
                $res_forscreen_box = $m_smallapp_forscreen_record->getFeastInteractBoxByHotelId($hotel_id,$time_date,2,5);
                if(!empty($res_forscreen_box)){
                    $sale_dinner_zxhdnum = count($res_forscreen_box);
                }
                $sale_lunch_cvr = $sale_dinner_cvr = 0;
                if($sale_lunch_zxhdnum && $lunch_zxhdnum){
                    $sale_lunch_cvr = sprintf("%.2f",$sale_lunch_zxhdnum/$lunch_zxhdnum);
                }
                if($sale_dinner_zxhdnum && $dinner_zxhdnum){
                    $sale_dinner_cvr = sprintf("%.2f",$sale_dinner_zxhdnum/$dinner_zxhdnum);
                }

                $lunch_zxnum = $m_heartlog->getHotelOnlineBoxnum($date,$hotel_id,1,0);
                $dinner_zxnum = $m_heartlog->getHotelOnlineBoxnum($date,$hotel_id,2,0);
                $zxnum = $m_heartlog->getHotelOnlineBoxnum($date,$hotel_id,0,0);

                $lunch_zxrate = $dinner_zxrate = $zxrate = 0;
                if($lunch_zxnum && $wlnum){
                    $lunch_zxrate = sprintf("%.2f",$lunch_zxnum/$wlnum);
                }
                if($dinner_zxnum && $wlnum){
                    $dinner_zxrate = sprintf("%.2f",$dinner_zxnum/$wlnum);
                }
                if($zxnum && $wlnum){
                    $zxrate = sprintf("%.2f",$zxnum/$wlnum);
                }

                //扫码数
                $fields = "count(a.id) as num";
                $qrcode_where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
                $qrcode_where['a.type'] = array('in',$scan_qrcode_types);
                $qrcode_where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
                $res_qrcode = $m_qrcodelog->getScanqrcodeNum($fields,$qrcode_where);
                $scancode_num = intval($res_qrcode[0]['num']);

                $fields = "count(DISTINCT(a.openid)) as num";
                $res_userqrcode = $m_qrcodelog->getScanqrcodeNum($fields,$qrcode_where);
                $user_num = intval($res_userqrcode[0]['num']);

                //互动总数
                $interact_standard_num = $interact_mini_num = $interact_sale_num = $interact_game_num = 0;
                $forscreen_where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0,'a.is_valid'=>1);
                $forscreen_where['a.mobile_brand'] = array('neq','devtools');
                $forscreen_where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
                $forscreen_where['a.small_app_id'] = array('in',array(1,2,5,11));//小程序ID 1普通版,2极简版,5销售端,11 h5互动游戏
                $fields = "count(a.id) as fnum,a.small_app_id";
                $res_forscreen = $m_smallapp_forscreen_record->getWhere($fields,$forscreen_where,'','a.small_app_id');
                foreach ($res_forscreen as $fv){
                    switch ($fv['small_app_id']){
                        case 1:
                            $interact_standard_num = $fv['fnum'];
                            break;
                        case 2:
                            $interact_mini_num = $fv['fnum'];
                            break;
                        case 5:
                            $interact_sale_num = $fv['fnum'];
                            break;
                        case 11:
                            $interact_game_num = $fv['fnum'];
                            break;
                    }
                }
                $interact_num = $interact_standard_num+$interact_mini_num+$interact_sale_num+$interact_game_num;
                $user_lunch_interact_num = $m_smallapp_forscreen_record->getFeastForscreenNumByHotelId($hotel_id,$time_date,1,1);
                $user_dinner_interact_num = $m_smallapp_forscreen_record->getFeastForscreenNumByHotelId($hotel_id,$time_date,2,1);
                $interact_sale_signnum = $m_smallapp_forscreen_record->getSaleSignForscreenNumByHotelId($hotel_id,$time_date);

                //餐厅扫码数
                $fields = "count(a.id) as num";
                $restaurantqrcode_where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
                $restaurantqrcode_where['a.type'] = array('in',$scan_qrcode_types);
                $restaurantqrcode_where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
                $restaurantqrcode_where['_string'] = 'a.openid in(select invalidid from savor_smallapp_forscreen_invalidlist where type=2)';
                $res_qrcode = $m_qrcodelog->getScanqrcodeNum($fields,$restaurantqrcode_where);
                $restaurant_scancode_num = intval($res_qrcode[0]['num']);

                $fields = "count(DISTINCT(a.openid)) as num";
                $res_userqrcode = $m_qrcodelog->getScanqrcodeNum($fields,$restaurantqrcode_where);
                $restaurant_user_num = intval($res_userqrcode[0]['num']);

                $restaurant_interact_standard_num = 0;
                $iforscreen_where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0,'a.is_valid'=>1);
                $iforscreen_where['a.mobile_brand'] = array('neq','devtools');
                $iforscreen_where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
                $iforscreen_where['a.small_app_id'] = array('in',array(1,2,11));//小程序ID 1普通版,2极简版,5销售端,11 h5互动游戏
                $fields = 'count(a.id) as fnum';
                $res_iforscreen = $m_smallapp_iforscreen_record->getWhere($fields,$iforscreen_where,'','');
                if(!empty($res_iforscreen)){
                    $restaurant_interact_standard_num = $res_iforscreen[0]['fnum'];
                }
                $restaurant_user_lunch_zxhdnum = $restaurant_user_dinner_zxhdnum = 0;
                $res_iforscreen_box = $m_smallapp_iforscreen_record->getFeastInteractBoxByHotelId($hotel_id,$time_date,1,1);
                if(!empty($res_forscreen_box)){
                    $restaurant_user_lunch_zxhdnum = count($res_iforscreen_box);
                }
                $res_iforscreen_box = $m_smallapp_iforscreen_record->getFeastInteractBoxByHotelId($hotel_id,$time_date,2,1);
                if(!empty($res_iforscreen_box)){
                    $restaurant_user_dinner_zxhdnum = count($res_iforscreen_box);
                }

                $add_data = array('area_id'=>$hv['area_id'],'area_name'=>$hv['area_name'],'hotel_id'=>$hv['hotel_id'],'hotel_name'=>$hv['hotel_name'],
                    'hotel_box_type'=>$hv['hotel_box_type'],'is_4g'=>$hv['is_4g'],'is_5g'=>$hv['is_5g'],'hotel_level'=>$hv['hotel_level'],'trainer_id'=>$hv['trainer_id'],'train_date'=>$hv['train_date'],
                    'maintainer_id'=>$hv['maintainer_id'],'tech_maintainer'=>$hv['tech_maintainer'],'box_num'=>$box_num,'faultbox_num'=>$faultbox_num,'normalbox_num'=>$normalbox_num,
                    'fault_rate'=>$fault_rate,'lostbox_num'=>$lost_boxnum,'lostfault_rate'=>$lostfault_rate,'wlnum'=>$wlnum,'wl_hdnum'=>$wl_hdnum,'heart_num'=>$heart_num,'avg_down_speed'=>$avg_down_speed,
                    'user_lunch_zxhdnum'=>$user_lunch_zxhdnum,'lunch_zxhdnum'=>$lunch_zxhdnum,'user_lunch_cvr'=>$user_lunch_cvr,'user_dinner_zxhdnum'=>$user_dinner_zxhdnum,
                    'dinner_zxhdnum'=>$dinner_zxhdnum,'user_dinner_cvr'=>$user_dinner_cvr,'sale_lunch_zxhdnum'=>$sale_lunch_zxhdnum,'sale_dinner_zxhdnum'=>$sale_dinner_zxhdnum,
                    'sale_lunch_cvr'=>$sale_lunch_cvr,'sale_dinner_cvr'=>$sale_dinner_cvr,'lunch_zxnum'=>$lunch_zxnum,'dinner_zxnum'=>$dinner_zxnum,
                    'lunch_zxrate'=>$lunch_zxrate,'dinner_zxrate'=>$dinner_zxrate,'zxnum'=>$zxnum,'zxrate'=>$zxrate,
                    'scancode_num'=>$scancode_num,'user_num'=>$user_num,'interact_num'=>$interact_num,'user_lunch_interact_num'=>$user_lunch_interact_num,
                    'user_dinner_interact_num'=>$user_dinner_interact_num,'interact_standard_num'=>$interact_standard_num,
                    'interact_mini_num'=>$interact_mini_num,'interact_sale_num'=>$interact_sale_num,'interact_sale_signnum'=>$interact_sale_signnum,
                    'interact_game_num'=>$interact_game_num,'meal_heart_num'=>$meal_heart_num,'room_heart_num'=>$room_heart_num,'room_meal_heart_num'=>$room_meal_heart_num,
                    'restaurant_user_num'=>$restaurant_user_num,'restaurant_scancode_num'=>$restaurant_scancode_num,'restaurant_interact_standard_num'=>$restaurant_interact_standard_num,
                    'restaurant_user_lunch_zxhdnum'=>$restaurant_user_lunch_zxhdnum,'restaurant_user_dinner_zxhdnum'=>$restaurant_user_dinner_zxhdnum,
                    'static_date'=>$static_date
                );
                if($hv['trainer_id']){
                    $res_user = $m_sysuser->getUserInfo($hv['trainer_id']);
                    $add_data['trainer'] = $res_user['remark'];
                }
                if($hv['maintainer_id']){
                    $res_user = $m_sysuser->getUserInfo($hv['maintainer_id']);
                    $add_data['maintainer'] = $res_user['remark'];
                }

                $this->add($add_data);
//                echo "hotel_id:$hotel_id ok \r\n";
            }
            echo "date:$static_date ok \r\n";
        }
    }

}