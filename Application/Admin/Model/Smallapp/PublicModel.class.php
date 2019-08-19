<?php
/**
 * @desc   小程序用户公开资源
 * @author zhang.yingtao
 *
 */
namespace Admin\Model\Smallapp;
use Think\Model;
use Common\Lib\Page;
class PublicModel extends Model
{
	protected $tableName='smallapp_public';
	public function addInfo($data,$type=1){
	    if($type==1){
	        $ret = $this->add($data);
	         
	    }else {
	        $ret = $this->addAll($data);
	    }
	    return $ret;
	}
	public function updateInfo($where,$data){
	    $ret = $this->where($where)->save($data);
	    return $ret;
	}
	public function getWhere($fields,$where,$order,$limit,$group){
	    $data = $this->field($fields)->where($where)->order($order)->group($group)->limit($limit)->select();
	    return $data;
	}
	public function getOne($fields,$where,$order){
	    $data =  $this->field($fields)->where($where)->order($order)->find();
	    return $data;
	}
	public function countNum($where){
	    $nums = $this->where($where)->count();
	    return $nums;
	}
	public function getList($fields="a.id",$where, $order='a.id desc', $start=0,$size=5){
	    $list = $this->alias('a')
	                 ->join('savor_smallapp_user user on a.openid= user.openid','left')
            	     ->field($fields)
            	     ->where($where)
            	     ->order($order)
            	     ->limit($start,$size)
            	     ->select();
	    $count = $this->alias('a')
	                  ->where($where)->count();
	    $objPage = new Page($count,$size);
	    $show = $objPage->admin_page();
	    $data = array('list'=>$list,'page'=>$show);
	    return $data;
	}

	public function cronforscreenPublicnums(){
        $fields = 'id,forscreen_id';
        $where = array('res_nums'=>0);
	    $res_list = $this->field($fields)->where($where)->select();
	    if(!empty($res_list)){
	        $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
            foreach ($res_list as $v){
                $field = 'count(id) as num';
                $res_forscreen_num = $m_forscreen->getOne($field,array('forscreen_id'=>$v['forscreen_id']));
                if(!empty($res_forscreen_num)){
                    $res_nums = $res_forscreen_num['num'];
                    $res = $this->updateInfo(array('id'=>$v['id']),array('res_nums'=>$res_nums));
                    if($res){
                        echo "id: {$v['id']} ok \r\n";
                    }else{
                        echo "id: {$v['id']} error \r\n";
                    }
                }
            }
        }else{
	        echo "no data \r\n";
        }
    }
}