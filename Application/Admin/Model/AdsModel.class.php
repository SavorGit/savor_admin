<?php
/**
 *@author hongwei
 *
 *
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class AdsModel extends BaseModel{
	protected $tableName='ads';

	public function getadvInfo($hotelid, $menuid){
		$field = "media.id AS vid,
				media.oss_addr AS name,
				media.md5 AS md5,
				'easyMd5' AS md5_type,
				case ads.type
				when 1 then 'ads'
				when 2 then 'pro'
				when 3 then 'adv' END AS type,
				media.oss_addr AS oss_path,
				media.duration AS duration,
				media.surfix ,
				item.sort_num AS sortNum,
				item.ads_name AS chinese_name";
		$sql = "select ".$field;

		$sql .= " FROM savor_ads ads
        LEFT JOIN savor_menu_item item on ads.name like CONCAT('%',item.ads_name,'%')
        LEFT JOIN savor_media media on media.id = ads.media_id
        where ads.type=3
            and ads.hotel_id={$hotelid}
            and (item.ads_id is null or item.ads_id=0)
            and ads.state=1
            and item.menu_id={$menuid}

            and media.oss_addr is not null";

		$result = $this->query($sql);
		return $result;

	}


	public function getuAdvname($hotelid, $menuid){
		$field = "ads.name adname,sht.name hname,ads.id ads_id,media.oss_addr,
		sht.id hotel_id";
		$sql = "select ".$field;

		$sql .= " FROM savor_ads ads
        LEFT JOIN savor_menu_item item on ads.name like CONCAT('%',item.ads_name,'%')
        LEFT JOIN savor_media media on media.id = ads.media_id
        left join savor_hotel sht on ads.hotel_id = sht.id
        where ads.type=3
            and ads.hotel_id={$hotelid}
            and (item.ads_id is null or item.ads_id=0)
            and ads.state=1
            and item.menu_id={$menuid}

            and media.oss_addr is not null";

		$result = $this->query($sql);
		return $result;

	}

	public function getupanadvInfo($hotelid, $menuid){
		$field = "media.id AS vid,
				media.oss_addr AS name,
				media.md5 AS md5,
				'easyMd5' AS md5_type,
				case ads.type
				when 1 then 'ads'
				when 2 then 'pro'
				when 3 then 'adv' END AS type,
				media.oss_addr AS oss_path,
				media.duration AS duration,
				media.surfix ,
				item.sort_num AS sortNum,
				item.ads_name AS chinese_name";
		$sql = "select ".$field;

		$sql .= " FROM savor_ads ads
        LEFT JOIN savor_menu_item item on ads.name like CONCAT('%',item.ads_name,'%')
        LEFT JOIN savor_media media on media.id = ads.media_id
        where ads.type=3
            and ads.hotel_id={$hotelid}
            and (item.ads_id is null or item.ads_id=0)
            and ads.state=1
            and item.menu_id={$menuid}";

		$result = $this->query($sql);
		return $result;

	}

	public function getadsInfo($menuid){
		$field = "media.id AS vid,
				media.oss_addr AS name,
				media.md5 AS md5,
				'easyMd5' AS md5_type,
				case ads.type
				when 1 then 'ads'
				when 2 then 'pro'
				when 3 then 'adv' END AS type,
				media.oss_addr AS oss_path,
				media.duration AS duration,
				media.surfix ,
				item.sort_num AS `order`,
				item.ads_name AS chinese_name";
		$sql = "select ".$field;

		$sql .= "  FROM savor_ads ads
        LEFT JOIN savor_menu_item item on ads.id = item.ads_id
        LEFT JOIN savor_media media on media.id = ads.media_id
        where
            ads.state=1
            and item.menu_id={$menuid}
            and ads.type = 1
            and media.oss_addr is not null";
		$result = $this->query($sql);
		return $result;

	}

	public function  getupanproInfo($menuid){
        $field = "media.id AS vid,
                    media.oss_addr AS name,
                    media.md5 AS md5,
                    'easyMd5' AS md5_type,
                    case ads.type
                    when 1 then 'ads'
                    when 2 then 'pro'
                    when 3 then 'adv' END AS type,
                    media.oss_addr AS oss_path,
                    media.duration AS duration,
                    media.surfix ,
                    item.sort_num AS sortnum,
                    item.ads_name AS chinese_name";
        $sql = "select ".$field;

        $sql .= "  FROM savor_ads ads LEFT JOIN savor_menu_item item
              on ads.id = item.ads_id
            LEFT JOIN savor_media media on media.id = ads.media_id
            where
                ads.state=1
                and item.menu_id=$menuid
                and ads.type = 2";

        $result = $this->query($sql);
        return $result;
    }

	public function getproInfo($menuid){
		$field = "media.id AS vid,
				media.oss_addr AS name,
				media.md5 AS md5,
				'easyMd5' AS md5_type,
				case ads.type
				when 1 then 'ads'
				when 2 then 'pro'
				when 3 then 'adv' END AS type,
				media.oss_addr AS oss_path,
				media.duration AS duration,
				media.surfix ,
				item.sort_num AS sortnum,
				item.ads_name AS chinese_name";
		$sql = "select ".$field;

		$sql .= "  FROM savor_ads ads LEFT JOIN savor_menu_item item
          on ads.id = item.ads_id
        LEFT JOIN savor_media media on media.id = ads.media_id
        where
            ads.state=1
            and item.menu_id=$menuid
            and ads.type = 2
            and media.oss_addr is not null";

		$result = $this->query($sql);
		return $result;

	}

	public function getWhere($where, $field){
		$list = $this->where($where)->field($field)->select();
		return $list;
	}

	public function delData($id) {
		$delSql = "DELETE FROM `savor_mb_content` WHERE id = '{$id}'";
		$result = $this -> execute($delSql);
		return  $result;
	}

	public function getList($where, $order='id desc', $start=0,$size=5){
		$list = $this->where($where)
			->order($order)
			->limit($start,$size)
			->select();
		$count = $this->where($where)
			->count();
		$objPage = new Page($count,$size);
		$show = $objPage->admin_page();
		$data = array('list'=>$list,'page'=>$show);
		return $data;
	}

}