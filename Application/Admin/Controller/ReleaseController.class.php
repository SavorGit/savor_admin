<?php
/**
 *@author hongwei
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Admin\Model\CategoModel;

class ReleaseController extends BaseController{

	public $path = 'category/img';
	public $oss_host = '';
	public function __construct() {
		parent::__construct();
		$this->oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
	}


	/**
	 * 分类列表
	 * @return [type] [description]
	 */
	public function category(){
		$catModel = new CategoModel;
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','sort_num');
		$this->assign('_order',$order);
		$sort = I('_sort','asc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$where = "1=1";
		$name = I('name');
		if($name){
			$this->assign('name',$name);
			$where .= "	AND name LIKE '%{$name}%'";
		}
		$result = $catModel->getList($where,$orders,$start,$size);
		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('cate');
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
			$vinfo['oss_addr'] = $vinfo['img_url'];
			$this->assign('vinfo',$vinfo);
		}
		return $this->display('addCat');
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
		$save['state']    = I('post.state','0','intval');
		$save['update_time'] = date('Y-m-d H:i:s');
		$mediaid = I('post.media_id');
		$mediaModel = new \Admin\Model\MediaModel();
		//$mediaid = 11;
		$oss_addr = $mediaModel->find($mediaid);
		$oss_addr = $oss_addr['oss_addr'];
		$image_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
		$oss_addr = $image_host.$oss_addr;
		$save['img_url'] = $oss_addr;
		if($id){
		    $res_save = $catModel->where('id='.$id)->save($save);
			if($res_save){
				return $this->output('操作成功!', 'release/doAddCat');
			}else{
				return $this->output('操作失败!', 'release/doAddCat');
			}
		}else{
			$save['create_time'] = date('Y-m-d H:i:s');
            //刷新页面，关闭当前
            $res_save = $catModel->add($save);
			if($res_save){
				return $this->output('添加分类成功!', 'release/category', 1);
			}else{
				return $this->output('操作失败!', 'release/doAddCat');
			}
		}
	}

}
