<?php
namespace Admin\Controller;
/**
 * @desc 运维端员工管理
 *
 */
class OpsstaffController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function stafflist() {
    	$keyword = I('keyword','','trim');
        $area_id = I('area_id',0,'intval');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $where = array();
        if(!empty($keyword)){
            $where['u.remark'] = array('like',"%$keyword%");
        }
        if($area_id){
            $where['a.area_id'] = $area_id;
        }
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        $all_area = array();
        foreach ($area_arr as $v){
            $all_area[$v['id']] = $v;
        }
        $start  = ($page-1) * $size;
        $m_opsstaff  = new \Admin\Model\OpsstaffModel();
        $fields = 'a.*,u.remark as uname';
        $result = $m_opsstaff->getCustomList($fields,$where,'a.id desc',$start,$size);
        $datalist = $result['list'];
        foreach ($datalist as $k=>$v){
            $datalist[$k]['area_name'] = $all_area[$v['area_id']]['region_name'];
        }

        $this->assign('area', $area_arr);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('stafflist');
    }
    
    public function staffadd(){
        $id = I('id', 0, 'intval');
        $m_opsstaff  = new \Admin\Model\OpsstaffModel();
        if(IS_GET){
            $m_area  = new \Admin\Model\AreaModel();
            $area_arr = $m_area->getAllArea();
        	$vinfo = array('area_id'=>1,'status'=>1,'hscope'=>3,'is_show_area'=>0);
            $sysuser_id = 0;
        	if($id){
                $vinfo = $m_opsstaff->getInfo(array('id'=>$id));
                $sysuser_id = $vinfo['sysuser_id'];
                $permission = json_decode($vinfo['permission'],true);
                $vinfo['hscope'] = $permission['hotel_info']['type'];
                $hotel_area_ids = $permission['hotel_info']['area_ids'];
                foreach ($area_arr as $k=>$v){
                    $select_str = '';
                    if(in_array($v['id'],$hotel_area_ids)){
                        $select_str = 'selected';
                    }
                    $area_arr[$k]['is_select'] = $select_str;
                }
                if(in_array($vinfo['hscope'],array(2,4))){
                    $vinfo['is_show_area'] = 1;
                }
        	}
            $sysusers = array();
            $m_sysuser = new \Admin\Model\UserModel();
            $uwhere = 'and id!=1';
            $res_user = $m_sysuser->getUser($uwhere);
            foreach ($res_user as $v){
                $selected_str = '';
                if($v['id']==$sysuser_id){
                    $selected_str = 'selected';
                }
                $tinfo = array('id'=>$v['id'],'name'=>$v['remark'],'selected_str'=>$selected_str);
                $sysusers[]=$tinfo;
            }
            $hotel_scopes = array('1'=>'全国','2'=>'城市','3'=>'个人','4'=>'城市+个人');
            $this->assign('hotel_scopes', $hotel_scopes);
            $this->assign('areas', $area_arr);
            $this->assign('sysusers',$sysusers);
        	$this->assign('vinfo',$vinfo);
        	$this->display('staffadd');
        }else{
        	$sysuser_id = I('post.sysuser_id',0,'intval');
        	$area_id = I('post.area_id',0,'intval');
            $job = I('post.job','','trim');
            $mobile = I('post.mobile','','trim');
            $hscope = I('post.hscope',0,'intval');
            $hotel_area_id = I('post.hotel_area_id');
        	$status = I('post.status',1,'intval');
        	if(empty($area_id) || ($hscope==2 && empty($hotel_area_id))){
        		$this->output('缺少必要参数!', 'opsstaff/staffadd', 2, 0);
        	}
        	if($hscope==4){
        	    if(!in_array($area_id,$hotel_area_id)){
                    $this->output('请勾选上自己所在的城市', 'opsstaff/staffadd', 2, 0);
                }
            }
        	$data = array('sysuser_id'=>$sysuser_id,'area_id'=>$area_id,'job'=>$job,'mobile'=>$mobile,'status'=>$status,
                'hotel_role_type'=>$hscope);
            $permission = array('hotel_info'=>array('type'=>$hscope,'area_ids'=>$hotel_area_id));
            $data['permission'] = json_encode($permission);
        	if($id){
                $data['update_time'] = date('Y-m-d H:i:s');
                $result = $m_opsstaff->updateData(array('id'=>$id),$data);
        	}else{
        		$result = $m_opsstaff->addData($data);
        	}
        	if($result){
        		$this->output('操作成功', 'opsstaff/stafflist');
        	}else{
        		$this->output('操作失败', 'opsstaff/staffadd',2,0);
        	}
        }
    }

    public function staffdel(){
        $staff_id = I('get.id', 0, 'intval');
        $m_opsstaff  = new \Admin\Model\OpsstaffModel();
        $condition = array('id'=>$staff_id);
        $result = $m_opsstaff->delData($condition);
        if($result){
            $this->output('删除成功', '',2);
        }else{
            $this->output('删除失败', '',2);
        }
    }
}