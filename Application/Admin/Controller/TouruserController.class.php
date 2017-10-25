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

	public function changeRepairInfo($box_infod, $damagetype){
		$box_info = $box_infod['list'];
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
			if($damagetype == 0) {
				if ( empty($rinfo) ) {
					$box_info[$bk]['repair_error'] = '';
				} else {
					$repair_arr =  C('HOTEL_DAMAGE_CONFIG');
					foreach ($rinfo as $rv) {
						$dam_str .= $repair_arr[$rv['repair_type']].',';
					}
					$dam_str = substr($dam_str,0 , -1);
					$box_info[$bk]['repair_error'] = $dam_str;
				}
			} else {
				if ( empty($rinfo) ) {
					unset($box_info[$bk]);
				} else {
					$tmp_damage = array();
					$repair_arr =  C('HOTEL_DAMAGE_CONFIG');
					foreach ($rinfo as $rv) {
						$dam_str .= $repair_arr[$rv['repair_type']].',';					$tmp_damage[] = $rv['repair_type'];

					}
					if(in_array($damagetype, $tmp_damage)) {
						$dam_str = substr($dam_str,0 , -1);
						$box_info[$bk]['repair_error'] = $dam_str;
					} else {
						unset($box_info[$bk]);
					}
				}
			}

		}
		foreach ($box_info as $key => $row)
		{
			$volumbe[$key]  = $row['ctime'];
		}
		array_multisort($volumbe, SORT_DESC, $box_info);
		return $box_info;
	}


	public function getRepairBoxInfo($userid, $beg_time, $end_time){
		$redMo = new \Admin\Model\RepairBoxUserModel();
		$field = " sys.remark username,sru.id rpid,
                sru.remark,
                sru.state,sru.create_time ctime,sru.type boxtype,sbo.name
                mac_name,sru.datetime,sru.hotel_id,sru.mac,sht.name
                hotel_name ";
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
		$order = " CONCAT(sru.DATETIME,sru.create_time) DESC ";
		$box_info = $redMo->getRepairInfo($field, $condition, $order);
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
		$damage_type = I('damage_type','0');
		if(!empty($end_time) && !empty($beg_time)) {
			if($beg_time > $end_time) {
				$msg = '开始时间必须小于或者等于结束时间';
				$this->error($msg);
			}
		}
        //获取维修机顶盒人员
		$box_info = $this->getRepairBoxInfo($userid, $beg_time, $end_time);
		$box_info = $this->changeRepairInfo($box_info, $damage_type);
		//var_dump($box_info);
		$start  = ( $start-1 ) * $size;
		//数组分页
		$len = count($box_info);
		$objPage = new \Common\Lib\Page($len,$size);
		$show = $objPage->admin_page();
		$result['page'] = $show;
		$result['list'] = array_slice($box_info, $start, $size);
		//获取运维组人员
		$sysusergroup  = new \Admin\Model\SysusergroupModel();
		$map['sgr.name'] = '酒楼运维';
		$map['su.status'] = '1';
		$field = 'su.id uid,su.remark';
		$userarr =  $sysusergroup->getOpeprv($map, $field);
		//获取损坏类型
		$damage_config = C('HOTEL_DAMAGE_CONFIG');
		$this->assign('damagetype', $damage_type );
	    $this->assign('list',$result['list']);
	    $this->assign('page',$result['page']);
	    $this->assign('damage_arr',$damage_config);
	    $this->assign('user_arr', $userarr);
		$this->assign('useride',$userid);
	    $this->display('index');
	}

}