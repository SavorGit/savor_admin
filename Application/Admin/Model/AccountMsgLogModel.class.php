<?php
/**
 *@desc 短信发送
 *@author  zhang.yingtao
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class AccountMsgLogModel extends BaseModel{
	protected $tableName = 'account_msg_log';

	public function getOne($map = array(),$order){
		if(!empty($map)){
			$result = $this->where($map)
				->order($order)
				->limit(0,1)
				->find();
			return $result;
		}
	}


	public function addData($data) {
		$bool = $this->add($data);
		return $bool;
	}

    public function getList($fields,$where,$order,$start,$size){
        $list = $this->alias('a')
                     ->join('savor_option_task task on a.detail_id=task.id','left')
                     ->join('savor_sysuser user on task.exe_user_id= user.id','left')
                     ->field($fields)
                     ->where($where)
                     ->order($order)
                     ->limit($start,$size)
                     ->select();
        $count = $this->alias('a')
                      ->where($where)
                      ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

}
