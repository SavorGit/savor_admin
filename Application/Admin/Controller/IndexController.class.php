<?php
namespace Admin\Controller;
class IndexController extends BaseController {
    public function index(){
        $m_sysmenu = new \Admin\Model\SysmenuModel();
        $menuList = $this->getMyMenuList();
        $version = $m_sysmenu->getMysqlVersion();
        $this->assign('menuList', $menuList);
        $this->assign('VerMysql', $version);
        $this->assign('VerPHP', PHP_VERSION);
        $this->display();
    }
    
    //获取当前用户管理栏目
    private function getMyMenuList(){
        $host_name=$this->host_name();
        $userInfo = session('sysUserInfo');
        $uid = $userInfo['id'];
        $moudMenu = new \Admin\Model\SysmenuModel();
        $getList  = $moudMenu->getAllList();
        $myMenu = new \Admin\Model\StaffauthModel();
        $getMyList= $myMenu->getInfo($uid);
        
        if($getList && $getMyList) {
            $menu = $menu_child = array();
            foreach ($getList as $key => $v){
                $nodekey = $v['nodekey'];
                if($v['menulevel'] == 0){
                    $menu[$nodekey]=$v;
                }else{
                    $menu_child[$nodekey][]=$v;
                }
                if(isset($menu_child[$nodekey])){
                    $menu[$nodekey]['child']=$menu_child[$nodekey];
                }else{
                    $menu[$nodekey]['child']=array();
                }
            }
            $myList = json_decode($getMyList['code']);
            $myMenuList = '';
            foreach ($menu as $k => $v) {
                $parent_s = '<li><a href="#menu'.$k.'" data-index="'.$k.'"><i class="icon-folder"></i> '.$v['modulename'].'</a><ul id="menu'.$k.'" class="collapse in">';
                $child = '';
                if($v['child']) {
                    $menu_list = '';
                    foreach ($v['child'] as $key => $vv){
                        $display='';
                        if($vv['id']==21){
                            $display='style="display:none;"';
                        }
                        $flag = false;
                        if(in_array($vv['code'], $myList)){
                            $flag = true;
                        }
                        if($flag) {
                            $back_url = str_replace('.', '/', $vv['jstext']);
                            $child .= '<li '.$display.'><a href="admin/'.$back_url.'" target="navTab" rel="'.$back_url.'">'.$vv['modulename'].'</a></li>';
                        }else{
                            $child .= '<li disabled ><a>'.$vv['modulename'].'</a></li>';
                        }
                    }
                }
                $parent_e = '</ul></li>';
                $menu_list = $parent_s.$child.$parent_e;
                $myMenuList .= $menu_list;
            }
            return $myMenuList;
        }
    }
}