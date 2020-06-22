<?php
namespace Admin\Controller;

use Common\Lib\Aliyun;
use Think\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Common\Lib\SavorRedis;
use Common\Lib\AliyunMsn;

// use Common\Lib\SavorRedis;
/**
 * @desc 功能测试类
 *
 */
class TestController extends Controller {
    public function clearCollect(){
    	exit();
    	$sql ="SELECT a.*,b.status pub_status FROM `savor_smallapp_collect` a left join savor_smallapp_public b on a.res_id= b.forscreen_id where a.type=2 and a.status=1 and b.status!=2 ";
    	$data = M()->query($sql);
    	$flag = 0;
    	foreach($data as $key=>$v){
    		$sql = "update `savor_smallapp_collect` set status=0 where id=".$v['id']." limit 1";
    		$ret = M()->execute($sql);
    		if($ret){
    			$flag ++;
    		}
    	}
    	echo $flag;
    }
    public function pltozj(){
        exit();
        $sql ="SELECT hotel_id FROM `savor_smallapp_hotelgoods` WHERE goods_id=144 ";
        $list = M()->query($sql);
        
        $ids = array(55,137,142,143,145);
        $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
        foreach($ids as $v){
            
            foreach($list as $key=>$vv){
                $data['goods_id'] = $v;
                $data['hotel_id'] = $vv['hotel_id'];
                $ret = $m_hotelgoods->addData($data);
                
            }
        }
        echo 'ok';
    }
    public function tasktozj(){
        $sql ="SELECT hotel_id FROM `savor_smallapp_hotelgoods` WHERE goods_id=144 ";
        $list = M()->query($sql);
        
        $m_task_hotel = new \Admin\Model\Integral\TaskHotelModel();
        foreach($list as $key=>$vv){
            $data['task_id']  = 5;
            $data['hotel_id'] = $vv['hotel_id'];
            $data['uid']      = 1;
            
            $m_task_hotel->addData($data);
        }
        echo "ok";exit;
    }
    
