<?php

namespace Admin\Model;
use Common\Lib\Page;

class LifeAdsModel extends BaseModel{
	protected $tableName='life_ads';

	public function getList($field,$where, $order='id desc', $start=0,$size=5){
		$list = $this->alias('lifeads')
			->where($where)
			->field($field)
			->join('LEFT JOIN savor_ads ads ON ads.id=lifeads.ads_id')
			->order($order)
			->limit($start,$size)
			->select();
		$count = $this->alias('lifeads')
			->field('lifeads.id')
			->join('LEFT JOIN savor_ads ads ON ads.id=lifeads.ads_id')
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

    public function getAdsInfoByid($field, $where) {
        $list = $this->alias('lifeads')
            ->where($where)
            ->join('savor_ads ads ON lifeads.ads_id = ads.id')
            ->join('savor_media med ON med.id = ads.media_id')
            ->field($field)
            ->find();
        return $list;
    }

    public function getDataCount($where){
        $count = $this->where($where)->count();
        return $count;
    }
}



