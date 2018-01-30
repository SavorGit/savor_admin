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
use Common\Lib\SavorRedis;

class TvModel extends BaseModel{
    protected $tableName  ='tv';
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


	public function changeTv($result=[]){

		$r_arr = array(
			1=>'包间',
			2=>'大厅',
			3=>'等候区',
		);
		$tv_arr = array(
			1=>'ant',
			2=>'av',
			3=>'hdmi',
		);
		$tv_stet = array(
			1=>'正常',
			2=>'冻结',
			3=>'报损',
		);

		if(!$result || !is_array($result)){
			return [];
		}
		//$boxId = [];
		$boxModel = new BoxModel;
		foreach ($result as $key=> $value){
			foreach ($r_arr as $rkey=>$rvalue){
				if($value['rtp'] == $rkey) {
					$result[$key]['rtp']  = $rvalue;
				}
			}
			if(is_NULL($value['tstate'])) {
				$result[$key]['tstate']  = '无';
				$result[$key]['tsource']  = '无';
				$result[$key]['tbr']  = '无';
				$result[$key]['tsize']  = '无';
			} else {
				foreach ($tv_stet as $tvkey=>$tvvalue){
					if($value['tstate'] == $tvkey) {
						$result[$key]['tstate']  = $tvvalue;
					}
				}
				foreach ($tv_arr as $tkey=>$tvalue){
					if($value['tsource'] == $tkey) {
						$result[$key]['tsource']  = $tvalue;
					}
				}
			}


		}
		/*
		$filter       = [];
		$filter['id'] = ['IN',$boxId];

		$arrBox = $boxModel->getAll('id,name',$filter);
		foreach ($result as &$value){
			foreach ($arrBox as  $row){
				if($value['box_id'] == $row['id']){
					$value['box_name'] = $row['name'];
				}
			}
		}*/
		return $result;
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
		//$boxId = [];
		$boxModel = new BoxModel;
		foreach ($result as $key=> $value){
			//$boxId[] = $value['box_id'];
		  $rt =  $boxModel->getRow('id,name','id='.$value['box_id']);
		  $result[$key]['box_name'] = $rt['name'];
		}
		
		
		/*
		$filter       = [];
		$filter['id'] = ['IN',$boxId];
		
		$arrBox = $boxModel->getAll('id,name',$filter);
		foreach ($result as &$value){
			foreach ($arrBox as  $row){
				if($value['box_id'] == $row['id']){
					$value['box_name'] = $row['name'];
				}
			}
		}*/
		return $result;
	}

	public function saveBatdat($data, $id) {
		$redis  =  \Common\Lib\SavorRedis::getInstance();
		$redis->select(15);
		$cache_key = C('DB_PREFIX').$this->tableName.'_'.$id;
		$redis->set($cache_key, json_encode($data));
	}

	/**
	 * @desc 添加TV信息
	 */
	public function addData($data){
	    if(!empty($data)){
	        $this->add($data);
	        $insert_id = $this->getLastInsID();
	        if($insert_id){
	            $redis = SavorRedis::getInstance();
	            $redis->select(15);
	            $cache_key =  C('DB_PREFIX').$this->tableName.'_'.$insert_id;
	            $redis->set($cache_key, json_encode($data));
	            return $insert_id;
	        }else {
	            return false;
	        }
	    }else {
	        return false;
	    }
	}
	/**
	 * @desc 编辑TV信息
	 */
	public function editData($id,$data){
	    if(!empty($id)){
	        $rt = $this->where('id='.$id)->save($data);
	        if($rt){
	            $redis = SavorRedis::getInstance();
	            $redis->select(15);
	            $cache_key =  C('DB_PREFIX').$this->tableName.'_'.$id;
	            $redis->set($cache_key,json_encode($data));
	            return true;
	        } else {
	            return false;
	        }
	    }else {
	        return false;
	    }
	}


	public function isTvInfo($field,$where,$start,$size){
		//savor_tv
		$sql ="select $field from savor_tv as tv
	           left join savor_box as b on b.id=tv.box_id
	           left join savor_room as r on r.id=b.room_id
	           left join savor_hotel as h on h.id=r.hotel_id
	           where ".$where.' limit '.$start.','.$size;

		$countsql ="select count('id') as num from savor_tv as tv
	           left join savor_box as b on b.id=tv.box_id
	           left join savor_room as r on r.id=b.room_id
	           left join savor_hotel as h on h.id=r.hotel_id
	           where ".$where;


		$result = $this->query($sql);
		print_r($this->getLastSql());
		die;
		$counts = $this->query($countsql);
		$count = $counts[0]['num'];
		$objPage = new Page($count,$size);
		$show = $objPage->admin_page();
		$data = array('list'=>$result,'page'=>$show);
		return $data;
	}
}
