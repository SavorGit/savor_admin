<?php
/**
 * @desc  微信授权登录查看文章记录微信用户信息
 * @author zhang.yingtao
 * @since  2017.08.28
 *
 */
namespace Admin\Model;
use Admin\Model\BaseModel;
class ContentWxAuthModel extends BaseModel
{
	protected $tableName='content_wx_auth';
	
	/**
	 * @desc 添加数据
	 */
	public function addInfo($data){
	    return $this->add($data);
	}
}