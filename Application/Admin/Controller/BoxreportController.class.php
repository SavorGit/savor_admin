<?php
/**
 *@author hongwei
 * @desc 心跳显示列表
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;

class BoxreportController extends BaseController{

	public function __construct() {
		parent::__construct();
	}


	/**
	 * 机顶盒失联列表
	 * @access public
	 * @param $dtype 1:当年，2当月，3当日(即是昨天的)，4指定日期
	 * @return [type] [description]
	 */
	public function rplist(){
		$dtype = I('post.dtyp','');
		if ($dtype) {
			if ( $dtype == 1) {
				$table = 'heart_count_year';
				$time = date("Y",time());
			} else if ($dtype == 2) {
				$table = 'heart_count_month';
				$time = date("Y-m",time());
			} else if ($dtype == 3) {
				$table = 'heart_count_day';
				$time = date("Y-m-d",time()-86400);

			} else if ($dtype == 4) {
				$table = 'heart_count';
				$starttime = I('post.starttime','');
				$endtime = I('post.endtime','');
			}
		} else {
			$dtype = 3;
			$table = 'heart_count_day';
			$time = date("Y-m-d",time()-86400);
		}
		$this->assign('dtype',$dtype);
		$boxreModel =  new \Admin\Model\BoxReportModel($table);
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','box_id');
		$this->assign('_order',$order);
		$sort = I('_sort','asc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$where = "1=1";
		$hname = I('hotelname','');
		if($hname){
			$this->assign('hotelname',$hname);
			$where .= "	AND hotel_name LIKE '%{$hname}%'";
		}
		if($dtype){
			if($dtype == 4) {
				if($starttime){
					$this->assign('s_time',$starttime);
					$where .= "	AND time >= '{$starttime}'";
				}
				if($endtime){
					$this->assign('e_time',$endtime);
					$where .= "	AND time <=  '{$endtime}'";
				}

			} else {
				$where .= "	AND time= '{$time}' ";
			}

		}
		$result = $boxreModel->getList($where,$orders,$start,$size);
		session('boxlostreport',$result['list']);
		$ind = $start;
		foreach ($result['list'] as &$val) {
			$val['indnum'] = ++$ind;
		}
		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('boxlist');
	}


	/**
	 * 新增分类
	 *
	 */
	public function addCate(){
		$id = I('get.id');
		$catModel = new CategoModel;
		if($id){
			$vinfo = $catModel->find($id);
			$image_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
			$vinfo['oss_addr'] = $image_host.$vinfo['img_url'];
			$this->assign('vinfo',$vinfo);
		}
		return $this->display('addCat');
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
