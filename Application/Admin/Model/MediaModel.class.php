<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class MediaModel extends BaseModel{

	public function getList($where, $order='id desc', $start=0,$size=5){
	    $list = $this->where($where)
					  ->order($order)
					  ->limit($start,$size)
					  ->select();
		$count = $this->where($where)->count();
		if($count){
		    $image_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
		    foreach ($list as $k=>$v){
		        $list[$k]['oss_addr'] = $image_host.$v['oss_addr'];
		    }
		}
		$objPage = new Page($count,$size);		  
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
        return $data;
	}


}
