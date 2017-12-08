<?php
namespace Admin\Model;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class OpuserroleModel extends BaseModel
{
    protected $tableName='opuser_role';
    public function addInfo($data){
        $ret = $this->add($data);
        return $ret;
    }
    public function getList($fields,$where,$order,$limit){
        $data = $this->field($fields)->where($where)->order($order)->limit($limit)->select();
        return $data;
    }
    public function getPageList($fields,$where, $order='id desc', $start=0,$size=5){
        $list = $this->alias('a')
        ->join('savor_sysuser b on a.user_id=b.id','left')
        ->field($fields)
        ->where($where)
        ->order($order)
        ->limit($start,$size)
        ->select();
        $count = $this->where($where)
        ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
    public function getInfo($fields,$where){
        $data = $this->alias('a')
                     ->join('savor_sysuser user on a.user_id=user.id')
                     ->field($fields)
                     ->where($where)
                     ->find();
        return $data;
    }
    public function saveInfo($where,$data){
        $ret = $this->where($where)->save($data);
        return $ret;
    }
}