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

    public function updateGoodsname($attr_id,$name,$new_name){
        $where = array('attr_id'=>$attr_id);
        $attrs = $this->getAll('*',$where,0,1000,'id desc','goods_id');
        $goods_ids = array();
        foreach ($attrs as $v){
            $goods_ids[]=$v['goods_id'];
        }
        if(!empty($goods_ids)){
            $m_dishgoods = new \Admin\Model\Smallapp\DishgoodsModel();
            $goods_where = array('id'=>array('in',$goods_ids));
            $res_goods = $m_dishgoods->getDataList('*',$goods_where,'id desc');
            foreach ($res_goods as $gv){
                if(!empty($gv['attr_name'])){
                    $data = array('attr_name'=>str_replace($name,$new_name,$gv['attr_name']));
                    $m_dishgoods->updateData(array('id'=>$gv['id']),$data);
                }
            }
        }
        return true;
    }

}