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
}