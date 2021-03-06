<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController;

/**
 * @desc 商品规格管理
 *
 */
class GoodsspecificationController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }

    public function specificationlist() {
        $category_id = I('category_id',0,'intval');
        $status   = I('status',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $m_category = new \Admin\Model\CategoryModel();
        $categorys = $m_category->getCategory($category_id,1,7);
        $all_status = array('1'=>'正常','2'=>'禁用');

        $m_goodsspecification = new \Admin\Model\Smallapp\GoodsspecificationModel();
        $start  = ($page-1) * $size;
        $where = array();
        if($category_id){
            $where['category_id'] = $category_id;
        }
        if($status){
            $where['status'] = $status;
        }
        if($category_id){
            $orderby = 'sort desc,id desc';
        }else{
            $orderby = 'id desc';
        }
        $result = $m_goodsspecification->getDataList('*',$where,$orderby,$start,$size);
        $datalist = $result['list'];
        foreach ($datalist as $k=>$v){
            $res_category = $m_category->getInfo(array('id'=>$v['category_id']));
            $datalist[$k]['category'] = $res_category['name'];
            $datalist[$k]['status_str'] = $all_status[$v['status']];

        }
        $this->assign('status',$status);
        $this->assign('all_status',$all_status);
        $this->assign('categorys', $categorys);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('specificationlist');
    }

    public function specificationadd(){
        $id = I('id', 0, 'intval');
        $m_goodsspecification = new \Admin\Model\Smallapp\GoodsspecificationModel();
        if(IS_GET){
            if($id){
                $dinfo = $m_goodsspecification->getInfo(array('id'=>$id));
                $category_id = $dinfo['category_id'];
            }else{
                $category_id = 0;
                $dinfo = array('status'=>1);
            }
            $m_category = new \Admin\Model\CategoryModel();
            $categorys = $m_category->getCategory($category_id,1,7);

            $this->assign('categorys',$categorys);
            $this->assign('vinfo',$dinfo);
            $this->display('specificationadd');
        }else{
            $name = I('post.name','','trim');
            $category_id = I('post.category_id',0,'intval');
            $sort = I('post.sort',0,'intval');
            $status = I('post.status',1,'intval');

            $sort_where = array('category_id'=>$category_id,'sort'=>$sort);
            if($id){
                $sort_where['id'] = array('neq',$id);
            }
            $res_sort = $m_goodsspecification->getInfo($sort_where);
            if(!empty($res_sort)){
                $this->output('当前分类-名称排序值重复,请认真填写排序值', "goodsspecification/specificationadd",2,0);
            }
            if($status==1){
                $where = array('category_id'=>$category_id,'status'=>1);
                $res_specification = $m_goodsspecification->getDataList('*',$where,'id desc',0,1);
                if($res_specification['total']>=5){
                    $this->output('当前分类规格不能超过5个', "goodsspecification/specificationadd",2,0);
                }
            }
            
            $data = array('name'=>$name,'category_id'=>$category_id,'sort'=>$sort,'status'=>$status);
            if($id){
                $m_goodsspecification->updateData(array('id'=>$id),$data);
            }else{
                $m_goodsspecification->add($data);
            }
            $this->output('操作成功', "goodsspecification/specificationlist");
        }

    }


}