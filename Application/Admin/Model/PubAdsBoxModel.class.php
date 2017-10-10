<?php
/**
 *@author zhang.yingtao
 *
 */
namespace Admin\Model;
use Admin\Model\BaseModel;
use Common\Lib\Page;
class PubAdsBoxModel extends BaseModel
{
	protected $tableName='pub_ads_box';
	public function getBoxArrByPubAdsId($pub_ads_id){
	    $fields = 'box_id';
	    $where = array('pub_ads_id'=>$pub_ads_id);
	    $group = ' box_id';
	    $data = $this->field($fields)->where($where)->group($group)->select();
	    return $data;
	}
	/**
	 * @desc 取出该机顶盒所有未填写位置的列表
	 */
	public function getEmptyLocation($pub_ads_id,$box_id){
	    $fields = 'id';
	    $where = array();
	    $where['pub_ads_id'] = $pub_ads_id;
	    $where['box_id']     = $box_id;
	    $where['location_id']= array('eq',0);
	    $order = ' id asc';
	    $data = $this->field($fields)->where($where)->order($order)->select();
	    return $data;
	}
	public function getLocationList($pub_ads_id,$box_id,$start_date,$end_date){
	    /* $where  = '  a.pub_ads_id='.$pub_ads_id
	              .' and a.box_id='.$box_id
	              ." and ((b.start_date>='".$start_date."' and b.start_date<='".$end_date."')
	                       or (b.start_date<='".$start_date."' and b.end_date>='".$end_date."')
	                       or (b.end_date>='".$start_date."' and b.end_date<='".$end_date."'))"; */
	    $where = '  a.pub_ads_id='.$pub_ads_id
	              .' and a.box_id='.$box_id
	              ." and '".$end_date."'>=b.start_date and '".$start_date."'<=b.end_date";
	    $data = $this->alias('a')
	         ->field('a.location_id')
	         ->join('savor_pub_ads b on a.pub_ads_id=b.id','left')
	         ->where($where)
	         ->select();            
	    return $data;
	}
	public function updateInfo($where,$data){
	    $ret = $this->where($where)->save($data);
	    return $ret;
	}
}