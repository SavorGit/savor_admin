<?php
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;
class UploadmgrModel extends Model{
    protected $tableName='uploadmgr';
    public function getList($where, $start=0,$size=5){
        $data = array();
        $totalSql  = "SELECT COUNT(*) as num FROM `savor_uploadmgr` {$where} order by id desc";
        $totalRows = $this->query($totalSql);
        $totalRows = !empty($totalRows)?$totalRows[0]['num']:0;
        $pageShow = new Page($totalRows,$size);
        $show = $pageShow->admin_page();//分页显示输出
        //进行分页数据查询 使用page类属性
        $getSql = "select * from `savor_uploadmgr` {$where} order by id desc limit $start,$size";
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
    public function getInfo($id) {
        $data  = array();
        $getInfoSql  = "SELECT * FROM `savor_uploadmgr` WHERE id = '{$id}'";
        $InfoData    = $this->query($getInfoSql);
        $data        = !empty($InfoData)? $InfoData[0] : $data;
        
        return $data;
    }
    public function groupInfo($a) {
        $data  = array();
        $getInfoSql  = "SELECT `{$a}` FROM `savor_uploadmgr` GROUP BY `{$a}`";
        $InfoData    = $this->query($getInfoSql);
        $data        = !empty($InfoData)? $InfoData[0] : $data;
        
        return $data;
    }
    //删除数据
    public function delData($id) {
        $delSql = "DELETE FROM `savor_uploadmgr` WHERE id = '{$id}'";
        $result = $this -> execute($delSql);
        return  $result;
    }
}