    //销售端用户数据平移
    public function getSmallSaleHotel(){
        $sql ="SELECT * FROM `savor_hotel_invite_code` WHERE openid!='' and type=2 group by hotel_id";
        $hotel_list = M()->query($sql);
        print_r($hotel_list);
    }
    public function ttps(){
        exit();
        $sql ="SELECT user.*,ic.hotel_id,hotel.name FROM savor_hotel_invite_code ic  
            left join `savor_smallapp_user` user
             on user.openid=ic.openid
               left join savor_hotel hotel on ic.hotel_id=hotel.id WHERE `small_app_id`=5 and ic.state=1 and ic.flag=0";
        $user_list = M()->query($sql);
        $tmp = $aps = [];
        foreach($user_list as $key=>$v){
            if($v['hotel_id']){
                $sql ="select mt.*,staff.id parent_id from savor_integral_merchant mt
                        left join savor_integral_merchant_staff staff on mt.id=staff.merchant_id
                       where mt.hotel_id=".$v['hotel_id'].' and mt.status=1 and mt.id !=92';
                
                $mt_info = M()->query($sql);
                //print_r($mt_info);exit;
                //print_r($v);exit;
                
                if(empty($mt_info)){
                    $tmp[$key]['name'] = $v['name'];
                    $tmp[$key]['hotel_id'] = $v['hotel_id'];
                    $tmp[$key]['nickname'] = $v['nickname'];
                    $tmp[$key]['openid'] = $v['openid'];
                }else {
                    $sql ="select * from savor_integral_merchant_staff where openid='".$v['openid']."'";
                    $s_info = M()->query($sql);
                    if(empty($s_info)){
                        $le_staff_arr[$key]['merchant_id'] = $mt_info[0]['id'];
                        $le_staff_arr[$key]['parent_id']   = $mt_info[0]['parent_id'];
                        $le_staff_arr[$key]['name']        = '';
                        $le_staff_arr[$key]['openid']      = $v['openid'];
                        $le_staff_arr[$key]['beinvited_time'] = date('Y-m-d H:i:s');
                        $le_staff_arr[$key]['trees']       = '';
                        $le_staff_arr[$key]['level']       = 2;
                        $le_staff_arr[$key]['sysuser_id']  = 1;
                        $le_staff_arr[$key]['status']      = 1;
                    }
                    
                }
            }else {
                
            }
            
        }
        $flag = 0;
        //print_r($le_staff_arr);exit;
        $m_staff = new \Admin\Model\Integral\StaffModel();
        foreach($le_staff_arr as $key=>$v){
            //print_r($v);exit;
            $ret = $m_staff->addData($v);
            if($ret){
                $flag ++;
            }
        }
        
        print_r($flag);
        echo "ok";exit;
    }
    public function pySmallSaleUser(){
        exit();
        $sql ="select a.* from `savor_hotel_invite_code` a 
               left join savor_smallapp_user u on a.openid=u.openid 
               where a.openid !='' and a.type=2 and a.invite_id=0 
               and a.state=1 and a.flag=0 and u.small_app_id=5 ";
        $user_list = M()->query($sql);
        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $m_staff = new \Admin\Model\Integral\StaffModel();
        
        foreach($user_list as $key=>$v){
            //查看当前酒楼是否有管理员
            $sql ="select * from savor_integral_merchant where hotel_id=".$v['hotel_id']." and status=1";
            $where = [];
            $where['a.hotel_id'] = $v['hotel_id'];
            $where['a.status']   = 1;
            
            
            $mt_info = $m_merchant->alias('a')
                                  ->join('savor_integral_merchant_staff st on a.id=st.merchant_id','left')
                                  ->field('a.*,st.id parent_id')
                                  ->where($where)->find();
            if(empty($mt_info)){
                $data = [];
                $data['hotel_id'] = $v['hotel_id'];
                $data['service_model_id'] = 1;
                $data['channel_id'] = 1;
                $data['rate_groupid'] = 100;
                $data['cash_rate'] = 1.0;
                $data['recharge_rate'] = 1.0;
                $data['name'] = '';
                $data['job']  = '';
                $data['code'] = $v['code'];
                $data['mobile'] = $v['bind_mobile'];
                $data['type'] = 2;
                $data['sysuser_id'] = 1;
                $data['status'] = 1;
                $mt_id = $m_merchant->addData($data);
                $data = [];
                $data['merchant_id'] = $mt_id;
                $data['parent_id']   = 0;
                $data['name'] = '';
                $data['openid'] = $v['openid'];
                $data['beinvited_time'] = date('Y-m-d H:i:s');
                $data['trees'] = '';
                $data['level'] = 1;
                $data['sysuser_id'] =1;
                $data['status'] = 1;
                $staff_id = $m_staff->addData($data);
                
                
                //获取该用户下的员工列表 插入员工表
                $sql ="select * from `savor_hotel_invite_code` where openid !='' and type=2  and invite_id=".$v['id'].' and state=1 and flag=0';
                $le_staff = M()->query($sql);
                $le_staff_arr = [];
                foreach($le_staff  as $kk=>$vv){
                    $le_staff_arr[$kk]['merchant_id'] = $mt_id;
                    $le_staff_arr[$kk]['parent_id']   = $staff_id;
                    $le_staff_arr[$kk]['name']        = '';
                    $le_staff_arr[$kk]['openid']      = $vv['openid'];
                    $le_staff_arr[$kk]['beinvited_time'] = date('Y-m-d H:i:s');
                    $le_staff_arr[$kk]['trees']       = '';
                    $le_staff_arr[$kk]['level']       = 2;
                    $le_staff_arr[$kk]['sysuser_id']  = 1;
                    $le_staff_arr[$kk]['status']      = 1;
                }
                $m_staff->addAll($le_staff_arr);
            }else {//如果已建立该商家
                
                $data = [];
                $data['merchant_id'] = $mt_info['id'];
                $data['parent_id']   = $mt_info['parent_id'];
                $data['name'] = '';
                $data['openid'] = $v['openid'];
                $data['beinvited_time'] = date('Y-m-d H:i:s');
                $data['trees'] = '';
                $data['level'] = 2;
                $data['sysuser_id'] =1;
                $data['status'] = 1;
                $staff_id = $m_staff->addData($data);
            }  
        }
        
        /* $sql ="select a.* from `savor_hotel_invite_code` a
               left join savor_smallapp_user u on a.openid=u.openid
               where a.openid !='' and a.type=3 and a.invite_id=0
               and a.state=1 and a.flag=0 and u.small_app_id=5 ";
        $user_list = M()->query($sql);
        foreach($user_list as $key=>$v){
            $data = [];
            $data['merchant_id'] = 3;
            $data['parent_id']   = 1;
            $data['name'] = '';
            $data['openid'] = $v['openid'];
            $data['beinvited_time'] = date('Y-m-d H:i:s');
            $data['trees'] = '';
            $data['level'] = 0;
            $data['sysuser_id'] =1;
            $data['status'] = 1;
            $staff_id = $m_staff->addData($data);
        } */
        
        echo "OK";
    }
    
    
    public function rmvCache(){
        exit;
        $redis = SavorRedis::getInstance();
        $redis->select(10);
        $keys = "vsmall:ads:*";
        $k_arr = $redis->keys($keys);
        $flag = 0;
        foreach ($k_arr as $v){
            
            $tt = $redis->remove($v);
            if($tt){
                $flag ++;
            }
            //echo $flag;exit;
        }
        echo $flag;exit; 
    }
    public function getJjInfo(){
        $box_mac =  I('box_mac');
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $k = C('SAPP_SIMPLE_UPLOAD_RESOUCE').$box_mac;
        $rets = $redis->lgetrange($k,0,-1);
        $data = array();
        foreach($rets as $key=>$v){
            $data[$key] = json_decode($v,true);
        }
        print_r($data);
    }
    public function openCloseQrcode(){
        $is_close = I('is_close','0','intval');
        //echo $is_close;exit;
        if(!in_array($is_close,array(0,1))){
            exit('参数错误');
        }
        $redis = SavorRedis::getInstance();
        $redis->select(15);
        $sql = "select box.* from savor_box box
                left join savor_room room on box.room_id=room.id
                left join savor_hotel hotel on room.hotel_id=hotel.id
                where hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0 and hotel.area_id=1";
        $data = M()->query($sql);
        $flag = 0;
        foreach($data as $key=>$v){
        
            $sql ="update savor_box set is_sapp_forscreen=$is_close,is_open_simple=$is_close where id=".$v['id'].' limit 1';
            //echo $sql;exit;
            M()->execute($sql);
        
            $box_info = array();
            $box_id = $v['id'];
            $box_info['id']      = $v['id'];
            $box_info['room_id'] = $v['room_id'];
            $box_info['name']    = $v['name'];
            $box_info['mac']     = $v['mac'];
            $box_info['switch_time'] = $v['switch_time'];
            $box_info['volum']   = $v['volum'];
            $box_info['tag']     = $v['tag'];
            $box_info['device_token'] = $v['device_token'];
            $box_info['state']   = $v['state'];
            $box_info['flag']    = $v['flag'];
            $box_info['create_time'] = $v['create_time'];
            $box_info['update_time'] = $v['update_time'];
            $box_info['adv_mach']    = $v['adv_mach'];
            $box_info['tpmedia_id']  = $v['tpmedia_id'];
            $box_info['qrcode_type'] = $v['qrcode_type'];
            $box_info['is_sapp_forscreen'] = $is_close;
            $box_info['is_4g']       = $v['is_4g'];
            $box_info['box_type']    = $v['box_type'];
            $box_info['wifi_name']   = $v['wifi_name'];
            $box_info['wifi_password']=$v['wifi_password'];
            $box_info['wifi_mac']    = $v['wifi_mac'];
            $box_info['is_open_simple'] = $is_close;
            $box_info['is_open_interactscreenad'] = $v['is_open_interactscreenad'];
            $box_info['is_open_signin'] = $v['is_open_signin'];
            $box_cache_key = C('DB_PREFIX').'box_'.$box_id;
            $redis->set($box_cache_key, json_encode($box_info));
            $flag++;
        }
        if($is_close==0){
            echo "北京地区已全部关闭";
        }else {
            echo "北京地区已全部打开";
        }
        
    }
    public function removeHotelinfoCache(){
        $redis = SavorRedis::getInstance();
        $redis->select(15);

        $sql ="select * from savor_hotel hotel 
               
               ";
        $data = M()->query($sql);
        $data = array();
        foreach($data  as $key=>$v){
            $hotel_info = array();
            $hotel_ext_info = array();
            $hotel_id = $v['id'];
            $hotel_info['name']      = $v['name'];
            $hotel_info['addr']      = $v['addr'];
            $hotel_info['area_id']   = $v['area_id'];
            $hotel_info['county_id'] = $v['county_id'];
            $hotel_info['media_id']  = $v['media_id'];
            $hotel_info['contractor']= $v['contractor'];
            $hotel_info['mobile']    = $v['mobile'];
            $hotel_info['tel']       = $v['tel'];
            $hotel_info['maintainer']= $v['maintainer'];
            $hotel_info['level']     = $v['level'];
            $hotel_info['iskey']     = $v['iskey'];
            $hotel_info['install_date'] = $v['install_date'];
            $hotel_info['state']     = $v['state'];
            $hotel_info['state_change_reason'] = $v['state_change_reason'];
            $hotel_info['gps']       = $v['gps'];
            $hotel_info['remark']    = $v['remark'];
            $hotel_info['hotel_box_type'] = $v['hotel_box_type'];
            $hotel_info['create_time']=$v['create_time'];
            $hotel_info['update_time']=$v['update_time'];
            $hotel_info['flag']      = $v['flag'];
            $hotel_info['tech_maintainer'] = $v['tech_maintainer'];
            $hotel_info['remote_id'] = $v['remote_id'];
            $hotel_info['hotel_wifi']= $v['hotel_wifi'];
            $hotel_info['hotel_wifi_pas'] = $v['hotel_wifi_pas'];
            $hotel_info['bill_per']  = $v['bill_per'];
            $hotel_info['collection_company'] = $v['collection_company'];
            $hotel_info['bank_account'] = $v['bank_account'];
            $hotel_info['bank_name'] = $v['bank_name'];
            $hotel_info['is_4g']     = $v['is_4g'];
            $hotel_cache_key = C('DB_PREFIX').'hotel_'.$hotel_id;
            $redis->set($hotel_cache_key, json_encode($hotel_info));

            /* $hotel_ext_info['mac_addr'] = $v['mac_addr'];
            $hotel_ext_info['ip_local'] = $v['ip_local'];
            $hotel_ext_info['ip']       = $v['ip'];
            $hotel_ext_info['server_location'] = $v['server_location'];
            $hotel_ext_info['tag']      = $v['tag'];
            $hotel_ext_info['hotel_id'] = $v['hotel_id'];
            $hotel_ext_info['is_open_customer'] = $v['is_open_customer'];
            $hotel_ext_info['maintainer_id'] = $v['maintainer_id'];
            $hotel_ext_info['adplay_num'] = $v['adplay_num'];
            $hotel_ext_info['food_style_id'] = $v['food_style_id'];
            $hotel_ext_info['avg_expense']= $v['avg_expense'];
            $hotel_ext_info['hotel_cover_media_id'] = $v['hotel_cover_media_id'];
            $hotel_ext_info['contract_expiretime']  = $v['contract_expiretime'];
            $hotel_ext_cache_key = C('DB_PREFIX').'hotel_ext_'.$hotel_id;
            $redis->set($hotel_ext_cache_key, json_encode($hotel_ext_info)); */


        }
        $sql ="select * from savor_hotel_ext 
        
               ";
        $data = M()->query($sql);
        $data = array();
        foreach ($data as $key=>$v){
             $hotel_id = $v['hotel_id'];
             $hotel_ext_info = array();
             $hotel_ext_info['mac_addr'] = $v['mac_addr'];
             $hotel_ext_info['ip_local'] = $v['ip_local'];
             $hotel_ext_info['ip']       = $v['ip'];
             $hotel_ext_info['server_location'] = $v['server_location'];
             $hotel_ext_info['tag']      = $v['tag'];
             $hotel_ext_info['hotel_id'] = $v['hotel_id'];
             $hotel_ext_info['is_open_customer'] = $v['is_open_customer'];
             $hotel_ext_info['is_open_integral'] = $v['is_open_integral'];
             $hotel_ext_info['maintainer_id'] = $v['maintainer_id'];
             $hotel_ext_info['adplay_num'] = $v['adplay_num'];
             $hotel_ext_info['food_style_id'] = $v['food_style_id'];
             $hotel_ext_info['avg_expense']= $v['avg_expense'];
             $hotel_ext_info['hotel_cover_media_id'] = $v['hotel_cover_media_id'];
             $hotel_ext_info['contract_expiretime']  = $v['contract_expiretime'];
             $hotel_ext_info['activity_contact']     = $v['activity_contact'];
             $hotel_ext_info['activity_phone']       = $v['activity_phone'];
             $hotel_ext_cache_key = C('DB_PREFIX').'hotel_ext_'.$hotel_id;
             $redis->set($hotel_ext_cache_key, json_encode($hotel_ext_info));
        }

        //包间
        $sql  = "select * from savor_room ";
        $data = M()->query($sql);
        $data = array();
        foreach($data as $key=>$v){
            $room_info = array();
            $room_id  = $v['id'];
            $room_info['id'] = $v['id'];
            $room_info['hotel_id'] = $v['hotel_id'];
            $room_info['name']     = $v['name'];
            $room_info['type']     = $v['type'];
            $room_info['remark']   = $v['remark'];
            $room_info['probe']    = $v['probe'];
            $room_info['create_time'] = $v['create_time'];
            $room_info['update_time'] = $v['update_time'];
            $room_info['flag']     = $v['flag'];
            $room_info['state']    = $v['state'];
            $room_cache_key =   C('DB_PREFIX').'room_'.$room_id;
            $redis->set($room_cache_key, json_encode($room_info));

        }
        //$sql = "select * from savor_box ";
        $sql = "select box.* from savor_box box
                left join savor_room room on box.room_id=room.id
                left join savor_hotel hotel on room.hotel_id=hotel.id
                where hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0 and hotel.area_id=1";
        $data = M()->query($sql);
        $flag = 0;
        $data = array();
        foreach($data as $key=>$v){

            $sql ="update savor_box set switch_time=30 where id=".$v['id'].' limit 1';
            //echo $sql;exit;
            M()->execute($sql);

            $box_info = array();
            $box_id = $v['id'];
            $box_info['id']      = $v['id'];
            $box_info['room_id'] = $v['room_id'];
            $box_info['name']    = $v['name'];
            $box_info['mac']     = $v['mac'];
            $box_info['switch_time'] = 30;
            $box_info['volum']   = $v['volum'];
            $box_info['tag']     = $v['tag'];
            $box_info['device_token'] = $v['device_token'];
            $box_info['state']   = $v['state'];
            $box_info['flag']    = $v['flag'];
            $box_info['create_time'] = $v['create_time'];
            $box_info['update_time'] = $v['update_time'];
            $box_info['adv_mach']    = $v['adv_mach'];
            $box_info['tpmedia_id']  = $v['tpmedia_id'];
            $box_info['qrcode_type'] = $v['qrcode_type'];
            $box_info['is_sapp_forscreen'] = $v['is_sapp_forscreen'];
            $box_info['is_4g']       = $v['is_4g'];
            $box_info['box_type']    = $v['box_type'];
            $box_info['wifi_name']   = $v['wifi_name'];
            $box_info['wifi_password']=$v['wifi_password'];
            $box_info['wifi_mac']    = $v['wifi_mac'];
            $box_info['is_open_simple'] = $v['is_open_simple'];
            $box_info['is_open_interactscreenad'] = $v['is_open_interactscreenad'];
            $box_info['is_open_signin'] = $v['is_open_signin'];
            $box_cache_key = C('DB_PREFIX').'box_'.$box_id;
            $redis->set($box_cache_key, json_encode($box_info));
            $flag++;
        }
        echo "ok";
    }

