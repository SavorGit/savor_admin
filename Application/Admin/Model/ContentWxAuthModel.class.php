<?php
/**
 * @desc  微信授权登录查看文章记录微信用户信息
 * @author zhang.yingtao
 * @since  2017.08.28
 *
 */
namespace Admin\Model;
use Common\Lib\Page;
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

	public function getList($fields= "*",$where, $order='id desc', $start=0,$size=5){
	    $list = $this->alias('a')
            	     ->join('savor_mb_content b on a.contentid=b.id','left')
            	     ->join('savor_mb_hot_category c on b.hot_category_id=c.id')
            	     ->field($fields)
            	     ->where($where)
            	     ->order($order)
            	     ->limit($start,$size)
            	     ->select();
	    $count = $this->alias('a')->where($where)->count();
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}
	public function getInfo($fields= "*",$where,$order,$limit,$type = 1){
	    if($type==1){
	        $data = $this->alias('a')
                	     ->join('savor_mb_content b on a.contentid=b.id','left')
                	     ->join('savor_mb_hot_category c on b.hot_category_id=c.id')
                	     ->field($fields)
                	     ->where($where)
                	     ->order($order)
                	     ->find();
	    }else {
	        $data = $this->alias('a')
                	     ->join('savor_mb_content b on a.contentid=b.id','left')
                	     ->join('savor_mb_hot_category c on b.hot_category_id=c.id')
                	     ->field($fields)
                	     ->where($where)
                	     ->order($order)
                	     ->limit($limit)
                	     ->select();
	    }
	    return $data;
	}
}