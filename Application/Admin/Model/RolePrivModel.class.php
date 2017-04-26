<?php
namespace Admin\Model;
use Think\Model;
use Common\Lib\Page;
class RolePrivModel extends Model{
    protected $trueTableName='savor_role_priv';
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

    public function getWhere($where, $field){
        $list = $this->where($where)->field($field)->select();
        return $list;
    }
    
    public function getInfoByroleid($code) {
        if($code){
            $getInfoSql  = "SELECT * FROM `savor_role_priv` WHERE roleid = '{$code}'";
            $result = $this->query($getInfoSql);
        }else{
            $result = array();
        }

        return $result;
    }
    
    //查找当前所有的节点
    public function getAllList() {
        $data  = array();
        $getListSql  = "SELECT * FROM `savor_role_priv`";
        $listData    = $this->query($getListSql);
        $data        = !empty($listData)? $listData : $data;
        return $data;
    }

    /**
     *  检查指定菜单是否有权限
     * @param array $data menu表中数组
     * @param int $roleid 需要检查的角色ID
     */
    public function is_checked($data,$roleid,$priv_data) {
        $priv_arr = array('m','c','a','menulevel','nodeid');
        if($data['m'] == '') return false;
        foreach($data as $key=>$value){
            if($key == 'id'){
                $data['nodeid'] = $value;
            }
            if(!in_array($key,$priv_arr)) unset($data[$key]);
        }
        $data['roleid'] = $roleid;
        $info = in_array($data, $priv_data);
        if($info){
            return true;
        } else {
            return false;
        }

    }


    /**
     * 获取菜单深度
     * @param $id
     * @param $array
     * @param $i
     */
    public function get_level($id,$array=array(),$i=0) {
        foreach($array as $n=>$value){
            if($value['id'] == $id)
            {
                if($value['parentid']== '0') return $i;
                $i++;
                return $this->get_level($value['parentid'],$array,$i);
            }
        }
    }

    public function get_menuinfo($menuid,$menu_info) {
        $menuid = intval($menuid);
        unset($menu_info[$menuid][id]);
        return $menu_info[$menuid];
    }
    
    //删除数据
    public function delData($id) {
        $delSql = "DELETE FROM `savor_role_priv` WHERE roleid = '{$id}'";
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
    public function getMenuList($where,$order){
        $sql ="select n.id,n.name,rp.m,rp.a,rp.c,n.menulevel,n.media_id,n.select_media_id from savor_role_priv as rp
               left join savor_nodemenu as n on rp.nodeid=n.id 
               where 1 ".$where.' order by '.$order;
        $result = $this->query($sql);
        return $result;
    }
    /**
     * @desc 获取角色的三级节点权限
     */
    public function getPrivByGroupId($groupid){
        $where['menulevel'] = 2;
        $where['roleid'] = $groupid;
        $result = $this->where($where)->select();
        return $result;
    }
}