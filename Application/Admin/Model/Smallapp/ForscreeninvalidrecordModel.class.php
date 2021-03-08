<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class ForscreeninvalidrecordModel extends BaseModel{
	protected $tableName='smallapp_forscreen_invalidrecord';

    public function getList($fields="a.id",$where, $order='a.id desc', $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('a')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->where($where)->count();

        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

}