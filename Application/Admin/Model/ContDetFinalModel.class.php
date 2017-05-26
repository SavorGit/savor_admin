<?php
/**
 * @author zhang.yingtao
 * @desc 内容与推广数据
 */
namespace Admin\Model;
use Common\Lib\Page;
use Think\Model;
class ContDetFinalModel extends Model
{
    protected $tableName='content_details_final';
    public function getDataList($where, $order='id desc', $start=0,$size=5){
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
    public function getAllList($where,$order){
        $list = $this->where($where)
        ->order($order)
        ->select();
        return $list;
    }
}
