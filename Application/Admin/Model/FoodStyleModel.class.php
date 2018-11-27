<?php
/**
 * @author zhang.yingtao
 * @desc   酒楼菜系
 * @since  20181127
 * 
 */
namespace Admin\Model;

use Think\Model;

class FoodStyleModel extends Model
{
	protected $tableName='hotel_food_style';
	public function getWhere($fields,$where,$order,$limit){
	    $data = $this->field($fields)->where($where)->order($order)->limit($limit)->select();
	    return $data;
	}
}