<?php
/**
 *@desc u盘日志上报
 *
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
use Common\Lib\SavorRedis;
class SmallController extends BaseController{
    
    public function __construct(){
        parent::__construct();
    }
    /**
     * @desc 查看小平台节目单资源下载情况
     */
    public function mediadownloadlist(){
        $ajaxversion   = I('ajaxversion',0,'intval');//1 版本升级酒店列表
		$size   = I('numPerPage',500);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','update_time');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;

        $where = array();
        $heart_hotel = C('heart_hotel_box_type');
        $heart_hotel_box_type_arr = array_keys($heart_hotel);
        
        $where['hotel_box_type'] = array('in',$heart_hotel_box_type_arr);
        $where['state']          = 1;
        $where['flag']           = 0;
        $area_id =  I('area_id');
        $name    = I('name','','trim');
        if(!empty($area_id)){
            $where['area_id'] = $area_id;
            $this->assign('area_k',$area_id);
        }
        if(!empty($name)){
            $where['name']    = array('like',"%$name%");
            $this->assign('name',$name);
        }
		$m_hotel = new \Admin\Model\HotelModel();
		
		$fields = "id,name hotel_name,addr";
		$orders= 'area_id asc';
		$hotel_list = $m_hotel->getList($where,$orders,$start,$size,$fields);
		//echo $m_hotel->getLastSql();exit;
		$m_program_menu_hotel = new \Admin\Model\ProgramMenuHotelModel();
		$m_program_menu_item = new \Admin\Model\ProgramMenuItemModel();
		$m_box = new \Admin\Model\BoxModel();
		$m_pub_ads = new \Admin\Model\PubAdsModel();
		
		$program_ads_cache_pre = C('PROGRAM_ADS_CACHE_PRE');
		$redis = new SavorRedis();
		$redis->select(12);
		foreach($hotel_list['list'] as $key=>$v){
		    //获取该酒楼下的最新节目单
		    //获取最新节目单
		    $fields = "a.menu_id";
		    $order  = "pl.id desc ";
		    $limit  = " limit 0,1";
		    $menu_info = $m_program_menu_hotel->getProgramByHotelId($v['id'], $fields, $order, $limit);   //获取最新的一期节目单
		    if($menu_info){//节目资源
		        
		        $menu_id   = $menu_info[0]['menu_id'];
		        $map = array();
		        $map['a.menu_id'] = $menu_id;
		        $map['a.type']    = 2;
		        $fields = "media.id media_id,ads.name media_name";
		        $order ="a.sort_num asc";
		        $pro_list = $m_program_menu_item->getMediaList($fields, $map, $order, '');
		    }
		    //宣传片
		    $adv_arr = $m_program_menu_item->getadvInfo($v['id'], $menu_id);
		    
		    //获取该酒楼下的盒子
		    $box_list = $m_box->getInfoByHotelid($v['id'], 'box.id box_id', " and box.state=1 and box.flag=0");
		    $ads_arr = array();
		    foreach($box_list as $kk=>$vv){
		        $cache_key = $program_ads_cache_pre.$vv['box_id'];
		        $redis_value = $redis->get($cache_key);
		        if($redis_value){
		            $redis_value = json_decode($redis_value,true);
		            $redis_value = $redis_value['ads_list'];
		            $redis_value = assoc_unique($redis_value,'pub_ads_id');
		            $pub_ads_id_arr = array_keys($redis_value);
		            $whs = array();
		            $whs['pads.id'] = array('in',$pub_ads_id_arr);
		            $whs['pads.state']  = array('neq',2);
		            $ads_list = $m_pub_ads->getPubAdsList('med.id media_id,ads.name media_name',$whs);
		            
		            foreach($ads_list as $ks=>$vs){
		                
		                $ads_arr[] = $vs;
		            }
		        }
		    }
		    $ads_arr = assoc_unique($ads_arr, 'media_id');
		    $media_arr = array_merge($pro_list,$adv_arr,$ads_arr);
		    $z_media_arr = array();
		    //print_r($media_arr);exit;
		    foreach($media_arr as $zk=>$zv){
		        $z_media_arr[] = $zv['media_id'];
		    }
		    
		    $cache_key = C('SMALL_PROGRAM_LIST_KEY').$v['id'];
		    $redis->select(8);
		    $upload_media_list = $redis->get($cache_key);
		    $flag = 1;
		    if($upload_media_list){
		        $upload_media_list = json_decode($upload_media_list,true);
		        $upload_media_list = $upload_media_list['media_list'];
		        $up_media_arr = array();
		        foreach($upload_media_list as $mk=>$mv){
		            if($mv['flag']==0){
		                $flag = 0;
		                
		            }
		            $up_media_arr[] = $mv['id'];
		        }
		        if($flag==1){
		            //print_r($z_media_arr);exit;
		           $diff_arr = array_diff($z_media_arr, $up_media_arr);
		           //print_r($diff_arr);exit;
		           if(!empty($diff_arr)){
		               $flag = 0;
		           }
		        
		        }
		    }else {
		        $flag = 0;
		    }
		    
		    
		    
		    if($flag ==0){
		        $hotel_list['list'][$key]['small_download_state'] = 0;
		    }else {
		        $hotel_list['list'][$key]['small_download_state'] = 1;
		    }
		}
		sortArrByOneField($hotel_list['list'],'small_download_state',false);
		//print_r($hotel_list['list']);exit;
		//城市
		$m_area = new \Admin\Model\AreaModel();
		$area_arr = $m_area->getAllArea();
		
		$this->assign('area', $area_arr);
		$this->assign('list',$hotel_list['list']);
		$this->assign('page',$hotel_list['page']);
		$this->display('mediadownloadlist');
    }
    public function medialist(){
        $hotel_id = I('get.id','0','intval');
        if(!empty($hotel_id)){
            $redis = new SavorRedis();
            $redis->select(8);
            $cache_key = C('SMALL_PROGRAM_LIST_KEY').$hotel_id;
            $list = $redis->get($cache_key);
            
            if(!empty($list)){
                $list =  json_decode($list,true);
                $list =  $list['media_list'];
                $m_media = new \Admin\Model\MediaModel();
                sortArrByOneField($list,'type');
                foreach($list as $key=>$v){
                    $up_media_arr[] = $v['id'];
                    if($v['flag']==1){
                        $list[$key]['down_state'] = '已下载';
                    }else {
                        $list[$key]['down_state'] ='未下载';
                    }
                    $media_info = $m_media->getMediaInfoById($v['id']);
                    $list[$key]['name'] = $media_info['name'];
                    $list[$key]['oss_addr'] = $media_info['oss_addr'];
                    switch ($v['type']){
                        case 'pro':
                            $list[$key]['type'] = '节目';
                            break;
                        case 'adv':
                            $list[$key]['type'] = '宣传片';
                            break;
                        case 'ads':
                            $list[$key]['type'] = '广告';
                            break;
                    }
                }
                //start
                //获取最新节目单
                $m_program_menu_hotel = new \Admin\Model\ProgramMenuHotelModel();
                $m_program_menu_item = new \Admin\Model\ProgramMenuItemModel();
                $m_box = new \Admin\Model\BoxModel();
                $m_pub_ads = new \Admin\Model\PubAdsModel();
                $fields = "a.menu_id";
                $order  = "pl.id desc ";
                $limit  = " limit 0,1";
                $menu_info = $m_program_menu_hotel->getProgramByHotelId($hotel_id, $fields, $order, $limit);   //获取最新的一期节目单
                if($menu_info){//节目资源
                
                    $menu_id   = $menu_info[0]['menu_id'];
                    $map = array();
                    $map['a.menu_id'] = $menu_id;
                    $map['a.type']    = 2;
                    $fields = "media.id media_id,ads.name media_name,'pro' as type";
                    $order ="a.sort_num asc";
                    $pro_list = $m_program_menu_item->getMediaList($fields, $map, $order, '');
                    
                }
                //宣传片
                $adv_arr = $m_program_menu_item->getadvInfo($hotel_id, $menu_id);
                
                //获取该酒楼下的盒子
                $box_list = $m_box->getInfoByHotelid($hotel_id, 'box.id box_id', " and box.state=1 and box.flag=0");
                $ads_arr = array();
                $program_ads_cache_pre = C('PROGRAM_ADS_CACHE_PRE');
                foreach($box_list as $kk=>$vv){
                    $cache_key = $program_ads_cache_pre.$vv['box_id'];
                    $redis_value = $redis->get($cache_key);
                    if($redis_value){
                        $redis_value = json_decode($redis_value,true);
                        $redis_value = $redis_value['ads_list'];
                        $redis_value = assoc_unique($redis_value,'pub_ads_id');
                        $pub_ads_id_arr = array_keys($redis_value);
                        $whs = array();
                        $whs['pads.id'] = array('in',$pub_ads_id_arr);
                        $whs['pads.state']  = array('neq',2);
                        $ads_list = $m_pub_ads->getPubAdsList("med.id media_id,ads.name media_name,'ads' as type",$whs);
                
                        foreach($ads_list as $ks=>$vs){
                
                            $ads_arr[] = $vs;
                        }
                    }
                }
                $ads_arr = assoc_unique($ads_arr, 'media_id');
                
                $media_arr = array_merge($pro_list,$adv_arr,$ads_arr);
                $media_arr = assoc_unique($media_arr, 'media_id');
                
                //end
                
                foreach($media_arr as $zk=>$zv){
                    $temp[$zv['media_id']] = $zv;
                    $z_media_arr[] = $zv['media_id'];
                }
                $diff = array_diff($z_media_arr,$up_media_arr);
                
                $diff_arr = array();
                foreach($diff as $key=>$v){
                    
                    
                    $diff_arr[$key]['down_state'] ='未下载';
                    
                    $media_info = $m_media->getMediaInfoById($v);
                    $diff_arr[$key]['name'] = $media_info['name'];
                    $diff_arr[$key]['oss_addr'] = $media_info['oss_addr'];
                    $type = $temp[$v]['type'];
                    switch ($type){
                        case 'pro':
                            $diff_arr[$key]['type'] = '节目';
                            break;
                        case 'adv':
                            $$diff_arr[$key]['type'] = '宣传片';
                            break;
                        case 'ads':
                            $$diff_arr[$key]['type'] = '广告';
                            break;
                    }
                }
                
                $this->assign('diff',$diff_arr);
                $this->assign('list',$list);
                $this->display(medialist);
            }else {
                echo '<script>$.pdialog.closeCurrent();  alertMsg.error("该酒楼下的小平台未上报下载资源数据");</script>';
            }
        }else {
            $this->error('酒楼id错误');
        }
        
    }
}