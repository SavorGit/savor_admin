<?php
namespace Admin\Controller;
class IndexController extends BaseController {
    public function index(){
        $m_sysmenu = new \Admin\Model\SysmenuModel();
        $menuList = $this->getMyMenuList();
        $version = $m_sysmenu->getMysqlVersion();
        $firstMenu = $m_sysmenu->where('menulevel=0')->select();
        $mediaModel =  new \Admin\Model\MediaModel();
        $ico_arr = array();
        foreach($firstMenu as $key=> $v){
            if($v['select_media_id']){
                $mediainfo = $mediaModel->getMediaInfoById($v['select_media_id']);
                $ico_arr[$key]['img_url'] = $mediainfo['oss_addr']; 
                $ico_arr[$key]['id'] = $v['id'];
            }
        }
        $this->assign('ico_arr',$ico_arr);
        $this->assign('menuList', $menuList);
        $this->assign('VerMysql', $version);
        $this->assign('VerPHP', PHP_VERSION);
        $this->display();
    }

    //记录日志
    public function receiveLogs($val, $username){
        $path = '/tmp/savor_logs';
        $time = date('Y-m-d h:i:s',time());
        $file = $path."/".date('Y-m-d',time()).".log";
        if ( !(is_dir($path)) ) {
            mkdir ( $path, 0777, true );
        }

        $fp = fopen($file,"a+");

        $content ="";
        $start="time:".$time."\r\n"."username:".$username."\r\n"."---------- content start ----------"."\r\n";
        $end ="\r\n"."---------- content end ----------"."\r\n\n";
        $content=$start."".$val."".$end;
        fwrite($fp,$content);
        fclose($fp);
    }
    
    //获取当前用户管理栏目
    private function getMyMenuList(){
        $host_name=$this->host_name();
        $userInfo = session('sysUserInfo');
        $username = $userInfo['username'];
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
            $mediaModel = new \Admin\Model\MediaModel();
            foreach ($menu as $k => $v) {
                
                $mediainfo = $mediaModel->getMediaInfoById($v['media_id']);
                $parent_s = '<li  id="'.$v['id'].'"><a href="#menu'.$k.'" data-index="'.$k.'"><img class="menulist_ico" style="height:14px;margin-right: 5px;" src="'.$mediainfo['oss_addr'].'">'.$v['modulename']
                            .'<img style="float:right;margin-right:4px;margin-top:7px" src="/Public/admin/assets/img/sysmenuico/more.png" /></a><ul id="menu'
                            .$k.'" class="collapse in"><input type="hidden" id="mo_'.$v['id'].'" value="'.$mediainfo['oss_addr'].'"  />';
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
            $this->receiveLogs($myMenuList, $username);

            return $myMenuList;
        }
    }
}