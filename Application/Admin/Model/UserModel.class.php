<?php
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;
class UserModel extends Model{
    protected $tableName='sysuser';
    public function getUserlist($where,$order,$start=0,$size=5){
        $data = array();
        $totalSql  = "SELECT COUNT(*) as num FROM `savor_sysuser` {$where} order by id desc";
        $totalRows = $this->query($totalSql);
        $totalRows = !empty($totalRows)?$totalRows[0]['num']:0;
        $pageShow = new Page($totalRows,$size);
        $show = $pageShow->admin_page();//分页显示输出
        //进行分页数据查询 使用page类属性
        $getSql = "select * from `savor_sysuser` {$where} order by {$order} limit $start,$size";
        $list = $this->query($getSql);
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
    
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
    public function getUserInfo($id) {
        $data  = array();
        $getInfoSql  = "SELECT * FROM `savor_sysuser` WHERE id = '{$id}'";
        $InfoData    = $this->query($getInfoSql);
        $data        = !empty($InfoData)? $InfoData[0] : $data;
        
        return $data;
    }
    
    //查询当前用户权限
    public function getUserRank($uid){
            $data = array();
            $getRankSql = "SELECT u.id, u.remark, u.username, u.groupId as gid, r.id as rid, r.staff_id, r.code from `savor_sysuser` as u left join `savor_staffauth` as r ON u.id=r.staff_id where u.id = {$uid} limit 1";
            $getInfo = $this->query($getRankSql);
            $data    = !empty($getInfo)? $getInfo[0] : $data;
            return $data;
    }
    
    public function modifyUserRankByGroupid($id,$code){
        $sql = "update savor_staffauth set code='$code' where staff_id in(select id from savor_sysuser where groupId='$id')";
        $res = $this->execute($sql);
        return $res;
    }
    
    //删除数据
    public function delData($id) {
        $delSql = "DELETE FROM `savor_sysuser` WHERE id = '{$id}'";
        $result = $this -> execute($delSql);
        return  $result;
    }
    
    //检测用户登陆
    public function checkUser($data) {
        if(is_array($data) && !empty($data)) {
            $userName = $data['userName'];
            $userPwd  = md5($data['userPwd']);
            $checkUser= "SELECT * FROM `savor_sysuser` where `username` = '{$userName}' and `password` = '{$userPwd}' and `status` = 1";
            $result   = $this->query($checkUser);
            return $result;
        } else {
            return 0;
        }
    }
    
    public function getUser($where=''){
        $checkUser= "SELECT * FROM `savor_sysuser` where 1 ".$where." and `status` = 1";
        $result   = $this->query($checkUser);
        return $result;
    }

    public function getUserCount($where){
        return $this->where($where)->count();
    }
    public function getGourpList($fields,$where,$order,$limit){
        $data = $this->alias('a')
             ->join('savor_sysusergroup b on a.groupId= b.id','left')
             ->field($fields)
             ->where($where)
             ->order($order)
             ->limit($limit)
             ->select();
        return $data;
    }
}