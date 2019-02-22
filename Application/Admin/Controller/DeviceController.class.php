<?php
/**
 *机顶盒管理
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Admin\Model\BoxModel;
use Admin\Model\RoomModel;
use Admin\Model\TvModel;
use Common\Lib\SavorRedis; 

class DeviceController extends BaseController{

    /**
     * 机顶盒列表
     * 
     * @return [type] [description]
     */
    public function box(){	

    	$boxModel = new BoxModel;
    	$size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = "1=1";
        $name = I('name');
        $hotel_id = I('hotel_id',0,'intval');
        $room_id = I('room_id',0,'intval');
        if($name){
        	$this->assign('name',$name);
        	$where .= "	AND name LIKE '%{$name}%'";
        }
        if($room_id){
            $where.=" AND room_id='$room_id'";
            $result = $boxModel->getList($where,$orders,$start,$size);
        }elseif($hotel_id){
            $hotelModel = new \Admin\Model\HotelModel();
            $rooms = $hotelModel->getStatisticalNumByHotelId($hotel_id,'room');
            if($rooms['room_num']){
                $rooms_str = join(',', $rooms['room']);
                $where.=" AND room_id in ($rooms_str)";
                $result = $boxModel->getList($where,$orders,$start,$size);
            }
        }
        if(!empty($result['list'])){
            $poly_screen_media_arr = C('POLY_SCREEN_MEDIA_LIST');
            $tvModel = new \Admin\Model\TvModel();
            foreach ($result['list'] as $k=>$v){
                $box_id = $v['id'];
                $tv_num = $tvModel->where("box_id='$box_id'")->count();
                $result['list'][$k]['tv_num'] = $tv_num;
                $tpmedia_id_str = $space = '';
                if($v['tpmedia_id']){
                    $tpmedia_id_arr = explode(',', $v['tpmedia_id']);
                    foreach($tpmedia_id_arr as $vv){
                        $tpmedia_id_str .= $space . $poly_screen_media_arr[$vv];
                        $space = ',';
                    }
                    $result['list'][$k]['tpmedia_id_str'] = $tpmedia_id_str;
                }
            }
            $result['list'] = $boxModel->roomIdToRoomName($result['list']);

        }
   		$this->assign('room_id', $room_id);
   		$this->assign('hotel_id', $hotel_id);
   		$this->assign('list', $result['list']);
   	    $this->assign('page',  $result['page']);
        $this->display('box');
    }


    /**
     * 电视管理列表
     * @return [type] [description]
     */
    public function tv(){
    	$hotel_id = I('hotel_id',0,'intval');
    	$box_id = I('box_id',0,'intval');
    	$size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $name = I('name');
        $where = array();
        if($name){
        	$this->assign('name',$name);
        	$where['tv_brand'] = array('LIKE',"%$name%");
        }
    	$tvModel = new TvModel;
    	if($box_id){
    	    $where['box_id'] = $box_id;
    	    $result = $tvModel->getList($where,$orders,$start,$size);
    	}elseif($hotel_id){
            $hotelModel = new \Admin\Model\HotelModel();
            $boxs = $hotelModel->getStatisticalNumByHotelId($hotel_id,'box');
            if($boxs['box_num']){
                $box_str = join(',', $boxs['box']);
                $where['box_id'] = array('IN',"$box_str");
                $result = $tvModel->getList($where,$orders,$start,$size);
            }
        }else{
            $result = $tvModel->getList($where,$orders,$start,$size);
        }
		
        $result['list'] = $tvModel->boxIdToBoxName($result['list']);
        $this->assign('hotel_id',$hotel_id);
        $this->assign('box_id',$box_id);
   		$this->assign('list', $result['list']);
   	    $this->assign('page',  $result['page']);
        $this->display('tv');
    }

    /**
	 * 新增tv
	 * 
	 */
	public function addTv(){	
		$id = I('get.id');
		$box_id = I('box_id',0);
		$boxModel = new BoxModel;
		$tvModel =  new TvModel;

		if($id){
			$vinfo = $tvModel->where('id='.$id)->find();
			$box_id = $vinfo['box_id'];
			$temp = $boxModel->getRow('name',['id'=>$box_id]);
			$vinfo['box_name'] = $temp['name'];
		}elseif($box_id){
		    $temp = $boxModel->getRow('name',['id'=>$box_id]);
		    $vinfo = array();
		    $vinfo['box_name'] = $temp['name'];
		    $vinfo['box_id'] = $box_id;
			$vinfo['state'] = 1;
		}


		$this->assign('vinfo',$vinfo);

		$this->display('addTv');
	}

	/**
	 * 新增机顶盒
	 * 
	 */

	public function addBox(){
		$room_id = I('get.room_id');
		$roomModel = new RoomModel;
		$temp = $roomModel->getRow('name',['id'=>$room_id]);
		$this->assign('room_name',$temp['name']);
		$this->assign('room_id',$room_id);
		$vinfo['state'] = 2;
		$vinfo['name'] = $temp['name'];
		$vinfo['switch_time'] = 30;
		$vinfo['volum'] = 60;
		$this->assign('vinfo', $vinfo);
		$ad_machine = C('ADV_MACH');
		$this->assign('ad_mache', $ad_machine);
		
		//聚屏广告第三方媒体
		$poly_screen_media_arr = C('POLY_SCREEN_MEDIA_LIST');
		$this->assign('poly_screen_media_arr',$poly_screen_media_arr);
		return $this->display('addBox');
	}


	/**
	 * 编辑机顶盒
	 * 
	 */
	public function editBox(){	
		$id = I('get.id');
		$hotel_id = I('get.hotel_id','0','intval');
		$roomModel = new RoomModel;
		$boxModel  = new BoxModel;
		$vinfo  = [];
		$vinfo = $boxModel->getRow('*',['id'=>$id]);

		if($hotel_id){
		    $room_list = $roomModel->where("hotel_id='$hotel_id'")->field('id,name')->select();
		}else{
		    $room_list = $roomModel->field('id,name')->select();
		}
		$rooms = array();
		foreach ($room_list as $v){
		    $rooms[$v['id']] = $v['name'];
		}
		//聚屏广告第三方媒体
		$poly_screen_media_arr = C('POLY_SCREEN_MEDIA_LIST');
		$this->assign('poly_screen_media_arr',$poly_screen_media_arr);
		$tpmedia_id_arr = array();
		if($vinfo['tpmedia_id']){
		    $tpmedia_id_arr = explode(',', $vinfo['tpmedia_id']);
		    $this->assign('tpmedia_id_arr',$tpmedia_id_arr);
		}
		
		
		$ad_machine = C('ADV_MACH');
		$this->assign('ad_mache', $ad_machine);

		$this->assign('rooms',$rooms);
		$this->assign('vinfo',$vinfo);
		$this->display('editBox');
	}

    /**
	 * 保存或者更新电视
	 */
	public function doAddTv(){
		$id                = I('post.id');
		$save              = [];
		$save['tv_brand']  = I('post.tv_brand','','trim');
		$save['tv_size']   = I('post.tv_size','','trim');
		$save['flag']      = I('post.flag','','intval');
		$save['state']     = I('post.state','','intval');
		$save['tv_source'] = I('post.tv_source','','trim');
		$save['box_id']    = I('post.box_id','','intval');
		$tvModel = new TvModel;
		$redis = SavorRedis::getInstance();
		$m_box = new \Admin\Model\BoxModel();
		$map = array();
		$where = "1 and b.id=".$save['box_id'];
		$hotel_info = $m_box->isHaveMac('h.id hotel_id', $where);
		
		if($id){
			if($tvModel->editData($id,$save)){
			    $redis->select(12);
			    $cache_key = C('SMALL_TV_LIST').$hotel_info[0]['hotel_id'];
			    $redis->remove($cache_key);

                $all_hotelids = getVsmallHotelList();
                if(in_array($hotel_info[0]['hotel_id'],$all_hotelids)){
                    sendTopicMessage($hotel_info[0]['hotel_id'],4);
                }

				$this->output('更新成功!', 'device/tv');
			}else{
				 $this->output('更新失败!', 'device/doAddTv');
			}		
		}else{	
			if($tvModel->addData($save)){
			    $redis->select(12);
			    $cache_key = C('SMALL_TV_LIST').$hotel_info[0]['hotel_id'];
			    $redis->remove($cache_key);
                $all_hotelids = getVsmallHotelList();
                if(in_array($hotel_info[0]['hotel_id'],$all_hotelids)){
                    sendTopicMessage($hotel_info[0]['hotel_id'],4);
                }
				$this->output('添加成功!', 'device/tv');
			}else{
				 $this->output('添加失败!', 'device/doAddTv');
			}	
		}		
	}


	/**
	 * 保存或者更新机顶盒
	 */
	public function doAddBox(){
		$id                  = I('post.id');
		$save                = [];
		$save['name']        = I('post.name','','trim');
		$save['mac']         = I('post.mac','','trim');
		$save['flag']        = I('post.flag','','intval');
		$save['state']       = I('post.state','','intval');
		$save['switch_time'] = I('post.switch_time','','trim');
		$save['volum']       = I('post.volum','','trim');
		$save['tag']         = I('post.tag','','trim');
		$save['room_id']     = I('post.room_id','','intval');
		$save['adv_mach']    = I('post.adv_machine',0,'intval');
		$save['is_sapp_forscreen'] = I('post.is_sapp_forscreen',0,'intval');
		$save['wifi_name']   = I('post.wifi_name','','trim');
		$save['wifi_password']=I('post.wifi_password','','trim');
		$save['wifi_mac']     =I('post.wifi_mac','','trim');
		$save['is_open_simple']=I('post.is_open_simple',0,'intval');
		$save['is_4g']       = I('post.is_4g',0,'intval');
		$save['box_type']    = I('post.box_type',0,'intval');
		$tpmedia_id_arr      = I('post.tpmedia_id');
		
		if($tpmedia_id_arr){
		    foreach($tpmedia_id_arr as $v){
		        $tpmedia_id_str .=$space . $v;
		        $space = ',';
		    }
		    $save['tpmedia_id'] = $tpmedia_id_str; 
		}else {
		    $save['tpmedia_id'] = '';
		}
		$boxModel = new BoxModel;
		$save['update_time'] = date('Y-m-d H:i:s');
		if($save['mac']){
		    if(!preg_match('/[0-9A-Z]{12}/', $save['mac'])){
		        $this->error('请输入正确的Mac地址');
		    }
		    if($id){
		        $where = " b.mac='".$save['mac']."' and b.flag=0 and b.id !=".$id." and b.state=1";
		    }else {
		        $where = " b.mac='".$save['mac']."' and b.flag=0 and b.state=1";
		    }
		    if($save['flag']==0){
		        $isHaveMac = $boxModel->isHaveMac('h.name as hotel_name,r.name as room_name,b.id as id',$where);
		        if(!empty($isHaveMac)){
		            $str = 'Mac地址存在于'.$isHaveMac[0]['hotel_name'].'酒楼'.$isHaveMac[0]['room_name'].'包间';
		            $this->error($str);
		        }
		    }
		    
		}
        $redis = SavorRedis::getInstance();
		//广告机只考虑是否被删除
		if($id){
			//获取原有酒楼机顶盒数
			$map = array();
			$hextModel = new \Admin\Model\HotelExtModel();
			$wherea = '';
			$wherea = '1 and b.id='.$id;
			$h_box_info = $boxModel->isHaveMac('h.id hoid', $wherea);
			$hotelid = $h_box_info[0]['hoid'];
			$map['hotel_id'] = $hotelid;
			$mfield = 'adplay_num';
			$hex_info = $hextModel->getOneData($mfield, $map);
			$originon_adnum = $hex_info['adplay_num'];
			if($boxModel->editData($id, $save)){
				//查找酒楼下现有所有广告机顶盒
				$hotelModel = new \Admin\Model\HotelModel();
				$mfield = 'count(*) num';
				$map = array();
				$map['sht.id'] = $hotelid;
				$map['sht.flag'] = 0;
				$map['sht.state'] = 1;
				$map['room.state'] = 1;
				$map['room.flag'] = 0;
				$map['box.flag'] = 0;
				$map['box.adv_mach'] = 1;
				$rnum_arr  = $hotelModel->getBoxOrderMacByHid($mfield, $map);
				$rnum = $rnum_arr[0]['num'];
				if($rnum != $originon_adnum) {
					//更新
					$map = array();
					$map['adplay_num'] = $rnum;
					$rp = array();
					$rp['hotel_id'] = $hotelid;
					$hextModel->saveData($map, $rp);
				}
				$redis->select(12);
				$cache_key = C('SMALL_BOX_LIST').$hotelid;
				$redis->remove($cache_key);
                $all_hotelids = getVsmallHotelList();
                if(in_array($hotelid,$all_hotelids)){
                    sendTopicMessage($hotelid,3);
                }
				$this->output('更新成功!', 'device/box');
			}else{
				 $this->output('更新失败!', 'device/doAddBox');
			}		
		}else{
			$save['update_time'] = date('Y-m-d H:i:s');
			$save['create_time'] = date('Y-m-d H:i:s');
            $is_sendtopic = 0;
			if($boxModel->addData($save)){
			    $box_id = $boxModel->getLastInsID();
			    $is_sendtopic = 1;
			    $wherea = '';
			    $wherea = '1 and b.id='.$box_id;
			    $h_box_info = $boxModel->isHaveMac('h.id hoid', $wherea);
			    $hotelid = $h_box_info[0]['hoid'];
				if($save['flag']  != 1 && $save['adv_mach'] == 1 ) {
					//酒楼机顶盒数+1
					
					
					$hextModel = new \Admin\Model\HotelExtModel();
					$hextModel->where('hotel_id='.$hotelid)->setInc('adplay_num', 1);
				}
				$redis->select(12);
				$cache_key = C('SMALL_BOX_LIST').$hotelid;
				$redis->remove($cache_key);
                if($is_sendtopic){
                    $all_hotelids = getVsmallHotelList();
                    if(in_array($hotelid,$all_hotelids)){
                        sendTopicMessage($hotelid,3);
                    }
                }
				$this->output('添加成功!', 'hotel/room');
			}else{
				 $this->output('添加失败!', 'device/doAddBox');
			}	
		}		
	}
}
