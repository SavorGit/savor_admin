<?php
/**
 *@author hongwei
 * 
 * 
 */
namespace Admin\Model;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class RoomModel extends BaseModel
{
	protected $tableName='room';
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



	public function saveData($data, $id = 0) {
		$redis  =  \Common\Lib\SavorRedis::getInstance();
		$redis->select(15);
		//判定key是否有没有的，如果存在则修改
		if($id){
			//获取创建时间
			$bool = $this->where('id='.$id)->save($data);
			$res = $this->find($id);
			$data['create_time'] = $res['create_time'];
			$cache_key = C('DB_PREFIX').$this->tableName.'_'.$id;
			$redis->set($cache_key, json_encode($data));
		}else{
			$data['create_time'] = date('Y-m-d H:i:s');
			$bool = $this->add($data);
			$insert_id = $this->getLastInsID();
			$cache_key = C('DB_PREFIX').$this->tableName.'_'.$insert_id;
			$redis->set($cache_key, json_encode($data));
		}
		return $bool;
	}




}//End Class