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
            $m_sysuser = new \Admin\Model\UserModel();
            $all_types = C('CRM_TASK_TYPES');
            $all_status = array('1'=>'正常','2'=>'禁用');
            foreach ($res_list['list'] as $v){
                if($v['update_time']=='0000-00-00 00:00:00'){
                    $v['update_time'] = '';
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
            $task_finish_day = I('post.task_finish_rate',0,'intval');
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



}