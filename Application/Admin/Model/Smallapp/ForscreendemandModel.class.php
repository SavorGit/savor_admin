<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class ForscreendemandModel extends BaseModel{
    protected $tableName='smallapp_forscreen_demand';
    public function getList($field,$where,$group, $order='id desc', $start=0,$size=5)
    {
       
        $list = $this->field($field)
        ->where($where)
        ->order($order)
        ->limit($start,$size)
        ->group($group)
        ->select();
        $rt = $this->where($where)
                      ->group($group)
                      ->select();
        $count = count($rt);
        $objPage = new Page($count,$size);
        
        $show = $objPage->admin_page();
        
        
        $data = array('list'=>$list,'page'=>$show);
        
        return $data;
        
    }//End Function
}