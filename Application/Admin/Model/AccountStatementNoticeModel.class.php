<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class AccountStatementNoticeModel extends BaseModel
{
	protected $tableName='account_statement_notice';


	public function saveStRedis($data, $id){
		$redis  =  \Common\Lib\SavorRedis::getInstance();
		$redis->select(15);
		$data = array_unique($data);
		$cache_key = C('DB_PREFIX').$this->tableName;

		$max = $redis->lsize($cache_key);
		$redisdata = $redis->lgetrange($cache_key,0,$max);
		foreach($data as $v){
			if(!in_array($v, $redisdata)){
				$redis->lpush($cache_key, $v);
			}
		}

	}


	public function insertDup($val_str, $where){
		$sql ="INSERT INTO savor_account_statement_notice(`id`)
  $val_str  ON DUPLICATE KEY UPDATE $where";
		echo '<br/>'.$sql.'<br/>';
		 $this->execute($sql);
	}

	public function saveData($data, $where) {
		$bool = $this->where($where)->save($data);
		return $bool;
	}


	public function getWhere($where, $field){
		$list = $this->where($where)->field($field)->select();
		return $list[0];
	}

	/**
	 * 获取多条数据
	 * @param  string  $field  [description]
	 * @param  string  $filter [description]
	 * @param  integer $offset [description]
	 * @param  integer $limit  [description]
	 * @param  string  $order  [description]
	 * @param  string  $group  [description]
	 * @return [type]          [description]
	 */
	public function getAll($field='*',$filter='',$order='',$group=''){
		$res = $this->field($field)
			->where($filter)
			->order($order)
			->group($group)
			->select();
		return $res;
	}

}//End Class