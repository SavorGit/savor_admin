<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class GoodsAttrModel extends BaseModel{
	protected $tableName='smallapp_goods_attr';


    public function getGoodsAttrByPid($pid,$specification_id=0){
        $m_dishgoods = new \Admin\Model\Smallapp\DishgoodsModel();
        $res_goods = $m_dishgoods->getDataList('id',array('parent_id'=>$pid),'id desc');
        $attrs = array();
        if(!empty($res_goods)){
            $goods_ids = array();
            foreach ($res_goods as $v){
                $goods_ids[]=$v['id'];
            }
            $where = array('goods_id'=>array('in',$goods_ids));
            if($specification_id){
                $where['specification_id'] = $specification_id;
            }
            $attrs = $this->getDataList('*',$where,'id desc');
        }
        return $attrs;
    }

}