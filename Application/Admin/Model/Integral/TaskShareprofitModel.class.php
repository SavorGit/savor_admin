<?php
namespace Admin\Model\Integral;
use Admin\Model\BaseModel;

class TaskShareprofitModel extends BaseModel{
	
	protected $tableName='integral_task_shareprofit';

    public function getTaskShareprofit($fields,$where,$orderby,$start=0,$size=0){
        if($start >= 0 && $size){
            $data = $this->field($fields)->where($where)->order($orderby)->limit($start,$size)->select();
        }else{
            $data = $this->field($fields)->where($where)->order($orderby)->select();
        }
        return $data;
    }

}