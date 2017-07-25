<?php
/**
 *@author hongwei
 * @desc 心跳显示列表
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;

class TestappscreenController extends BaseController{

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
		$appscreenModel =  new \Admin\Model\TestappscreenRpModel();
		$hotelModel = new \Admin\Model\HotelModel();
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','timestamps');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$source_type = I('source_type','');

		$where = "1=1";
		$hname = I('hotelname','');
		if($hname){
			$where .="	AND hotelname = '{$source_type}'";
			$this->assign('sot',$source_type);
		}
		if($starttime){
			$this->assign('s_time',$starttime);
			$sttime = strtotime($starttime);
			$where .= "	AND substring(`timestamps`,0,-3) >= '{$starttime}'";
		}
		if($endtime){
			$etime = strtotime($endtime);
			$this->assign('e_time',$endtime);
			$where .= "	AND substring(`timestamps`,0,-3) <=  '{$etime}'";
		}
		$result = $appscreenModel->getList($where,$orders,$start,$size);
		$so_type = C('source_type');
		$ind = $start;
		foreach ($result['list'] as &$val) {
			$val['indnum'] = ++$ind;
			$val['addtime'] = date("Y-m-d",substr($val['timestamps'],0,-3));
		}

		$this->assign('sce_type', $so_type);
		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('screenlist');
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
