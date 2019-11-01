<?php
/**
 * @desc   销售端-服务模型
 * @author zhang.yingtao
 * @since  2019-11-01
 */

namespace Admin\Model\Integral;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class ServiceMxModel extends BaseModel{
	
	protected $tableName='integral_service_model';
	public function getList($fields,$where,$order,$start,$size){
	    $list = $this->alias('a')
	                 ->join('savor_sysuser user on a.uid=user.id','left')
	                 ->field($fields)
	                 ->where($where)
	                 ->order($order)
	                 ->limit($start,$size)
	                 ->select();
	    $count = count($list);
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
}