<?php
namespace Admin\Controller;
/**
 * @desc 用户管理类
 *
 */
class UserController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function userList() {
        $user  = new \Admin\Model\UserModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = " where 1";
        $searchTitle= I('searchTitle');
        if($searchTitle) {
            $where .= " and remark like '%$searchTitle%'";
            $this->assign('searchTitle',  $searchTitle);
        }
        $searchClass=I('searchCid');
        if($searchClass) {
            $where .= " and shw_cid = '$searchClass'";
            $this->assign('searchClass',  $searchClass);
        }
        
        $result = $user->getUserlist($where, $orders, $start, $size);
        $groups = new \Admin\Model\SysusergroupModel();
        $rstGroup= $groups->getAllGroup();
        $this->assign('groupslist',$rstGroup);
        
        $this->assign('userlist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('index');
    }
    
    //新增用户
    public function userAdd(){
        $acttype = I('acttype', 0, 'int');
        
        //处理提交数据
        if(IS_POST) {
            //获取参数
            $userId  = I('id', '', 'int');
            $remark  = I('remark');
            $username= I('username');
            $userpwd = I('userpwd');
            $status  = I('status', 1, 'int');
            //判断添加
            if($remark && $username && $userpwd) {
                $data['id']   = $userId;
                $data['remark']   = $remark;
                $data['username'] = $username;
                $data['status'] = $status;
                $pwdpre = C('PWDPRE');
                $userpwd = $userpwd.$pwdpre;
                $data['password'] = md5($userpwd);
                $user = new \Admin\Model\UserModel();
                $result = $user->addData($data, $acttype);
                if($result) {
                    $this->output('操作成功!', 'user/userList');
                } else {
                    $this->output('操作失败!', 'user/userAdd');
                }
            } else {
                $this->output('缺少必要参数!', 'user/userAdd');
            }
        }
        
        //非提交处理
        if(1 === $acttype) {
            $uid = I('id', 0, 'int');
            if(!$uid) {
                $this->output('当前用户不存在!', 'user/userList');
            }
            $user = new \Admin\Model\UserModel();
            $result = $user->getUserInfo($uid);
            $this->assign('vinfo', $result);
            $this->assign('acttype', 1);
        } else {
            $this->assign('acttype', 0);
        }
        $this->display('User/useradd');
    }
    
    //用户修改密码
    public function userEdit(){
        $acttype = I('acttype', 1, 'int');
        $uid = I('uid', 0, 'int');
        if(IS_POST) {
            //获取参数
            $userId  = I('id', '', 'int');
            $remark  = I('remark');
            $newuserpwd = I('newuserpwd');
            $status  = I('status', 1, 'int');
            if($userId && $remark) {
                $data['id']   = $userId;
                $data['remark']   = $remark;
                $data['status'] = $status;
                if($newuserpwd){
                    $pwdpre = C('PWDPRE');
                    $newuserpwd = $newuserpwd.$pwdpre;
                    $data['password'] = md5($newuserpwd);
                }
                $user = new \Admin\Model\UserModel();
                $result = $user->addData($data, $acttype);
                if($result) {
                    $this->output('操作成功!', 'user/userList');
                } else {
                    $this->output('操作失败!', 'user/userEdit');
                }
            } else {
                $this->output('缺少必要参数!', 'user/userEdit');
            }
            
        }
        //非提交处理
        $user = new \Admin\Model\UserModel();
        $result = $user->getUserInfo($uid);
        $this->assign('vinfo', $result);
        $this->display('useredit');
    }
    
    
    //当前用户修改自己密码
    public function chagePwd(){
        $userInfo = session('sysUserInfo');
        $acttype = I('acttype', 1, 'int');
        if(IS_POST) {
            //获取参数
            $uid  = $userInfo['id'];
            $curentuserpwd = I('curentuserpwd');
            $newuserpwd = I('newuserpwd');
            $remark  = I('remark');
            if($uid && $curentuserpwd && $newuserpwd) {
                $pwdpre = C('PWDPRE');
                $curentuserpwd = $curentuserpwd.$pwdpre;
                $newuserpwd = $newuserpwd.$pwdpre;
                $olderpwd = md5($curentuserpwd);
                $user = new \Admin\Model\UserModel();
                $result = $user->getUserInfo($uid);
                if( $olderpwd != $result['password']){
                    $this->output('当前密码不正确!', 'user/chagePwd', 2, 0);
                }
                $data['id']   = $uid;
                $data['password'] = md5($newuserpwd);
                $data['remark'] =$remark;
                $result = $user->addData($data, $acttype);
                if($result) {
                    $this->output('操作成功!', 'user/userList');
                } else {
                    $this->output('操作失败!', 'user/chagePwd');
                }
            } else {
                $this->output('缺少必要参数!', 'user/chagePwd');
            }
    
        }
        //非提交处理
        $user = new \Admin\Model\UserModel();
        $result = $user->getUserInfo($userInfo['id']);
        $this->assign('vinfo', $result);
        $this->display('chagepwd');
    }
    
