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
}