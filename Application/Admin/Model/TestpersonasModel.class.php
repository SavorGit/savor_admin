<?php
/**
 * @author zhang.yingtao
 * @since  2017-06-13
 */
namespace Admin\Model;
use Common\Lib\Page;
use Admin\Model\BaseModel;
class TestpersonasModel extends BaseModel{
    protected $tableName='personas';
    public function getList($where,$order ,$start,$size){
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
}