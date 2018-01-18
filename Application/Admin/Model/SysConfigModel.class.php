<?php
/**
 *系统配置model
*@author zhang.yingtao
*
*/
namespace Admin\Model;


class SysConfigModel extends BaseModel{
    protected $tableName  ='sys_config';

    public function getInfo($where){
        if($where){
            $where =" config_key in(".$where.") and status=1";
        }
        $result = $this->where($where)->select();
        return $result;
    }

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
    public function getList($where){
        $ret = $this->where($where)->select();
        return $ret;
    }
    public function updateInfo($data){
        if(!empty($data) && is_array($data)){
            $where ="";
            foreach($data as $key=>$v){
                $where .= $space ."('$key','$v','1')";
                $space =',';
            }
            $sql =" replace into `savor_sys_config` (`config_key`,`config_value`,`status`) values ".$where;
            return $this->execute($sql);
        }else {
            return false;
        }
        
    }
}