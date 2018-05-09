<?php
/**
 *酒店model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class ProgramMenuItemModel extends BaseModel
{
	protected $tableName='programmenu_item';

	public function getAdInfoByAid($where, $order, $field) {
		$list = $this->alias('a')
					 ->where($where)
			         ->join(' LEFT JOIN savor_ads ads on ads.id = a.ads_id')
					 ->order($order)
			         ->field($field)
			         ->select();
		return $list;
	}


	public function getWhere($where, $order, $field){

		$list = $this->where($where)->order($order)->field($field)->select();



		return $list;
	}

	public function getCopyMenuInfo($where, $order, $field){

		$list = $this->alias('spi')
					 ->where($where)
					 ->join('`savor_ads` as sads on spi.ads_id = sads.id','left')
					 ->order($order)
					 ->field($field)
					 ->select();



		return $list;
	}


	public function getList($where, $order='id desc', $start=0,$size=5)
	{	
		
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


	}//End Function

    public function getMediaList($fields,$where,$order,$limit){
        $data = $this->alias('a')
                ->join('savor_ads ads on a.ads_id=ads.id','left')
                ->join('savor_media media on media.id=ads.media_id','left')
                ->field($fields)
                ->where($where)
                ->order($order)
                ->limit($limit)
                ->select();
        return $data;             
    }
    /**
     * @desc 获取节目单的宣传片数据
     */
    public function getadvInfo($hotelid,$menuid){
        $field = "media.id AS media_id,
				item.ads_name AS media_name,
                'adv' as type";
        $sql = "select ".$field;
         
        $sql .= " FROM savor_ads ads
        LEFT JOIN savor_programmenu_item item on ads.name like CONCAT('%',item.ads_name,'%')
        LEFT JOIN savor_media media on media.id = ads.media_id
        where item.type=3
        and ads.hotel_id={$hotelid}
        and (item.ads_id is null or item.ads_id=0)
        and ads.state=1
        and item.menu_id={$menuid}
        and media.oss_addr is not null order by item.sort_num asc";
        $result = $this->query($sql);
        return $result;
    }


}//End Class
