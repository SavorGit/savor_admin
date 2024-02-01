<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class DistributionuserController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $status = I('status',0,'intval');
        $name = I('name','','trim');

        $where = array('level'=>1);
        if(!empty($name)){
            $where['name'] = array('like',"%$name%");
        }
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $m_duser = new \Admin\Model\Smallapp\DistributionUserModel();
        $res_list = $m_duser->getDataList('*',$where,'id desc',$start,$size);
        $data_list = $res_list['list'];
        $all_status = array('1'=>'正常','2'=>'禁用');
        $all_level = array('1'=>'一级','2'=>'二级');
        if(!empty($data_list)){
            foreach ($data_list as $k=>$v){
                $res_user_num = $m_duser->getDataList('count(id) as num',array('parent_id'=>$v['id']),'id desc');
                $user_num = intval($res_user_num[0]['num']);

                $data_list[$k]['user_num'] = $user_num;
                $data_list[$k]['status_str'] = $all_status[$v['status']];
                $data_list[$k]['level_str'] = $all_level[$v['level']];
            }
        }

        $this->assign('name',$name);
        $this->assign('all_status',$all_status);
        $this->assign('status',$status);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function adduser(){
        $id = I('id',0,'intval');
        $level = I('level',0,'intval');
        $m_duser = new \Admin\Model\Smallapp\DistributionUserModel();
        if(IS_POST){
            $openid = I('post.openid','','trim');
            $name = I('post.name','','trim');
            $mobile = I('post.mobile','','trim');
            $parent_id = I('post.parent_id',0,'intval');
            $sysuser_id = I('post.sysuser_id',0,'intval');
            $status = I('post.status',0,'intval');
            if(empty($sysuser_id)){
                $this->output('请关联系统用户', 'distributionuser/datalist',2,0);
            }
            $user = session('sysUserInfo');
            $op_sysuser_id = $user['id'];
            $add_data = array('name'=>$name,'openid'=>$openid,'mobile'=>$mobile,'parent_id'=>$parent_id,
                'level'=>$level,'op_sysuser_id'=>$op_sysuser_id,'sysuser_id'=>$sysuser_id,'status'=>$status);
            if($id){
                $add_data['update_time'] = date('Y-m-d H:i:s');
                $m_duser->updateData(array('id'=>$id),$add_data);
                if($level==1){
                    if($status==2){
                        $m_duser->updateData(array('parent_id'=>$id),array('status'=>2,'op_sysuser_id'=>$op_sysuser_id,'update_time'=>date('Y-m-d H:i:s')));
                    }else{
                        $m_duser->updateData(array('parent_id'=>$id),array('status'=>1,'op_sysuser_id'=>$op_sysuser_id,'update_time'=>date('Y-m-d H:i:s')));
                    }
                }
            }else{
                $m_duser->addData($add_data);
            }
            $this->output('操作成功!', 'distributionuser/datalist');
        }else{
            $vinfo = array('status'=>2,'level'=>$level);
            if($id){
                $vinfo = $m_duser->getInfo(array('id'=>$id));
            }
            $duser_list = array();
            if($level==2){
                $duser_list = $m_duser->getDataList('id,name',array('level'=>1,'status'=>1),'id asc');
            }
            $sysusers = array();
            $m_sysuser = new \Admin\Model\UserModel();
            $res_user = $m_sysuser->getUserData('*',array('id'=>array('neq',1)));
            foreach ($res_user as $v){
                $selected_str = '';
                if($v['id']==$vinfo['sysuser_id']){
                    $selected_str = 'selected';
                }
                $tinfo = array('id'=>$v['id'],'name'=>$v['remark'],'selected_str'=>$selected_str);
                $sysusers[]=$tinfo;
            }
            $this->assign('sysusers',$sysusers);
            $this->assign('duser_list',$duser_list);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function userlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $parent_id = I('parent_id',0,'intval');
        $status = I('status',0,'intval');
        $name = I('name','','trim');

        $where = array('parent_id'=>$parent_id);
        if(!empty($name)){
            $where['name'] = array('like',"%$name%");
        }
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $m_duser = new \Admin\Model\Smallapp\DistributionUserModel();
        $res_list = $m_duser->getDataList('*',$where,'id desc',$start,$size);
        $data_list = $res_list['list'];
        $all_status = array('1'=>'正常','2'=>'禁用');
        $all_level = array('1'=>'一级','2'=>'二级');
        if(!empty($data_list)){
            foreach ($data_list as $k=>$v){
                $data_list[$k]['status_str'] = $all_status[$v['status']];
                $data_list[$k]['level_str'] = $all_level[$v['level']];
            }
        }
        $this->assign('name',$name);
        $this->assign('status',$status);
        $this->assign('parent_id',$parent_id);
        $this->assign('datalist',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function edituserstatus(){
        $id = I('get.id',0,'intval');
        $status = I('get.status',0,'intval');

        $user = session('sysUserInfo');
        $sysuser_id = $user['id'];
        $m_duser = new \Admin\Model\Smallapp\DistributionUserModel();
        $data = array('sysuser_id'=>$sysuser_id,'status'=>$status,'update_time'=>date('Y-m-d H:i:s'));
        $m_duser->updateData(array('id'=>$id),$data);

        $this->output('状态更新成功', 'distributionuser/userlist',2);
    }

}