    public function forscreenboxcache(){
        exit;
        $redis = SavorRedis::getInstance();
        $redis->select(15);

        $sql = "select box.* from savor_box box
                left join savor_room room on box.room_id=room.id
                left join savor_hotel hotel on room.hotel_id=hotel.id
                where hotel.area_id=236 and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0";
//        $sql = "SELECT box.*FROM savor_box box LEFT JOIN savor_room room ON box.room_id=room.id LEFT JOIN savor_hotel hotel ON room.hotel_id=hotel.id WHERE hotel.state=1 AND hotel.flag=0 AND box.state=1 AND box.flag=0 AND box.mac IN (SELECT box_mac FROM savor_smallapp_forscreen_record WHERE small_app_id IN (2,3) AND create_time>='2019-10-01 00:00:00' AND create_time<='2019-12-10 13:00:00' GROUP BY box_mac)";

        $data = M()->query($sql);
        $flag = 0;
        foreach($data as $key=>$v){
//            if($v['is_open_simple']==1 && $v['is_sapp_forscreen']==0){
//                $v['is_sapp_forscreen'] = 1;
//            }elseif($v['is_open_simple']==1 && $v['is_sapp_forscreen']==1){
//                $v['is_open_simple'] = 0;
//            }
            $v['is_sapp_forscreen'] = 1;
            $v['is_open_simple'] = 0;


            $is_open_simple = $v['is_open_simple'];
            $is_sapp_forscreen = $v['is_sapp_forscreen'];
            $sql ="update savor_box set is_open_simple=$is_open_simple,is_sapp_forscreen=$is_sapp_forscreen where id=".$v['id'].' limit 1';
            M()->execute($sql);

            $box_info = array();
            $box_id = $v['id'];
            $box_info['id']      = $v['id'];
            $box_info['room_id'] = $v['room_id'];
            $box_info['name']    = $v['name'];
            $box_info['mac']     = $v['mac'];
            $box_info['switch_time'] = $v['switch_time'];
            $box_info['volum']   = $v['volum'];
            $box_info['tag']     = $v['tag'];
            $box_info['device_token'] = $v['device_token'];
            $box_info['state']   = $v['state'];
            $box_info['flag']    = $v['flag'];
            $box_info['create_time'] = $v['create_time'];
            $box_info['update_time'] = $v['update_time'];
            $box_info['adv_mach']    = $v['adv_mach'];
            $box_info['tpmedia_id']  = $v['tpmedia_id'];
            $box_info['qrcode_type'] = $v['qrcode_type'];
            $box_info['is_sapp_forscreen'] = $v['is_sapp_forscreen'];
            $box_info['is_4g']       = $v['is_4g'];
            $box_info['box_type']    = $v['box_type'];
            $box_info['wifi_name']   = $v['wifi_name'];
            $box_info['wifi_password']=$v['wifi_password'];
            $box_info['wifi_mac']    = $v['wifi_mac'];
            $box_info['is_open_simple'] = $v['is_open_simple'];
            $box_info['is_open_interactscreenad'] = $v['is_open_interactscreenad'];
            $box_info['is_open_signin'] = $v['is_open_signin'];
            $box_cache_key = C('DB_PREFIX').'box_'.$box_id;
            $redis->set($box_cache_key, json_encode($box_info));

            if(empty($v['mac'])){
                continue;
            }
            $res_box = $v;
            $forscreen_type = 1;//1外网(主干) 2直连(极简)
            if(!empty($res_box)){
                $box_forscreen = "{$res_box['is_sapp_forscreen']}-{$res_box['is_open_simple']}";
                switch ($box_forscreen){
                    case '1-0':
                        $forscreen_type = 1;
                        break;
                    case '0-1':
                        $forscreen_type = 2;
                        break;
                    case '1-1':
                        if(in_array($res_box['box_type'],array(3,6))){
                            $forscreen_type = 2;
                        }elseif($res_box['box_type']==2){
                            $forscreen_type = 1;
                        }
                        break;
                    default:
                        $forscreen_type = 1;
                }
                $redis->select(14);
                $box_mac = $res_box['mac'];
                $box_key = "box:forscreentype:$box_mac";
                $forscreen_info = array('box_id'=>$box_id,'forscreen_type'=>$forscreen_type);
                $redis->set($box_key,json_encode($forscreen_info));
                echo "box_id:$box_id \r\n";
            }
            $flag++;
        }
        echo "total:$flag ok";
    }

    public function ttss(){
        $redis = SavorRedis::getInstance();
        $redis->select(10);
    }

