<?php
namespace Dataexport\Controller;

class DepartmentController extends BaseController{
    
    public function  userlist(){
        $m_user = new \Admin\Model\UserModel();
        $field = 'a.id,a.remark name,a.job_id,d.name department_name';
        $where = [];
        $where['a.job_id'] = array('in',array('1','2'));
        $where['a.status'] = 1;
        $datalist = $m_user->alias('a')
                           ->join('savor_sysuser_department d on a.deparment=d.id','left')
                           ->field($field)
                           ->where($where)
                           ->select();
        $job_department_list = C('JOB_DEPARTMENT_LIST');
        foreach($datalist as $key=>$v){
            $datalist[$key]['job'] = $job_department_list[$v['job_id']]['name'];
            $datalist[$key]['sale_task_nums'] = '';
            $datalist[$key]['person_sale_task_nums'] = '';
            $datalist[$key]['cost'] = '';
        }
        $cell = array(
           array('id','人员ID'),
           array('name','姓名'),
           array('job','职位'),
           array('department_name','小组'),
           array('sale_task_nums','小组销量任务数'),
           array('person_sale_task_nums','个人销量任务数'),
           array('cost','成本'),
        );
        $filename = '绩效基础表';
        $this->exportToExcel($cell,$datalist,$filename,1);
    }
    
    
}