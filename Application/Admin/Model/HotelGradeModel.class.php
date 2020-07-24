<?php
namespace Admin\Model;
use Common\Lib\Page;

class HotelGradeModel extends BaseModel{
	protected $tableName='hotel_grade';


    public function getDatas($fields,$where,$order='',$group='',$start=0,$size=0){
        $list = $this->field($fields)->where($where)->limit($start,$size)->order($order)->group($group)->select();
        $res_nums = $this->field('count(distinct hotel_id) as num')->where($where)->select();
        $count = $res_nums[0]['num'];
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        return $data;
    }


}