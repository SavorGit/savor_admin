<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController;

class StoreController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }

    public function datalist() {
        $category_id = I('category_id',0,'intval');
        $status   = I('status',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $m_category = new \Admin\Model\CategoryModel();
        $m_store = new \Admin\Model\Smallapp\StoreModel();
        $start  = ($page-1) * $size;
        $where = array();
        if($category_id){
            $res_cate = $m_category->getDataList('id',array('parent_id'=>$category_id),'id desc');
            if(!empty($res_cate)){
                $cates = array();
                foreach ($res_cate as $v){
                    $cates[]=$v['id'];
                }
                $where['category_id'] = array('in',$cates);
            }else{
                $where['category_id'] = $category_id;
            }
        }
        if($status){
            $where['status'] = $status;
        }
        $orderby = 'id desc';
        $result = $m_store->getDataList('*',$where,$orderby,$start,$size);
        $datalist = $result['list'];
        $all_status = array('1'=>'正常','2'=>'禁用');
        $m_media = new \Admin\Model\MediaModel();
        foreach ($datalist as $k=>$v){
            $res_category = $m_category->getInfo(array('id'=>$v['category_id']));
            $datalist[$k]['category'] = $res_category['name'];
            $datalist[$k]['status_str'] = $all_status[$v['status']];
            $cover_img = '';
            if(!empty($v['cover_media_id'])){
                $res_media = $m_media->getMediaInfoById($v['cover_media_id']);
                $cover_img = $res_media['oss_addr'];
            }
            $datalist[$k]['cover_img'] = $cover_img;
        }
        $categorys = $m_category->getCategory($category_id,0,8);

        $this->assign('status',$status);
        $this->assign('all_status',$all_status);
        $this->assign('categorys', $categorys);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('datalist');
    }

    public function addstore(){
        $id = I('id', 0, 'intval');
        $m_store = new \Admin\Model\Smallapp\StoreModel();
        $m_category = new \Admin\Model\CategoryModel();
        if(IS_GET){
            if($id){
                $dinfo = $m_store->getInfo(array('id'=>$id));
                $category_id = $dinfo['category_id'];
                $maintainer_id = $dinfo['maintainer_id'];
                $area_id = $dinfo['area_id'];
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getMediaInfoById($dinfo['cover_media_id']);
                $dinfo['oss_addr'] = $res_media['oss_addr'];
            }else{
                $category_id = 0;
                $maintainer_id = 0;
                $area_id = 1;
                $dinfo = array('status'=>1,'area_id'=>$area_id);
            }

            $categorys = $m_category->getCategory($category_id,0,8);
            $m_area = new \Admin\Model\AreaModel();
            $all_area = $m_area->getAllArea();
            $parent_id = $this->getParentAreaid($area_id);
            $county_list = $m_area->getWhere('id,region_name',array('parent_id'=>$parent_id));

            $m_opuser_role = new \Admin\Model\OpuserroleModel();
            $opusers = $m_opuser_role->getOpuser($maintainer_id);
            $this->assign('county_list',$county_list);
            $this->assign('opusers', $opusers);
            $this->assign('area', $all_area);
            $this->assign('categorys',$categorys);
            $this->assign('vinfo',$dinfo);
            $this->display('addstore');
        }else{
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $addr = I('post.addr','','trim');
            $area_id = I('post.area_id',0,'intval');
            $county_id = I('post.county_id',0,'intval');
            $contractor = I('post.contractor','','trim');
            $mobile = I('post.mobile','','trim');
            $tel = I('post.tel','','trim');
            $category_id = I('post.category_id',0,'intval');
            $avg_expense = I('post.avg_expense',0,'intval');
            $gps = I('post.gps','','trim');
            $maintainer_id = I('post.maintainer_id',0,'intval');
            $status = I('post.status',1,'intval');

            $res_category = $m_category->getInfo(array('id'=>$category_id));
            if($res_category['level']==1){
                $this->output('请选择子分类', "store/addstore",2,0);
            }
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];

            $data = array('name'=>$name,'addr'=>$addr,'cover_media_id'=>$media_id,'category_id'=>$category_id,'avg_expense'=>$avg_expense,
                'mobile'=>$mobile,'tel'=>$tel,'contractor'=>$contractor,'maintainer_id'=>$maintainer_id,'area_id'=>$area_id,'county_id'=>$county_id,
                'gps'=>$gps,'status'=>$status,'sysuser_id'=>$sysuser_id);
            if($id){
                $data['update_time'] = date('Y-m-d H:i:s');
                $m_store->updateData(array('id'=>$id),$data);
            }else{
                $m_store->add($data);
            }
            $this->output('操作成功', "store/datalist");
        }

    }


}