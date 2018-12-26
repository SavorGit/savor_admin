<?php
/**
 * Project savor_admin
 *
 * @author baiyutao <------@gmail.com> 2017-06-19
 */
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Common\Lib\SavorRedis;
use Common\Lib\Ucpaas;

/**
 * Class CheckaccountController
 * 对账单列表
 * /
 * @package Admin\Controller
 */
class CheckaccountController extends BaseController{


	/**
	 * @desc 对账单列表
	 *
	 */
	public function rplist(){
		$starttime = I('post.starttime','');
		$endtime = I('post.endtime','');
		$stateModel =  new \Admin\Model\AccountStatementModel();
		$size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$order = I('_order','sast.create_time');
		$this->assign('_order',$order);
		$sort = I('_sort','desc');
		$this->assign('_sort',$sort);
		$orders = $order.' '.$sort;
		$start  = ( $start-1 ) * $size;
		$fee_type = C('fee_type');
		$where = "1=1";
		if($starttime){
			$this->assign('s_time',$starttime);
			$where .= "	AND date_format(sast.create_time,'%Y-%m-%d') >= '{$starttime}'";
		}
		if($endtime){
			$this->assign('e_time',$endtime);
			$where .= "	AND date_format(sast.create_time,'%Y-%m-%d') <=  '{$endtime}'";
		}
		$result = $stateModel->getAll($where,$orders, $start,$size);
		$ind = $start;
		foreach ($result['list'] as &$val) {
			$val['indnum'] = ++$ind;
			foreach ($fee_type as $fk=>$fv){
				if($fk == $val['cost_type']){
					$val['cost_type'] = $fv;
				}
			}
		}

		$this->assign('list', $result['list']);
		$this->assign('page',  $result['page']);
		$this->display('accountlist');
	}

	private function sendMessage($info){
		//$sjson  = '{"resp":{"respCode":"000000","templateSMS":{"createDate":"20170621131304","smsId":"3bcd56624d1d60a6e5830c3886f2f31d"}}}';
		$fe_start = $info['fee_start'];
		$fe_end = $info['fee_end'];
		$tel= $info['tel'];
		$detailid = $info['id'];
		$short = encrypt_data($detailid);
		$shortlink = $this->host_name.'/admin/hotelbill/index?id='.$short;
		$shortlink = shortUrlAPI(1, $shortlink);

		$param="$shortlink";
		$bool = $this->sendToUcPa($info,$param);
		return $bool;
	}

	public function resendMsg(){
		$did = I('get.detailid',0);
		$now = time();
		$statedetailModel = new \Admin\Model\AccountStatementDetailModel();
		$accountLogModel = new \Admin\Model\AccountMsgLogModel();
		$statenoticeModel = new \Admin\Model\AccountStatementNoticeModel();
		$info = $statedetailModel->getWhereSql($did);
		if($info['state'] == 1){
			if($info['check_status'] == 0 || $info['check_status'] == 1 || $info['check_status'] == 2) {
				//获取notice表更新时间
				$field = 'count,id noticeid, f_type ftype, update_time';
				$dat['detail_id'] = $did;
				$dat['f_type'] = 1;
				$notice_arr = $statenoticeModel->getWhere($dat, $field);

				$notice_id = $notice_arr['noticeid'];
				$notice_uptime = strtotime($notice_arr['update_time']);
				$count = $notice_arr['count'];
				//一份钟
				//考虑第一次的情况,获取redis
				$redis  =  SavorRedis::getInstance();
				$redis->select(15);
				$rkey = 'savor_account_statement_notice';
				$max = $redis->lsize($rkey);
				$data = $redis->lgetrange($rkey,0,$max);
				if(in_array($did, $data)){
					$this->error('计划任务未执行不允许点击');
				}
				$map['detail_id'] = $did;
				$order = 'id desc';
				$loginfo =	$accountLogModel->getOne($map, $order);
				$log_uptime = strtotime($loginfo['update_time']);
				if($now-$log_uptime<3600){
					$this->error('一小时内不允许重复发送');
				}
				if( $count >= 8 ){
					$this->error('发送失败超过最大限制');
				}
				//发送短信
				//清空原有notice状态
				$dap['status'] = 0;
				$where = "id = ".$notice_id;
				$statenoticeModel->saveData($dap,$where);
				$m_state = $this->sendMessage($info);
				if($m_state){
					//更新notice状态
					$dap['status'] = 1;
					$dap['update_time'] = date("Y-m-d H:i:s",$now);
					$statenoticeModel->saveData($dap,$where);
					$wherea = 'id = '.$did;
					$detail['check_status'] = 0;
					$statedetailModel->saveData($detail,$wherea);
					$this->output('发送短信成功', U('checkaccount/showHotel?statementid='.$did),2);
				}else{
					$dap['update_time'] = date("Y-m-d H:i:s",$now);
					$dap['count'] = $count+1;
					$statenoticeModel->saveData($dap,$where);
				}

			}else{
				$this->error('状态不允许');
			}
		}else{
			$this->error('无发送权限');
		}
	}

