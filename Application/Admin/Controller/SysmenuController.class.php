<?php
namespace Admin\Controller;
/**
 * @desc 系统菜单管理类
 *
 */
class SysmenuController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function sysmenuList() {
        $sysMenu  = new \Admin\Model\SysmenuModel();
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
            $where .= " and modulename like '%$searchTitle%'";
            $this->assign('searchTitle',  $searchTitle);
        }
        $searchClass=I('searchCode');
        if($searchClass) {
            $where .= " and nodekey = '$searchClass'";
            $this->assign('searchCode',  $searchClass);
        }
        
        $result = $sysMenu->getList($where,$orders, $start, $size);
        $this->assign('sysmenulist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('index');
    }
    
    //新增用户
    public function sysmenuAdd(){
        $acttype = I('acttype', 0, 'int');
        
        //处理提交数据
        if(IS_POST) {
            //获取参数
            $id          = I('post.id', '', 'int');
            $nodekey     = I('post.nodekey');
            $modulename  = I('post.modulename');
            $menulevel   = I('post.menulevel');
            $displayorder= I('post.displayorder');
            $code        = I('post.code');
            $jstext      = I('post.jstext');
            $isenable    = I('post.isenable');
            $media_id    = I('post.media_id');
            $sysMenu = new \Admin\Model\SysmenuModel();
            if($acttype==1){
                $sysinfo=$sysMenu->getInfo($id);
            }
            //判断添加
            if($modulename) {
                $data['id']     = $id;
                $data['code']   = $code;
                $data['jstext'] = $jstext;
                $data['nodekey']= $nodekey;
                $data['modulename']= $modulename;
                $data['menulevel']  = $menulevel;
                $data['displayorder']= $displayorder;
                $data['isenable']   = $isenable; 
                $data['media_id'] = $media_id;
                $result = $sysMenu->addData($data, $acttype);
                if($result) {
                    $this->output('操作成功!', 'sysmenu/sysmenuList');
                } else {
                    $this->output('操作失败!', 'sysmenuAdd', 2, 0);
                }
            } else {
                $this->output('缺少必要参数!', 'sysmenuAdd', 2, 0);
            }
        }
        
        //非提交处理
        if(1 === $acttype) {
            $uid = I('id', 0, 'int');
            if(!$uid) {
                $this->output('当前信息不存在!', 'sysmenuList');
            }
            
            $sysMenu = new \Admin\Model\SysmenuModel();
            $result = $sysMenu->getInfo($uid);
            if($result['media_id']){
                $oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
                
                $mediaModel = new \Admin\Model\MediaModel();
                $mediaInfo = $mediaModel->getMediaInfoById($result['media_id']);
                $result['oss_addr'] = $oss_host.$mediaInfo['oss_addr'];
            }
           
            $this->assign('vinfo', $result);
            $this->assign('acttype', 1);
        } else {
            $this->assign('acttype', 0);
        }
        $this->display('sysmenuadd');
    }
    
    
    //删除 记录
    public function sysmenuDel() {
        $gid = I('get.id', 0, 'int');
        if($gid) {
            $delete    = new \Admin\Model\SysMenuModel();
            $result = $delete -> delData($gid);
            if($result) {
                $this->output('删除成功', 'sysmenuList',2);
            } else {
                $this->output('删除失败', 'sysmenuList',2);
            }
        } else {
            $this->error('删除失败,缺少参数!');
        }
    }
}