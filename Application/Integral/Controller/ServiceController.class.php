<?php
namespace Integral\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController ;
/**
 * @desc 销售端积分-服务列表
 *
 */
class ServiceController extends BaseController {
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
        $sort = I('_sort','asc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        
        $start  = ( $start-1 ) * $size;
        $m_service = new \Admin\Model\Integral\IntegralServiceModel();
        
        $fields = 'a.id,a.name service_name,a.type,user.remark user_name,euser.remark e_user_name,a.create_time,a.update_time';
        $where = [];
        $where['a.status'] = 1;
        
        $list = $m_service->getList($fields,$where,$orders,$start,$size);
        //$servie_type = C('servie_type');
        $this->assign('service_type',$this->servie_type);
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->display('index');
        
    }
    /**
     * @desc 添加服务
     */
    public function add(){
        
        $service_type = $this->servie_type;
        if(IS_POST){
            $m_integral_service = new \Admin\Model\Integral\IntegralServiceModel();
            $name = I('name','','trim');
            $m_name = I('m_name','','trim');
            if(empty($name)) $this->error('服务名称不能为空');
            $info = $m_integral_service->getRow('id',array('name'=>$name,'status'=>1));
            if(!empty($info)) $this->error('该服务名称已存在');
            if(empty($m_name)) $this->error('模块名称不能为空');
            $info = $m_integral_service->getRow('id',array('m_name'=>$m_name,'status'=>1));
            if(!empty($info)) $this->error('该模块名称已存在');
            $type = I('type',0,'intval');
            $desc = I('desc','','trim');
            $data = [];
            $data['name'] = $name;
            $data['m_name'] = $m_name;
            $data['type'] = $type;
            $data['desc'] = $desc;
            
            $userinfo = session('sysUserInfo');
            $data['uid'] = $userinfo['id'];
            $data['status'] = 1;
            $ret = $m_integral_service->addData($data);
            
            if($ret){
                $this->output('添加成功', "service/index");
            }else {
                $this->output('添加失败', "service/index",2,0);
            }
        }else{
            $service_list = C('service_list');
            $this->assign('service_list',$service_list);
            $this->assign('service_type',$service_type);
            $this->display();
        }
    }
    /**
     * @desc 编辑服务
     */
    public function edit(){
        $service_type = $this->servie_type;
        $m_integral_service = new \Admin\Model\Integral\IntegralServiceModel();
        
        if(IS_POST){
            $id   = I('id',0,'intval');
            $name = I('name','','trim');
            $m_name = I('m_name','','trim');
            $type = I('type',0,'intval');
            $desc = I('desc','','trim');
            $where = [];
            $where['name']   = $name;
            $where['status'] = 1;
            $where['id']     = array('neq',$id);
            $info = $m_integral_service->getRow('id',$where);
            if(!empty($info)) $this->error('该服务名称已存在!');
            
            $where = [];
            $where['m_name'] = $m_name;
            $where['status'] = 1;
            $where['id']     = array('neq',$id);
            $info = $m_integral_service->getRow('id',$where);
            if(!empty($info)) $this->error('该模块名称已存在!');
            
            $data = [];
            $data['name'] = $name;
            $data['m_name'] = $m_name;
            $data['type'] = $type;
            $data['desc'] = $desc;
            $userinfo = session('sysUserInfo');
            $data['e_uid'] = $userinfo['id'];
            $data['update_time'] = date('Y-m-d H:i:s');
            $ret = $m_integral_service->updateData(array('id'=>$id), $data);
            if($ret){
                $this->output('编辑成功', "service/index");
            }else {
                $this->output('编辑失败', "service/index",2,0);
            }
        }else {
            $id   = I('id',0,'intval');
            
            $info = $m_integral_service->getRow('id,name,m_name,type,desc',array('id'=>$id));
            $service_list = C('service_list');
            $this->assign('service_list',$service_list);
            $this->assign('vinfo',$info);
            $this->assign('service_type',$service_type);
            $this->display();
        }
    }
    public function delete(){
        $id   = I('get.id',0,'intval');
        
        $data = [];
        $data['status'] = 0;
        $data['update_time'] = date('Y-m-d H:i:s');
        $userinfo = session('sysUserInfo');
        $data['e_uid'] = $userinfo['id'];
        
        $m_integral_service = new \Admin\Model\Integral\IntegralServiceModel();
        $ret = $m_integral_service->updateData(array('id'=>$id), $data);
        if($ret){
            $this->output('删除成功', "service/index",2);
        }else {
            $this->error('删除失败');
        }
    }
}