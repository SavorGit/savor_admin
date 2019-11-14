<?php
namespace Integral\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController ;
/**
 * @desc 销售端积分-服务列表
 *
 */
class ServicemodelController extends BaseController {
private  $servie_type;
    public function __construct() {
        parent::__construct();
        $this->servie_type = C('servie_type');
    }
    public function index(){
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        
        $fields = 'a.id,a.name,a.create_time,a.update_time,user.remark user_name,euser.remark e_user_name';
        $where = [];
        $where['a.status'] = 1;
        $start  = ( $start-1 ) * $size;
        $m_service_mx = new \Admin\Model\Integral\ServiceMxModel();
        $list = $m_service_mx->getList($fields, $where, $order, $start, $size);
        
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        
        $this->display();
    }
    public function add(){
        
        if(IS_POST){
            $m_service_mx = new \Admin\Model\Integral\ServiceMxModel();
            $ids = I('post.ids','','trim');
            $name = I('post.name','','trim');
            if(empty($ids)) $this->error('请选择服务');
            
            $info = $m_service_mx->getRow('id',array('name'=>$name,'status'=>1));
            if(!empty($info)) $this->error('该模型名称已存在!');
            
            $data = [] ;
            $data['name'] = $name;
            $data['service_ids']  = json_encode($ids);
            $userinfo     = session('sysUserInfo');
            $data['uid']  = $userinfo['id'];
            $data['status']= 1;
            
            $ret = $m_service_mx->addData($data);
            if($ret){
                $this->output('添加成功', "servicemodel/index");
            }else {
                $this->output('添加失败', "servicemodel/index",2,0);
            }
            
        }else {
            $m_service = new \Admin\Model\Integral\IntegralServiceModel();
            $where = [];
            $where['status'] = 1;
            $orderby = 'type asc,id asc';
            $service_list = $m_service->getDataList('id,name,desc,type', $where, $orderby);
            $service_type = $this->servie_type;
            $this->assign('service_type',$service_type);
            $this->assign('service_list',$service_list);
            $this->display();
        }
    }
    public function edit(){
        $id = I('id',0,'intval');
        $m_service_mx =  new \Admin\Model\Integral\ServiceMxModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $ids = I('post.ids','','trim');
            $data = [];
            if(empty($ids)) $this->error('请选择服务');
            $where = [];
            $where['name'] = $name;
            $where['status']= 1;
            $where['id'] = array('neq',$id);
            $info = $m_service_mx->getRow('id',$where);
            
            if(!empty($info)) $this->error('该模型名称已存在!');
            
            $data['name'] = $name;
            $data['service_ids'] = json_encode($ids);
            $data['update_time'] = date('Y-m-d H:i:s');
            $userinfo     = session('sysUserInfo');
            $data['e_uid']  = $userinfo['id'];
            $where = [];
            $where['id'] = $id;
            
            $ret = $m_service_mx->updateData($where, $data);
            if($ret){
                $this->output('编辑成功', "servicemodel/index");
            }else {
                $this->output('编辑失败', "servicemod/index",2,0);
            }
        }else{
            $info = $m_service_mx->getRow('id,service_ids,name',array('id'=>$id));
            
            $service_ids_arr = json_decode($info['service_ids'],true);
            
            $m_service = new \Admin\Model\Integral\IntegralServiceModel();
            $where = [];
            $where['status'] = 1;
            $orderby = 'type asc ,id asc';
            $service_list = $m_service->getDataList('id,name,desc,type', $where, $orderby);
            $service_type = $this->servie_type;
            $this->assign('service_type',$service_type);
            $this->assign('service_list',$service_list);
            $this->assign('service_ids_arr',$service_ids_arr);
            $this->assign('vinfo',$info);
            
            $this->display();
        }
    }
    public function delete(){
        $id = I('get.id');
        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        
        $where = [];
        $where['service_model_id'] = $id;
        $where['status']  = 1;
        $nums = $m_merchant->where($where)->count();
        if(empty($nums)){
            $m_service_model = new \Admin\Model\Integral\ServiceMxModel();
            $userinfo     = session('sysUserInfo');
            $where = [];
            $where['id'] = $id;
            $data = [];
            $data['status'] = 0;
            $data['e_uid'] = $userinfo['id'];
            $ret = $m_service_model->updateData($where, $data);
            if($ret){
                $this->output('删除成功', "servicemodel/index");
            }else {
                $this->output('删除失败', "servicemod/index",2,0);
            }
        }else {
            $this->error('该模型已被商家使用，不可删除');
        }
    }
}