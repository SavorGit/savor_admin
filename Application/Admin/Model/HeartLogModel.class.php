<?php
/**
 *@author hongwei
 * 
 * 
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;
class HeartLogModel extends BaseModel
{
	protected $tableName='heart_log';

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
    public function getCount($where){
        $m_box = new \Admin\Model\BoxModel();
        $m_hotel = new \Admin\Model\HotelExtModel();
        $list = $this->where($where)->select();
        foreach($list as $key=>$val){
            if($val['type'] ==1){ //小平台
                $ret = $m_hotel->isHaveMac('he.mac_addr','  he.hotel_id='.$val['hotel_id']);
            
                if($ret[0]['mac_addr'] != $val['box_mac']){
                    unset($list[$key]);
                    continue;
                }
            }else if($val['type'] ==2){//机顶盒
                $ret =$m_box->getUsedBoxByMac($val['box_mac']);
                if(empty($ret)){
                    unset($list[$key]);
                    continue;
                }
            }
        }      
        $count = count($list);  
        return $count;
    }


	/**
	 * [areaIdToAareName description]
	 * @param  array  $result [description]
	 * @return [type]         [description]
	 */
	public function areaIdToAareName($result=[])
	{
		if(!$result || !is_array($result))
		{
			return [];
		}

		$area = $this->getAllArea();

		foreach ($result as &$value) 
		{
			foreach($area as $row)
			{	
				if($value['area_id'] == $row['id'])
				{
					$value['area_name'] = $row['region_name'];
				}
					
			}

		}

		return $result;

	}//End Function




}//End Class