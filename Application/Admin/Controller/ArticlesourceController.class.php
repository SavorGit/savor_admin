<?php
/**
 * @desc 来源管理
 * @author zhang.yingtao
 * @since  2017-06-15
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class ArticlesourceController extends BaseController{
    var $oss_host ;
    public function __construct() {
		parent::__construct();
		$this->oss_host = get_oss_host();
	}
	/**
	 * @desc 列表
	 */
	public function index(){
	    $size   = I('numPerPage',50);      //显示每页记录数
	    $this->assign('numPerPage',$size);
	    $start = I('pageNum',1);           //当前页数
	    $this->assign('pageNum',$start);
		$order = I('_order',' convert(a.`name` using gbk) ');
	    $this->assign('_order',$order);
	    $sort = I('_sort','asc');         //排序类型
	    $this->assign('_sort',$sort);
	    $orders = $order.' '.$sort;
	    $start  = ( $start-1 ) * $size;
	    
	    $where =" a.status=1";
	    $name = I('name','','trim');       //搜索字段：来源名称
	    if($name){
	        $this->assign('name',$name);
	        $where .= " and a.name like '%$name%'";
	    }
	    $m_article_source = new \Admin\Model\ArticleSourceModel();
	    $result = $m_article_source->getList($where , $orders, $start, $size);
	    
	    $this->assign('list',$result['list']);
	    $this->assign('page',  $result['page']);
		$this->display('index');
	}
	/**
	 * @desc  增加文章来源
	 */
	public function add(){
	    
	    $this->display('add');
	}
	/**
	 * @desc 保存增加文章来源
	 */
	public function doadd(){
	    if(IS_POST){
	        $name =  I('name','','trim');
	        $media_id = I('media_id','0','intval');
	        if(empty($name)){
	            $this->error('来源名称必填');
	        }
	        if(empty($media_id)){
	            $this->error('封面图必填');
	        }
	        $m_article_source = new \Admin\Model\ArticleSourceModel();
	        $info = $m_article_source->getWhere('id',array('name'=>$name));
	        if(!empty($info)){
	            $this->error('该名称已经存在');
	        }
	        $introduction = I('introduction');
	        $user = session('sysUserInfo');
	        //print_r($user);exit;
	        $data = array();
	        $data['name'] = $name;
	        $data['logo'] = $media_id;
	        $data['introduction'] = $introduction;
	        $data['add_user_id']  = $user['id']; 
	        $data['add_time']     = date('Y-m-d H:i:s');
	        
	        
	        $ret = $m_article_source->addInfo($data);
	        if($ret){
	            $this->output('新增成功', 'articlesource/index', 1);
	        }else {
	            $this->error('新增失败');
	        }
	    }
	}
	/**
	 * @desc 编辑
	 */
	public function edit(){
	    
	    $id = I('id',0,'intval');
	    if(empty($id)){
	        $this->error('操作有误,请刷新页面');
	    }
	    $m_article_source = new \Admin\Model\ArticleSourceModel();
	    $vinfo = $m_article_source->getInfoById($id);
	    $vinfo['oss_addr'] = $this->oss_host.$vinfo['oss_addr'];
	    $this->assign('vinfo',$vinfo);
	    $this->display('edit');
	}
	/**
	 * @desc 保存编辑
	 */
	public function doedit(){
	    if(IS_POST){
	        $id = I('id','0','intval');
	        if(empty($id)){
	            $this->error('操作非法');
	        }
	        $name =  I('name','','trim');
	        $media_id = I('media_id','0','intval');
	        if(empty($name)){
	            $this->error('来源名称必填');
	        }
	        if(empty($media_id)){
	            $this->error('封面图必填');
	        }
	        $m_article_source = new \Admin\Model\ArticleSourceModel();
	        $map= array();
	        $map['name'] = $name;
	        $map['id'] = array('neq',$id);
	        $info = $m_article_source->getWhere('name',$map);
	        if(!empty($info)){
	            $this->error('该名称已经存在');
	        }
	        $introduction = I('introduction');
	        $user = session('sysUserInfo');
	        //print_r($user);exit;
	        $data = array();
	        $data['name'] = $name;
	        $data['logo'] = $media_id;
	        $data['introduction'] = $introduction;
	        $data['edit_user_id']  = $user['id']; 
	        $data['edit_time']     = date('Y-m-d H:i:s');
	        $where['id'] = $id;
	        
	        $ret = $m_article_source->updateInfo($where , $data);
	        if($ret){
	            $this->output('编辑成功', 'articlesource/index', '1');
	        }else {
	            $this->error('编辑失败');
	        }
	    }
	}
	/**
	 * @desc 删除来源
	 */
	public function delete(){
	    $id = I('get.id','0','intval');
	    if(empty($id)){
	        $this->error('非法操作');
	    }
	    $m_article =  new \Admin\Model\ArticleModel();
	    $num = $m_article->countNumBySourceId($id);
	    
	    if(!empty($num) && $num>0){
	        $this->error('该标签文章有引用，不能删除!');
	    }
	    $m_article_source = new \Admin\Model\ArticleSourceModel();
	    $ret = $m_article_source->deleteInfoById($id);
	    if($ret){
	        $this->output('删除成功', 'articlesource/index', '2');
	    }else {
	        $this->error('删除失败');
	    }
	}
}
