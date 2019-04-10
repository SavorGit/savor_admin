<?php
/**
 *@author zhang.yingtao
 *@since  20190322
 *
 */
namespace Admin\Model\Smallapp;

use Admin\Model\BaseModel;
use Common\Lib\Page;

class SimuForscreenLogModel extends BaseModel
{
	protected $tableName='smallapp_simuforscreen_log';
	
    public function addInfo($data,$type=1){
	    if($type==1){
	        $ret = $this->add($data);
	        
	    }else {
	        $ret = $this->addAll($data);
	    }
	    return $ret;
	}
}