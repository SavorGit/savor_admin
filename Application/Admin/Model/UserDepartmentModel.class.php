<?php
namespace Admin\Model;
use Common\Lib\Page;
class UserDepartmentModel extends BaseModel{
    protected $tableName='sysuser_department';
    public function getList($field,$where, $order='id desc', $start=0,$size=5){
        $list = $this->alias('a')
                     ->join('savor_sysuser u on a.leader_user_id=u.id','left')
                     ->field($field)
                     ->where($where)
                     ->order($order)
                     ->limit($start,$size)
                     ->select();
        $count = $this->alias('a')
                      ->where($where)
                      ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
}