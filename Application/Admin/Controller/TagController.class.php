<?php
/**
 * Project savor_admin
 *
 * @author baiyutao <------@gmail.com> 2017-06-12
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Common\Lib\Aliyun;
use Think\Model;

/**
 * Class TagController
 * 标签控制器
 * @package Admin\Controller
 */
class TagController extends BaseController{
    private $oss_host = '';
    public function __construct(){
        parent::__construct();
        $this->oss_host = get_oss_host();
    }

	/*
	 * 标签列表
	 * @access public
	 * @param
	 * @return mixed
	 */
	public function rplist(){
		$tagModel = new \Admin\Model\TagListModel();
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','convert(tagname using gbk)');
		$this->assign('_order',$order);
		$sort = I('_sort','asc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$where = "1=1";
		$tagname = I('tagname','','trim');

		if($tagname){
			$this->assign('tagname',$tagname);
			$where .= "	AND tagname LIKE '%{$tagname}%'";
		}
		$where .= " AND flag = 1";
		$result = $tagModel->getList($where,$orders,$start,$size);
		$this->assign('datalist', $result['list']);
	   $this->assign('page',  $result['page']);
	    $this->display('rplist');
		//$this->display('boxliuyang');
	}




	/**
	 * 新增分类
	 *
	 */
	public function addtag(){

		return $this->display('addtag');
	}

	public function doAddAjaxTag(){
		$tagModel = new \Admin\Model\TagListModel();
		$save                = [];
		$save['tagname']        = I('post.tagname','','trim');
		$save['flag']    =  1;
		if($save['tagname']){
			$is_have = $tagModel->where($save)->find();
			if($is_have){
				$result = array('code'=>0,'err_msg'=>'该标签名称已经存在');

			}
			if(!preg_match('/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u',$save['tagname'], $result)){
				$result = array('code'=>0,'err_msg'=>'只可输入数字、字母、汉字');
			}
			if(mb_strlen($save['tagname'])<2 || mb_strlen($save['tagname'])>15) {
				$result = array('code'=>0,'err_msg'=>'标签长度最小为2最大为6');
			}
			echo json_encode($result);
			die;
		}
		$save['update_time'] = date('Y-m-d H:i:s');
		$save['create_time'] = date('Y-m-d H:i:s');
		//刷新页面，关闭当前
		$res_save = $tagModel->addData($save);
		if($res_save){
			$result = array('code'=>1,'msg'=>'操作成功');
		}else{
			$result = array('code'=>0,'err_msg'=>'操作失败');
		}
		echo json_encode($result);
	}


	/**
	 * 保存或者更新分类信息
	 * @return [type] [description]
	 */
	public function doAddTag(){
		$tagModel = new \Admin\Model\TagListModel();
		$save                = [];
		$save['tagname']        = I('post.tag_name','','trim');
		$save['flag']    =  1;
		if($save['tagname']){
				$is_have = $tagModel->where($save)->find();
				if($is_have){
					$this->error('该标签名称已经存在');
				}
				if(!preg_match('/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u',$save['tagname'], $result)){
					$this->error('操作失败，只可输入数字、字母、汉字');
				}
				if(mb_strlen($save['tagname'])<2 || mb_strlen($save['tagname'])>15) {
					$this->error('标签长度最小为2最大为6');
				}
		}else{
			$this->error('该标签不可为空');
		}
		$save['update_time'] = date('Y-m-d H:i:s');
		$save['create_time'] = date('Y-m-d H:i:s');
		//刷新页面，关闭当前
		$res_save = $tagModel->addData($save);
		if($res_save){
			$this->output('添加标签成功!', 'tag/rplist');
		}else{
			$this->error('操作失败!');
		}

	}


	public function getajaxpage(){
		$tagModel = new \Admin\Model\TagListModel();
		$size   = I('numPerPage',20);//显示每页记录数
		$start = I('pageNum',1);
		$order = I('_order','convert(tagname using gbk)');
		$sort = I('_sort','asc');
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$where = "1=1";
		$tagname = I('fatagname','','trim');

		if($tagname){
			$where .= "	AND tagname LIKE '%{$tagname}%'";
		}
		$where .= " AND flag = 1";
		$field = 'id,tagname';
		$result = $tagModel->getList($where,$orders,$start,$size,$field);


		 $result['page'] = $tagModel->getPageCount($where);
		$result['page'] = ceil($result['page']/$size);
		echo json_encode($result);
	}

	public function articleTagList(){
		$tagid = I('tagid', 0, 'int');
		$tagname = I('tagname', '', 'trim');
		$tagModel = new \Admin\Model\TagModel();
		$taglistModel = new \Admin\Model\TagListModel();
		if($tagid){
			$where = 'tagid='.$tagid;
			$field = 'article_id';
			$res = $tagModel->getWhereData($where,$field);
		}
		if ($res) {
			$taglen = count($res);
			$dap['num'] = $taglen;
			$where = 'id='.$tagid;
			$taglistModel->saveData($dap,$where);
			$where = "1=1";
			$art_str = ' AND id in (';
			foreach($res as $v){
				$art_str .=$v['article_id'].',';
			}
			$art_str = substr($art_str,0,-1);
			$art_str .= ')';
			$where .= $art_str;
			$artModel = new \Admin\Model\ArticleModel();
			$size   = I('numPerPage',50);//显示每页记录数
			$this->assign('numPerPage',$size);
			$start = I('pageNum',1);
			$this->assign('pageNum',$start);
			$order = I('_order','update_time');
			$this->assign('_order',$order);
			$sort = I('_sort','desc');
			$this->assign('_sort',$sort);
			$orders = $order.' '.$sort;
			$start  = ( $start-1 ) * $size;
			$result = $artModel->getList($where,$orders,$start,$size);
			$result['list'] = $artModel->changeCatname($result['list']);
		}
		$this->assign('tagarticlename', $tagname);
		$this->assign('taglistid', $tagid);
		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('tagarticle');
	}


	public function deltag() {
		$gid = I('get.tagid', 0, 'int');
		if($gid) {
			//删除tag表对应文章
			$reas = true;
			$tagModel = new \Admin\Model\TagModel();
			//$tranDb = new Model();
			//$tranDb->startTrans();
			$res = $tagModel->where('tagid='.$gid)->find();
			if($res) {
				$this->error('有文章引用标签不可删除');
				//$reas = $tagModel->delData($gid);
			}

			$tagListModel = new \Admin\Model\TagListModel();
			$save['flag'] = 0;
			$where =  'id='.$gid;
			//更新taglist表
			$result = $tagListModel -> saveData($save, $where);
			if($result) {
				$this->output('删除成功', 'tag/rplist',2);
			} else {
				$this->error('删除失败');
			}
		} else {
			$this->error('删除失败,缺少参数!');
		}
	}


}

