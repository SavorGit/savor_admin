<?php
/**
 * Project savor_admin
 *
 * @author baiyutao <------@gmail.com> 2017-5-9
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;

/**
 * Class BoxawardController
 * 机顶盒奖励控制器
 * @package Admin\Controller
 */
class BoxawardController extends BaseController{

	/**
	 *
     */
	public function __construct() {
		parent::__construct();
	}


	/**
	 * 机顶盒配置奖励列表
	 * @access public
	 * @param
	 * @return mixed
	 */
	public function rplist(){

		$hotelModel = new \Admin\Model\HotelModel();
		$awardConfigModel = new \Admin\Model\AwardConfigModel();
		$boxAwardModel = new \Admin\Model\BoxAwardModel();
		$starttime = I('post.starttime','');
		$endtime = I('post.endtime','');
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','baw.`date_time` desc,baw.`create_time`');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$rtype = array(
			'1'=>'包间',
			'2'=>'大厅',
			'3'=>'等候区',
		);

		$where = "1=1";

		if($starttime){
			$this->assign('s_time',$starttime);
			$where .= "	AND   baw.`date_time` >= '{$starttime}' ";
		}
		if($endtime){
			$this->assign('e_time',$endtime);
			$where .= "	AND baw.`date_time` <=  '{$endtime}'";
		}
		$result = $boxAwardModel->getList($where,$orders,$start,$size);

		$ind = $start;

