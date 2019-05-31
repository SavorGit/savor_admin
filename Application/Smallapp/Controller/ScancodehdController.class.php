<?php
namespace Smallapp\Controller; 

use Admin\Controller\BaseController ;
/**
 * @desc 小程序数据统计-酒楼评级
 *
 */
class ScancodehdController extends BaseController {
    public function __construct() {
        parent::__construct();
    }
    public function index(){
        
        $where = $qrcode_where = '1 ';
        //开始时间
        $start_date = I('start_date') ? I('start_date') : date('Y-m-d',strtotime('-7 days'));
        if($start_date){
            $where .=" and a.create_time>='".$start_date." 00:00:00'";
            $qrcode_where .= " and a.create_time>='".$start_date." 00:00:00'";
            $this->assign('start_date',$start_date);
        }
        //结束时间
        $end_date   = I('end_date') ? I('end_date') : date('Y-m-d');
        if($end_date){
            $where .=" and a.create_time<='".$end_date." 23:59:59'";
            $qrcode_where .=" and a.create_time<='".$end_date." 23:59:59'";
            $this->assign('end_date',$end_date);
        }
        //城市
        $area_v = I('area_v');
        if ($area_v) {
            $this->assign('area_k',$area_v);
            if(!empty($area_v) ){
                $where .= "	AND hotel.area_id = $area_v";
                $qrcode_where .=" and hotel.area_id = $area_v";
            }
        }
        //机顶盒类型
        $hbt_v = I('hbt_v');
        if ($hbt_v) {
            $this->assign('hbt_v',$hbt_v);
            $where .= "	AND hotel.hotel_box_type = $hbt_v";
            $qrcode_where .= "	AND hotel.hotel_box_type = $hbt_v";
        }
        //是否4G
        $is_4g = I('is_4g','-1','intval');
        
		if($is_4g>0){
		    $where .=" AND box.is_4g=".$is_4g;  
		    $qrcode_where .=" AND box.is_4g=".$is_4g;  
		}else if($is_4g==0){
		    $where .=" AND box.is_4g !=1";
		    $qrcode_where .=" AND box.is_4g !=1";
		}
		$this->assign('is_4g',$is_4g);
        //小程序类型
        $small_app_id = I('small_app_id');
        if($small_app_id){
            $where .=" and a.small_app_id=".$small_app_id;
            if($small_app_id==1){
                $qrcode_where .=" and a.type in(1,2,3,5)";
            }else if($small_app_id==3){
                $qrcode_where .=" and a.type=6";
            }
            $this->assign('small_app_id',$small_app_id);
        }else {
            $where .=" and a.small_app_id in(1,2)";
        }
		//合作维护人
        $maintainer_id = I('maintainer_id',0,'intval');
        if($maintainer_id){
            $where .=" and ext.maintainer_id=$maintainer_id";
            $qrcode_where .=" and ext.maintainer_id=$maintainer_id";
            //echo $where;exit;
            $this->assign('maintainer_id',$maintainer_id);
        }
        
        //地区
        $m_area = new \Admin\Model\AreaModel();
        $area_list = $m_area->getHotelAreaList();
        $this->assign('area_list',$area_list);
        
        
        //机顶盒类型
        $hotel_box_type_arr = array(array('id'=>'2','name'=>'二代网络'),
                                    array('id'=>'3','name'=>'二代5G'),
                                    array('id'=>'6','name'=>'三代网络')
            
        );
        $this->assign('hotel_box_type',$hotel_box_type_arr);
        //小程序类型
        $small_app_id_arr = array(array('id'=>1,'name'=>'普通版'),
                                  array('id'=>2,'name'=>'极简版')
        );
        $this->assign('small_app_id_arr',$small_app_id_arr);
        
        //获取所有合作维护人
		$m_opuser_role = new \Admin\Model\OpuserroleModel();
		$fields = 'a.user_id uid,user.remark ';
		$map = array();
		$map['state']   = 1;
		$map['role_id']   = 1;
		$user_info = $m_opuser_role->getAllRole($fields,$map,'' );
		
		$u_arr = array();
		$hezuo_arr = array();
		foreach($user_info as $uv) {
			$u_arr[$uv['uid']] = trim($uv['remark']);
		}
		foreach($u_arr as $key=>$v){
		    $firstCharter = getFirstCharter(cut_str($v, 1));
		    $tmp['uid'] = $key;
		    $tmp['remark'] = $v;
		    $hezuo_arr[$firstCharter][] = $tmp;
		}
		ksort($hezuo_arr);
		$this->assign('hezuo_arr',$hezuo_arr);
        $hd_where = $where;
		
		$where .=" and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0 and a.mobile_brand!='devtools' and suser.unionId!=''";
		$qrcode_where .=" and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0 and suser.unionId!=''";
		$hd_where .= " and hotel.state=1 and hotel.flag=0 and box.state=1 and box.flag=0 and a.mobile_brand!='devtools' ";
		//扫码人数
		$qrcode_log = new \Admin\Model\Smallapp\QrcodeLogModel();
		$qrcode_person_nums = $qrcode_log->getQrcount($qrcode_where,'suser.unionid');
        //echo $qrcode_log->getLastSql();
		//互动人数
		$forscreen_record = new \Admin\Model\Smallapp\ForscreenRecordModel();
		$hd_person_nums = $forscreen_record->getHdCountPerson($where,'suser.unionid');
		
		
		//互动量
		$hd_nums = $forscreen_record->getHdCountPerson($hd_where);

		//酒楼数
		
        
		$this->assign('qrcode_person_nums',$qrcode_person_nums);
		$this->assign('hd_person_nums',$hd_person_nums);
        $this->assign('hd_nums',$hd_nums);
		$this->display('index');
    }
}