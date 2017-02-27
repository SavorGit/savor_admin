<?php
/**
 *@author hongwei
 * @desc 心跳显示列表
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;

class MobileController extends BaseController{


	public function __construct() {
		parent::__construct();
	}


	/**
	 * 分类列表
	 * @return [type] [description]
	 */
	public function report(){

		$mobileModel = new \Admin\Model\MobileStaModel();
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','project_count');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$where = "1=1";
		$name = I('name');
		$type = I('type');
		if($name){
			$this->assign('name',$name);
			$where .= "	AND hotel_name LIKE '%{$name}%'";
		}

		if($type){
			$where .= "	AND type= '{$type}' ";
		}
		$result = $mobileModel->getList($where,$orders,$start,$size);
		$ind = $start;
		foreach ($result['list'] as &$val) {
			$val['indnum'] = ++$ind;
		}

		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('mobilelist');
	}



	public function boxReport(){

		$boxstaModel = new \Admin\Model\BoxStaModel();
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','project_count');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$where = "1=1";
		$name = I('name');
		$type = I('type');
		if($name){
			$this->assign('name',$name);
			$where .= "	AND hotel_name LIKE '%{$name}%'";
		}

		if($type){
			$where .= "	AND type= '{$type}' ";
		}
		$result = $boxstaModel->getList($where,$orders,$start,$size);
		$ind = $start;
		foreach ($result['list'] as &$val) {
			$val['indnum'] = ++$ind;
		}

		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('boxlist');
	}




}