		foreach ($result['list'] as &$val) {


			foreach($rtype as $rk=>$rv){
				if ($rk == $val['rtp']){
					$val['rname'] = $val['rname'].'('.$rv.')';
					break;
				}
			}
			$val['indnum'] = ++$ind;
			$bpize_arr = json_decode($val['bpr'],true);
			$str = '';
			foreach($bpize_arr as $bk=>$bv){
				$str .= "<span>{$bv['prize_name']}:</span>{$bv['prize_num']}个<span></span><span>概率:{$bv['prize_pos']}</span><br/>";
			}
			$val['bpr'] = $str;
		}
		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		//酒楼列表
		$map['flag'] = 0;
		$map['state'] = 1;
		$list = $hotelModel->getInfo('id,name', $map);
		$this->assign('hlist', $list);
		$this->display('screenlist');
	}
	/*
	 * 复制奖励对应日期
	 */
	public function copyaward(){

		$boxAwardModel = new \Admin\Model\BoxAwardModel();
		$boxid = I('post.regiona3_id');
		$starttime = I('post.copydatestart','');
		$endtime = I('post.copydateend','');
		$date_tim = date("Y-m-d");
		if($boxid == -1){
			$this->error('请选择机顶盒');
		}
		if(!$starttime){
			$this->error('开始日期不得为空');
		}
		if(!$endtime){
			$this->error('结束日期不得为空');
		}
		if($endtime < $date_tim) {
			$this->error('结束日期不得小于等于当前日期');
		}
		/*if($starttime >= $endtime){
			$this->error('开始日期不得大于结束日期');
		}*/
		//复制日期不得复制已经存在
		$map['date_time'] = $endtime;
		$map['box_id'] = $boxid;
		$map['flag'] = 1;
		$cot = $boxAwardModel->getCount($map);
		if($cot>0){
			$this->error('该机顶盒对应批次日期已经存在');
		}
		$map['date_time'] = $starttime;

		$res = $boxAwardModel->getAwardData($map);
		$map['create_time'] = date("Y-m-d H:i:s");
		$map['update_time'] = $map['create_time'];
		$map['date_time'] = $endtime;
		$priv_arr = array('id','create_time','update_time','date_time','box_id');
		if ($res) {
			foreach($res as $k=>$v){
				foreach($v as $ks=>$vs){
					if(in_array($ks,$priv_arr)){
						continue;
					}else{
						$map[$ks] = $vs;
					}
				}

			}
			$acttype = 0;
			$result = $boxAwardModel->addData($map, $acttype);
			if($result) {
				$this->output('复制成功!', 'boxaward/rplist',2);
			} else {
				$this->output('复制失败!', 'boxaward/addprize', 2, 0);
			}

		} else {
			$this->error('该机顶盒{$starttime}没有数据，请重新选择');
		}
	}


	/**
	 *添加机顶盒奖励
	 * @access public
	 *
     */
	public function addPrize(){
		$hotelModel = new \Admin\Model\HotelModel();
		$awardConfigModel = new \Admin\Model\AwardConfigModel();
		$boxAwardModel = new \Admin\Model\BoxAwardModel();
		$acttype = I('acttype', 0, 'int');
		$where = '1=1';
		if($acttype == 0) {

			if(IS_POST){
				//机顶盒唯一所以box_id是唯一的
				$id          = I('post.id', '', 'int');
				//酒楼id
				$hid  = I('post.region1_id',0);
				//包间id
				$roomid  = I('post.region2_id',0);
				//机顶盒id
				$boxid  = I('post.region3_id',0);
				$flag  = I('post.isenable',0);
				$date_time  = I('post.addawardtime','');

				if(!$date_time){
					$date_time = date("Y-m-d");
				}
				if($hid<0){
					$this->error('酒楼不可为空，请重新选择');
				}
				if($roomid<0){
					$this->error('包间不可为空，请重新选择');
				}
				if($boxid<0){
					$this->error('机顶盒不可为空，请重新选择');
				}
				$ap['box_id'] = $boxid;
				$ap['flag'] = 1;
				$ap['date_time'] = $date_time;
				$count = $boxAwardModel->getCount($ap);
				if($count){
					$this->error('日期若不设置默认为当前日期，而当前日期机顶盒抽奖已经设置');
				}
				$firstnum   = I('post.firstnum',0);
				$firstpos= I('post.firstpos',0);
				$secondnum       = I('post.secondnum',0);
				$secondpos      = I('post.secondpos',0);
				$thirdnum    = I('post.thirdnum',0);
				$thirdpos    = I('post.thirdpos',0);
				$fid = I('post.fhidden',0);
				$sid = I('post.shidden',0);
				$tid = I('post.thidden',0);
				if(!$firstnum){
					$firstnum = 0;
				}
				if(!$secondnum){
					$secondnum = 0;
				}
				if(!$thirdnum){
					$thirdnum = 0;
				}
				$firstposarr = $awardConfigModel->getOne($fid);
				$secondposarr = $awardConfigModel->getOne($sid);
				$thirdposarr = $awardConfigModel->getOne($tid);
				if($firstpos ===  ''){
					$firstpos = $firstposarr['pos'];
				}
				if($secondpos ===  ''){
					$secondpos = $secondposarr['pos'];
				}
				if($thirdpos ===  ''){
					$thirdpos = $thirdposarr['pos'];
				}
				if($thirdpos+$secondpos+$firstpos!=100){
					$this->error('概率和必须等于100');
				}
				$pr[]= array('prize_id'=>$fid,'prize_name'=>$firstposarr['name'],'prize_num'=>$firstnum,'prize_pos'=>$firstpos,'prize_level'=>$firstposarr['level']);
				$pr[]= array('prize_id'=>$sid,'prize_name'=>$secondposarr['name'],'prize_num'=>$secondnum,'prize_pos'=>$secondpos,'prize_level'=>$secondposarr['level']);
				$pr[]= array('prize_id'=>$tid,'prize_name'=>$thirdposarr['name'],'prize_num'=>$thirdnum,'prize_pos'=>$thirdpos,'prize_level'=>$thirdposarr['level']);
				$dap['box_id'] = $boxid;
				$dap['room_id'] = $roomid;
				$dap['hotel_id'] = $hid;
				$dap['prize'] = json_encode($pr);
				$dap['flag'] = $flag;
				$dap['create_time'] = date("Y-m-d H:i:s");
				$dap['update_time'] = $dap['create_time'];
				$dap['date_time'] = $date_time;

				$result = $boxAwardModel->addData($dap, $acttype);
				if($result) {
					$this->output('操作成功!', 'boxaward/rplist');
				} else {
					$this->output('操作失败!', 'boxaward/addprize', 2, 0);
				}
			}else{
				$vinfo['isenable'] = 1;
				$this->assign('vinfo', $vinfo);
				$map['flag'] = 0;
				$map['state'] = 1;
				$list = $hotelModel->getInfo('id,name', $map);
				$this->assign('hlist', $list);
				$con_arr = $awardConfigModel->select();
				$this->assign('aw_co_list', $con_arr);
				$this->display('addaward');
			}
		}

		//非提交处理
		if(1 === $acttype) {
			$bawid = I('id', 0, 'int');
			if(IS_POST){
				$flag  = I('post.isenable',0,'int');
				$firstnum   = I('post.firstnum',0,'int');
				$firstpos= I('post.firstpos',0,'int');
				$secondnum       = I('post.secondnum',0,'int');
				$secondpos      = I('post.secondpos',0,'int');
				$thirdnum    = I('post.thirdnum',0,'int');
				$thirdpos    = I('post.thirdpos',0,'int');
				if($thirdpos+$secondpos+$firstpos!=100){
					$this->error('概率和必须等于100');
				}
				$drp['update_time'] = date("Y-m-d H:i:s");
				$drp['id'] = $bawid;
				$drp['flag'] = $flag;
				$result = $boxAwardModel->find($bawid);
				$bpize_arr = json_decode($result['prize'],true);

				foreach($bpize_arr as &$bv){

					if($bv['prize_name'] == '一等奖'){
						$bv['prize_num'] = $firstnum;
						$bv['prize_pos'] = $firstpos;
					}
					if($bv['prize_name'] == '二等奖'){
						$bv['prize_num'] = $secondnum;
						$bv['prize_pos'] = $secondpos;
					}
					if($bv['prize_name'] == '三等奖'){
						$bv['prize_num'] = $thirdnum;
						$bv['prize_pos'] = $thirdpos;
					}
				}
				$drp['prize'] = json_encode($bpize_arr);
				$res = $boxAwardModel->addData($drp, $acttype);
				if($res) {
					$this->output('修改成功!', 'boxaward/rplist');
				} else {
					$this->output('修改失败!', 'boxaward/addprize', 2, 0);
				}
			}else{
				$this->assign('acttype', $acttype);
				$where .= "	AND baw.`id` =  '{$bawid}'";
				$result = $boxAwardModel->getOneBoxAward($where);

				foreach($result as $rk=>$val){
					if($rk == 'bpr'){
						$result[$rk] = json_decode($val,true);

					}
				}
				$this->assign('vlist', $result);
			}
			$this->display('editaward');
		}

	}

	/*
	 * 获取奖励信息
	 */
	public function getaward(){
		$boxAwardModel = new \Admin\Model\BoxAwardModel();
		$roomModel = new \Admin\Model\RoomModel();
		$boxModel = new \Admin\Model\BoxModel();
		$htype = I('post.htype');
		$aid = I('post.aid',0);
		$rtype = array(
			'1'=>'包间',
			'2'=>'大厅',
			'3'=>'等候区',
		);
		if($aid<=0){
			$result = array();
			$this->ajaxReturn($result);
		}
		//0 1 2 分别对应 酒楼，包间，机顶盒
		if($htype == 0){
			//查找包间
			$map['hotel_id'] = $aid;
			$map['flag'] = 0;
			$map['state'] = 1;
			$result = $roomModel->field('`id`,`name`,`type`')->where($map)->select();
			foreach($result as $k=>$v){
				foreach($rtype as $rk=>$rv){
					if ($rk == $result[$k]['type']){
						$result[$k]['name'] = $result[$k]['name'].'('.$rv.')';
						break;
					}
				}
			}

		}else if($htype == 1){
			$dap['room_id'] = $aid;
			$dap['flag'] = 0;
			$dap['state'] = 1;
			$result = $boxModel->field('`id`,`name`,`mac`')->where($dap)->select();
			foreach($result as $k=>$v){
				$result[$k]['name'] = $result[$k]['name'].'('.$result[$k]['mac'].')';
			}

		}
		$this->ajaxReturn($result);

	}




	/*
	 * 修改状态
	 */

	public function changestate(){
		$cid = I('post.cid');
		$save = array();
		$save['state'] = I('post.state');
		$catModel = new CategoModel;
		$res_save = $catModel->where('id='.$cid)->save($save);
		if($res_save){
			echo 1;
		} else {
			echo 0;
		}
	}
    /**
     * @desc 机顶盒抽奖日志
     */
	public function awardLogList(){
	    $size   = I('numPerPage',50);//显示每页记录数
	    $this->assign('numPerPage',$size);
	    $start = I('pageNum',1);
	    $this->assign('pageNum',$start);
	    $order = I('_order','time');
	    $this->assign('_order',$order);
	    $sort = I('_sort','desc');
	    $this->assign('_sort',$sort);
	    $orders = $order.' '.$sort;
	    $start  = ( $start-1 ) * $size;
	    
	    $where = "";
	    
	    $m_award_log = new \Admin\Model\AwardLogModel();
	    $result = $m_award_log->getList($where,$orders,$start,$size);
	    $this->assign('list', $result['list']);
	    $this->assign('page',  $result['page']);
	    $this->display('award_log');
	}


}
