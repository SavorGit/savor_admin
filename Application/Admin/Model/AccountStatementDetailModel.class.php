<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class AccountStatementDetailModel extends BaseModel{
	protected $tableName = 'account_statement_detail';


	public function getAll($where,$order, $start=0,$size=5){
		$sql =   "SELECT sdet.state, sht.NAME,sdet.id detailid  ,sdet.money , sdet.check_status,  sdet.hotel_id  hotelid FROM savor_account_statement_detail sdet LEFT
  JOIN savor_hotel sht ON sht.id = sdet.hotel_id where $where order by $order limit $start, $size";

		$list = $this->query($sql);

		$sqlb = "SELECT count(*) cot  FROM savor_account_statement_detail sdet LEFT
  JOIN savor_hotel sht ON sht.id = sdet.hotel_id where $where";
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

	public function getWhereSql($detailid) {
		$where = ' where 1=1 ';
		$where .= ' AND  sdet.id = '.$detailid;

		$sql =" select sdet.fee_start,sdet.fee_end, sdet.id, sht.bill_tel tel, sht.id hotelid,sdet.state state,sdet.check_status from savor_account_statement_detail  sdet join savor_hotel sht on sdet.hotel_id = sht.id $where limit 1";
		$result = $this->query($sql);
		return $result[0];
	}
	public function getBillDetail($id = 0){
	    $data = $this->alias('a')
	         ->join(' savor_account_statement b on a.statement_id=b.id')
	         ->join(' savor_hotel c on a.hotel_id = c.id')
	         ->join(' savor_account_info d on b.receipt_addrid = d.id')
	         ->field('a.check_status,c.name as hotel_name,a.money,b.cost_type,b.fee_start,
	                  b.fee_end,d.receipt_addr,d.receipt_tel,d.receipt_head,d.receipt_taxnum')
	         ->where('a.id='.$id)
	         ->find();
	    return $data; 
	}
}
