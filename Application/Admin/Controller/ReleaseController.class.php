<?php
/**
 *@author hongwei
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Admin\Model\CategoModel;
use Admin\Model\ArticleModel;
use Common\Lib\xhprof\xhprof_lib\utils\xhprof_runs;

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
		$articleModel =  new ArticleModel();
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
		foreach($result['list'] as $key=>$v){
		    $result['list'][$key]['counts'] = $articleModel->getCountByCatid($v['id']);   
		}
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
		$cid = I('request.cid');
		$save = array();
		$save['state'] = I('request.state');
		$catModel = new CategoModel;
		$res_save = $catModel->where('id='.$cid)->save($save);
		
		if($res_save){
		    $message = '更新成功!';
		    $url = 'release/category';
		} else {
			$message = '更新失败!';
	        $url = 'release/category';
		}
		$this->output($message, $url,2);
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
		
		$mediaModel = new \Admin\Model\MediaModel();
		if($id){
		    if($save['name']){
		        $map['name'] = array('like',$save['name']);
		        $map['id'] = array('neq',$id);
		        $is_have = $catModel->where($map)->find();
		        if($is_have){
		            $this->error('该分类名称已经存在');
		        }
		    }
			$res_save = $catModel->where('id='.$id)->save($save);
			if($res_save){
				$this->output('操作成功!', 'release/category');
			}else{
				$this->output('操作失败!', 'release/doAddCat');
			}
		}else{
		    if($save['name']){
		        $map['name'] = array('like',$save['name']);
		        $is_have = $catModel->where($map)->find();
		        if($is_have){
		            $this->error('该分类名称已经存在');
		        }
		    }
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
    /**
     * @desc 删除分类
     */
	public function delcat(){
	    $category_id = I('get.id', 0, 'int');
	    
	    //查找是否在首页内容中引用
	    if($category_id) {
	        $articleModel = new ArticleModel();
	        $article_info = $articleModel->where('category_id='.$category_id)->find();
	        
	        if(!empty($article_info)){
	            $this->error('该分类下有文章不可删除!');
	        }else{
	            $catModel = new CategoModel();
	            $result = $catModel->delData($category_id);
	            if($result) {
	                $this->output('删除成功', 'release/category',2);
	            } else {
	                $this->error('删除失败!');
	            }
	        }
	    }else {
	        $this->error('删除失败,缺少参数!');
	    }
	}
	/**
	 * @desc 排序
	 */
	public function addsort(){
	    $catModel = new CategoModel();
	    $order = I('_order','sort_num');
	    $this->assign('_order',$order);
	    $sort = I('_sort','asc');
	    $this->assign('_sort',$sort);
	    $orders = $order.' '.$sort;
	    $where = "1=1";
	    $result = $catModel->where($where)->order($order)->select();
	    $this->assign('list', $result);
	    $this->display('homesort');
	}
	public function doSort(){
	
	    $sort_str= I('post.soar');
	    $sort_arr = explode(',', $sort_str);
	    $sql = 'update savor_mb_category  SET sort_num = CASE id ';
	    foreach($sort_arr as $k=>$v){
	        $k = $k+1;
	        $sql .= ' WHEN '.$v.' THEN '.$k;
	    }
	    $sql .= ' END WHERE id IN ('.$sort_str.')';
	    $mbHome = new \Admin\Model\CategoModel();
	    $bool = $mbHome->execute($sql);
	    if($bool){
	        $this->output('操作成功','release/category');
	
	    } else{
	        $this->output('未改顺序','release/category',1,0);
	    }
	
	    /*    SET display_order = CASE id
	     WHEN 1 THEN 3
	     WHEN 2 THEN 4
	     WHEN 3 THEN 5
	     END
	     WHERE id IN (1,2,3)*/
	
	}
}