	/*
	 * @desc 确认付款
	 */
	public function confirmPayDone(){
		$did = I('get.detailid',0);
		$statementid = I('get.statementid',0);
		$now = time();
		if($did){
			$statedetailModel = new \Admin\Model\AccountStatementDetailModel();
			$accountLogModel = new \Admin\Model\AccountMsgLogModel();
			$info = $statedetailModel->find($did);
			$ch_staus =  $info['check_status'];
			$state = $info['state'];
					if($state==1){
						//根据log判定
						$map['detail_id'] = $info['id'];
						$order = 'id desc';
					   $loginfo =	$accountLogModel->getOne($map, $order);
						$log_uptime = strtotime($loginfo['update_time']);
						if($now-$log_uptime<300){
							$this->error('由于第三方短信运营商规则，五分钟之内不允许重复下发短信，请稍后再试');
						}else{
							//更新状态
							$dat['check_status'] = 3;
							$where = 'id = '.$did;
							$statedetailModel->saveData($dat, $where);
							//下发短信
							$info = $statedetailModel->getWhereSql($did);
							$this->sendPayMessage($info);
							//重新载入
							$this->output('确认付款成功!', U('checkaccount/showHotel?statementid='.$statementid),2);
						}
					}else{
						$this->error('不允许');
					}



		}else{
			$this->error('传参不能为空');
		}
	}


	/*
	 * @desc 显示对账酒楼明细
	 */
	public function showhotel(){

		$statementid = I('statementid',0);
		$statedetailModel = new \Admin\Model\AccountStatementDetailModel();
		$statenoticeModel = new \Admin\Model\AccountStatementNoticeModel();
		$stateModel = new \Admin\Model\AccountStatementModel();
		$info = $stateModel->find($statementid);
		$summary = $info['summary'];
		$this->assign('instruction',$summary);
		if($statementid){
			$size   = I('numPerPage',50);//显示每页记录数
			$this->assign('numPerPage',$size);
			$start = I('pageNum',1);
			$this->assign('pageNum',$start);
			$order = I('_order','sdet.id');
			$this->assign('_order',$order);
			$sort = I('_sort','asc');
			$this->assign('_sort',$sort);
			$orders = $order.' '.$sort;
			$start  = ( $start-1 ) * $size;
			$where = "1=1";
			$where .= " AND sdet.statement_id = ".$statementid;
			$result = $statedetailModel->getAll($where,$orders, $start,$size);
			//var_export($result['list']);
			$ind = $start;
			$notice_state = C('NOTICE_STATAE');
			$check_state = C('CHECK_STATAE');
			foreach ($result['list'] as &$val){

				if($val['state']!=1){
					$dinfo = $statedetailModel->find($val['detailid']);
					$val['hotelid'] = $dinfo['hotel_id'];
					$val['name'] = $dinfo['hotel_name'];
					$val['money'] = $dinfo['money'];
				}
				$val['indnum'] = ++$ind;
				foreach($check_state as $ch=>$cv){
                      if($ch == $val['check_status']) {
						  if($val['state'] !=1){
							  if($val['state'] == 4){
								  if( $val['check_status'] != 3){
									  $val['cont'] = '确认付款完成';
									  $val['ch_mes'] = '';
								  }else{
									  $val['cont'] = '2';
									  $val['ch_mes'] = $cv;
								  }

							  }else{
								  $val['cont'] = 2;
								  $val['ch_mes'] = '';
							  }

						  }else{
							  $val['ch_mes'] = $cv;
							  if($ch == 3) {
								  $val['cont'] = '2';
							  }else{
								  $val['cont'] = '确认付款完成';
							  }

						  }
						  break;
					  }
				}
				foreach($notice_state as $nh=>$nv){
					if($nh == $val['state']) {
						if($val['state'] == 1) {
							$dat['detail_id'] = $val['detailid'];
							$dat['f_type'] = 1;
							$notice_arr = $statenoticeModel->getWhere($dat);
							$nostus = $notice_arr['status'];
							if($nostus == 1){
								$val['no_mes'] = '发送成功';
							}else {
								$val['no_mes'] = '发送中';
							}
							break;
						} else {
							$val['no_mes'] = $nv;
							break;
						}
					}
				}

			}
			$this->assign('statementid', $statementid);
			$this->assign('list', $result['list']);
			$this->assign('page',  $result['page']);
			$this->display('showHotel');
		}
	}