    /*
     * 针对某个城市开启某个聚屏广告位
     */
    public function genPolyadsboxHotelInfoCache(){
        $sql ="select box.* ,hotel.id hotel_id,hotel.area_id from savor_box box 
               left join savor_room  room on box.room_id=room.id
               left join savor_hotel hotel on room.hotel_id = hotel.id
               where 1 and hotel.hotel_box_type in(2,3,6)
               and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0
               ";
        $data = M()->query($sql);
        $redis = SavorRedis::getInstance();
        $redis->select(15);
        $flag = 0;
        foreach($data as $key=>$v){
            $rt = false;
            $tpmedia_ids = '';
            $tpmedia_id_arr = explode(',',$v['tpmedia_id']);
            if(empty($tpmedia_id_arr)){
                $tpmedia_id_arr = array();
            }
            if($v['area_id']==246 && !in_array($v['hotel_id'],array(830,829,828,823))){
                $tpmedia_ids = 6;
                $sql =  "update savor_box set tpmedia_id='{$tpmedia_ids}' where id=".$v['id']." limit 1";
                $rt = M()->execute($sql);
//                if(!in_array(6,$tpmedia_id_arr)){
//                    $tpmedia_id_arr[] = 6;
//                    $tpmedia_ids = join(',',$tpmedia_id_arr);
//                    $sql =  "update savor_box set tpmedia_id='{$tpmedia_ids}' where id=".$v['id']." limit 1";
//                    $rt = M()->execute($sql);
//                }
            }else{
                $position_6 = array_search(6,$tpmedia_id_arr);
                if($position_6){
                    unset($tpmedia_id_arr[$position_6]);
                    $tpmedia_ids = join(',',$tpmedia_id_arr);
                    $sql =  "update savor_box set tpmedia_id='{$tpmedia_ids}' where id=".$v['id']." limit 1";
                    $rt = M()->execute($sql);
                }
            }
            if($rt){
                $box_info = array();
                $box_id = $v['id'];
                $box_info['id']      = $v['id'];
                $box_info['room_id'] = $v['room_id'];
                $box_info['name']    = $v['name'];
                $box_info['mac']     = $v['mac'];
                $box_info['switch_time'] = $v['switch_time'];
                $box_info['volum']   = $v['volum'];
                $box_info['tag']     = $v['tag'];
                $box_info['device_token'] = $v['device_token'];
                $box_info['state']   = $v['state'];
                $box_info['flag']    = $v['flag'];
                $box_info['create_time'] = $v['create_time'];
                $box_info['update_time'] = $v['update_time'];
                $box_info['adv_mach']    = $v['adv_mach'];

                if($tpmedia_ids){
                    $box_info['tpmedia_id']  = $tpmedia_ids;
                }

                $box_info['qrcode_type'] = 2;
                $box_info['is_sapp_forscreen'] = $v['is_sapp_forscreen'];
                $box_info['is_4g']       = $v['is_4g'];
                $box_info['box_type']    = $v['box_type'];
                $box_info['wifi_name']   = $v['wifi_name'];
                $box_info['wifi_password']=$v['wifi_password'];
                $box_info['wifi_mac']    = $v['wifi_mac'];
                $box_info['is_open_simple'] = $v['is_open_simple'];
                $box_info['is_open_interactscreenad'] = $v['is_open_interactscreenad'];
                $box_cache_key = C('DB_PREFIX').'box_'.$box_id;
                $redis->set($box_cache_key, json_encode($box_info));
                $flag++;
            }
        }
        echo $flag;
    }
    /**
     * @去掉某些酒楼版位的易售广告
     */
    public function removePolyadsboxHotelInfoCache(){
        exit();
        $sql ="select box.* ,hotel.id hotel_id,hotel.area_id from savor_box box
               left join savor_room  room on box.room_id=room.id
               left join savor_hotel hotel on room.hotel_id = hotel.id
               where 1 and hotel.hotel_box_type in(2,3,6)
               and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0
               and hotel.id in(680,199,464,431,435,206,461,463,460,243,470,867,434,433,618,349,631,352,354,356,351,359,845,482) and FIND_IN_SET('6', box.tpmedia_id);";
        $data = M()->query($sql);
        $redis = SavorRedis::getInstance();
        $redis->select(15);
        $flag = 0;
        foreach($data as $key=>$v){
            $rt = false;
            $tpmedia_ids = '';
            $tpmedia_id_arr = explode(',',$v['tpmedia_id']);
            if(empty($tpmedia_id_arr)){
                $tpmedia_id_arr = array();
            }
            $position_6 = array_search(6,$tpmedia_id_arr);
            
            if($position_6 >=0){
                    unset($tpmedia_id_arr[$position_6]);
                    $tpmedia_ids = join(',',$tpmedia_id_arr);
                    
                    $sql =  "update savor_box set tpmedia_id='{$tpmedia_ids}' where id=".$v['id']." limit 1";
                    $rt = M()->execute($sql);
            }
            
            if($rt){
                $box_info = array();
                $box_id = $v['id'];
                $box_info['id']      = $v['id'];
                $box_info['room_id'] = $v['room_id'];
                $box_info['name']    = $v['name'];
                $box_info['mac']     = $v['mac'];
                $box_info['switch_time'] = $v['switch_time'];
                $box_info['volum']   = $v['volum'];
                $box_info['tag']     = $v['tag'];
                $box_info['device_token'] = $v['device_token'];
                $box_info['state']   = $v['state'];
                $box_info['flag']    = $v['flag'];
                $box_info['create_time'] = $v['create_time'];
                $box_info['update_time'] = $v['update_time'];
                $box_info['adv_mach']    = $v['adv_mach'];
            
                if($tpmedia_ids){
                    $box_info['tpmedia_id']  = $tpmedia_ids;
                }
            
                $box_info['qrcode_type'] = $v['qrcode_type'];
                $box_info['is_sapp_forscreen'] = $v['is_sapp_forscreen'];
                $box_info['is_4g']       = $v['is_4g'];
                $box_info['box_type']    = $v['box_type'];
                $box_info['wifi_name']   = $v['wifi_name'];
                $box_info['wifi_password']=$v['wifi_password'];
                $box_info['wifi_mac']    = $v['wifi_mac'];
                $box_info['is_open_simple'] = $v['is_open_simple'];
                $box_info['is_open_interactscreenad'] = $v['is_open_interactscreenad'];
                $box_cache_key = C('DB_PREFIX').'box_'.$box_id;
                $redis->set($box_cache_key, json_encode($box_info));
                $flag++;
            }
        }
        echo $flag;exit;
    }

    public function genHotelInfoCache(){
        //'848','679','833','827','315','340','601','392','421','418'  第一批
        //'258','267','273','327','328','766','818','618','445','601','690','676','293','266','60','837','839','795','9','85','89','555','18','46','549','678','685','28','116','129','896','147','552','742','878','177','223','833','186','827','34','563','763','238'
        //199 464 431 435 206 461 463 460 243 470 867 434 433   第三批 关闭聚屏广告
        //网络版版位 展示二维码
        $sql ="select box.* ,hotel.id hotel_id from savor_box box 
               left join savor_room  room on box.room_id=room.id
               left join savor_hotel hotel on room.hotel_id = hotel.id
               where 1 and hotel.hotel_box_type in(2,3,6)
               and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0
               ";
        $data = M()->query($sql);
        //print_r($data);exit;
        $redis = SavorRedis::getInstance();
        $redis->select(15);
        $flag = 0;
        foreach($data as $key=>$v){
//            $sql =  "update savor_box set tpmedia_id='1,2,3,4,5,6' where id=".$v['id']." limit 1";
            $sql  = "update savor_box set qrcode_type=2 where id=".$v['id']." limit 1";
            
            $rt = M()->execute($sql);
            if($rt){
                $box_info = array();
                $box_id = $v['id'];
                $box_info['id']      = $v['id'];
                $box_info['room_id'] = $v['room_id'];
                $box_info['name']    = $v['name'];
                $box_info['mac']     = $v['mac'];
                $box_info['switch_time'] = $v['switch_time'];
                $box_info['volum']   = $v['volum'];
                $box_info['tag']     = $v['tag'];
                $box_info['device_token'] = $v['device_token'];
                $box_info['state']   = $v['state'];
                $box_info['flag']    = $v['flag'];
                $box_info['create_time'] = $v['create_time'];
                $box_info['update_time'] = $v['update_time'];
                $box_info['adv_mach']    = $v['adv_mach'];
                $box_info['tpmedia_id']  = $v['tpmedia_id'];
                $box_info['qrcode_type'] = 2;
                $box_info['is_sapp_forscreen'] = $v['is_sapp_forscreen'];
                $box_info['is_4g']       = $v['is_4g'];
                $box_info['box_type']    = $v['box_type'];
                $box_info['wifi_name']   = $v['wifi_name'];
                $box_info['wifi_password']=$v['wifi_password'];
                $box_info['wifi_mac']    = $v['wifi_mac'];
                $box_info['is_open_simple'] = $v['is_open_simple'];
                $box_info['is_open_interactscreenad'] = $v['is_open_interactscreenad'];
                $box_cache_key = C('DB_PREFIX').'box_'.$box_id;
                $redis->set($box_cache_key, json_encode($box_info));
                $flag++;
            }
            
            
        }
        echo $flag;
        
    }
    
    
    public function openZmtmpid(){
        exit(1);
        $m_box = new \Admin\Model\BoxModel();
        $sql =" select box.id
                from savor_box box
                left join savor_room room on box.room_id=room.id
                left join savor_hotel hotel on hotel.id=room.hotel_id
                where hotel.hotel_box_type in(2,3,6) and hotel.flag=0
                and hotel.state=1 and box.flag=0 and box.state=1";
        $data = $m_box->query($sql);
        $flag = 0;
        foreach($data as $key=>$v){
            $sql ="update savor_box set tpmedia_id='1,2,3,4' where id=".$v['id'];
            $rt = M()->execute($sql);
            if($rt){
                $flag ++;
            }    
        } 
        echo $flag;
    }
    
    
    //全量打开网络版机顶盒的互动广告开关
    public function openHdBox(){
        exit(1);
        $m_box = new \Admin\Model\BoxModel();
        $sql ="select box.id,box.is_open_interactscreenad 
               from savor_box box 
               left join savor_room room on box.room_id=room.id 
               left join savor_hotel hotel on hotel.id=room.hotel_id 
               where hotel.hotel_box_type in(2,3,6) and hotel.flag=0 
               and hotel.state=1 and box.flag=0 and box.state=1 and box.is_open_interactscreenad=0 ";
        
        $data = $m_box->query($sql);
        $flag = 0;
        foreach($data as $key=>$v){
            $sql ='update savor_box set is_open_interactscreenad=1 where id='.$v['id']." limit 1";
            
            //echo $sql;exit;
            $rt = M()->execute($sql);
            if($rt){
                $flag ++;
            }
        }
        echo $flag;
    }
    
    
    //打开二代网络  主干版小程序开关
    public function openSecSmallapp(){
        exit(1);
        $m_box = new \Admin\Model\BoxModel();
        $sql =  "SELECT box.id box_id FROM savor_box box
                 left join savor_room room  on box.room_id=room.id
                 left join savor_hotel hotel on room.hotel_id=hotel.id
                 where box.state=1 and box.flag=0 and hotel.flag=0 
                 and hotel.state=1 and hotel.hotel_box_type in(2) 
                 and box.is_sapp_forscreen=0";
        $list = M()->query($sql);
        
        $flag = 0;
        foreach($list as $key=>$v){
            $data['is_sapp_forscreen']  = 1;
            $id = $v['box_id'];
            $ret = $m_box->editData($id, $data);
            if($ret){
                $flag++;
            }
        }
        
        echo $flag;
        
    }
    
    
    
