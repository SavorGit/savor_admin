<?php
namespace Admin\Model;
use Common\Lib\Page;

class HotelGradeModel extends BaseModel{
	protected $tableName='hotel_grade';


    public function getDatas($fields,$where,$order='',$group='',$start=0,$size=0){
        $list = $this->field($fields)->where($where)->limit($start,$size)->order($order)->group($group)->select();
        $count = $this->where($where)->group($group)->select();
        $count = count($count);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        return $data;
    }


}