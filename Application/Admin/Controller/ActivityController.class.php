<?php
/**
 * @desc   活动
 * @author zhang.yingtao
 * @since  2017-07-18
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class ActivityController extends BaseController{
    /**
     * @desc 
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
        
        $where = ' a.status!=2';
        $name = I('post.name','','trim');
        if($name){
            $where = " and name like'$name'";
            $this->assign('name',$name);
        }
        $m_activity_config =  new \Admin\Model\ActivityConfigModel(); 
        $result = $m_activity_config->getList('a.*,b.remark',$where,$order,$start,$size);
        //print_r($result);exit;
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->display('index');
    }
    /**
     * @desc 添加活动
     */
    public function add(){

        $this->display('add');
    }
    /**
     * @desc 提交活动信息
     */
    public function doadd(){
        if(IS_POST){
            $name = I('post.name','','trim');
            if(empty($name)){
                $this->error('活动名称不能未空');
            }
            $media_id = I('post.media_id','0','intval');
            $oss_info = array();
            $data = array();
            if(!empty($media_id)){
                $m_media = new \Admin\Model\MediaModel();
                $oss_info = $m_media->getMediaInfoById($media_id);    
            }
            $start_time = I('post.start_time');
            $end_time   = I('post.end_time');
            if(!empty($start_time) && empty($end_time)){
               $this->error('请填写活动结束时间'); 
            }
            if(empty($start_time) && !empty($end_time)){
                $this->error('请填写活动开始时间');
            }
            if(!empty($start_time) && !empty($end_time)){
                $startstr = strtotime($start_time);
                $endstr   = strtotime($end_time); 
                if($startstr>$endstr){
                    $this->error('活动开始时间不能大于结束时间');
                }
                $data['start_time'] = $start_time;
                $data['end_time']   = $end_time;
            }
            
            $goods_nums =  I('post.goods_nums','');
            if(is_numeric($goods_nums)){
                $data['goods_nums'] = $goods_nums;
            }
            $data['name'] = $name;
            if(!empty($oss_info)){
                $data['img_url'] = $oss_info['oss_addr'];
            }
            $userInfo = session('sysUserInfo');
            $data['operator_id'] = $userInfo['id'];
            $m_activity_config =  new \Admin\Model\ActivityConfigModel();
            $ret = $m_activity_config->addInfo($data);
            if($ret){
                $this->output('新增成功', 'activity/index', 2);
            }else {
                $this->error('新增失败');
            }
        }else {
            $this->error('非法操作');
        }
    }
    
    /**
     * @desc 佳美体验卡活动
     */
    public function toothwash(){
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
        
        $where = '';
        
        $mobile =  I('post.mobile','','trim');
        if($mobile){
            $where = " and mobile ='$mobile'";
            $this->assign('mobile',$mobile);
        }
        $start_time = I('post.start_time','');
        if($start_time){
            $where = " and a.add_time>='$start_time'";
            $this->assign('start_time',$start_time);
        }
        $end_time   = I('post.end_time','');
        if($end_time){
            $where = " and a.add_time<='$end_time'";
            $this->assign('end_time',$end_time);
        }
        $m_activity_data = new \Admin\Model\ActivityDataModel();
        $field = "a.id,a.add_time,a.receiver,a.mobile,a.address";
        $list = $m_activity_data->getList($field,$where , $order, $start ,$size);
        
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->display('toothwash');
    }
    public function changestatus(){
        $id = I('get.id','0','intval');
        $status = I('get.status','0','intval');
        
        $m_activity_config = new \Admin\Model\ActivityConfigModel();
        $info = $m_activity_config->getInfo('id,status',array('id'=>$id,'status'=>array('in','0,1')));
        //print_r($info);exit;
        if(empty($info)){
            $this->error('该活动不存在');
        }
        if($status==0 && $info['status']== $status){
            $this->error('该活动已下线,请不要重新操作');
        }
        if($status ==1 && $info['status'] == $status){
            $this->error('该活动已上线,请不要重新操作');
        }
        $map = $data = array();
        $userInfo = session('sysUserInfo');
        $map['id'] = $id;
        $data['status'] = $status;
        $data['edit_time'] = date('Y-m-d H:i:s');
        $data['operator_id'] = $userInfo['id'];
        $ret = $m_activity_config->editInfo($map,$data);
        if($ret){
            $this->output('状态更新成功', 'activity/index',2);
        }else {
            $this->output('状态更新失败', 'activity/index',2);
        }
    }
    /**
     * @desc 删除活动
     */
    public function delActivity(){
        $id = I('get.id','0','intval');
        $m_activity_config = new \Admin\Model\ActivityConfigModel();
        $info = $m_activity_config->getInfo('id,status',array('id'=>$id,'status'=>array('in','0,1')));
        if(empty($info)){
            $this->error('该活动不存在');
        }
        $map = $data = array();
        $userInfo = session('sysUserInfo');
        $map['id'] = $id;
        $data['status'] = 2;
        $data['edit_time'] = date('Y-m-d H:i:s');
        $data['operator_id'] = $userInfo['id'];
        $ret = $m_activity_config->editInfo($map, $data);
        if($ret){
            $this->output('删除成功!', 'acitivity/index',2);
        }else {
            $this->error('删除失败','activity/index');
        }   
    } 
}