    //打开二代5G、三代网络版酒楼宣传片的 小程序二维码
    public function openAdvQrcode(){
        exit(1);
        $sql ="select id hotel_id,name hotel_name from savor_hotel where state=1 and flag=0 
               and hotel_box_type in(3,6)";
        
        $hotel_list = M()->query($sql);
        $flag = 0;
        
        foreach($hotel_list as $key=>$v){
            /* $sql ="update savor_ads set is_sapp_qrcode=1 where hotel_id=".$v['hotel_id']." and type=3";
            $ret = M()->execute($sql);
            if($ret){
                $flag ++;
            } */
            $sql ="select id from savor_ads where hotel_id=".$v['hotel_id']." and type=3 and is_sapp_qrcode=0";
            $rt = M()->query($sql);
            if(!empty($rt)){
                $ht[] = $v;
            }
        }
        print_r($ht);
    }
    
    
    public function countHdNums(){
        $date = I('date');
        $start_time = $date.' 00:00:00';
        $end_time   = $date.' 23:59:59';
        $sql ="select openid from savor_smallapp_qrcode_log 
               where `create_time`>='".$start_time."' and `create_time`<='".$end_time."' group by openid";
        $data = M()->query($sql);
        
        $scan_code = count($data); //扫码人数
        
        //投屏人数
        $sql ="select a.openid from savor_smallapp_forscreen_record a
               left join savor_smallapp_user  u on a.openid = u.openid
               left join savor_smallapp_game_user gu  on u.mpopenid = gu.openid
               where a.mobile_brand !='devtools' 
               and  a.`create_time`>='".$start_time."' and a.`create_time`<='".$end_time."'
               and a.small_app_id!=4     group by a.openid";
        
        $data = M()->query($sql);
        
        $forscreen_count = count($data);
        echo $date."扫码人数：".$scan_code.",互动人数:".$forscreen_count;
        
        
        
        
    }
    
    public function removeProCach(){
        exit('11111');
        $redis = SavorRedis::getInstance();
        $redis->select(12);
        $keys  = $redis->keys('program_pro_*');
        foreach($keys as $key){
            //echo $key;exit;
            $redis->remove($key);
        }
    }
    
    
    public function closeSmallappJijian(){
        exit(1);
        $m_box = new \Admin\Model\BoxModel();
        $sql =  "SELECT box.id box_id FROM savor_box box
left join savor_room room  on box.room_id=room.id
left join savor_hotel hotel on room.hotel_id=hotel.id
where box.state=1 and box.flag=0 and hotel.flag=0 and hotel.state=1 and hotel.hotel_box_type in(2,3) and box.is_open_simple=1";
        $list = M()->query($sql);
        $flag = 0;
        foreach($list as $key=>$v){
            $data['is_open_simple']  = 0;
            $id = $v['box_id'];
            $ret = $m_box->editData($id, $data);
            if($ret){
                $flag++;
            }
        }
        
        echo $flag;
        
    }
    public function closeSmallappJjPt(){
        exit(1);
        $m_box = new \Admin\Model\BoxModel();
        $sql =  "SELECT box.id box_id FROM savor_box box
left join savor_room room  on box.room_id=room.id
left join savor_hotel hotel on room.hotel_id=hotel.id
where 1 and box.flag=0 and hotel.flag=0 and hotel.state=1 and hotel.hotel_box_type in(2) ";
        
        $list = M()->query($sql);
        $flag = 0;
        foreach($list as $key=>$v){
            $data['is_open_simple']  = 0;
            $data['is_sapp_forscreen'] = 0;
            $id = $v['box_id'];
            $ret = $m_box->editData($id, $data);
            if($ret){
                $flag++;
            }
        }
    
        echo $flag;
    
    }
    //打开三代网络酒楼盒子的极简版开关
    public function openSmallappJijian(){
        exit(1);
        $m_box = new \Admin\Model\BoxModel();
        $where = array();
        $where['box.state'] = 1;
        $where['box.flag']  = 0;
        
        $where['hotel.hotel_box_type'] = array('in','6');
        
        $where['hotel.flag'] = 0;
        $where['hotel.state']= 1;
        $where['box.is_open_simple'] = 0;
        $list = $m_box->alias('box')
        ->join('savor_room room on box.room_id=room.id','left')
        ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
        ->join('savor_area_info area on hotel.area_id=area.id','left')
        ->field('box.wifi_mac,hotel.id,area.region_name,hotel.name,box.id box_id,hotel.hotel_box_type')
        ->where($where)
        ->select();
        $flag = 0;
        foreach($list as $key=>$v){
            $data['is_open_simple']  = 1;
            $id = $v['box_id'];
            $ret = $m_box->editData($id, $data);
            if($ret){
                $flag++;
            }
        }
        
        echo $flag;
        
    }
    
    
    
    

    public function operateH5game(){
        exit;
        $m_game_tree = new \Admin\Model\Smallapp\GameClimbtreeModel();
        echo "da";
        $list = $m_game_tree->alias('a')
                    ->join('savor_smallapp_game_interact b on a.activity_id=b.id','left')
                    ->field('b.box_mac,a.openid,a.create_time')
                    ->where('b.is_start=1')
                    ->select();
        $m_forscreen_log = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $flag = 0;
        foreach($list as $key=>$v){ 
            $data = array();
            $data['openid'] = $v['openid'];
            $data['box_mac']= $v['box_mac'];
            $data['action'] = 101;
            $data['small_app_id'] = 11;
            $data['imgs'] = '[]';
            $data['forscreen_id'] = 0;
            $data['resource_id'] = 0;
            $data['resource_size'] = 0;
            $data['create_time'] = $v['create_time'];
            $ret =$m_forscreen_log->addInfo($data,1);
            if($ret){
                $flag++;
            }
        }
        echo $flag;
    }
    
    
    public function ats(){
        $redis = SavorRedis::getInstance();
        $redis->select(8);
        $list = $redis->get('small_program_list_7');
        $list = json_decode($list,true);
        print_r($list);
        
    }
    //关闭大厅小程序、极简版开关
    public function closeDt(){
        exit('1');
        $m_box = new \Admin\Model\BoxModel();
        $where = array();
        $where['box.state'] = 1;
        $where['box.flag']  = 0;
        $where['room.type'] = array('in','2,3');
        $where['hotel.hotel_box_type'] = array('in','2,3,6');
        $where['hotel.id']  = array('not in','199,464,431,209,435,206,461,463,243,201,470,867,434,433');
        $where['hotel.flag'] = 0;
        $where['hotel.state']= 1;
        //$where['hotel.area_id'] = array('in','236,246');
        //$where['box.is_sapp_forscreen'] = 1;
        $list = $m_box->alias('box')
                      ->join('savor_room room on box.room_id=room.id','left')
                      ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
                      ->join('savor_area_info area on hotel.area_id=area.id','left')
                      ->field('box.wifi_mac,hotel.id,area.region_name,hotel.name,box.id box_id,hotel.hotel_box_type')
                      ->where($where)
                      ->select();
        
        $flag = 0;
        //print_r($list);exit;
        /* foreach($list as $key=>$v){
            $map = $data = array();
            //$map['id'] = $v['box_id'];
            $id = $v['box_id'];
            $data['is_sapp_forscreen'] = 1;
            $data['is_open_simple']  = 1;
            $ret = $m_box->editData($id, $data);
            //echo $m_box->getLastSql();exit;
            if($ret){
                $flag ++;
            }
        } */
        foreach($list as $key=>$v){
            if(empty($v['wifi_mac'])){
                $id = $v['box_id'];
                $data['is_open_simple']  = 0;
                $ret = $m_box->editData($id, $data);
                if($ret){
                    $flag ++;
                }
            }
        }
        echo $flag;
    }
    
    
    public function updateBoxInfo(){
        exit(1);
        $m_box = new \Admin\Model\BoxModel();
        $where = array();
        $where['box.state'] = 1;
        $where['box.flag']  = 0;
        $where['hotel.hotel_box_type'] = array('neq',0);
        $list = $m_box->alias('box')
                      ->join('savor_room room on box.room_id=room.id','left')
                      ->join('savor_hotel hotel on room.hotel_id=hotel.id','left')
                      ->field('box.id box_id,hotel.hotel_box_type')
                      ->where($where)
                      ->select();
        $flag = 0 ;
        foreach($list as $key=>$v){
            $where = array();
            $data  = array();
            $where['id']      = $v['box_id'];
            $data['box_type'] = $v['hotel_box_type']; 
            $ret = $m_box->where($where)->save($data);
            echo $m_box->getLastSql();exit;
            if($ret){
                $flag ++;
            }
        }
        echo $flag;
    }
    
