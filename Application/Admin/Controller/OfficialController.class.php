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
	    $m_heart_log = new \Admin\Model\HeartLogModel();
	    $where = array();
	    $fields = "box_id";
	    $heart_time = date('Y-m-d H:i:s',strtotime('-5 minutes')); 
	    $where['type'] = 2;
	    $where['last_heart_time'] = array('egt',$heart_time);
	    
	    $online_box = $m_heart_log->getHotelHeartBox($where,$fields);
	    $online_box_num = count($online_box);
	    
	    $m_box = new \Admin\Model\BoxModel();
	    
	    $fields = 'b.id';
	    $where = '1';
	    $where .= ' and h.flag  = 0';
	    $where .= ' and h.state = 1';
	    $where .= ' and b.flag  = 0';
	    $where .= ' and b.state = 1';
	    $box_list = $m_box->isHaveMac($fields,$where);
	    $normal_box_nums = count($box_list);
	    $data =  array();
	    $data['online_box_num'] = $online_box_num;
	    $data['normal_box_num'] = $normal_box_nums;
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
	    $where['complete_time'] = array('egt',$month_start_time);
	    $not_complete_task_num = $m_option_task->countNums($where); //本月待处理的任务
	    
	    $data =  array();
	    $data['complete_task_num'] = $complete_task_num;
	    $data['not_complete_task_num'] = $not_complete_task_num;
	    echo "bigscreen(".json_encode($data).")";
	    
	}
	/**
	 * @desc 
	 */
}

?>