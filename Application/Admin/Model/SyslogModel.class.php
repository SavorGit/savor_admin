<?php
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;
class syslogModel extends Model{
    protected $tableName='syslog';
    public function getList($where, $order, $start=0,$size=5){
        $data = array();
        $totalSql  = "SELECT COUNT(*) as num FROM `savor_syslog` {$where} order by id desc";
        $totalRows = $this->query($totalSql);
        $totalRows = !empty($totalRows)?$totalRows[0]['num']:0;
        $pageShow = new Page($totalRows,$size);
        $show = $pageShow->admin_page();//分页显示输出
        //进行分页数据查询 使用page类属性
        $getSql = "select * from `savor_syslog` {$where} order by {$order} limit $start,$size";
        $list = $this->query($getSql);
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
    
    //新增和修改
    public function addData($data, $acttype) {
        if(0 === $acttype) {
                $result = $this->add($data);
        } else {
            $id = $data['id'];
            $result = $this->where("id={$id}")->save($data);
        }
        return $result;
    }
    
    //查找其中的一条
    public function getInfo($id) {
        $data  = array();
        $getInfoSql  = "SELECT * FROM `savor_syslog` WHERE id = '{$id}'";
        $InfoData    = $this->query($getInfoSql);
        $data        = !empty($InfoData)? $InfoData[0] : $data;
        
        return $data;
    }
    
    //删除数据
    public function delData($id) {
        $delSql = "DELETE FROM `savor_syslog` WHERE id = '{$id}'";
        $result = $this -> execute($delSql);
        return  $result;
    }
    
}