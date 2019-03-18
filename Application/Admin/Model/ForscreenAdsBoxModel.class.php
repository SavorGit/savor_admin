<?php
namespace Admin\Model;
use Common\Lib\Page;

class ForscreenAdsBoxModel extends BaseModel{
	protected $tableName='forscreen_ads_box';

    public function getList($field,$where, $order='id desc', $start=0,$size=5){
        $list = $this->alias('adsbox')
            ->where($where)
            ->field($field)
            ->join('LEFT JOIN savor_box box ON adsbox.box_id=box.id')
            ->join('LEFT JOIN savor_room room ON room.id=box.room_id')
            ->join('LEFT JOIN savor_hotel hotel ON hotel.id=room.hotel_id')
            ->order($order)
            ->limit($start,$size)
            ->select();
        $count = $this->alias('adsbox')->where($where)->count();
        $objPage = new Page($count,$size);
        $pagestyle = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$pagestyle);
        return $data;
    }

    public function getWhere($where, $field){
        $list = $this->where($where)->field($field)->select();
        return $list;
    }

    public function getDataCount($where){
        $count = $this->where($where)->count();
        return $count;
    }

	public function getBoxArrByForscreenAdsId($forscreen_ads_id){
	    $fields = 'box_id';
	    $where = array('forscreen_ads_id'=>$forscreen_ads_id);
	    $group = ' box_id';
	    $data = $this->field($fields)->where($where)->group($group)->select();
	    return $data;
	}

}



