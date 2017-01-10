<?php
namespace Admin\Controller;
/**
 * @desc 系统菜单管理类
 *
 */
class SysusergroupController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function sysusergroupList() {
        $sysusergroup  = new \Admin\Model\SysusergroupModel();
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
            $where .= " and name like '%$searchTitle%'";
            $this->assign('searchTitle',  $searchTitle);
        }
        
        $result = $sysusergroup->getList($where, $orders, $start, $size);
        
        $this->assign('sysusergrouplist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('index');
    }
    
    //新增用户
    public function sysusergroupAdd(){
        $acttype = I('acttype', 0, 'int');
        
        //处理提交数据
        if(IS_POST) {
            //获取参数
            $id   = I('post.id', '', 'int');
            $name = I('post.name');
            $rank = I('post.rank');
            $code = json_encode($rank);
            $pid  = I('post.pid', 0, 'int');
            
            //判断添加
            if($name && $rank) {
                if(!empty($id)) $data['id'] = $id;
                $data['name']   = $name;
                $data['code']   = $code;
                $data['pid']    = $pid;
                $userInfo = session('sysUserInfo');
                $username = $userInfo['username'];
                $data['userName']= $username;
                $data['createtime']= date("Y-m-d H:i:s");
                
                $sysusergroup = new \Admin\Model\SysusergroupModel();
                $result = $sysusergroup->addData($data, $acttype);
                if($result) {
                    //修改分组对应下的用户权限
                    $m_user = new \Admin\Model\UserModel();
                    $m_user->modifyUserRankByGroupid($id, $code);
                    
                    $this->output('操作成功!', 'sysusergroup/sysusergroupList');
                } else {
                    $this->output('操作失败!', 'sysusergroupAdd', 2, 0);
                }
            } else {
                $this->output('缺少必要参数!', 'sysusergroupAdd', 2, 0);
            }
        }
        
        //非提交处理
        if(1 === $acttype) {
            $uid = I('id', 0, 'int');
            if(!$uid) {
                $this->output('当前信息不存在!', 'sysusergroupList');
            }
            
            $sysusergroup = new \Admin\Model\SysusergroupModel();
            $result = $sysusergroup->getInfo($uid);
            $result['code'] = json_decode($result['code']);
            $this->assign('vinfo', $result);
            $this->assign('acttype', 1);
        } else {
            $this->assign('acttype', 0);
        }
        
        $groupList = parent::getMenuList();
        $this->assign('groupList', $groupList);
        $this->display('sysusergroupadd');
    }
    
    
    //删除 记录
    public function sysusergroupDel() {
        $gid = I('get.id', 0, 'int');
        if($gid) {
            $delete    = new \Admin\Model\SysusergroupModel();
            $result = $delete -> delData($gid);
            if($result) {
                $this->output('删除成功', 'sysusergroupList',2);
            } else {
                $this->output('删除失败', 'sysusergroupList',2);
            }
        } else {
            $this->error('删除失败,缺少参数!');
        }
    }
}