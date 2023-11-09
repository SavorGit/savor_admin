<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class CrmtaskController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $type = I('type',0,'intval');
        $status = I('status',0,'intval');

        $m_crmtask = new \Admin\Model\Smallapp\CrmtaskModel();
        $where = array();
        if($type){
            $where['type'] = $type;
        }
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_crmtask->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_ops_staff = new \Admin\Model\OpsstaffModel();
            $m_sysuser = new \Admin\Model\UserModel();
            $all_types = C('CRM_TASK_TYPES');
            $all_status = array('1'=>'正常','2'=>'禁用');
            foreach ($res_list['list'] as $v){
                if($v['update_time']=='0000-00-00 00:00:00'){
                    $v['update_time'] = '';
                }
                if($v['sysuser_id']==0 && $v['ops_staff_id']){
                    $res_ops = $m_ops_staff->getInfo(array('id'=>$v['ops_staff_id']));
                    $v['sysuser_id'] = $res_ops['sysuser_id'];
                }
                $res_suser = $m_sysuser->getUserInfo($v['sysuser_id']);
                $v['sys_uname'] = $res_suser['remark'];
                $v['type_str'] = $all_types[$v['type']];
                $v['status_str'] = $all_status[$v['status']];
                $data_list[] = $v;
            }
        }
        $this->assign('type',$type);
        $this->assign('status',$status);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function addtask(){
        $id = I('id',0,'intval');
        $m_crmtask = new \Admin\Model\Smallapp\CrmtaskModel();
        $vinfo = $m_crmtask->getInfo(array('id'=>$id));
        $task_type = $vinfo['type'];
        if(IS_POST){
            $name = I('post.name','','trim');
            $sale_manager_num = I('post.sale_manager_num',0,'intval');
            $cate_num = I('post.cate_num',0,'intval');
            $stock_num = I('post.stock_num',0,'intval');
            $task_finish_rate = I('post.task_finish_rate',0);
            $task_finish_day = I('post.task_finish_day',0,'intval');
            $is_upimg = I('post.is_upimg',0,'intval');
            $is_check_location = I('post.is_check_location',0,'intval');
            $start_time = I('post.start_time');
            $end_time = I('post.end_time');
            $desc = I('post.desc','','trim');
            $notify_day = I('post.notify_day',0,'intval');
            $notify_handle_day = I('post.notify_handle_day',0,'intval');
            $status = I('post.status',2,'intval');

            $sysuserInfo = session('sysUserInfo');
            $sysuser_id = $sysuserInfo['id'];
            $update_time = date('Y-m-d H:i:s');

            $updata = array('name'=>$name,'sale_manager_num'=>$sale_manager_num,'cate_num'=>$cate_num,'stock_num'=>$stock_num,
                'task_finish_rate'=>$task_finish_rate,'task_finish_day'=>$task_finish_day,'is_upimg'=>$is_upimg,'is_check_location'=>$is_check_location,
                'start_time'=>$start_time,'end_time'=>$end_time,'desc'=>$desc,'notify_day'=>$notify_day,'notify_handle_day'=>$notify_handle_day,
                'status'=>$status,'update_time'=>$update_time,'sysuser_id'=>$sysuser_id
                );
            $m_crmtask->updateData(array('id'=>$id),$updata);

            $this->output('操作成功!', 'crmtask/datalist');
        }else{
            $all_jump_url = array('1'=>'addopensale','2'=>'adddeliverdrinks','3'=>'addarrears','4'=>'addoverduearrears','5'=>'addroom',
                '6'=>'addinvitation','7'=>'addcheck','8'=>'adddemand','9'=>'addboot','10'=>'addwechat','11'=>'addcustom'
            );
            $display_html = $all_jump_url[$task_type];
            if($task_type==11){
                $all_residenter_ids = explode(',',$vinfo['residenter_ids']);
                $m_opuser_role = new \Admin\Model\OpuserroleModel();
                $fields = 'a.user_id as main_id,user.remark';
                $where = array('a.state'=>1,'user.status'=>1,'a.role_id'=>array('in',array(1,3)),'user.id'=>array('gt',0));
                $residenter_list = $m_opuser_role->getAllRole($fields,$where,'' );
                foreach ($residenter_list as $k=>$v){
                    $is_select = '';
                    if(in_array($v['main_id'],$all_residenter_ids)){
                        $is_select = 'selected';
                    }
                    $residenter_list[$k]['is_select'] = $is_select;
                }
                $this->assign('residenter_list',$residenter_list);
            }
            $this->assign('vinfo',$vinfo);
            $this->display($display_html);
        }
    }

    public function copytask(){
        $task_id = I('get.task_id');

        $m_crmtask = new \Admin\Model\Smallapp\CrmtaskModel();
        $task_info = $m_crmtask->getInfo(array('id'=>$task_id));
        unset($task_info['id'],$task_info['update_time'],$task_info['add_time']);
        $userinfo = session('sysUserInfo');
        $uid = $userinfo['id'];
        $task_info['name'] = $task_info['name'].'-'.date('YmdHis');
        $task_info['sysuser_id']  = $uid;
        $task_info['status'] = 2;
        $ret = $m_crmtask->addData($task_info);
        if($ret){
            $this->output('复制成功', "crmtask/datalist",2);
        }else {
            $this->output('删除失败', "crmtask/datalist",2,0);
        }
    }

    public function addhotel(){
        $id = I('id',0,'intval');

        if(IS_POST){
            $hbarr = $_POST['hbarr'];
            if(empty($hbarr)){
                $this->output('请选择酒楼','crmtask/datalist',2,0);
            }
            $hotel_arr = json_decode($hbarr, true);
            if(empty($hotel_arr)){
                $this->output('请选择酒楼','crmtask/datalist',2,0);
            }
            $m_taskhotel = new \Admin\Model\Crm\TaskHotelModel();
            foreach ($hotel_arr as $v){
                $hotel_id = $v['hotel_id'];

                $data = array('hotel_id'=>$hotel_id,'task_id'=>$id);
                $res = $m_taskhotel->where($data)->find();
                if(empty($res)){
                    $m_taskhotel->add($data);
                }
            }
            $this->output('操作成功!', 'crmtask/datalist');
        }else{
            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();
            $this->assign('areainfo', $area_arr);
            $m_task = new \Admin\Model\Crm\TaskModel();
            $vinfo = $m_task->getInfo(array('id'=>$id));
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function hotellist() {
        $task_id = I('task_id',0,'intval');
        $keyword = I('keyword','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);

        $where = array('a.task_id'=>$task_id);
        if(!empty($keyword)){
            $where['h.name'] = array('like',"%$keyword%");
        }
        $start  = ($page-1) * $size;
        $fields = 'a.id,a.add_time,h.id as hotel_id,h.name as hotel_name';
        $m_taskhotel = new \Admin\Model\Crm\TaskHotelModel();
        $result = $m_taskhotel->getHotelList($fields,$where,'a.id desc', $start,$size);
        $datalist = $result['list'];

        $this->assign('task_id',$task_id);
        $this->assign('keyword',$keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('hotellist');
    }

    public function hoteldel(){
        $id = I('get.id',0,'intval');

        $user = session('sysUserInfo');
        $sysuser_id = $user['id'];
        $m_taskhotel = new \Admin\Model\Crm\TaskHotelModel();
        $result = $m_taskhotel->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'crmtask/hotellist',2);
        }else{
            $this->output('操作失败', 'crmtask/hotellist',2,0);
        }
    }

    public function recordlist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $status = I('status',0,'intval');
        $handle_status = I('handle_status',99,'intval');
        $start_date = I('start_time','');
        $end_date = I('end_time','');
        $keyword = I('keyword','','trim');

        if(empty($start_date)){
            $start_date = date('Y-m-d',strtotime('-7 days'));
        }
        if(empty($end_date)){
            $end_date = date('Y-m-d');
        }
        $start_time = "$start_date 00:00:00";
        $end_time = "$end_date 23:59:59";
        $where = array('a.add_time'=>array(array('egt',$start_time),array('elt',$end_time)));
        if(!empty($keyword)){
            $where['hotel.name'] = array('like',"%{$keyword}%");
        }
        if($status){
            $where['a.status'] = $status;
        }
        if($handle_status!=99){
            $where['a.handle_status'] = $handle_status;
        }
        $m_taskrecord = new \Admin\Model\Crm\TaskRecordModel();
        $start = ($page-1) * $size;
        $fields = 'a.*,hotel.name as hotel_name,task.name as task_name,task.type';
        $result = $m_taskrecord->getTaskRecordList($fields,$where,'a.id desc', $start,$size);

        $datalist = array();
        $all_status_map = C('CRM_TASK_STATUS');
        $all_form_type = C('CRM_TASK_FORM_TYPE');
        $all_handle_status = C('CRM_TASK_HANDLE_STATUS');
        $oss_host = get_oss_host();
        foreach ($result['list'] as $v){
            if($v['finish_time']=='0000-00-00 00:00:00'){
                $v['finish_time'] = '';
            }
            $status_str = '';
            if(isset($all_status_map[$v['status']])){
                $status_str = $all_status_map[$v['status']];
            }
            $off_state_str = '正常';
            if($v['off_state']==2){
                $off_state_str = '删除';
            }
            $v['off_state_str'] = $off_state_str;
            $v['status_str'] = $status_str;
            $v['handle_status_str'] = $all_handle_status[$v['handle_status']];
            $form_type_str = '';
            if(isset($all_form_type[$v['form_type']])){
                $form_type_str = $all_form_type[$v['form_type']];
            }
            $v['form_type_str'] = $form_type_str;
            $is_trigger_str = '否';
            if($v['is_trigger']==1){
                $is_trigger_str = '是';
            }
            $v['is_trigger_str'] = $is_trigger_str;
            $is_handle = 0;
            if(in_array($v['type'],array(10,11)) && in_array($v['status'],array(1,2))){
                $is_handle= 1;
            }
            $v['is_handle'] = $is_handle;
            $imgs = array();
            if(!empty($v['img'])){
                $tmp_imgs = explode(',',$v['img']);
                foreach ($tmp_imgs as $iv){
                    if(!empty($iv)){
                        $imgs[]=$oss_host.$iv;
                    }
                }
            }
            $v['imgs'] = $imgs;

            $datalist[]=$v;
        }

        $this->assign('start_time',$start_date);
        $this->assign('end_time',$end_date);
        $this->assign('handle_status', $handle_status);
        $this->assign('status', $status);
        $this->assign('keyword', $keyword);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function handletask(){
        $id = I('id',0,'intval');
        $m_taskrecord = new \Admin\Model\Crm\TaskRecordModel();
        $vinfo = $m_taskrecord->getTaskRecords('a.id,task.name',array('a.id'=>$id));
        $vinfo = $vinfo[0];
        if(IS_POST){
            $audit_handle_status = I('post.audit_handle_status',0,'intval');//1不通过 2通过
            $userinfo = session('sysUserInfo');
            $audit_uid = $userinfo['id'];
            if($audit_handle_status==2){
                $updata = array('status'=>3,'form_type'=>1,'finish_time'=>date('Y-m-d H:i:s'),
                    'audit_handle_status'=>$audit_handle_status,'audit_time'=>date('Y-m-d H:i:s'),'audit_uid'=>$audit_uid);
            }else{
                $updata = array('status'=>0,'form_type'=>0,'handle_status'=>0,
                    'audit_time'=>date('Y-m-d H:i:s'),'audit_uid'=>$audit_uid);
            }
            $m_taskrecord->updateData(array('id'=>$id),$updata);
            $this->output('操作成功!', 'crmtask/recordlist');
        }else{
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }


}