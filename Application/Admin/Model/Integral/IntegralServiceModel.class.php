<?php
/**
 * @desc   ���۶�-����
 * @author zhang.yingtao
 *
 */

namespace Admin\Model\Integral;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class IntegralServiceModel extends BaseModel{
	
	protected $tableName='integral_service';
	public function getList($fields,$where,$order,$start,$size){
	    $list = $this->alias('a')
	                 ->join('savor_sysuser user on a.uid=user.id','left')
	                 ->field($fields)
	                 ->where()
	                 ->order($order)
	                 ->limit($start,$size)
	                 ->select();

	    $count = count($data);
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
}