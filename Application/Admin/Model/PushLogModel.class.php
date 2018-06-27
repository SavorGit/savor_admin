<?php
/**
 * @author zhang.yingtao
 * @since  20180626
 * @desc   友盟推送日志
 */
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;
class PushLogModel extends Model
{
	protected $tableName='push_log';
	public function addInfo($data,$type= 1){
	    if($type==1){
	        $ret = $this->add($data);
	    }else {
	        $ret = $this->addAll($data);
	    }
	    return $ret;
	}
}