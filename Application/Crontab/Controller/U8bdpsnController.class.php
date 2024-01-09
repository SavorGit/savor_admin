<?php
namespace Crontab\Controller;
use Think\Controller;

class U8bdpsnController extends Controller{
    //同步部门成员
    public function syncDepartmentUsers(){
        $u8 = new \Common\Lib\U8cloud();
        $m_department_user = new \Admin\Model\FinanceDepartmentUserModel();
        
        $where = [];
        $where['status'] =1;
        $user_list = $m_department_user->getAllData('id,department_id,name,u8_pk_id',$where);
        
        foreach($user_list as $key=>$v){
            $params = [];
            $data   = [];
            $data['currentcorp']                           = '02';                //热点投屏公司
            $data['psnbasvo']['psnname']                   = $v['name'];          //员工名称
            $data['psnmanvo']['pk_deptdoc']                = $v['department_id']; //部门id
            $data['psnmanvo']['pk_psncl']                  = '01';                //人员类别 01正式员工
            $data['psnmanvo']['psncode']                   = $v['id'];            //员工id
            
            if(empty($v['u8_pk_id'])){//新增
                
            }else {//编辑
                
                $data['psnbasvo']['pk_psnbasdoc'] = $v['u8_pk_id'];
                $data['psnbasvo']['usedname']     = '';
            }
            $params['psn'][]['parentvo'] = $data;
            
            $ret = $u8->saveDepartmentUser($params);
            $result = json_decode($ret['result'],true);
            $status = $result['status'];
            if($status=='success'){//同步成功
                
                $ret_data = json_decode($result['data'],true);
                $map  = [];
                $info = [];
                $map['id'] = $v['id'];
                $info['u8_pk_id'] = $ret_data[0]['parentvo']['psnbasvo']['pk_psnbasdoc'];
                $m_department_user->updateData($map, $info);
            }
        }
        echo date('Y-m-d H:i:s').' OK';
    }
    //删除部门成员(如果有需要删除)
    public function delDepartmentUsers(){
        
        $u8 = new \Common\Lib\U8cloud();
        $m_department_user = new \Admin\Model\FinanceDepartmentUserModel();
        
        $where = [];
        $where['status'] =2;
        $where['u8_pk_id'] = array('neq','');
        $user_list = $m_department_user->getAllData('id,department_id,name,u8_pk_id',$where);
        
        foreach($user_list as $key=>$v){
            
            $params = [];
            $params['corpcode'] = '02';      //热点投屏公司
            $params['psncode']  = $v['id'];  //员工id
            
            $ret = $u8->delDepartmentUser($params);
        }
        echo date('Y-m-d H:i:s').' OK';
    }
    
    public function sysuserToDepartment(){
        $m_department_user = new \Admin\Model\FinanceDepartmentUserModel();
        $m_sys_user = new \Admin\Model\UserModel();
        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $where = [];
        $where['status'] =1;
        $user_list = $m_department_user->getAllData('id,sys_user_id,department_id,name,u8_pk_id',$where);
        $flag = 0;
        foreach ($user_list as $key=>$v){
            $map = [];
            $map['remark'] = $v['name'];
            $map['status'] = 1;
            
            //$sys_user_info = $m_sys_user->field('id,remark')->where($map)->find();
            
            
            $where = [];
            $where['a.state']  = 1;
            $where['b.status'] = 1;
            $where['b.remark'] = $v['name'];
            $sys_user_info =  $m_opuser_role->alias('a')
                                ->join('savor_sysuser b on a.user_id=b.id','left')
                                ->field('a.manage_city,a.user_id,b.remark as username')
                                ->order('a.manage_city asc')
                                ->where($where)
                                ->find();
            
            
            if(!empty($sys_user_info)){
                $m_department_user->updateData(array('id'=>$v['id']), array('sys_user_id'=>$sys_user_info['user_id']));
                $flag ++;
            }
        }
        echo date('Y-m-d H:i:s').' OK';
    }
    public function getrepeatUsers(){
        $m_department_user = new \Admin\Model\FinanceDepartmentUserModel();
        
        $where['department_id']= 3;
        $user_list = $m_department_user->getAllData('*',$where);
        $repeat_users = [];
        foreach ($user_list as $key=>$v){
            $map = [];
            $map['department_id'] = array('in','9,10,16,17,18,19,20,21,');
            $map['name']  = $v['name'];
            
            $ret = $m_department_user->getAllData('*',$map);
            if(!empty($ret)){
                $repeat_users[] = $ret[0];
            }
            
        }
        print_r($repeat_users);exit;
    }
}