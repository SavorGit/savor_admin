<?php 
/**
 *@author zhang.yingtao
 *@desc 公司官网接口
 *
 */
namespace Admin\Controller;

use Think\Controller;
use Common\Lib\SavorRedis;
class officialController extends Controller {
    public function getHotelGps(){
        $areaid = I('get.areaid','0','intval');
        $HotelModel = new \Admin\Model\HotelModel();
        $result = array();
        $map = array();
        if(!empty($areaid))
        {
            $map['area_id'] = $areaid;
        }
        $map['state'] =1;
         
        $list = $HotelModel->getInfo('id,name,gps,addr,hotel_box_type,area_id',$map);
        foreach($list as $keyd=>$v){
            $tmp = array();
            if(!empty($v['gps'])){
                $gps_arr = explode(',', $v['gps']);
                $tmp['id'] = $v['id'];
                $tmp['name'] = $v['name'];
                $tmp['gps'] = $v['gps'];
                $tmp['lng'] = $gps_arr[0];
                $tmp['lat'] = $gps_arr[1];
                $tmp['addr'] = $v['addr'];
                $tmp['areaid'] = $v['area_id'];
                if(empty($v['hotel_box_type'])){
                    $tmp['is_screen'] = 0;
                }else {
                    if($v['hotel_box_type'] ==1 || $v['hotel_box_type'] ==2){
                        $tmp['is_screen'] = 0;
                    }else if($v['hotel_box_type'] ==3){
                        $tmp['is_screen'] = 1;
                    }
                }
                $result[] = $tmp;
            }
        }
        //echo  json_encode($result);exit;
        echo  "login(".json_encode($result).")";
    }
    public function getOpHotelGps(){
        $userid = I('get.userid','0','intval');
        //$userid = 137;
        $result = array();
        if($userid){
            $op_user_role_arr = C('OPTION_USER_ROLE_ARR');
            $m_user = new \Admin\Model\UserModel();
            $userinfo = $m_user->getUserInfo($userid);
            $role_id =  $userinfo['groupid'];
            
            if($role_id ==1){ //超级管理员
                $m_option_task = new \Admin\Model\OptiontaskModel();
                $sql ="select hotel_id from savor_option_task
                           where task_type=4 and flag=0 and state in(1,2,3)
                           group by hotel_id";
                 
                $data = $m_option_task->query($sql);
            }else {
                $m_opuser_role = new \Admin\Model\OpuserroleModel();
                
                $fields = 'a.role_id';
                $where = array();
                $where['a.user_id'] = $userid;
                $where['a.state'] = 1;
                $where['user.status'] = 1;
                $user_arr = $m_opuser_role->getInfo($fields, $where);
                
                if($user_arr['role_id'] ==3){//执行者
                    
                    $m_option_task = new \Admin\Model\OptiontaskModel();
                    $sql ="select hotel_id from savor_option_task 
                           where task_type=4 and  FIND_IN_SET($userid,`exe_user_id`) 
                           and flag=0 and state in(1,2,3)
                           group by hotel_id";
                    $data = $m_option_task->query($sql);
                    
                }else {//非执行者
                    $m_option_task = new \Admin\Model\OptiontaskModel();
                    $sql ="select hotel_id from savor_option_task 
                           where task_type=4 and flag=0 and state in(1,2,3)
                           group by hotel_id";
                   
                    $data = $m_option_task->query($sql);
                }  
            } 
            $m_hotel =  new \Admin\Model\HotelModel();
            foreach($data as $key=>$v){
                $map = array();
                $map['id'] = $v['hotel_id'];
                
                $list = $m_hotel->getInfo('id,name,gps,addr,hotel_box_type,area_id',$map);
                $hotel_info = $list[0];
            
                $tmp = array();
                if(!empty($hotel_info['gps'])){
                    $gps_arr = explode(',', $hotel_info['gps']);
                    $tmp['id'] = $hotel_info['id'];
                    $tmp['name'] = $hotel_info['name'];
                    $tmp['gps'] = $hotel_info['gps'];
                    $tmp['lng'] = $gps_arr[0];
                    $tmp['lat'] = $gps_arr[1];
                    $tmp['addr'] = $hotel_info['addr'];
                    $tmp['areaid'] = $hotel_info['area_id'];
                    if(empty($hotel_info['hotel_box_type'])){
                        $tmp['is_screen'] = 0;
                    }else {
                        $heart_box_type_arr = C('heart_hotel_box_type');
                        if(array_key_exists($hotel_info['hotel_box_type'], $heart_box_type_arr)){
                            $tmp['is_screen'] = 1;
                        } else {
                            $tmp['is_screen'] = 0;
                        }
                         
                    }
                    $result[] = $tmp;
                }
            }
        }
        
        echo  "login(".json_encode($result).")";
    }
    
    
    
