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

    public function clearFnums(){
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $cache_key = C('SAPP_FORSCREEN_NUMS')."*";
        $keys = $redis->keys($cache_key);
        foreach($keys as $v){
            $k_arr = explode(':', $v);
            $openid = $k_arr[3];
            $user_info = $m_user->getOne('is_interact', array('openid'=>$openid));
            if(!empty($user_info) && $user_info['is_interact']==1){
                $redis->remove($v);
            }else {
                
                
                $forscreen_nums_list = $redis->lgetrange($v,0,-1);
                $nums_data = array();
                foreach($forscreen_nums_list as $kk=>$vv){
                    $vv = json_decode($vv,true);
                    $nums_data[] = $vv['forscreen_id'];
                }
                
                
                
                $forscreen_nums = count($nums_data);
                if($forscreen_nums>=5){
                    $m_user->updateInfo(array('openid'=>$openid), array('is_interact'=>1));
                    $redis->remove($v);
                }
            }
        }
        
        echo "ok";exit;
        exit;
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        
        $forscreen_nums_cache_key = C('SAPP_FORSCREEN_NUMS').'ofYZG4yZJHaV2h3lJHG5wOB9MzxE';
        $forscreen_nums_list = $redis->lgetrange($forscreen_nums_cache_key,0,-1);
        $nums_data = array();
        foreach($forscreen_nums_list as $key=>$v){
            $v = json_decode($v,true);
            $nums_data[] = $v['forscreen_id'];
        }
        print_r($nums_data);exit;
        $forscreen_nums = count($nums_data);
        print_r($forscreen_nums);exit;
        
        
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $cache_key = C('SAPP_FORSCREEN_NUMS')."*";
        $keys = $redis->keys($cache_key);
        foreach($keys as $v){
            $k_arr = explode(':', $v);
            $openid = $k_arr[3];
            $user_info = $m_user->getOne('is_interact', array('openid'=>$openid));
            
            if(!empty($user_info) && $user_info['is_interact']==1){
                $redis->remove($v);
            }else {
                $forscreen_nums = $redis->get($v);
                if($forscreen_nums>=5){
                    $m_user->updateInfo(array('openid'=>$openid), array('is_interact'=>1));
                    $redis->remove($v);
                }
            }
        }
        echo 'ok';
    }
    public function checkOutIp(){
        $redis = SavorRedis::getInstance();
        $redis->select(13);
        $keys = 'heartbeat:*';
        $keys_arr = $redis->keys($keys);
  
        foreach($keys_arr as $key=>$v){
            $info = $redis->get($v);
            $info = json_decode($info,true);

            if($info['outside_ip']=='182.18.10.238'){
                print_r($info);
            }
        }
    }
    public function pushSapp(){
        $wechat = new \Common\Lib\Wechat();
        $data = array(
            'touser'=>'o5mZpw4cUfhsqqQRroL8oKswnLQ0',
            'template_id'=>"kTn7TCT1BVbSpE9JASuVgqv5iu8MQ9LgvVBLfSLMLX0",
            'url'=>"",
            'miniprogram'=>array('appid'=>'wxfdf0346934bb672f','pagepath'=>'/pages/index/index'),
            'data'=>array(
                'first'=>array('value'=>'包间设备异常提醒') ,
                'keyword1'=>array('value'=>'测试包间电视'),
                'keyword2'=>array('value'=>'此包间电视未开机，为不影响食客使用及您的积分收益，请及时开机。'),
                'keyword3'=>array('value'=>date('Y-m-d H:i')),
                'keyword4'=>array('value'=>'北京热点投屏科技有限公司。'),
            )
        );
        $data = json_encode($data);
        $res = $wechat->templatesend($data);
        var_dump($res);
    }
    public function removeAdsCache(){
        exit();
        $redis = SavorRedis::getInstance();
        $redis->select(10);
        $keys = "vsmall:ads:*";
        $keys_arr = $redis->keys($keys);
        $flag = 0;
        foreach($keys_arr as $key=>$v){
            $ret = $redis->remove($v);
            if($ret){
                $flag++;
            }
        }
        echo $flag;exit;
    }
    
    public function countForscreenNum(){
        $start_time = I('start_time');
        $end_time   = I('end_time');
        $where = '';
        if(!empty($start_time)){
            $where .=" and create_time>='".$start_time."'";
        }
        if(!empty($end_time)){
            $where .= " and create_time <='".$end_time."'";
        }

        //视频投屏
        $sql = "select sum(resource_size) as all_resource_size from savor_smallapp_forscreen_record where action = '2' AND resource_type = '2' ".$where;
        //echo $sql;exit;
        $ret  = M()->query($sql);
        $all_resource_size = $ret[0]['all_resource_size'];
        $all_resource_size = round(($all_resource_size/1024)/(1024*1024),2);
        echo '视频投屏资源大小：'.$all_resource_size."G";
    }

    private function curlPost($url = '',  $post_data = ''){
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $post_data,
        CURLOPT_HTTPHEADER => array(
        "Content-Type: application/x-www-form-urlencoded",
        ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return 0;
        } else {
    
            return $response;
        }
    }
    /**
     * @desc 推送饭点中提醒服务员引导客人评价（机顶盒弹幕）
     */
    public function pushRemindComment(){
        exit();
        $sql = "SELECT staff.room_id,staff.hotel_id,box.mac box_mac,
                user.nickName,user.avatarUrl
                FROM `savor_integral_merchant_staff` staff
                left join savor_room room on staff.room_id=room.id
                left join savor_box box on room.id = box.room_id
                left join savor_hotel hotel on hotel.id=room.hotel_id
                left join savor_smallapp_user user on staff.openid= user.openid
                WHERE staff.level in(2,3) and staff.status =1 and 
                staff.hotel_id!=0 and staff.room_id!=0 and hotel.state=1 
                and hotel.flag=0 and box.state=1 and box.flag=0";
        $staff_box_list = M()->query($sql);
        $post_data = http_build_query($netty_data);
        $nettyBalanceURL = C('NETTY_BALANCE_URL');
        
        $staff_box_list = array(array('room_id'=>10498,'hotel_id'=>7,'box_mac'=>'00226D583D92','nickName'=>'jet','avatarUrl'=>'https://thirdwx.qlogo.cn/mmopen/vi_32/50q6nBfu9QmWUz8vOY6ibibRM4M3fibXjUhic9d8n3bsAGzvsNMmH5BajJNu6kJbianHWCCkkc77Cnas7B41bKCrdTA/132'));
        $barrage = '亲,别忘了扫码评价哦~';
        foreach($staff_box_list as $key=>$v){
            
            $box_mac = $v['box_mac'];
            
            $req_id = getMillisecond();
            
            $post_data = array('box_mac'=>$box_mac,'req_id'=>$req_id);
            
            $post_data = http_build_query($post_data);
            
            $result = $this->curlPost($nettyBalanceURL, $post_data);
            $result_postion = json_decode($result,true);
            
            if($result_postion['code']==10000){
                $req_id = getMillisecond();
                if(!empty($v['avatarUrl'])){
                    $head_pic = base64_encode($v['avatarUrl']);
                }
                $user_barrages[] = array('nickName'=>$v['nickName'],'headPic'=>$head_pic,'avatarUrl'=>$v['avatarUrl'],'barrage'=>$barrage);
                $msg = array('action'=>122,'userBarrages'=>$user_barrages);
                
                
                $netty_data = array('box_mac'=>$box_mac,'cmd'=>'call-mini-program','msg'=>json_encode($msg),'req_id'=>$req_id);   
                $post_data = http_build_query($netty_data);
                
                $netty_push_url = 'http://'.$result_postion['result'].'/push/box';
                $ret = $this->curlPost($netty_push_url,$post_data);
                $netty_result = json_decode($ret,true);
                print_r($netty_result);exit;
            }
        }
        
        echo "OK";
        
        #redis_conn.set('hello','world')
        //rets = requests.post('https://api-nzb.littlehotspot.com/netty/box/connections',data='showFields=box_mac,http_host,http_port',headers=headers);
    }
    public function pushRemindPowerOn(){
        exit();
        $wechat = new \Common\Lib\Wechat();
        $access_token = $wechat->getWxAccessToken();
        
        $sql = "SELECT user.wx_mpopenid,staff.room_id,staff.hotel_id 
                FROM `savor_integral_merchant_staff` staff 
                left join savor_smallapp_user user on staff.openid= user.openid WHERE staff.level in(2,3) and staff.status =1 and staff.hotel_id!=0 and staff.room_id!=0 and user.mpopenid!='' ";
        $user = M()->query($sql);
        
        foreach($user as $key=>$v){
            $res = $wechat->getWxUserDetail($access_token ,$v['wx_mpopenid']);
            if($res['subscribe']){
                
                $sql ="select box.id box_id from savor_box box
                       left join savor_room room on box.room_id=room.id
                       left join savor_hotel hotel on room.hotel_id=hotel.id
                       where room.id=".$v['room_id'].' and hotel.id='.$v['hotel_id'].' and hotel.state=1 and hotel.flag=0
                       and box.state=1 and box.flag = 0';
                $box_list = M()->query($sql);
                $now_date = date('Ymd');
                foreach($box_list as $kk=>$vv){
                    //判断机顶盒11:00 - 12:00有没有开机(心跳)
                    $sql ="select hour11 from savor_heart_all_log where date=".$now_date.' and box_id='.$vv['box_id'].' and type=2';
                    $heart_list = M()->query($sql);
                    if(empty($heart_list) || $heart_list[0]['hour11']==0){
                        $data = array(
                            'touser'=>$v['wx_mpopenid'],
                            'template_id'=>"8HdJeBWn7ZmpKWYQgH17A5ZaD75CxL8zrFcNoTzmDqg",
                            'url'=>"",
                            /*'miniprogram'=>array(
                             'appid'=>'wxfdf0346934bb672f',
                                'pagepath'=>'pages/index/index',
                            ),*/
                            'data'=>array(
                                'first'=>array('value'=>'您好，您的会员积分信息有了新的变更。') ,
                                'keyword1'=>array('value'=>'jet'),
                                'keyword2'=>array('value'=>6009891111),
                                'keyword3'=>array('value'=>300,),
                                'keyword4'=>array('value'=>1200),
                                'remark'=>array('value'=>'如有疑问，请拨打123456789.','color'=>"#FF1C2E"),
                            )
                        );
                        $data = json_encode($data);
                        $res = $wechat->templatesend($data);
                    }    
                }
            } 
        }
    }
    /**
     * @desc 计算用户使用了多少次(一天内有扫码有一次)
     */
    public function countUseSmall(){
        exit();
        $sql ="select openid from savor_smallapp_user where small_app_id=1 and create_time>'2020-09-23 09:26:14' group by openid"; 
        
        $openid_list = M()->query($sql);
        foreach($openid_list as $key=>$v){
            $sql ="SELECT DATE_FORMAT(create_time,'%Y-%m-%d') AS ad_time FROM `savor_smallapp_qrcode_log` WHERE openid='".$v['openid']."' GROUP BY ad_time";
            
            $ret = M()->query($sql);
            $use_time = count($ret);
            if($use_time>0){
                $sql = "update savor_smallapp_user set use_time= use_time +$use_time where openid='".$v['openid']."'";
                M()->execute($sql);
            }
        }
        echo date('Y-m-d H:i:s').'OK';
    }
    //维护数据统计主干版用户使用小程序次数
    public function countUseQrcode(){
        exit();
        $date = strtotime('-1 day');
        $yesterday_start_time = date('Y-m-d 00:00:00',$date);
        $yesterday_end_time = date('Y-m-d 23:59:59',$date);
        $sql ="select openid from savor_smallapp_qrcode_log where `create_time`>'".$yesterday_start_time."' and `create_time`<'".$yesterday_end_time."' group by openid";
        
        $user_list = M()->query($sql);
        foreach($user_list as $key=>$v){
            $sql = "update savor_smallapp_user set use_time= use_time +$use_time where openid='".$v['openid']."'";
            M()->execute($sql);
        }
        echo date('Y-m-d H:i:s').'OK';
        
    }
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

    public function updatehotelcache(){
        $redis = SavorRedis::getInstance();
        $redis->select(15);

        $sql ="select * from savor_hotel";
        $data = M()->query($sql);
        foreach($data  as $key=>$v){
            $hotel_id = $v['id'];
            $hotel_info = $v;
            $hotel_cache_key = C('DB_PREFIX').'hotel_'.$hotel_id;
            $redis->set($hotel_cache_key, json_encode($hotel_info));
            echo "hotel_id:{$v['id']} ok \r\n";
        }
//        $sql ="select * from savor_hotel_ext";
//        $data = M()->query($sql);
//        foreach ($data as $key=>$v){
//            $hotel_id = $v['hotel_id'];
//            $hotel_ext_cache_key = C('DB_PREFIX').'hotel_ext_'.$hotel_id;
//            $hotel_ext_info = $v;
//            $redis->set($hotel_ext_cache_key, json_encode($hotel_ext_info));
//        }

    }

    public function removeHotelinfoCache(){
        $redis = SavorRedis::getInstance();
        $redis->select(15);

        $sql ="select * from savor_hotel where state=1 and flag=0";
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

    public function updateboxcache(){
        exit;
        $redis = SavorRedis::getInstance();
        $redis->select(15);

//        $close_forscreen_boxs = $this->closeboxforscreen();

        $sql = "select box.* from savor_box box
                left join savor_room room on box.room_id=room.id
                left join savor_hotel hotel on room.hotel_id=hotel.id
                where hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0";
//                where hotel.area_id=236 and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0";
//        $sql = "SELECT box.* FROM savor_box box LEFT JOIN savor_room room ON box.room_id=room.id LEFT JOIN savor_hotel hotel ON room.hotel_id=hotel.id WHERE hotel.state=1 AND hotel.flag=0 AND box.state=1 AND box.flag=0 AND box.mac IN (SELECT box_mac FROM savor_smallapp_forscreen_record WHERE small_app_id IN (2,3) AND create_time>='2019-10-01 00:00:00' AND create_time<='2019-12-10 13:00:00' GROUP BY box_mac)";

//        $sql = "select box.* from savor_box box
//                left join savor_room room on box.room_id=room.id
//                left join savor_hotel hotel on room.hotel_id=hotel.id
//                where box.state=1 and box.flag=0 and box.mac in('00226D8BCC87','00226D583D1E','00226D5841A1','00226D8BCAD9','00226D8BCE36','00226D8BCA81','00226D8BCA67','00226D58473F','00226D8BC963','00226D8BCE8E','00226D8BCE87','00226D8BCCBA','40E7932536F8','00226D655107','00226D5844AD','00226D655239','00226D8BCD72','00226D655464','00226D584717','00226D8BC9AD','00226D65515F','00226D5846E9','00226D655571','00226D6553DC','00226D65548E','00226D8BC969','00226D5840F0','00226D8BCC3D','00226D8BCD39','00226D8BCB37','00226D8BCA03','00226D8BC956','00226D8BCDB1','00226D655398','40E7932533FB','40E793253755','40E793253563','40E7932533FA','40E793253410','00226D8BCB0D','00226D8BCD60','00226D8BCCF6','00226D8BCD0B','00226D8BCC2A','00226D8BCD35','00226D8BCC64','00226D8BCE62','00226D8BC9EA','00226D8BCB42','00226D8BCD04','00226D8BCBE7','00226D8BCD2F','00226D8BCCE0','00226D8BCC33','00226D8BCB63','00226D8BCD1A','00226D8BC9DC','00226D8BCDF9','00226D8BCE88','00226D8BCE95','00226D8BCAD5','00226D8BCB16','00226D8BC959','00226D8BCDF7','00226D8BCDEE','00226D8BCA04','40E79325342F')";

        $data = M()->query($sql);
        $flag = 0;
        foreach($data as $key=>$v){
//            if($v['is_open_simple']==1 && $v['is_sapp_forscreen']==0){
//                $v['is_sapp_forscreen'] = 1;
//            }elseif($v['is_open_simple']==1 && $v['is_sapp_forscreen']==1){
//                $v['is_open_simple'] = 0;
//            }
//            if(isset($close_forscreen_boxs[$v['mac']])){
//                $v['is_interact'] = 0;
//                $v['is_sapp_forscreen'] = 0;
//                $v['is_open_simple'] = 1;
//                $is_open_simple = $v['is_open_simple'];
//                $is_sapp_forscreen = $v['is_sapp_forscreen'];
//                $is_interact = $v['is_interact'];
//                $sql ="update savor_box set is_interact=$is_interact,is_open_simple=$is_open_simple,is_sapp_forscreen=$is_sapp_forscreen where id=".$v['id'].' limit 1';
//                M()->execute($sql);
//                echo $v['mac']." close ok \n";
//            }
//            $v['is_interact'] = 0;
//            $v['is_sapp_forscreen'] = 0;
//            $v['is_open_simple'] = 0;
//            $is_open_simple = $v['is_open_simple'];
//            $is_sapp_forscreen = $v['is_sapp_forscreen'];
//            $is_interact = $v['is_interact'];
            $sql ="update savor_box set is_4g={$is_4g} where id=".$v['id'].' limit 1';
            M()->execute($sql);
            echo $v['mac']." is_4g:$is_4g ok \n";


            $box_info = array();
            $box_id = $v['id'];
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
        $start = I('get.start',100,'intval');
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        $res = array();
        $money = array(20,50,100,10);
        foreach ($area_arr as $k=>$v){
            $area_id = $v['id'];
            $area_name = $v['region_name'];
            $offset = $start+($k*50);
            $sql = "select avatarUrl,nickName from savor_smallapp_user where small_app_id=1 and nickName!='' and unionId='' order by id desc limit $offset,50";
            $res_user = $m_area->query($sql);
            foreach ($res_user as $uv){
                shuffle($money);
                $u_money = $money[0];
                $info = array('area_id'=>$area_id,'area_name'=>$area_name,'avatar_url'=>$uv['avatarurl'],'name'=>$uv['nickname'],'money'=>$u_money);
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

    public function forscreen(){
        $mac = I('mac','');
        $f_url = I('f','');
        $redis = new \Common\Lib\SavorRedis();
        $boxs = array();
        if(!empty($mac)){
            $boxs[]=$mac;
        }
        $file_info = pathinfo($f_url);
        $file_name = $file_info['basename'];

        $message_data = array('openid'=>'ofYZG455VSavvB3fumKZKXlE50_Q','action'=>2,'resource_type'=>2,'forscreen_char'=>'',
            'mobile_brand'=>'HUAWEI','mobile_model'=>'TAS-AN00','resource_size'=>7741280,'imgs'=>'["forscreen/resource/'.$file_name.'"]');

        $push_boxs = array();
        if(!empty($boxs)){
            foreach ($boxs as $b){
                $now_timestamps = getMillisecond();
                $message_data['forscreen_id'] = $now_timestamps;
                $message_data['box_mac'] = $b;
                $message_data['resource_id'] = $now_timestamps;
                $message_data['res_sup_time'] = $now_timestamps;
                $message_data['res_eup_time'] = $now_timestamps;
                $message_data['create_time'] = date('Y-m-d H:i:s');

                $netty_data = array('action'=>2,'resource_type'=>2,'url'=>"forscreen/resource/$file_name",'filename'=>"$file_name",
                    'openid'=>'ofYZG455VSavvB3fumKZKXlE50_Q','video_id'=>$now_timestamps,'forscreen_id'=>$now_timestamps
                );
                $msg = json_encode($netty_data);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://mobile.littlehotspot.com/Netty/index/pushnetty",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => array('box_mac'=>$b,'msg'=>"$msg"),
                ));
                $response = curl_exec($curl);
                curl_close($curl);
                $res = json_decode($response,true);
                if(is_array($res) && isset($res['code'])){
                    $push_boxs[]=$b;
                    $cache_key = 'smallapp:forscreen:'.$b;
                    $redis->select(5);
                    $redis->rpush($cache_key, json_encode($message_data));
                }
            }
        }
        echo 'push box:'.json_encode($push_boxs)." OK \r\n";
    }

    public function hotellevel(){
        $file_path = SITE_TP_PATH.'/Public/content/酒楼更改级别1102.xlsx';
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");

        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $other_hotel = array();
        $m_hotel = new \Admin\Model\HotelModel();
        $model = M();
        $all_hotel_level = array();
        for ($row = 2; $row <= $highestRow; $row++){
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            if(!empty($rowData[0][0])){
                $hotel_name = trim($rowData[0][0]);
                $res_hotel = $m_hotel->getInfo('*',array('name'=>$hotel_name,'state'=>1,'flag'=>0),'id desc','0,1');
                if(!empty($res_hotel)){
                    $hotel_id = $res_hotel[0]['id'];
                    $hotel_level = $rowData[0][1];
                    $all_hotel_level[$hotel_id] = $hotel_level;
//                    $sql = "update savor_smallapp_static_hotelassess set hotel_level='{$hotel_level}' where hotel_id={$hotel_id}";
//                    $res = $model->execute($sql);
//                    if($res){
//                        echo "hotel_id:$hotel_id ok\r\n";
//                    }
                }else{
                    $other_hotel[]=$hotel_name;
                }
            }
        }
        return $all_hotel_level;
    }

    public function cachehotelassess(){
        $all_hotel_level = $this->hotellevel();

        $file_path = SITE_TP_PATH.'/Public/content/广州考核酒楼1111.xlsx';
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

                    if(isset($all_hotel_level[$res_hotel[0]['id']])){
                        $hotel_level = $all_hotel_level[$res_hotel[0]['id']];
                    }else{
                        $hotel_level = $rowData[0][5];
                    }

                    $hotel_info[$res_hotel[0]['id']] = array('hotel_id'=>$res_hotel[0]['id'],'hotel_box_type'=>$res_hotel[0]['hotel_box_type'],
                        'hotel_name'=>$hotel_name,
                        'area_id'=>236,'area_name'=>$rowData[0][1],'hotel_level'=>$hotel_level,'team_name'=>$rowData[0][4],'maintainer'=>$rowData[0][6]);
                }else{
                    $other_hotel[]=$hotel_name;
                }
            }
        }
        $redis->select(1);
        $key = 'smallapp:hotelassess';
        $redis->set($key,json_encode($hotel_info));
        print_r($hotel_info);
        print_r($other_hotel);
        exit;
    }

    public function handleforscreen(){
        echo "start_time:".date('Y-m-d H:i:s')."\r\n";
        $m_forscreen = new \Admin\Model\ForscreenRecordModel();
        $m_forscreen->syncForscreendata();
        echo "end_time:".date('Y-m-d H:i:s')."\r\n";
    }

    public function hotelassess(){
        $m_statichotelassess = new \Admin\Model\Smallapp\StaticHotelassessModel();
        $m_statichotelbasicdata = new \Admin\Model\Smallapp\StaticHotelbasicdataModel();
        $res_data = $m_statichotelassess->getDataList('*',array(),'id asc');
        $config = $m_statichotelassess->assessConfig();

        foreach ($res_data as $v){
            $time_date = strtotime($v['date']);
            $hotel_id = $v['hotel_id'];
            $data = array();
            /*
            $data['operation_assess'] = 1;
            if($v['fault_rate']>$config[$v['hotel_level']]['fault_rate']){
                $data['operation_assess'] = 2;
            }
            $data['channel_assess'] = 1;
            if($v['zxrate']<$config[$v['hotel_level']]['zxrate']){
                $data['channel_assess'] = 2;
            }
            $data['data_assess'] = 1;
            if($v['fjrate']<$config[$v['hotel_level']]['fjrate']){
                $data['data_assess'] = 2;
            }
            $data['saledata_assess'] = 1;
            if($v['fjsalerate']<$config[$v['hotel_level']]['fjsalerate']){
                $data['saledata_assess'] = 2;
            }
            $data['all_assess'] = 1;
            if($data['operation_assess']==2 || $data['channel_assess']==2 || $data['data_assess']==2 || $data['saledata_assess']==2){
                $data['all_assess'] = 2;
            }
            $res = $m_statichotelassess->updateData(array('id'=>$v['id']),$data);
            if($res){
                echo "id:{$v['id']}--{$v['date']} ok \r\n";
            }else{
                echo "id:{$v['id']}--{$v['date']} fail \r\n";
            }
            */

            $res_hoteldata = $m_statichotelbasicdata->getInfo(array('static_date'=>date('Y-m-d',$time_date),'hotel_id'=>$hotel_id));
            $zxnum = $wlnum = $user_zxhdnum = $sale_zxhdnum = $zxhdnum = 0;
            if(!empty($res_hoteldata)){
                $wlnum = $res_hoteldata['wlnum'];
                $zxnum = $res_hoteldata['lunch_zxnum'] + $res_hoteldata['dinner_zxnum'];
                $user_zxhdnum = $res_hoteldata['user_lunch_zxhdnum'] + $res_hoteldata['user_dinner_zxhdnum'];
                $sale_zxhdnum = $res_hoteldata['sale_lunch_zxhdnum'] + $res_hoteldata['sale_dinner_zxhdnum'];
                $zxhdnum = $res_hoteldata['lunch_zxhdnum'] + $res_hoteldata['dinner_zxhdnum'];
            }
            $data = array();
            $data['zxnum'] = $zxnum;
            $data['wlnum'] = $wlnum;
            $data['user_zxhdnum'] = $user_zxhdnum;
            $data['sale_zxhdnum'] = $sale_zxhdnum;
            $data['zxhdnum'] = $zxhdnum;

            $zxrate = 0;
            if($zxnum && $wlnum){
                $total_wlnum = $wlnum * 2;
                $zxrate = sprintf("%.2f",$zxnum/$total_wlnum);
            }
            $data['zxrate'] = $zxrate;
            $data['channel_assess'] = 1;
            if($data['zxrate']<$config[$v['hotel_level']]['zxrate']){
                $data['channel_assess'] = 2;
            }
            $fjrate = 0;
            if($user_zxhdnum && $zxhdnum){
                $fjrate = sprintf("%.2f",$user_zxhdnum/$zxhdnum);
            }

            $data['fjrate'] = $fjrate;
            $data['data_assess'] = 1;
            if($data['fjrate']<$config[$v['hotel_level']]['fjrate']){
                $data['data_assess'] = 2;
            }
            $fjsalerate = 0;
            if($sale_zxhdnum && $zxhdnum){
                $fjsalerate = sprintf("%.2f",$sale_zxhdnum/$zxhdnum);
            }
            $data['fjsalerate'] = $fjsalerate;
            $data['saledata_assess'] = 1;
            if($data['fjsalerate']<$config[$v['hotel_level']]['fjsalerate']){
                $data['saledata_assess'] = 2;
            }
            $data['all_assess'] = 1;
            if($data['operation_assess']==2 || $data['channel_assess']==2 || $data['data_assess']==2 || $data['saledata_assess']==2){
                $data['all_assess'] = 2;
            }
            $res = $m_statichotelassess->updateData(array('id'=>$v['id']),$data);
            if($res){
                echo "id:{$v['id']}--{$v['date']} ok \r\n";
            }else{
                echo "id:{$v['id']}--{$v['date']} fail \r\n";
            }

        }
    }

    public function hotelbasicdata(){
        $where = array();
        $where['static_date'] = array(array('EGT','2021-08-01'),array('ELT','2021-08-23'));
        $m_statichotelbasicdata = new \Admin\Model\Smallapp\StaticHotelbasicdataModel();
        $res_data = $m_statichotelbasicdata->getDataList('id,hotel_id,static_date',$where,'id asc');
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $m_heartlog = new \Admin\Model\HeartAllLogModel();
        foreach ($res_data as $v){
            $hotel_id = $v['hotel_id'];
            $time_date = strtotime($v['static_date']);
            $date = date('Ymd',$time_date);


            $room_heart_num = $m_heartlog->getHotelAllHeart($date,$hotel_id,1);
            $room_meal_heart_num = $m_heartlog->getHotelMealHeart($date,$hotel_id,1);
            /*
            $lunch_zxhdnum = $m_heartlog->getHotelOnlineBoxnum($date,$hotel_id,1,1);
            $dinner_zxhdnum = $m_heartlog->getHotelOnlineBoxnum($date,$hotel_id,2,1);

            $user_lunch_zxhdnum = $v['user_lunch_zxhdnum'];
            $user_dinner_zxhdnum = $v['user_dinner_zxhdnum'];

            $user_lunch_cvr = $user_dinner_cvr = 0;
            if($user_lunch_zxhdnum && $lunch_zxhdnum){
                $user_lunch_cvr = sprintf("%.2f",$user_lunch_zxhdnum/$lunch_zxhdnum);
            }
            if($user_dinner_zxhdnum && $dinner_zxhdnum){
                $user_dinner_cvr = sprintf("%.2f",$user_dinner_zxhdnum/$dinner_zxhdnum);
            }

            $sale_lunch_zxhdnum = $v['sale_lunch_zxhdnum'];
            $sale_dinner_zxhdnum = $v['sale_dinner_zxhdnum'];

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

            $wlnum = $v['wlnum'];
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

            $interact_sale_signnum = $m_smallapp_forscreen_record->getSaleSignForscreenNumByHotelId($hotel_id,$time_date);

            $data = array(
                'lunch_zxhdnum'=>$lunch_zxhdnum,'user_lunch_cvr'=>$user_lunch_cvr,'dinner_zxhdnum'=>$dinner_zxhdnum,'user_dinner_cvr'=>$user_dinner_cvr,
                'sale_lunch_cvr'=>$sale_lunch_cvr,'sale_dinner_cvr'=>$sale_dinner_cvr,'lunch_zxnum'=>$lunch_zxnum,'dinner_zxnum'=>$dinner_zxnum,
                'lunch_zxrate'=>$lunch_zxrate,'dinner_zxrate'=>$dinner_zxrate,'zxnum'=>$zxnum,'zxrate'=>$zxrate,
                'interact_sale_signnum'=>$interact_sale_signnum,
            );
            */
            $data = array('room_heart_num'=>$room_heart_num,'room_meal_heart_num'=>$room_meal_heart_num);
            $res = $m_statichotelbasicdata->updateData(array('id'=>$v['id']),$data);
            if($res){
                echo "id:{$v['id']}--{$v['static_date']} ok \r\n";
            }else{
                echo "id:{$v['id']}--{$v['static_date']} fail \r\n";
            }
        }
    }




    public function assessmoney(){
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

    public function closeboxforscreen(){
        $start_time = '2020-10-03 00:00:00';
        $end_time = '2020-12-03 00:00:00';
        $boxs = array('40E793253499','00226D8BCD91','00226D8BCD46','00226D8BCA02','00226D8BCCDC','00226D8BCB45','00226D8BCE5B','00226D8BCE3B','00226D8BCE41','00226D8BCE2A','00226D8BCC73','00226D8BCDB2','40E79325345D','00226D583CB3','00226D5844E1','00226D8BCCD3','00226D8BCE7A','GZXWJD001482 ','GZXWJD001494','GZXWJD001495','40E793253706','00226D8BCD1F ','00226D8BCE52','00226D584045','00226D5846E6','00226D583FB0','00226D65557A','00226D655625','00226D65565D','00226D655266','00226D6552FF ','00226D655529','00226D655422','00226D6554FA','00226D6554D7 ','40E79325351C','00226D8BCABC','00226D583D16','00226D8BCBC7','GZXWJD000045','GZXWJD000038','00226D583ED7','00226D6553D4','00226D583DC3','00226D583E04','00226D583FB9','00226D583DBD','00226D583D30','00226D5843AC','40E793253682','40E793253686','00226D6554CE','40E793253412','40E793253715','40E793253408','40E7932535FA','00226D8BCBD2','00226D583CDF','00226D6554C0','00226D6552D6','00226D8BCD88','00226D583F65','00226D8BCDA2','00226D8BCDBC','00226D8BCD52','00226D8BCAE2','00226D8BCC49','00226D8BCAD7','00226D8BC97B','00226D8BCDC1','00226D8BC950','40E7932536C6','00226D65551C','00226D8BC944','00226D6551FA','00226D8BCCEF','00226D8BCBC4','00226D8BC995','00226D8BCC56','00226D6551D4','00226D58468F','00226D6554A5','00226D655183','00226D8BC9F1','00226D8BCA1D','00226D8BCAE3','00226D8BCD10','00226D8BCC96','00226D8BCA13','00226D8BCE6A','00226D8BCADA','00226D655607','00226D8BCD7B','00226D584330','00226D8BCB3F','00226D8BCCE1','00226D8BCD65','00226D8BCBC8','00226D583D13','00226D655398','00226D583F3C','40E793253511','00226D8BCDFA','00226D583ED4','00226D8BCC30','00226D8BCB7B','00226D8BC970','00226D8BCD1F','00226D5844DF','40E79325340E','00226D8BCE0B','00226D8BCAF2','40E7932536C2','40E7932533FF','00226D8BCA27','40E79325373B','40E79325345F','40E7932535F5','00226D8BC94D','00226D8BCBBF','00226D8BCAEA','00226D8BCB06','00226D8BCDBE','00226D8BCC89','00226D8BCE37','00226D8BCDBF','00226D8BCB3E','00226D8BCDA6','00226D8BCAF5','00226D8BCCB1','00226D8BCDD1','00226D8BCD9B','00226D8BCC3B','00226D8BCE7E','00226D8BCC7F','40E793253726','00226D8BCCF7','00226D58407F','40E793253614','00226D8BCD5B','00226D8BCB9C','00226D8BCE67','00226D584446','00226D8BC987','40E793253751','00226D583F3B','00226D584725','00226D583F41','00226D583F4A','00226D5841FB','00226D583F47','40E7932536CE','40E79325374F','00226D583DAC','00226D8BCC01','00226D583E10','00226D58418E','00226D584670','00226D584709','40E7932534B6','00226D584711','00226D583F85','00226D5846DD','00226D584710','40E7932534B8','00226D583F01','00226D5846A1','00226D58466A','00226D8BCC5E','00226D655415','40E7932534B3','00226D5846EF','00226D583E22','00226D584707','00226D58431D','00226D583E23','00226D5842B1','00226D65543F','40E7932536B0','40E793253607','00226D8BCC9C','00226D583D4E','00226D8BC9E0','00226D5840EE','40E793253764','40E7932535C9','40E793253765','00226D6554F6','00226D6553F9','00226D6554ED','00226D655594','00226D655657','40E7932535A9','40E793253481','00226D65510C','00226D65525D','00226D6554CD','00226D65531D','00226D6551ED','00226D655640','00226D65549E','00226D655130','00226D6555B0','00226D58409A','00226D655649','40E79325344C','00226D5845C0','00226D6553D8','00226D583EEE','00226D583EF4','40E793253515','00226D8BCC4D','00226D8BCA12','00226D8BCD37','00226D8BCDB4','00226D8BCE8C','00226D8BCD42','40E7932534A7','40E7932535BF','40E7932533F5','00226D8BCC92','00226D8BCE2C','00226D8BCA05','00226D8BCBFE','00226D8BCC5F','00226D8BCC43','00226D8BC9DD','00226D8BCE93','00226D8BC982','00226D8BCC75','40E79325789D','40E7932534CB','00226D8BCD7D','00226D8BCC23','00226D8BCC23','00226D65565B','00226D8BCB52','40E7932534E8','40E7932534E8','00226D8BC9F3','00226D8BCD57','00226D5840F3','00226D58460F','00226D655417','00226D655542','00226D655617','00226D8BC9BC','00226D583DDC','00226D8BCAFC','00226D8BC9C0','40E79325369D','40E793253612','40E79325363C','40E7932535B8','00226D8BCA1E','40E7932534A9','00226D8BCCBE','40E7932534FE','40E79325341D','40E7932536F8','40E79325368D','40E793253723','00226D8BCB86','00226D8BCE0A','00226D8BCE10','00226D8BCB4E','00226D8BCA61','00226D8BCB1E','00226D8BCC8A','00226D8BC9E1','00226D8BC98B','00226D655509','00226D6555D7','00226D6554DD','00226D6551B4','00226D8BC9DF','00226D6555F7','00226D8BCE6B','40E79325361C','00226D8BCB6E','40E7932536C1','00226D8BCA24','40E79325357C','00226D8BC942','00226D8BC94B','00226D583FBD','40E793253730','00226D5844AA','00226D65515F','00226D5846E9','00226D8BC9AD','00226D655571','00226D8BCB21','00226D8BC983','00226D8BC9F9','00226D8BC94C','00226D8BCD62','40E79325787C','00226D8BCBA0','00226D8BCCB8','00226D8BCE72','00226D8BCB22','40E793253713','00226D8BCD7A','00226D8BCAB0','00226D5846CB','00226D584673','00226D5840C2','00226D8BCE91','40E7932534FA','00226D8BCDA7','00226D8BCE8B','00226D8BCE89','00226D8BCE96','00226D8BCE85','00226D8BC985','40E79325350E','00226D8BCBBD','00226D8BC962','40E79325349A','40E79325368B','40E793253601','40E7932534F2','40E79325355D','40E7932536D1','00226D8BCC34','40E793253414','00226D8BCC2D','40E793253495','40E793253619','00226D8BC967','00226D8BCBEF','40E79325788F','40E793257895','40E7932578B8','40E79325787E','00226D584567','00226D58476B','00226D583C92','00226D583EC4','00226D584588','00226D5846E3','40E79325345A','40E79325360E','40E793257876','40E7932535C8','40E793257867','40E793257889','40E79325786D','40E7932535F4','40E793257874','40E793253618','40E79325786A','40E79325787A','00226D5840AB','00226D584462','00226D8BCC18','00226D8BCD41','00226D8BCC05','00226D8BCA15','00226D8BCDD9','00226D8BCB54','40E79325371B','00226D655351','00226D655281','40E79325363B','40E793253537','40E79325370A','00226D583F81','00226D583FB1','00226D584560','00226D583E0E','00226D5840BA','00226D583D1C','00226D584288','00226D58442B','00226D584278','00226D5845D0','00226D583D18','00226D58428C','00226D584622','00226D583F5C','00226D58460D','00226D5841CD','00226D584239','00226D583E0D','00226D583E0F','40E793253659','40E79325372C','00226D8BCA43','00226D8BCD89','00226D8BCD8E','00226D8BCC14','00226D583E31','00226D8BCB58','40E79325362A','00226D8BCD2E','40E79325340C','00226D8BCB43','00226D8BCC95','00226D8BCC54','40E7932536FE','00226D8BCCCD','00226D8BCD2B','00226D584216','00226D584770','00226D584631','00226D583F0B','00226D583F3F','00226D5843F6','00226D583D79','40E7932536F7','40E793253646','40E7932535FC','40E793253642','40E793253758','40E793253655','40E793253428','40E79325373C','40E7932536D3','00226D8BCD9F','00226D8BCB07','00226D8BCCC4','40E79325346B','40E793253568','40E793253465','40E7932535B4','40E793253451','40E7932535F7','40E793253580','40E7932535D2','40E793253732','00226D8BC95C','00226D8BC9B4','40E7932536CA','00226D584310','40E79325372D','40E7932536F6','40E79325365A','40E793253662','40E79325362E','40E793253656','40E7932534C5','40E793253687','40E79325364B','40E7932536E5','40E7932536E8','40E7932535D1','40E79325374B','40E793253650','40E793253688','40E7932578B0','40E7932534F0','40E7932536E4','40E7932535DC','40E7932536F3','40E7932536A5','40E7932534E6','40E79325369E','40E7932535F8','00226D58424F','00226D583C8F','00226D584614','00226D5841E2','00226D584253','00226D5845D2','00226D6553FA','00226D5845B4','00226D655632','40E793253562','00226D8BCBB0','40E7932534ED','40E793253415','40E79325372B','40E7932536C8','40E793253474','00226D8BCCE5','40E79325350C','40E79325786F','00226D8BCAE1','40E7932578B1','00226D8BC9D6','40E793253756');
        $model = M();
        $forscreen_boxs = array();
        $handle_boxs = array();
        foreach ($boxs as $v){
            $time_where = "create_time>='{$start_time}' and create_time<='{$end_time}'";
            $sql = "select count(*) as num from savor_smallapp_forscreen_record where box_mac='{$v}' and {$time_where} and small_app_id in(1,2)";
            $res_num = $model->query($sql);
            $num = intval($res_num[0]['num']);
            if(!empty($res_num) && $num>20){
                $forscreen_boxs[]=array('box'=>$v,'num'=>$num);
            }else{
                $handle_boxs[$v]=array('box'=>$v,'num'=>$num);
            }
        }
        return $handle_boxs;
    }

    public function staticmealuser(){
        $start_time = '2020-08-01 00:00:00';
        $end_time = '2020-11-31 23:59:59';
        $user_sql = "SELECT openid FROM savor_smallapp_forscreen_record 
        WHERE create_time >= '$start_time' AND create_time <= '$end_time' and area_id=236
        and mobile_brand!='devtools' AND is_valid = 1 AND small_app_id in(1,2) AND openid not in (
        select u.openid from (
        (select openid from savor_smallapp_user where unionId in(
        select unionId from savor_smallapp_user where openid in(select openid from savor_smallapp_user_signin group by openid) 
        and unionId!='' group by unionId
        ) and small_app_id=1) union (select invalidid as openid from savor_smallapp_forscreen_invalidlist where type=2)
        ) as u
        ) 
        group by openid";
        $model = M();
        $res_user = $model->query($user_sql);
        $meal_1 = $meal_2 = $meal_3 = $meal_4 = $meal_egt5 = array();
        foreach ($res_user as $v){
            $openid = $v['openid'];
            $forscreen_sql = "SELECT DISTINCT DATE(create_time) as forscreen_date FROM savor_smallapp_forscreen_record 
            WHERE create_time >= '$start_time' AND create_time <= '$end_time' and openid='$openid'";
            $res_forscreen_date = $model->query($forscreen_sql);
            $meal_num = 0;
            foreach ($res_forscreen_date as $dv){
                $forscreen_date = $dv['forscreen_date'];
                $lunch_start_time = date("$forscreen_date 10:00:00");
                $lunch_end_time = date("$forscreen_date 14:59:59");
                $sql_lunch = "SELECT id,box_mac,hotel_id,hotel_name,create_time FROM savor_smallapp_forscreen_record 
                WHERE create_time >= '$lunch_start_time' AND create_time <= '$lunch_end_time' and openid='$openid'";
                $res_lunch = $model->query($sql_lunch);
                if(!empty($res_lunch)){
                    $meal_num++;
                }
                $dinner_start_time = date("$forscreen_date 17:00:00");
                $dinner_end_time = date("$forscreen_date 23:59:59");
                $sql_lunch = "SELECT id,box_mac,hotel_id,hotel_name,create_time FROM savor_smallapp_forscreen_record 
                WHERE create_time >= '$dinner_start_time' AND create_time <= '$dinner_end_time' and openid='$openid'";
                $res_lunch = $model->query($sql_lunch);
                if(!empty($res_lunch)){
                    $meal_num++;
                }
            }
            echo "openid: $openid  num: $meal_num \n";
            if($meal_num==1){
                $meal_1[]=$openid;
            }elseif($meal_num==2){
                $meal_2[]=$openid;
            }elseif($meal_num==3){
                $meal_3[]=$openid;
            }elseif($meal_num==4){
                $meal_4[]=$openid;
            }elseif($meal_num>=5){
                $meal_egt5[]=$openid;
            }
        }
        $res = array(
            'meal_1'=>array('num'=>count($meal_1),'openids'=>$meal_1),
            'meal_2'=>array('num'=>count($meal_2),'openids'=>$meal_2),
            'meal_3'=>array('num'=>count($meal_3),'openids'=>$meal_3),
            'meal_4'=>array('num'=>count($meal_3),'openids'=>$meal_4),
            'meal_egt5'=>array('num'=>count($meal_egt5),'openids'=>$meal_egt5),
        );
        print_r($res);
        $user = array('all'=>count($res_user),'meal_1'=>count($meal_1),'meal_2'=>count($meal_2),'meal_3'=>count($meal_3),
        'meal_4'=>count($meal_4),'meal_egt5'=>count($meal_egt5));
        print_r($user);
    }

    public function statichotelmealuser(){
        $start_time = '2019-10-01 00:00:00';
        $end_time = '2020-11-30 23:59:59';
        $user_sql = "SELECT openid FROM savor_smallapp_forscreen_record 
        WHERE create_time >= '$start_time' AND create_time <= '$end_time'
        and mobile_brand!='devtools' AND is_valid = 1 AND small_app_id in(1,2) AND openid not in (
        select u.openid from (
        (select openid from savor_smallapp_user where unionId in(
        select unionId from savor_smallapp_user where openid in(select openid from savor_smallapp_user_signin group by openid) 
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
            WHERE create_time >= '$start_time' AND create_time <= '$end_time' and openid='$openid'";
            $res_forscreen_hotel = $model->query($forscreen_sql);

            foreach ($res_forscreen_hotel as $hv){
                $meal_num = 0;
                $forscreen_hotel_id = $hv['hotel_id'];

                $forscreen_sql = "SELECT DISTINCT DATE(create_time) as forscreen_date FROM savor_smallapp_forscreen_record 
            WHERE create_time >= '$start_time' AND create_time <= '$end_time' and openid='$openid' and hotel_id={$forscreen_hotel_id}";
                $res_forscreen_date = $model->query($forscreen_sql);
                foreach ($res_forscreen_date as $dv){
                    $forscreen_date = $dv['forscreen_date'];

                    $lunch_start_time = date("$forscreen_date 10:00:00");
                    $lunch_end_time = date("$forscreen_date 14:59:59");
                    $sql_lunch = "SELECT id,box_mac,hotel_id,hotel_name,create_time FROM savor_smallapp_forscreen_record 
                WHERE create_time >= '$lunch_start_time' AND create_time <= '$lunch_end_time' and openid='$openid' and hotel_id={$forscreen_hotel_id}";
                    $res_lunch = $model->query($sql_lunch);
                    if(!empty($res_lunch)){
                        $meal_num++;
                    }
                    $dinner_start_time = date("$forscreen_date 17:00:00");
                    $dinner_end_time = date("$forscreen_date 23:59:59");
                    $sql_lunch = "SELECT id,box_mac,hotel_id,hotel_name,create_time FROM savor_smallapp_forscreen_record 
                WHERE create_time >= '$dinner_start_time' AND create_time <= '$dinner_end_time' and openid='$openid' and hotel_id={$forscreen_hotel_id}";
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

        print_r($meal_user);
        echo 'user: '.count($meal_user);
    }


    public function pushFileToBox(){
        $redis = \Common\Lib\SavorRedis::getInstance();
        $redis->select(5);
        $cache_key = 'smallapp:fileforscreen:845db8bb98b4b0e3d2bd9d7abbf96b47';
        $res_cache = $redis->get($cache_key);
        $resource_list = array();
        $imgs = json_decode($res_cache, true);
        if(!empty($imgs)){
            foreach ($imgs as $v){
                $filename = str_replace(array('forscreen/','/'),array('','_'),$v);
                $resource_list[]=array('url'=>$v,'filename'=>$filename);
            }
        }
        $resource_type  = 3;//1视频 2图片 3文件
        $box_mac = '00226D583F40';
        $message = array('action'=>171,'resource_type'=>$resource_type,'resource_list'=>$resource_list);
        echo json_encode($message);
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $res_netty = $m_netty->pushBox($box_mac,json_encode($message));
        print_r($res_netty);
    }

    public function track(){
        $time_str = "add_time>='2021-01-25 00:00:00' and add_time<='2021-01-26 23:59:59' and is_success=0";
        $sql = "SELECT * FROM `savor_smallapp_forscreen_track` where {$time_str}";
        $model = M();
        $res = $model->query($sql);
        foreach ($res as $v){
            $forscreen_record_id = $v['forscreen_record_id'];
            $sql_f = "select * from savor_smallapp_forscreen_record where id={$forscreen_record_id}";
            $res_f = $model->query($sql_f);
            if(!empty($res_f) && $res_f[0]['is_exist']==1){
                $v['box_downstime'] = 1;
                $v['box_downetime'] = 1;
            }

            if($v['position_nettystime']>0 && $v['position_nettystime']>0 && $v['request_nettytime']>0 && $v['netty_receive_time']>0
            && $v['netty_pushbox_time']>0 && $v['box_receivetime']>0 && $v['box_downstime']>0 && $v['box_downetime']>0){
                $netty_result = json_decode($v['netty_result'],true);
                if($netty_result['code']==10000){
                    $track_info = $v;
                    $oss_timeconsume = $track_info['oss_etime']-$track_info['oss_stime'];
                    $netty_position_timeconsume = 0;
                    if($track_info['request_nettytime']){
                        $netty_position_timeconsume = $track_info['request_nettytime']-$track_info['position_nettystime'];
                    }
                    $netty_timeconsume = 0;
                    if($track_info['netty_receive_time'] && $track_info['netty_pushbox_time']){
                        if($track_info['netty_callback_time']){
                            $netty_timeconsume = $track_info['netty_callback_time']-$track_info['netty_receive_time'];
                        }else{
                            $netty_timeconsume = $track_info['netty_pushbox_time']-$track_info['netty_receive_time'];
                        }
                    }
                    $box_down_timeconsume = 0;
                    if($track_info['box_receivetime'] && $track_info['box_downstime'] && $track_info['box_downetime']){
                        $box_down_timeconsume = $track_info['box_downetime']-$track_info['box_downstime'];
                    }
                    $total_time = ($oss_timeconsume+$netty_position_timeconsume+$netty_timeconsume+$box_down_timeconsume)/1000;

                    $sql_up = "update savor_smallapp_forscreen_track set is_success=1,total_time='{$total_time}' where id={$v['id']}";
                    $res_up = $model->execute($sql_up);
                    if($res_up){
                        echo "ID: {$v['id']} ok \r\n";
                    }
                }

            }
        }
    }

    public function hotelteam(){
        $file_path = SITE_TP_PATH.'/Public/content/酒楼维护人变更名单.xlsx';
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");

        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $team_ids = array("Oiyoboy"=>309,"超凡组"=>310,"勇者组"=>311);
        $data = array();
        $model = M();
        for ($row = 2; $row <= $highestRow; $row++){
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            if(!empty($rowData[0][0])){
                $row_info = $rowData[0];
                $hotel_id = $row_info[0];
                $team_name = $row_info[2];
                if(!empty($hotel_id) && !empty($team_name)){
                    $team_name = trim($team_name);
                    $maintainer_id = $team_ids["$team_name"];
                    $data[]=array('hotel_id'=>$hotel_id,'team_name'=>$team_name,'maintainer_id'=>$maintainer_id);
                    $sql = "UPDATE savor_hotel_ext SET maintainer_id=$maintainer_id WHERE hotel_id=$hotel_id";
                    $res = $model->execute($sql);
                    if($res){
                        echo 'hotel_id:'.$hotel_id." ok \r\n";
                    }
                }

            }
        }
    }

    public function rdtesthotel(){
        $file_path = SITE_TP_PATH.'/Public/content/20210511研发部实验酒楼.xlsx';
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");

        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $data = array();
        for ($row = 2; $row <= $highestRow; $row++){
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            if(!empty($rowData[0][0])){
                $row_info = $rowData[0];
                $hotel_id = $row_info[0];
                $hotel_name = $row_info[1];
                $short_name = $row_info[2];
                $data[$hotel_id]=array('hotel_id'=>$hotel_id,'hotel_name'=>$hotel_name,'short_name'=>$short_name);
            }
        }
        var_export($data);
    }

    public function hotplay(){
        $model = M();
        $sql = "select * from savor_smallapp_play_log where type=4 order by nums desc limit 0,8";
        $res = $model->query($sql);
        $m_hotplay = new \Admin\Model\Smallapp\HotplayModel();
        $sort = 100;
        foreach ($res as $k=>$v){
            $forscreen_record_id = $v['res_id'];
            $sql_forscreen = "select * from savor_smallapp_forscreen_record where id={$forscreen_record_id}";
            $res_forscreen = $model->query($sql_forscreen);
            $forscreen_id = $res_forscreen[0]['forscreen_id'];
            if(!empty($forscreen_id)){
                $sql_public = "select * from savor_smallapp_public where forscreen_id={$forscreen_id} order by id asc";
                $res_public = $model->query($sql_public);
                $public_id = $res_public[0]['id'];
                $sort--;
                $data = array('data_id'=>$public_id,'forscreen_record_id'=>$forscreen_record_id,'update_time'=>$v['update_time'],
                    'add_time'=>$v['create_time'],'sort'=>$sort,'type'=>1,'status'=>1);
                $m_hotplay->add($data);
                echo "res_id:$forscreen_record_id ok \r\n";
            }else{
                echo "res_id:$forscreen_record_id fail \r\n";
            }
        }

    }

    public function setsalewxtest(){
        //清除线上销售端微信测试人员测试信息
        $sql_staff = 'delete from savor_integral_merchant_staff where merchant_id=92';
        $model = M();
        $model->execute($sql_staff);

        $sql_user = 'delete from savor_smallapp_user where mobile=15810260493';
        $model->execute($sql_user);

        $redis = SavorRedis::getInstance();
        $redis->select(14);
        $key = 'smallappdinner_vcode_15810260493';
        $redis->set($key,1234);
        echo 'set wxtest ok';
    }

    public function testrdpush(){
        $box_mac = '00226D583ECD';
        $hotel_id = 7;
        $rd_hotel = C('RD_TEST_HOTEL');

        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $m_media = new \Admin\Model\MediaModel();
        $res_hotel_ext = $m_hotel_ext->getInfo(array('hotel_id'=>$hotel_id));
        $hotel_logo = '';
        if($res_hotel_ext['hotel_cover_media_id']>0){
            $res_media = $m_media->getMediaInfoById($res_hotel_ext['hotel_cover_media_id']);
            $hotel_logo = $res_media['oss_addr'];
        }
        $user_info = array('nickName'=>$rd_hotel[$hotel_id]['short_name'],'avatarUrl'=>$hotel_logo);
        $mpcode = 'http://dev-mobile.littlehotspot.com/Smallapp46/qrcode/getBoxQrcode?box_id=1101&box_mac=40E793253553&data_id=6&type=38';
        $message = array('action'=>138,'countdown'=>120,'nickName'=>$user_info['nickName'],
            'avatarUrl'=>$user_info['avatarUrl'], 'codeUrl'=>$mpcode);
        $message['headPic'] = base64_encode($user_info['avatarUrl']);
        $res_netty = $m_netty->pushBox($box_mac, json_encode($message));
        echo json_encode($res_netty);
    }

    public function startju(){
        $box_mac = '00226D583ECD';
        $m_hotel = new \Admin\Model\HotelModel();
        $field = 'a.name as hotel_name,ext.hotel_cover_media_id';
        $res_hotel_ext = $m_hotel->getHotelInfo($field,array('a.id'=>7));
        $m_media = new \Admin\Model\MediaModel();
        $res_media = $m_media->getMediaInfoById($res_hotel_ext['hotel_cover_media_id']);
        $headPic = base64_encode($res_media['oss_addr']);

        $m_box = new \Admin\Model\BoxModel();
        $res_box = $m_box->getHotelInfoByBoxMac($box_mac);
        $code_url = "http://mobile.littlehotspot.com//Smallapp46/qrcode/getBoxQrcode?box_id={$res_box['box_id']}&box_mac={$box_mac}&data_id=10557&type=39";
        $message = array('action'=>151,'countdown'=>120,'nickName'=>$res_hotel_ext['hotel_name'],'headPic'=>$headPic,'codeUrl'=>$code_url);
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $res_netty = $m_netty->pushBox($box_mac,json_encode($message));
        echo json_encode($message);
        print_r($res_netty);
        exit;
    }

    public function pushgoods(){
        $box_mac = '00226D583ECD';
        $code_url = 'http://mobile.littlehotspot.com/smallapp46/qrcode/getBoxQrcode?box_mac=00226D583D92&box_id=13384&data_id=622&type=24';
        $message = array('action'=>40,'goods_id'=>622,'qrcode_url'=>$code_url);
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $res_netty = $m_netty->pushBox($box_mac,json_encode($message));
        print_r($res_netty);
    }

    public function pushjuactivity(){
        $all_boxs = array('00226D584189','00226D583D92');
        $m_box = new \Admin\Model\BoxModel();
        $fields = 'box.id as box_id,box.mac as box_mac,hotel.id as hotel_id,hotel.name as hotel_name';
        $where = array('box.state'=>1,'box.flag'=>0,'hotel.state'=>1,'hotel.flag'=>0);
        $where['box.mac'] = array('in',$all_boxs);
        $res_boxs = $m_box->getBoxByCondition($fields,$where);
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $m_media = new \Admin\Model\MediaModel();
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $m_activity = new \Admin\Model\Smallapp\ActivityModel();
        foreach ($res_boxs as $v){
            $res_hotel_ext = $m_hotel_ext->getInfo(array('hotel_id'=>$v['hotel_id']));
            $res_media = $m_media->getMediaInfoById($res_hotel_ext['hotel_cover_media_id']);
            $headPic = base64_encode($res_media['oss_addr']);

            $awhere = array('hotel_id'=>$v['hotel_id'],'box_mac'=>$v['box_mac'],'status'=>0,'type'=>5);
            $res_activity = $m_activity->getAll('*',$awhere,0,1,'id desc');
            if(!empty($res_activity)){
                $activity_id = $res_activity[0]['id'];
            }else{
                $add_data = array('hotel_id'=>$v['hotel_id'],'box_mac'=>$v['box_mac'],'name'=>'幸运大奖',
                    'prize'=>'免费品鉴赖茅生肖酒一次','attach_prize'=>'厂家直销价9.5折优惠并赠送100元菜品',
                    'type'=>5);
                $activity_id = $m_activity->add($add_data);
            }
            $code_url = "http://mobile.littlehotspot.com//Smallapp46/qrcode/getBoxQrcode?box_id={$v['box_id']}&box_mac={$v['box_mac']}&data_id={$activity_id}&type=39";
            $message = array('action'=>151,'countdown'=>120,'nickName'=>$v['hotel_name'],'headPic'=>$headPic,'codeUrl'=>$code_url);
            $res_netty = $m_netty->pushBox($v['box_mac'],json_encode($message));
            $now_time = date('Y-m-d H:i:s');
            echo "$now_time box_mac:{$v['box_mac']} activity_id:$activity_id netty_result:".json_encode($res_netty)." \r\n";
        }

    }

    public function endju(){
        $box_mac = '00226D583ECD';
        $prize_list = array('1.赖茅生肖酒试喝120ml','2.赖茅生肖酒购买9.5折');
        $price = '854';
        $jd_price = '898';
        $message = array('action'=>152,'countdown'=>60,'prize_list'=>$prize_list,'price'=>$price,'jd_price'=>$jd_price);
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $res_netty = $m_netty->pushBox($box_mac,json_encode($message));
        echo json_encode($message);
        print_r($res_netty);
        exit;
    }

    public function sendsms(){
        $sms_config = C('ALIYUN_SMS_CONFIG');
        $alisms = new \Common\Lib\AliyunSms();
        $template_code = $sms_config['public_audit_templateid'];
        $send_mobiles = C('PUBLIC_AUDIT_MOBILE');
        foreach ($send_mobiles as $v){
            $res = $alisms::sendSms($v,'',$template_code);
            print_r($res);
        }
        echo "ok";
    }

    public function resetlaimaogoods(){
        $goods_id = 622;
        $m_goods = new \Admin\Model\Smallapp\GoodsModel();
        $d_data = array('start_time'=>date('Y-m-d 00:00:00'),'end_time'=>date('Y-m-d 23:59:59'));

        $m_goods->updateData(array('id'=>$goods_id),$d_data);
        $m_hotelgoods = new \Admin\Model\Smallapp\HotelGoodsModel();
        $m_hotelgoods->HandleGoodsperiod($goods_id);
        echo 'now_time:'.date('Y-m-d H:i:s')."\r\n";
    }

    public function syshotplay(){
        $model = M();
        $sql = "select * from savor_smallapp_play_log where type=4 order by nums desc limit 0,8";
        $res_hotplay = $model->query($sql);
        $sort_num = 1000;
        $m_hoteplay = new \Admin\Model\Smallapp\HotplayModel();
        $all_data = array();
        foreach ($res_hotplay as $v){
            $forscreen_record_id = $v['res_id'];
            $sql_forscreen = "select * from savor_smallapp_forscreen_record where id={$forscreen_record_id}";
            $res_forscreen = $model->query($sql_forscreen);
            $forscreen_id = $res_forscreen[0]['forscreen_id'];

            if(!empty($forscreen_id)){
                $sql_public = "select * from savor_smallapp_public where forscreen_id={$forscreen_id} order by id asc limit 0,1";
                $res_public = $model->query($sql_public);
                $data_id = $res_public[0]['id'];
                $sort = $sort_num --;
                $add_data = array('data_id'=>$data_id,'forscreen_record_id'=>$forscreen_record_id,'sort'=>$sort,'type'=>1,'status'=>1);
                $all_data[]=$add_data;
            }
        }
        $m_hoteplay->addAll($all_data);
    }

    public function hoteldrinksimg() {
        $accessKeyId = C('OSS_ACCESS_ID');
        $accessKeySecret = C('OSS_ACCESS_KEY');
        $endpoint = 'oss-cn-beijing.aliyuncs.com';
        $bucket = C('OSS_BUCKET');
        $aliyunoss = new Aliyun($accessKeyId, $accessKeySecret, $endpoint);
        $aliyunoss->setBucket($bucket);

        $dir = '/application_data/web/php/savor_admin/Public/content/hotel_drinks_img';
        $all_hotel_img = array();
        $objects = scandir($dir);
        $m_hotel_drinks = new \Admin\Model\HoteldrinksModel();
        $m_media = new \Admin\Model\MediaModel();
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                $hotel_id = $object;
                $res_hotelimgs = scandir($dir."/".$object);
                $hotel_img = array();
                foreach ($res_hotelimgs as $img){
                    if ($img != "." && $img != "..") {
                        $file_path = $dir.'/'.$object.'/'.$img;
                        $hotel_img[] = $file_path;

                        $tempInfo = pathinfo($file_path);
                        $surfix = $tempInfo['extension'];
                        if($surfix){
                            $surfix = strtolower($surfix);
                        }
                        $typeinfo = C('RESOURCE_TYPEINFO');
                        if(isset($typeinfo[$surfix])){
                            $type = $typeinfo[$surfix];
                        }else{
                            $type = 3;
                        }
                        $file_size = 0;
                        $oss_addr = 'media/resource/'.getMillisecond().".$surfix";
                        $res_upload = $aliyunoss->uploadFile($oss_addr,$file_path);
                        if(!empty($res_upload['info']['url'])){
                            $file_info = $aliyunoss->getObject($oss_addr,'');
                            $md5_str = md5($file_info);
                            $res_object = $aliyunoss->getObjectMeta($oss_addr);
                            if(isset($res_object['content-length']) && $res_object['content-length']>0 && isset($res_object['oss-request-url'])){
                                $tmp_file = explode("$endpoint/",$res_object['oss-request-url']);
                                if($tmp_file[1]==$oss_addr){
                                    $file_size = $res_object['content-length'];
                                }
                            }
                            $add_mediadata = array('name'=>$tempInfo['filename'],'oss_addr'=>$oss_addr,'oss_filesize'=>$file_size,
                                'md5'=>$md5_str,'surfix'=>$surfix,'create_time'=>date('Y-m-d H:i:s'),'type'=>$type);
                            $media_id = $m_media->add($add_mediadata);
                            $add_drinks_data = array('hotel_id'=>$hotel_id,'media_id'=>$media_id,'type'=>2);
                            $res_drinks = $m_hotel_drinks->add($add_drinks_data);
                            if($res_drinks){
                                echo "hotel_id:$hotel_id  $oss_addr ok \r\n";
                            }
                        }
                    }
                }
                $all_hotel_img[] = array('hotel_id'=>$hotel_id,'imgs'=>$hotel_img);
            }
        }
        print_r($all_hotel_img);
    }

    public function hoteldrinksprice(){
        $file_path = SITE_TP_PATH.'/Public/content/副本酒楼白酒种类清单-0817.xlsx';
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");

        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $m_hotel_drinks = new \Admin\Model\HoteldrinksModel();
        $m_room = new \Admin\Model\RoomModel();
        $m_box = new \Admin\Model\BoxModel();
        $data = array();
        for ($row = 3; $row <= $highestRow; $row++){
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            if(!empty($rowData[0][0])){
                $city = $rowData[0][0];
                $hotel_name = $rowData[0][1];
                $hotel_id = $rowData[0][2];

                $rfields = 'count(*) as num';
                $rwhere = array('hotel_id'=>$hotel_id,'state'=>array('in',array(1,2)),'flag'=>0);
                $res_room = $m_room->getInfo($rfields,$rwhere,'id desc','');
                $room_num = intval($res_room[0]['num']);

                $bfields = 'count(box.id) as num';
                $bwhere = array('hotel.id'=>$hotel_id,'box.state'=>array('in',array(1,2)),'box.flag'=>0);
                $res_box = $m_box->getBoxByCondition($bfields,$bwhere,'');
                $box_num = intval($res_box[0]['num']);

                $tmp_drinks = array_slice($rowData[0],5);
                if(!empty($tmp_drinks)){
                    $d_num = ceil(count($tmp_drinks) / 2);
                    for ($i=0;$i<$d_num;$i++){
                        $offset = $i*2;
                        $now_drinks = array_slice($tmp_drinks,$offset,2);
                        if(empty($now_drinks[0]) && empty($now_drinks[1])){
                            break;
                        }
                        $name_info = explode('、',$now_drinks[0]);

                        $name = join('',$name_info);
                        $price = $now_drinks[1];
                        $brand = $name_info[0];
                        $series = $name_info[1];
                        $degree = $name_info[2];
                        $capacity = $name_info[3];
                        $dinfo = array('city'=>$city,'hotel_name'=>$hotel_name,'hotel_id'=>$hotel_id,'room_num'=>$room_num,
                            'box_num'=>$box_num,'brand'=>$brand,'series'=>$series,'degree'=>$degree,'capacity'=>$capacity,
                            'price'=>$price,'name'=>$name);
                        $data[]=$dinfo;


//                        $res_drinks = $m_hotel_drinks->getInfo(array('hotel_id'=>$hotel_id,'name'=>$name,'type'=>1));
//                        if(empty($res_drinks)){
//                            $add_drinks = array('hotel_id'=>$hotel_id,'name'=>$name,'price'=>$price,'type'=>1);
//                            $row_id = $m_hotel_drinks->add($add_drinks);
//                        }else{
//                            if($res_drinks['price']==$price){
//                                $row_id = true;
//                            }else{
//                                $up_drinks = array('price'=>$price,'update_time'=>date('Y-m-d H:i:s'));
//                                $row_id = $m_hotel_drinks->updateData(array('id'=>$res_drinks['id']),$up_drinks);
//                            }
//                        }
//                        echo "hotel_id:$hotel_id name:$name price:$price ok \r\n";
                    }
                }
            }
        }
        print_r($data);

    }

    public function getheart(){
        $mac = I('get.mac','');
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(20);
        $params_cache_key = $mac.':'.date('Ymd');
        $res = $redis->get($params_cache_key);
        if(!empty($res)){
            $result = json_decode($res,true);
            $str = "机顶盒：$mac 心跳上报如下：<br>";
            foreach ($result as $v){
                $str.="序号：{$v['serial_no']}，时间：{$v['time']}<br>";
            }
            echo $str;
        }
    }

    public function publicwh(){
        $m_public = new \Admin\Model\Smallapp\PublicModel();
        $m_public->handle_widthheight();
    }

    public function cleandownload(){
        $m_hotel = new \Admin\Model\HotelModel();
//        $m_hotel->cleanWanHotelCache(array(7));
        $m_hotel->handle_timeout_download();
    }

    public function cleanbox(){
        $mac = I('mac','','trim');
        $type = I('type',0,'intval');
        $message = array('action'=>998,'type'=>$type);
        //1.删除当前正在下载的一期视频内容，腾出空间
        //2.删除正在播放的广告数据
        //3.删除生日歌
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $res_netty = $m_netty->pushBox($mac,json_encode($message));
        print_r($res_netty);
    }

}