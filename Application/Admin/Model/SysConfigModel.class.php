<?php
/**
 *系统配置model
*@author zhang.yingtao
*
*/
namespace Admin\Model;


class SysConfigModel extends BaseModel{
    protected $tableName  ='sys_config';
    public function getOne($config_key){
        $ret = $this->where("config_key='".$config_key."'")->find();
        return $ret;
    }
    public function addData($data){
        if(!empty($data) && is_array($data)){
            $rt = $this->add($data);
            return $rt;
        }else {
            return false;
        }
    }
    public function editData($data,$config_key){
        if(!empty($data)&& is_array($data)){
            $rt = $this->where("config_key='".$config_key."'")->save($data);
            return $rt;
        }else {
            return false;
        }
    }
}