<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class ForscreenhelpModel extends BaseModel{
	protected $tableName='smallapp_forscreen_help';

    public function getList($fields,$where, $order, $start=0,$size=5){
        $list = $this->alias('a')
            ->join('savor_smallapp_forscreen_record f on a.forscreen_record_id=f.id','left')
            ->join('savor_smallapp_public p on p.forscreen_id= f.forscreen_id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($start,$size)
            ->select();

        $count = $this->alias('a')
            ->join('savor_smallapp_forscreen_record f on a.forscreen_record_id=f.id','left')
            ->join('savor_smallapp_public p on p.forscreen_id= f.forscreen_id','left')
            ->field($fields)
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

    public function getHelpdetail($fields,$where){
        $res = $this->alias('a')
            ->join('savor_smallapp_forscreen_record f on a.forscreen_record_id=f.id','left')
            ->join('savor_smallapp_public p on p.forscreen_id= f.forscreen_id','left')
            ->field($fields)
            ->where($where)
            ->select();
        if(!empty($res)){
            $res = $res[0];
        }
        return $res;
    }
}