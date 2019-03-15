<?php

namespace Admin\Model;
use Common\Lib\Page;

class ForscreenAdsModel extends BaseModel{
	protected $tableName='forscreen_ads';

	public function getList($field,$where,$group, $order='id desc', $start=0,$size=5){
		$list = $this->alias('forscreenads')
			->where($where)
			->field($field)
			->group($group)
			->join('LEFT JOIN savor_forscreen_ads_box sbox ON sbox.forscreen_ads_id = forscreenads.id ')
			->join('LEFT JOIN savor_ads ads ON ads.id=forscreenads.ads_id')
			->order($order)
			->limit($start,$size)
			->select();
		$count = $this
			->field('forscreenads.id')
			->join('LEFT JOIN savor_forscreen_ads_box sbox ON sbox.forscreen_ads_id = forscreenads.id ')
			->join('LEFT JOIN savor_ads ads ON ads.id=forscreenads.ads_id')
			->group($group)
			->alias('forscreenads')
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
            ->join('savor_ads ads ON pads.ads_id = ads.id')
            ->join('savor_media  med ON med.id = ads.media_id')
            ->field($field)
            ->find();
        return $list;
    }
}



