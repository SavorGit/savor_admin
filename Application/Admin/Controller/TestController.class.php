<?php
namespace Admin\Controller;

use Think\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Common\Lib\SavorRedis;


// use Common\Lib\SavorRedis;
/**
 * @desc 功能测试类
 *
 */
class TestController extends Controller {
    
    public function recCollectCount(){
        $m_public = new \Admin\Model\Smallapp\PublicModel();
        $m_collect_count = new \Admin\Model\Smallapp\CollectCountModel();
        
        $fields = "forscreen_id";
        $where['status'] = 2;
        $limit  = '0,1000';
        $list = $m_public->getWhere($fields, $where,'', $limit);
        
        foreach($list as $key=>$v){
            $where = array();
            $where['res_id'] = $v['forscreen_id'];
            $nums = $m_collect_count->countNum($where);
            if($nums){
                $rand_nums = rand(1, 10);
                $m_collect_count->where($where)->setInc('nums',$rand_nums);
            }else {
                $data = array();
                $data['res_id'] = $v['forscreen_id'];
                $data['type']   = 2;
                $data['nums']   = 1;
                $m_collect_count->addInfo($data);
            }
        }
        echo 'ok';
    }
    public function test(){
        exit('非法进入');
        /* $is_support_netty  = $_GET['is_support_netty'];
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $redis->set('support_netty_balance',$is_support_netty); */
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $m_smallapp_forscreen_record = new \Admin\Model\ForscreenRecordModel();
        
        $cache_key = C('SAPP_BOX_FORSCREEN_NET')."*";
        $keys = $redis->keys($cache_key);
        $flag = 0;
        foreach($keys as $k){
            
            $data = $redis->lgetrange($k, 0, -1);
            
            foreach($data as $v){
                $flag ++;
                $netresource = json_decode($v,true);
                
                $search = array();
                $search['forscreen_id'] = $netresource['forscreen_id'];
                $search['resource_id']  = $netresource['resource_id'];
                $tmp = $m_smallapp_forscreen_record->getOne('id', $search);
                
                if(!empty($tmp)){
                    if($netresource['is_exist']==0){//资源不存在
                        if(!empty($netresource['resource_id']) && !empty($netresource['openid'])){
                            $where = array();
                            $dt = array();
                            //$where['action'] = array('neq',8);
                            $where['forscreen_id'] = $netresource['forscreen_id'];
                            $where['resource_id'] = $netresource['resource_id'];
                            $where['openid'] = $netresource['openid'];
                            if(!empty($netresource['box_res_sdown_time'])){
                                $dt['box_res_sdown_time'] = $netresource['box_res_sdown_time'];
                            }
                            if(!empty($netresource['box_res_edown_time'])){
                                $dt['box_res_edown_time'] = $netresource['box_res_edown_time'];
                            }
                            $dt['is_break'] = $netresource['is_break'];
                            $dt['is_exist'] = $netresource['is_exist'];
                            $dt['update_time'] = date('Y-m-d H:i:s');
                            $ret = $m_smallapp_forscreen_record->updateInfo($where, $dt);
                            $redis->lpop($k);
                        }
                    }else if($netresource['is_exist']==1 || $netresource['is_exist']==2){//资源存在 //资源下载失败
                        $where = $dt = array();
                        //$where['action'] = array('neq',8);
                        $where['forscreen_id'] = $netresource['forscreen_id'];
                        $where['resource_id'] = $netresource['resource_id'];
                        $where['openid'] = $netresource['openid'];
                        $dt['is_break'] = $netresource['is_break'];
                        $dt['is_exist'] = $netresource['is_exist'];
                        $dt['update_time'] = date('Y-m-d H:i:s');
                        $ret = $m_smallapp_forscreen_record->updateInfo($where, $dt);
                        
                        $tt = $redis->lpop($k);
                    }
                }else {
                    $redis->lpop($k);
                }
                $ret = $redis->lgetrange($k,0,-1);
                if(empty($ret)) $redis->remove($k);
            }
        }
        echo $flag ."ddd";
    }
    
    
    
   
    //生成好友关系
    public function smallappFriends(){
        exit('非法进入');
        $hour = date('H');
        //$hour =14;
        if($hour==14){
            $start_time = date('Y-m-d')." 11:00:00";
            $end_time   = date('Y-m-d')." 14:00:00";
        }else{
            $start_time = date('Y-m-d')." 17:00:00";
            $end_time   = date('Y-m-d')." 23:00:00";
        }
        $sql = "select box_mac from savor_smallapp_forscreen_record where create_time>='".$start_time."'
                and create_time<='".$end_time."' and mobile_brand !='devtools' group by box_mac";
        $forscreen_box_arr = M()->query($sql);
        $sql ="select box_mac from savor_smallapp_turntable_log where create_time>='".$start_time."'
                and create_time<='".$end_time."' group by box_mac";
        $turntable_box_arr = M()->query($sql);
        $box_list = array_merge($forscreen_box_arr,$turntable_box_arr);
        $ret = assoc_unique($box_list, 'box_mac');
        $box_list = array_keys($ret);
        $m_user = new \Admin\Model\Smallapp\UserModel();
        foreach($box_list as $v){
            $sql ="select openid from savor_smallapp_forscreen_record where box_mac='".$v."' 
                   and  create_time>='".$start_time."'
                   and create_time<='".$end_time."' and mobile_brand !='devtools' 
                   group by openid";
            //echo $sql;exit;
            $forscreen_openid_arr = M()->query($sql);  //投屏用户
            $sql ="select openid from savor_smallapp_turntable_log where box_mac='".$v."'
                   and  create_time>='".$start_time."'
                   and create_time<='".$end_time."' group by openid";
            $turntab_openid_arr = M()->query($sql);
            $sql ="select a.openid from savor_smallapp_turntable_detail a 
                   left join savor_smallapp_turntable_log b on a.activity_id = b.id
                   where b.box_mac='".$v."'
                   and  a.create_time>='".$start_time."'and a.create_time<='".$end_time."'
                   group by openid";
            $turntab_detail_openid_arr = M()->query($sql);
            $openid_list = array_merge($forscreen_openid_arr,$turntab_openid_arr,$turntab_detail_openid_arr);
            
            $nums = count($openid_list);
            if($nums<=1) continue;
            $openid_list = assoc_unique($openid_list, 'openid');
            $openid_list = array_keys($openid_list);
            if(count($openid_list)<=1) continue;
            $m_friend = new \Admin\Model\Smallapp\FriendModel();
            $f_arr = array();
            $flag=0;
            foreach($openid_list as $ov){
                $sql ="select count(id) as nums from savor_smallapp_user where openid='".$ov."' limit 1";
                $ret = M()->query($sql);
                $user_nums = $ret[0]['nums'];
                if(empty($user_nums)){
                    $sql =" insert into savor_smallapp_user(`openid`) values('".$ov."')";
                    M()->execute($sql);
                }
                foreach($openid_list as $fv){
                    if($ov!=$fv){
                        $sql ="select status from savor_smallapp_friend 
                               where openid='".$ov."' and f_openid='".$fv."'";
                        $ret = M()->query($sql);
                        if(empty($ret)){
                            
                            $f_arr[$flag]['openid'] = $ov;
                            $f_arr[$flag]['f_openid'] = $fv;
                            $f_arr[$flag]['type'] = 1;
                            $f_arr[$flag]['status'] = 1;
                            $flag++;
                            
                        }else {
                            if($ret[0]['status'] ==0){
                                $where = array();
                                $where['openid'] = $ov;
                                $where['f_openid']= $fv;
                                $m_friend->updateInfo($where, array('status'=>1));
                            }
                        }
                    }
                }
            }
            $m_friend->addInfo($f_arr,2);
        }
        echo "ok";
    }
    
}