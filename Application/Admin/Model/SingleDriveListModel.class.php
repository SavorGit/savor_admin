<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class SingleDriveListModel extends BaseModel
{
	protected $tableName='singlezip_list';

	//�������޸�
	public function addData($data, $acttype) {
		if(0 === $acttype) {
			$result = $this->add($data);
		} else {
			$uid = $data['id'];
			$result = $this->where("id={$uid}")->save($data);
		}
		return $result;
	}

	public function getList($where, $order='id desc', $start=0,$size=5)
	{


		$list = $this->alias('a')
			->field('a.create_time, a.update_time, a.gendir, a.state,a.id,sysuser.`remark` uname')
			->join('savor_sysuser as sysuser on a.creator_id=sysuser.id','left')
			->join('savor_sysusergroup as sysgroup on sysuser.groupId=sysgroup.id','left')
			->where($where)
			->order($order)
			->limit($start,$size)
			->select();


		$count = $this->alias('a')
			->join('savor_sysuser as sysuser on a.creator_id=sysuser.id','left')
			->join('savor_sysusergroup as sysgroup on sysuser.groupId=sysgroup.id','left')
			->where($where)
			->count();

		$objPage = new Page($count,$size);

		$show = $objPage->admin_page();


		$data = array('list'=>$list,'page'=>$show);


		return $data;

	}

	public function getWhere($where, $field){
		$list = $this->where($where)->field($field)->select();
		return $list;
	}

	public function updateInfo($where,$data){
		$ret = $this->where($where)->save($data);
		return $ret;
	}


	public function getOne($field,$where){
		$data = $this->field($field)->where($where)->find();
		return $data;
	}

	public function getOrderOne($field,$where, $order,$limit){
		$data = $this->field($field)->where($where)->order($order)->limit($limit)->select();
		return $data;
	}


}//End Class