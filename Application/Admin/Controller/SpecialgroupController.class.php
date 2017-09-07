<?php
namespace Admin\Controller;

/**
 *@desc 专题组控制器,对专题组添加或者修改
 * @Package Name: SpecialgroupController
 *
 * @author      白玉涛
 * @version     3.0.1
 * @copyright www.baidu.com
 */
use Admin\Controller\BaseController;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;

class SpecialgroupController extends BaseController {

    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->oss_host = get_oss_host();
    }


    /*
	 * @desc 根据文章名称获取文章列表
     * @method getArticleByname
     * @access public
     * @http GET
     * @param tagname 文章名称
     * @return void
	 */
    public function getArticleByname(){
        $sname = I('request.tagname','','trim');
        if($sname){
            $artModel = new \Admin\Model\ArticleModel();
            $field = 'id,title name,img_url';
            $map['title'] = array('like','%'.$sname.'%');
            $map['state'] = 2;
            $map['_string'] = '((bespeak=1 or bespeak=2) and
            bespeak_time<NOW()) or bespeak=0 or bespeak=null';
            $result = $artModel->getWhere($map, $field);
            $oss_host = $this->oss_host;
            foreach($result as $sk=>$sv) {
                $result[$sk]['img_url'] = $oss_host.$result[$sk]['img_url'];
            }

        } else {
            $result = array();
        }
        echo json_encode($result);
    }

    /*
	 * @desc 逻辑删除专题组
     * @method delsp
     * @access public
     * @http GET
     * @param sgid 专题组id
     * @return void
	 */

    public function delSpGroup(){
        $sgid = I('request.sgid','0','intval');
        $spModel = new \Admin\Model\SpecialGroupModel();
        $now_time = date('Y-m-d H:i:s');
        $spinfo = $spModel->find($sgid);
        if($spinfo['state'] == 1) {
            $message = '已发布的内容不可删除';
            $this->error($message);
        }
        $binfo = array(
            'update_time'=>$now_time,
            'state'=>2,
        );
        $res = $spModel->saveData($binfo, array('id'=>$sgid));
        if ($res) {
            $message = '删除成功';
            $this->output($message, 'specialgroup/homemanager',2);
        } else {
            $message = '操作失败';
            $this->error($message);
        }
    }


    /*
	 * @desc 修改专组发布布状态
     * @method operateStatus
     * @access public
     * @http GET
     * @param sgid 专题组id
     * @param flag 状态标识
     * @return void
	 */
    public function operateStatus(){
        $sgid = I('request.sgid','0','intval');
        $flag = I('request.flag','0','intval');
        if(0 == $flag){
            $state = 1;
        } else if(1 == $flag){
            $state = 0;
        }
        $spModel = new \Admin\Model\SpecialGroupModel();
        $now_time = date('Y-m-d H:i:s');
        $binfo = array(
            'update_time'=>$now_time,
            'state'=>$state,
        );
        $res = $spModel->saveData($binfo, array('id'=>$sgid));
        if ($res) {
            $message = '更新审核状态成功';
            $this->output($message, 'specialgroup/homemanager',2);
        } else {
            $message = '操作失败';
            $this->error($message);
        }
    }

    /**
     * @desc 专题组列表
     * @method homemanager
     * @access public
     * @http post
     * @param numPerPage intger 显示每页记录数
     * @param pageNum intger 当前页数
     * @param _order $record 排序
     * @return void|array
     */
    public function homemanager(){

        $spgModel = new \Admin\Model\SpecialGroupModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','sg.create_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $orderas = 'sg.update_time desc';
        $start  = ( $start-1 ) * $size;
        $where = "1=1 and sg.state != 2";
        $name = I('sgroupname');
        if ($name) {
            $this->assign('sgname', $name);
            $where .= " and sg.name like '%".$name."%' ";
        }
        $join = 1;
        $result = $spgModel->getList($join, $where, $orders,$start,$size);
        $resulta = $spgModel->getList($join, $where, $orderas,$start,$size);
        $sg_state = C('SP_GR_STATE');
        array_walk($result['list'], function(&$v, $k)use($sg_state){
            $st_num = $v['state'];
            if (array_key_exists($st_num, $sg_state)){
                $v['pub'] = $sg_state[$st_num];
            }
        });
        $is_home = array();
        foreach($resulta['list'] as $rs=>$rv) {
            if($rv['state'] == 1) {
                $spid = $rv['id'];
                break;
            }
        }
        foreach($result['list'] as $rs=>$rv) {
            if($rv['id'] == $spid) {
                $result['list'][$rs]['is_index'] = 1;
                break;
            }
        }
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('homesgroup');
    }


    /**
     * @desc 处理专题组错误提示
     * @method processTips
     * @access public
     * @http NULL
     * @param $save array 得到的数组
     * @return void|array
     */
    public function processTips($save) {
        if ($save['name']) {
            if ( mb_strlen($save['name']) >= 2 && mb_strlen($save['name']) <= 12) {

            } else {
                $errmsg = '专题名称字数不符';
                $this->error($errmsg);
            }
        } else {
            $errmsg = '专题名称不可为空';
            $this->error($errmsg);
        }

        if ($save['title']) {
            if ( mb_strlen($save['title']) > 50) {
                $errmsg = '标题字符限制为50';
                $this->error($errmsg);
            }
        } else {
            $errmsg = '标题名称不可为空';
            $this->error($errmsg);
        }


        if ($save['desc']) {
            if ( mb_strlen($save['desc']) > 200) {
                $errmsg = '描述字符限制为200';
                $this->error($errmsg);
            }
        } else {
            $errmsg = '专题简介未填';
            $this->error($errmsg);
        }

    }


    /**
     * @desc 处理专题组关系
     * @method processrelation
     * @access public
     * @http NULL
     * @param $save array 得到的数组
     * @return void|array
     */
    public function processRelation($spgroupModel, $sgid, $sp_relation_arr) {
        //根据标签遍历
        $num = 0;
        $sp = array();
        $spRelation = new \Admin\Model\SpecialGroupRelationModel();
        foreach($sp_relation_arr as $sv) {
            foreach ($sv as $gk=>$gv) {
                if('scontent' == $gk) {
                    $sp[$num] = array(
                        'sgid' => $sgid,
                        'sgtype' => 1,
                        'stext' => $gv,
                        'sarticleid' => 0,
                        'spictureid' => 0,
                        'stitle' => '',
                    );
                } else if('sarticle' == $gk) {
                    $sp[$num] = array(
                        'sgid' => $sgid,
                        'sgtype' => 2,
                        'stext' => '',
                        'sarticleid' => $gv,
                        'spictureid' => 0,
                        'stitle' => '',
                    );
                } else if('spid' == $gk) {
                    $sp[$num] = array(
                        'sgid' => $sgid,
                        'sgtype' => 3,
                        'stext' => '',
                        'sarticleid' => 0,
                        'spictureid' => $gv,
                        'stitle' => '',
                    );
                } else if('stitle' == $gk) {
                    $sp[$num] = array(
                        'sgid' => $sgid,
                        'sgtype' => 4,
                        'stext' => '',
                        'sarticleid' => 0,
                        'spictureid' => 0,
                        'stitle' => $gv,
                    );
                }  else {

                }
            }
            $num++;
        }
        $resp = $spRelation->addAll($sp);
        return $resp;
    }


    /**
     * @desc 处理专题组添加与编辑的处理过程
     * @method processGroup
     * @access public
     * @http NULL
     * @param $stype int  1添加2编辑
     * @return void|array
     */
    public function processGroup($stype) {
        $spgroupModel = new \Admin\Model\SpecialGroupModel();

        $save['title']        = I('post.zttitle','','trim');                $save['name']        = I('post.ztname','','trim');
        $save['desc']        = I('post.zt-synopsis','','trim');
        $save['title'] = htmlspecialchars(strip_tags($save['title']));
        $save['name'] = htmlspecialchars(strip_tags($save['name']));
        $save['desc'] = htmlspecialchars(strip_tags($save['desc']));
        //处理错误提示
        $this->processTips($save);

        $sp_relation_arr  = I('post.addspgroup','','trim');
        $sp_relation_arr = json_decode($sp_relation_arr, true);
        if (count($sp_relation_arr)<5) {
            $this->error('专题组内容不可少于5条');
        }

        $mediaid = I('post.media_id','0','intval');
        if ($stype == 1) {
            //添加
            $spcar['name'] = $save['name'];
            $spcar['state'] = array('neq',2);
            $retar = $spgroupModel->getOneRow($spcar);
            if($retar){
                $this->error('失败专题组名称已存在!');
            }

            $save['create_time'] = date('Y-m-d H:i:s');
            $save['update_time'] = date('Y-m-d H:i:s');
            $save['state'] = 0;
            $userInfo = session('sysUserInfo');
            $save['creator_id'] = $userInfo['id'];
            $mediaModel = new \Admin\Model\MediaModel();
            if($mediaid){
                $oss_addr = $mediaModel->find($mediaid);
                $oss_addr = $oss_addr['oss_addr'];
                $save['img_url'] = $oss_addr;
            }else{
                $this->error('失败封面必填');
            }
            //开启事务
            $spgroupModel->startTrans();
            $res = $spgroupModel->addData($save);
            if ($res) {
                $sgid = $spgroupModel->getLastInsID();
                $resp = $this->processRelation($spgroupModel, $sgid, $sp_relation_arr);
                if ($resp) {
                    $spgroupModel->commit();
                    $this->output('添加成功!', 'specicalgroup/homemanager');
                } else {
                    $spgroupModel->rollback();
                    $this->error('添加失败');
                }
            } else {
                $spgroupModel->rollback();
                $this->error('添加失败');
            }

        } else {
            //更新
            $spcar['name'] = $save['name'];
            $spcar['state'] = array('neq',2);
            $save['update_time'] = date('Y-m-d H:i:s');
            $save['id']        = I('post.id','','trim');
            $sgid = $save['id'];
            //获取原有名称
            $sppp_arr = $spgroupModel->find($sgid);
            $sp_name = $sppp_arr['name'];
            if( !strcasecmp($sp_name, $spcar['name'])) {

            } else {
                $retar = $spgroupModel->getOneRow($spcar);
                if($retar){
                    $this->error('失败专题组名称已存在!');
                }
            }
            $save['state'] = 0;
            $mediaModel = new \Admin\Model\MediaModel();
            if($mediaid){
                $oss_addr = $mediaModel->find($mediaid);
                $oss_addr = $oss_addr['oss_addr'];
                $save['img_url'] = $oss_addr;
            }else{

            }
            //更新
            //开启事务
            $spgroupModel->startTrans();
            $res = $spgroupModel->saveData($save, array('id'=>$sgid));
            if ($res) {
                //删除以前更新表
                $spRelation = new \Admin\Model\SpecialGroupRelationModel();
               $del_state =  $spRelation->delData( array('sgid'=>$sgid) );
                if ($del_state) {
                    $resp = $this->processRelation($spgroupModel, $sgid, $sp_relation_arr);
                    if ($resp) {
                        $spgroupModel->commit();
                        $this->output('更新成功!', 'specicalgroup/homemanager');
                    } else {
                        $spgroupModel->rollback();
                        $this->error('更新失败');
                    }
                } else {
                    $spgroupModel->rollback();
                    $this->error('更新失败');
                }
            } else {
                $spgroupModel->rollback();
                $this->error('更新失败');
            }
        }
    }


    /*
    * @desc 修改专题组
    * @method doeditSpecialGroup
    * @access public
    * @http Post
    * @param
    * @return void
    */
    public function doeditSpecialGroup()
    {
        $sg_method_type = I('post.sgmethod');
        $this->processGroup($sg_method_type);
        die;
    }


    /*
     * @desc 添加专题组
     * @method doaddspecialgroup
     * @access public
     * @http Post
     * @param
     * @return void
     */
    public function doAddSpecialGroup(){
        $sg_method_type = I('post.sgmethod');
        $this->processGroup($sg_method_type);
        die;

    }

    /*
    * @desc 添加专题组
    * @method addSpecialGroup
    * @access public
    * @http NULL
    * @return void
    */
    public function addSpecialGroup(){
        $this->display('addspecialgroup');
    }
    /*
     * @desc 显示h5页面
     * @method editSpecialGroup
     * @access public
     * @http NULL
     * @return void
     */
    public function showsp(){
        $sourcename = I('get.location','');
        $this->assign('sourc', $sourcename);
        $spgroupModel = new \Admin\Model\SpecialGroupModel();
        $id = I('get.id');
        $field = "sg.NAME,sg.title sptitle, sg.img_url spimg,sg.desc        spdesc,sr.sgtype,sr.stext,sr.sarticleid,sr.spictureid,
        sr.stitle,sm.oss_addr simg,smc.title mtitle,smc.img_url mimg,
        smc.id marticle,smc.content_url mcurl,smc.create_time ";
        $where =  " 1=1 and sg.id = $id";
        $speca_arr_info = $spgroupModel->fetchDataBySql($field, $where);
        $oss_host = $this->oss_host;
        $spinfo = array(
            'sgid'=>$id,
            'name'=>$speca_arr_info[0]['name'],
            'title'=>$speca_arr_info[0]['sptitle'],
            'oss_addr'=>$oss_host.$speca_arr_info[0]['spimg'],
            'desc'=>$speca_arr_info[0]['spdesc'],
        );
        if ($speca_arr_info) {
            foreach ($speca_arr_info as $spk=>$spv) {
                if($spv['sgtype'] == 3) {
                    $speca_arr_info[$spk]['simg'] = $oss_host.$spv['simg'];
                }else if($spv['sgtype'] == 2){
                    $speca_arr_info[$spk]['mimg'] = $oss_host.$spv['mimg'];
                    $speca_arr_info[$spk]['mcurl'] = $this->host_name().'/'.$spv['mcurl'];
                    $speca_arr_info[$spk]['create_time'] = date("Y-m-d", strtotime($spv['create_time']));
                }
            }

        } else {
            $speca_arr_info = array();
        }

        $this->assign('srinfo', $speca_arr_info);
        $this->assign('vinfo', $spinfo);
        $this->display('new_special');

    }



    /*
    * @desc 修改专题组
    * @method editSpecialGroup
    * @access public
    * @http NULL
    * @return void
    */
    public function editSpecialGroup(){
        $spgroupModel = new \Admin\Model\SpecialGroupModel();
        $id = I('get.id');
        $field = "sg.NAME,sg.title sptitle, sg.img_url spimg,sg.desc        spdesc,sr.sgtype,sr.stext,sr.sarticleid,sr.spictureid,
        sr.stitle,sm.oss_addr simg,smc.title mtitle,smc.img_url mimg,
        smc.id marticle";
        $where =  " 1=1 and sg.id = $id";
        $speca_arr_info = $spgroupModel->fetchDataBySql($field, $where);
        $oss_host = $this->oss_host;
        $spinfo = array(
            'sgid'=>$id,
            'name'=>$speca_arr_info[0]['name'],
            'title'=>$speca_arr_info[0]['sptitle'],
            'oss_addr'=>$oss_host.$speca_arr_info[0]['spimg'],
            'desc'=>$speca_arr_info[0]['spdesc'],
           // 'media_id' =>888,
        );
        if ($speca_arr_info) {
            foreach ($speca_arr_info as $spk=>$spv) {
                if($spv['sgtype'] == 3) {
                    $speca_arr_info[$spk]['simg'] = $oss_host.$spv['simg'];
                }else if($spv['sgtype'] == 2){
                    $speca_arr_info[$spk]['mimg'] = $oss_host.$spv['mimg'];
                }
            }

        } else {
            $speca_arr_info = array();
        }
        $this->assign('srinfo', $speca_arr_info);
        $this->assign('vinfo', $spinfo);
        $this->display('editspecialgroup');
    }

}
