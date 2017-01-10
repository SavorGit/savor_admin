<?php
namespace Admin\Controller;
/**
 * @desc 系统日志记录类
 *
 */
class SyslogController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $sysMenu = new \Admin\Model\SysmenuModel();
        $result = $sysMenu->getList($where="where `menulevel`=1  ", 'id desc', $start=0,$size=500);
        $this->assign('classList',  $result['list']);
    }
    
    public function SyslogList() {
        $syslog  = new \Admin\Model\SyslogModel();
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
            $where = " where CONCAT(`opprate`,`program`,`logtime`) LIKE '%$searchTitle%'";
            $this->assign('searchTitle',  $searchTitle);
        }
        
        $shwcid=I('shwcid');
        if($shwcid) {
            $where .= " and actionid = '$shwcid'";
            $this->assign('shwcid',  $shwcid);
        }
        $result = $syslog->getList($where, $orders, $start, $size);
        foreach ($result['list'] as $k => $v){
            $action_name = $this->actionName($v['actionid'], 1);
            $result['list'][$k]['loginid'] = $this->getUserName($v['loginid']);
            $result['list'][$k]['actionid'] = $this->actionName($v['actionid'], 1);
            $tabName = $this->actionName($v['actionid'], 2);
            $result['list'][$k]['program'] = $this->programName($v['program'], $tabName,$action_name);
        }
        $this->assign('sysloglist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('index');
    }
    
    //删除 记录
    public function syslogDel() {
        $gid = I('get.id', 0, 'int');
        if($gid) {
            $delete    = new \Admin\Model\SyslogModel();
            $result = $delete -> delData($gid);
            if($result) {
                $this->output('删除成功', 'syslogList',2);
            } else {
                $this->output('删除失败', 'syslogList',2);
            }
        } else {
            $this->error('删除失败,缺少参数!');
        }
    }
    
    //获取用户名称
    public function getUserName($UserId){
        $user = new \Admin\Model\UserModel();
        $result = $user->getUserInfo($UserId);
        return $result['username'];
    }
    
    //获取当前栏目名称
    public function actionName($actionId, $type){
        if(is_numeric($actionId)){
            $sysMenu = new \Admin\Model\SysmenuModel();
            $result = $sysMenu->getInfo($actionId);
            if($type == 1){
                return $result['modulename'];
            }else{
                return $result['code'];
            }
            
        }else{
            return $actionId;
        }
    }
    //获取当前栏目对应列表名称
    public function programName($programId, $tabName,$action_name){
        $programName  = $programId;
        if(is_numeric($programId)) {
            $tabName = strchr($tabName, '.', true);
            $is_table = M()->query("SHOW TABLES LIKE '%savor_$tabName%'");
            if($is_table){
                $progam = M("$tabName");
                $vinfo = $progam->find($programId);
                $programName = $vinfo['title'];
            }else{
                $programName = $action_name;
            }
        }
        return $programName;
    }
}