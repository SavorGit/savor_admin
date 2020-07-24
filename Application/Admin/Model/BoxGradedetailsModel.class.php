<?php
namespace Admin\Model;

class BoxGradedetailsModel extends BaseModel{
	protected $tableName='box_grade_details';

    public function getDatas($field='*',$filter='',$order='',$group=''){
        $res = $this->field($field)
            ->where($filter)
            ->order($order)
            ->group($group)
            ->select();
        return $res;
    }
}