    public function getHotelByPage(){
        $pageSize = I('get.pageSize','12','intval');   //每页条数
        $pageNo  = I('get.pageNo','1','intval');       //当前页数
        $areaid = I('get.areaid','0','intval');        //区域id
        $offset = ($pageNo-1) * $pageSize;
        $hotelMode = new \Admin\Model\HotelModel();
        $result =  array();
         
        $map['state'] = 1;
        $map['gps'] = array('NEQ','');
        if($areaid){
            $map['area_id'] = $areaid;
        }
        $limit = "$offset,$pageSize";
         
        $list = $hotelMode->getInfo('id,name,gps,addr,hotel_box_type,area_id',$map,'',$limit);
        foreach($list as $keyd=>$v){
            $tmp = array();

            $gps_arr = explode(',', $v['gps']);
            $tmp['id'] = $v['id'];
            $tmp['name'] = $v['name'];
            $tmp['gps'] = $v['gps'];
            $tmp['lng'] = $gps_arr[0];
            $tmp['lat'] = $gps_arr[1];
            $tmp['addr'] = $v['addr'];
            $tmp['areaid'] = $v['area_id'];
            if(empty($v['hotel_box_type'])){
                $tmp['is_screen'] = 0;
            }else {
                if($v['hotel_box_type'] ==1 || $v['hotel_box_type'] ==2){
                    $tmp['is_screen'] = 0;
                }else if($v['hotel_box_type'] ==3){
                    $tmp['is_screen'] = 1;
                }
            }
            $result[] = $tmp;
             
        }
        $where['state'] = 1;
        if($areaid){
            $where['area_id'] = $areaid;
        }
        $where['gps'] = array("NEQ",'');
        $count = $hotelMode->getHotelCount($where);
        $total_page = ceil($count/$pageSize);
        $data['list'] = $result;
        $data['totalPage'] = $total_page;
        $data['count'] = $count;
        echo "hotel(".json_encode($data).")";
    }
    
