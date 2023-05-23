<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class InvitationModel extends BaseModel{
	protected $tableName='smallapp_invitation';

    public function getInvitationList($fields,$where,$order, $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
            ->join('savor_sysuser sysuser on ext.maintainer_id=sysuser.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $count = $this->alias('a')
            ->join('savor_hotel_ext ext on a.hotel_id=ext.hotel_id','left')
            ->field('a.id')
            ->where($where)
            ->select();
        $count = count($count);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
}