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
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array();
        if($status)     $where['a.status'] = $status;
        if($area_id)    $where['area.id']=$area_id;
        if(!empty($keyword)){
            $where['hotel.name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        $fields = 'a.id,a.name,a.cover_imgs,a.intro,a.intro_type,a.price,a.is_top,a.status,
        user.nickName as staff_name,user.avatarUrl as staff_url,hotel.name as hotel_name,area.region_name as area_name';
        $result = $m_goods->getDishList($fields,$where, 'a.id desc', $start, $size);
        $datalist = $result['list'];

        $goods_status = C('DISH_STATUS');
        $oss_host = get_oss_host();
        foreach ($datalist as $k=>$v){
            $cover_imgsinfo = explode(',',$v['cover_imgs']);
            $image = '';
            if(!empty($cover_imgsinfo)){
                $image = $oss_host.$cover_imgsinfo[0];
            }
            $datalist[$k]['image'] = $image;
            $datalist[$k]['statusstr'] = $goods_status[$v['status']];
        }

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $this->assign('area_id',$area_id);
        $this->assign('area',$area_arr);
        $this->assign('status',$status);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('goodslist');
    }

    public function changestatus(){
        $id = I('get.id',0,'intval');
        $status = I('get.status',0,'intval');

        $m_goods  = new \Admin\Model\Smallapp\DishgoodsModel();
        $result = $m_goods->updateData(array('id'=>$id),array('status'=>$status));
        if($result){
            $this->output('操作成功!', 'dishgoods/goodslist',2);
        }else{
            $this->output('操作失败', 'dishgoods/goodslist',2,0);
        }
    }

}