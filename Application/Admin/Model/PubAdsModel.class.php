<?php
/**
 *@author zhang.yingtao
 *
 */
namespace Admin\Model;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class PubAdsModel extends BaseModel
{
	protected $tableName='pub_ads';
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
}