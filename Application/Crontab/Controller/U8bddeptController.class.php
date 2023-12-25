<?php
namespace Crontab\Controller;
use Think\Controller;

class U8bddeptController extends Controller{
    //同步部门档案
    public function syncDepartment(){
        $u8 = new \Common\Lib\U8cloud();
        
        $m_department = new \Admin\Model\FinanceDepartmentModel();
        
        $department_list = $this->getDepartmentTree(2,0);
        //print_r($department_list);exit;
        foreach($department_list as $key=>$v){
            $params = [];
            $data   = [];
            if($v['u8_pk_id']==''){//新增
                
                
                $data['createDate'] = substr($v['add_time'], 0,10);
                $data['deptattr']   = '6';   //部门属性0:销售部门 1:工厂 2；辅助生产车间 3:采购部门 4:采购、销售部门 5:基本生产车间 6:其他部门
                $data['deptcode']   = $v['id'];
                $data['deptname']   = $v['name'];
                $data['deptshortname'] = $v['name'];
                $data['depttype']   = 0;  //部门类型 0普通部门1虚拟部门
                $data['isuseretail']= 'N'; //是否用于零售
                $data['pk_corp']    = '02'; //北京热点投屏科技发展有限公司
                
                if(empty($v['parent_id'])){//一级层级
                    $data['pk_fathedept'] = '';
                }else {//非一级层级
                    
                    $ret_info = $m_department->getInfo(array('id'=>$v['parent_id']));
                    
                    if(empty($ret_info['u8_pk_id'])){
                        continue;
                    }
                    $data['pk_fathedept'] = $v['parent_id'];
                    
                    
                }
                $params['deptdoc'][] = $data;
                $ret = $u8->addDepartmentInfo($params);
                $result = $ret['result'];
                $result = json_decode($result,true);
                if($result['status']=='success'){
                    $map = [];
                    $info= [];
                    $map['id'] = $v['id'];
                    $ret_data = json_decode($result['data'],true);
                    
                    $info['u8_pk_id'] = $ret_data[0]['pk_deptdoc'];
                    $rts = $m_department->updateData($map, $info);
                    
                }
            }else {//编辑
                $data['pk_deptdoc'] = $v['u8_pk_id'];
                //$data['deptcode']   = $v['id'];
                $data['deptname']   = $v['name'];
                //$data['pk_fathedept'] = $v['parent_id'];
                $data['pk_corp']    = '02'; //北京热点投屏科技发展有限公司
                
                $params['deptdoc'][] = $data;
                
               
                $ret = $u8->editDepartmentInfo($params);
                
                
            }
        }
        echo date('Y-m-d H:i:s').' OK';
    }
    private function getDepartmentTree($add_type=1,$is_tree =1){//$add_type 1添加部门   2添加人员
        $department_list_tree = [];
        $m_department = new \Admin\Model\FinanceDepartmentModel();
        $where = [];
        $where['status'] = 1;
        $where['parent_id'] = 0;
        $department_list = $m_department->where($where)->select();
        foreach($department_list as $key=>$v){
            $department_list_tree[] = $v;
            $map = [];
            $map['status'] = 1;
            $map['parent_id'] = $v['id'];
            $f_department_list = $m_department->where($map)->select();
            if(!empty($f_department_list)){
                foreach($f_department_list as $kk=>$vv){
                    
                    if($is_tree==1){
                        $vv['name'] = '&nbsp;&nbsp;&nbsp;&nbsp;|-'.$vv['name'];
                    }else {
                        $vv['name'] = $vv['name'];
                    }
                    
                    $department_list_tree[] = $vv;
                    
                    if($add_type ==2){
                        $tps = [];
                        $tps['status'] = 1;
                        $tps['parent_id'] = $vv['id'];
                        $s_department_list = $m_department->where($tps)->select();
                        
                        if(!empty($s_department_list)){
                            foreach($s_department_list as $kkk=>$vvv){
                                
                                if($is_tree==1){
                                    $vvv['name'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-".$vvv['name'];
                                }else {
                                    $vvv['name'] = $vvv['name'];
                                }
                                
                                
                                $department_list_tree[] = $vvv;
                                
                            }
                        }
                    }
                    
                }
                
            }
        }
        return $department_list_tree;
    }
}