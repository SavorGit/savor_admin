<?php
/**
 *@author zhang.yingtao
 * 
 * 
 */
namespace Admin\Model;

use Think\Model;
use Common\Lib\Page;
class AdminLogModel extends Model
{
	protected $tableName='admin_log';
	public function addInfo($data){
	    if(!empty($data) && is_array($data)){
	        return $this->add($data);
	    }else {
	        return false;
	    }
	    
	}
	
	public function getList($where, $order='id desc', $start=0,$size=5){
	    $data = array();
	    $totalSql  = "SELECT COUNT(*) as num FROM `savor_admin_log` {$where} order by id desc";
	    $totalRows = $this->query($totalSql);
	    $totalRows = !empty($totalRows)?$totalRows[0]['num']:0;
	    $pageShow = new Page($totalRows,$size);
	    $show = $pageShow->admin_page();//分页显示输出
	    //进行分页数据查询 使用page类属性
	    $getSql = "select * from `savor_admin_log` {$where} order by {$order} limit $start,$size";
	    $list = $this->query($getSql);
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
}
