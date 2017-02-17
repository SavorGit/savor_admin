<?php
/**
 *TV model
 *@author  hongwei <[<email address>]>
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;
use Admin\Model\BoxModel;

class TvModel extends BaseModel{
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



	/**
	 * 机顶盒ID转换为机顶盒名称
	 * @param  array  $result [description]
	 * @return [type]         [description]
	 */
	public function boxIdToBoxName($result=[]){
		if(!$result || !is_array($result)){
			return [];
		}
		$boxId = [];
		foreach ($result as $value){
			$boxId[] = $value['box_id'];
		}
		$filter       = [];
		$filter['id'] = ['IN',$boxId];
		$boxModel = new BoxModel;
		$arrBox = $boxModel->getrealAll('id,name',$filter);
		foreach ($result as &$value){
			foreach ($arrBox as  $row){
				if($value['box_id'] == $row['id']){
					$value['box_name'] = $row['name'];
				}
			}
		}
		return $result;
	}

}
