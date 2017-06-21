<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class StatementDetailModel extends BaseModel{
	protected $tableName = 'account_statement_detail';


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

		$sql =" select sdet.fee_start,sdet.fee_end, sdet.id, sht.bill_tel tel, sht.id hotelid from savor_account_statement_detail  sdet join savor_hotel sht on sdet.hotel_id = sht.id $where limit 1";
		$result = $this->query($sql);
		return $result[0];
	}

}
