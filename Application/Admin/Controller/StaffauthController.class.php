<?php
namespace Admin\Controller;
/**
 * @desc 用户管理类
 *
 */
class StaffauthController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function staffauthList() {
        $staffauth  = new \Admin\Model\StaffauthModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $start  = ( $start-1 ) * $size;
        $result = $staffauth->getList($start, $size);
        
        $this->assign('staffauthlist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('index');
        
    }
    
    //新增用户
    public function staffauthAdd(){
        $acttype = I('acttype', 0, 'int');
        
        //处理提交数据
        if(IS_POST) {
            //获取参数
            $staffauthId  = I('id', '', 'int');
            $remark  = I('remark');
            $staffauthname= I('staffauthname');
            $staffauthpwd = I('staffauthpwd');
            $status  = I('status', 1, 'int');
            
            //判断添加
            if($remark && $staffauthname) {
                $data['id']   = $staffauthId;
                $data['remark']   = $remark;
                $data['staffauthname'] = $staffauthname;
                $data['status'] = $status;
                $data['password'] = md5($staffauthpwd);
                $staffauth = new \Admin\Model\StaffauthModel();
                $result = $staffauth->addData($data, $acttype);
                if($result) {
                    $this->output('操作成功!', 'staffauth/staffauthList');
                } else {
                    $this->output('操作失败!', 'staffauth/staffauthAdd', 2, 0);
                }
            } else {
                $this->output('缺少必要参数!', 'staffauth/staffauthAdd', 2, 0);
            }
        }
        
        //非提交处理
        if(1 === $acttype) {
            $uid = I('id', 0, 'int');
            if(!$uid) {
                $this->output('当前用户不存在!', 'staffauth/staffauthList');
            }
            
            $staffauth = new \Admin\Model\StaffauthModel();
            $result = $staffauth->getstaffauthInfo($uid);
            $this->assign('vinfo', $result);
            $this->assign('acttype', 1);
        } else {
            $this->assign('acttype', 0);
        }
        $this->display('staffauth/staffauthadd');
    }
    
    //用户修改密码
    public function staffauthEdit(){
        $acttype = I('acttype', 1, 'int');
        $uid = I('uid', 0, 'int');
        if(IS_POST) {
            //获取参数
            $staffauthId  = I('id', '', 'int');
            $remark  = I('remark');
            $newstaffauthpwd = I('newstaffauthpwd');
            $status  = I('status', 1, 'int');
            
            if($staffauthId && $remark) {
                $data['id']   = $staffauthId;
                $data['remark']   = $remark;
                $data['status'] = $status;
                if($newstaffauthpwd){
                    $data['password'] = md5($newstaffauthpwd);
                }
                
                $staffauth = new \Admin\Model\StaffauthModel();
                $result = $staffauth->addData($data, $acttype);
                if($result) {
                    $this->output('操作成功!', 'staffauth/staffauthList');
                } else {
                    $this->output('操作失败!', 'staffauth/staffauthEdit');
                }
            } else {
                $this->output('缺少必要参数!', 'staffauth/staffauthEdit');
            }
            
        }
        //非提交处理
        $staffauth = new \Admin\Model\StaffauthModel();
        $result = $staffauth->getstaffauthInfo($uid);
        $this->assign('vinfo', $result);
        $this->display('staffauthedit');
    }
    
    //删除 记录
    public function staffauthDel() {
        $gid = I('get.id', 0, 'int');
        if($gid) {
            $delete    = new \Admin\Model\StaffauthModel();
            $result = $delete -> delData($gid);
            if($result) {
                $this->output('删除成功', 'staffauth/staffauthList',2);
            } else {
                $this->output('删除失败', 'staffauth/staffauthList',2);
            }
        } else {
            $this->error('删除失败,缺少参数!');
        }
    }
}