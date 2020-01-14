<?php
namespace Admin\Model\Integral;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class StaffModel extends BaseModel{

    protected $tableName='integral_merchant_staff';

    public function getUserIntegralList($fields,$where,$order,$start,$size){
        $list = $this->alias('a')
            ->join('savor_smallapp_user u on a.openid=u.openid','left')
            ->join('savor_smallapp_user_integral i on a.openid=i.openid','left')
            ->field($fields)
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

    public function getMerchantStaffInfo($fields,$where){
        $data = $this->alias('a')
            ->join('savor_integral_merchant m on a.merchant_id=m.id','left')
            ->field($fields)
            ->where($where)
            ->select();
        $res_data = !empty($data)?$data[0]:array();
        return $res_data;
    }
}