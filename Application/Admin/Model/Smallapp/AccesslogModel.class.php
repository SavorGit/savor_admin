<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class AccesslogModel extends BaseModel{
	protected $tableName='smallapp_access_log';

    public function getCustomeList($fields="*",$count_field,$where,$group,$order='',$start=0,$size=5){
        $list = $this->field($fields)->where($where)->order($order)->group($group)->limit($start,$size)->select();
        $res_count = $this->field($count_field)->where($where)->select();
        $count = $res_count[0]['tp_count'];
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        return $data;
    }
}