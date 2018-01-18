<?php
/**
 *@author zhang.yingtao
 *@desc 酒楼运维任务
 *@since 2017-10-19
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
use Common\Lib\UmengApi;
use Common\Lib\UmengNotice;
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
	    
	    $this->task_type_arr = array('5'=>'报修单机版','6'=>'单机版特殊更新','1'=>'网络信息采集及报价','8'=>'网络施工改造','2'=>'安装机顶盒','3'=>'安装网络+机顶盒','4'=>'报修网络版','9'=>'设备拆回');
	    $this->task_emerge_arr = array('1'=>'火烧眉毛','2'=>'急','3'=>'一般');
	    $this->task_area_arr = array('1'=>'广州','2'=>'上海','3'=>'深圳','4'=>'北京');
	    $this->task_person_arr = array('1'=>'苏苏','2'=>'张磊','3'=>'成通','4'=>'黄勇','5'=>'刘朝伟','6'=>'罗浩','7'=>'邱志宇','8'=>'施华杰','9'=>'朱毅','10'=>'张文宇','11'=>'王卫华','12'=>'李丛','13'=>'任伟','14'=>'郑伟','15'=>'冯颖亮','16'=>'外包','17'=>'汪朋');
		$this->person_device_token = array('1'=>'Ak6nFuL7K3nu4AVVAHMLUEJK1Fc-RHUDL8pBONVbdf5S');
		$this->person_device_token = array('1'=>'Ak6nFuL7K3nu4AVVAHMLUEJK1Fc-RHUDL8pBONVbdf5S','2'=>'Ap0h2sGR8i9Q2uQvA_-RKQupNMi9yI3xV4pMjv3xmDo7');
		$this->person_device_iostoken = array('1'=>'bede4b5d51c2f5dca3ec7ddfa19f54c23fb91d485da0ef880fed0b880fffde4d','2'=>'34e426055514d99da6803ed309da1b3d983035299dbae7e01c0e8f3ea99c324a');
//用的是UMENBAI_API_CONFIG
	}

	public function testduo_iossa(){
		$obj = new UmengNotice();
		$type = 'listcast';
		$list = $obj->umeng_ios($type);
		//设置属于哪个app
		$config_parm = 'opclient';
		//设置app打开后选项
		$after_a = C('AFTER_APP');
		$list->setParam($config_parm);
		$pam['device_tokens'] = implode("," ,$this->person_device_iostoken);
		$pam['time'] = time();
		$pam['alert'] = '龙的少林寺';
		$pam['badge'] = '龙的少林寺';
		$pam['sound'] = '龙独孤九剑';
		$pam['production_mode'] = 'false';
		$pam['customm'] = array(1=>'我是天谁');
		$list->sendIOSListcast($pam);

	}

	public function testdan_iossa(){
		$obj = new UmengNotice();
		$type = 'unicast';
		$unicast = $obj->umeng_ios($type);
		//设置属于哪个app
		$config_parm = 'opclient';
		//设置app打开后选项
		$after_a = C('AFTER_APP');
		$unicast->setParam($config_parm);
		$pam['device_tokens'] = $this->person_device_iostoken[1];
		$pam['time'] = time();
		$pam['alert'] = '少林寺';
		$pam['badge'] = '天龙八部';
		$pam['sound'] = '独孤九剑';
		$pam['production_mode'] = 'false';
		$pam['customm'] = array(1=>'我是谁');
		$unicast->sendIOSUnicast($pam);

	}

	public function testduo_androidsb(){
		$obj = new UmengNotice();
		$type = 'listcast';
		$listcast = $obj->umeng_android($type);
		//设置属于哪个app
		$config_parm = 'opclient';
		//设置app打开后选项
		$after_a = C('AFTER_APP');
		$listcast->setParam($config_parm);
		$pam['device_tokens'] = implode("," ,$this->person_device_token);
		$pam['time'] = time();
		$pam['ticker'] = '我的少林寺';
		$pam['title'] = '我的天龙八部';
		$pam['text'] = '我的独孤九剑';
		$pam['after_open'] = $after_a[3];
		$pam['production_mode'] = 'false';
		$pam['extra'] = array(1=>'我的飞龙在生');
		$listcast->sendAndroidListcast($pam);
	}


	public function testdan_androidsa(){
		$obj = new UmengNotice();
		$type = 'unicast';
		$unicast = $obj->umeng_android($type);
		//设置属于哪个app
		$config_parm = 'opclient';
		//设置app打开后选项
		$after_a = C('AFTER_APP');
		$unicast->setParam($config_parm);

		$pam['device_tokens'] = $this->person_device_token[1];
		$pam['time'] = time();
		$pam['ticker'] = '少林寺';
		$pam['title'] = '天龙八部';
		$pam['text'] = '独孤九剑';
		$pam['after_open'] = $after_a[3];
		$pam['production_mode'] = 'false';
		$pam['extra'] = array(1=>'飞龙在生');
		$unicast->sendAndroidUnicast($pam);

	}


	/**
	 * sendandroid
	 * @param $config_parm //设置属于哪个app如运维等
	 * @param $type//哪种广播类型 unicast,'listcast'
	 * @param $pam//所需要字段
     */
    public function sendandroid($config_parm,$type, $pam ){
		$obj = new UmengNotice();
		switch($type) {
			case 'unicast':
				$unicast = $obj->umeng_android($type);
				$unicast->setParam($config_parm);
				$unicast->sendAndroidUnicast($pam);
				break;
			case 'listcast':
				$unicast = $obj->umeng_android($type);
				$unicast->setParam($config_parm);
				$unicast->sendAndroidListcast($pam);
				break;

		}

	}




	/**
	 * sendios
	 * @param $config_parm //设置属于哪个app如运维等
	 * @param $type//哪种广播类型 unicast,'listcast'
	 * @param $pam//所需要字段
	 */
	public function sendios($config_parm,$type, $pam ){
		$obj = new UmengNotice();
		switch($type) {
			case 'unicast':
				$unicast = $obj->umeng_ios($type);
				$unicast->setParam($config_parm);
				$unicast->sendIOSUnicast($pam);
				break;
			case 'listcast':
				$unicast = $obj->umeng_ios($type);
				$unicast->setParam($config_parm);
				$unicast->sendIOSListcast($pam);
				break;

		}

	}


	public function test(){
		//发送单人任务

		$umengApi = new UmengApi();
		$android_params = array();
		$ios_params = array();
		$android_params['type'] = 'unicast';
		$android_params['device_tokens']    = $this->person_device_token[1];
		$android_params['display_type'] = 'notification';
		$android_params['ticker']  = '我是成龙';
		$android_params['title']   = 'A计划';
		$android_params['text']    = '我是谁';
		$android_params['after_open'] = 'go_custom';
		$android_params['production_mode'] = "false";
		//$ext_arr = array();
		$ext_arr = array('type'=>1,'params'=>json_encode(array('error_id'=>555)));

		$ret = $umengApi->umeng_api_android_single($android_params,$ext_arr);

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
		$publisher = I('publisher');
		$task_type = I('task_type');
		
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$offsets = $start+1;
		$this->assign('offsets',$offsets);
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
		if($publisher){
		    $where .=" and publisher like '%$publisher%'";
		    $this->assign('publisher',$publisher);
		}
		if($task_type){
		    $where .=" and task_type=$task_type";
		    $this->assign('task_type',$task_type);
		}
		
		$m_option_task = new \Admin\Model\OptiontaskoldModel();
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
		if($data['performer']) {
			$performer = implode(',', $data['performer']);
		} else {
			$performer = '';
		}
		if(empty($data['palan_finish_time'])) {
			$data['palan_finish_time'] = date("Y-m-d");
		}
	    if($data['id']){
	        $id = $data['id'];
	        unset($data['id']);
	        unset($data['ajax']);


	        $data['performer'] = $performer; 
	        $data['update_time'] = date('Y-m-d H:i:s');
	        $m_option_task = new \Admin\Model\OptiontaskoldModel();
	        $ret = $m_option_task->where('id='.$id)->save($data);
	        if($ret){
	            $this->output('修改成功', 'optiontask/index', 1);
	        }else {
	            $this->error('修改失败');
	        }
	    }else {
	        unset($data['id']);
	        unset($data['ajax']);
	        $data['performer'] = $performer;
	        //print_r($data);exit;
	        $m_option_task = new \Admin\Model\OptiontaskoldModel();
	        $ret = $m_option_task->add($data);
	        if($ret){
	            $this->output('发布成功', 'optiontask/index', 1);
	        }else {
	            $this->error('发布失败');
	        }
	    }
	    
	}
	public function edit(){
	     $m_option_task = new \Admin\Model\OptiontaskoldModel();
	    $id = I('get.id',0,'intval');
	    $list = $m_option_task->where('id='.$id)->find();
	    $performer = $list['performer'];
		$list['palan_finish_time'] = date("Y-m-d",strtotime($list['palan_finish_time']));
	    $performer_arr = explode(',', $performer);
	    $this->assign('performer_arr',$performer_arr);
	    $this->assign('vinfo',$list);
	    $this->assign('install_state_arr',$this->install_state_arr);
	    $this->assign('task_state_arr',$this->task_state_arr);
	    $this->assign('task_type_arrde',$this->task_type_arr);
	    $this->assign('task_emerge_arr',$this->task_emerge_arr);
	    $this->assign('task_area_arr',$this->task_area_arr);
	    $this->assign('task_person_arr',$this->task_person_arr);
	    $this->assign('id',$id);
	    $this->display('add');
	}
	public function delete(){
	    $id = I('get.id');
	    $m_option_task = new \Admin\Model\OptiontaskoldModel();
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
	    $publisher = I('publisher');
	    $task_type = I('task_type');
	    
	    $offsets = $start+1;
	    $this->assign('offsets',$offsets);
	    
	    
	    $where['state'] = array('in','4');
	    $where['flag'] = 0;
	    if($publisher){
	        $where['publisher'] = array('like',"%$publisher%");
	        $this->assign('publisher',$publisher);
	    }
	    if($task_type){
	        $where['task_type'] = $task_type;
	        $this->assign('task_type',$task_type);
	    }
	    $m_option_task = new \Admin\Model\OptiontaskoldModel();
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