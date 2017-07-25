<?php
/**
 *@desc   文章来源model
 *@author zhang.yingtao
 *@since  2017-06-15 
 * 
 */
namespace Admin\Model;

use Common\Lib\Page;
use Admin\Model\BaseModel;

class ArticleSourceModel extends BaseModel{
	protected $tableName = 'article_source';
	/**
	 * @desc 获取文章来源列表
	 */
	public function getList($where,$order='id desc ',$start=0,$size=5){
	    $list = $this->alias('a')
	         ->join(' savor_sysuser b on a.add_user_id=b.id')
	         ->join(' savor_media c on a.logo = c.id')
	         ->field('a.*,b.remark,c.oss_addr')
	         ->where($where)
			 ->order($order)
			 ->limit($start,$size)
			 ->select();
	    $count = $this->alias('a')->where($where)
	                  ->count();
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
	/**
	 * @desc 新增数据
	 */
	public function addInfo($data){
	    if(!empty($data) && is_array($data)){
	        return $this->add($data);
	    }else {
	        return false;
	    }
	}
	/**
	 * @desc 获取文章来源信息
	 */
	public function getInfoById($id){
	    if($id){
	        $data = $this->alias('a')
	             ->join(' savor_media b on a.logo=b.id ')
	             ->field('a.*,b.oss_addr')
	             ->where('a.id='.$id)
	             ->find();
	        return $data;
	    }else {
	        return false;
	    }
	}
	/**
	 * @desc 编辑信息
	 */
	public function updateInfo($where ,$data){
	    return $this->where($where)->save($data);
	}
	/**
	 * @desc 删除信息
	 */
	public function deleteInfoById($id){
	    return $this->where('id='.$id)->delete();
	}
	/**
	 * @desc 获取来源所有正常数据
	 */
	public function getAll(){
	    return $this->where('status = 1')->select();
	}
	/**
	 * @desc 获取数据
	 */
	public function getWhere($field = '*',$where){
	    $result = $this->field($field)->where($where)->select();
	    return $result;
	}
}