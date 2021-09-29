<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class ActivityapplyModel extends BaseModel{
	protected $tableName='smallapp_activityapply';

    public function getList($fields,$where,$orderby,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->alias('a')
                ->join('savor_smallapp_user user on a.openid=user.openid','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->limit($start,$size)
                ->select();
            $count = $this->alias('a')
                ->join('savor_smallapp_user user on a.openid=user.openid','left')
                ->where($where)
                ->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->alias('a')
                ->join('savor_smallapp_user user on a.openid=user.openid','left')
                ->field($fields)
                ->where($where)
                ->order($orderby)
                ->select();
        }
        return $data;
    }

    public function getApplyDatas($fields,$where,$order,$limit,$group){
        $data = $this->alias('a')
            ->join('savor_smallapp_activity activity on a.activity_id=activity.id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->group($group)
            ->select();
        return $data;
    }

    public function gettastwineList($fields,$where,$orderby,$start=0,$size=0){
        $list = $this->alias('a')
            ->join('savor_smallapp_activity activity on a.activity_id=activity.id','left')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->field($fields)
            ->where($where)
            ->order($orderby)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('a')
            ->join('savor_smallapp_activity activity on a.activity_id=activity.id','left')
            ->join('savor_smallapp_user user on a.openid=user.openid','left')
            ->where($where)
            ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        return $data;
    }

}