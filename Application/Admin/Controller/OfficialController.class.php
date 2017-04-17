<?php 
/**
 *@author zhang.yingtao
 *@desc 公司官网接口
 *
 */
namespace Admin\Controller;

use Think\Controller;
class officialController extends Controller {
	public function getHotelGps(){
	    $HotelModel = new \Admin\Model\HotelModel();
	    $result = array();
	    $map = array();
	    $map['state'] =1;
	    
	    $list = $HotelModel->getInfo('id,name,gps,addr,hotel_box_type',$map);
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
	public function getHotelByPage(){
	    $pageSize = I('get.pageSize','12','intval');   //每页条数
	    $pageNo  = I('get.pageNo','1','intval');       //当前页数
	    $offset = ($pageNo-1) * $pageSize;
	    $hotelMode = new \Admin\Model\HotelModel();
	    $result =  array();
	    $map['state'] = 1;
	    $map['gps'] = array('NEQ','');
	    $limit = "$offset,$pageSize";
	    
	    $list = $hotelMode->getInfo('id,name,gps,addr,hotel_box_type',$map,'',$limit);
	    foreach($list as $keyd=>$v){
	       $tmp = array();
	       
           $gps_arr = explode(',', $v['gps']);
           $tmp['id'] = $v['id'];
           $tmp['name'] = $v['name'];
           $tmp['gps'] = $v['gps'];
           $tmp['lng'] = $gps_arr[0];
           $tmp['lat'] = $gps_arr[1];
           $tmp['addr'] = $v['addr'];
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
	    $where['gps'] = array("NEQ",'');
	    $count = $hotelMode->getHotelCount($where);
	    $total_page = ceil($count/$pageSize);
	    $data['list'] = $result;
	    $data['totalPage'] = $total_page;
	    $data['count'] = $count;
	    echo "hotel(".json_encode($data).")";
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
}

?>