	/**
	 * 添加对账单信息,录入对账单
	 * @access public
	 */
	public function addcheckaccount(){
		//费用类型
		$fee_type = C('fee_type');
		//各种地址
		$accountconfigModel =  new \Admin\Model\AccountConfigModel();
		$where = ' 1=1 ';
		$order =  'id asc';
		$configinfo = $accountconfigModel->getAll('', $where, $order);
		$this->assign('fee_list', $fee_type);
		$this->assign('account_config', $configinfo);
		return $this->display('addAccount');
	}



	/*
	 * @desc 获取地址配置信息
	 */

	public function getaccountinfo(){
		$cid = I('post.tid');
		$accountModel = new \Admin\Model\AccountConfigModel();
		$res_save = $accountModel->find($cid);
		$res_save['receipt_tel'] = str_replace(',','  ', $res_save['receipt_tel']);
		if($res_save){
			$result = array('code'=>1,'list'=>$res_save);
		}
		echo json_encode($result);
	}




	/*
 * 处理excel数据
 */
	public function analyseExcel(){
		$path = $_POST['excelpath'];
		if  ($path == '') {
			$res = array('error'=>0,'message'=>array());
			echo json_encode($res);
		}
		$type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
		vendor("PHPExcel.PHPExcel.IOFactory");
		if ($type == 'xlsx' || $type == 'xls') {
			$objPHPExcel = \PHPExcel_IOFactory::load($path);
		} elseif ($type == 'csv') {
			$objReader = \PHPExcel_IOFactory::createReader('CSV')
				->setDelimiter(',')
				->setInputEncoding('GBK')//不设置将导致中文列内容返回boolean(false)或乱码
				->setEnclosure('"')
				->setLineEnding("\r\n")
				->setSheetIndex(0);
			$objPHPExcel = $objReader->load($path);
		} else {
			//$this->output('文件格式不正确', 'importdata', 0, 0);
			$res = array('error'=>1,'message'=>'文件格式不正确');
			echo json_encode($res);
			die;
		}

		$sheet = $objPHPExcel->getSheet(0);
		//获取行数与列数,注意列数需要转换
		$highestRowNum = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$highestColumnNum = \PHPExcel_Cell::columnIndexFromString($highestColumn);
		if($highestColumnNum == 2){
			$res = array('error'=>1,'message'=>'必须为三列');
			echo json_encode($res);
			die;
		}
		//取得字段，这里测试表格中的第一行为数据的字段，因此先取出用来作后面数组的键名
		$filed = array();
		for ($i = 0; $i < $highestColumnNum; $i++) {
			$cellName = \PHPExcel_Cell::stringFromColumnIndex($i) . '1';
			$cellVal = $sheet->getCell($cellName)->getValue();//取得列内容
			$filed[] = $cellVal;
		}
		if($filed[0] != 'id' || $filed[1] != 'name' || $filed[2] != 'money') {
			$res = array('error'=>1,'message'=>'第一行对应三列必须为id,name,money');
			echo json_encode($res);
			die;
		}

		//开始取出数据并存入数组
		$data = array();
		for ($i = 2; $i <= $highestRowNum; $i++) {//ignore row 1
			$row = array();
			for ($j = 0; $j < $highestColumnNum; $j++) {
				$cellName = \PHPExcel_Cell::stringFromColumnIndex($j) . $i;
				$cellVal = (string)$sheet->getCell($cellName)->getValue();
				if($cellVal === 'null'){
					$cellVal = '';
				}
				if($cellVal === '"' ||  $cellVal === "'"){
					$cellVal = '#';
				}
				if($cellVal === 'null'){
					$cellVal = '';
				}
				$row[$filed[$j]] = $cellVal;
			}
			$data [] = $row;
		}
		$res = array('error'=>0,'message'=>$data);
		echo json_encode($res);
		die;
	}






