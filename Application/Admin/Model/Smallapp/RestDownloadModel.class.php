<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class RestDownloadModel extends BaseModel{
	protected $tableName='smallapp_rest_download';
	
	public function getList($fields="a.id",$where, $order='a.id desc', $start=0,$size=5){
	    $list = $this->alias('a')
	    ->join('savor_media media on a.media_id=media.id','left')
	    
	    ->field($fields)
	    ->where($where)
	    ->order($order)
	    ->limit($start,$size)
	    ->select();
	    $count = $this->alias('a')
        	          ->where($where)->count();
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
	public function getInfo($fields,$where){
	    $data = $this->alias('a')
	         ->join('savor_media media on a.media_id=media.id','left')
	         ->field($fields)
	         ->find();
	    return $data;     
	}
}