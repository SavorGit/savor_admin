<?php
/**
 *机顶盒管理
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Admin\Model\BoxModel;
use Admin\Model\RoomModel;
use Admin\Model\TvModel;

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
            $tvModel = new \Admin\Model\TvModel();
            foreach ($result['list'] as $k=>$v){
                $box_id = $v['id'];
                $tv_num = $tvModel->where("box_id='$box_id'")->count();
                $result['list'][$k]['tv_num'] = $tv_num;
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
		$vinfo['volum'] = 50;
		$this->assign('vinfo', $vinfo);
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
		$this->assign('rooms',$rooms);
		$this->assign('vinfo',$vinfo);
		$this->display('editBox');
	}

    /**
	 * 保存或者更新机顶盒
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
		if($id){
			if($tvModel->editData($id,$save)){
				$this->output('更新成功!', 'device/box');
			}else{
				 $this->output('更新失败!', 'device/doAddTv');
			}		
		}else{	
			if($tvModel->addData($save)){
				$this->output('添加成功!', 'device/box');
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
		$save['room_id']     = I('post.room_id','','intval');
		$boxModel = new BoxModel;
		$hotelModel = new \Admin\Model\HotelModel();
		$roomModel = new \Admin\Model\RoomModel();
		//MAC地址:[机顶盒后四位] 在 [酒楼名称]-[包间名称]下，请重新输入
		$temp = $boxModel->getRow('id,mac,room_id',['mac'=>$save['mac']]);
		//var_dump($temp);
		if($temp){
			$info = $boxModel->find($temp['id']);
			$roid = $info['room_id'];
			$ro_arr = $roomModel->find($roid);
			$roomname = $ro_arr['name'];
			$h_id = $ro_arr['hotel_id'];
			$h_arr = $hotelModel->find($h_id);
			$hname = $h_arr['name'];
			$str = 'MAC地址：'.substr($save['mac'],-4).'在 ['.$hname.']-['.$roomname.']下重复，请重新输入';
			if ($id) {
				if($temp['id'] != $id) {
					$this->error($str);
				}
			} else{
				$this->error($str);
			}

		}

		if($id){
			if($boxModel->editData($id, $save)){
				$this->output('更新成功!', 'device/box');
			}else{
				 $this->output('更新失败!', 'device/doAddBox');
			}		
		}else{	
			if($boxModel->addData($save)){
				$this->output('添加成功!', 'hotel/room');
			}else{
				 $this->output('添加失败!', 'device/doAddBox');
			}	
		}		
	}
}
