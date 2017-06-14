<?php
/**
 * @author zhang.yingtao
 * @since  2017-06-13
 */
namespace Admin\Model;
use Admin\Model\BaseModel;
class UserMarkModel extends BaseModel{
    protected $tableName='user_mark';
    public function getInfoByModelid($mobile_id){
        
        $info = $this->where("mobile_id='".$mobile_id."'")->find();
       
        return $info;
    }
}