<?php
namespace Admin\Model;

use Common\Lib\Page;

class OpsstaffModel extends BaseModel{
    protected $tableName='ops_staff';

    public function getCustomList($fields,$where,$order,$start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_sysuser u on a.sysuser_id=u.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('a')
            ->join('savor_sysuser u on a.sysuser_id=u.id','left')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
}