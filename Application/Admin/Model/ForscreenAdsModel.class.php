<?php

namespace Admin\Model;
use Common\Lib\Page;

class ForscreenAdsModel extends BaseModel{
	protected $tableName='forscreen_ads';

	public function getList($field,$where, $order='id desc', $start=0,$size=5){
		$list = $this->alias('forscreenads')
			->where($where)
			->field($field)
			->join('LEFT JOIN savor_ads ads ON ads.id=forscreenads.ads_id')
			->order($order)
			->limit($start,$size)
			->select();
		$count = $this->alias('forscreenads')
			->field('forscreenads.id')
			->join('LEFT JOIN savor_ads ads ON ads.id=forscreenads.ads_id')
			->where($where)
			->count();
		$objPage = new Page($count,$size);
		$pagestyle = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$pagestyle);
		return $data;
	}

    public function getWhere($where, $field){
        $list = $this->where($where)->field($field)->select();
        return $list;
    }

    public function getForscreenAdsInfoByid($field, $where) {
        $list = $this->alias('forscreenads')
            ->where($where)
            ->join('savor_ads ads ON forscreenads.ads_id = ads.id')
            ->join('savor_media med ON med.id = ads.media_id')
            ->field($field)
            ->find();
        return $list;
    }
}



