<?php
/**
 *
 *
 *
 * 
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Admin\Model\HotelModel;

class HotelController extends BaseController 
{

	 public function __construct() {
        parent::__construct();
    }


    /**
     * 
     * 
     * @return [type] [description]
     */
	public function manager()
	{	
		$hotelModel = new HotelModel;

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

        $result = $hotelModel->getList($where,$orders,$start,$size);

   		$this->assign('list', $result['list']);
   	    $this->assign('page',  $result['page']);
        $this->display('index');

	}//End Function




	/**
	 * 新增
	 * 
	 */
	public function add()
	{	
		$id = I('get.id');

		$hotelModel = new HotelModel;

		if($id)
		{
			$vinfo = $hotelModel->where('id='.$id)->find();
			$this->assign('vinfo',$vinfo);

		}
			
		return $this->display('add');

	}



	/**
	 * 保存或者更新
	 * 
	 * @return [type] [description]
	 */
	public function doAdd()
	{
		$id                          = I('post.id');
		$save                        = [];
		$save['name']                = I('post.name','','trim');
		$save['addr']                = I('post.addr','','trim');
		$save['contactor']           = I('post.contactor','','trim');
		$save['tel']                 = I('post.tel','','trim');
		$save['level']               = I('post.level','','trim');
		$save['iskey']               = I('post.iskey','','intval');
		$save['install_date']        = I('post.install_date','','trim');
		$save['level']               = I('post.level','','trim');
		$save['state']               = I('post.state','','intval');
		$save['state_change_reason'] = I('post.state_change_reason','','trim');
		$save['remark']              = I('post.remark','','trim');
		$save['flag']                = I('post.flag','','intval');
		$save['update_time']         = date('Y-m-d H:i:s');
		$save['mobile']              = I('post.mobile','','trim');
		
		$hotelModel = new HotelModel;

		if($id)
		{
			if($hotelModel->where('id='.$id)->save($save))
			{
				$this->output('操作成功!', 'hotel/manager');
			}
			else
			{
				 $this->output('操作失败!', 'hotel/add');
			}		
		}
		else
		{	
			
			$save['create_time'] = date('Y-m-d H:i:s');
			if($hotelModel->add($save))
			{
				$this->output('操作成功!', 'hotel/manager');
			}
			else
			{
				 $this->output('操作失败!', 'hotel/add');
			}	

		}		


	}//End Function





}//End Class
