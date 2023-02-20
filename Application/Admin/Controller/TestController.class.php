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

    public function updatemaintainer(){
        //$path = "D:\\upmant.xlsx";
        exit;
        $path = '/application_data/web/php/savor_admin/Public/zyt.xlsx';
        if  ($path == '') {
            $res = array('error'=>0,'message'=>array());
            echo json_encode($res);
        }
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        vendor("PHPExcel.PHPExcel.IOFactory");
        if ($type == 'xlsx' || $type == 'xls') {
            $objPHPExcel = \PHPExcel_IOFactory::load($path);
        } elseif ($type == 'csv') {
            $objReader = \PHPExcel_IOFactory::createReader('CSV')
            ->setDelimiter(',')
            ->setInputEncoding('GBK')//不设置将导致中文列内容返回boolean(false)或乱码
            ->setEnclosure('"')
            ->setLineEnding("\r\n")
            ->setSheetIndex(0);
            $objPHPExcel = $objReader->load($path);
        } else {
            //$this->output('文件格式不正确', 'importdata', 0, 0);
            $res = array('error'=>1,'message'=>'文件格式不正确');
            echo json_encode($res);
            die;
        }
        
        $sheet = $objPHPExcel->getSheet(0);
        //获取行数与列数,注意列数需要转换
        $highestRowNum = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);
       
        
        //取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名
        $filed = array();
        for ($i = 0; $i < $highestColumnNum; $i++) {
            $cellName = \PHPExcel_Cell::stringFromColumnIndex($i) . '1';
            $cellVal = $sheet->getCell($cellName)->getValue();//取得列内容
            $filed[] = $cellVal;
        }
        
        //开始取出数据并存入数组
        $data = array();
        $hotel_str = '';
        $spx = '';
        for ($i = 2; $i <= $highestRowNum; $i++) {//ignore row 1
            $row = array();
            for ($j = 0; $j < $highestColumnNum; $j++) {
                $cellName = \PHPExcel_Cell::stringFromColumnIndex($j) . $i;
                $cellVal = (string)$sheet->getCell($cellName)->getValue();
                if($cellVal === 'null'){
                    $cellVal = '';
                }
                if($cellVal === '"' ||  $cellVal === "'"){
                    $cellVal = '#';
                }
                if($cellVal === 'null'){
                    $cellVal = '';
                }
                $row[$filed[$j]] = $cellVal;
            }
            $hotel_str .= $spx. $row['id'];
            $spx = ',';
            if(!empty($row['id'])){
                $data [] = $row;
            }
        }
        $redis = SavorRedis::getInstance();
        $redis->select(15);
        $m_sysuser = new \Admin\Model\UserModel();
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $flag = 0 ;
        //$data = array_slice($data,0,1);
        foreach($data as $key=>$v){
            
            if($v['name']=='辛立娟'){
                $v['name'] = '辛丽娟';
            }
            $where           = [];
            $where['remark'] = $v['name'];
            $where['status'] = 1;
            $ret = $m_sysuser->field('id maintainer_id')->where($where)->find();
            /*if(empty($ret)){
                echo $v['id'].'-'.$v['name']."<br>";
            }*/
            
            $ext_info = $m_hotel_ext->field('*')->where(array('hotel_id'=>$v['id']))->find();
            //echo $m_hotel_ext->getLastSql();exit;
            
            if($ret['maintainer_id'] != $ext_info['maintainer_id']){
                $sql ="update savor_hotel_ext set maintainer_id=".$ret['maintainer_id']." where hotel_id=".$v['id'].' limit 1';
                //echo $sql."<br>";
                
                $rts = M()->execute($sql);
                if($rts){
                    $ext_info['maintainer_id'] = $ret['maintainer_id'];
                    $hotel_ext_cache_key = C('DB_PREFIX').'hotel_ext_'.$v['id'];
                    $hotel_ext_info = $ext_info;
                    $redis->set($hotel_ext_cache_key, json_encode($hotel_ext_info));
                    $flag ++;
                }
                
            }
        }
        echo $flag;
        
    }
    public function deleteSmallappUser(){
        $openid  = I('openid');
        $m_user = new \Admin\Model\Smallapp\UserModel();
        if(!empty($openid)){
            $user_info = $m_user->getOne('is_interact', array('openid'=>$openid));
            if(!empty($user_info)){
                $redis = SavorRedis::getInstance();
                $redis->select(5);
                $cache_key = C('SAPP_FORSCREEN_NUMS').$openid;
                $redis->remove($cache_key);
                $where = [];
                $where['openid'] = $openid;
                $m_user->where($where)->limit(1)->delete();
                echo "OK";
            }else {
                echo 'user empty';
            }
        }else {
            echo "openid can not empty";
        }
        
    }
    public function resetNewSmallappUser(){
        $openid = I('openid','');
        $type = I('type',1,'intval');
        if(!empty($openid)){
            $m_user = new \Admin\Model\Smallapp\UserModel();
            $user_info = $m_user->getOne('is_interact', array('openid'=>$openid));
            if(!empty($user_info)){
                switch ($type){
                    case 1:
                        $redis = SavorRedis::getInstance();
                        $redis->select(5);
                        $cache_key = C('SAPP_FORSCREEN_NUMS').$openid;

                        $up_data = array('is_interact'=>0,'mobile'=>'','is_wx_auth'=>0);
                        $m_user->updateInfo(array('openid'=>$openid),$up_data);
                        $redis->remove($cache_key);
                        break;
                    case 2:
                        $m_staff = new \Admin\Model\Integral\StaffModel();
                        $m_staff->delData(array('openid'=>$openid));
                        break;
                }

                echo "OK";
            }else {
                echo 'user empty';
            }
            
            
        }else {
            echo "openid empty";
        }
    }
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
//            $sql ="update savor_box set is_4g={$is_4g} where id=".$v['id'].' limit 1';
//            M()->execute($sql);
//            echo $v['mac']." is_4g:$is_4g ok \n";

            $sql ="update savor_box set switch_time=999 where id=".$v['id'].' limit 1';
            M()->execute($sql);
            echo $v['mac']." switch_time:999 ok \n";


            $box_info = array();
            $box_id = $v['id'];
            $box_info = $v;
            $box_cache_key = C('DB_PREFIX').'box_'.$box_id;
            $redis->set($box_cache_key, json_encode($box_info));

            /*
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
//                        if(in_array($res_box['box_type'],array(3,6,7))){
//                            $forscreen_type = 2;
//                        }elseif($res_box['box_type']==2){
//                            $forscreen_type = 1;
//                        }
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
            */

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

    public function gddata(){
        $file_path = SITE_TP_PATH.'/Public/content/gdbank_0513.xlsx';
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");

        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $all_data = array();
        for ($row = 2; $row <= $highestRow; $row++){
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            $jyrq = $rowData[0][0];
            $jysj = $rowData[0][1];
            $jffse = $rowData[0][2];
            $dffse = $rowData[0][3];
            $zhye = $rowData[0][4];
            if(empty($jffse)){
                $jffse='';
            }else{
//                $jffse = number_format($jffse,2);
            }
            if(empty($dffse)){
                $dffse='';
            }else{
//                $dffse = number_format($dffse,2);
            }
            if(empty($zhye)){
                $zhye = '';
            }else{
//                $zhye = number_format($zhye,2);
            }

            $all_data[]=array('jyrq'=>$jyrq,'jysj'=>$jysj,'jffse'=>$jffse,'dffse'=>$dffse,
                'zhye'=>$zhye,'dfzh'=>$rowData[0][5],'dfmc'=>$rowData[0][6],'pzh'=>$rowData[0][7],
                'lsh'=>$rowData[0][8],'zy'=>$rowData[0][9],'sort_num'=>$row
            );
        }
        var_export($all_data);
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
        $scan_qrcode_types = C('SCAN_QRCODE_TYPES');
        $all_hotel_types = C('heart_hotel_box_type');
        $where = array();
        $where['static_date'] = array(array('EGT','2022-01-01'),array('ELT','2022-01-12'));
        $m_statichotelbasicdata = new \Admin\Model\Smallapp\StaticHotelbasicdataModel();
        $res_data = $m_statichotelbasicdata->getDataList('id,hotel_id,static_date',$where,'id asc');
        $m_smallapp_forscreen_record = new \Admin\Model\SmallappForscreenRecordModel();
        $m_qrcodelog = new \Admin\Model\Smallapp\QrcodeLogModel();
        $m_smallapp_iforscreen_record = new \Admin\Model\Smallapp\ForscreeninvalidrecordModel();

        $m_heartlog = new \Admin\Model\HeartAllLogModel();
        foreach ($res_data as $v){
            $hotel_id = $v['hotel_id'];
            $time_date = strtotime($v['static_date']);
            $date = date('Ymd',$time_date);
            $start_time = date('Y-m-d 00:00:00',$time_date);
            $end_time = date('Y-m-d 23:59:59',$time_date);

            /*
            $room_heart_num = $m_heartlog->getHotelAllHeart($date,$hotel_id,1);
            $room_meal_heart_num = $m_heartlog->getHotelMealHeart($date,$hotel_id,1);

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
            $data = array('room_heart_num'=>$room_heart_num,'room_meal_heart_num'=>$room_meal_heart_num);
            */
            //餐厅扫码数
//            $fields = "count(a.id) as num";
//            $restaurantqrcode_where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0);
//            $restaurantqrcode_where['a.type'] = array('in',$scan_qrcode_types);
//            $restaurantqrcode_where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
//            $restaurantqrcode_where['_string'] = 'a.openid in(select invalidid from savor_smallapp_forscreen_invalidlist where type=2)';
//            $res_qrcode = $m_qrcodelog->getScanqrcodeNum($fields,$restaurantqrcode_where);
//            $restaurant_scancode_num = intval($res_qrcode[0]['num']);
//
//            $fields = "count(DISTINCT(a.openid)) as num";
//            $res_userqrcode = $m_qrcodelog->getScanqrcodeNum($fields,$restaurantqrcode_where);
//            $restaurant_user_num = intval($res_userqrcode[0]['num']);
//
//            $restaurant_interact_standard_num = 0;
//            $iforscreen_where = array('hotel.id'=>$hotel_id,'box.state'=>1,'box.flag'=>0,'a.is_valid'=>1);
//            $iforscreen_where['a.mobile_brand'] = array('neq','devtools');
//            $iforscreen_where['a.create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
//            $iforscreen_where['a.small_app_id'] = array('in',array(1,2,11));//小程序ID 1普通版,2极简版,5销售端,11 h5互动游戏
//            $fields = 'count(a.id) as fnum';
//            $res_iforscreen = $m_smallapp_iforscreen_record->getWhere($fields,$iforscreen_where,'','');
//            if(!empty($res_iforscreen)){
//                $restaurant_interact_standard_num = $res_iforscreen[0]['fnum'];
//            }
            $restaurant_user_lunch_zxhdnum = $restaurant_user_dinner_zxhdnum = 0;
            $res_iforscreen_box = $m_smallapp_iforscreen_record->getFeastInteractBoxByHotelId($hotel_id,$time_date,1,1);
            if(!empty($res_iforscreen_box)){
                $restaurant_user_lunch_zxhdnum = count($res_iforscreen_box);
            }
            $res_iforscreen_box = $m_smallapp_iforscreen_record->getFeastInteractBoxByHotelId($hotel_id,$time_date,2,1);
            if(!empty($res_iforscreen_box)){
                $restaurant_user_dinner_zxhdnum = count($res_iforscreen_box);
            }
            $data = array(
//                'restaurant_user_num'=>$restaurant_user_num,'restaurant_scancode_num'=>$restaurant_scancode_num,'restaurant_interact_standard_num'=>$restaurant_interact_standard_num,
                'restaurant_user_lunch_zxhdnum'=>$restaurant_user_lunch_zxhdnum,'restaurant_user_dinner_zxhdnum'=>$restaurant_user_dinner_zxhdnum,
            );
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

    public function setopswxtest(){
        $model = M();
        $sql_staff = "UPDATE savor_ops_staff SET openid='' WHERE id=7";
        $model->execute($sql_staff);
        $sql_user = 'delete from savor_smallapp_user where mobile=15810260493';
        $model->execute($sql_user);

        $redis = SavorRedis::getInstance();
        $redis->select(14);
        $cache_key = C('SAPP_OPS').'register:15810260493';
        $redis->set($cache_key,1234);
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

    public function pushsellwine(){
        $box_mac = I('mac','','trim');
        $countdown = I('countdown',3600,'intval');
        $activity_id = 1;
        $m_sellwine_activity = new \Admin\Model\Smallapp\SellwineActivityModel();
        $res_activity = $m_sellwine_activity->getInfo(array('id'=>$activity_id));

        $m_media = new \Admin\Model\MediaModel();
        $res_media = $m_media->getMediaInfoById($res_activity['tvleftmedia_id']);
        $img_path = $res_media['oss_path'];

        $name = '每瓶酒可奖励';
        $content = '20-100元红包';
        $filename = 'ZntWp83Ghb.mp4';
        $video_path = 'media/resource/ZntWp83Ghb.mp4';
        $netty_data = array('action'=>163,'img_path'=>$img_path,'video_path'=>$video_path,'filename'=>$filename,'name'=>$name,'content'=>$content,'countdown'=>$countdown);
        $message = json_encode($netty_data);
        echo $message;
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $ret = $m_netty->pushBox($box_mac,$message);
        print_r($ret);
    }

    public function pushbonus(){
        $box_mac = I('mac','');

        $http_host = 'https://mobile.littlehotspot.com';
        $trade_no = 10188;
        $qrinfo =  $trade_no.'_'.$box_mac;
        $mpcode = $http_host.'/h5/qrcode/mpQrcode?qrinfo='.$qrinfo;
        $op_info = C('BONUS_OPERATION_INFO');
        $message = array('action'=>121,'nickName'=>$op_info['nickName'],'rtype'=>1,
            'avatarUrl'=>$op_info['avatarUrl'],'codeUrl'=>$mpcode,'img_path'=>$op_info['popout_img'],'content'=>'和朋友们分享，一起抢红包');

        $message['headPic'] = base64_encode($message['avatarUrl']);
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $res_netty = $m_netty->pushBox($box_mac,json_encode($message));
        print_r($res_netty);
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

    public function resetseckilltimegoods(){
        echo 'now_time:'.date('Y-m-d H:i:s')."\r\n";
        $m_goods = new \Admin\Model\Smallapp\DishgoodsModel();
        $res_goods = $m_goods->getDataList('id',array('type'=>43,'is_seckill'=>1),'id desc');
        $d_data = array('start_time'=>date('Y-m-d 00:00:00'),'end_time'=>date('Y-m-d 23:59:59'));
        foreach ($res_goods as $v){
            if(!empty($v['id'])){
                $m_goods->updateData(array('id'=>$v['id']),$d_data);
                echo "goods_id: {$v['id']} reset ok \r\n";
            }
        }
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

    public function sceneadvpush(){
        $day = date('w');
        if($day==0 || $day==6){//周六周日
            $url = 'media/resource/kzZsH5pPHT.jpg';
            $filename = 'kzZsH5pPHT.jpg';
            $resource_size = 239687;
        }else{
            $url = 'media/resource/5bZDJpFsJJ.jpg';
            $filename = '5bZDJpFsJJ.jpg';
            $resource_size = 179965;
        }
        $all_hotel = array(7);

        $m_box = new \Admin\Model\BoxModel();
        $where = array('box.state'=>1,'box.flag'=>0,'hotel.id'=>array('in',$all_hotel));
        $res_box = $m_box->getBoxByCondition('hotel.id as hotel_id,box.mac',$where);
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        foreach ($res_box as $v){
            $forscreen_id = getMillisecond();
            $message = array('forscreen_id'=>$forscreen_id,'action'=>4,'resource_type'=>2,
                'openid'=>'','avatarUrl'=>'','nickName'=>'',
                'img_list'=>array(array('url'=>$url,'filename'=>$filename,'img_id'=>$forscreen_id,'resource_size'=>$resource_size))
            );
            $box_mac = $v['mac'];
            $ret = $m_netty->pushBox($box_mac,json_encode($message));
            echo "hotel_id:{$v['hotel_id']} mac:$box_mac result:".json_encode($ret)."\r\n";
        }
    }

    public function testopenlottery(){
        $box_mac = I('mac','','trim');
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $users = $m_user->getWhere('openid,avatarUrl,nickName',array('small_app_id'=>1,'nickName'=>array('neq','')),'id desc','0,20','');
        $lottery_nums = array(1,5);
        $partake_user = array();
        $lottery_users = array();
        foreach ($users as $k=>$uv){
            $is_lottery = 0;
            if(in_array($k,$lottery_nums)){
                $is_lottery = 1;
//                if($k==1){
//                    $level=1;
//                    $dish_name = '古奢清香酒';
//                    $dish_image = 'media/resource/nDPfaQ8Bh2.jpg';
//                }else{
//                    $level = 2;
//                    $dish_name = '冰清酒';
//                    $dish_image = 'media/resource/nDZKJKRbRx.jpg';
//                }
                $dish_name = '古奢清香酒';
                $dish_image = 'media/resource/nDPfaQ8Bh2.jpg';
                $lottery_users[] = array('openid'=>$uv['openid'],'dish_name'=>$dish_name,
                    'dish_image'=>$dish_image,'level'=>0,'room_name'=>'');
            }
            $partake_user[] = array('openid'=>$uv['openid'],'avatarUrl'=>base64_encode($uv['avatarurl']),'nickName'=>$uv['nickname'],'is_lottery'=>$is_lottery);
        }
        $netty_data = array('action'=>156,'partake_user'=>$partake_user,'lottery'=>$lottery_users);
        $message = json_encode($netty_data);
        echo $message;

        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $ret = $m_netty->pushBox($box_mac,$message);
    }

    public function testlottery(){
        $box_mac = I('mac','','trim');
        $bwhere = array('box.mac'=>$box_mac,'box.state'=>1,'box.flag'=>0);
        $m_box = new \Admin\Model\BoxModel();
        $res_box = $m_box->getBoxByCondition('box.id as box_id,box.mac as box_mac,hotel.name,ext.hotel_cover_media_id',$bwhere);
        $res_box = $res_box[0];

        $host_name = 'https://mobile.littlehotspot.com';
        $activity_id = 999;
        $headPic = '';
        if($res_box['hotel_cover_media_id']>0){
            $m_media = new \Admin\Model\MediaModel();
            $res_media = $m_media->getMediaInfoById($res_box['hotel_cover_media_id']);
            $headPic = base64_encode($res_media['oss_addr']);
        }
        /*
        $m_activity = new \Admin\Model\Smallapp\ActivityModel();
        $res_activity = $m_activity->getInfo(array('id'=>$activity_id));
        $dish_name_info = pathinfo($res_activity['image_url']);
        $lottery_countdown = 60;
        $lottery_countdown = $lottery_countdown>0?$lottery_countdown:0;
        $message = array('action'=>158,
            'lottery_countdown'=>$lottery_countdown,'partake_img'=>$res_activity['image_url'],'partake_filename'=>$dish_name_info['basename'],
            'partake_name'=>$res_activity['prize'],'activity_name'=>'售酒抽奖',
        );
        $code_url = $host_name."/Smallapp46/qrcode/getBoxQrcode?box_id={$res_box['box_id']}&box_mac={$res_box['mac']}&data_id={$activity_id}&type=45";
        $message['codeUrl']=$code_url;
        */
        $code_url = $host_name."/Smallapp46/qrcode/getBoxQrcode?box_id={$res_box['box_id']}&box_mac={$res_box['box_mac']}&data_id={$activity_id}&type=49";
        $message = array('action'=>138,'countdown'=>120,'nickName'=>$res_box['name'],'headPic'=>$headPic,'codeUrl'=>$code_url);

        $now_message = json_encode($message);
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $ret = $m_netty->pushBox($box_mac,$now_message);
        echo $now_message;
    }

    public function testok(){
        $m_prize = new \Admin\Model\Smallapp\SyslotteryPrizeModel();
        $activity_id = 40;
        $res_prize = $m_prize->getDataList('*',array('syslottery_id'=>$activity_id),'probability asc');
        $success_rate=$fail_rate=$success_num=$fail_num = 0;
        $all_probability = array();
        foreach ($res_prize as $v){
            if($v['type']==3){
                $fail_rate+=$v['probability'];
                $fail_num++;
            }else{
                $success_rate+=$v['probability'];
                $success_num++;
            }
            $all_probability[$v['id']]=array('probability'=>$v['probability'],'type'=>$v['type']);
        }
        $is_lottery = 0;
        if($is_lottery){
            $amount = $success_num;
            $rate = $fail_rate;
        }else{
            $amount = $fail_num;
            $rate = $success_rate;
        }
        $avg_num = intval($rate/$amount);
        $last_num = fmod($rate,$amount);
        $all_nums = array();
        for($i=1;$i<=$amount;$i++){
            $all_nums[]=$avg_num;
        }
        if($last_num){
            $all_nums[$amount-1] = $all_nums[$amount-1]+$last_num;
        }
        foreach ($all_probability as $k=>$v){
            if($is_lottery){
                if($v['type']==3){
                    $all_probability[$k]['probability']=0;
                }else{
                    $now_avg_num = array_shift($all_nums);
                    $all_probability[$k]['probability'] = $v['probability'] + intval($now_avg_num);
                }
            }else{
                if($v['type']==3){
                    $now_avg_num = array_shift($all_nums);
                    $all_probability[$k]['probability'] = $v['probability'] + intval($now_avg_num);
                }else{
                    $all_probability[$k]['probability']=0;
                }
            }
        }
        print_r($all_probability);
    }

    public function senduserbonus(){
        $orderid= I('oid',0,'intval');
        $box_mac = I('mac','','trim');
        $op_info = C('BONUS_OPERATION_INFO');

        $http_host = 'https://mobile.littlehotspot.com';
        $trade_no = $orderid;
        $qrinfo =  $trade_no.'_'.$box_mac;
        $mpcode = $http_host.'/h5/qrcode/mpQrcode?qrinfo='.$qrinfo;
        $netty_data = array('action'=>121,'nickName'=>$op_info['nickName'],
            'avatarUrl'=>$op_info['avatarUrl'],'codeUrl'=>$mpcode,'img_path'=>$op_info['popout_img']);
        $netty_data['headPic'] = base64_encode($netty_data['avatarUrl']);
        $message = json_encode($netty_data);
        echo $message;
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $ret = $m_netty->pushBox($box_mac,$message);
        print_r($ret);
    }

    public function sendbonus(){
        $box_mac = I('mac','','trim');
        $op_info = C('BONUS_OPERATION_INFO');

        $http_host = 'https://mobile.littlehotspot.com';
        $trade_no = 16455;
        $qrinfo =  $trade_no.'_'.$box_mac;
        $mpcode = $http_host.'/h5/qrcode/mpQrcode?qrinfo='.$qrinfo;
        $netty_data = array('action'=>121,'nickName'=>$op_info['nickName'],
            'avatarUrl'=>$op_info['avatarUrl'],'codeUrl'=>$mpcode,'img_path'=>$op_info['popout_img']);
        $netty_data['headPic'] = base64_encode($netty_data['avatarUrl']);
        $message = json_encode($netty_data);
        echo $message;
        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $ret = $m_netty->pushBox($box_mac,$message);
        print_r($ret);
    }

    public function stockprice(){
        $model = M();
        $sql = "SELECT * from savor_finance_stock_record where type=1 and id in(5,6,7,8,18,19) order by id asc";
        $res_record = $model->query($sql);
        $m_record = new \Admin\Model\FinanceStockRecordModel();
        $error_ids = array();
        $error_goods_ids = array();
        $error_unit_ids = array();
        foreach ($res_record as $v){
            $id = $v['id'];
            $stock_id = $v['stock_id'];
            $stock_detail_id = $v['stock_detail_id'];
            $sql_detail = "SELECT * from savor_finance_stock_detail where id={$stock_detail_id}";
            $res_detail = $model->query($sql_detail);
            $res_detail = $res_detail[0];

            $goods_id = $res_detail['goods_id'];
            $unit_id = $res_detail['unit_id'];
            $purchase_detail_id = intval($res_detail['purchase_detail_id']);
            $sql_purchase_detail = "select * from savor_finance_purchase_detail where id={$purchase_detail_id} and status=1";
            $res_purchase = $model->query($sql_purchase_detail);
            if(empty($res_purchase)){
                $error_ids[]=$id;
            }
            elseif($goods_id!=$v['goods_id']){
                $error_goods_ids[]=$id;
            }
            elseif($unit_id!=$v['unit_id']){
                $error_unit_ids[]=$id;
            }
            else{
                $amount = 1;
                $sql_unit = "select * from savor_finance_unit where id={$unit_id}";
                $res_unit = $model->query($sql_unit);
                $res_unit = $res_unit[0];
                $total_amount = $res_unit['convert_type']*$amount;

                $total_fee = $res_purchase[0]['price'];
                $price = sprintf("%.2f",$total_fee/$total_amount);//单瓶价格
                $up_data = array('price'=>$price,'total_fee'=>$total_fee,'unit_id'=>$unit_id,
                    'amount'=>$amount,'total_amount'=>$total_amount,'update_time'=>date('Y-m-d H:i:s'));

//                $idcode = $v['idcode'];
//                $sql_idcodes = "SELECT * from savor_finance_stock_record where idcode='{$idcode}' order by id asc";
//                $res_idcodes = $model->query($sql_idcodes);
//                foreach ($res_idcodes as $iv){
//                    if($iv['amount']<0 && $iv['total_amount']<0){
//                        $up_data['price'] = -$price;
//                        $up_data['total_fee'] = -$total_fee;
//                        $up_data['amount'] = -$amount;
//                        $up_data['total_amount'] = -$total_amount;
//                    }
//                    $m_record->updateData(array('id'=>$iv['id']),$up_data);
//                    echo 'id:'.$iv['id']."ok \r\n";
//                }
            }
        }

        echo 'error_ids:'.json_encode($error_ids);
        echo 'error_goods_ids:'.json_encode($error_goods_ids);
        echo 'error_unit_ids:'.json_encode($error_unit_ids);
    }

    public function stockunpackprice(){
        $model = M();
        $sql = "SELECT * from savor_finance_stock_record where type=3 order by id asc";
        $res_record = $model->query($sql);
        $m_record = new \Admin\Model\FinanceStockRecordModel();


        foreach ($res_record as $v){
            $id = $v['id'];
            $stock_detail_id = $v['stock_detail_id'];
            $idcode = $v['idcode'];

            $sql_idcodes = "SELECT * from savor_finance_stock_record where idcode='{$idcode}' order by id asc";
            $res_idcodes = $model->query($sql_idcodes);
            if($res_idcodes[0]['type']==3){

                $qrcode_id = decrypt_data($idcode,false);
                $qrcode_id = intval($qrcode_id);
                $m_qrcode_content = new \Admin\Model\FinanceQrcodeContentModel();
                $res_qrcode = $m_qrcode_content->getInfo(array('id'=>$qrcode_id));

                if(!empty($res_qrcode['parent_id'])){
                    $p_idcode = encrypt_data($res_qrcode['parent_id']);
                    $where = array('idcode'=>$p_idcode,'type'=>1);
                    $res_stock_record = $m_record->getInfo($where);

                    $sql_detail = "SELECT * from savor_finance_stock_detail where id={$stock_detail_id}";
                    $res_detail = $model->query($sql_detail);
                    $res_detail = $res_detail[0];
                    $goods_id = $res_detail['goods_id'];
                    $unit_id = $res_detail['unit_id'];
                    $price = $res_stock_record['price'];
                    $amount = 1;

                    $up_data = array('price'=>$price,'total_fee'=>$price,'unit_id'=>$unit_id,
                        'amount'=>$amount,'total_amount'=>$amount,'update_time'=>date('Y-m-d H:i:s'));

                    foreach ($res_idcodes as $iv){
                        if($iv['amount']<0 && $iv['total_amount']<0){
                            $up_data['price'] = -$price;
                            $up_data['total_fee'] = -$price;
                            $up_data['amount'] = -$amount;
                            $up_data['total_amount'] = -$amount;
                        }
                        $m_record->updateData(array('id'=>$iv['id']),$up_data);
                        echo 'id:'.$iv['id']."ok \r\n";
                    }

                }
            }
        }
    }

    public function stockqrcode(){
        $file_path = SITE_TP_PATH.'/Public/content/箱码明细0620.xlsx';
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");

        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $code_url = 'https://oss.littlehotspot.com/qrcode/goods/template6';
        $big_image_position = 'g_east,x_200,y_40';
        $data = array();
        $codes = array();
        for ($row = 2; $row <= $highestRow; $row++){
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            if(!empty($rowData[0][0])){
                $goods_id = $rowData[0][0];
                $hotel_id = $rowData[0][1];
                $hotel_name = trim($rowData[0][2]);
                $goods_name = trim($rowData[0][3]);
                $unit_name = $rowData[0][4];
                $stock_num = $rowData[0][5];
                $idcode = trim($rowData[0][6]);

                $id_num = decrypt_data($idcode,false);
                $big_file_name = "qrcode/goods/$id_num.png";
                $encode_file_name = $this->urlsafe_b64encode($big_file_name);
                $print_img = $code_url."-$id_num.jpg?x-oss-process=image/watermark,image_$encode_file_name,$big_image_position";

                $dinfo = array('goods_id'=>$goods_id,'hotel_id'=>$hotel_id,'hotel_name'=>$hotel_name,
                    'goods_name'=>$goods_name,'unit_name'=>$unit_name,'stock_num'=>$stock_num,
                    'idcode'=>$idcode,'id'=>$id_num,'img'=>$print_img);
                $data[]=$dinfo;
                $codes[]=$idcode;
            }
        }
        $cell = array(
            array('goods_id','商品ID'),
            array('hotel_id','酒楼ID'),
            array('hotel_name','酒楼名称'),
            array('goods_name','商品名称'),
            array('unit_name','单位'),
            array('stock_num','当前库存'),
            array('idcode','箱码'),
            array('img','二维码地址'),
        );

        $filename = '箱码明细';
        $fileName = $filename.'_'.date('YmdHis');

        $cellNum = count($cell);
        $dataNum = count($data);

        $objPHPExcel = new \PHPExcel();
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

        for ($i = 0; $i < $cellNum; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '1', $cell[$i][1]);
        }
        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 2), $data[$i][$cell[$j][0]]);
            }
        }
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $fileName . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    private function urlsafe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }

    public function cleanactivity(){
        $idcode = I('code','');
        $qrcontent = decrypt_data($idcode);
        $qr_id = intval($qrcontent);
        $m_qrcode_content = new \Admin\Model\FinanceQrcodeContentModel();
        $res_qrcontent = $m_qrcode_content->getInfo(array('id'=>$qr_id));
        if(empty($res_qrcontent)){
            $this->output('此码不存在');
        }
        $model = M();
        $sql = "select * from savor_smallapp_activityapply where activity_id in 
            (select id from savor_smallapp_activity where idcode='$idcode')";
        $res_apply = $model->query($sql);
        $res_activity = $model->query("select id from savor_smallapp_activity where idcode='$idcode'");

        if(empty($res_apply) && empty($res_activity)){
            $this->output('此码没有发起过售酒抽奖，可正常使用');
        }
        $sql_delapply = "delete from savor_smallapp_activityapply where activity_id in (
            select id from savor_smallapp_activity where idcode='$idcode')";
        $model->execute($sql_delapply);

        $sql_delactivity = "delete from savor_smallapp_activity where idcode='$idcode'";
        $model->execute($sql_delactivity);

        $this->output('清理成功,可再次发起售酒抽奖');
    }

    private function output($msg){
        header("Content-type: text/html; charset=utf-8");
        die($msg);
    }

    public function handlestockgoods(){
        $model = M();
        $sql = 'select * from savor_finance_stock_record where type=5 and dstatus=1 order by id desc';
        $res = $model->query($sql);
        $res_data = array();
        foreach ($res as $v){
            $idcode = $v['idcode'];
            $sql_unpack = "select * from savor_finance_stock_record where idcode='{$idcode}' and  dstatus=1 order by id desc";
            $res_unpack = $model->query($sql_unpack);
            if(!empty($res_unpack) && $res_unpack[0]['type']==3){
                $res_data[]=$idcode;
                echo "$idcode \r\n";
            }
        }
        print_r($res_data);
    }

    public function encodecoupon(){
        $coupon_id = I('get.couponuer_id',0,'intval');
        $en_data = array('type'=>'coupon','id'=>$coupon_id);
        $data_id = encrypt_data(json_encode($en_data),C('API_SECRET_KEY'));
        echo $data_id;
    }

    public function decodecoupon(){
        $code = I('get.coupon','');
        $coupon_content = decrypt_data($code,true,C('API_SECRET_KEY'));
        print_r($coupon_content);
        exit;
    }

    public function cleandevsell(){
        $dev_idcodes = "'7b949a819efc4af4','da85d75c1af9b074','f0c46956cf9c2ceb','dfa47a62ecf97108','28401d8af46e6016','566df1cafaa2aa8d','7f7144b1ae584e52','9540bf692c156f39','0b6e2109b0ec64e3','706c048d9f181a6b','905d28125391f848','abc5cd9d536cb2fd','06e41b8476e91942','960687635d7f736d','e14d0f22d85bf841','55b152928eaa4d95','86b5855c8df4c092','24e211212eb9ae35','7ef3ea2b832d6ade'";


        $sql_1 = "delete from savor_finance_stock_record where idcode in ({$dev_idcodes}) and type=7";
        $sql_2 = "delete from savor_smallapp_activityapply where activity_id in (select id from savor_smallapp_activity where 
         idcode in ({$dev_idcodes}))";
        $sql_3 = "delete from savor_smallapp_activity where idcode in ({$dev_idcodes})";
        $sql_4 = "delete from savor_smallapp_user_integralrecord where jdorder_id in ({$dev_idcodes})";
        $sql_5 = "UPDATE `cloud`.`savor_smallapp_usercoupon` SET `idcode` = '', `use_time` = '', `ustatus` = 1, `op_openid` = '' WHERE idcode in ({$dev_idcodes})";

        $model = M();
        $model->execute($sql_1);
        $model->execute($sql_2);
        $model->execute($sql_3);
        $model->execute($sql_4);
        $model->execute($sql_5);
        echo 'clean ok';
    }

    public function avgprice(){
        //暂时废弃
        exit;
        $m_goods = new \Admin\Model\FinanceGoodsModel();
        $res_goods = $m_goods->getDataList('*',array(),'id asc');
        $m_goods_avgprice = new \Admin\Model\FinanceGoodsAvgpriceModel();
        foreach ($res_goods as $v){
            $goods_id = $v['id'];
            $sql = "select goods_id,stock_id,stock_detail_id,sum(total_amount) as total_num,price from savor_finance_stock_record 
            where goods_id={$goods_id} and type in (1,3) and status=0 and dstatus=1 group by stock_detail_id";
            $res_num = $m_goods->query($sql);
            $price = 0;
            if(!empty($res_num)){
                $p_num = 0;
                $p_price = 0;
                foreach ($res_num as $pv){
                    if($pv['total_num']>0 && $pv['price']>0){
                        $p_num+=$pv['total_num'];
                        $p_price+=$pv['total_num']*$pv['price'];
                    }
                }
                $price = sprintf("%.2f",$p_price/$p_num);
            }
            $m_goods_avgprice->add(array('goods_id'=>$goods_id,'price'=>$price));
            echo "goods_id:$goods_id,price:$price ok \r\n";
        }
    }

    public function avgprices(){
        $m_qrcode_content = new \Admin\Model\FinanceQrcodeContentModel();
        $m_goods = new \Admin\Model\FinanceGoodsModel();
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $m_purchase_detail = new \Admin\Model\FinancePurchaseDetailModel();
        $m_avgprice = new \Admin\Model\FinanceGoodsAvgpriceModel();

        $res_goods = $m_goods->getDataList('*',array(),'id asc');
        $all_goods_avg_prices = array();
        foreach ($res_goods as $v){
            $goods_id = $v['id'];

            $sql = "select a.goods_id,sum(a.total_amount) as total_num,a.price,a.stock_id,a.stock_detail_id,stock.purchase_id,detail.purchase_detail_id from savor_finance_stock_record as a 
                left join savor_finance_stock as stock on a.stock_id=stock.id
                left join savor_finance_stock_detail as detail on a.stock_detail_id=detail.id
                where a.goods_id={$goods_id} and a.type=1 and stock.io_type=11 group by a.stock_detail_id";
            $res_data = $m_stock_record->query($sql);
            $goods_data = array();
            foreach ($res_data as $gk=>$gv){
                $stock_detail_id = $gv['stock_detail_id'];
                $in_where = array('goods_id'=>$goods_id,'stock_detail_id'=>$stock_detail_id,'type'=>1);
                $res_inend = $m_stock_record->getDataList('idcode,add_time,amount,total_amount',$in_where,'id desc');
                $idcodes = array();
                foreach ($res_inend as $inv){
                    if($inv['total_amount']==$inv['amount']){
                        $idcodes[]=$inv['idcode'];
                    }else{
                        $idcodes[]=$inv['idcode'];
                        $qrcontent = decrypt_data($inv['idcode']);
                        $qr_id = intval($qrcontent);
                        $res_allqrcode = $m_qrcode_content->getDataList('id',array('parent_id'=>$qr_id),'id asc');
                        foreach ($res_allqrcode as $qrv){
                            $qrcontent = encrypt_data($qrv['id']);
                            $idcodes[]=$qrcontent;
                        }
                    }
                }
                $gv['in_time'] = $res_inend[0]['add_time'];
                $gv['idcodes'] = $idcodes;
                $goods_data[]=$gv;
            }
            sortArrByOneField($goods_data,'in_time',false);

            $now_goods_avgprices = array();
            foreach ($goods_data as $gdk=>$gdv){
                $num = $gdv['total_num'];
                $now_idcodes = $gdv['idcodes'];

                $res_pd = $m_purchase_detail->getInfo(array('id'=>$gdv['purchase_detail_id']));
                $now_price = sprintf("%.2f",$res_pd['total_fee']/$res_pd['total_amount']);
                if($gdk==0){
                    $avg_price = ($num*$now_price)/$num;
                    $stock_num = 0;
                }else{
                    $last_total_num = $goods_data[$gdk-1]['total_num'];
                    $last_stock_idcodes = $goods_data[$gdk-1]['idcodes'];
                    $start_time = $goods_data[$gdk-1]['in_time'];
                    $end_time = $gdv['in_time'];

                    $wo_where = array('goods_id'=>$goods_id,'idcode'=>array('in',$last_stock_idcodes),'type'=>7,'wo_status'=>array('in','1,2,4'));
//                    $wo_where['add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                    $wo_fields = 'sum(total_amount) as total_num';
                    $res_wo_num = $m_stock_record->getAll($wo_fields,$wo_where,0,1);
                    $wo_num = 0;
                    if(!empty($res_wo_num[0]['total_num'])){
                        $wo_num = abs($res_wo_num[0]['total_num']);
                    }
                    $re_where = array('goods_id'=>$goods_id,'idcode'=>array('in',$last_stock_idcodes),'type'=>6,'status'=>array('in','1,2'));
//                    $re_where['add_time'] = array(array('egt',$start_time),array('elt',$end_time));
                    $re_fields = 'sum(total_amount) as total_num';
                    $res_re_num = $m_stock_record->getAll($re_fields,$re_where,0,1);
                    $report_num = 0;
                    if(!empty($res_re_num[0]['total_num'])){
                        $report_num = abs($res_re_num[0]['total_num']);
                    }
                    $stock_num  = $last_total_num-$wo_num-$report_num;
                    $stock_num = $stock_num + $now_goods_avgprices[$gdk-1]['stock_num'];
                    $last_avg_price = $now_goods_avgprices[$gdk-1]['avg_price'];

                    $avg_price = ($num*$now_price+$stock_num*$last_avg_price)/($num+$stock_num);
                    $avg_price = sprintf("%.2f",$avg_price);
                }
                $now_goods_avgprices[$gdk] = array('avg_price'=>$avg_price,'purchase_detail_id'=>$gdv['purchase_detail_id'],
                    'stock_detail_id'=>$gdv['stock_detail_id'],'stock_num'=>$stock_num);
                //更新savor_finance_stock_record表中 移动平均价avg_price
//                $m_stock_record->updateData(array('idcode'=>array('in',$now_idcodes)),array('avg_price'=>$avg_price));
            }

//            foreach ($now_goods_avgprices as $ngav){
//                $avg_data = array('goods_id'=>$goods_id,'stock_detail_id'=>$ngav['stock_detail_id'],
//                    'purchase_detail_id'=>$ngav['purchase_detail_id'],'price'=>$ngav['avg_price']);
//                $m_avgprice->add($avg_data);
//            }
            echo "goods_id:{$goods_id} ok \r\n";
            $all_goods_avg_prices[$goods_id] = $now_goods_avgprices;
        }
        print_r($all_goods_avg_prices);
    }

}