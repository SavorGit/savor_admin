<?php
/**
 *@desc    聚屏广告
 *@author  zhang.yingtao
 *@since   20180410
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class PubPolyAdsModel extends BaseModel{
	protected $tableName = 'pub_poly_ads';
	public function getList($fields,$where, $order='id desc', $start=0,$size=5){
	    $list = $this->alias('a')
	                 ->join('savor_ads ads on a.ads_id=ads.id','left')
	                 ->join('savor_media media on ads.media_id=media.id','left')
	                 ->join('savor_sysuser user on a.creator_id=user.id','left')
	                 ->field($fields)
	                 ->where($where)
	                 ->order($order)
	                 ->limit($start,$size)
	                 ->select();
	    $count = $this->alias('a')
            	      ->join('savor_ads ads on a.ads_id=ads.id','left')
            	      ->join('savor_media media on ads.media_id=media.id','left')
            	      ->join('savor_sysuser user on a.creator_id=user.id','left')
	                  ->where($where)->count();
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
	
	public function addInfo($data,$type=1){
	    if($type == 1){
	        $ret = $this->add($data);
	    }else {
	        $ret = $this->addAll($data);
	    }
	    return $ret;
	}
	public function getInfo($fields,$where,$order,$limit,$type=1){
	    if($type==1){
	        $data = $this->alias('a')
            	         ->join('savor_ads ads on a.ads_id=ads.id','left')
            	         ->join('savor_media media on ads.media_id=media.id','left')
            	         ->join('savor_sysuser user on a.creator_id=user.id','left')
            	         ->field($fields)
            	         ->order($order)
            	         ->where($where)
            	         ->find();
	    }else {
	        $data = $this->alias('a')
            	         ->join('savor_ads ads on a.ads_id=ads.id','left')
            	         ->join('savor_media media on ads.media_id=media.id','left')
            	         ->join('savor_sysuser user on a.creator_id=user.id','left')
            	         ->field($fields)
            	         ->where($where)
            	         ->order($order)
            	         ->limit($limit)
            	         ->select();
	    }
	    return $data;
	}
	public function updateInfo($where,$data){
	    $ret = $this->where($where)->save($data);
	    return $ret;
	}
}