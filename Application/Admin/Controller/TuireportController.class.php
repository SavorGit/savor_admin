<?php
/**
 *@author hongwei
 * @desc 心跳显示列表
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;

class TuireportController extends BaseController{

	public function __construct() {
		parent::__construct();
	}

	public function mobile_download(){
		$dtype = 2;
		$this->assign('dtype',$dtype);
		$table = 'first_mobile_download';
		$starttime = I('post.starttimefmd','');
		$endtime = I('post.endtimefmd','');
		$tuiModel =  new \Admin\Model\TuiRpModel($table);
		$size   = I('numPerPagefmd',50);//显示每页记录数
		$start = I('pageNum',1);
		$order = I('_orderfmd','bct');
		$sort = I('_sortfmd','asc');
		if(empty($size)){
			$size = 50;
		}
		if(empty($start)){
			$start = 1;
		}
		if(empty($order)){
			$order = bct;
		}

		if(empty($sort)){
			$sort = asc;
		}
		$this->assign('numPerPagefmd',$size);
		$this->assign('pageNumfmd',$start);
		$this->assign('_orderfmd',$order);
		$this->assign('_sortfmd',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$hname = I('hotelnamefmd','');
		$where = "1=1 ";
		if($hname){
			$this->assign('hotelnamefmd',$hname);
			$where .= "	AND hotel_name LIKE '%{$hname}%'";
		}
		$where = "1=1";
		$group =  'hotel_id';
		$field = 'sum(`box_count`) as bct,sum(`download_count`) as   dct,hotel_name,time';

		if($starttime){
			$this->assign('s_timefmd',$starttime);
			$where .= "	AND time >= '{$starttime}'";
		}
		if($endtime){
			$this->assign('e_timefmd',$endtime);
			$where .= "	AND time <=  '{$endtime}'";
		}

		$result = $tuiModel->getList($where,$field,$group,$orders,$start,$size);
		//dump($result);
		$ind = $start;
		foreach ($result['list'] as &$val) {
			$val['indnum'] = ++$ind;
		}
		$this->assign('listfmd', $result['list']);
		$this->assign('pagefmd',  $result['page']);
		$this->display('recolist');

	}


	/**
	 * 机顶盒失联列表
	 * @access public
	 * @param $dtype 1:当年，2当月，3当日(即是昨天的)，4指定日期,5所有次数
	 * @return [type] [description]
	 */
	public function rplist(){

		$dtype = 1;
		$table = 'first_mobile_interaction_final';
		$starttime = I('post.starttime','');
		$endtime = I('post.endtime','');

		$this->assign('dtype',$dtype);
		$tuiModel =  new \Admin\Model\TuiRpModel($table);
		$size   = I('numPerPage',50);//显示每页记录数
		$start = I('pageNum',1);
		$order = I('_order','count');
		$sort = I('_sort','asc');

		if(empty($size)){
			$size = 50;
		}
		if(empty($start)){
			$start = 1;
		}
		if(empty($order)){
			$order = 'count';
		}

		if(empty($sort)){
			$sort = 'asc';
		}
		$orders = $order.' '.$sort;
		$this->assign('numPerPage',$size);
		$this->assign('pageNum',$start);
		$this->assign('_order',$order);
		$this->assign('_sort',$sort);
		$start  = ( $start-1 ) * $size;
		$where = "1=1";
		$hname = I('hotelname','');
		$group =  'hotel_name,box_name';
		$field = 'sum(`count`) count,hotel_name,box_name,date_time';
		if($hname){
			$this->assign('hotelname',$hname);
			$where .= "	AND hotel_name LIKE '%{$hname}%'";
		}
		if($starttime){
			$this->assign('s_time',$starttime);
			$where .= "	AND date_time >= '{$starttime}'";
		}
		if($endtime){
			$this->assign('e_time',$endtime);
			$where .= "	AND date_time <=  '{$endtime}'";
		}
		//var_dump($where,$field,$group,$orders,$start,$size);
		$result = $tuiModel->getList($where,$field,$group,$orders,$start,$size);
		$ind = $start;
		foreach ($result['list'] as &$val) {
			if(empty($val['project_count'])){
				$val['project_count'] = 0;
			}
			if(empty($val['demand_count'])){
				$val['demand_count'] = 0;
			}
			$val['indnum'] = ++$ind;
		}
		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('recolist');
	}





	/*
	 * 修改状态
	 */

	public function changestate(){
		$cid = I('post.cid');
		$save = array();
		$save['state'] = I('post.state');
		$catModel = new CategoModel;
		$res_save = $catModel->where('id='.$cid)->save($save);
		if($res_save){
			echo 1;
		} else {
			echo 0;
		}
	}

	/**
	 * 保存或者更新分类信息
	 * @return [type] [description]
	 */
	public function doAddCat(){
		$catModel = new CategoModel;
		$id                  = I('post.id');
		$save                = [];
		$save['name']        = I('post.cat_name','','trim');
		$save['sort_num']    = I('post.sort','','intval');

		$save['update_time'] = date('Y-m-d H:i:s');
		$mediaid = I('post.media_id');
		$mediaModel = new \Admin\Model\MediaModel();
		//$mediaid = 11;
		$oss_addr = $mediaModel->find($mediaid);
		$oss_addr = $oss_addr['oss_addr'];
		$save['img_url'] = $oss_addr;
		if($id){
			$res_save = $catModel->where('id='.$id)->save($save);
			if($res_save){
				$this->output('操作成功!', 'release/category');
			}else{
				$this->output('操作失败!', 'release/doAddCat');
			}
		}else{
			$save['state']    =  0;
			$save['create_time'] = date('Y-m-d H:i:s');
			//刷新页面，关闭当前
			$res_save = $catModel->add($save);
			if($res_save){
			    $this->output('添加分类成功!', 'release/category');
			}else{
				$this->output('操作失败!', 'release/doAddCat');
			}
		}
	}

}
