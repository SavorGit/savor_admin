<?php
/**
 * @desc   活动
 * @author zhang.yingtao
 * @since  2017-10-16
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class OptionuserController extends BaseController{
    private $option_user_skill_arr;
    private $option_user_role_arr;
    public function __construct() {
        parent::__construct();
        $this->option_user_skill_arr = C('OPTION_USER_SKILL_ARR');
        $this->option_user_role_arr = C('OPTION_USER_ROLE_ARR');
    }
    public function index(){
        
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','a.update_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $opuser_arr = C('OPUSER_ARRAY');
        $this->assign('opuser_arr',$opuser_arr);
        $where = ' state=1';
        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $list = $m_opuser_role->getPageList('a.*,b.remark as username',$where,$orders,$start,$size);
        
        
        $m_area = new \Admin\Model\AreaModel();
        $result =$m_area->getHotelAreaList();
        foreach($result as $key=>$v){
            $area_info[$v['id']] = $v;
        }
        
        foreach($list['list'] as $key=>$v){
            $space = '';
            if($v['manage_city'] ==9999){
                $list['list'][$key]['manage_city_str'] = '全国';
            }else {
                $manage_city_arr = explode(',', $v['manage_city']);
                foreach($manage_city_arr as $mv){
                    $list['list'][$key]['manage_city_str'] .= $area_info[$mv]['region_name'];
                }
            }
            if(!empty($v['skill_info'])){
                $skill_info_arr = explode(',', $v['skill_info']);
                foreach($skill_info_arr as $sv){
                    $list['list'][$key]['skill_str'] .= $space. $this->option_user_skill_arr[$sv];
                    $space = ',';
                }
                
            }
            $list['list'][$key]['role_name'] = $this->option_user_role_arr[$v['role_id']];
        }
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']); 
       
        $this->display('index');
    }
    public function add(){
        //获取运维端人员得账号列表
        $m_sysuser = new \Admin\Model\UserModel();
        
        $fields = 'a.id,a.remark'; 
        $where  = array();
        $where['a.status'] = 1;
        $where['b.name'] = array('like','酒楼运维%');
        $order = 'a.id asc';
        $limit = '';
        
        $list = $m_sysuser->getGourpList($fields, $where, $order, $limit);
        
        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $set_user_list = $m_opuser_role->getList('user_id','state=1');
        foreach($set_user_list as $v){
            $tmp[] = $v['user_id'];
        }
        
        $userlist = array();
        foreach($list as $key=>$v){
            if(in_array($v['id'], $tmp)){
                continue;
            }
            $firstCharter = getFirstCharter($v['remark']);
            $userlist[$firstCharter][] = $v;
        }
        ksort($userlist);
        //print_r($userlist);exit;
        //获取省份
        
        $m_area_info = new \Admin\Model\AreaModel();
        $areaList = $m_area_info->getHotelAreaList();
        $city_area_list = $areaList;
        
        $nationwide = array('id'=>9999,'region_name'=>'全国');
        array_unshift($areaList, $nationwide);
        
        $this->assign('city_area_list',$city_area_list);
        $this->assign('areaList',$areaList);
        $this->assign('userlist',$userlist);
        $this->assign('option_user_role_arr',$this->option_user_role_arr);
        $this->assign('option_user_skill_arr',$this->option_user_skill_arr);
        $this->display('add');
        
    }
    public function doadd(){
        //print_r($_POST);exit;
        $id =I('post.id');
        $user_id= I('post.user_id',0,'intval');   //账号id
        $role_id = I('post.role_id',0,'intval');  //角色id
        if(empty($user_id)){
            $this->error('请选择账号');
        }
        if(empty($role_id)){
            $this->error('请选择角色');
        }
        if(empty($id)){
        
            
            $skill_info = I('post.skill');            //技能
            
            $m_opser_role = new \Admin\Model\OpuserroleModel();
            $role_info = $m_opser_role->getList('id',array('user_id'=>$user_id,'state'=>1));
            if(!empty($role_info)){
                $this->error('该账号已经设置过运维端账号！');
            }
            
            foreach($skill_info as $key=> $v){
                if(!empty($v)){
                    $skill_info_str .= $space .$v;
                    $space = ',';
                }
            }
            $is_lead_install = I('post.is_lead_install',0,'intval');  //是否带队安装
            /* if($role_id ==1 || $role_id==3){
                $manage_city = I('post.manage_city_one');
            }else {
                $manage_city = I('post.manage_city');
            }
            
             */
            $manage_city = I('post.manage_city');
            $manage_city_one = I('post.manage_city_one');
            foreach($manage_city as $key=>$v){
                $manage_city_str .= $separator . $v;
                $separator         = ',';    
            }
            if($role_id ==1 || $role_id ==3){//发布者核执行者 不可以多选城市
                if(strstr($manage_city_str, ',')){
                    $this->error('发布者和执行者不能选择多个城市');
                }
            }
            
            $userinfo = session('sysUserInfo');
            $oprator_id = $userinfo['id'];                //操作人id
            $data = array();
            $data['user_id']    = $user_id;
            $data['role_id']    = $role_id;
            if($role_id ==3){
                $data['skill_info'] = $skill_info_str;
                $data['is_lead_install'] = $is_lead_install;
            }
            
            
            if($role_id ==1 || $role_id ==3){
                $data['manage_city'] = $manage_city_one;
            }else {
                $data['manage_city'] = $manage_city_str;
            }
            $data['oprator_id']  = $oprator_id;

            $ret = $m_opser_role->addInfo($data);
            if($ret){
                $this->output('新增成功', 'optionuser/index', 1);
                
            }else{
                $this->error('添加失败');
            }
        }else {
            
            $skill_info = I('post.skill');            //技能
            
            foreach($skill_info as $key=> $v){
                if(!empty($v)){
                    $skill_info_str .= $space .$v;
                    $space = ',';
                }
            }
            $is_lead_install = I('post.is_lead_install',0,'intval');  //是否带队安装
            $manage_city = I('post.manage_city');
            $manage_city_one = I('post.manage_city_one');
            
            foreach($manage_city as $key=>$v){
                $manage_city_str .= $separator . $v;
                $separator         = ',';
            }
          /*   if($role_id ==1 || $role_id ==3){//发布者核执行者 不可以多选城市
                if(strstr($manage_city_str, ',')){
                    $this->error('发布者和执行者不能选择多个城市');
                }
            } */
            
            $userinfo = session('sysUserInfo');
            $oprator_id = $userinfo['id'];                //操作人id
            $data = array();
            $map['id'] = $id;
            $data['user_id']    = $user_id;
            $data['role_id']    = $role_id;
            if($role_id ==3){
                $data['skill_info'] = $skill_info_str;
                $data['is_lead_install'] = $is_lead_install;
            }
            
            
            if($role_id ==1 || $role_id ==3){
                $data['manage_city'] = $manage_city_one;
            }else {
                $data['manage_city'] = $manage_city_str;
            }
            
            $data['oprator_id']  = $oprator_id;
   
            $m_opser_role = new \Admin\Model\OpuserroleModel();
            $ret = $m_opser_role->saveInfo($map,$data);
            //$ret = $m_opser_role->addInfo($data);
            if($ret){
                $this->output('更新成功', 'optionuser/index', 1);
            
            }else{
                $this->error('更新失败');
            }
        }
    }
    public function edit(){
        $id = I('get.id',0,'intval');
        
        
        
        $m_opser_role = new \Admin\Model\OpuserroleModel();
        $where = array();
        $where['a.id'] = $id;
        $fields = 'a.role_id,a.skill_info,a.is_lead_install,a.manage_city,user.id  ,user.remark';
        $info =  $m_opser_role->getInfo($fields,$where);
        $manage_city = $info['manage_city'];
        $manage_city_arr = explode(',', $manage_city);
        $this->assign('manage_city_arr',$manage_city_arr);
        if($info['role_id'] ==3){//如果是执行者
            $skill_info_arr = explode(',', $info['skill_info']);
            $this->assign('skill_info_arr',$skill_info_arr);
            
        }else {
            $this->assign('skill_info_arr',array());
        }
        $list = array(array('id'=>$info['id'],'remark'=>$info['remark']));
        foreach($list as $key=>$v){
            $firstCharter = getFirstCharter($v['remark']);
            $userlist[$firstCharter][] = $v;
        }
        //获取省份
        
        $m_area_info = new \Admin\Model\AreaModel();
        $areaList = $m_area_info->getHotelAreaList();
        $city_area_list = $areaList;
        $nationwide = array('id'=>9999,'region_name'=>'全国');
        array_unshift($areaList, $nationwide);
        
        $this->assign('areaList',$areaList);
        $this->assign('city_area_list',$city_area_list);
        $this->assign('manage_city_arr',$manage_city_arr);
        $this->assign('userlist',$userlist);
        $this->assign('option_user_role_arr',$this->option_user_role_arr);
        $this->assign('option_user_skill_arr',$this->option_user_skill_arr);
        $this->assign('info',$info);
        $this->assign('id',$id);
        $this->display('edit');
    }
    public function delete(){
        $id = I('get.id',0,'intval');
        $m_opser_role = new \Admin\Model\OpuserroleModel();
        $where = $data = array();
        $where['id'] = $id;
        $data['state'] = 0;
        $ret = $m_opser_role->saveInfo($where,$data);
        if($ret){
            $this->output('删除成功', 'optionuser/index', '2');
        }else {
            $this->error('删除失败');
        }
    }
}