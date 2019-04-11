<?php
namespace Admin\Controller;
/**
 * @desc 分类管理
 *
 */
class CategoryController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function categorylist() {
    	$keyword = I('keyword','','trim');
    	$category_id = I('category_id',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $type = I('type',0,'intval');
        if(empty($keyword)){
        	$where = array('parent_id'=>0);//只显示一级分类
        	if($category_id){
        		$where['id'] = $category_id;
        	}
        }else{
        	$where['name'] = array('like',"%$keyword%");
        	if($category_id){
        		$where['trees'] = array('like',"%,$category_id,%");
        	}
        }
        if($type){
            $where['type'] = $type;
            unset($where['id'],$where['trees']);
        }
        $start  = ($page-1) * $size;
        $m_category  = new \Admin\Model\CategoryModel();
        $result = $m_category->getCustomList($where, 'id desc', $start, $size);
        $all_types = array(0=>'',1=>'内容',2=>'场景',3=>'人员属性',4=>'饭局性质',5=>'内容所用软件');
        foreach ($result['list'] as $k=>$v){
        	$trees = $m_category->get_category_tree($v['id']);
        	if(!empty($trees)){
        		unset($trees[0]);
        	}
        	$result['list'][$k]['trees'] = $trees;
        	$result['list'][$k]['typestr'] = $all_types[$v['type']];
        }
        $category = $m_category->getCategory($category_id,1,1);
        unset($all_types[0]);
        $this->assign('alltype',$all_types);
        $this->assign('type',$type);
        $this->assign('category',$category);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('categorylist');
    }
    
    public function categoryadd(){
        $id = I('id', 0, 'intval');
        $type = I('type',0,'intval');
        $m_category  = new \Admin\Model\CategoryModel();
        if(IS_GET){
        	$dinfo = array('status'=>1,'sort'=>1,'type'=>$type);
        	if($id){
        		$dinfo = $m_category->getInfo(array('id'=>$id));
        		if(!empty($dinfo['parent_id'])){
        			$id = $dinfo['parent_id'];
        		}
        	}
        	$category = $m_category->getCategory($id);
        	$this->assign('category',$category);
        	$this->assign('dinfo',$dinfo);
        	$this->display('categoryadd');
        }else{
        	$name = I('post.name','','trim');
        	$category_id = I('post.category_id',0,'intval');
        	$sort = I('post.sort',1,'intval');
        	$type = I('post.type',0,'intval');
        	$status = I('post.status',1,'intval');

        	if(empty($name)){
        		$this->output('缺少必要参数!', 'category/categoryadd', 2, 0);
        	}
        	$where = array('name'=>$name,'type'=>$type);
        	if($id){
                $where['id']= array('neq',$id);
        		$res_category = $m_category->getInfo($where);
        	}else{
        		$res_category = $m_category->getInfo($where);
        	}
        	if(!empty($res_category)){
        		$this->output('名称不能重复', 'category/categoryadd', 2, 0);
        	}

        	$data = array('name'=>$name,'sort'=>$sort,'type'=>$type,'status'=>$status);
        	if($id){
        		if(empty($category_id)){
        			$data['level'] = 1;
        			$data['trees'] = ",$id,";
        		}else{
        			if($id!=$category_id){
        				$data['parent_id'] = $category_id;
	        			$res_cateinfo = $m_category->getInfo(array('id'=>$category_id));
	        			$cate_tree = trim($res_cateinfo['trees'],',');
	        			$cate_treearr = explode(',', $cate_tree);
	        			$cate_treearr = array_unique($cate_treearr);
	        			$data['level'] = count($cate_treearr) + 1;
	        			$data['trees'] = $res_cateinfo['trees']."$id,";
        			}
        		}
        		$condition = array('id'=>$id);
        		$result = true;
        	}else{
        		if(empty($category_id)){
        			$data['level'] = 1;
        		}else{
        			$data['parent_id'] = $category_id;
        			$res_cateinfo = $m_category->getInfo(array('id'=>$category_id));
        			$cate_tree = trim($res_cateinfo['trees'],',');
        			$cate_treearr = explode(',', $cate_tree);
        			$data['level'] = count($cate_treearr) + 1;
        		}
        		$result = $m_category->addData($data);
	        	if($result){
	        		if($data['level'] == 1){
	        			$trees = ",$result,";
	        		}else{
	        			$trees = $res_cateinfo['trees']."$result,";
	        		}
	        		$condition = array('id'=>$result);
	        		$data = array('trees'=>$trees);
	        	}
        	}
        	if($result){
        		$m_category->updateData($condition, $data);
        		$this->output('操作成功', 'category/categorylist');
        	}else{
        		$this->output('操作失败', 'category/categoryadd',2,0);
        	}
        }
    }

    public function operatestatus(){
        $id = I('get.id',0,'intval');
        $status = I('get.status',0,'intval');
        $condition = array('id'=>$id);
        $data = array('status'=>$status);
        $m_category = new \Admin\Model\CategoryModel();
        $m_category->updateData($condition, $data);
        if($status==1){
            $message = '启用成功';
        }elseif($status==0){
            $message = '禁用成功';
        }else{
            $message = '操作成功';
        }
        $this->output($message, 'category/categorylist',2);
    }

    public function categorydel(){
    	$category_id = I('get.id', 0, 'intval');
        $m_category = new \Admin\Model\CategoryModel();
    	$condition = array('trees'=>array('like',"%,$category_id,%"));
    	$result = $m_category->delData($condition);
    	if($result){
    		$this->output('删除成功', '',2);
    	}else{
    		$this->output('删除失败', '',2);
    	}
    }
}