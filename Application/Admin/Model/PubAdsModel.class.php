<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class PubAdsModel extends BaseModel
{
	protected $tableName='pub_ads';

	public function getBoxPlayTimes($where, $field) {
		$list = $this->alias('ads')
					 ->where($where)
					 ->join('savor_pub_ads_box ads_box ON ads.id
					 = ads_box.pub_ads_id')
			         ->field($field)
			         ->select();
		return $list;
	}


	public function getPubAdsInfoByid($field, $where) {
		$list = $this->alias('pads')
			->where($where)
			->join('savor_ads ads ON pads.ads_id = ads.id')
			->join('savor_media  med ON med.id = ads.media_id')
			->field($field)
			->find();
		return $list;
	}

	public function getWhere($where, $field){
		$list = $this->where($where)->field($field)->select();
		return $list;
	}


	public function getList($field,$where, $order='id desc', $start=0,$size=5)
	{


		$list = $this->alias('pads')
			->where($where)
			->field($field)
			->join('left join savor_ads ads ON pads.ads_id = ads.id ')
			->order($order)
			->limit($start,$size)
			->select();

		$count = $this
			->join('savor_ads ads ON pads.ads_id = ads.id ')
			->alias('pads')
			->where($where)
			->count();

		$objPage = new Page($count,$size);

		$show = $objPage->admin_page();


		$data = array('list'=>$list,'page'=>$show);


		return $data;

	}

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

	public function getEmptyLocationList(){
	    $where = array();
	    $where['state'] = 0;  //发布但未添加具体机顶盒位置的广告
	    $order = 'id asc';
	    $fields = 'id,start_date,end_date';
	    $data = $this->field($fields)->where($where)->order($order)->select();
	    return $data;
	}
	public function updateInfo($where,$data){
	    $ret = $this->where($where)->save($data);
	    return $ret;
	}
}//End Class