	public function doaddCheckAccount(){
		$user = new \Admin\Model\UserModel();
		$statementModel = new \Admin\Model\AccountStatementModel();
		$statedetailModel = new \Admin\Model\AccountStatementDetailModel();
		$statenoticeModel = new \Admin\Model\AccountStatementNoticeModel();
		$date_now         = date('Y-m-d H:i:s');
		$start_date   = I('post.starttime','');
		$end_date   = I('post.endtime','');
		$rec_addr_id   = I('post.rec_addr','');
		$fee = I('post.fee');
		$remark= I('post.remark','','trim');
		$hotel_acc_arr   = json_decode ($_POST['accountjson'],true);
		if(empty($rec_addr_id)){

			$this->error('请选择发票邮寄地址');
		}
		if(empty($hotel_acc_arr)){
			$this->error('请导入酒楼金额明细EXCEL');
		}
		
		$where =' 1=1';
		if(empty($start_date) || empty($end_date)){
			$this->error('开始结束时间不得为空','notclose');
		}
		if($start_date && $end_date){
			if($end_date<=$start_date){
				$this->error('结束时间应在开始时间之后','notclose');
			}
		}
		//判酒楼是否已经存在以及detail表是否有
		$hotel_acc_info = $this->judgeHotel($hotel_acc_arr,$start_date, $end_date,$fee);

		$statement_num = 0;
		foreach($hotel_acc_info as $hk=>$hv){
			$statement_num++;
			if($hv['state'] == 2 || $hv['state'] == 3 || $hv['state'] == 4 || $hv['state'] == 5 || $hv['state'] == 6 ||  $hv['state'] == 7){
				continue;
			}else{
				if(!isset($hv['state'])){
					$hotel_acc_info[$hk]['state'] = 1;
				}

			}

		}
		$err1 = '';
		$err2 = '';
		$err3 = '';
		$err4 = '';
		$err5 = '';
		$err6 = '';
		$succ = 0;
		$fail = 0;
		foreach($hotel_acc_info as $ht=>$hv){
			if($hv['state'] == 1){
				$succ++;
				continue;
			}else{
				if($hv['state'] == 2){
					$err1 .= "<br/><br/>".$hv['name'].'(id:'.$hv['id'].')'.'     失败原因:该酒楼不存在';
				}else if($hv['state'] == 3){
					$err2 .= "<br/><br/>".$hv['name'].'(id:'.$hv['id'].')'.'     失败原因:该酒楼已经下发';
				}else if($hv['state'] == 4){
					$err3 .= "<br/><br/>".$hv['name'].'(id:'.$hv['id'].')'.'     失败原因:该酒楼对账单人联系电话为空';
				}else if($hv['state'] == 5){
					$err4 .= "<br/><br/>".$hv['name'].'(id:'.$hv['id'].')'.'     失败原因:该酒楼下发金额为负值';
				}else if($hv['state'] == 6){
					$err5 .= "<br/><br/>".$hv['name'].'(id:'.$hv['id'].')'.'     失败原因:该酒楼EXCEL表中已经存在';

				}else if($hv['state'] == 7){
					$err6 .= "<br/><br/>".$hv['name'].'(id:'.$hv['id'].')'.'     失败原因:该酒楼下发金额为空值';

				}
				$fail++;


			}

		}
		$sa = '发送失败明细'.$err1.$err2.$err3.$err4.$err5.$err6;
		$sustr = '发送成功'.$succ.'家酒楼,失败'.$fail.'家.由于使用第三方平台，可能有延时<br/><br/>';
		if($fail == 0){
			$sustr = $sustr;
		}else{
			$sustr = $sustr.$sa;
		}
		//添加savor_account_statement表operator operatorid
		$save['summary']  = '';
		$save['fee_start']  = $start_date;
		$save['fee_end']  = $end_date;
		$save['cost_type'] = $fee;
		$save['receipt_addrid'] = $rec_addr_id;
		$save['remark'] = $remark;
		$save['create_time'] = $date_now;
		$save['update_time'] = $date_now;
		$save['count'] = $statement_num;
		$userInfo = session('sysUserInfo');
		$user_info = $user->getUserInfo($userInfo['id']);
		$save['operatorid'] = $user_info['id'];
		$res = $statementModel->add($save);
		$insertid = $statementModel->getLastInsID();
		if($res){
			//添加savor_account_statement_detail表
			$datalist = array();
			foreach ($hotel_acc_info as $hk=>$hv) {
				if(is_null($hv['id'])){
					$hv['id'] = 'null';
				}
				if(is_null($hv['money'])){
					$hv['money'] = 'null';
				}
					$datalist[] = array(
						'hotel_id'=>$hv['id'],
						'check_status' =>0,
						'statement_id' =>$insertid,
						'money' =>$hv['money'],
						'state'=>$hv['state'],
						'err_msg'=>'',
						'fee_start'=>$start_date,
						'fee_end' =>$end_date,
						'cost_type' => $fee,
						'create_time' => $date_now,
						'update_time' => $date_now,
						'hotel_name'=>$hv['name'],

					);
			}

			$rdetail = $statedetailModel->addAll($datalist);
			if($rdetail){
				$rd = array();
				$rd['statement_id'] = $insertid;
				$detail_arr = $statedetailModel->getWhereData($rd);
				foreach($detail_arr as $ha=>$hi){
					if($hi['state'] == 1){

						$message[$ha]['detail_id'] = $hi['id'];
						$ma[] = $hi['id'];
						$message[$ha]['status'] = 0;
						$message[$ha]['f_type'] = 1;
						$message[$ha]['create_time'] = $date_now;
						$message[$ha]['update_time'] = $date_now;
						$message[$ha]['count'] = 0;

					}else{
						continue;
					}

				}

				//添加savor_account_notice表
				sort($message);
				$statenoticeModel->addAll($message);
				//添加到redis
				$statenoticeModel->saveStRedis($ma);
				if($fail == 0){
					$this->outputNew($sustr,'Checkaccount/rplist',1,1);
				}else{
					$this->outputNew($sustr,'Checkaccount/rplist',1,0);
				}

			}else{
				$this->error('添加对账单明细失败','notclose');
			}

		}else{
			$this->error('添加对账单失败','notclose');
		}
	}

