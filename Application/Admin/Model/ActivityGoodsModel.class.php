<?php
/**
 * @desc 活动商品
 * @author zhang.yingtao
 * @since  2017-09-09 
 */
namespace Admin\Model;
use Common\Lib\Page;
use Admin\Model\BaseModel;

class ActivityGoodsModel extends BaseModel
{
	protected $tableName='activity_goods';
    public function getOne($fields = '*',$where){
        $data = $this->field($fields)->where($where)->find();
        return $data;
    }
    public function getInfo($fields = '*',$where,$order){
        $data = $this->field($fields)->order($order)->where($where)->select();
        return $data;
    }
    /**
     * @desc  获取列表
     */
    public function getList($field="*",$where,$order,$start,$size){
        $list = $this->alias('a')
        ->join('savor_sysuser b on a.operator_id = b.id')
        ->join('savor_activity_config c on a.activity_id =c.id')
        ->field($field)
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
    /**
     * @desc 新增数据
     */
    public function addInfo($data){
        return $this->add($data);
    }
    /**
     * @desc 修改信息
     */
    public function editInfo($map,$data){
        $ret = $this->where($map)->save($data);
        return $ret;
    }
}	