<?php
/**
 *@author zhang.yingtao
 *@desc 酒楼运维任务
 *@since 2017-10-19
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
use Common\Lib\Page;
class TouruserController extends BaseController {
	public function __construct() {
		parent::__construct();
	}

	public function getRepairBox($userid, $beg_time, $end_time, $damage_type, $start, $size){
		$redMo = new \Admin\Model\RepairBoxUserModel();
		$field = " sys.remark username,sru.id rpid,
                sru.remark,
                sru.state,sru.create_time ctime,sru.type boxtype,sbo.name
                mac_name,sru.datetime,sru.hotel_id,sru.mac,sht.name
                hotel_name,GROUP_CONCAT(sdetail.repair_type) rtype ";
		if ( $userid ) {
			$condition = ' 1=1 and sru.userid ='.$userid;

		} else {
			$condition = ' 1=1 ';

		}

		if($beg_time)   {
			$condition.=" AND sru.create_time>='$beg_time'";
			$this->assign('start_date',$beg_time);
		}

		if($end_time) {
			$condition.=" AND sru.create_time<='$end_time 23:59:59'";
			$this->assign('end_date', $end_time);
		}
		if($damage_type) {
			$condition.=" AND sdetail.repair_type = ".$damage_type;
		}
		$order = " sru.id DESC ";
		$box_info = $redMo->getRepairDetail($field, $condition, $order, $start, $size);
		return $box_info;
	}


	public function changeRepairDetail($box_infod){
		$box_info = $box_infod;
		$rdeitalModel = new \Admin\Model\RepairDetailModel();

		foreach ($box_info as $bk=>$bv) {
			if ($bv['boxtype'] == 2) {
				$box_info[$bk]['boxtypename']= '机顶盒';

			} else {
				$box_info[$bk]['boxtypename'] = '小平台';

			}
			if ($bv['state'] == 2) {
				$box_info[$bk]['state']= '未解决';

			} elseif($bv['state'] == 1) {
				$box_info[$bk]['state'] = '已解决';

			}
			//获取出错条件
			$rinfo = $rdeitalModel->fetchDataWhere(array('repair_id'=>$bv['rpid']),'','repair_type',2);
			$dam_str = '';
			if ( empty($rinfo) ) {
				$box_info[$bk]['repair_error'] = '';
			}
			else {
				$repair_arr =  C('HOTEL_DAMAGE_CONFIG');
				foreach ($rinfo as $rv) {
					$dam_str .= $repair_arr[$rv['repair_type']].',';
				}
				$dam_str = substr($dam_str,0 , -1);
				$box_info[$bk]['repair_error'] = $dam_str;
			}
		}
		foreach ($box_info as $key => $row)
		{
			$volumbe[$key]  = $row['ctime'];
		}
		array_multisort($volumbe, SORT_DESC, $box_info);
		return $box_info;
	}


	/**
	 * @desc 巡检列表
	 */
	public function index(){

	    $size   = I('numPerPage',50);//显示每页记录数
		$this->assign('numPerPage',$size);
		$start = I('pageNum',1);
		$this->assign('pageNum',$start);
		$beg_time = I('start_date','');
		$end_time = I('end_date','');
		$userid = I('userinfo','0');
		$start  = ( $start-1 ) * $size;
		$damage_type = I('damage_type','0');
		if(!empty($end_time) && !empty($beg_time)) {
			if($beg_time > $end_time) {
				$msg = '开始时间必须小于或者等于结束时间';
				$this->error($msg);
			}
		}
		//获取维修机顶盒人员
		$box_info = $this->getRepairBox($userid, $beg_time, $end_time, $damage_type, $start, $size);
		$box_detail = $box_info['list'];
		$box_detail = $this->changeRepairDetail($box_detail);
		$sysusergroup  = new \Admin\Model\SysusergroupModel();
		$map['sgr.name'] = array('like','酒楼运维%');
		$map['su.status'] = '1';
		$field = 'su.id uid,su.remark';
		$userarr =  $sysusergroup->getOpeprv($map, $field);
		//获取损坏类型
		$damage_config = C('HOTEL_DAMAGE_CONFIG');
		$this->assign('damagetype', $damage_type );
	    $this->assign('list',$box_detail);
	    $this->assign('page',$box_info['page']);
	    $this->assign('damage_arr',$damage_config);
	    $this->assign('user_arr', $userarr);
		$this->assign('useride',$userid);
	    $this->display('index');
	}

}