	/*
	 * 判断当前酒楼应该是何状态
	 */
	private function judgeHotel($info,$st,$en,$fee){


		$num = array();
		$money = array();
		//判断酒楼是否存在
		$hotelModel = new \Admin\Model\HotelModel();
		$statedetailModel = new \Admin\Model\AccountStatementDetailModel();
		$repeat_arr = array();
		//酒楼id非法的
		$ill_hotel = array();
		$rest = array();
		$emparray = array();
		$num = array();
		$sort = array();
		foreach($info as $rk=>$rv) {
			if(in_array($rv['id'], $num)){
				$info[$rk]['state'] = 6;
				continue;
			}else if(empty($rv['id']) || !preg_match("/^\d*$/",$rv['id'])){
				$info[$rk]['state'] = 2;
				continue;
			}else{
				//判断id是否存在hotel表
				$dat['id'] = $rv['id'];

				//$field = 'id,name,bill_per,bill_tel';
				$finfo = $hotelModel->find($dat['id']);
			//	var_dump( $finfo);
				if($finfo){
					$info[$rk]['bill_tel'] = $finfo['bill_tel'];
					$num[] = $rv['id'];
					$where = ' 1=1 and state=1 and cost_type='.$fee;
					//判断酒楼是否下发
					//ft<=en   开始值要小于给出结束值
					//fe>=st   结束值要大于给出开头值
					if($st){
						$where .= " and fee_end >='".$st."'";
					}
					if($en){
						//$start_date = date('YmdH',strtotime($start_date));
						$where .= " AND fee_start <='".$en."' AND hotel_id = ".$rv['id'];
					}
					$field = '`hotel_id`';
					$rest = $statedetailModel->getWhereData($where,$field);
					if($rest){
						$info[$rk]['state'] = 3;
						$info[$rk]['name'] = $finfo['name'];
						continue;
					}else{
						if(empty($info[$rk]['money']) ||  (!is_numeric($info[$rk]['money']))){
							$info[$rk]['state'] = 7;
							continue;
						}else{
							if($info[$rk]['money']<0){
								$info[$rk]['state'] = 5;
								continue;
							}
							if(empty($finfo['bill_tel'])){
								$info[$rk]['state'] = 4;
								continue;
							}
						}
					}
				}else{
					$info[$rk]['state'] = 2;
					continue;
				}
			}
		}
		return $info;
		die;
	}



