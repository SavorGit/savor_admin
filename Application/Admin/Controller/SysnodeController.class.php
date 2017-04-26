<?php
namespace Admin\Controller;
/**
 * @desc 系统菜单管理类
 *
 */
class SysnodeController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function manager() {

        $sysMenu  = new \Admin\Model\SysmenuModel();
        $sysNode = new \Admin\Model\SysnodeModel();
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
        $searchClass=I('searchCode');
        if($searchClass) {
            $where .= " and nodekey = '$searchClass'";
            $this->assign('searchCode',  $searchClass);
        }

        $result = $sysNode->getList($where,$orders, $start, $size);
        $this->assign('sysmenulist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('index');
    }
    
    //新增用户
    public function sysnodeadd(){


        $sysMenu = new \Admin\Model\SysmenuModel();
        $sysNode = new \Admin\Model\SysnodeModel();
        $acttype = I('acttype', 0, 'int');
        //处理提交数据
        if(IS_POST) {
            //获取参数
            $id          = I('post.id', '', 'int');
            $sysmenuid          = I('post.sysmenuid', '', 'int');
            $nodekey     = I('post.nodekey');
            $modulename  = I('post.modulename','','trim');
            $menulevel   = I('post.menulevel');
            $displayorder= I('post.displayorder');
            $code        = I('post.code');
            $jstext      = I('post.jstext');
            $isenable    = I('post.isenable');
            $media_id    = I('post.media_id','0','intval');
            $select_media_id    = I('post.select_media_id','0','intval');
            $mfield      =   strtolower(I('post.mfield','','trim'));
            $cfield      = strtolower(I('post.cfield','','trim'));
            $afield      = strtolower(I('post.afield','','trim'));
            $nodeparm['m'] = $mfield;
            $nodeparm['c'] = $cfield;
            $nodeparm['a'] = $afield;
            $nodeparm['isenable'] = $isenable;
            //三级节点只添加node表
            if ($menulevel == 2 ) {
                //u新增
                $nodeparm['menulevel'] = $menulevel;
                $nodeparm['isenable'] = 1;
                $nodeinfo = $sysNode->getInfo($nodeparm);
                $nodeparm['parentid'] = I('post.secid');
                $nodeparm['displayorder'] = $displayorder;
                $nodeparm['nodekey']= $nodekey;
                $nodeparm['name'] = $modulename;
                if( $acttype == 0) {
                    if ($nodeinfo) {
                        $this->error('不能新增相同三级节点');
                    } else {
                        $result = $sysNode->add($nodeparm);
                    }

                } else {
                    $nodeparm['isenable'] = $isenable;
                    if ($nodeinfo) {
                        $nid = $nodeinfo['id'];
                        $result = $sysNode->where("id={$nid}")->save($nodeparm);
                    } else {
                        $result = $sysNode->add($nodeparm);
                    }
                }
                if($result) {
                    $this->output('操作成功!', 'sysnode/manager');
                } else {
                    $this->output('操作失败!', 'sysmenuAdd', 2, 0);
                }
            }

            if($acttype == 0 ){
                $ojt['isenable'] = 1;
                $ojt['jstext'] = $jstext;
                $count = $sysMenu->getCount($ojt);
                if($count>0){
                    $this->error('一级节点二级节点不允许添加相同已经存在且有效的jstext');
                }
                if($menulevel == 0){
                    $oat['nodekey'] = $nodekey;
                    $oat['menulevel'] = 0;
                    $oat['isenable'] = 1;
                    $count = $sysMenu->getCount($oat);
                    if($count>0){
                        $this->error('一级节点在不被禁止的情况下只能有一 个');
                    }
                }
            }
            //加限制不能添加相同jstext
            $castr = strtolower($cfield.'.'.$afield);
            if($castr != strtolower($jstext) ){
                $this->error('jstext与cfield,afiled必须保持一致');
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
                if($media_id) $data['media_id'] = $media_id;
                
                if($select_media_id) $data['select_media_id'] = $select_media_id;

                if(0 === $acttype) {
                    $result = $sysMenu->add($data);
                    $id = $sysMenu->getLastInsID();
                } else {
                    $data['id'] = $sysmenuid;
                    $id = $sysmenuid;

                    $res = $sysMenu->where("id={$id}")->save($data);
                }
                $nodeparm['sysmenuid'] = $id;
                $nodeparm['menulevel'] = $menulevel;
                $nodeparm['isenable'] = 1;
                $nodeinfo = $sysNode->getInfo($nodeparm);
                $nodeparm['isenable'] = $isenable;
                $nodeparm['displayorder'] = $displayorder;
                $nodeparm['nodekey']= $nodekey;
                $nodeparm['name'] = $modulename;
                if($media_id) $nodeparm['media_id'] = $media_id;

                if($select_media_id) $nodeparm['select_media_id'] = $select_media_id;
                if ($nodeinfo) {
                    $nid = $nodeinfo['id'];
                    $result = $sysNode->where("id={$nid}")->save($nodeparm);
                } else {
                    //二级节点

                    if($menulevel == 1){
                        $pat['nodekey'] = $nodekey;
                        $pat['parentid'] = 0;
                        $res = $sysNode->getInfo($pat);
                        $menulevel = $res['id'];
                    }
                    $nodeparm['parentid'] = $menulevel;
                    $nodeparm['displayorder'] = $displayorder;
                    $nodeparm['isenable'] = $isenable;
                    $result = $sysNode->add($nodeparm);
                }
                if($result) {
                    $this->output('操作成功!', 'sysnode/manager');
                } else {
                    $this->error('操作失败!');
                }
            } else {
                $this->error('缺少必要参数!');
            }
        }
        
        //非提交处理
        if(1 === $acttype) {
            $uid = I('id', 0, 'int');
            $sysmenuid = I('sysmenuid', 0, 'int');
            $result['sysmenuid'] = $sysmenuid;
            if(!$uid) {
                $this->error('当前信息不存在!');
            }
            //$result = $sysMenu->getInfo($uid);
            $result = $sysNode->getoneInfo($uid);
            if($result['media_id']){
               
                $mediaModel = new \Admin\Model\MediaModel();
                $mediaInfo = $mediaModel->getMediaInfoById($result['media_id']);
                $result['oss_addr'] = $mediaInfo['oss_addr'];
            }
            if($result['select_media_id']){
                $mediaModel = new \Admin\Model\MediaModel();
                $mediaInfo = $mediaModel->getMediaInfoById($result['select_media_id']);
                $result['select_oss_addr'] = $mediaInfo['oss_addr'];
            }
            //要改回来的
            $result['jstext'] = $result['c'].'.'.$result['a'];
            $result['mfield'] = MODULE_NAME;
            $result['cfield']  = $result['c'];
            $result['afield']  = $result['a'];
            $this->assign('vinfo', $result);
            $this->assign('acttype', 1);
        } else {
            $res['mfield'] = MODULE_NAME;
            $this->assign('vinfo',$res);
            $this->assign('acttype', 0);
        }
        $this->display('sysnodeadd');
    }

    //获取信息
    public function getnodeinfo(){
        $sysNode = new \Admin\Model\SysnodeModel();
        $nodekey = I('post.nokey','','trim');
        $field = "id,name";
        $where = "1=1 AND nodekey = '{$nodekey}'";

        $wherea = $where;
        $where .= " AND parentid = 0";
        $result = $sysNode->getWhere($where, $field);

        $aid = $result[0]['id'];
        $wherea .= " AND parentid = $aid";
        $result = $sysNode->getWhere($wherea, $field);
        //var_dump($result);
        echo json_encode($result);
        die;
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