<?php
/**
 * @desc   活动商品
 * @author zhang.yingtao
 * @since  2017-09-09
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class ActivitygoodsController extends BaseController{
    
    /**
     * @desc 活动商品列表 
     */
    public function index(){
    
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','a.add_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        
        $where = ' a.status =1';
        $name = I('post.name','','trim');
        if($name){
            $where .= " and a.name like '%$name%'";
            $this->assign('name',$name);
        }
        
        //$m_activity_config =  new \Admin\Model\ActivityConfigModel();
        $m_activity_goods = new \Admin\Model\ActivityGoodsModel();
        $result = $m_activity_goods->getList('a.*,b.remark,c.name as activity_name',$where,$order,$start,$size);
        //print_r($result);exit;
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->display('index');  
    }
    
    /**
     * @desc 添加活动商品
     */
    public function add(){
        
        $m_activity_config = new \Admin\Model\ActivityConfigModel();
        $where = array();
        $where['status'] = array('neq','2');
        $order = ' id desc';
        $activitylist = $m_activity_config->getInfo('id,name',$where,$order,'',2);
     
        $this->assign('activitylist',$activitylist);
        $this->display('add');
        
    }
    /**
     * @desc 保存添加活动商品
     */
    public function doAdd(){
        if(IS_POST){
            
            $goods_name = I('goods_name','','trim');
            
            if(empty($goods_name)){
                $this->error('商品名称不能为空');
            }
            $media_id = I('post.media_id','0','intval');
            $oss_info = array();
            $data = array();
            if(!empty($media_id)){
                $m_media = new \Admin\Model\MediaModel();
                $oss_info = $m_media->getMediaInfoById($media_id);
            }
            
        
            $goods_nums =  I('post.goods_nums','');
            if(is_numeric($goods_nums)){
                $data['goods_nums'] = $goods_nums;
            }
            $data['goods_name'] = $goods_name;
            if(!empty($oss_info)){
                $data['img_url'] = $oss_info['oss_addr'];
            }
        
            $data['goods_price'] = I('goods_price',0.00,'float');
            $activity_id = I('activity_id',0,'intval');
            if(empty($activity_id)){
                $this->error('请选择所属活动');
            }
            $data['activity_id'] = $activity_id;
            
            $userInfo = session('sysUserInfo');
            $data['operator_id'] = $userInfo['id'];
            $m_activity_goods =  new \Admin\Model\ActivityGoodsModel();
            $ret = $m_activity_goods->addInfo($data);
            if($ret){
                $this->output('新增成功', 'activitygoods/index', 1);
            }else {
                $this->error('新增失败');
            }
        }else {
            $this->error('非法操作');
        }
    }
    /**
     * @desc 编辑活动商品
     */
    public function edit(){
        $id = I('get.id','','intval');
        $m_activity_goods = new \Admin\Model\ActivityGoodsModel();
        $vinfo = $m_activity_goods->getInfo('*', array('id'=>$id));
        if(empty($vinfo)){
            $this->error('活动不存在');
        }
        $vinfo = $vinfo[0];
        $m_activity_config = new \Admin\Model\ActivityConfigModel();
        $where = array();
        $where['status'] = array('neq','2');
        $order = ' id desc';
        $activitylist = $m_activity_config->getInfo('id,name',$where,$order,'',2);
         
        $this->assign('activitylist',$activitylist);
        $this->assign('id',$id);
        $this->assign('vinfo',$vinfo);
        $this->display('edit');
    }
    /**
     * @desc 保存编辑商品
     */
    public function doEdit(){
        if(IS_POST){
            $id = I('post.id','0','intval');
            $goods_name = I('post.goods_name','','trim');
            if(empty($goods_name)){
                $this->error('商品名称不能未空');
            }
            $media_id = I('post.media_id','0','intval');
            $oss_info = array();
            $data = array();
            if(!empty($media_id)){
                $m_media = new \Admin\Model\MediaModel();
                $oss_info = $m_media->getMediaInfoById($media_id);
            }
           
        
            $goods_nums =  I('post.goods_nums','');
            if(is_numeric($goods_nums)){
                $data['goods_nums'] = $goods_nums;
            }
            $data['goods_name'] = $goods_name;
            if(!empty($oss_info)){
                $data['img_url'] = $oss_info['oss_addr'];
            }
            $data['goods_price'] = I('goods_price',0.00,'float');
            $activity_id = I('activity_id',0,'intval');
            if(empty($activity_id)){
                $this->error('请选择所属活动');
            }
            $data['activity_id'] = $activity_id;
            
            $userInfo = session('sysUserInfo');
            $data['operator_id'] = $userInfo['id'];
            $m_activity_goods =  new \Admin\Model\ActivityGoodsModel();
            
            $ret = $m_activity_goods->editInfo(array('id'=>$id),$data);
            if($ret){
                $this->output('修改成功', 'activitygoods/index', 1);
            }else {
                $this->error('修改失败');
            }
        }else {
            $this->error('非法操作');
        }
    }
    /**
     * @desc 删除商品
     */
    public function delete(){

        $id = $_GET['id'];
        
        $status = I('status',0,'intval');
        
        $map = $data = array();
        $userInfo = session('sysUserInfo');
        $map['id'] = $id;
        $data['status'] = $status;
        $data['edit_time'] = date('Y-m-d H:i:s');
        $data['operator_id'] = $userInfo['id'];
        $m_activity_goods= new \Admin\Model\ActivityGoodsModel();
        
        
        $ret = $m_activity_goods->editInfo($map,$data);
        if($ret){
            $this->output('状态更新成功', 'activitygoods/index',2);
        }else {
            $this->output('状态更新失败', 'activitygoods/index',2);
        }
    }
}