	private function judgeHotelbak($info,$st,$en,$fee){
		$num = array();
		$money = array();
		//判断酒楼是否存在
		$hotelModel = new \Admin\Model\HotelModel();
		$statedetailModel = new \Admin\Model\AccountStatementDetailModel();
		$repeat_arr = array();
		//酒楼id非法的
		$ill_hotel = array();
		$rest = array();
		$emparray = array();
		$num = array();
		$sort = array();
		foreach($info as $rk=>$rv) {
			//$info[$rk]['sort_num'] = $rk;
			if(in_array($rv['id'], $num)){
				$repeat_arr[$rk] = $rv;
				continue;
			}else if(empty($rv['id'])){
				$emparray[$rk] = $rv;
				continue;
			} else if(!is_int($rv['id'])){
				$ill_hotel[$rk] = $rv;
				continue;
			}else{
				$num[] = $rv['id'];
				$money[$rv['id']] = $rv['money'];
				$sort[$rv['id']] = $rk;
				$rest[$rv['id']] = $rv;
				continue;
			}
		}

		$num_str = implode(',', $num);
		$dat['id']=array('in',$num_str);
		$dat['flag']= 0;
		$field = 'id,name,bill_per,bill_tel';
		$res = $hotelModel->getWhereData($dat, $field);
		$num_true = array();
		foreach($res as $rk=>$rv) {
			$res[$rk]['money'] = $money[$rv['id']];
			$num_true[] = $rv['id'];
			if($res[$rk]['money']<0){
				$res[$rk]['state'] = 5;
				continue;
			}
			if(!is_numeric($res[$rk]['money'])){
				$res[$rk]['state'] = 7;
				continue;
			}


		}
		$count = count($num_true);
		$ar_diff = array_diff($num, $num_true);
		//找到状态为2即不存在
		foreach($ar_diff as $av){
			$res[$count]['id'] = $av;
			$res[$count]['state'] = 2;
			$res[$count]['name'] = $rest[$av]['name'];
			$res[$count]['money'] = $rest[$av]['money'];
			$res[$count]['bill_per'] = '';
			$res[$count]['bill_tel'] = '';
			$count++;
		}

		if($repeat_arr){
			foreach($repeat_arr as $rk=>$rv){
				$res[$count]['id'] = $rv['id'];
				$res[$count]['state'] = 6;
				$res[$count]['name'] = $rv['name'];
				$res[$count]['money'] = $rv['money'];
				$res[$count]['bill_per'] = '';
				$res[$count]['bill_tel'] = '';
				$count++;
			}
		}

		if($ill_hotel){
			foreach($ill_hotel as $rk=>$rv){
				$res[$count]['id'] = $rv['id'];
				$res[$count]['state'] = 2;
				$res[$count]['name'] = $rv['name'];
				$res[$count]['money'] = $rv['money'];
				$res[$count]['bill_per'] = '';
				$res[$count]['bill_tel'] = '';
				$count++;
			}
		}
		if($emparray){
			foreach($emparray as $rk=>$rv){
				$res[$count]['id'] = $rv['id'];
				$res[$count]['state'] = 2;
				$res[$count]['name'] = $rv['name'];
				$res[$count]['money'] = $rv['money'];
				$res[$count]['bill_per'] = '';
				$res[$count]['bill_tel'] = '';
				$count++;
			}
		}



		//判断酒楼是否下发
		//ft<=en   开始值要小于给出结束值
		//fe>=st   结束值要大于给出开头值
		$where = ' 1=1 and state=1 and cost_type='.$fee;
		if($st){
			$where .= " and fee_end >='".$st."'";
		}
		if($en){
			//$start_date = date('YmdH',strtotime($start_date));
			$where .= " AND fee_start <='".$en."' ";
		}
		if($num_true){
			$num_true_str = implode(',', $num_true);
			$where .= "and hotel_id in ($num_true_str)";
		}
		$field = '`hotel_id`';
		$rest = $statedetailModel->getWhereData($where,$field);
		foreach($rest as $rv){
			$numpp = $rv['hotel_id'];
			$fee_time_num[$numpp] = $numpp;
		}
		foreach($res as $rk=>$rv) {

			if ( isset($fee_time_num[$rv['id']]) &&  !isset($res[$rk]['state'])){

				$res[$rk]['state'] = 3;

			}else{
				if(empty($res[$rk]['bill_tel']) && empty($res[$rk]['state'])){
					$res[$rk]['state'] = 4;
				}
			}
		}
		return $res;
	}




