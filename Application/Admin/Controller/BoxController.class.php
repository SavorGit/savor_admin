<?php
/**
 * @desc   机顶盒数据
 * @author zhang.yingtao
 * @since  20171229 
 * 
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class BoxController extends BaseController{
	 private $oss_host = '';
	 public function __construct(){
	     parent::__construct();
	     $this->oss_host = get_oss_host();
	 }
	 /**
	  * @desc 获取机顶盒的最新广告列表
	  */
	 public function getAdsList(){
	     $box_id = I('get.box_id',0,'intval');
	     $redis  =  \Common\Lib\SavorRedis::getInstance();
	     $redis->select(12);
	     $cache_key = C('PROGRAM_ADS_CACHE_PRE').$box_id;
	     $program_ads_info = $redis->get($cache_key);
	     $program_ads_info = json_decode($program_ads_info,true);
	     //print_r($program_ads_info);
	     $ads_list = array();
	     $ads_num  = '';
	     if(!empty($program_ads_info)){
	         $ads_num = $program_ads_info['menu_num'];
	         $ads_list = $program_ads_info['ads_list'];
	         $m_pub_ads = new \Admin\Model\PubAdsModel();
	         foreach($ads_list as $key=>$v){
	             $media_info = $m_pub_ads->getPubAdsInfoByid('med.name,med.oss_addr,med.duration',array('pads.id'=>$v['pub_ads_id']));
	             $ads_list[$key]['name'] = $media_info['name'];
	             $ads_list[$key]['oss_addr']=$this->oss_host.$media_info['oss_addr'];
	             $ads_list[$key]['duration'] = $media_info['duration'];
	         }
	     }
	     $this->assign('ads_list',$ads_list);
	     $this->assign('ads_num',$ads_num);
	     $this->display('Device/adslist');
	 }
}