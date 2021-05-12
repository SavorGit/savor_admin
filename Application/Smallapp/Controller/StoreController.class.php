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
        $m_media = new \Admin\Model\MediaModel();
        if(IS_GET){
            $ads_info = array('id'=>0,'cover_img'=>'','choose_ads_style'=>'','ads_info_style'=>'display: none;');
            $detail_img_num = 6;
            $detailaddr = array();
            if($id){
                $oss_host = get_oss_host();
                $dinfo = $m_store->getInfo(array('id'=>$id));

                $category_id = $dinfo['category_id'];
                $maintainer_id = $dinfo['maintainer_id'];
                $area_id = $dinfo['area_id'];
                $res_media = $m_media->getMediaInfoById($dinfo['cover_media_id']);
                $dinfo['oss_addr'] = $res_media['oss_addr'];
                $coupon_oss_addr = '';
                if(!empty($dinfo['coupon_media_id'])){
                    $res_media = $m_media->getMediaInfoById($dinfo['coupon_media_id']);
                    $coupon_oss_addr = $res_media['oss_addr'];
                }
                $dinfo['couponoss_addr'] = $coupon_oss_addr;
                if($dinfo['ads_id']){
                    $m_ads = new \Admin\Model\AdsModel();
                    $ads_info = $m_ads->getInfo(array('id'=>$dinfo['ads_id']));
                    $res_ads_media = $m_media->getMediaInfoById($dinfo['media_id']);
                    $ads_info['ads_oss_addr'] = $res_ads_media['oss_addr'];
                    $ads_info['cover_img'] = $oss_host.$ads_info['img_url'];
                    $ads_info['choose_ads_style'] = "display: none;";
                    $ads_info['ads_info_style'] = "";
                }
                if($dinfo['detail_imgs']){
                    $detail_imgs = explode(',',$dinfo['detail_imgs']);
                    foreach ($detail_imgs as $k=>$v){
                        if(!empty($v)){
                            $detailaddr[$k+1] = array('media_id'=>$v,'oss_addr'=>$oss_host.$v);
                        }
                    }
                }
            }else{
                $category_id = 0;
                $maintainer_id = 0;
                $area_id = 1;
                $dinfo = array('status'=>1,'area_id'=>$area_id);
            }
            $detail_imgs = array();
            for($i=1;$i<=$detail_img_num;$i++){
                $img_info = array('id'=>$i,'imgid'=>'detail_id'.$i,'media_id'=>0);
                if(isset($detailaddr[$i])){
                    $img_info['media_id'] = $detailaddr[$i]['media_id'];
                    $img_info['oss_addr'] = $detailaddr[$i]['oss_addr'];
                }
                $detail_imgs[] = $img_info;
            }

            $categorys = $m_category->getCategory($category_id,0,8);
            $m_area = new \Admin\Model\AreaModel();
            $all_area = $m_area->getAllArea();
            $parent_id = $this->getParentAreaid($area_id);
            $county_list = $m_area->getWhere('id,region_name',array('parent_id'=>$parent_id));

            $m_opuser_role = new \Admin\Model\OpuserroleModel();
            $opusers = $m_opuser_role->getOpuser($maintainer_id);
            $this->assign('detail_imgs',$detail_imgs);
            $this->assign('ads_info',$ads_info);
            $this->assign('county_list',$county_list);
            $this->assign('opusers', $opusers);
            $this->assign('area', $all_area);
            $this->assign('categorys',$categorys);
            $this->assign('vinfo',$dinfo);
            $this->display('addstore');
        }else{
            $name = I('post.name','','trim');
            $cover_media_id = I('post.logomedia_id',0,'intval');
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
            $ads_id = I('post.marketid',0,'intval');
            $ads_img_media_id = I('post.ads_img_media_id',0,'intval');
            $detailmedia_id = I('post.detailmedia_id','');
            $couponmedia_id = I('post.couponmedia_id','');

            $res_category = $m_category->getInfo(array('id'=>$category_id));
            if($res_category['level']==1){
                $this->output('请选择子分类', "store/addstore",2,0);
            }
            if(!empty($ads_id) || !empty($detailmedia_id) || !empty($couponmedia_id)){
                if(empty($ads_id) || empty($detailmedia_id) || empty($couponmedia_id)){
                    $this->output('请完善优惠券,详情,广告信息', "store/addstore",2,0);
                }
            }
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $data = array('name'=>$name,'addr'=>$addr,'cover_media_id'=>$cover_media_id,'category_id'=>$category_id,'avg_expense'=>$avg_expense,
                'coupon_media_id'=>$couponmedia_id,'mobile'=>$mobile,'tel'=>$tel,'contractor'=>$contractor,'maintainer_id'=>$maintainer_id,'area_id'=>$area_id,'county_id'=>$county_id,
                'gps'=>$gps,'ads_id'=>$ads_id,'status'=>$status,'sysuser_id'=>$sysuser_id);
            $detail_imgs = array();
            if(!empty($detailmedia_id)){
                foreach ($detailmedia_id as $v){
                    if(!empty($v)){
                        if(is_numeric($v)){
                            $res_m = $m_media->getMediaInfoById($v);
                            $img = $res_m['oss_path'];
                        }else{
                            $img = $v;
                        }
                        $detail_imgs[]=$img;
                    }
                }
            }
            if(!empty($detail_imgs)){
                $data['detail_imgs'] = join(',',$detail_imgs);
            }else{
                $data['detail_imgs'] = '';
            }

            if($ads_id){
                $ads_updata = array('type'=>8);
                if($ads_img_media_id){
                    $m_media = new \Admin\Model\MediaModel();
                    $media_info = $m_media->field('oss_addr')->where('id='.$ads_img_media_id)->find();
                    $ads_updata['img_url'] = $media_info['oss_addr'];
                }
                $m_ads = new \Admin\Model\AdsModel();
                $m_ads->updateData(array('id'=>$ads_id),$ads_updata);
            }
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