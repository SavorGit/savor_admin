<?php

namespace Admin\Model;
use Common\Lib\Page;

class StoresaleAdsModel extends BaseModel{
	protected $tableName='storesale_ads';

	public function getList($field,$where, $order='id desc', $start=0,$size=5){
		$list = $this->alias('storesaleads')
			->where($where)
			->field($field)
			->join('LEFT JOIN savor_ads ads ON ads.id=storesaleads.ads_id')
			->order($order)
			->limit($start,$size)
			->select();
		$count = $this->alias('storesaleads')
			->field('storesaleads.id')
			->join('LEFT JOIN savor_ads ads ON ads.id=storesaleads.ads_id')
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
        $list = $this->alias('storesaleads')
            ->where($where)
            ->join('savor_ads ads ON storesaleads.ads_id = ads.id')
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



