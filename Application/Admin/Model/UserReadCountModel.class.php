<?php
/**
 * @author zhang.yingtao
 * @since  2017-06-13
 */
namespace Admin\Model;
use Admin\Model\BaseModel;
class UserReadCountModel extends BaseModel{
    protected $tableName='user_read_count';
    public function getwhere($field,$where,$order ,$limit){
        $result = $this->field($field)->where($where)->order($order)->limit($limit)->select();
        return $result;
    }
}