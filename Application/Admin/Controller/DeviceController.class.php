<?php
/**
 *@author hongwei
 *
 *
 * 
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Admin\Model\BoxModel;
use Admin\Model\RoomModel;
use Admin\Model\TvModel;

class DeviceController extends BaseController 
{

	



    /**
     * 机顶盒列表
     * 
     * @return [type] [description]
     */
    public function box()
    {	

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

        if($name)
        {
        	$this->assign('name',$name);
        	$where .= "	AND name LIKE '%{$name}%'";
        }

        $result = $boxModel->getList($where,$orders,$start,$size);

        $result['list'] = $boxModel->roomIdToRoomName($result['list']);
        
   		$this->assign('list', $result['list']);
   	    $this->assign('page',  $result['page']);
        $this->display('box');

    }//End Function




    /**
     * 电视管理列表
     * @return [type] [description]
     */
    public function tv()
    {
    	$tvModel = new TvModel;
    	

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

        if($name)
        {
        	$this->assign('name',$name);
        	$where .= "	AND name LIKE '%{$name}%'";
        }

        $result = $tvModel->getList($where,$orders,$start,$size);

        $result['list'] = $tvModel->boxIdToBoxName($result['list']);
        //print_r($result);die;
   		$this->assign('list', $result['list']);
   	    $this->assign('page',  $result['page']);
        $this->display('tv');





    }//End Function





    /**
	 * 新增tv
	 * 
	 */
	public function addtv()
	{	
		$id = I('get.id');

		$boxModel = new BoxModel;
		$tvModel =  new TvModel;

		if($id)
		{
			$vinfo = $tvModel->where('id='.$id)->find();

			$temp = $boxModel->getRow('name',['id'=>$vinfo['box_id']]);
			
			$vinfo['box_name'] = $temp['name'];

			$this->assign('vinfo',$vinfo);

		}
			
		return $this->display('addtv');

	}





	/**
	 * 新增机顶盒
	 * 
	 */
	public function addBox()
	{	
		$room_id = I('get.room_id');

		
		$roomModel = new RoomModel;

		$temp = $roomModel->getRow('name',['id'=>$room_id]);
		

		$this->assign('room_name',$temp['name']);

		$this->assign('room_id',$room_id);
			
		return $this->display('addbox');

	}


	/**
	 * 编辑机顶盒
	 * 
	 */
	public function editBox()
	{	
		$id = I('get.id');

		$roomModel = new RoomModel;

		$boxModel  = new BoxModel;

		$vinfo  = [];

		$vinfo = $boxModel->getRow('*',['id'=>$id]);

		
		$temp = $roomModel->getRow('name',['id'=>$vinfo['room_id']]);
		
		$vinfo['room_name'] = $temp['name'];

		$this->assign('vinfo',$vinfo);
		
		return $this->display('editBox');

	}


		/**
	 * 保存或者更新机顶盒
	 * 
	 * @return [type] [description]
	 */
	public function doAddTv()
	{
		$id                = I('post.id');
		$save              = [];
		$save['tv_brand']  = I('post.tv_brand','','trim');
		$save['tv_size']   = I('post.tv_size','','trim');
		$save['flag']      = I('post.flag','','intval');
		$save['state']     = I('post.state','','intval');
		$save['tv_source'] = I('post.tv_source','','trim');
		$save['box_id']    = I('post.box_id','','intval');
		
		
		$tvModel = new TvModel;

		if($id)
		{
			if($tvModel->where('id='.$id)->save($save))
			{
				$this->output('更新成功!', 'device/addTv');
			}
			else
			{
				 $this->output('更新失败!', 'device/doAddTv');
			}		
		}
		else
		{	
			if($tvModel->add($save))
			{
				$this->output('添加成功!', 'device/addTv');
			}
			else
			{
				 $this->output('添加失败!', 'device/doAddTv');
			}	

		}		


	}//End Function




	/**
	 * 保存或者更新机顶盒
	 * 
	 * @return [type] [description]
	 */
	public function doAddBox()
	{
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

		if($id)
		{
			if($boxModel->where('id='.$id)->save($save))
			{
				$this->output('更新成功!', 'device/addBox');
			}
			else
			{
				 $this->output('更新失败!', 'device/doAddBox');
			}		
		}
		else
		{	
			if($boxModel->add($save))
			{
				$this->output('添加成功!', 'device/addBox');
			}
			else
			{
				 $this->output('添加失败!', 'device/doAddBox');
			}	

		}		


	}//End Function





}//End Class
