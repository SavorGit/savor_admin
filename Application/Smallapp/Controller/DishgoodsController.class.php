<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

/**
 * @desc 菜品管理
 *
 */
class DishgoodsController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }

    public function goodslist() {
        $area_id = I('area_id',0,'intval');
        $status   = I('status',0,'intval');
        $type   = I('type',0,'intval');
        $flag   = I('flag',0,'intval');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array();
        if($status)     $where['a.status'] = $status;
        if($type)       $where['a.type'] = $type;
        if($flag)       $where['a.flag'] = $flag;
        if($area_id)    $where['area.id']=$area_id;
        if(!empty($keyword)){
            $where['hotel.name'] = array('like',"%$keyword%");
        }

        $start  = ($page-1) * $size;
        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        $fields = 'a.id,a.name,a.cover_imgs,a.intro,a.price,a.is_top,a.status,a.flag,
        user.nickName as staff_name,user.avatarUrl as staff_url,hotel.name as hotel_name,area.region_name as area_name';
        $result = $m_goods->getDishList($fields,$where, 'a.id desc', $start, $size);
        $datalist = $result['list'];

        $goods_status = C('DISH_STATUS');
        $goods_flag = C('DISH_FLAG');
        $oss_host = get_oss_host();
        foreach ($datalist as $k=>$v){
            $cover_imgsinfo = explode(',',$v['cover_imgs']);
            $image = '';
            if(!empty($cover_imgsinfo)){
                $image = $oss_host.$cover_imgsinfo[0];
            }
            if(isset($goods_flag[$v['flag']])){
                $flagstr = $goods_flag[$v['flag']];
            }else{
                $flagstr = '';
            }
            if($v['is_localsale']){
                $datalist[$k]['localstr']='是';
            }else{
                $datalist[$k]['localstr']='否';
            }
            $datalist[$k]['flagstr'] = $flagstr;
            $datalist[$k]['image'] = $image;
            $datalist[$k]['statusstr'] = $goods_status[$v['status']];
        }

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $this->assign('area_id',$area_id);
        $this->assign('area',$area_arr);
        $this->assign('status',$status);
        $this->assign('type',$type);
        $this->assign('flag',$flag);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('goodslist');
    }

    public function goodsadd(){
        $id = I('id', 0, 'intval');
        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        if(IS_GET){
            $detail_img_num = $cover_img_num = 6;
            $merchant_id = $category_id =0;
            $detailaddr = $coveraddr = array();
            $dinfo = array('type'=>22,'amount'=>1);

            $goods_types = C('DISH_TYPE');
            if($id){
                $m_media = new \Admin\Model\MediaModel();
                $dinfo = $m_goods->getInfo(array('id'=>$id));
                if($dinfo['type']==21){
                    unset($goods_types[22]);
                }else{
                    unset($goods_types[21]);
                }
                $poster_oss_addr = '';
                if(!empty($dinfo['poster_media_id'])){
                    $res_media = $m_media->getMediaInfoById($dinfo['poster_media_id']);
                    $poster_oss_addr = $res_media['oss_addr'];
                }
                $dinfo['poster_oss_addr'] = $poster_oss_addr;
                if($dinfo['amount']==0){
                    $dinfo['amount'] = 1;
                }
                $merchant_id = $dinfo['merchant_id'];
                $category_id = $dinfo['category_id'];
                $oss_host = get_oss_host();
                if($dinfo['detail_imgs']){
                    $detail_imgs = explode(',',$dinfo['detail_imgs']);
                    foreach ($detail_imgs as $k=>$v){
                        if(!empty($v)){
                            $detailaddr[$k+1] = array('media_id'=>$v,'oss_addr'=>$oss_host.$v);
                        }
                    }
                }
                if($dinfo['cover_imgs']){
                    $cover_imgs = explode(',',$dinfo['cover_imgs']);
                    foreach ($cover_imgs as $k=>$v){
                        if(!empty($v)){
                            $coveraddr[$k+1] = array('media_id'=>$v,'oss_addr'=>$oss_host.$v);
                        }
                    }
                }
            }else{
                unset($goods_types[21]);
            }
            $m_category = new \Admin\Model\CategoryModel();
            $categorys = $m_category->getCategory($category_id,1,7);

            $m_merchant = new \Admin\Model\Integral\MerchantModel();
            $where = array('a.status'=>1,'hotel.state'=>1,'hotel.flag'=>0);
            $fields = 'a.id,a.is_takeout,hotel.name';
            $merchants = $m_merchant->getMerchants($fields,$where,'a.id desc');
            foreach ($merchants as $k=>$v){
                if($merchant_id && $v['id']==$merchant_id){
                    $merchants[$k]['is_select'] = 'selected';
                }else{
                    $merchants[$k]['is_select'] = '';
                }
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
            $cover_imgs = array();
            for($i=1;$i<=$cover_img_num;$i++){
                $img_info = array('id'=>$i,'imgid'=>'cover_id'.$i,'media_id'=>0);
                if(isset($coveraddr[$i])){
                    $img_info['media_id'] = $coveraddr[$i]['media_id'];
                    $img_info['oss_addr'] = $coveraddr[$i]['oss_addr'];
                }
                $cover_imgs[] = $img_info;
            }
            $this->assign('cover_imgs',$cover_imgs);
            $this->assign('detail_imgs',$detail_imgs);
            $this->assign('merchants',$merchants);
            $this->assign('goods_types',$goods_types);
            $this->assign('categorys',$categorys);
            $this->assign('vinfo',$dinfo);
            $this->display('goodsadd');
        }else{
            $name = I('post.name','','trim');
            $covermedia_id = I('post.covermedia_id','');
            $detailmedia_id = I('post.detailmedia_id','');
            $video_intromedia_id = I('post.media_vid',0,'intval');
            $intro = I('post.intro','');
            $price = I('post.price',0);
            $amount = I('post.amount',0,'intval');
            $supply_price = I('post.supply_price',0);
            $line_price = I('post.line_price',0);
            $distribution_profit = I('post.distribution_profit',0);
            $merchant_id = I('post.merchant_id',0,'intval');
            $type = I('post.type',0,'intval');
            $category_id = I('post.category_id',0,'intval');
            $status = I('post.status',0,'intval');
            $is_localsale = I('post.is_localsale',0,'intval');
            $flag = I('post.flag',0,'intval');
            $postermedia_id = I('post.postermedia_id',0,'intval');

            if($type==22){
                if($price<$supply_price){
                    $this->output('零售价必须大于供货价', "dishgoods/goodsadd", 2, 0);
                }
                if($line_price && $line_price<$price){
                    $this->output('划线价必须大于零售价', "dishgoods/goodsadd", 2, 0);
                }
            }

            if(!$merchant_id){
                $this->output('请先选择商家', "dishgoods/goodsadd", 2, 0);
            }

            $where = array('name'=>$name,'merchant_id'=>$merchant_id);
            if($id){
                $where['id']= array('neq',$id);
                $res_goods = $m_goods->getInfo($where);
            }else{
                $res_goods = $m_goods->getInfo($where);
            }
            if(!empty($res_goods)){
                $this->output('名称不能重复', "dishgoods/goodsadd", 2, 0);
            }
            $userinfo = session('sysUserInfo');
            $sysuser_id = $userinfo['id'];
            $data = array('name'=>$name,'video_intromedia_id'=>$video_intromedia_id,'intro'=>$intro,'price'=>$price,'distribution_profit'=>$distribution_profit,
                'amount'=>$amount,'supply_price'=>$supply_price,'line_price'=>$line_price,'merchant_id'=>$merchant_id,'poster_media_id'=>$postermedia_id,
                'type'=>$type,'category_id'=>$category_id,'sysuser_id'=>$sysuser_id,'update_time'=>date('Y-m-d H:i:s'));
            if($type==22){
                if($flag==2){
                    $status = 1;
                }else{
                    $status = 2;
                }
            }
            $data['status'] = $status;
            $data['flag'] = $flag;
            $data['is_localsale'] = $is_localsale;
            $m_media = new \Admin\Model\MediaModel();
            $cover_imgs = array();
            if(!empty($covermedia_id)){
                foreach ($covermedia_id as $v){
                    if(!empty($v)){
                        if(is_numeric($v)){
                            $res_m = $m_media->getMediaInfoById($v);
                            $img = $res_m['oss_path'];
                        }else{
                            $img = $v;
                        }
                        $cover_imgs[]=$img;
                    }
                }
            }
            if(!empty($cover_imgs)){
                $data['cover_imgs'] = join(',',$cover_imgs);
            }else{
                $data['cover_imgs'] = '';
            }
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

            if($id){
                $m_goods->updateData(array('id'=>$id),$data);
                $result = true;
                $goods_id = $id;
            }else{
                $result = $m_goods->add($data);
                $goods_id = $result;
            }
            $m_merchant = new \Admin\Model\Integral\MerchantModel();
            $res_merchant = $m_merchant->getInfo(array('id'=>$merchant_id));

            if($res_merchant['is_takeout']==0){
                $m_merchant->updateData(array('id'=>$res_merchant['id']),array('is_takeout'=>1));
            }
            if($result){
                $this->output('操作成功', "dishgoods/goodslist");
            }else{
                $this->output('操作失败', "dishgoods/goodsadd",2,0);
            }
        }
    }

    public function changestatus(){
        $id = I('get.id',0,'intval');
        $status = I('get.status',0,'intval');
        if($status==1){
            $flag = 2;
        }else{
            $flag = 3;
        }
        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        $result = $m_goods->updateData(array('id'=>$id),array('status'=>$status,'flag'=>$flag));
        if($result){
            $this->output('操作成功!', 'dishgoods/goodslist',2);
        }else{
            $this->output('操作失败', 'dishgoods/goodslist',2,0);
        }
    }

}