	private function sendToUcPa($info,$param,$type=1){
		$to = $info['tel'];
		$bool = true;
		$ucconfig = C('SMS_CONFIG');
		$options['accountsid'] = $ucconfig['accountsid'];
		$options['token'] = $ucconfig['token'];
		//确认付款通知
		if($type == 2){
			$templateId = $ucconfig['payment_templateid'];
		}else{
			$templateId = $ucconfig['bill_templateid'];
		}
		$ucpass= new Ucpaas($options);
		$appId = $ucconfig['appid'];
		$sjson = $ucpass->templateSMS($appId,$to,$templateId,$param);

		$sjson = json_decode($sjson,true);
		$code = $sjson['resp']['respCode'];

		if($code === '000000') {
		}else{
			$bool = false;
		}
		if($type == 1){
			$this->addTelLog($sjson, $param, $info,$type, $bool);
		}else{
			$this->addTelLog($sjson, $param, $info,$type, $bool);
		}

		return $bool;

	}


	private function addTelLog($sjson, $param, $info, $type, $bool){
		$now = date("Y-m-d H:i:s");
		$save = array();
		$accountMsgModel = new \Admin\Model\AccountMsgLogModel();
		$save['status'] = ($bool==true)?1:0;
		$save['type'] = $type;
		$save['detail_id'] = $info['id'];
		$save['hotel_id'] = $info['hotelid'];
		$save['create_time'] = $now;
		$save['update_time'] = $now;
		$save['url'] = $param;
		$save['smsId'] = $sjson['resp']['templateSMS']['smsId'];
		$save['tel'] = $info['tel'];
		$save['resp_code'] = $sjson['resp']['respCode'];
		$accountMsgModel->addData($save);
	}





	private function sendPayMessage($info){
		//$sjson  = '{"resp":{"respCode":"000000","templateSMS":{"createDate":"20170621131304","smsId":"3bcd56624d1d60a6e5830c3886f2f31d"}}}';
		$fe_start = $info['fee_start'];
		$fe_end = $info['fee_end'];
		$tel= $info['tel'];
		$to = $tel;
		$param="$fe_start,$fe_end";
		$bool = $this->sendToUcPa($info,$param, 2);
		return $bool;
	}

}
