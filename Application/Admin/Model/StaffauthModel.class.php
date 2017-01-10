<?php
namespace Admin\Model;
use Think\Model;
class StaffauthModel extends Model{
    protected $tableName='staffauth';
    //新增和修改
    public function addData($data, $acttype) {
        if(0 === $acttype) {
            $result = $this->add($data);
        } else {
            $uid = $data['id'];
            $result = $this->where("id={$uid}")->save($data);
        }
        return $result;
    }
    
    //查找其中的一条
    public function getInfo($id) {
        $data  = array();
        $getInfoSql  = "SELECT * FROM `savor_staffauth` WHERE staff_id = '{$id}'";
        $InfoData    = $this->query($getInfoSql);
        $data        = !empty($InfoData)? $InfoData[0] : $data;
        return $data;
    }
    //删除数据
    public function delData($id) {
        $delSql = "DELETE FROM `savor_staffauth` WHERE id = '{$id}'";
        $result = $this -> execute($delSql);
        return  $result;
    }
    public function getStaffInfo($id){
        $sql = "select s.id,s.staff_id,staff_name,s.code,u.groupId,u.username FROM `savor_staffauth` as s left join savor_sysuser as u on s.staff_id=u.id where s.staff_id=$id";
        $result = $this->query($sql);
        $data   = !empty($result)? $result[0] : array();
        return $data;
    }
    
}