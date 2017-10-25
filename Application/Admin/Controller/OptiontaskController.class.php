<?php
/**
 *@author zhang.yingtao
 *@desc 酒楼运维任务
 *@since 2017-10-19
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
class OptiontaskController extends BaseController {
    private $install_state_arr;
    private $task_state_arr;
    private $task_type_arr;
    private $task_emerge_arr;
    private $task_area_arr;
    private $plan_finish_time;
    private $task_person_arr;
	public function __construct() {
		parent::__construct();
		$this->install_state_arr = array('1'=>'待完成','2'=>'已完成','3'=>'不需要');
	    $this->task_state_arr = array('1'=>'新任务','2'=>'执行中','3'=>'排队等待','4'=>'已完成');	
	    $this->task_type_arr = array('1'=>'网络检测','2'=>'安装机顶盒','3'=>'安装网络+机顶盒','4'=>'报修网络版','5'=>'报修单机版','6'=>'单机版特殊更新','7'=>'内网改造报价');
	    $this->task_emerge_arr = array('1'=>'火烧眉毛','2'=>'急','3'=>'一般');
	    $this->task_area_arr = array('1'=>'广州','2'=>'上海','3'=>'深圳','4'=>'北京');
	    $this->task_person_arr = array('1'=>'苏苏','2'=>'张磊','3'=>'成通','4'=>'黄勇','5'=>'刘朝伟','6'=>'罗浩','7'=>'邱志宇','8'=>'施华杰','9'=>'朱毅','10'=>'张文宇','11'=>'王卫华','12'=>'李丛','13'=>'任伟','14'=>'郑伟','15'=>'冯颖亮');
	}
	/**
	 * @desc 任务列表
	 */
	public function index(){
	    
	    $size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','update_time');
		$plan_finish_time = I('plan_finish_time');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$personid = I('personid');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		
		/* $where['state'] = array('in','1,2,3');
		$where['flag'] = 0; */
		$where= ' 1 and state in(1,2,3) and flag=0';
		if($plan_finish_time){
		  $where.="  and palan_finish_time>='$plan_finish_time 00:00:00' and palan_finish_time<='$plan_finish_time 23:59:59'";
		  
		  $this->assign('palan_finish_time',$plan_finish_time);
		}
		if($personid){
		    $where.=" and FIND_IN_SET($personid,performer) ";
		    
		    $this->assign('personid',$personid);
		}
		$m_option_task = new \Admin\Model\OptiontaskModel();
		$list= $m_option_task->getList($where,$orders,$start,$size);
		foreach($list['list'] as $key=>$v){
		    $performer_str = '';
		    $space = '';
		    $performer = explode(',', $v['performer']);
		    foreach($performer as $pv){
		        $performer_str .= $space .$this->task_person_arr[$pv];
		        $space = ',';
		    }
		    $list['list'][$key]['performer'] = $performer_str;
		}
	    $this->assign('list',$list['list']);
	    
	    $this->assign('page',$list['page']);
	    $this->assign('plan_finish_time',$plan_finish_time);
	    $this->assign('task_state_arr',$this->task_state_arr);
	    $this->assign('install_state_arr',$this->install_state_arr);
	    $this->assign('task_type_arr',$this->task_type_arr);
	    $this->assign('task_area_arr',$this->task_area_arr);
	    $this->assign('task_person_arr',$this->task_person_arr);
	    $this->assign('task_emerge_arr',$this->task_emerge_arr);
	    
	    $this->display('index');
	}
	public function add(){
	    $this->assign('install_state_arr',$this->install_state_arr);
	    $this->assign('task_state_arr',$this->task_state_arr);
	    $this->assign('task_type_arr',$this->task_type_arr);
	    $this->assign('task_emerge_arr',$this->task_emerge_arr);
	    $this->assign('task_person_arr',$this->task_person_arr);
	    $this->assign('task_area_arr',$this->task_area_arr);
	    $this->display('add');
	}
	public function doadd(){
	    $data = $_POST;
	    
	    if($data['id']){
	        $id = $data['id'];
	        unset($data['id']);
	        unset($data['ajax']);
	        $performer = implode(',', $data['performer']);
	        $data['performer'] = $performer; 
	        $m_option_task = new \Admin\Model\OptiontaskModel();
	        $ret = $m_option_task->where('id='.$id)->save($data);
	        if($ret){
	            $this->output('修改成功', 'optiontask/index', 1);
	        }else {
	            $this->error('修改失败');
	        }
	    }else {
	        unset($data['id']);
	        unset($data['ajax']);
	        $performer = implode(',', $data['performer']);
	        $data['performer'] = $performer;
	        //print_r($data);exit;
	        $m_option_task = new \Admin\Model\OptiontaskModel();
	        $ret = $m_option_task->add($data);
	        if($ret){
	            $this->output('发布成功', 'optiontask/index', 1);
	        }else {
	            $this->error('发布失败');
	        }
	    }
	    
	}
	public function edit(){
	     $m_option_task = new \Admin\Model\OptiontaskModel();
	    $id = I('get.id',0,'intval');
	    $list = $m_option_task->where('id='.$id)->find(); 
	    $performer = $list['performer'];
	    $performer_arr = explode(',', $performer);
	    $this->assign('performer_arr',$performer_arr);
	    $this->assign('vinfo',$list);
	    $this->assign('install_state_arr',$this->install_state_arr);
	    $this->assign('task_state_arr',$this->task_state_arr);
	    $this->assign('task_type_arr',$this->task_type_arr);
	    $this->assign('task_emerge_arr',$this->task_emerge_arr);
	    $this->assign('task_area_arr',$this->task_area_arr);
	    $this->assign('task_person_arr',$this->task_person_arr);
	    $this->assign('id',$id);
	    $this->display('add');
	}
	public function delete(){
	    $id = I('get.id');
	    $m_option_task = new \Admin\Model\OptiontaskModel();
	    $data['flag'] = 1;
	    $ret = $m_option_task->where('id='.$id)->save($data);
	    if($ret){
	        $this->output('删除成功', 'optiontask/index', 2);
	    }else {
	        $this->error('删除失败');
	    }
	}
	public function historytask(){
	    $size   = I('numPerPage',50);//显示每页记录数
	    $this->assign('numPerPage',$size);
	    $start = I('pageNum',1);
	    $this->assign('pageNum',$start);
	    $order = I('_order','update_time');
	    $this->assign('_order',$order);
	    $sort = I('_sort','desc');
	    $this->assign('_sort',$sort);
	    $orders = $order.' '.$sort;
	    $start  = ( $start-1 ) * $size;
	    
	    $where['state'] = array('in','4');
	    $where['flag'] = 0;
	    $m_option_task = new \Admin\Model\OptiontaskModel();
	    $list= $m_option_task->getList($where,$orders,$start,$size);
	    
	    
	    foreach($list['list'] as $key=>$v){
	        $performer_str = '';
	        $space = '';
	        $performer = explode(',', $v['performer']);
	        foreach($performer as $pv){
	            $performer_str .= $space .$this->task_person_arr[$pv];
	            $space = ',';
	        }
	        $list['list'][$key]['performer'] = $performer_str;
	    }
	    
	    $this->assign('list',$list['list']);
	    $this->assign('page',$list['page']);
	    $this->assign('task_state_arr',$this->task_state_arr);
	    $this->assign('install_state_arr',$this->install_state_arr);
	    $this->assign('task_type_arr',$this->task_type_arr);
	    $this->assign('task_emerge_arr',$this->task_emerge_arr);
	    $this->assign('task_area_arr',$this->task_area_arr);
	    $this->assign('task_person_arr',$this->task_person_arr);
	    $this->display('historytask');
	}
	
}