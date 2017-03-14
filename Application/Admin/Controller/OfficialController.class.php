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
	    
	    $list = $HotelModel->getInfo('id,name,gps,addr',$map);
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
	           $result[] = $tmp;
	       }
	    }
	    //echo  json_encode($result);exit;
	   echo  "login(".json_encode($result).")";
	}
	
}

?>