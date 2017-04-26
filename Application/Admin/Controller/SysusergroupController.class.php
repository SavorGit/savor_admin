<?php
namespace Admin\Controller;
/**
 * @desc 系统菜单管理类
 *
 */
use Common\Lib\Tree;
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



    //新增用户测试
    public function sysusergroupAddTest(){
        $sysNode = new \Admin\Model\SysnodeModel();
        $rolePrivModel = new \Admin\Model\RolePrivModel();
        $sysusergroup = new \Admin\Model\SysusergroupModel();
        $acttype = I('acttype', 0, 'int');
        $name = I('post.name');
        //处理提交数据
        if(IS_POST) {
            //新增
            $id   = I('post.id', '', 'int');
            if($acttype == 0){
                $userInfo = session('sysUserInfo');
                $username = $userInfo['username'];
                $data['userName']= $username;
                $data['createtime']= date("Y-m-d H:i:s");
                $data['name']   = $name;
                $result = $sysusergroup->addData($data, $acttype);
                $roleid = $sysusergroup->getLastInsID();
            }elseif($acttype == 1){
                //删除已经存在的
                $roleid = $id;
            }
            if (is_array($_POST['menuid']) && count($_POST['menuid']) > 0) {
                $rolePrivModel->delData($roleid);
                $menuinfo = $sysNode->field('`id`,`m`,`c`,`a`,`menulevel`')->select();

                foreach ($menuinfo as $_v) $menu_info[$_v[id]] = $_v;

                foreach($_POST['menuid'] as $menuid){
                    $info = array();
                    $info = $rolePrivModel->get_menuinfo(intval($menuid),$menu_info);
                    $info['nodeid'] = intval($menuid);
                    $info['roleid'] = $roleid;
                    $rolePrivModel->add($info);
                }

               $this->output('操作成功','sysusergroup/sysusergroupList');
            }else{
                $rolePrivModel->delData($roleid);
            }
        }

        //非提交处理
        if(1 === $acttype) {
            $gid = I('id', 0, 'int');
            if(!$gid) {
                $this->output('当前信息不存在!', 'sysusergroupList');
            }
            $resulta = $sysusergroup->getInfo($gid);
            $this->assign('vinfo', $resulta);
            $this->assign('acttype', 1);
        } else {
            $this->assign('acttype', 0);
        }
        //获取树形结构
        $matre = new Tree();
        $matre->icon = array('│ ','├─ ','└─ ');
        $matre->nbsp = '&nbsp;&nbsp;&nbsp;';
        //获取所有节点
        $result = $sysNode->getAllList();
        //获取权限表数据
        $priv_data = $rolePrivModel->getInfoByroleid($gid);
        var_dump($priv_data);
        echo '<hr/><hr/>';
        foreach ($result as $n=>$t) {
            var_dump($t);

            $result[$n]['cname'] = $t['name'];
            $result[$n]['checked'] = $rolePrivModel->is_checked($t,$gid,$priv_data)? ' checked' : '';
            $result[$n]['level'] = $rolePrivModel->get_level($t['id'],$result);
            $result[$n]['parentid_node'] = ($t['parentid'])? ' class="child-of-node-'.$t['parentid'].'"' : '';
            die;

        }


        $str  = "<tr id='node-\$id' \$parentid_node>
							<td style='padding-left:30px;'>\$spacer<input type='checkbox' name='menuid[]' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'> \$cname</td>
						</tr>";
        $matre->init($result);
        //var_dump($priv_data);
        //var_dump($nodelist);
//echo $str;
        $categorys = $matre->get_tree(0, $str);
        //echo $categorys;
        $ra['temp'] = $categorys;
        $groupList = parent::getMenuList();
        $this->assign('categor', $ra);
        //$this->assign('groupList', $groupList);
        $this->display('sysusergroupaddtest');
    }

    //删除 记录
    public function sysusergroupDel() {
        $gid = I('get.id', 0, 'int');
        if($gid) {
            $delete    = new \Admin\Model\SysusergroupModel();
            $rolePrivModel = new \Admin\Model\RolePrivModel();
            $rolePrivModel->delData($gid);
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