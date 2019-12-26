<?php
/**
 * @desc 心跳上报历史统计数据表
 * @since 20170815
 * @author zhang.yingtao
 */
namespace Admin\Model;
use Common\Lib\Page;
class HeartAllLogModel extends BaseModel{
	protected $tableName='heart_all_log';
	
	/**
	 * @获取分页数据
	 */
	public function getlist($field= '*',$where,$order,$start=0,$size=5){
	    $list = $this->field($field)->where($where)->order($order)->limit($start,$size)->select();
	    $count = $this->getCount($where);
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;  
	}

	public function getOne($mac,$type,$date){
	    $where = array();
	    $where['date']= $date;
	    $where['mac'] = $mac;
	    $where['type']= $type;
	    $info = $this->where($where)->find();
	    return $info;
	}

	public function getCount($where){
        $count = $this->where($where)->count();
        return $count;
    }

	public function addInfo($data){
	    if(!empty($data)){
	        $ret = $this->add($data);
	    }else{
	        $ret = false;
	    }
	    return $ret;
	}

	public function updateInfo($mac,$type,$date,$filed,$apk = ''){
	    $set = '';
	    if(!empty($apk)){ 
	        $set  = ",apk_version='".$apk."'";
	    }
	    $sql ="update savor_heart_all_log set `$filed` = `$filed`+1 ".$set."
	    where `date`={$date} and  `mac`='{$mac}' and `type`={$type}";
	    return $this->execute($sql);
	}

	public function getCountByHour($dates,$area_id){
	    $where = array('date'=>array('in',$dates));
	    $where['type'] = 2;
	    if($area_id){
	        $where['area_id'] = $area_id;
        }



    }
}