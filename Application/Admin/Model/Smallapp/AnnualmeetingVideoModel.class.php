<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class AnnualmeetingVideoModel extends BaseModel{
	protected $tableName='smallapp_annualmeeting_video';


    public function getList($fields,$where,$orderby,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->alias('a')
                ->join('savor_smallapp_annualmeeting m on a.annualmeeting_id=m.id','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->limit($start,$size)
                ->select();
            $count = $this->alias('a')
                ->join('savor_smallapp_annualmeeting m on a.annualmeeting_id=m.id','left')
                ->where($where)
                ->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->alias('a')
                ->join('savor_smallapp_annualmeeting m on a.annualmeeting_id=m.id','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->select();
        }
        return $data;
    }

}