    //修改用户权限
    public function userRank(){
        $acttype = I('acttype', 1, 'int');
        $uid = I('uid', 0, 'int');
        $groupId= I('groupId', 0, 'int');
        
        if(IS_POST) {
            $uid = I('uid', 0, 'int');
            $groupId= I('group', 0, 'int');
            $data['id']   = $uid;
            $data['groupId']=$groupId;
            //更新user表中的groupid的值
            $user = new \Admin\Model\UserModel();
            $user->addData($data, $acttype);
            
            //获取当前选的角色的栏目
            $groups = new \Admin\Model\SysusergroupModel();
            $groupInfo= $groups->getInfo($groupId);
            $groupCode=json_decode($groupInfo['code']);
            
            //检测当前用户是否已有记录
            $userRank = new \Admin\Model\StaffauthModel();
            $userInfo = $userRank->getInfo($uid);
            $rackType = !empty($userInfo)? 1 : 0;
            $datas['id']      = !empty($userInfo['id'])? $userInfo['id'] : '';
            $datas['staff_id']= $uid;
            $datas['code']    = json_encode($groupCode);
            $datas['staff_name']='test';
            $addRankInfo = $userRank->addData($datas, $rackType);
            if($addRankInfo){
                $this->output('操作成功!', 'user/userList');
            }else{
                $this->output('操作失败!', 'user/userEdit');
            }
        }
        
        //非提交处理
        $user = new \Admin\Model\UserModel();
        $result = $user->getUserRank($uid);
        $result['code'] =  json_decode($result['code']);
        $this->assign('vinfo', $result);
        $groups = new \Admin\Model\SysusergroupModel();
        $rstGroup= $groups->getAllGroup();
        $this->assign('groupslist',$rstGroup);
        $userrank = parent::getMenuList();
        $this->assign('userrank', $userrank);
        $this->display('userrank');
    }
    
    //删除 记录
    public function userDels(){
        $gid = I('get.id', 0, 'int');
        if($gid) {
            $delete    = new \Admin\Model\UserModel();
            $result = $delete -> delData($gid);
            if($result) {
                //如成功删除则删除该用户权限管理
                $m_staffauth = new \Admin\Model\StaffauthModel();
                $staffauth_info = $m_staffauth->getInfo($gid);
                if($staffauth_info)$m_staffauth->delData($staffauth_info['id']);
                $this->output('删除成功', 'user/userList',2);
            } else {
                $this->output('删除失败', 'user/userList',2);
            }
        } else {
            $this->error('删除失败,缺少参数!');
        }
    }

    //ajax获取当前角色下的权限
    public function currentRank(){
        $moduleList = '当前模块出错,请联系管理员!';
        $gid = I('gid', '', 'int');
        if($gid) {
            $AllModule = new \Admin\Model\SysmenuModel();
            $parentModule = $AllModule->getAllList();
            $currenModule= new \Admin\Model\SysusergroupModel();
            $getInfo = $currenModule->getInfo($gid);
            $childeModule = !empty($getInfo['code'])? json_decode($getInfo['code']) : array();            
            //当前用户的栏目和所有的进行对比， 判断是否为选中栏目
            if($parentModule && $childeModule) {
                foreach ($parentModule as $key => $vv){
                    foreach ($childeModule as $k => $v) {
                        if($vv['code'] == $v){
                            $check="checked=checked";
                            break;
                        }else{
                            $check="";
                        }
                    }
                    $list .= '<div class="col-xs-4 col-sm-3 col-md-2"><input class="disabled" name="rank[]" value="'.$vv['code'].'"  type="checkbox" '.$check.' disabled="true" >'.$vv['modulename'].'</div>';
                }
                $moduleList = $list;
                echo $moduleList;
            }else{
                echo $moduleList;
            }
        }else{
            echo $moduleList;
        }
    }
}