    public function recCollectCount(){
        exit(1);
        $m_public = new \Admin\Model\Smallapp\PublicModel();
        $m_collect_count = new \Admin\Model\Smallapp\CollectCountModel();
        
        $fields = "forscreen_id";
        $where['status'] = 2;
        $limit  = '0,1000';
        $list = $m_public->getWhere($fields, $where,'id desc', $limit);
        print_r($list);exit;
        foreach($list as $key=>$v){
            $where = array();
            $where['res_id'] = $v['forscreen_id'];
            $nums = $m_collect_count->countNum($where);
            if($nums){
                $rand_nums = rand(1, 10);
                $m_collect_count->where($where)->setInc('nums',$rand_nums);
            }else {
                $data = array();
                $data['res_id'] = $v['forscreen_id'];
                $data['type']   = 2;
                $rand_nums = rand(1, 10);
                $data['nums']   = $rand_nums;
                $m_collect_count->addInfo($data);
            }
        }
        echo 'ok';
    }
    public function test(){
        exit('非法进入');
        /* $is_support_netty  = $_GET['is_support_netty'];
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $redis->set('support_netty_balance',$is_support_netty); */
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $m_smallapp_forscreen_record = new \Admin\Model\ForscreenRecordModel();
        
        $cache_key = C('SAPP_BOX_FORSCREEN_NET')."*";
        $keys = $redis->keys($cache_key);
        $flag = 0;
        foreach($keys as $k){
            
            $data = $redis->lgetrange($k, 0, -1);
            
            foreach($data as $v){
                $flag ++;
                $netresource = json_decode($v,true);
                
                $search = array();
                $search['forscreen_id'] = $netresource['forscreen_id'];
                $search['resource_id']  = $netresource['resource_id'];
                $tmp = $m_smallapp_forscreen_record->getOne('id', $search);
                
                if(!empty($tmp)){
                    if($netresource['is_exist']==0){//资源不存在
                        if(!empty($netresource['resource_id']) && !empty($netresource['openid'])){
                            $where = array();
                            $dt = array();
                            //$where['action'] = array('neq',8);
                            $where['forscreen_id'] = $netresource['forscreen_id'];
                            $where['resource_id'] = $netresource['resource_id'];
                            $where['openid'] = $netresource['openid'];
                            if(!empty($netresource['box_res_sdown_time'])){
                                $dt['box_res_sdown_time'] = $netresource['box_res_sdown_time'];
                            }
                            if(!empty($netresource['box_res_edown_time'])){
                                $dt['box_res_edown_time'] = $netresource['box_res_edown_time'];
                            }
                            $dt['is_break'] = $netresource['is_break'];
                            $dt['is_exist'] = $netresource['is_exist'];
                            $dt['update_time'] = date('Y-m-d H:i:s');
                            $ret = $m_smallapp_forscreen_record->updateInfo($where, $dt);
                            $redis->lpop($k);
                        }
                    }else if($netresource['is_exist']==1 || $netresource['is_exist']==2){//资源存在 //资源下载失败
                        $where = $dt = array();
                        //$where['action'] = array('neq',8);
                        $where['forscreen_id'] = $netresource['forscreen_id'];
                        $where['resource_id'] = $netresource['resource_id'];
                        $where['openid'] = $netresource['openid'];
                        $dt['is_break'] = $netresource['is_break'];
                        $dt['is_exist'] = $netresource['is_exist'];
                        $dt['update_time'] = date('Y-m-d H:i:s');
                        $ret = $m_smallapp_forscreen_record->updateInfo($where, $dt);
                        
                        $tt = $redis->lpop($k);
                    }
                }else {
                    $redis->lpop($k);
                }
                $ret = $redis->lgetrange($k,0,-1);
                if(empty($ret)) $redis->remove($k);
            }
        }
        echo $flag ."ddd";
    }
    
    
    
   
    //生成好友关系
    public function smallappFriends(){
        exit('非法进入');
        $hour = date('H');
        //$hour =14;
        if($hour==14){
            $start_time = date('Y-m-d')." 11:00:00";
            $end_time   = date('Y-m-d')." 14:00:00";
        }else{
            $start_time = date('Y-m-d')." 17:00:00";
            $end_time   = date('Y-m-d')." 23:00:00";
        }
        $sql = "select box_mac from savor_smallapp_forscreen_record where create_time>='".$start_time."'
                and create_time<='".$end_time."' and mobile_brand !='devtools' group by box_mac";
        $forscreen_box_arr = M()->query($sql);
        $sql ="select box_mac from savor_smallapp_turntable_log where create_time>='".$start_time."'
                and create_time<='".$end_time."' group by box_mac";
        $turntable_box_arr = M()->query($sql);
        $box_list = array_merge($forscreen_box_arr,$turntable_box_arr);
        $ret = assoc_unique($box_list, 'box_mac');
        $box_list = array_keys($ret);
        $m_user = new \Admin\Model\Smallapp\UserModel();
        foreach($box_list as $v){
            $sql ="select openid from savor_smallapp_forscreen_record where box_mac='".$v."' 
                   and  create_time>='".$start_time."'
                   and create_time<='".$end_time."' and mobile_brand !='devtools' 
                   group by openid";
            //echo $sql;exit;
            $forscreen_openid_arr = M()->query($sql);  //投屏用户
            $sql ="select openid from savor_smallapp_turntable_log where box_mac='".$v."'
                   and  create_time>='".$start_time."'
                   and create_time<='".$end_time."' group by openid";
            $turntab_openid_arr = M()->query($sql);
            $sql ="select a.openid from savor_smallapp_turntable_detail a 
                   left join savor_smallapp_turntable_log b on a.activity_id = b.id
                   where b.box_mac='".$v."'
                   and  a.create_time>='".$start_time."'and a.create_time<='".$end_time."'
                   group by openid";
            $turntab_detail_openid_arr = M()->query($sql);
            $openid_list = array_merge($forscreen_openid_arr,$turntab_openid_arr,$turntab_detail_openid_arr);
            
            $nums = count($openid_list);
            if($nums<=1) continue;
            $openid_list = assoc_unique($openid_list, 'openid');
            $openid_list = array_keys($openid_list);
            if(count($openid_list)<=1) continue;
            $m_friend = new \Admin\Model\Smallapp\FriendModel();
            $f_arr = array();
            $flag=0;
            foreach($openid_list as $ov){
                $sql ="select count(id) as nums from savor_smallapp_user where openid='".$ov."' limit 1";
                $ret = M()->query($sql);
                $user_nums = $ret[0]['nums'];
                if(empty($user_nums)){
                    $sql =" insert into savor_smallapp_user(`openid`) values('".$ov."')";
                    M()->execute($sql);
                }
                foreach($openid_list as $fv){
                    if($ov!=$fv){
                        $sql ="select status from savor_smallapp_friend 
                               where openid='".$ov."' and f_openid='".$fv."'";
                        $ret = M()->query($sql);
                        if(empty($ret)){
                            
                            $f_arr[$flag]['openid'] = $ov;
                            $f_arr[$flag]['f_openid'] = $fv;
                            $f_arr[$flag]['type'] = 1;
                            $f_arr[$flag]['status'] = 1;
                            $flag++;
                            
                        }else {
                            if($ret[0]['status'] ==0){
                                $where = array();
                                $where['openid'] = $ov;
                                $where['f_openid']= $fv;
                                $m_friend->updateInfo($where, array('status'=>1));
                            }
                        }
                    }
                }
            }
            $m_friend->addInfo($f_arr,2);
        }
        echo "ok";
    }
    public function hotelredis(){
        $redis = SavorRedis::getInstance();
        $redis->select(15);
        $cache_key = "savor_hotel_7";
        $info = $redis->get($cache_key);
        $info = json_decode($info,true);
        print_r($info);
   
    }
    public function getHotelinfosByboxmac(){
        $box_mac = I('get.box_mac');
        $sql ="select hotel.name hotel_name,room.name room_name,box.name box_name
               from savor_box box left join savor_room room on box.room_id=room.id
               left join savor_hotel hotel on  room.hotel_id=hotel.id
               where box.mac='".$box_mac."' and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0";
        $data = M()->query($sql);
        print_r($data);
    }
    public function testredis(){
        $redis = SavorRedis::getInstance();
        $redis->select(15);
        $sql ="select * from savor_hotel 
               
               ";
        $data = M()->query($sql);
        $data = array();
        foreach($data as $key=>$v){
            $tmp = $redis->get('savor_hotel_'.$v['id']);
            if($tmp){
                $hotel_info = json_decode($tmp,true);
                if($v['name'] != $hotel_info['name']){
                    echo $v['name'].'savor_hotel_'.$v['id'].$hotel_info['name']."<br>";
                }
            }
        }
        $sql ="select * from savor_hotel_ext ";
        $data = M()->query($sql);
        $data = array();
        foreach($data as $key=>$v){
            $tmp = $redis->get('savor_hotel_ext_'.$v['hotel_id']);
            if($tmp){
                $hotel_ext_info = json_decode($tmp,true);
                if($v['mac_addr']!=$hotel_ext_info['mac_addr']){
                    echo $v['hotel_id']."<br>";
                }
            }
        }
        $sql ="select * from savor_room";
        $data = M()->query($sql);
        $data = array();
        foreach($data as $key=>$v){
            $tmp = $redis->get('savor_room_'.$v['id']);
            if($tmp){
                $room_info = json_decode($tmp,true);
                if($v['name']!=$room_info['name']){
                    echo $v['id']."<br>";
                }
            }
        }
        $sql ="select * from savor_box ";
        $data = M()->query($sql);
        foreach($data as $key=>$v){
            $tmp = $redis->get('savor_box_'.$v['id']);
            if($tmp){
                $box_info = json_decode($tmp,true);
                if($v['name']!=$box_info['name']){
                    echo $v['id'];
                }
            }
        }
        echo "ok";
    }

    public function helpcontent(){
        $id = I('get.id',0,'intval');
        $content_key = C('SAPP_SELECTCONTENT_CONTENT');
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(5);
        $res_cache = $redis->get($content_key);
        if($res_cache){
            $res_data = json_decode($res_cache,true);
            foreach ($res_data as $k=>$v){
                if($v['id']==$id){
                    unset($res_data[$k]);
                }
            }
            print_r($res_data);
            $redis->set($content_key,json_encode($res_data));
        }
    }

