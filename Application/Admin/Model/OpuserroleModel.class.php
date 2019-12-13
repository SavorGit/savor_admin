<?php
namespace Admin\Model;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class OpuserroleModel extends BaseModel
{
    protected $tableName='opuser_role';
    public function addInfo($data){
        $ret = $this->add($data);
        return $ret;
    }
    public function getList($fields,$where,$order,$limit){
        $data = $this->field($fields)->where($where)->order($order)->limit($limit)->select();
        return $data;
    }
    public function getPageList($fields,$where, $order='id desc', $start=0,$size=5){
        $list = $this->alias('a')
        ->join('savor_sysuser b on a.user_id=b.id','left')
        ->field($fields)
        ->where($where)
        ->order($order)
        ->limit($start,$size)
        ->select();
        $count = $this->alias('a')
                      ->join('savor_sysuser b on a.user_id=b.id','left')
                      ->where($where)
                      ->count();
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }
    public function getInfo($fields,$where){
        $data = $this->alias('a')
                     ->join('savor_sysuser user on a.user_id=user.id')
                     ->field($fields)
                     ->where($where)
                     ->find();
        return $data;
    }
    public function saveInfo($where,$data){
        $ret = $this->where($where)->save($data);
        return $ret;
    }

    public function getAllRole($fields,$where,$order,$limit){
        $data = $this->alias('a')
            ->join('savor_sysuser as user on user.id=a.user_id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit()
            ->select();
        return $data;
    }


    public function getRelaOpHotel($fields,$where,$order,$limit){
        $data = $this->alias('a')
            ->join('savor_hotel_ext as sext on sext.maintainer_id=a.user_id','left')
            ->join('savor_hotel as sht on sht.id=sext.hotel_id','left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->limit()
            ->count();

        return $data;
    }

    public function getOpuser($maintainer_id=0){
        $fields = 'a.user_id uid,user.remark ';
        $where = array('state'=>1,'role_id'=>1);
        $res_users = $this->getAllRole($fields,$where,'' );

        $opusers = array();
        foreach($res_users as $v){
            $uid = $v['uid'];
            $remark = $v['remark'];
            if($uid==$maintainer_id){
                $select = 'selected';
            }else{
                $select = '';
            }
            $firstCharter = getFirstCharter(cut_str($remark, 1));
            $opusers[$firstCharter][] = array('uid'=>$uid,'remark'=>$remark,'select'=>$select);
        }
        ksort($opusers);
        return $opusers;
    }

}