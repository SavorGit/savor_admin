<?php
/**
 *@author hongwei
 * @desc 心跳显示列表
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;

class BoxawardController extends BaseController{

	public function __construct() {
		parent::__construct();
	}


	/**
	 * 机顶盒失联列表
	 * @access public
	 * @param $dtype 1:当年，2当月，3当日(即是昨天的)，4指定日期,5所有次数
	 * @return [type] [description]
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
		$order = I('_order','`baw`.`update_time`');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$source_type = I('source_type','');

		$where = "1=1";
		$hname = I('hotelname','');
		if($hname){
			$where .="	AND hotelname = '{$source_type}'";
			$this->assign('sot',$source_type);
		}
		if($starttime){
			$this->assign('s_time',$starttime);
			$where .= "	AND `create_time` >= '{$starttime}'";
		}
		if($endtime){
			$this->assign('e_time',$endtime);
			$where .= "	AND `create_time` <=  '{$starttime}'";
		}
		$result = $boxAwardModel->getList($where,$orders,$start,$size);

		$ind = $start;
		foreach ($result['list'] as &$val) {
			$val['indnum'] = ++$ind;
			$bpize_arr = json_decode($val['bpr'],true);
			$str = '';
			foreach($bpize_arr as $bk=>$bv){
				$bv = explode(',', $bv);
				$str .= "<span>$bv[1]:</span>$bv[2]个<span></span><span>概率:$bv[3]</span><br/>";
			}
			$val['bpr'] = $str;
		}
		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('screenlist');
	}


	/**
	 * 新增分类
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
				if($hid<0 || $roomid<0 || $boxid<0){
					$this->error('选择错误请重新选择');
				}
				$ap['box_id'] = $boxid;
				$ap['flag'] = 1;
				$count = $boxAwardModel->getCount($ap);
				if($count){
					$this->error('机顶盒已经存在');
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
				$pr['f'] = $fid.','.$firstposarr['name'].','.$firstnum.','.$firstpos;
				$pr['s'] = $sid.','.$secondposarr['name'].','.$secondnum.','.$secondpos;
				$pr['t'] = $tid.','.$thirdposarr['name'].','.$thirdnum.','.$thirdpos;
				$dap['box_id'] = $boxid;
				$dap['room_id'] = $roomid;
				$dap['hotel_id'] = $hid;
				$dap['prize'] = json_encode($pr);
				$dap['flag'] = $flag;
				$dap['create_time'] = date("Y-m-d H:i:s");
				$dap['update_time'] = $dap['create_time'];
				$dap['date_time'] = date("Y-m-d");

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
				$drp['update_time'] = date("Y-m-d H:i:s");
				$drp['id'] = $bawid;
				$drp['flag'] = $flag;
				$result = $boxAwardModel->find($bawid);
				$bpize_arr = json_decode($result['prize'],true);
				foreach($bpize_arr as $bk=>$bv){
					$bav = explode(',', $bv);
					if($bk == 'f'){
						$bav[2] = $firstnum;
						$bav[3] = $firstpos;
					}
					if($bk == 's'){
						$bav[2] = $secondnum;
						$bav[3] = $secondpos;
					}
					if($bk == 't'){
						$bav[2] = $thirdnum;
						$bav[3] = $thirdpos;
					}
					$bar = implode(',', $bav);
					$bpize_arr[$bk] = $bar;
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
						$bpize_arr = json_decode($val,true);
						foreach($bpize_arr as $bk=>$bv){
							$bav = explode(',', $bv);
							$result[$bk] = $bav;
						}
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
					if ($rk == $result[$k][type]){
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