    public function saleuser(){
        $sql ="select * from savor_smallapp_user where small_app_id=5";
        $res_user = M()->query($sql);
        foreach ($res_user as $v){
            if(!empty($v['mobile'])){
                $sql_invite = "select * from savor_hotel_invite_code where bind_mobile='{$v['mobile']}'";
                $res_invite = M()->query($sql_invite);
                if(!empty($res_invite)){
                    $id = $res_invite[0]['id'];
                    $openid = $v['openid'];
                    $sql_upinvite = "update savor_hotel_invite_code set openid='$openid' where id=$id";
                    $res = M()->execute($sql_upinvite);
                    if($res){
                        echo "$id ok \r\n";
                    }
                }
            }
        }
    }

    public function getpublic(){
        $sql = "select * from savor_smallapp_public where create_time>='2019-08-18 00:00:00'";
        $res = M()->query($sql);
        foreach ($res as $v){
            $openid = $v['openid'];
            $forscreen_id = $v['forscreen_id'];
            $sql_public = "select * from savor_smallapp_public where forscreen_id='$forscreen_id' and openid='$openid' order by id asc";
            $res_public = M()->query($sql_public);
            if(count($res_public)>1){
                unset($res_public[0]);
                foreach ($res_public as $pv){
                    $sql_unset = "DELETE from savor_smallapp_public where id={$pv['id']}";
                    M()->execute($sql_unset);
                    echo $pv['id'].'==='.$forscreen_id."<br> \r\n";
                }
            }
        }
        echo 'finish';
    }

    public function jd(){
//        $data['promotionCodeReq'] = array(
//            'materialId'=>"http://item.jd.com/1003077.html",
//            'chainType'=>3,
//            'subUnionId'=>'14737',
//        );
//        $res = jd_union_api($data,'jd.union.open.promotion.bysubunionid.get');
//        if($res['code']==200){
//            $click_url = urlencode($res['data']['clickURL']);
//            $jd_config = C('JD_UNION_CONFIG');
//            $page_url = '/pages/proxy/union/union?spreadUrl='.$click_url.'&customerinfo='.$jd_config['customerinfo'];
//        }
//        echo $page_url;
//        exit;

        $data['orderReq'] = array(
            'pageNo'=>1,
            'pageSize'=>500,
            'type'=>1,
            'time'=>'2019091211',
//            'time'=>'2019091615',
        );
        $res = jd_union_api($data,'jd.union.open.order.query');
        print_r($res);
        exit;

    }

    public function decodeoid(){
        $oid = $_REQUEST['oid'];
        $hash_ids_key = 'Q1t80oXSKl';
        $hashids = new \Common\Lib\Hashids($hash_ids_key);
        $decode_info = $hashids->decode($oid);
        echo $decode_info[0];
        exit;
    }

    public function hotelinvitetomerchant(){
        //1.排查绑定多个账号的用户
        $model = M();
        $sql = "select * from savor_hotel_invite_code where type=2 and state=1 and flag=0 and invite_id=0 order by hotel_id asc ";
        $res_hotel_invite = $model->query($sql);
        $hotels = array();
        foreach ($res_hotel_invite as $v){
            $hotels[$v['hotel_id']][]=array('id'=>$v['id'],'bind_mobile'=>$v['bind_mobile']);
        }
//        print_r($hotels);
        $hotel_accounts = array();
        foreach ($hotels as $k=>$v){
            if(count($v)>1){
                $sql_hotel = "select * from savor_hotel where id=$k";
                $res_hotel = $model->query($sql_hotel);
                $hotel_str = $res_hotel[0]['name'];
                foreach ($v as $bv){
                    $hotel_str.=','.$bv['bind_mobile'];
                }
                $hotel_accounts[]=$hotel_str;
            }
        }
        print_r($hotel_accounts);
    }

    public function exchangerecord(){
        $start = I('get.start',1000,'intval');
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        $res = array();
        $money = array(20,50,100,10);
        foreach ($area_arr as $k=>$v){
            $area_id = $v['id'];
            $area_name = $v['region_name'];
            $offset = $start+($k*50);
            $sql = "select avatarUrl,nickName from savor_smallapp_user where small_app_id=1 and nickName!='' and unionId='' order by id asc limit $offset,50";
            $res_user = $m_area->query($sql);
            foreach ($res_user as $uv){
                shuffle($money);
                $u_money = $money[0];
                $info = array('area_id'=>$area_id,'area_name'=>$area_name,'name'=>$uv['nickname'],'money'=>$u_money);
                $res[]=$info;
            }
        }
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(14);
        $cache_key = C('SAPP_SALE').'exchangerecord';
        $redis->set($cache_key,json_encode($res));
        echo 'ok';
    }

    public function laimao(){
        exit;
        $goods_id = 127;//赖茅
        //$all_hotel_ids = array(883);
        $sql ="SELECT hotel_id FROM `savor_smallapp_hotelgoods` WHERE goods_id=145 ";
        $zt_list = M()->query($sql);
        $all_hotel_ids = array_column($zt_list,'hotel_id');
        //print_r($all_hotel_ids);exit;
        
        $m_sysconfig = new \Admin\Model\SysConfigModel();
        $res_config = $m_sysconfig->getAllconfig();
        $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $m_staff = new \Admin\Model\Integral\StaffModel();
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        foreach ($all_hotel_ids as $hotel_id){
            $res_merchant = $m_merchant->getInfo(array('hotel_id'=>$hotel_id,'status'=>1));
            $staff_num = 0;
            if(!empty($res_merchant)){
                $res_staff = $m_staff->getDataList('openid',array('merchant_id'=>$res_merchant['id'],'status'=>1),'id desc');
                if(!empty($res_staff)){
                    $staff_num = count($res_staff);
                    foreach ($res_staff as $stav){
                        $openid = $stav['openid'];
                        $data = array('hotel_id'=>$hotel_id,'openid'=>$openid,'goods_id'=>$goods_id,'type'=>2);
                        $res_hotelgoods = $m_hotelgoods->getInfo($data);
                        if(empty($res_hotelgoods)){
                            $m_hotelgoods->addData($data);
                        }
                    }
                }
            }

            $redis->select(14);
            $cache_key = C('SAPP_SALE').'activitygoods:loopplay:'.$hotel_id;
            $res_cache = $redis->get($cache_key);
            if(!empty($res_cache)){
                $data = json_decode($res_cache,true);
            }else{
                $data = array();
            }
            if($res_config['activity_adv_playtype']==1){
                $data = array();
            }
            if(!array_key_exists($goods_id,$data)){
                $data_num = count($data);
                if($data_num>4){
                    $data = array_slice($data,0,4);
                }
                $data[$goods_id] = $goods_id;
                $program_key = C('SAPP_SALE_ACTIVITYGOODS_PROGRAM').":$hotel_id";
                $period = getMillisecond();
                $period_data = array('period'=>$period);
                $redis->set($program_key,json_encode($period_data));
            }
            $redis->set($cache_key,json_encode($data));

            echo "hotel_id:$hotel_id staff_num:$staff_num ok \r\n";
        }
    }

    public function getfileinfo(){
        $forscreen_id = I('get.fid',0,'intval');
        $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $res_forscreen = $m_forscreen->getInfo(array('forscreen_id'=>$forscreen_id));
        $imgs = json_decode($res_forscreen['imgs'],true);
        $oss_addr = $imgs[0];

        $accessKeyId = C('OSS_ACCESS_ID');
        $accessKeySecret = C('OSS_ACCESS_KEY');
        $endpoint = 'oss-cn-beijing.aliyuncs.com';
        $bucket = C('OSS_BUCKET');
        $aliyunoss = new Aliyun($accessKeyId, $accessKeySecret, $endpoint);
        $aliyunoss->setBucket($bucket);

        $res_object = $aliyunoss->getObjectMeta($oss_addr);
        $file_size = 0;
        if(isset($res_object['content-length']) && $res_object['content-length']>0 && isset($res_object['oss-request-url'])){
            $tmp_file = explode("$endpoint/",$res_object['oss-request-url']);
            if($tmp_file[1]==$oss_addr){
                $file_size = $res_object['content-length'];
            }
        }
        $is_eq = 0;
        if($file_size==$res_forscreen['resource_size']){
            $is_eq = 1;
        }
        $oss_filesize = $file_size;
        $range = '0-199';
        $bengin_info = $aliyunoss->getObject($oss_addr,$range);
        $last_range = $oss_filesize-199;
        $last_size = $oss_filesize-1;
        $last_range = $last_size - 199;
        $last_range = $last_range.'-'.$last_size;
        $end_info = $aliyunoss->getObject($oss_addr,$last_range);
        $file_str = md5($bengin_info).md5($end_info);
        $fileinfo = strtoupper($file_str);
        if(!empty($bengin_info) && !empty($end_info)){
            $md5_file = md5($fileinfo);
        }else{
            $md5_file = '';
        }


        $res = array('db_size'=>$res_forscreen['resource_size'],'oss_size'=>$file_size,'is_eq'=>$is_eq,
            'md5_file'=>$md5_file,'oss_addr'=>$oss_addr);
        print_r($res);
        exit;
    }

