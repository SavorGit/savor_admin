<?php
namespace Admin\Model\Crm;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class TaskRecordModel extends BaseModel{
	protected $tableName='crm_task_record';

    public function getTaskRecordList($fields,$where,$order, $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_crm_task task on a.task_id=task.id','left')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $count = $this->alias('a')
            ->join('savor_crm_task task on a.task_id=task.id','left')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
            ->field('a.id')
            ->where($where)
            ->select();
        $count = count($count);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

    public function getTaskRecords($fileds,$where,$orderby='',$limit='',$group=''){
        $res = $this->alias('a')
            ->field($fileds)
            ->join('savor_crm_task task on a.task_id=task.id','left')
            ->join('savor_hotel hotel on a.hotel_id=hotel.id','left')
            ->join('savor_hotel_ext ext on hotel.id=ext.hotel_id','left')
            ->where($where)
            ->order($orderby)
            ->limit($limit)
            ->group($group)
            ->select();
        return $res;
    }

    public function getHandleTasks($type){
        $where = array('a.status'=>1,'a.off_state'=>1,'task.type'=>$type,'task.status'=>1);
        $fileds = 'a.id,a.task_id,a.hotel_id,a.residenter_id,a.status,a.form_type,a.handle_status,a.audit_handle_status,
        a.is_trigger,a.integral_task_id,a.reset_time,a.add_time,
        task.sale_manager_num,task.cate_num,task.stock_num,task.task_finish_rate,task.task_finish_day,task.is_upimg,
        task.is_check_location,task.notify_day,task.notify_handle_day';
        $res = $this->alias('a')
            ->field($fileds)
            ->join('savor_crm_task task on a.task_id=task.id','left')
            ->where($where)
            ->order('a.id asc')
            ->select();
        return $res;
    }
}