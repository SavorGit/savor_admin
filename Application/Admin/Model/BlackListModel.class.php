<?php
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;
class BlackListModel extends Model
{

    protected $tableName='black_list';


    public function __consruct($name){
        parent::__construct();
        $this->tableName = $name;
    }
    public function getList($where, $order='id desc', $start=0,$size=5){
	    $list = $this->where($where)
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
	public function countBlackBoxNum(){
	    $yestoday_time = strtotime('-1 day');
	    $yestoday_start = date('Y-m-d 00:00:00',$yestoday_time);
	    $yestoday_end   = date('Y-m-d 23:59:59',$yestoday_time);
	    $where = array();
	    $where['create_time'] = array('EGT',$yestoday_start);
	    $where['create_time'] = array('ELT',$yestoday_end);
	    $nums = $this->where($where)->count();
	    return $nums;
	}
}