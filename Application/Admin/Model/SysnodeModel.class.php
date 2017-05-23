<?php
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;
class SysnodeModel extends Model{
    protected $trueTableName='savor_nodemenu';
    public function getList($where, $order='id desc', $start=0,$size=5){
        $data = array();
        $totalSql  = "SELECT COUNT(*) as num FROM `savor_nodemenu` {$where} order by id desc";
        $totalRows = $this->query($totalSql);
        $totalRows = !empty($totalRows)?$totalRows[0]['num']:0;
        $pageShow = new Page($totalRows,$size);
        $show = $pageShow->admin_page();//分页显示输出
        //进行分页数据查询 使用page类属性
        $getSql = "select * from `savor_nodemenu` {$where} order by {$order} limit $start,$size";
        $list = $this->query($getSql);
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
    
    //新增和修改
    public function addData($data, $acttype=0) {
        if(0 === $acttype) {
            $result = $this->add($data);
        } else {
            $id = $data['id'];
            $result = $this->where("id={$id}")->save($data);
        }
        return $result;
    }
    
    //查找其中的一条
    public function getInfo($parm) {
       $result = $this->where($parm)->find();
        return $result;
    }

    //查找其中的一条
    public function getoneInfo($id,$code='') {
        $data  = array();
        $getInfoSql  = "SELECT * FROM `savor_nodemenu` WHERE id = '{$id}'";
        $InfoData    = $this->query($getInfoSql);
        $data        = !empty($InfoData)? $InfoData[0] : $data;
        return $data;
    }

    public function getWhere($where, $field,$order){
        $list = $this->where($where)->field($field)->order($order)->select();
        return $list;
    }
    
    public function getInfoByCode($code) {
        $getInfoSql  = "SELECT * FROM `savor_sysmenu` WHERE code = '{$code}'";
        $result = $this->query($getInfoSql);
        return $result;
    }
    
    //查找当前所有的节点
    public function getAllList() {
        $data  = array();
        $getListSql  = "SELECT * FROM `savor_nodemenu` WHERE isenable=1 order by id desc";
        $listData    = $this->query($getListSql);
        $data        = !empty($listData)? $listData : $data;
        return $data;
    }
    
    //删除数据
    public function delData($id) {
        $delSql = "DELETE FROM `savor_sysmenu` WHERE id = '{$id}'";
        $result = $this -> execute($delSql);
        return  $result;
    }
    //更新模块名称
    public function updateSystemName($name="",$rename=""){
        $sql=" UPDATE `savor_sysmenu` SET modulename='".$rename."' WHERE modulename LIKE '%".$name."%'";
        $result = $this -> execute($sql);
        return  $result;
    }
    //检测用户登陆
    public function checksysmenu($data) {
        if(is_array($data) && !empty($data)) {
            $sysmenuName = $data['sysmenuName'];
            $sysmenuPwd  = md5($data['sysmenuPwd']);
            $checksysmenu= "SELECT * FROM `savor_syssysmenu` where `sysmenuname` = '{$sysmenuName}' and `password` = '{$sysmenuPwd}' and `status` = 1";
            $result   = $this->query($checksysmenu);
            return $result;
        } else {
            return 0;
        }
    }
    public function getMysqlVersion(){
        $sql = "select VERSION() as version";
        $res_version = $this->query($sql);
        $version = !empty($res_version[0]['version'])?$res_version[0]['version']:'5.1';
        return $version;
    }

    public function getCount($where){
        $numbe = $this->where($where)->count();
        return $numbe;
    }
}