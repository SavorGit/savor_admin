<?php
/**
 *@author hongwei
 * @desc 内容与广告显示列表
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;

class ContentwxauthController extends BaseController{

	public function __construct() {
		parent::__construct();
	}
	public function index(){
	    $size   = I('numPerPage',50);//显示每页记录数
	    $this->assign('numPerPage',$size);
	    $start = I('pageNum',1);
	    $this->assign('pageNum',$start);
	    $order = I('_order','a.create_time');
	    $this->assign('_order',$order);
	    $sort = I('_sort','desc');
	    $this->assign('_sort',$sort);
	    $orders = $order.' '.$sort;
	    $start  = ( $start-1 ) * $size;
	    
	    $where      = ' 1';
	    $openid     = I('post.openid','','trim');
	    $start_date = I('post.start_date');
	    $end_date   = I('post.end_date');
	    $contentid  = I('post.contentid',0,'intval');
	    
	    if(!empty($openid)){
	        $where .=" and a.openid='$openid'";
	    }
	    if(!empty($start_date) && !empty($end_date)){
	        if($start_date>$end_date){
	            $this->error('开始时间不能大于结束时间');
	        }
	    }
	    if(!empty($start_date)){
	        $where .= " and a.create_time>='".$start_date." 00:00:00'";
	        $this->assign('start_date',$start_date);
	    }
	    if(!empty($end_date)){
	        $where .=" and a.create_time<='".$end_date." 23:59:59'";
	        $this->assign('end_date',$end_date);
	    }
	    if(!empty($contentid)){
	        $where .=" and a.contentid=$contentid";
	        $this->assign('contentid',$contentid);
	    }
	    
	    $m_content_wx_auth = new \Admin\Model\ContentWxAuthModel();
	    $data = $m_content_wx_auth->getList('a.*,b.title ,c.name catname',$where,$orders, $start,$size);
	    
	    $this->assign('list', $data['list']);
		$this->assign('page',  $data['page']);
	    $this->display('index');
	}
}