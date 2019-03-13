<?php
/**
 *@desc u盘日志上报
 *
 */
namespace Admin\Controller;
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
        
        $where['a.hotel_box_type'] = array('in',$heart_hotel_box_type_arr);
        $where['a.state']          = 1;
        $where['a.flag']           = 0;
        $where['ext.mac_addr']     = array(array('neq','000000000000'),array('neq',''), 'and') ; ;
        
        
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
		$m_box   = new \Admin\Model\BoxModel();
		
		$fields = "a.id,a.name hotel_name,a.addr,a.area_id,area.region_name";
		$orders= 'a.area_id asc ,a.id asc';
		$hotel_list = $m_hotel->getListExt($where,$orders,$start,$size,$fields);

		$m_program_menu_hotel = new \Admin\Model\ProgramMenuHotelModel();
		$m_program_menu_item = new \Admin\Model\ProgramMenuItemModel();
		$m_box = new \Admin\Model\BoxModel();
		$m_pub_ads = new \Admin\Model\PubAdsModel();
		
		$program_ads_cache_pre = C('PROGRAM_ADS_CACHE_PRE');
		$redis = new SavorRedis();
		$redis->select(12);
        $m_program_menu_hotel = new \Admin\Model\ProgramMenuHotelModel();
		foreach($hotel_list['list'] as $key=>$v){
		    //获取该酒楼下的最新节目单
		    //获取最新节目单
		    $fields = "a.menu_id,pl.menu_num";
		    $order  = "pl.id desc ";
		    $limit  = " limit 0,1";
		    $menu_info = $m_program_menu_hotel->getProgramByHotelId($v['id'], $fields, $order, $limit);//获取最新的一期节目单
            $newest_num = 0;
            if($menu_info){//节目资源
                $newest_num = $menu_info[0]['menu_num'];
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
		    foreach($media_arr as $zk=>$zv){
		        $z_media_arr[] = $zv['media_id'];
		    }
		    
		    $cache_key = C('SMALL_PROGRAM_LIST_KEY').$v['id'];
		    $redis->select(8);
		    $upload_media_list = $redis->get($cache_key);
		    $flag = 1;
		    $data_type = 0;
		    if($upload_media_list){
		        $upload_media_list = json_decode($upload_media_list,true);
		        $upload_media_list = $upload_media_list['media_list'];
		        $up_media_arr = array();
		        foreach($upload_media_list as $mk=>$mv){
		            if(isset($mv['version']) && $newest_num){
		                if($mv['version']!=$newest_num){
                            $data_type = 1;
                        }
                    }
		            if($mv['flag']==0){
		                $flag = 0;
		            }
		            $up_media_arr[] = $mv['id'];
		        }
		        if(isset($upload_media_list[0]['version']) && $newest_num && $data_type==0){
                    $data_type = 2;
                }

		        if($flag==1){
		           $diff_arr = array_diff($z_media_arr, $up_media_arr);
		           if(!empty($diff_arr)){
		               $flag = 0;
		           }
		        }
		    }else {
		        $flag = 0;
		    }
            $hotel_list['list'][$key]['data_type'] = $data_type;
		    if($data_type==1){
                $hotel_list['list'][$key]['data_str'] = '旧资源';
            }elseif($data_type==2){
                $hotel_list['list'][$key]['data_str'] = '最新';
            }else{
                $hotel_list['list'][$key]['data_str'] = '';
            }

		    if($flag ==0){
		        $pre_box_mac ='';
		        $region_name = str_replace(array('省','市'), array('',''), $v['region_name']);
		        
		        $pre_box_mac = getFirstCharter($region_name);
		        
		        $box_list = $m_box->isHaveMac('b.mac', 'h.id='.$v['id']." and b.mac like '".$pre_box_mac."%' and b.state=1 and b.flag=0");
		        
		        if(!empty($box_list)){
		            $hotel_list['list'][$key]['is_installing'] = 1;
		        }
		        $count = $m_box->countNums(array('hotel.id='.$v['id']));
		        if(empty($count)){
		            $hotel_list['list'][$key]['is_installing'] = 1;
		        }
		        $hotel_list['list'][$key]['small_download_state'] = 0;
                $hotel_list['list'][$key]['small_download_state_str'] = '未下载完';
		    }else {
		        $hotel_list['list'][$key]['small_download_state'] = 1;
                $hotel_list['list'][$key]['small_download_state_str'] = '已下载完';
		    }
		}
		sortArrByOneField($hotel_list['list'],'small_download_state',false);
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
            $m_program_menu_hotel = new \Admin\Model\ProgramMenuHotelModel();
            $fields = "a.menu_id,pl.menu_num";
            $order  = "pl.id desc ";
            $limit  = " limit 0,1";
            $menu_info = $m_program_menu_hotel->getProgramByHotelId($hotel_id, $fields, $order, $limit);   //获取最新的一期节目单
            $newest_num = $menu_info[0]['menu_num'];

            $redis = new SavorRedis();
            $redis->select(8);
            $cache_key = C('SMALL_PROGRAM_LIST_KEY').$hotel_id;
            $list = $redis->get($cache_key);
            $list =  json_decode($list,true);
            if(!empty($list['media_list'])){
                $list =  $list['media_list'];
                $m_media = new \Admin\Model\MediaModel();
                sortArrByOneField($list,'type');
                $up_media_arr = array();
                foreach($list as $key=>$v){
                    $diff_status = 0;
                    $up_media_arr[] = $v['id'];
                    if($v['flag']==1){
                        $list[$key]['down_state'] = '已下载';
                    }else {
                        $list[$key]['down_state'] ='未下载';
                    }
                    if(isset($v['version'])){
                        if($v['version']==$newest_num){
                            $diff_status = 1;
                        }else{
                            $diff_status = 2;
                        }
                    }
                    $list[$key]['diff_status'] = $diff_status;
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
                sortArrByOneField($list,'diff_status',true);

                //获取最新节目单
                $m_program_menu_item = new \Admin\Model\ProgramMenuItemModel();
                $m_box = new \Admin\Model\BoxModel();
                $m_pub_ads = new \Admin\Model\PubAdsModel();

                $pro_list = $adv_arr = array();
                if($menu_info){//节目资源
                    $menu_id = $menu_info[0]['menu_id'];
                    $map = array('a.menu_id'=>$menu_id,'a.type'=>2);
                    $fields = "media.id media_id,ads.name media_name,'pro' as type";
                    $order ="a.sort_num asc";
                    $pro_list = $m_program_menu_item->getMediaList($fields, $map, $order, '');

                    //宣传片
                    $adv_arr = $m_program_menu_item->getadvInfo($hotel_id, $menu_id);
                }

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
                $z_media_arr = $temp = array();
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
                            $diff_arr[$key]['type'] = '宣传片';
                            break;
                        case 'ads':
                            $diff_arr[$key]['type'] = '广告';
                            break;
                    }
                }
                $this->assign('newest_num',$newest_num);
                $this->assign('diff',$diff_arr);
                $this->assign('list',$list);
                $this->display('medialist');
            }else {
                echo '<script>$.pdialog.closeCurrent();  alertMsg.error("该酒楼下的小平台未上报下载资源数据");</script>';
            }
        }else {
            $this->error('酒楼id错误');
        }
        
    }
}