    public function publicmd5(){
        exit;
        $accessKeyId = C('OSS_ACCESS_ID');
        $accessKeySecret = C('OSS_ACCESS_KEY');
        $endpoint = 'oss-cn-beijing.aliyuncs.com';
        $bucket = C('OSS_BUCKET');
        $aliyunoss = new Aliyun($accessKeyId, $accessKeySecret, $endpoint);
        $aliyunoss->setBucket($bucket);
        $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();

        $m_public = new \Admin\Model\Smallapp\PublicModel();
        $where = array('status'=>2,'res_type'=>2);
        $order = 'id desc';
        $res_public = $m_public->field('id,forscreen_id')->where($where)->order($order)->select();
        $md5_data = array();
        foreach ($res_public as $v){
            $forscreen_id = $v['forscreen_id'];
            $res_forscreen = $m_forscreen->getInfo(array('forscreen_id'=>$forscreen_id,'resource_type'=>2));
            $imgs = json_decode($res_forscreen['imgs'],true);
            $oss_addr = $imgs[0];
            if(empty($oss_addr)){
                continue;
            }
            usleep(100000);
            $res_object = $aliyunoss->getObjectMeta($oss_addr);
            $file_size = 0;
            if(isset($res_object['content-length']) && $res_object['content-length']>0 && isset($res_object['oss-request-url'])){
                $tmp_file = explode("$endpoint/",$res_object['oss-request-url']);
                if($tmp_file[1]==$oss_addr){
                    $file_size = $res_object['content-length'];
                }
            }
            $is_eq = 0;
            if($file_size==$res_forscreen['resource_size']){
                $is_eq = 1;
            }
            echo "forscreen_id:$forscreen_id is_eq:$is_eq \r\n";
            $res = array('forscreen_id'=>$forscreen_id,'db_size'=>$res_forscreen['resource_size'],'oss_size'=>$file_size,'is_eq'=>$is_eq);
            if($is_eq==0){
                $md5_data[]=$res;
            }
        }
        $res = var_export($md5_data,true);
        $log_file_name = '/application_data/web/php/savor_admin/Public/content/'.'publicmd5_'.date("YmdHis").".log";
        @file_put_contents($log_file_name, $res, FILE_APPEND);
    }

    public function upmd5(){
        exit;
        require_once '/application_data/web/php/savor_admin/Public/content/publicmd5_20191218.php';
        $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();

        $accessKeyId = C('OSS_ACCESS_ID');
        $accessKeySecret = C('OSS_ACCESS_KEY');
        $endpoint = C('OSS_HOST');
        $bucket = C('OSS_BUCKET');
        $aliyun = new Aliyun($accessKeyId, $accessKeySecret, $endpoint);
        $aliyun->setBucket($bucket);
        $error = array();
        foreach ($publicmd5 as $k=>$v){
            $forscreen_id = $v['forscreen_id'];
            $res_forscreen = $m_forscreen->getInfo(array('forscreen_id'=>$forscreen_id,'resource_type'=>2));
            if(!empty($res_forscreen)){
                $id = $res_forscreen['id'];
                $oss_filesize = $v['oss_size'];
                $imgs = json_decode($res_forscreen['imgs'],true);
                $oss_addr = $imgs[0];

                $range = '0-199';
                $bengin_info = $aliyun->getObject($oss_addr,$range);
                $last_range = $oss_filesize-199;
                $last_size = $oss_filesize-1;
                $last_range = $last_size - 199;
                $last_range = $last_range.'-'.$last_size;
                $end_info = $aliyun->getObject($oss_addr,$last_range);
                $file_str = md5($bengin_info).md5($end_info);
                $fileinfo = strtoupper($file_str);
                $md5_file = md5($fileinfo);

                $where = array('id'=>$id);
                $data = array('resource_size'=>$oss_filesize);
                if(!empty($bengin_info) && !empty($end_info)){
                    $data['md5_file'] = $md5_file;
                }else{
                    $error[]=$v;
                }
                $res = $m_forscreen->updateInfo($where,$data);
                if($res){
                    echo "$k==$id md5_file=$md5_file \r\n";
                }else{
                    echo "$k==$id ok \r\n";
                }
            }
        }
        echo "finish \r\n";
        echo json_encode($error);
    }

    public function hotelpy(){
        exit;
        $pin = new \Common\Lib\Pin();
        $obj_pin = new \Overtrue\Pinyin\Pinyin();

        $m_hotel = new \Admin\Model\HotelModel();
        $sql ="select * from savor_hotel order by id asc limit 800,200";
        $result =  $m_hotel->query($sql);
        $hotels = array();
        foreach ($result as $v){
            $s_hotel_name = $v['name'];

            $code_charter = '';
//            $s_hotel_name = mb_substr($res_hotel['name'], 0,2,'utf8');
            if(preg_match('/[a-zA-Z]/', $s_hotel_name)){
                $code_charter = $s_hotel_name;
            }else {
                $code_charter = $obj_pin->abbr($s_hotel_name);
                $code_charter  = strtolower($code_charter);
                if(strlen($code_charter)==1){
                    $code_charter .=$code_charter;
                }
            }
            $code_charter  = strtolower($code_charter);

            $condition = array('id'=>$v['id']);
            $m_hotel->updateData($condition,array('pinyin'=>$code_charter));
            echo 'hotel_id:'.$v['id']."\r\n";
        }
        echo 'finish';
        print_r($hotels);
    }

    public function dishorder(){
        exit;
//        $sql = "SELECT * from savor_smallapp_dishorder where add_time<='2020-03-24 23:59:59' order by id asc";
        $sql = "SELECT * from savor_smallapp_dishorder where add_time>='2020-04-02 10:00:00' and add_time<='2020-04-02 22:18:00' order by id asc";
        $model = M();
        $res_order = $model->query($sql);
        $m_order = new \Admin\Model\Smallapp\OrderModel();
        $m_ordergoods = new \Admin\Model\Smallapp\OrdergoodsModel();
        foreach ($res_order as $v){
            $dish_order_id = $v['id'];
            if($v['type']==1){
                $v['otype']=3;
            }elseif($v['type']==2){
                $v['otype']=4;
            }else{
                $v['otype']=0;
            }
            if($v['pay_type']==1){
                $v['pay_type']=20;
            }
            $v['goods_id'] = $v['dishgoods_id'];
            unset($v['id'],$v['type'],$v['dishgoods_id']);
            $order_id = $m_order->add($v);
            if($order_id){
                $res_ogoods = $m_ordergoods->getDataList('*',array('order_id'=>$dish_order_id),'id asc');
                $ogoods = array();
                if(!empty($res_ogoods)){
                    foreach ($res_ogoods as $gv){
                        unset($gv['id']);
                        $gv['order_id'] = $order_id;
                        $ogoods[]=$gv;
                    }
                    $m_ordergoods->addAll($ogoods);
                }
            }
        }
        echo 'ok';
    }

    public function address(){
        $sql = "SELECT * FROM `savor_smallapp_address` where add_time>='2020-03-24 23:59:59' and add_time<='2020-04-02 23:59:59'";
        $model = M();
        $res_order = $model->query($sql);
        $m_area = new \Admin\Model\AreaModel();

        foreach ($res_order as $v){
            $res_area = $m_area->find($v['area_id']);
            $res_county = $m_area->find($v['county_id']);
            $address = $res_area['region_name'].$res_county['region_name'].$v['address'];
            $lnglat = $this->getGDgeocodeByAddress($address);
            if(!empty($lnglat)){
                $sql_lnglat = "UPDATE `savor_smallapp_address` SET `lng`='134.121231',`lat`='39.1212' WHERE `id`={$v['id']}";
                $model->execute($sql_lnglat);
            }
        }
        echo 'ok';
    }

    private function getGDgeocodeByAddress($address){
        $url = "https://restapi.amap.com/v3/geocode/geo?address=$address&output=json&key=5bbbc02151b52dad229231c5fc1ac4aa";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL=>$url,
            CURLOPT_TIMEOUT=>2,
            CURLOPT_HEADER=>0,
            CURLOPT_RETURNTRANSFER=>1,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $res = json_decode($response,true);
        $data = array();
        if(is_array($res) && $res['status']==1 && $res['infocode']==10000){
            if(!empty($res['geocodes'][0]['location'])){
                $location_arr = explode(',',$res['geocodes'][0]['location']);
                $data['lng'] = $location_arr[0];//经度
                $data['lat'] = $location_arr[1];//维度
            }
        }
        return $data;
    }


    public function orderNotify(){
//        $content = file_get_contents('php://input');
//        $log_content = "nofity_data:$content";
//        $this->addLog('',$log_content);

        $content = '{"signature":"9daeeb2a84b0954cae9de015858716ab","client_id":"1070428388962074624","order_id":"1000504","order_status":3,"cancel_reason":"","cancel_from"
:0,"dm_id":2535448,"dm_name":"黄晓飞","dm_mobile":"15120020991","update_time":1585540555}';

        if(!empty($content)) {
            $res = json_decode($content, true);
            if(!empty($res) && isset($res['order_id'])){
                $data = array('client_id'=>$res['client_id'],'order_id'=>$res['order_id'],'update_time'=>$res['update_time']);
                asort($data, SORT_STRING);  // 按键值升序排序
                $sign_data = array_values($data);
                $sign = md5(join('',$sign_data));

                echo $sign;
                echo '====';
                echo $res['signature'];
                exit;

            }
        }
        echo 'success';
    }
}