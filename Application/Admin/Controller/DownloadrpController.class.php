<?php
/**
 *@author hongwei
 * @desc 心跳显示列表
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;

class DownloadrpController extends BaseController{

	public function __construct() {
		parent::__construct();
	}


	/**
	 * 机顶盒失联列表
	 * @access public
	 * @param $dtype 1:当年，2当月，3当日(即是昨天的)，4指定日期,5所有次数
	 * @return [type] [description]
	 */
	public function rplist(){

		$starttime = I('post.starttime','');
		$endtime = I('post.endtime','');
		$downloadModel =  new \Admin\Model\DownloadRpModel();
		$hotelModel = new \Admin\Model\HotelModel();
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','add_time');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$source_type = I('source_type','');

		$where = "1=1";
		$hname = I('hotelname','');
		if($source_type){
			$where .="	AND source_type = '{$source_type}'";
			$this->assign('sot',$source_type);
		}
		if($starttime){
			$this->assign('s_time',$starttime);
			$where .= "	AND add_time >= '{$starttime}'";
		}
		if($endtime){
			$this->assign('e_time',$endtime);
			$where .= "	AND add_time <=  '{$endtime}'";
		}
		$result = $downloadModel->getList($where,$orders,$start,$size);
		$so_type = C('source_type');
		$ind = $start;
		foreach ($result['list'] as &$val) {
			$rs = $hotelModel->find($val['hotelid']);
			$val['hotelname'] = $rs['name'];
			$val['indnum'] = ++$ind;
		}

		$this->assign('sce_type', $so_type);
		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('screenlist');
	}
}
