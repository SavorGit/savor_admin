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



class DailycontentController extends BaseController {

    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->oss_host = get_oss_host();
        $this->lnum = 10;
    }



    /*
	 * @desc 逻辑删除专题组
     * @method delsp
     * @access public
     * @http GET
     * @param sgid 专题组id
     * @return void
	 */

    public function delcontent(){
        $sgid = I('request.sgid','0','intval');
        $dcontent = new \Admin\Model\DailyContentModel();
        $now_time = date('Y-m-d H:i:s');
        $spinfo = $dcontent->find($sgid);
        if($spinfo['state'] == 1) {
            $message = '已发布的内容不可删除';
            $this->error($message);
        }
        $binfo = array(
            'update_time'=>$now_time,
            'state'=>2,
        );
        $res = $dcontent->saveData($binfo, array('id'=>$sgid));
        if ($res) {
            $message = '删除成功';
            $this->output($message, 'dailycontent/homemanager',2);
        } else {
            $message = '操作失败';
            $this->error($message);
        }
    }



    public function doSort(){
        $now_date = date('Y-m-d H:i:s');
        $save['update_time'] = $now_date;
        $save['create_time'] = $now_date;
        $save['bespeak_time'] = I('post.subdailytime','');
        $sort_str= I('post.dailysoar');
        $sort_arr = explode(',', $sort_str);
        if (count($sort_arr) != $this->lnum) {
            $this->error('内容不满足'.$this->lnum.'条');
        }

        //判断该日期是否发布过
        if($save['bespeak_time'] == '' || $save['bespeak_time']=='0000-00-00 00:00:00'){
            $save['bespeak'] = 0;
            $save['bespeak_time'] = $now_date;
        }else{
            $yes = strtotime("-1 days");
            if ( strtotime($save['bespeak_time']) < $yes) {
                $this->error('预约日期不可小于今天');
            }
            $save['bespeak'] = 1;
        }

        $dat_time = date("Y-m-d", strtotime
        ($save['bespeak_time']));
        $dailylkModel = new \Admin\Model\DailyLkModel();
        $where = '1=1 and DATE_FORMAT(`bespeak_time`,"%Y-%m-%d")
         ="'.$dat_time.'"';
        $number = $dailylkModel->getCount($where, $field='*');
        if ( $number > 0) {
            $this->error('请重新选择发布日期，'.$dat_time.'（日期）已发布了内容');
        }
        $userInfo = session('sysUserInfo');
        $save['creator_id']   = $userInfo['id'];
        $save['homestate'] = 1;
        //插入lk表
        $res = $dailylkModel->addData($save);
        if ($res) {
            $sgid = $dailylkModel->getLastInsID();
            foreach($sort_arr as $k=>$v){
                $sp[$k] = array(
                    'lkid' => $sgid,
                    'dailyid'=>$v,
                    'sort_num'=>$k+1,
                );
            }
            $dhomeModel = new \Admin\Model\DailyHomeModel();
            $dcontentModel = new \Admin\Model\DailyContentModel();
            $resp = $dhomeModel->addAll($sp);
            $dat['state'] = 1;
            foreach($sort_arr as $k=>$v){
                $map['id'] = $v;
                $dcontentModel->saveData($dat, $map);
            }
            if ($resp) {
                $dailylkModel->commit();
                $this->output('添加成功!', 'dailycontent/rplist');
            } else {
                $dailylkModel->rollback();
                $this->error('添加失败');
            }
        } else {
            $dailylkModel->rollback();
            $this->error('添加失败');
        }
    }


    public function addSort(){
        $dcontentModel = new \Admin\Model\DailyContentModel();
        $order = I('_order','create_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $where = "1=1 and state=0";
        $field = "id, title,DATE_FORMAT(`create_time`,'%Y-%m-%d') ctime";
        $limit = $this->lnum;
        $result = $dcontentModel->getWhere($where, $field, $orders, $limit);
        $index = 1;
        foreach($result as &$value) {
            $value['index'] = $index;
            $index++;
        }
        $this->assign('list', $result);
        $this->display('dailysort');
    }



    public function homemanager(){
        $dHomeModel = new \Admin\Model\DailyHomeModel();
        $dcontentModel = new  \Admin\Model\DailyContentModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $orders = ' lk.bespeak_time DESC,sh.sort_num ASC ';
        $start  = ( $start-1 ) * $size;
        $where = "1=1";
        $name = I('searchhome');
        $field = " sh.dailyid,sh.sort_num,sc.title,lk.create_time,lk.bespeak_time,lk.creator_id,su.remark ";
        if($name){
            $this->assign('serachnn',$name);
            $where .= " and sc.title like '%".$name."%'";
        }
        $result = $dHomeModel->getDailyHomeInfo($where, $field);

        $index = 1;
        foreach($result as &$value) {
            $value['index'] = $index;
            $index++;
        }
        $this->assign('list', $result);
        $this->assign('page',  $result['page']);
        $this->display('homedaily');
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
    public function rplist(){

        $dcontentModel = new \Admin\Model\DailyContentModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order',' dc.create_time ');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = "1=1 and dc.state != 2";
        $name = I('serachdaily');
        if ($name) {
            $this->assign('dailyname', $name);
            $where .= " and dc.title like '%".$name."%' ";
        }
        $field = 'dc.id,dc.title,dc.creator_id,su.remark,dc.create_time,dc
        .state,lk.bespeak_time';
        $result = $dcontentModel->getList($field, $where, $orders,$start,$size);

        $sg_state = C('SP_GR_STATE');
        array_walk($result['list'], function(&$v, $k)use($sg_state){
            $st_num = $v['state'];
            if (array_key_exists($st_num, $sg_state)){
                $v['pub'] = $sg_state[$st_num];
            }
            if( empty($v['bespeak_time'])) {
                $v['bespeak_time'] = '无';
            }
        });


        foreach($result['list'] as $key=>$v){

            $pushdata = array();
            $pushdata['id'] = $v['id'];
            $result['list'][$key]['pushdata'] = json_encode($pushdata,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

        }
        $retp = $result['list'];
        $this->assign('list', $retp);
        $this->assign('page',  $result['page']);
        $this->display('dailylist');
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
        if ($save['title']) {
            if ( mb_strlen($save['title'], 'utf-8') >= 1 && mb_strlen($save['name'], 'utf-8') <= 50) {

            } else {
                $errmsg = '标题名称字数不符';
                $this->error($errmsg);
            }
        } else {
            $errmsg = '标题名称不可为空';
            $this->error($errmsg);
        }

        if ($save['keyword']) {
            if ( mb_strlen($save['keyword'], 'utf-8') > 6) {
                $errmsg = '关键词限制6个字以内';
                $this->error($errmsg);
            }
        } else {
            $errmsg = '关键词不可为空';
            $this->error($errmsg);
        }
        if ( mb_strlen($save['artpro'], 'utf-8') >= 1 && mb_strlen($save['artpro'], 'utf-8') <= 4) {

        } else {
            $errmsg = '属性名称字数不符';
            $this->error($errmsg);
        }


        if ($save['desc']) {
            if ( mb_strlen($save['desc']) > 200) {
                $errmsg = '描述字符限制为200';
                $this->error($errmsg);
            }
        } else {
            $errmsg = '描述未填';
            $this->error($errmsg);
        }

        if ( empty(($save['order_tag'])) ) {
            $this->error('标签数应大于0');
        }

        if( empty($save['media_id']) ){
            $this->error('失败封面必填');
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
    public function processRelation($sgid, $sp_relation_arr) {
        //根据标签遍历
        $num = 0;
        $sp = array();
        $dRelationModel = new \Admin\Model\DailyRelationModel();
        foreach($sp_relation_arr as $sv) {
            foreach ($sv as $gk=>$gv) {
                if('scontent' == $gk) {
                    $sp[$num] = array(
                        'dailyid' => $sgid,
                        'dailytype' => 1,
                        'stext' => $gv,
                        'spictureid' => 0,
                    );
                }  else if('spid' == $gk) {
                    $sp[$num] = array(
                        'dailyid' => $sgid,
                        'dailytype' => 3,
                        'stext' => '',
                        'spictureid' => $gv,
                    );
                }  else {

                }
            }
            $num++;
        }
        $resp = $dRelationModel->addAll($sp);
        return $resp;
    }


    /**
     * @desc 处理每日知享添加与编辑的处理过程
     * @method processGroup
     * @access public
     * @http NULL
     * @param $stype int  1添加2编辑
     * @return void|array
     */
    public function processGroup($stype) {

        $dcontentModel = new \Admin\Model\DailyContentModel();
        $save['title']        = I('post.dailytitle','','trim');
        $save['keyword']        = I('post.keyword','','trim');
        $save['desc']        = I('post.daily-desc','','trim');
        $save['source_id'] = I('post.source_id','','trim');
        $save['media_id'] = I('post.media_id','0','intval');
        $save['artpro'] = I('post.artpro', '', 'trim');
        // $save['desc'] = htmlspecialchars(strip_tags($save['desc']));
        //处理标签
        $_POST['taginfo'] = preg_replace("/\'/", '"', $_POST['dailyre']);
        $tagr = json_decode ($_POST['taginfo'],true);
        $ar = array();
        foreach ($tagr as $t=>$v) {
            if(in_array($v['tagid'], $ar)){
                $this->error('标签不可有重复');
            }
            $ar[]=$v['tagid'];
        }
        $save['sort_tag'] = implode(',',$ar);
        sort($ar);
        $save['order_tag'] = implode(',',$ar);
        //处理错误提示
        $this->processTips($save);
        $sp_relation_arr  = I('post.savedaily','','trim');
        $sp_relation_arr = json_decode($sp_relation_arr, true);
        //var_export($sp_relation_arr);
        if (count($sp_relation_arr)<1) {
            $this->error('原文内容至少有1条');
        }


        $oss_host = $this->oss_host;
        $m_media = new \Admin\Model\MediaModel();
        $marr = $m_media->getMediaInfoById($save['media_id']);
        $headerInfo = get_headers($marr[oss_addr],true);
        $img_size=ceil($headerInfo['Content-Length']/1000);
        if ($img_size>500) {
            $this->error('封面图限制500k以内');
        }
        if ($stype == 1) {
            //添加

            $spcar['title'] = $save['title'];
            $spcar['state'] = array('neq',2);
            $retar = $dcontentModel->getOneRow($spcar);

            if($retar){
                $this->error('失败标题名称已存在!');
            }

            $save['create_time'] = date('Y-m-d H:i:s');
            $save['update_time'] = date('Y-m-d H:i:s');
            $save['state'] = 0;
            $userInfo = session('sysUserInfo');
            $save['creator_id'] = $userInfo['id'];
            //开启事务

            $dcontentModel->startTrans();
            $res = $dcontentModel->addData($save);
            if ($res) {
                $sgid = $dcontentModel->getLastInsID();
                $resp = $this->processRelation($sgid, $sp_relation_arr);
                if ($resp) {
                    $dcontentModel->commit();
                    $this->output('添加成功!', 'dailycontent/rplist');
                } else {
                    $dcontentModel->rollback();
                    $this->error('添加失败');
                }
            } else {
                $dcontentModel->rollback();
                $this->error('添加失败');
            }

        } else {
            //更新

            $spcar['title'] = $save['title'];
            $spcar['state'] = array('neq',2);
            $save['update_time'] = date('Y-m-d H:i:s');
            $save['id']        = I('post.id','','trim');
            $sgid = $save['id'];
            //获取原有名称
            $sppp_arr = $dcontentModel->find($sgid);
            $sp_name = $sppp_arr['title'];
            if( !strcasecmp($sp_name, $spcar['title'])) {

            } else {
                $retar = $dcontentModel->getOneRow($spcar);
                if($retar){
                    $this->error('失败标题组名称已存在!');
                }
            }
            //更新
            //开启事务
            $dcontentModel->startTrans();
            $res = $dcontentModel->saveData($save, array('id'=>$sgid));


            if ($res) {
                //删除以前更新表
                $spRelation = new \Admin\Model\DailyRelationModel();
               $del_state =  $spRelation->delData( array('dailyid'=>intval($sgid)) );
                if ($del_state >= 0) {
                    $resp = $this->processRelation( $sgid, $sp_relation_arr);
                    if ($resp) {
                        $dcontentModel->commit();
                        $this->output('更新成功!', 'dailycontent/rplist', 1);
                    } else {
                        $dcontentModel->rollback();
                        $this->error('更新失败');
                    }
                } else {
                    $dcontentModel->rollback();
                    $this->error('更新失败');
                }
            } else {
                $dcontentModel->rollback();
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
    public function doeditContent()
    {

       // var_export($_POST);
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
    public function doAddContent(){

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
    public function addcontent(){
        //添加标签
        $pagearr = $this->getPageTag();
        //添加来源
        $m_article_source = new \Admin\Model\ArticleSourceModel();
        $article_list = $m_article_source->order('convert(`name` using gbk) asc')->getAll();

        $this->assign('sourcelist',$article_list);
        $this->assign('pageinfo',$pagearr['list']);
        $this->assign('pagecount',$pagearr['page']);
        $this->display('addcontent');
    }

    public function getPageTag(){
        $tagModel = new \Admin\Model\TagListModel();
        $size   = 20;//显示每页记录数
        $start = 1;
        $order = I('_order','convert(tagname using gbk)');
        $sort = I('_sort','asc');
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = "1=1";
        $tagname = I('tagname','','trim');
        if($tagname){
            $where .= "	AND tagname LIKE '%{$tagname}%'";
        }
        $where .= " AND flag = 1";
        $field = 'id,tagname';
        $result = $tagModel->getList($where,$orders,$start,$size,$field);
        $result['page'] = $tagModel->getPageCount($where);
        $result['page'] = ceil($result['page']/$size);
        return $result;
    }





    /*
    * @desc 修改专题组
    * @method editSpecialGroup
    * @access public
    * @http NULL
    * @return void
    */
    public function editContent(){
        $dcontentModel = new \Admin\Model\DailyContentModel();
        $id = I('get.id');
        $field = "sg.title title, sg.media_id mediaid,sg.keyword
        ,sg.desc,sg.source_id,sg.order_tag tag,sr.dailytype,sr.stext,sr
        .spictureid,sm.oss_addr simg,sg.artpro ";
        $where =  " 1=1 and sg.id = $id ";
        $dcontent_arr = $dcontentModel->fetchDataBySql($field,
            $where);
        $oss_host = $this->oss_host;
        $m_media = new \Admin\Model\MediaModel();
        $marr = $m_media->getMediaInfoById($dcontent_arr[0]['mediaid']);
        $spinfo = array(
            'sgid'=>$id,
            'title'=>$dcontent_arr[0]['title'],
            'keyword'=>$dcontent_arr[0]['keyword'],
            'artpro'=>$dcontent_arr[0]['artpro'],
            'desc'=>$dcontent_arr[0]['desc'],
            'media_id' =>$dcontent_arr[0]['mediaid'],
            'source_id'=>$dcontent_arr[0]['source_id'],
            'oss_addr'=>empty($dcontent_arr[0]['mediaid'])?'':$marr['oss_addr'],
        );
        //获取来源

        if ($dcontent_arr) {
            foreach ($dcontent_arr as $spk=>$spv) {
                if($spv['dailytype'] == 3) {
                    $dcontent_arr[$spk]['simg'] = $oss_host.$spv['simg'];
                }
            }

        } else {
            $dcontent_arr = array();
        }
        if ($dcontent_arr[0]['tag']) {
            $resp = $this->getTagInfoByDailyTag($dcontent_arr[0]['tag']);

        } else {
            $resp = array();
        }
        //添加标签
        $pagearr = $this->getPageTag();
        //添加来源
        $m_article_source = new \Admin\Model\ArticleSourceModel();
        $article_list = $m_article_source->order('convert(`name` using gbk) asc')->getAll();
        if($resp){
            $this->assign('tagaddart',$resp);
            $new = json_encode($resp);
            $new = preg_replace('/\"/', "'", $new);
            $this->assign('taginfod',$new);
        }




        $this->assign('sourcelist',$article_list);
        $this->assign('pageinfo',$pagearr['list']);
        $this->assign('pagecount',$pagearr['page']);
        $this->assign('tagaddart',$resp);
        $this->assign('srinfo', $dcontent_arr);
        $this->assign('vinfo', $spinfo);
        $this->display('editcontent');
    }


    public function getTagInfoByDailyTag($where){
        $map['id'] = array('in', $where);
        $tagModel = new \Admin\Model\TagListModel();
        $res = $tagModel->where($map)->field('id tagid,tagname')->select();
        return $res;
    }




}
