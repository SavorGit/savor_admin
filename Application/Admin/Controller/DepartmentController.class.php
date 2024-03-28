<?php
namespace Admin\Controller;
use Common\Lib\Page;
class DepartmentController extends BaseController {
    private $session_key = 'department_id_';
    public function __construct() {
        parent::__construct();
    }
    public function index(){
        $m_user_department  = new \Admin\Model\UserDepartmentModel();
        $m_user = new \Admin\Model\UserModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','a.id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $start  = ( $start-1 ) * $size;
        $orders = $order.' '.$sort;
        $search_name = I('search_name');
        
        $where = [];
        if(!empty($search_name)){
            $where['a.name'] = array('like',"%".$search_name."%");
            $this->assign('search_name',$search_name);
        }
        $where['a.status'] = 1;
        $fields = 'a.id,a.name,u.remark opt_user_name,a.create_time';
        $result = $m_user_department->getList($fields,$where,$orders,$start,$size);
        
        foreach($result['list'] as $key=>$v){
            $where = [];
            $where['deparment_id'] = $v['id'];
            $where['status'] = 1;
            $member_nums = $m_user->where($where)->count();
            $result['list'][$key]['member_nums'] = $member_nums;
        }
        $this->assign('userlist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('index');
    }
    public function add(){
        $userinfo = session('sysUserInfo');
        if(IS_POST) {
            $m_user_department = new \Admin\Model\UserDepartmentModel();   
            $name = I('post.name','','trim');
            $leader_user_id = I('post.leader_user_id',0,'intval');
            
            $userinfo = session('sysUserInfo');
            $data  = [];
            $data['name'] = $name;
            $data['leader_user_id'] = $leader_user_id;
            $data['opt_user_id']    = $userinfo['id'];
            $result = $m_user_department->addData($data);
            if($result) {
                $this->output('操作成功!', 'department/index');
            } else {
                $this->error('添加失败');
            }
        }else {
            $m_user = new \Admin\Model\UserModel();
            $where = [];
            $where['job_id'] = 2;
            //$where['status'] = 1;
            $userlist = $m_user->field('id,remark name')->where($where)->select();
            $this->assign('userlist',$userlist);
            $this->display('add');
        }
    }
    public function edit(){
        $id = I('id');
        if(IS_POST){
            $m_user_department = new \Admin\Model\UserDepartmentModel();
            $name = I('post.name','','trim');
            $leader_user_id = I('post.leader_user_id',0,'intval');
            $userinfo = session('sysUserInfo');
            $data  = [];
            $data['name'] = $name;
            $data['leader_user_id'] = $leader_user_id;
            $data['opt_user_id']    = $userinfo['id'];
            $data['update_time']    = date('Y-m-d H:i:s');
            
            $map = [];
            $map['id'] = $id;
            $result = $m_user_department->updateData($map, $data);
            
            if($result){
                $this->output('操作成功!', 'department/index');
            }else{
                $this->error('操作失败');
            }
        }else {
            $m_department = new \Admin\Model\UserDepartmentModel();
            $m_user = new \Admin\Model\UserModel();
            $where = [];
            $where['job_id'] = 2;
            $where['status'] = 1;
            
            $userlist = $m_user->field('id,remark name')->where($where)->select();
            
            $where = [];
            $where['id'] = $id;
            $department_info = $m_department->getInfo($where);
            
            $this->assign('vinfo',$department_info);
            $this->assign('userlist',$userlist);
            $this->assign('id',$id);
            $this->display('edit');
        }
    }
    public function memberlist(){
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $start  = ( $start-1 ) * $size;
        $orders = $order.' '.$sort;
        
        $department_id = I('department_id');
        if(!empty($department_id)){
            session($this->session_key,$department_id);
        }else {
            $department_id = session($this->session_key);
        }
        $search_name   = I('search_name');
        $m_user = new \Admin\Model\UserModel();
        $where = 'where 1 ';
        if(!empty($search_name)){
            $where .= ' and remark like "%'.$search_name.'%"';
            $this->assign('search_name',$search_name);
        }
        
        $where .=  " and deparment_id=".$department_id;
        $where .= " and `status`=1";
        $result = $m_user->getUserlist($where, $orders,$start,$size);
        $job_department_list = C('JOB_DEPARTMENT_LIST');
        
        $this->assign('job_department_list',$job_department_list);
        $this->assign('department_id',$department_id);
        $this->assign('user_list',$result['list']);
        $this->assign('page',  $result['page']);
        $this->display('memberlist');
    }
    public function memberadd(){
        $department_id = I('department_id');
        $m_user = new \Admin\Model\UserModel();
        if(IS_POST){
            $user_id = I('user_id');
            $where['id'] = $user_id;
            $data['deparment_id'] = $department_id;
            $result = $m_user->where($where)->save($data);
            if($result){
                $this->output('操作成功!', 'department/memberlist');
            }else{
                $this->error('操作失败');
            }
        }else{
            $m_department = new \Admin\Model\UserDepartmentModel();
            $where = [];
            $where['id'] = $department_id;
            $department_info = $m_department->getInfo($where);
            
            $field = 'id,remark name';
            $where = [];
            $where['job_id'] = 1;
            //$where['status'] = 1;
            //$where['deparment_id'] = array('neq',$department_id);
            
            $user_list = $m_user->field('id,remark name')->where($where)->select();
            echo $m_user->getLastSql();
            $this->assign('user_list',$user_list);
            $this->assign('department_info',$department_info);
            $this->display('memberadd');
        }
    }
    public function memberedit(){
        $department_id = I('department_id');
        $user_id = I('user_id');
        $m_user = new \Admin\Model\UserModel();
        if(IS_POST){
            $data = [];
            $data['deparment_id'] = $department_id;
            $map  = [];
            $map['id'] = $user_id;
            $result = $m_user->where($map)->save($data);
            if($result){
                $this->output('操作成功!', 'department/memberlist',1);
            }else{
                $this->error('操作失败');
            }
        }else {
            $m_department = new \Admin\Model\UserDepartmentModel();
            
            $field = 'id,remark name';
            $where = [];
            $where['job_id'] = 1;
            $where['status'] = 1;
            $user_list = $m_user->field('id,remark name')->where($where)->select();
            
            
            $where = [];
            $where['status'] =1;
            $department_list = $m_department->field('id,name')->where()->select();
            
            $this->assign('department_list',$department_list);
            $this->assign('user_list',$user_list);
            $this->assign('department_id',$department_id);
            $this->assign('user_id',$user_id);
            $this->display('memberedit');
        }
    }
    
}