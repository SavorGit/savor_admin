<?php
/**
 *
 * @desc 下载次数统计
 */
namespace Admin\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController;

class TestdownloadrpController extends BaseController{

	public function __construct() {
		parent::__construct();
	}


	/**
	 * @desc 下载次数统计
	 */
	public function rplist(){

		$starttime = I('post.starttime','');
		$endtime = I('post.endtime','');
		$downloadModel =  new \Admin\Model\DownloadRpModel();
		$hotelModel = new \Admin\Model\HotelModel();
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','add_time');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$source_type = I('source_type','');

		$where = "1=1";
		$hname = I('hotelname','');
		if($source_type){
			$where .="	AND source_type = '{$source_type}'";
			$this->assign('sot',$source_type);
		}
		if($starttime){
			$this->assign('s_time',$starttime);
			$where .= "	AND add_time >= '{$starttime}'";
		}
		if($endtime){
			$this->assign('e_time',$endtime);
			$where .= "	AND add_time <=  '{$endtime} 23:59:59'";
		}
		$result = $downloadModel->getList($where,$orders,$start,$size);
		$so_type = C('source_type');
		$ind = $start;
		foreach ($result['list'] as &$val) {
			$rs = $hotelModel->find($val['hotelid']);
			$val['hotelname'] = $rs['name'];
			$val['indnum'] = ++$ind;
		}

		$this->assign('sce_type', $so_type);
		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('screenlist');
	}
	/**
	 * @desc 
	 */
	public function appDownload(){
	    $size   = I('numPerPage',50);     //显示每页记录数
	    $this->assign('numPerPage',$size);
	    $start = I('pageNum',1);          //当前页码
	    $this->assign('pageNum',$start);
	    $order = I('_order','date_time'); //排序字段
	    $this->assign('_order',$order);
	    $sort = I('_sort','desc');        //排序类型
	    $this->assign('_sort',$sort);
	    $start_date = I('start_date');    //搜索条件 开始日期
	    $where =" and src in('box','mob','rq')";
	    
	    $hotel_name = I('hotel_name','','trim');
	    if(!empty($hotel_name)){
	        $where .=" and hotel_name like '%".$hotel_name."%'";
	        $this->assign('hotel_name',$hotel_name);
	    }
	    $guardian = I('guardian','','trim');
	    if(!empty($guardian)){
	        $where .=" and guardian like '%".$guardian."%'";
	        $this->assign('guardian',$guardian);
	    }
	    
	    if($start_date){
	        $where .=" and date_time>='".$start_date."'";
	        $this->assign('start_date',$start_date);
	    }
	    $end_date   = I('end_date');     //搜索条件  结束日期
	    if($end_date){
	        $where .= " and date_time<='".$end_date."'";
	        $this->assign('end_date',$end_date);
	    }
	    if(!empty($start_date) && !empty($end_date)){
	        if($end_date<$start_date){
	            $this->error('结束时间不能小于开始时间');
	        }
	    }
	    $m_app_download = new \Admin\Model\TestappdownloadModel();
	    $download_list = $m_app_download->getDownloadHotel($where ,$order,$sort);
	    
	    $data = array();
	    foreach($download_list as $key=>$v){
	        
	        $data[$v['hotel_id']][] = $v;
	    }
	   
	    $count = 0;
	    $list = array();
	    foreach($data as $key=>$val){
           $list[] = $val; 
           $count ++;
	    }
	    
	    $offset = ($start-1)*$size; #计算每次分页的开始位置
	    $list =  array_slice($list, $offset,$size);
	    $rts = array();
	    $flag = 0;
	    foreach($list as $key=>$val){
	        $rts[$flag]['hotel_id']   = $val[0]['hotel_id'];
	        $rts[$flag]['hotel_name'] = $val[0]['hotel_name'];
	        $rts[$flag]['guardian']   = $val[0]['guardian'];
	        $rts[$flag]['end_date_time']  = $val[0]['date_time'];
	        $c_count = count($val) -1;
	        $rts[$flag]['start_date_time'] = $val[$c_count]['date_time'];
	        $box = $mob = $rq = $arr = array();
	        
	        foreach($val as $k=>$v){
	            
	            if($v['src'] =='box'){
	                $box[]=$v['mobile_id'];
	            }else if($v['src']=='mob'){
	                $mob[] = $v['mobile_id'];
	            }else if($v['src']=='rq'){
	                $rq[] = $v['mobile_id'];
	            }
	        }
	        $rts[$flag]['box'] = $box;
	        $rts[$flag]['mob'] = $mob;
	        $rts[$flag]['rq']  = $rq;
	       
	        
	        $arr = array_merge($box,$mob,$rq);
	        $arr = array_unique($arr);
	        $rts[$flag]['all'] = $arr;
	        $rts[$flag]['box_num'] = count($box);
	        $rts[$flag]['mob_num'] = count($mob);
	        $rts[$flag]['rq_num']  = count($rq);
	        $rts[$flag]['all_num'] = count($arr);
	        $flag ++;
	    }
	    //print_r($rts);exit;
	    
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $this->assign('list', $rts);
		$this->assign('page',  $show);
	    $this->display('appdownload');
	}
}
