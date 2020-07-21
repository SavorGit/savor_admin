<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序数据统计配置
 *
 */
class StaticconfigController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    public function index(){
        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        
        $fields = "id,conf_data,type";
        $where = array();
        $where['status'] = 1;
        $order = ' type asc';
        $list = $m_static_config->getWhere($fields, $where, $order);
        $data = array();
        foreach($list as $key=>$v){
            $data[$v['type']] = json_decode($v['conf_data'],true);
        }
        $this->assign('data',$data);
        $this->display('index');
    }
    /**
     * @desc 修改总分数
     */
    public function editMulty(){
        
        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        if(IS_POST){
            $type = I('post.type',0,'intval');
            $heart = I('post.heart',0,'intval');
            $net   = I('post.net',0,'intval');
            $hd    = I('post.hd',0,'intval');
            $cover = I('post.cover',0,'intval');
            $data = $config =  array();
            $config['heart'] = $heart;
            $config['net']   = $net;
            $config['hd']    = $hd;
            $config['cover'] = $cover;
            $config = json_encode($config);
            $data['conf_data'] = $config;
            $data['type'] = $type;
            $data['status']= 1;
            $where = array();
            $where['type'] = $type;
            $where['status'] = 1;
            
            $nums = $m_static_config->countNum($where);
            if(empty($nums)){
                $ret =  $m_static_config->addInfo($data,1);
            }else {
                $ret = $m_static_config->updateInfo($where,$data);
            }
            if($ret){
                $this->output('更新成功!', 'staticconfig/index',1);
            }else {
                $this->output('更新失败!', 'staticconfig/index');
            }
            
        }else {
            $type = I('get.type',0,'intval');
            
            $where = array();
            
            $where['type'] = $type;
            $where['status'] = 1;
            $data = $m_static_config->getOne('conf_data', $where);
            if(!empty($data)){
                $data = json_decode($data['conf_data'],true);
                $this->assign('data',$data);
            }
            $this->assign('type',$type);
            $this->display('editmulty');
        }
    }
    /**
     * @desc 修改分数级别
     */
    public function editLevel(){
        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        if(IS_POST){
            $type = I('post.type',0,'intval');
            $mina = I('post.mina',0,'intval');
            $maxa = I('post.maxa',0,'intval');
            
            $minb = I('post.minb',0,'intval');
            $maxb = I('post.maxb',0,'intval');
            
            $minc = I('post.minc',0,'intval');
            $maxc = I('post.maxc',0,'intval');
            
            $config = $data = array();
            $config['mina'] = $mina;
            $config['maxa'] = $maxa;
            $config['minb'] = $minb;
            $config['maxb'] = $maxb;
            $config['minc'] = $minc;
            $config['maxc'] = $maxc;
            
            $config = json_encode($config);
            $data['conf_data'] = $config;
            $data['type'] = $type;
            $data['status']= 1;
            
            
            $where = array();
            $where['type'] = $type;
            $where['status'] = 1;
            
            
            $nums = $m_static_config->countNum($where);
            if(empty($nums)){
                $ret =  $m_static_config->addInfo($data,1);
            }else {
                $ret = $m_static_config->updateInfo($where,$data);
            }
            if($ret){
                $this->output('更新成功!', 'staticconfig/index',1);
            }else {
                $this->output('更新失败!', 'staticconfig/index');
            }
        }else {
            $type = I('get.type',0,'intval');
            
            $where = array();
            
            $where['type'] = $type;
            $where['status'] = 1;
            $data = $m_static_config->getOne('conf_data', $where);
            if(!empty($data)){
                $data = json_decode($data['conf_data'],true);
                
                $this->assign('data',$data);
            }
            $this->assign('type',$type);
            $this->display('editlevel');
        }
    }
    /**
     * @desc 修改心跳权重
     */
    public function editHeart(){
        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        if(IS_POST){
            $type = I('post.type',0,'intval'); 
            $min  = I('post.min');
            $max  = I('post.max');
            $score= I('post.score');
            $config = array();
            
            foreach($min as $key=>$v){
                $config[$key]['min']   = intval($v);
                $config[$key]['max']   = intval($max[$key]);
                $config[$key]['score'] = intval($score[$key]); 
                
            }
            
            $config = json_encode($config);
            $data['conf_data'] = $config;
            $data['type'] = $type;
            $data['status']= 1;
            
            
            $where = array();
            $where['type'] = $type;
            $where['status'] = 1;
            
            
            $nums = $m_static_config->countNum($where);
            if(empty($nums)){
                $ret =  $m_static_config->addInfo($data,1);
            }else {
                $ret = $m_static_config->updateInfo($where,$data);
            }
            if($ret){
                $this->output('更新成功!', 'staticconfig/index',1);
            }else {
                $this->output('更新失败!', 'staticconfig/index');
            }
        }else {
            $type = I('get.type',0,'intval');
            $where = array();
            
            $where['type'] = $type;
            $where['status'] = 1;
            $data = $m_static_config->getOne('conf_data', $where);
            if(!empty($data)){
                $data = json_decode($data['conf_data'],true);
                
                $this->assign('data',$data);
            }
            $this->assign('type',$type);
            $this->display('editheart');
        }
    }
    /**
     * @desc 修改网速权重
     */
    public function editNet(){
        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        if(IS_POST){
            $type = I('post.type',0,'intval');
            $min  = I('post.min');
            $max  = I('post.max');
            $score= I('post.score');
            $config = array();
            
            foreach($min as $key=>$v){
                $config[$key]['min']   = intval($v);
                $config[$key]['max']   = intval($max[$key]);
                $config[$key]['score'] = intval($score[$key]);
                
            }
            
            $config = json_encode($config);
            $data['conf_data'] = $config;
            $data['type'] = $type;
            $data['status']= 1;
            
            
            $where = array();
            $where['type'] = $type;
            $where['status'] = 1;
            
            
            $nums = $m_static_config->countNum($where);
            if(empty($nums)){
                $ret =  $m_static_config->addInfo($data,1);
            }else {
                $ret = $m_static_config->updateInfo($where,$data);
            }
            if($ret){
                $this->output('更新成功!', 'staticconfig/index',1);
            }else {
                $this->output('更新失败!', 'staticconfig/index');
            }
        }else {
            $type = I('get.type',0,'intval');
            $where = array();
            
            $where['type'] = $type;
            $where['status'] = 1;
            $data = $m_static_config->getOne('conf_data', $where);
            if(!empty($data)){
                $data = json_decode($data['conf_data'],true);
                
                $this->assign('data',$data);
            }
            $this->assign('type',$type);
            $this->display('editnet');
        }
    }
    /**
     * @desc 修改互动权重
     */
    public function editHd(){
        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        if(IS_POST){
            $type = I('post.type',0,'intval');
            $min  = I('post.min');
            $max  = I('post.max');
            $score= I('post.score');
            $config = array();
            
            foreach($min as $key=>$v){
                $config[$key]['min']   = intval($v);
                $config[$key]['max']   = intval($max[$key]);
                $config[$key]['score'] = intval($score[$key]);
                
            }
            
            $config = json_encode($config);
            $data['conf_data'] = $config;
            $data['type'] = $type;
            $data['status']= 1;
            
            
            $where = array();
            $where['type'] = $type;
            $where['status'] = 1;
            
            
            $nums = $m_static_config->countNum($where);
            if(empty($nums)){
                $ret =  $m_static_config->addInfo($data,1);
            }else {
                $ret = $m_static_config->updateInfo($where,$data);
            }
            if($ret){
                $this->output('更新成功!', 'staticconfig/index',1);
            }else {
                $this->output('更新失败!', 'staticconfig/index');
            }
        }else {
            $type = I('get.type',0,'intval');
            $where = array();
            
            $where['type'] = $type;
            $where['status'] = 1;
            $data = $m_static_config->getOne('conf_data', $where);
            if(!empty($data)){
                $data = json_decode($data['conf_data'],true);
            
                $this->assign('data',$data);
            }
            $this->assign('type',$type);
            $this->display('edithd');
        }
    }
    /**
     * @desc 修改覆盖率权重
     */
    public function editCover(){
        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        if(IS_POST){
            $type = I('post.type',0,'intval');
            $min  = I('post.min');
            $max  = I('post.max');
            $score= I('post.score');
            $config = array();
            
            foreach($min as $key=>$v){
                $config[$key]['min']   = intval($v);
                $config[$key]['max']   = intval($max[$key]);
                $config[$key]['score'] = intval($score[$key]);
                
            }
            
            $config = json_encode($config);
            $data['conf_data'] = $config;
            $data['type'] = $type;
            $data['status']= 1;
        
        
            $where = array();
            $where['type'] = $type;
            $where['status'] = 1;
        
            
            $nums = $m_static_config->countNum($where);
            if(empty($nums)){
                $ret =  $m_static_config->addInfo($data,1);
            }else {
                $ret = $m_static_config->updateInfo($where,$data);
            }
            if($ret){
                $this->output('更新成功!', 'staticconfig/index',1);
            }else {
                $this->output('更新失败!', 'staticconfig/index');
            }
        }else {
            $type = I('get.type',0,'intval');
            $where = array();
            
            $where['type'] = $type;
            $where['status'] = 1;
            $data = $m_static_config->getOne('conf_data', $where);
            if(!empty($data)){
                $data = json_decode($data['conf_data'],true);
            
                $this->assign('data',$data);
            }
            $this->assign('type',$type);
            $this->display('editcover');
        }
    }

    public function boxgrade(){
        $m_boxstatic = new \Admin\Model\BoxStaticgradeconfigModel();
        $where = array();
        $res = $m_boxstatic->getDataList('*',$where,'cnum asc');
        $config = array();
        foreach ($res as $v){
            if(in_array($v['score_type'],array(10,20))){
                $config[$v['score_type']][$v['type']][]=$v;
            }else{
                $config[$v['score_type']][$v['type']]=$v;
            }
        }
        $this->assign('config',$config);
        $this->display();
    }

    public function editboxgrade(){
        $score_type = I('score_type',0,'intval');
        $type = I('type',0,'intval');
        $m_boxstatic = new \Admin\Model\BoxStaticgradeconfigModel();
        if(IS_POST){
            $sample_num = I('sample_num',0,'intval');
            $ids = I('ids');
            $weights = I('weights');
            $min = I('min');
            $max = I('max');
            $grade = I('grade');
            $ret_grade = 0;
            foreach ($ids as $k=>$v){
                $where = array('id'=>intval($v));
                if(in_array($score_type,array(11,21))){
                    $now_weight = intval($weights[$k]);
                    $data = array('weights'=>$now_weight);
                }else{
                    $now_min = intval($min[$k]);
                    $now_max = intval($max[$k]);
                    $now_grade = intval($grade[$k]);
                    $data = array('min'=>$now_min,'max'=>$now_max,'grade'=>$now_grade);
                }
                $ret = $m_boxstatic->updateData($where,$data);
                if(!$ret_grade && $ret){
                    $ret_grade = 1;
                }
            }
            if(in_array($type,array(2,4,5))){
                if($score_type==10){
                    $now_score_type = 12;
                }else{
                    $now_score_type = 22;
                }
                $res_static = $m_boxstatic->getInfo(array('score_type'=>$now_score_type,'type'=>$type));
                if(!empty($res_static)){
                    $m_boxstatic->updateData(array('id'=>$res_static['id']),array('sample_num'=>$sample_num));
                }else{
                    $m_boxstatic->add(array('score_type'=>$now_score_type,'type'=>$type,'sample_num'=>$sample_num));
                }
            }
            if($ret_grade){
                $cache_key = 'cronscript:macgrade';
                $redis  =  \Common\Lib\SavorRedis::getInstance();
                $redis->select(1);
                $cache_data = json_encode(array('status'=>1,'time'=>date('Y-m-d H:i:s')));
                $redis->set($cache_key,$cache_data);
            }
            $this->output('更新成功!', 'staticconfig/boxgrade',1);
        }else{
            $type_str = array('1'=>'netty重连次数','2'=>'投屏成功率','3'=>'心跳次数','4'=>'上传网速','5'=>'下载网速');
            $where = array('score_type'=>$score_type);
            if($type)   $where['type'] = $type;
            $res = $m_boxstatic->getDataList('*',$where,'cnum asc');
            $vinfo = array();
            foreach ($res as $v){
                $v['gname'] = $type_str[$v['type']];
                if(in_array($score_type,array(11,21))){
                    $vinfo[$v['type']] = $v;
                }else{
                    $vinfo[] = $v;
                }
            }
            if(in_array($score_type,array(11,21))){
                $html = 'editboxtotalgrade';
            }else{
                $html = 'editboxgrade';
            }
            $sample_num = 0;
            $is_sample = 0;
            if(in_array($type,array(2,4,5))){
                $is_sample = 1;
                if($score_type==10){
                    $now_score_type = 12;
                }else{
                    $now_score_type = 22;
                }
                $res_static = $m_boxstatic->getInfo(array('score_type'=>$now_score_type,'type'=>$type));
                $sample_num = $res_static['sample_num'];
            }
            $this->assign('is_sample',$is_sample);
            $this->assign('sample_num',$sample_num);
            $this->assign('score_type',$score_type);
            $this->assign('type',$type);
            $this->assign('datalist',$vinfo);
            $this->display($html);
        }

    }
}