    public function getOpHotelByPage(){
        $userid   = I('get.userid','0','intval') ;     //用户id
        $pageSize = I('get.pageSize','12','intval');   //每页条数
        $pageNo  = I('get.pageNo','1','intval');       //当前页数
        $areaid = I('get.areaid','0','intval');        //区域id
        $offset = ($pageNo-1) * $pageSize;
        $hotelMode = new \Admin\Model\HotelModel();
        $result =  array();
         
       
        $limit = "$offset,$pageSize";

        $datas = array();
        if($userid){
            $op_user_role_arr = C('OPTION_USER_ROLE_ARR');
            $m_user = new \Admin\Model\UserModel();
            $userinfo = $m_user->getUserInfo($userid);
            $role_id =  $userinfo['groupid'];
        
            if($role_id ==1){ //超级管理员
                $m_option_task = new \Admin\Model\OptiontaskModel();
                $sql ="select hotel_id from savor_option_task
                           where task_type=4 and flag=0 and state in(1,2,3)
                           group by hotel_id limit $limit";
                 
                $data = $m_option_task->query($sql);
            }else {
                $m_opuser_role = new \Admin\Model\OpuserroleModel();
        
                $fields = 'a.role_id';
                $where = array();
                $where['a.user_id'] = $userid;
                $where['a.state'] = 1;
                $where['user.status'] = 1;
                $user_arr = $m_opuser_role->getInfo($fields, $where);
        
                if($user_arr['role_id'] ==3){//执行者
        
                    $m_option_task = new \Admin\Model\OptiontaskModel();
                    $sql ="select hotel_id from savor_option_task
                    where task_type=4 and  FIND_IN_SET($userid,`exe_user_id`)
                    and flag=0 and state in(1,2,3)
                    group by hotel_id limit $limit";
                    $data = $m_option_task->query($sql);
        
                }else {//非执行者
                    $m_option_task = new \Admin\Model\OptiontaskModel();
                    $sql ="select hotel_id from savor_option_task
                    where task_type=4 and flag=0 and state in(1,2,3)
                    group by hotel_id limit $limit";
          
                    $data = $m_option_task->query($sql);
                }
            }
            $m_hotel =  new \Admin\Model\HotelModel();
            foreach($data as $key=>$v){
                $map = array();
                $map['id'] = $v['hotel_id'];
                $map['gps'] = array('neq','');
                $list = $m_hotel->getInfo('id,name,gps,addr,hotel_box_type,area_id',$map);
                $hotel_info = $list[0];
            
                $tmp = array();
                if(!empty($hotel_info['gps'])){
                    $gps_arr = explode(',', $hotel_info['gps']);
                    $tmp['id'] = $hotel_info['id'];
                    $tmp['name'] = $hotel_info['name'];
                    $tmp['gps'] = $hotel_info['gps'];
                    $tmp['lng'] = $gps_arr[0];
                    $tmp['lat'] = $gps_arr[1];
                    $tmp['addr'] = $hotel_info['addr'];
                    $tmp['areaid'] = $hotel_info['area_id'];
                    if(empty($hotel_info['hotel_box_type'])){
                        $tmp['is_screen'] = 0;
                    }else {
                        $heart_box_type_arr = C('heart_hotel_box_type');
                        if(array_key_exists($hotel_info['hotel_box_type'], $heart_box_type_arr)){
                            $tmp['is_screen'] = 1;
                        } else {
                            $tmp['is_screen'] = 0;
                        }
                         
                    }
                    $result[] = $tmp;
                }
            }
            $m_option_task = new \Admin\Model\OptiontaskModel();
            $sql ="select count(id) as num from savor_hotel where id in(select hotel_id from savor_option_task
            where task_type=4 and flag=0 and state in(1,2,3)
            group by hotel_id ) and gps !=''";
            $ret = $m_option_task->query($sql);
            $count = $ret[0]['num'];
            $total_page = ceil($count/$pageSize);
            $datas['list'] = $result;
            $datas['totalPage'] = $total_page;
            $datas['count'] = $count;
            
        }
        echo "hotel(".json_encode($datas).")";
    }
    
	public function countDownload(){
	    $data = array();
	    $source_arr = array('office'=>1,'qrcode'=>2,'usershare'=>3,'scan'=>4);
        $client_arr = array('android'=>1,'ios'=>2);
	    $st = I('get.st','','trim');
	    $clientname = I('get.clientname','','trim');
	    $deviceid   = I('get.deviceid','','trim');
	    if(empty($st)){//来源
	        $data['source_type'] = 1;
	    }else {
	        if(!key_exists($st, $source_arr)){
	            return false;
	        }else {
	            $data['source_type'] = $source_arr[$st];    //分享设备类型
	        }
	    }
	    if(!empty($clientname)){
	        $clientname = strtolower($clientname);
	        if(key_exists($clientname, $client_arr)){
	            $data['clientid'] = $client_arr[$clientname];
	        }
	    }
	    if(!empty($deviceid)){
	        $data['deviceid'] = $deviceid;   //分享设备唯一标示
	    }
	    $data['dowload_device_id'] = I('get.dowload_device_id','0','intval');  //下载设备
	    $data['add_time'] = date('Y-m-d H:i:s');
	    $m_download_count = new \Admin\Model\DownloadCountModel();
	    $m_download_count->addInfo($data);
	    echo "download(".json_encode($data).")";
	}
	
	/**
	 * @desc 获取map地图区域列表
	 */
	public function getHotelAreaList(){
	    $m_area = new \Admin\Model\AreaModel();
	    $map = array();
	    $map['id'] = array('in','1,9,19');
	    $arealist = $m_area->field('id as areaid,region_name')->where($map)->select();
	    $arr = array('areaid'=>0,'region_name'=>'全国');
	    array_push($arealist, $arr);
	    echo "arealist(".json_encode($arealist).")";
	}
	
	/**
	 * @desc 大屏数据监控 - 获取在线版位以及版位总数
	 */
	public function getBoxNums(){
	    /* $m_heart_log = new \Admin\Model\HeartLogModel();
	    $where = array();
	    $fields = "box_id";
	    $heart_time = date('Y-m-d H:i:s',strtotime('-10 minutes')); 
	    $where['type'] = 2;
	    $where['last_heart_time'] = array('egt',$heart_time);
	    
	    $online_box = $m_heart_log->getHotelHeartBox($where,$fields);
	    $online_box_num = count($online_box); */
	    $online_box_num = 0;
	    $heart_time = date('YmdHis',strtotime('-10 minutes'));
	    $redis = new SavorRedis();
	    $redis->select('13');
	    $keys = $redis->keys('heartbeat:2:*');
	    foreach($keys as $v){
	        $h_data = $redis->get($v);
	        $h_data = json_decode($h_data,true);
	        if($h_data['date']>=$heart_time){
	            $online_box_num +=1;
	        }
	    }
	    
	    $m_box = new \Admin\Model\BoxModel();
	    
	    $fields = 'b.id';
	    $where = '1';
	    $where .= ' and h.flag  = 0';
	    $where .= ' and h.state = 1';
	    $where .= ' and b.flag  = 0';
	    $where .= ' and b.state = 1';
	    $heart_hotel_box_type = C('heart_hotel_box_type');
	    
	    $net_box_arr = array_keys($heart_hotel_box_type);
	    $net_box_str = '';
	    foreach($net_box_arr as $v){
	        $net_box_str .= $space. $v;
	        $space = ',';
	    }
	    $where .= ' and hotel_box_type in('.$net_box_str.')';
	    $box_list = $m_box->isHaveMac($fields,$where);
	    $normal_box_nums = count($box_list);
	    $data =  array();
	    $data['online_box_num'] = $online_box_num;
	    //$data['online_box_num']  = rand(20, 100);
	    $data['normal_box_num'] = $normal_box_nums;
	    $data['date'] = date('Ymd');
	    echo "bigscreen(".json_encode($data).")"; 
	}
	/**
	 * @desc 大屏数据监控-运维任务统计
	 */
	public function getOpTaskNums(){
	    $m_option_task =  new \Admin\Model\OptiontaskModel();
	    //当月第一天
	    $month_start_time = date('Y-m-01 H:i:s',strtotime(date('Y-m-d'))); 
	    $where = array();
	    $where['flag']  = 0;
	    $where['state'] = array('in',array('4'));
	    $where['complete_time'] = array('egt',$month_start_time);
	    $complete_task_num = $m_option_task->countNums($where);  //本月已处理任务
	    
	    $where = array();
	    $where['flag'] = 0;
	    $where['state'] = array('in',array('1','2','3'));
	    $where['create_time'] = array('egt',$month_start_time);
	    $not_complete_task_num = $m_option_task->countNums($where); //本月待处理的任务
	    
	    $data =  array();
	    $data['complete_task_num'] = $complete_task_num;
	    $data['not_complete_task_num'] = $not_complete_task_num;
	    echo "big_task_count(".json_encode($data).")";
	    
	}
	/**
	 * @desc 网络版酒楼有效开机
	 */
	public function wakeUphotelCount(){
	    $m_area_info = new \Admin\Model\AreaModel();
	    $area_list = $m_area_info->getHotelAreaList();
	    $m_box = new \Admin\Model\BoxModel();
	    $m_valid_online_monitor = new \Admin\Model\Statisticses\ValidOnlineMonitorModel();
	    
	    $report_time = date('Ymd',strtotime('-1 days'));
	    $type = 2;
	    $heart_hotel_box_type = C('heart_hotel_box_type');
	    $net_box_arr = array_keys($heart_hotel_box_type);
	    
	    foreach($area_list as $key=>$v){
	        $area_list[$key]['region_name'] = str_replace('市', '', $v['region_name']);
	        $map   = array();
	        $where = array();
	        $where['area_id'] = $v['id'];
	        $where['type'] = $type;
	        $where['report_date'] = $report_time;
			$all_box_nums = $m_valid_online_monitor->countNums($where);
			$where['state'] = 0;
			$not_valid_nums = $m_valid_online_monitor->countNums($where);
			$where['state'] = 1;
			$valid_nums = $m_valid_online_monitor->countNums($where);
	        $area_list[$key]['valid_nums'] = $valid_nums;
	        $area_list[$key]['not_valid_nums'] = $not_valid_nums;
			$area_list[$key]['all_box_nums'] = $all_box_nums;
	    }
	    $data = array();
	    $data['date'] = date('Y-m-d',strtotime($report_time));
	    $data['list'] = $area_list;
	    
	    echo "valid_box(".json_encode($data).")";
	}
	/**
	 * @desc 广告昨日到达明细
	 */
	public function adsReachCount(){
	    $m_program_ads = new \Admin\Model\PubAdsModel();
	    $fields = 'med.id,ads.name,pads.start_date,pads.id pub_ads_id';
	    $now_date =  date('Y-m-d H:i:s');
	    $where = array();
	    $where['pads.start_date'] = array('elt',$now_date);
	    $where['pads.end_date']   = array('egt',$now_date);
	    $where['pads.state']      = 1;
	    
	    $media_list = $m_program_ads->getPubAdsList($fields,$where);
	    $data =  array();
	    
	    $m_media_monitor = new \Admin\Model\Statisticses\MediaMonitorModel();
	    $m_pub_ads_box = new \Admin\Model\PubAdsBoxModel();
	    $type = 'ads';
	    $yesterday = date('Y-m-d 00:00:00',strtotime('-1 days'));
	    foreach($media_list as $key=>$v){
	        $media_list[$key]['start_date'] = date('Y-m-d',strtotime($v['start_date']));
	        //$pub_ads_count = $m_pub_ads_box->getDataCount(array('pub_ads_id'=>$v['pub_ads_id']));
	        $sql ="SELECT COUNT(t.counts) nums FROM  (SELECT COUNT(*) counts FROM savor_pub_ads_box t WHERE `pub_ads_id` = ".$v['pub_ads_id']." GROUP BY box_id) t";
	        $pub_ads_count = $m_program_ads->query($sql);
	        
	        //echo $m_pub_ads_box->getLastSql();exit;
	        $pub_ads_count = $pub_ads_count[0]['nums'];
	        
	        $where = array();
	        $where['media_id'] = $v['id'];
	        $where['media_type'] = $type;
	        $where['report_date'] = $yesterday;
	        $valid_nums = $m_media_monitor->countNums($where);
	        $media_list[$key]['valid_nums'] = $valid_nums;
	        $media_list[$key]['not_valid_nums'] = $pub_ads_count-$valid_nums;
	    }
	    $data = array();
	    $data['date'] = date('Y-m-d',strtotime('-1 days'));
	    $data['list'] = $media_list;
	    echo "valid_ads(".json_encode($data).")";
	    
	}
	/**
	 * @desc 内容到达昨日明细
	 */
	public function programReachCount(){
	    /* $m_program_list = new \Admin\Model\ProgramMenuListModel();
	    
	    $fields ='id,menu_name,menu_num,create_time';
	    $where = array();
	    $where['state'] = 1;
	    $program_list = $m_program_list->getAll($fields,$where,$offset=0,$limit=7,$order='id desc');
	     */
	    $hotel_box_type_arr = C('heart_hotel_box_type');
        $hotel_box_type_arr = array_keys($hotel_box_type_arr);
        $space = '';
        $hotel_box_type_str = '';
        foreach($hotel_box_type_arr as $key=>$v){
            $hotel_box_type_str .= $space .$v;
            $space = ',';
        }
        //获取所有酒楼
        $m_hotel = new \Admin\Model\HotelModel();        
        $where = "  a.state=1 and a.flag =0 and a.hotel_box_type in($hotel_box_type_str) ";
        $hotel_list = $m_hotel->getHotelLists($where,'','','a.id,b.mac_addr');
        $m_programmenu_hotel = new \Admin\Model\ProgramMenuHotelModel();
        $program_list = array();
        $mult_arr = array();

        foreach($hotel_list as $key=>$v){
            $fields = 'pl.id,pl.hotel_num,pl.menu_name,pl.create_time,pl.menu_num';
            $order = 'pl.create_time desc';
            $limit = ' 1';
            
            $ret = $m_programmenu_hotel->getProgramByHotelId($v['id'], $fields, $order, $limit);
            if($ret){
                $program_list[] = $ret[0];
                $mult_arr[] = $ret[0]['hotel_num'];
                
            }
            
        }
        sortArrByOneField($program_list, 'hotel_num',true);
        assoc_unique_new($program_list,'id');
        $program_list = array_slice($program_list, 0,7);
        //print_r($program_list);exit;
        $m_program_list = new \Admin\Model\ProgramMenuListModel();
        $where = array();
        $menu_id_arr = array();
        foreach($program_list as $v){
            $menu_id_arr[] = $v['id'];
        }
        
        $where['id'] = array('not in',$menu_id_arr);
        $where['hotel_num']= array('gt',0);
        $fields = 'id,hotel_num,menu_name,create_time,menu_num';
        $order  = ' id desc';
        $limit  = ' 13'; 
        $more_program_list = $m_program_list->getWhere($where,$fields,$order,$limit);
        //echo $m_program_list->getLastSql();exit;
        $program_list = array_merge($program_list,$more_program_list);

	    $m_program_hotel = new \Admin\Model\ProgramMenuHotelModel();
	    $m_box = new \Admin\Model\BoxModel();
	    $m_version_monitor = new \Admin\Model\Statisticses\VersionMonitorModel();
	    $type = 'pro_down';
	    $yesterday = date('Y-m-d 00:00:00',strtotime('-1 days'));
	    foreach($program_list as $key=>$v){
	        $program_list[$key]['create_time'] = date('Y-m-d H:i',strtotime($v['create_time']));
	        $where = array();
	        $where['menu_id'] = $v['id'];
	        $fields = 'hotel_id';
	        $hotel_list = $m_program_hotel->getWhere($where,'',$fields);
	        $box_all_nums = 0;
	        foreach($hotel_list as $k=>$kv){
	            $map = array();
	            $map['hotel.id'] = $kv['hotel_id'];
	            $map['hotel.flag'] = 0;
	            $map['hotel.state'] = 1;
	            $map['box.flag'] = 0;
	            $map['box.state'] =1;
	            $box_nums = $m_box->countNums($map);
	            $box_all_nums +=$box_nums;
	        }
	        $where = array();
	        $where['version_code'] = $v['menu_num'];
	        $where['version_type'] = $type;
	        $where['report_date'] = $yesterday;
	        $valid_nums = $m_version_monitor->countNums($where);
	        
	        $not_valid_nums = $box_all_nums - $valid_nums;
	        $not_valid_nums = $not_valid_nums>0 ?$not_valid_nums:0;
	        $program_list[$key]['valid_nums'] = $valid_nums;
	        $program_list[$key]['not_valid_nums'] = $not_valid_nums;
	    }
	    $data = array();
	    $data['date'] = date('Y-m-d',strtotime('-1 days'));
	    $data['list'] = $program_list;
	    echo "valid_program(".json_encode($data).")";
	}
}

?>