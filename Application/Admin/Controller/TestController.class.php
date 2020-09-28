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
            $where = array();
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
                $data = array();
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

        $sql ="select * from savor_hotel hotel";
        $data = M()->query($sql);
        $data = array();
        foreach($data  as $key=>$v){
            $hotel_info = array();
            $hotel_ext_info = array();
            $hotel_id = $v['id'];
            /*
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
            */
            $hotel_info = $v;
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
        $sql ="select * from savor_hotel_ext";
        $data = M()->query($sql);
        $data = array();
        foreach ($data as $key=>$v){
             $hotel_id = $v['hotel_id'];
             $hotel_ext_info = array();
             /*
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
             */
             $hotel_ext_cache_key = C('DB_PREFIX').'hotel_ext_'.$hotel_id;
             $hotel_ext_info = $v;
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
            /*
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
            */
            $box_info = $v;
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
                where hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0 and box.box_type in(6,7) and box.wifi_name!=''";
//                where hotel.area_id=236 and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0";
//        $sql = "SELECT box.* FROM savor_box box LEFT JOIN savor_room room ON box.room_id=room.id LEFT JOIN savor_hotel hotel ON room.hotel_id=hotel.id WHERE hotel.state=1 AND hotel.flag=0 AND box.state=1 AND box.flag=0 AND box.mac IN (SELECT box_mac FROM savor_smallapp_forscreen_record WHERE small_app_id IN (2,3) AND create_time>='2019-10-01 00:00:00' AND create_time<='2019-12-10 13:00:00' GROUP BY box_mac)";
//        $sql = "SELECT box.* FROM savor_box box LEFT JOIN savor_room room ON box.room_id=room.id LEFT JOIN savor_hotel hotel ON room.hotel_id=hotel.id WHERE hotel.state=1 AND hotel.flag=0 AND box.state=1 AND box.flag=0 AND box.mac IN('40E79325362D','40E793253454','40E79325351F','40E79325348E','40E79325357A','40E793253664','40E793253450','40E793253490','40E793253557','40E79325343E','40E793253573','40E7932534BA','40E79325344D','40E793253600','40E79325354B','40E79325375E','40E79325343C','40E793253442','40E793253586','40E793253413','40E7932534DA','40E7932534C6','40E79325370D','40E793253440','40E793253555','40E7932534B7','40E79325349B','40E7932534CD','40E7932534C1','40E793253483','40E79325347D','40E793253477','40E7932534D0','40E7932535B6','40E79325365B','40E79325344E','40E79325348A','40E793253604','40E7932534A8','40E7932535B5','40E793253441','40E793253603','40E793253689','40E793253606','40E79325348D','40E793253448','40E793253648','40E793253471','40E7932534D8','40E793253479','40E793253556','40E7932534B6','40E793253486','40E793253594','40E7932535D4','40E7932534B8','40E793253426','40E793253569','40E793253481','40E7932535A9','40E7932535D8','40E79325374D','40E793253658','40E793253742','40E79325347A','40E79325374C','40E793253747','40E79325374A','40E7932536E3','40E7932536F9','40E79325368C','40E793253453','40E793253711','40E793253704','40E7932534F8','40E793253681','40E793253692','40E7932534E7','40E7932534A1','40E793253545','40E79325360A','40E793253439','40E793253412','40E79325376E','40E793253408','40E7932535FA','40E793253707','40E793253663','40E79325371D','40E793253526','40E79325376B','40E79325372E','40E7932536CF','40E7932536CB','40E793253769','40E79325364C','40E7932534D4','40E7932534E5','40E793253767','40E793253432','40E793253734','40E793253492','40E7932534D2','40E79325376A','40E79325360C','40E793253751','40E7932536CE','40E79325374F','40E79325365D','40E79325373A','40E79325371B','40E79325356D','40E7932535D0','40E793253686','40E7932534E1','40E79325350F','40E793253517','40E793253720','40E7932534B3','40E79325350B','40E793253593','40E793253553','40E7932535A1','40E793253458','40E79325345E','40E793253682','40E793253667','40E793253519','40E793253466','40E793253722','40E7932536D2','40E7932535A3','40E793253498','40E793253645','40E7932536C0','40E7932535BA','40E79325359C','40E7932536EE','40E7932535CD','40E793253616','40E793253598','40E793253693','40E793253659','40E79325372C','40E79325366A','40E79325373E','40E7932536FB','40E793253743','40E793253701','40E793253621','40E793253735','40E79325366E','40E793253730','40E7932536F7','40E793253646','40E793253731','40E793253647','40E793253705','40E793253670','40E793253629','40E793253685','40E793253497','40E793253716','40E79325375A','40E79325370E','40E793253669','40E793253675','40E7932536D3','40E7932536E7','40E7932536C9','40E7932536FC','40E79325366C','40E79325347B','40E7932536E4','40E793253650','40E7932536E8','40E793253687','40E7932535D1','40E7932536E5','40E793253662','40E79325365A','40E7932536F6','40E7932534F0','40E79325372D','40E79325362E','40E7932534C5','40E793253688','40E79325374B','40E79325344A','40E793253570','40E7932534DF','40E793253478','40E7932534E2','40E793253444','40E793253572','40E793253504','40E793253597','40E79325358A','40E7932534D6','40E793253745','40E7932535DB','40E793253746','40E793253465','40E7932535F7','40E793253451','40E79325356B','40E79325346B','40E793253580','40E7932535B4','40E7932535D2','40E793253568','40E793253732','40E7932535B0','40E7932534B0','40E7932534C4','40E7932535B1','40E79325367C','40E793253521','40E7932538B7','40E79325346A','40E7932536A1','40E7932534DC','40E793253757','40E79325371E','40E7932535D3','40E793253489','40E7932536FA','40E79325349C','40E79325375C','40E79325342A','40E793253434','40E7932535A5','40E79325350A','40E793253630','40E7932535BE','40E7932534C7','40E79325343B','40E793253480','40E793253596','40E7932534EE','40E79325340A','40E793253549','40E7932534EA','40E7932534B2','40E79325348B','40E7932534F4','40E793253409','40E7932535ED','40E793253430','40E7932533F3','40E7932535CE','40E7932535BC','40E7932535C9','40E7932536A7','40E79325356F','40E793253764','40E793253484','40E793253765','40E793253493','40E7932536B8','40E793253744','40E793253694','40E7932536AA','40E793253652','40E793253750','40E7932536B9','40E7932535C6','40E7932535E5','40E79325371A','40E793253585','40E7932535FF','40E793253763','40E7932535E4','40E793253636','40E79325366F','40E793253462','40E79325360D','40E7932536EA','40E7932536A9','40E793253627','40E7932535E0','40E79325369C','40E7932536C6','40E793253613','40E7932535CF','40E7932535E6','40E7932535C1','40E7932536AD','40E793253635','40E79325363F','40E793253634','40E79325362B','40E7932535F6','40E7932536D9','40E7932535BB','40E7932536BA','40E793253589','40E7932534F3','40E793253665','40E7932535E7','40E7932535C2','40E79325376F','40E7932535BD','40E793253487','40E7932535CB','40E7932534C8','40E793253733','40E7932535CC')";

        $data = M()->query($sql);
        $flag = 0;
        foreach($data as $key=>$v){
//            if($v['is_open_simple']==1 && $v['is_sapp_forscreen']==0){
//                $v['is_sapp_forscreen'] = 1;
//            }elseif($v['is_open_simple']==1 && $v['is_sapp_forscreen']==1){
//                $v['is_open_simple'] = 0;
//            }
            if(!empty($v['wifi_name'])){
                $v['is_sapp_forscreen'] = 1;
                $v['is_open_simple'] = 1;
                $is_open_simple = $v['is_open_simple'];
                $is_sapp_forscreen = $v['is_sapp_forscreen'];
                $sql ="update savor_box set is_open_simple=$is_open_simple,is_sapp_forscreen=$is_sapp_forscreen where id=".$v['id'].' limit 1';
                M()->execute($sql);
            }


            $box_info = array();
            $box_id = $v['id'];
            /*
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
            */
            $box_info = $v;
            $box_cache_key = C('DB_PREFIX').'box_'.$box_id;
            $redis->set($box_cache_key, json_encode($box_info));

            if(empty($v['mac'])){
                continue;
            }
            $res_box = $v;
            $forscreen_type = 1;//1外网(主干) 2直连(极简)
            $box_forscreen = '1-0';
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
                        /* if(in_array($res_box['box_type'],array(3,6,7))){
                            $forscreen_type = 2;
                        }elseif($res_box['box_type']==2){
                            $forscreen_type = 1;
                        } */
                        $forscreen_type = 1;
                        break;
                    default:
                        $forscreen_type = 1;
                }
                $redis->select(14);
                $box_mac = $res_box['mac'];
                $box_key = "box:forscreentype:$box_mac";
                $forscreen_info = array('box_id'=>$box_id,'forscreen_type'=>$forscreen_type,'forscreen_method'=>$box_forscreen);
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

    public function getjjbox(){
        exit;
        $redis = SavorRedis::getInstance();
        $redis->select(14);
        $keys  = $redis->keys('box:forscreentype:*');

        $boxs = array();
        $all_box = "";
        foreach($keys as $key){
            $res = $redis->get($key);
            if(!empty($res)){
                $res_data = json_decode($res,true);
                if($res_data['forscreen_type']==2){
                    $box_arr = explode(':',$key);
                    echo $res_data['box_id'].'=='.$res_data['forscreen_type'].'=='.$box_arr[2]."\r\n";
                    $box_mac = $box_arr[2];
                    $all_box.="'$box_mac',";
                }

//                if(isset($res_data['forscreen_type']) && $res_data['forscreen_type']==2){
//                    echo $res_data['box_id'].'=='.$res_data['forscreen_type']."\r\n";
//
//                    $box_id = $res_data['box_id'];
//                    $box_cache_key = C('DB_PREFIX').'box_'.$box_id;
//                    $redis->select(15);
//                    $res_box = $redis->get($box_cache_key);
//                    if(!empty($res_box)){
//                        $box_data = json_decode($res_box,true);
//                        $boxs[]=$box_data['mac'];
//                    }
//                }
            }
        }
        echo $all_box;
    }

    public function tjjj(){
        exit;
        $sql_openid = "select openid from savor_smallapp_qrcode_log where type=8 and create_time>'2020-06-15 13:57:24' and box_mac in('00226D584378','FCD5D900B33A','00226D8BC9F0','00226D8BCC5D','00226D8BCDB1','F4285389498D','00226D5843BC','00226D584109','00226D58434E','00226D8BCCFD','00226D8BCDC2','111111111111','FCD5D900B2E0','00226D8BCE0F','00226D65542E','00226D6554A7','00226D583FC6','00226D655155','00226D8BCD40','00226D655557','00226D65524C','00226D583E8E','00226D584754','00226D58421F','00226D8BCA99','00226D584751','00226D6554FC','00226D5843B7','00226D8BCB5A','00226D6555EF','00226D583DB7','00226D655423','00226D5843EB','00226D5842A9','00226D5843A2','00226D8BC98F','00226D584043','00226D650002','FCD5D900B6C9','00226D8BCADE','00226D58451B','00226D5846D5','00226D8BCA9D','00226D583DF1','00226D6550EE','00226D584655','00226D8BCE47','00226D655308','00226D8BCACB','00226D2FB26F','00226D655260','00226D583E28','00226D584607','00226D650004','00226D651111','00226D2FB212','00226D655404','00226D583CC0','00226D2FB112','00226D655276','FCD5D900B4E9','00226D8BCAFD','00226D8BCC2E','00226D8BCBC6','00226D584311','00226D5841B7','00226D583FC9','00226D584187','00226D58461F','00226D584578','00226D65556F','00226D5845B7','00226D6554D7','00226D2FB24A','00226D8BCD79','00226D58406A','00226D655437','00226D6552E1','00226D5840C6','00226D655236','00226D6554F0','00226D58458A','00226D655628','00226D583E2D','00226D8BC9DA','00226D8BCBC9','00226D8BCA93','00226D583E1E','00226D6551B3','00226D583F94','00226D6555F3','00226D584540','00226D655218','00226D583C9B','00226D8BCA63','00226D8BCE0E','00226D8BCA98','00226D5843EF','00226D655389','00226D8BCC24','00226D583F87','00226D5841EC','00226D58448C','00226D584436','00226D2FB256','00226D8BCAAD','00226D6555BA','00226D65561C','00226D583E56','00226D8BC943','00226D8BCD56','00226D65549D','00226D583D77','00226D5843AB','00226D651000','00226D6554C2','00226D8BCDEF','00226D5842EA','00226D655455','00226D584253','00226D5842E1','00226D655171','00226D584730','00226D8BCCC2','00226D583E52','00226D65545A','00226D65521C','00226D655397','00226D5843D9','00226D65543A','00226D58421B','00226D5846D7','00226D65516C','00226D8BCDD3','00226D583F23','00226D65527F','00226D6554A3','00226D584764','00226D584281','00226D58409D','00226D584480','00226D6555AE','FCD5D900B19A','00226D583F30','00226D8BCBB3','00226D6555DD','00226D65523F','00226D6553CC','00226D584312','00226D584674','00226D583EF3','00226D6552C5','00226D8BCBD9','00226D6551BA','00226D6555E9','00226D584355','00226D58407A','00226D8BCC21','00226D58436C','00226D8BCAB5','00226D5840C7','00226D58474B','00226D8BCA51','00226D5845C2','00226D58472D','00226D655363','00226D6553A8','00226D5841E4','00226D584739','00226D65521B','00226D650001','00226D58452E','00226D5846AC','00226D6551E7','FCD5D900B412','00226D8BCB49','00226D65533D','00226D65514F','00226D655530','00226D8BCB47','00226D584584','00226D584359','00226D8BCDC7','00226D584548','00226D5845F1','00226D5843E5','00226D584170','00226D58415B','00226D8BCE1E','00226D6553D0','00226D5840D0','00226D655488','00226D6552D7','00226D8BCA9C','00226D655368','00226D8BCA6E','00226D584347','00226D584671','00226D583CF8','00226D8BCB0B','00226D583D33','00226D58411E','00226D5846BD','00226D655206','00226D58423C','00226D584511','00226D584229','00226D8BCDA0','00226D5843C9','00226D584492','00226D8BCA5E','00226D5841BC','00226D8BCA78','00226D584428','00226D655413','00226D655570','FCD5D900B693','00226D655603','00226D2FB237','00226D583D7C','00226D6554BC','00226D65563F','00226D655381','00226D8BCAC8','00226D8BCE61','00226D655515','F428538931C9','00226D65558C','00226D583F03','00226D58412A','00226D5840A1','00226D584009','00226D65548F','00226D5843D3','00226D5846B9','00226D5845BA','00226D8BCB0F','00226D5842D0','00226D8BCC20','00226D580002','00226D584429','00226D583E21','00226D65519D','00226D583FFC','00226D65511E','00226D8BCCA8','00226D6551DE','00226D584517','00226D8BCC94','00226D583FDF','00226D58466D','00226D58427A','00226D8BCABC','00226D583CF0','00226D58474A','00226D584701','00226D2FB216','00226D2FB222','00226D58475C','00226D8BCD73','FCD5D900B492','00226D8BCBF1','00226D583CCE','00226D5846E4','00226D584026','00226D655120','00226D655593','00226D5845E8','00226D583FDE','00226D583F92','00226D8BCE48','FCD5D900B7BC','00226D655395','00226D655222','00226D5846D6','00226D655270','00226D584447','00226D8BC991','00226D583D6E','00226D655169','00226D8BCE53','00226D5844F3','00226D65520F','00226D8BCB21','00226D5843FD','00226D6551AB','FCD5D900B70A','00226D655627','00226D8BCBB5','00226D8BCB64','00226D8BCA9A','00226D65521E','00226D6555C1','00226D655151','00226D5843FB','00226D583FC8','00226D8BCC37','00226D583FBA','00226D65511A','00226D583E2C','00226D584193','40E793253583','00226D584063','00226D6550F7','00226D583F1D','00226D8BCA4D','00226D584272','00226D655523','00226D8BC9D2','00226D58400E','00226D8BCBC2','00226D584376','00226D583EDC','00226D584225','00226D584090','00226D584661','00226D6553FB','00226D58477C','00226D8BCD1E','00226D8BCCB9','00226D583F64','00226D584395','00226D584430','00226D65537C','00226D65561A','00226D584309','00226D581111','00226D5840FE','00226D8BCAA1','00226D655370','00226D8BCDDA','00226D655226','00226D583D80','00226D655237','00226D58431A','00226D6554E5','00226D65522C','0022600120F3','00226D8BCAF1','00226D58464F','00226D583F32','00226D584761','00226D655540','00226D8BCB28','00226D655561','00226D584463','00226D650000','00226D655522','00226D655119','00226D584551','00226D655297','00226D583FF9','00226D655103','00226D65541C','00226D5843E8','00226D8BC951','00226D584338','00226D8BC940','00226D8BCA08','00226D58426D','00226D583C9E','00226D58473B','00226D2FB24D','00226D583EE9','00226D655223','00226D6551F3','00226D8BCA74','00226D65532A','00226D655124','00226D655510','00226D655631','00226D6554F9','00226D655460','00226D655498','00226D580001','00226D583D31','00226D584489','00226D6553C8','00226D8BCAE2','00226D583FA1','00226D8BCC41','00226D5845D2','00226D655116','00226D8BCA60','00226D5843CB','00226D6550FD','00226D584241','00226D58453A','00226D583F39','00226D65525C','00226D584379','00226D58460A','00226D8BC9E6','00226D584030','00226D6554BE','00226D58457A','00226D8BCA80','00226D583CE8','00226D583FD4','00226D655303','00226D5843AA','00226D8BCE19','00226D8BC979','00226D584297','00226D5841C1','00226D6555D4','00226D58423B','FCD5D900B390','00226D8BC9F7','00226D584145','00226D2F1223','00226D8BCAA4','00226D65535F','00226D2FB252','00226D58403E','00226D5846D8','00226D6555FB','00226D584415','00226D655629','00226D655256','00226D584367','00226D8BCA94','00226D5845BE','00226D65550C','00226D8BCDDF','00226D655367','00226D58405E','00226D8BC929','00226D6552FB','00226D8BCD9C','00226D8BCB97','00226D5845CB','00226D655362','00226D584464','00226D655250','00226D584507','00226D8BCA00','00226D583F45','00226D6551C0','00226D8BCC6E','00226D8BCC4E','00226D5841BB','00226D584757','00226D584058','00226D584490','00226D8BCE32','00226D584XXX','00226D8BCE59','00226D6553B1','00226D58437F','00226D584072','00226D58462D','00226D584104','00226D8BCA18','00226D58409C','00226D65520B','00226D584435','00226D8BCA5D','00226D655550','00226D6553BF','00226D6555CC','00226D583C9F','00226D8BCB8A','00226D584539','00226D8BCB13','00226D65511D','00226D58465E','00226D583FCB','00226D584541','00226D8BCBED','00226D655563','00226D5844A9','00226D8BCA16','00226D65537D','00226D5842F4','00226D5841AF','00226D583EBB','00226D5841D4','00226D584138','00226D6554B7','00226D58451D','00226D2FB24C','00226D650003','00226D58444F','00226D5841A8','00226D584224','00226D65512C','FCD5D900B829','00226D5845E1','00226D655387','00226D584332','00226D583CBC','00226D583E8C','00226D655211','00226D583EEC','00226D655366','00226D8BCAB9','00226D8BCA25','00226D655193','FCD5D900B347','00226D6554C4','00226D583D74','00226D655338','00226D8BCAA5','00226D6552BA','00226D6552BC','00226D655526','00226D583F9F','00226D6555BB','00226D8BCA4E','00226D6551FC','00226D655157','00226D8BCA76','00226D584418','00226D8BCACC','00226D8BCDE0','00226D655188','00226D5846A6','00226D8BCD83','00226D58444E','00226D8BC9BE','00226D8BCBFF','00226D583F22','00226D6552F0','00226D583FEE','00226D58456C','00226D8BCC06','00226D58401A','00226D583E98','00226D8BCB57','00226D5841C2','00226D8BCA79','00226D8BC93A','00226D584366','00226D2FB257','00226D6555E5','00226D6553D2','00226D65546D','00226D8BCAD0','00226D65554A','00226D5844C4','00226D583CED','00226D2FB231','00226D58450E','00226D6553D5','00226D58456D','00226D8BCDFD','00226D5846B8','00226D8BC992','00226D8BC946','00226D8BC99A','00226D8BCA4F','00226D5840B2','00226D583FDB','00226D8BCC0D','00226D584594','00226D655289','00226D5845AA','00226D655408','00226D58451C','00226D584547','00226D583E87','00226D5844AB','00226D2FB262','00226D584210','00226D5847E6','00226D6553FA','00226D8BCB85','00226D584089','00226D5845B6','00226D655271','00226D584076','00226D5845A0','00226D8BC9D0','00226D584042','00226D5845B4','00226D58476E','00226D583CDE','00226D8BCAA3','00226D6551D3','00226D583ED0','00226D6555A2','00226D655400','00226D8BCAC5','00226D655588','00226D58434F','00226D655632','00226D65532F','00226D655330','00226D8BCBD2','00226D583DE4','00226D583D5D','00226D65534C','00226D6554F5','00226D65564F','00226D65538D','00226D8BCCF2','FCD5D900B909','00226D8BCCA1','00226D583D29','00226D8BCBA4','00226D6554CF','00226D584427','00226D655601','00226D8BC928')
group by openid";
        $sql_wifierror_openids = "select openid from savor_smallapp_wifi_err where create_time>'2020-06-15 13:57:24' and openid in($sql_openid) group by openid";
        $res_error_openids = M()->query($sql_wifierror_openids);
        $error_openids = array();
        foreach ($res_error_openids as $v){
            $open_id = $v['openid'];
            $sql_record = "select openid,mobile_brand,mobile_model from savor_smallapp_forscreen_record where openid='$open_id' and small_app_id in(2,3) and create_time>'2020-06-15 13:57:24'";
            $res_record = M()->query($sql_record);
            if(empty($res_record)){
                $error_openids[]=$v;
            }
        }
        $all_openids = array();
        $res_openids = M()->query($sql_openid);
        $success_openids = array();
        $success_brandopenids = array();
        foreach ($res_openids as $v){
            $open_id = $v['openid'];
            if(!in_array($open_id,$error_openids)){
                $sql_record = "select openid,mobile_brand,mobile_model from savor_smallapp_forscreen_record where openid='$open_id' and small_app_id in(2,3) and create_time>'2020-06-15 13:57:24'";
                $res_record = M()->query($sql_record);
                if(!empty($res_record)){
                    $success_openids[]=$v;
                    $mobile_brand = $res_record[0]['mobile_brand'];
                    $mobile_model = $res_record[0]['mobile_model'];

                    $success_brandopenids[$mobile_brand][]=$v;
                }
            }
        }
        $res = array('all'=>count($res_openids),'error'=>count($error_openids),'success'=>count($success_openids));
        print_r($res);
        exit;
    }

    public function task(){
        $model = M();
        $sql_task = 'select * from savor_integral_task where status=1 and flag=1';
        $res_task = $model->query($sql_task);
        $all_task = array();
        foreach ($res_task as $v){
            $type = $v['type'];
            $task_info = json_decode($v['task_info'],true);
            $all_task[$v['id']] = $task_info['task_content_type'];
        }
        $sql_hotel_task = 'select GROUP_CONCAT(task_id) as task_ids,hotel_id from savor_integral_task_hotel group by hotel_id';
        $res_hotel_task = $model->query($sql_hotel_task);

        $hotel_more_task = array();
        $hotel_repeat_task = array();
        foreach ($res_hotel_task as $v){
            $tasks = explode(',',$v['task_ids']);
            if(count($tasks)>1){
                $res_hotel = $model->query('select name from savor_hotel where id='.$v['hotel_id']);
                $hotel_more_task[]=$v['hotel_id'];
                $hotel_tasks = array();
                foreach ($tasks as $tv){
                    if(isset($all_task[$tv])){
                        $t_type = $all_task[$tv];
                        $hotel_tasks[$t_type][]=$tv;
                    }
                }
                foreach ($hotel_tasks as $kk=>$kv){
                    if(count($kv)>1){
                        $hotel_repeat_task[]=array('hotel_id'=>$v['hotel_id'],'hotel_name'=>$res_hotel[0]['name'],'task_ids'=>$kv);
                    }
                }
            }
        }
        echo json_encode($hotel_more_task);
        echo '====';
        print_r($hotel_repeat_task);
    }

    public function uptasktype(){
        $model = M();
        $sql_task = 'select * from savor_integral_task';
        $res_task = $model->query($sql_task);
        foreach ($res_task as $v) {
            $id = $v['id'];
            $task_info = json_decode($v['task_info'], true);
            $task_type = $task_info['task_content_type'];
            $sql = "update savor_integral_task set task_type={$task_type} where id={$id}";

            $res = $model->execute($sql);
            if($res){
                echo "id $id ok\r\n";
            }
        }
    }


    public function forscreenhelpvideo(){
        $now_box_mac = I('mac','','trim');
        $url = 'https://api-nzb.littlehotspot.com/netty/box/connections';
        $curl = new \Common\Lib\Curl();
        $res_netty = '';
        $curl::get($url,$res_netty,10);
        $res_box = json_decode($res_netty,true);
        if(empty($res_box) || !is_array($res_box) || $res_box['code']!=10000){
            echo "netty connections api error \r\n";
            exit;
        }
        if(!empty($res_box['result'])){
            $netty_data = array('action'=>134,'resource_type'=>2,'url'=>'media/resource/h8YcE7debZ.mp4','filename'=>"h8YcE7debZ.mp4");
            $message = json_encode($netty_data);
            $netty_cmd = C('SAPP_CALL_NETY_CMD');
            $m_netty = new \Admin\Model\Smallapp\NettyModel();
            foreach ($res_box['result'] as $k=>$v){
                if($v['totalConn']>0){
                    foreach ($v['connDetail'] as $cv){
                        $box_mac = $cv['box_mac'];
                        if($box_mac==$now_box_mac){
                            $push_url = 'http://'.$cv['http_host'].':'.$cv['http_port'].'/push/box';
                            $req_id  = getMillisecond();
                            $box_params = array('box_mac'=>$box_mac,'msg'=>$message,'req_id'=>$req_id,'cmd'=>$netty_cmd);
                            $post_data = http_build_query($box_params);
                            $ret = $m_netty->curlPost($push_url,$post_data);
                            $res_push = json_decode($ret,true);
                            if($res_push['code']==10000){
                                echo "box_mac:$box_mac push ok \r\n";
                            }else{
                                echo "box_mac:$box_mac push error $ret  \r\n";
                            }
                            break;
                        }
                    }
                }

            }
        }
    }

    public function cachehotelassess(){
        $file_path = SITE_TP_PATH.'/Public/content/广州考核酒楼0925.xlsx';
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");

        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $other_hotel = array();
        $hotel_info = array();
        $redis = new \Common\Lib\SavorRedis();
        $m_hotel = new \Admin\Model\HotelModel();
        for ($row = 2; $row <= $highestRow; $row++){
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            if(!empty($rowData[0][0])){
                $hotel_name = trim($rowData[0][0]);
                $res_hotel = $m_hotel->getInfo('*',array('name'=>$hotel_name,'state'=>1,'flag'=>0),'id desc','0,1');
                if(!empty($res_hotel)){
                    $hotel_info[$res_hotel[0]['id']] = array('hotel_id'=>$res_hotel[0]['id'],'hotel_box_type'=>$res_hotel[0]['hotel_box_type'],
                        'hotel_name'=>$hotel_name,
                        'area_id'=>236,'area_name'=>$rowData[0][1],'hotel_level'=>$rowData[0][5],'team_name'=>$rowData[0][4],'maintainer'=>$rowData[0][6]);
                }else{
                    $other_hotel[]=$hotel_name;
                }
            }
        }
        $redis->select(1);
        $key = 'smallapp:hotelassess';
        $redis->set($key,json_encode($hotel_info));
        print_r($other_hotel);
        exit;
    }

    public function forscreenimage(){
        $now_box_mac = I('mac','','trim');

        $url = 'https://api-nzb.littlehotspot.com/netty/box/connections';
        $curl = new \Common\Lib\Curl();
        $res_netty = '';
        $curl::get($url,$res_netty,10);
        $res_box = json_decode($res_netty,true);
        if(empty($res_box) || !is_array($res_box) || $res_box['code']!=10000){
            $curl::get($url,$res_netty,10);
            $res_box = json_decode($res_netty,true);
        }
        if(empty($res_box) || !is_array($res_box) || $res_box['code']!=10000){
            echo "netty connections api error \r\n";
            exit;
        }

        if(!empty($res_box['result'])){

            $netty_cmd = C('SAPP_CALL_NETY_CMD');
            $m_netty = new \Admin\Model\Smallapp\NettyModel();
            foreach ($res_box['result'] as $k=>$v){
                if($v['totalConn']>0){
                    foreach ($v['connDetail'] as $cv){
                        $box_mac = $cv['box_mac'];
                        if($box_mac==$now_box_mac){

                            $forscreen_number  = rand(1001,5000);
                            $netty_data = array('action'=>133,'forscreen_number'=>$forscreen_number,'countdown'=>30);
                            $message = json_encode($netty_data);

                            $push_url = 'http://'.$cv['http_host'].':'.$cv['http_port'].'/push/box';
                            $req_id  = getMillisecond();
                            $box_params = array('box_mac'=>$box_mac,'msg'=>$message,'req_id'=>$req_id,'cmd'=>$netty_cmd);
                            $post_data = http_build_query($box_params);
                            $ret = $m_netty->curlPost($push_url,$post_data);
                            $res_push = json_decode($ret,true);
                            if($res_push['code']==10000){
                                echo "box_mac:$box_mac push ok \r\n";
                            }else{
                                echo "box_mac:$box_mac push error $ret  \r\n";
                            }

                            exit;
                        }
                    }
                }
            }
        }
    }

    public function pushdish(){
        $now_box_mac = I('mac','','trim');

        $url = 'https://api-nzb.littlehotspot.com/netty/box/connections';
        $curl = new \Common\Lib\Curl();
        $res_netty = '';
        $curl::get($url,$res_netty,10);
        $res_box = json_decode($res_netty,true);
        if(empty($res_box) || !is_array($res_box) || $res_box['code']!=10000){
            $curl::get($url,$res_netty,10);
            $res_box = json_decode($res_netty,true);
        }
        if(empty($res_box) || !is_array($res_box) || $res_box['code']!=10000){
            echo "netty connections api error \r\n";
            exit;
        }

        if(!empty($res_box['result'])){
            $activity_info = array('hotel_id'=>7,'dish'=>'新渝城传承川渝味道精髓招牌菜水煮鱼','start_time'=>'18:00','end_time'=>'18:50',
                'lottery_time'=>'12:00','dish_img'=>'lottery/activity/zzhx.jpg');
            $activity_info['lottery_time'] = time()+7200;
            $activity_info['lottery_time'] = date('Y-m-d H:i:s',$activity_info['lottery_time']);

            $netty_cmd = C('SAPP_CALL_NETY_CMD');
            $m_netty = new \Admin\Model\Smallapp\NettyModel();
            foreach ($res_box['result'] as $k=>$v){
                if($v['totalConn']>0){
                    foreach ($v['connDetail'] as $cv){
                        $box_mac = $cv['box_mac'];
                        if($box_mac==$now_box_mac){

                            $lottery_countdown = strtotime($activity_info['lottery_time']) - time();
                            $lottery_countdown = $lottery_countdown>0?$lottery_countdown:0;
                            $dish_name_info = pathinfo($activity_info['dish_img']);
                            $partake_img = $activity_info['dish_img'].'?x-oss-process=image/resize,m_mfit,h_200,w_300';
                            $netty_data = array('action'=>135,'countdown'=>30,'lottery_time'=>date('H:i',strtotime($activity_info['lottery_time'])),
                                'lottery_countdown'=>$lottery_countdown,'partake_img'=>$partake_img,'partake_filename'=>$dish_name_info['basename'],
                                'partake_name'=>$activity_info['dish'],'activity_name'=>'新渝城传承川渝味优惠大酬宾抽奖活动',
                            );
                            $message = json_encode($netty_data);

                            $push_url = 'http://'.$cv['http_host'].':'.$cv['http_port'].'/push/box';
                            $req_id  = getMillisecond();
                            $box_params = array('box_mac'=>$box_mac,'msg'=>$message,'req_id'=>$req_id,'cmd'=>$netty_cmd);
                            $post_data = http_build_query($box_params);
                            $ret = $m_netty->curlPost($push_url,$post_data);
                            $res_push = json_decode($ret,true);
                            if($res_push['code']==10000){
                                echo "box_mac:$box_mac push ok \r\n";
                            }else{
                                echo "box_mac:$box_mac push error $ret  \r\n";
                            }

                            exit;
                        }
                    }
                }
            }
        }
    }

    public function pushlottery(){
        $now_box_mac = I('mac','','trim');

        $url = 'https://api-nzb.littlehotspot.com/netty/box/connections';
        $curl = new \Common\Lib\Curl();
        $res_netty = '';
        $curl::get($url,$res_netty,10);
        $res_box = json_decode($res_netty,true);
        if(empty($res_box) || !is_array($res_box) || $res_box['code']!=10000){
            $curl::get($url,$res_netty,10);
            $res_box = json_decode($res_netty,true);
        }
        if(empty($res_box) || !is_array($res_box) || $res_box['code']!=10000){
            echo "netty connections api error \r\n";
            exit;
        }

        if(!empty($res_box['result'])){
            $activity_info = array('hotel_id'=>7,'dish'=>'至尊海鲜大咖1份','start_time'=>'18:00','end_time'=>'18:50',
                'lottery_time'=>'12:00','dish_img'=>'lottery/activity/zzhx.jpg');
            $activity_info['lottery_time'] = time()+3600;
            $activity_info['lottery_time'] = date('Y-m-d H:i:s',$activity_info['lottery_time']);

//            $redis = new \Common\Lib\SavorRedis();
//            $redis->select(1);
//            $key = 'smallapp:simulatelotteryuser';
//            $res_cache = $redis->get($key);
//            if(empty($res_cache)){
//                $limit = "1000,40";
//                $m_user = new \Admin\Model\Smallapp\UserModel();
//                $where = array('nickName'=>array('neq',''));
//                $res_user = $m_user->getWhere('openid,avatarUrl,nickName',$where,'id desc',$limit,'');
//                $redis->set($key,json_encode($res_user),86400*5);
//            }else{
//                $res_user = json_decode($res_cache,true);
//            }
            $limit = "1000,40";
            $m_user = new \Admin\Model\Smallapp\UserModel();
            $where = array('nickName'=>array('neq',''));
            $res_user = $m_user->getWhere('openid,avatarUrl,nickName',$where,'id desc',$limit,'');

            $netty_cmd = C('SAPP_CALL_NETY_CMD');
            $m_netty = new \Admin\Model\Smallapp\NettyModel();
            foreach ($res_box['result'] as $k=>$v){
                if($v['totalConn']>0){
                    foreach ($v['connDetail'] as $cv){
                        $box_mac = $cv['box_mac'];
                        if($box_mac==$now_box_mac){

                            $lottery_openid_id = mt_rand(0,39);
                            $lottery_openid = $res_user[$lottery_openid_id]['openid'];

                            $partake_user = array();
                            foreach ($res_user as $uv){
                                $is_lottery = 0;
                                if($uv['openid']==$lottery_openid){
                                    $is_lottery = 1;
                                }
                                $uv['avatarurl'] = substr($uv['avatarurl'],0,-3);
                                $uv['avatarurl'] = $uv['avatarurl'].'0';
                                $uinfo = array('avatarUrl'=>base64_encode($uv['avatarurl']),'nickName'=>$uv['nickname'],'is_lottery'=>$is_lottery);
                                $partake_user[] = $uinfo;
                            }
                            $lottery = array('dish_name'=>$activity_info['dish'],'dish_image'=>$activity_info['dish_img']);

                            $netty_data = array('action'=>136,'partake_user'=>$partake_user,'lottery'=>$lottery);
                            $message = json_encode($netty_data);
                            echo $message;

                            $push_url = 'http://'.$cv['http_host'].':'.$cv['http_port'].'/push/box';
                            $req_id  = getMillisecond();
                            $box_params = array('box_mac'=>$box_mac,'msg'=>$message,'req_id'=>$req_id,'cmd'=>$netty_cmd);
                            $post_data = http_build_query($box_params);
                            $ret = $m_netty->curlPost($push_url,$post_data);
                            $res_push = json_decode($ret,true);
                            if($res_push['code']==10000){
                                echo "box_mac:$box_mac push ok $ret \r\n";
                            }else{
                                echo "box_mac:$box_mac push error $ret  \r\n";
                            }

                            exit;
                        }
                    }
                }
            }
        }
    }

    public function pushweixin(){
        $config = C('SMALLAPP_CONFIG');
        $prize = '龙虾一份';
        $tips = '请3小时内找餐厅服务员领取';
        $push_wxurl = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token";
        $page_url = "games/pages/activity/din_dash";
        $token = getWxAccessToken($config);
        $tempalte_id = 'HqNYdceqH7MAQk6dl4Gn54yZObVRNG0FJk40OIwa9x4';
        $curl = new \Common\Lib\Curl();
        $miniprogram_state = 'developer';//developer为开发版；trial为体验版；formal为正式版
        $url = "$push_wxurl=$token";
        $lottery_time = date('Y-m-d H:i:s');
        $data=array(
            'date2'  => array('value'=>$lottery_time),
            'thing4'  => array('value'=>'已中奖'),
            'thing1'  => array('value'=>$prize),
            'thing3'  => array('value'=>$tips)
        );
        $openid='ofYZG4yZJHaV2h3lJHG5wOB9MzxE';
        $box_mac = '';
        $activity_id = 1;
        $page_url = "$page_url?openid=$openid&box_mac=$box_mac&activity_id=$activity_id";
        $template = array(
            'touser' => $openid,
            'template_id' => $tempalte_id,
            'page' => $page_url,
            'miniprogram_state'=>$miniprogram_state,
            'lang'=>'zh_CN',
            'data' => $data
        );
        $template =  json_encode($template);
        $res_data = '';
        $curl::post($url,$template,$res_data);
        echo $res_data;
        exit;
    }

    public function cachedishactivity(){
        $king_meal = C('ACTIVITY_KINGMEAL');
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(1);
        $key = 'smallapp:activity:kingmealhotel';
        $hotels = array();
        $now_date = date('Y-m-d 00:00:00');
        foreach ($king_meal as $v){
            foreach ($v as $dv){
                if($dv['start_time']>$now_date){
                    $hotels[$dv['hotel_id']][]=array('start_time'=>$dv['start_time'],'end_time'=>$dv['end_time']);
                }
            }

        }
        echo json_encode($hotels);

        $redis->set($key,json_encode($hotels));
    }

    public function welcometime(){
        $model = M();
        $sql = "select * from savor_smallapp_welcome where play_type=2 and status=3 and add_time>='2020-07-01 00:00:00' order by id desc ";
        $res_data = $model->query($sql);
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_forscreen = new \Admin\Model\SmallappForscreenRecordModel();
        foreach ($res_data as $v){
            $play_time = "{$v['play_date']} {$v['timing']}";
            $create_time = date('Y-m-d H:i:s',strtotime($play_time));

            $user_id = $v['user_id'];
            $res_user = $m_user->getOne('*',array('id'=>$user_id),'');
            $openid = $res_user['openid'];

            $start_time = $v['add_time'];
            $end_time = date('Y-m-d H:i:s',strtotime($start_time)+300);
            $time_condition = "create_time>='$start_time' and create_time<='$end_time'";
            $sql_forscreen = "select * from savor_smallapp_forscreen_record where {$time_condition} and openid='{$openid}' and action=41";
            if($v['type']==1){
                $sql_forscreen.=" and box_mac='{$v['box_mac']}'";
            }
            $res_forscreen = $model->query($sql_forscreen);
            if(!empty($res_forscreen)){
                foreach ($res_forscreen as $fv){
                    $m_forscreen->updateData(array('id'=>$fv['id']),array('create_time'=>$create_time));
                }
                echo "welcome_id:{$v['id']} time ok \r\n";
            }

        }
        echo "finish";
    }

    public function welcome(){
        $model = M();
        $sql = "select * from savor_smallapp_forscreen_record where box_mac=2 and create_time>='2020-07-01 00:00:00' order by id desc ";
        $res_data = $model->query($sql);
        if(!empty($res_data)){
            $m_staff = new \Admin\Model\Integral\StaffModel();
            $m_box = new \Admin\Model\BoxModel();
            $m_forscreen = new \Admin\Model\SmallappForscreenRecordModel();

            foreach ($res_data as $v){
                $forscreen_id = $v['id'];
                unset($v['id']);

                $openid = $v['openid'];
                $fields = 'm.hotel_id as hotel_id';
                $where = array('a.openid'=>$openid,'a.status'=>1,'m.status'=>1);
                $res_merchant = $m_staff->getMerchantStaffInfo($fields,$where);
                if(!empty($res_merchant)){
                    $hotel_id = $res_merchant['hotel_id'];
                    $where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
                    $res_boxs = $m_box->getBoxByCondition('box.mac as box_mac',$where);
                    if(!empty($res_boxs)){
                        foreach ($res_boxs as $bv){
                            $v['box_mac'] = $bv['box_mac'];
                            $forscreen_data = $v;
                            $m_forscreen->add($forscreen_data);
                        }
                    }
                    $sql_del = "delete from savor_smallapp_forscreen_record where id={$forscreen_id}";
                    $model->execute($sql_del);
                    echo "id:$forscreen_id hotel_id:$hotel_id ok \r\n";
                }else{
                    echo "id:$forscreen_id hotel_id:0 error \r\n";
                }
            }
        }
    }

    public function hotelassess(){
        $model = M();
        $sql = "select a.id,a.area_id,a.area_name,a.hotel_id,ext.is_train,a.hotel_name,a.hotel_box_type,a.hotel_level,a.team_name,a.maintainer,a.box_num,a.lostbox_num,a.fault_rate,a.all_assess,
a.operation_assess,a.zxrate,a.channel_assess,a.fjrate,a.data_assess,a.fjsalerate,a.saledata_assess,DATE_FORMAT(a.date,'%Y-%m-%d') as date 
from savor_smallapp_static_hotelassess as a left join savor_hotel_ext as ext on a.hotel_id=ext.hotel_id where a.date>=20200824 and a.date<=20200830";

        $res = $model->query($sql);
        $teams_money = array();
        $assess_money = array('A'=>10,'B'=>15,'C'=>5);
        foreach ($res as $k=>$v){
            $money = $assess_money[$v['hotel_level']];
            if($v['operation_assess']==1){
                $operation_money = $money;
            }else{
                $operation_money = -$money;
            }
            if($v['channel_assess']==1){
                $channel_money = $money;
            }else{
                $channel_money = -$money;
            }
            if($v['is_train']==1){
                if($v['data_assess']==1){
                    $data_money = $money;
                }else{
                    $data_money = -$money;
                }
            }else{
                $data_money = 0;
            }
            if($v['is_train']==1){
                if($v['saledata_assess']==1){
                    $saledata_money = $money;
                }else{
                    $saledata_money = -$money;
                }
            }else{
                $saledata_money = 0;
            }

            $teams_money[$v['team_name']]['channel_money'][]=$channel_money;
            $teams_money[$v['team_name']]['data_money'][]=$data_money;
            $teams_money[$v['team_name']]['operation_money'][]=$operation_money;
            $teams_money[$v['team_name']]['saledata_money'][]=$saledata_money;
        }

        foreach ($teams_money as $k=>$v){
            $teams_money[$k]['channel_money'] = array_sum($v['channel_money']);
            $teams_money[$k]['data_money'] = array_sum($v['data_money']);
            $teams_money[$k]['operation_money'] = array_sum($v['operation_money']);
            $teams_money[$k]['saledata_money'] = array_sum($v['saledata_money']);
        }

        print_r($teams_money);

    }
}