<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class AccountStatementModel extends BaseModel{
	protected $tableName = 'account_statement';

	public function getAll($where,$order, $start=0,$size=5){
		$sql =   "SELECT sast.id statementid,sast.cost_type,sast.fee_start,sast.fee_end,sast.create_time,sast.remark stremark,sast.count count,sainfo.receipt_addr,
  sainfo.receipt_tel,sainfo.receipt_head,sainfo.receipt_taxnum,suser.remark uremark
   FROM savor_account_statement sast join  savor_account_info  sainfo ON
sast.receipt_addrid = sainfo.id left join  savor_sysuser suser on  sast.operatorid = suser.id where $where order by $order limit $start, $size";
		$list = $this->query($sql);

		$sqlb = "SELECT count(*) cot
   FROM savor_account_statement sast join  savor_account_info  sainfo ON
sast.receipt_addrid = sainfo.id left join  savor_sysuser suser on  sast.operatorid = suser.id where $where";
		$count = $this->query($sqlb);
		$count = $count[0]['cot'];
		$objPage = new Page($count,$size);
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);

		return $data;
	}

	public function getInfo($field ='*',$where,$order,$limit){
        $result = $this->field($field)->where($where)->order($order)->limit($limit)->select();
        return $result;
    }

	public function saveData($data, $where) {
		$bool = $this->where($where)->save($data);
		return $bool;
	}

	public function addData($data) {
		$result = $this->add($data);
		return $result;
	}


	public function getOne($id){
		if ($id) {
			$res = $this->find($id);
			return $res;
		}

	}

	public function getWhereCount($where){
	    return $this->where($where)->count();
	}

	public function getWhereData($where, $field='') {
		$result = $this->where($where)->field($field)->select();
		return  $result;
	}

}
