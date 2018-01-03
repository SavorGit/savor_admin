<?php
/**
 * @desc  餐厅端邀请码
 *@author zhang.yingtao
 *@since  20171129
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class HotelInviteCodeModel extends BaseModel
{
	protected $tableName='hotel_invite_code';
	public function getList($fields,$where, $order='id desc', $start=0,$size=5){
	    $list = $this->alias('a')
	                 ->join('savor_sysuser b on a.creator_id= b.id','left')
	                 ->field($fields)
	                 ->where($where)
	                 ->order($order)
	                 ->limit($start,$size)
	                 ->select();
	    $count = $this->alias('a')->where($where)->count();
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
	public function countNums($where){
	    $nums = $this->where($where)->count();
	    return $nums;
	}
	public function addInfo($data,$type = 1){
	    if($type==1){
	        $ret = $this->add($data);
	    }else {
	        $ret = $this->addAll($data);
	    }
	    return $ret;
	}
	public function getInviteExcel($fields,$where, $order='id desc'){
		$list = $this->alias('a')
			->join('savor_hotel ht on ht.id= a.hotel_id','left')
			->field($fields)
			->where($where)
			->order($order)
			->select();

		return $list;
	}
}