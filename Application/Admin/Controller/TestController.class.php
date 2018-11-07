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
    
    
    
    public function importBoxLog(){
        exit('not come');
        $password = I('password');
        if(empty($password) || $password!='fklj'){
            exit('你的非法行为已被记录');
        }
        $m_oss_box_log = new \Admin\Model\Oss\BoxLogModel();
        
        $yesterday_start = date('Y-m-d 00:00:00',strtotime('-1 day'));
        $yesterday_end   = date('Y-m-d 23:59:59',strtotime('-1 day'));
        $fields = "*";
        $where = array();
        $where['create_time'] =array(array('EGT',$yesterday_start),array('ELT',$yesterday_end));
        $where['flag']        = array('in','16,18');
        $data = $m_oss_box_log->getInfo($fields, $where,'id asc');
        $result = array();
        $m_oss_box_log_detail = new \Admin\Model\Oss\BoxLogDetailModel();
        if(!empty($data)){
            $flag = 0;
            foreach($data as $key=>$v){
                $oss_key = $v['oss_key'];
                if(!empty($oss_key)){
                    $oss_key_arr = explode('/', $oss_key);
                    $v['log_create_date'] = $oss_key_arr[3];
                }
                $v['box_log_id'] = $v['id'];
                unset($v['id']);
                $result[$flag] = $v;
                $flag ++;
                if($flag%100==0){
                    //添加到数据库
                    $ret = $m_oss_box_log_detail->addAll($result);
                    //置空添加到数据库的数组
                    $result = array();
                    $flag = 0;
                }  
            }
            if(!empty($result)){
                //添加到数据库
                $ret = $m_oss_box_log_detail->addAll($result);
            } 
        }
        echo "昨天日志数据导入成功";
    }
    //生成好友关系
    public function smallappFriends(){
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
    /**
     * @desc 处理小程序公开投屏资源
     */
    public function recForscreenPub(){
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $cache_key = C('SAPP_SCRREN_SHARE')."*";
        $keys = $redis->keys($cache_key);
        $m_pub = new \Admin\Model\Smallapp\PublicModel(); 
        $m_pubdetail = new \Admin\Model\Smallapp\PubdetailModel();
        foreach($keys as $k){
            $data = $redis->lgetrange($k,0,-1);
            $infos = json_decode($data[0],true);
            $k_arr = explode(':', $k);
            $map = array();
            $map['box_mac'] = $k_arr[3];
            $map['openid']  = $k_arr[4];
            $map['forscreen_id'] = $k_arr[5];
            $map['res_type'] = $infos['res_type'];
            $map['res_nums'] = count($data);
            $map['status']   = 1;
            $m_pub->addInfo($map,1);
            $ret = array();
            foreach($data as $kk=>$vv){
                $vv = json_decode($vv,true);
                $ret[$kk]['forscreen_id'] = $vv['forscreen_id'];
                $ret[$kk]['resource_id']  = $vv['resource_id'];
                $ret[$kk]['res_url']      = $vv['res_url'];
            }
            $m_pubdetail->addInfo($ret,2);
            $redis->remove($k);
        }
        echo "ok";
    
    }
    public function removeHistoryForscreen(){
        $redis = SavorRedis::getInstance();
        $redis->select(5);
        $cache_key = C('SAPP_HISTORY_SCREEN')."*";
        $keys = $redis->keys($cache_key);
        foreach($keys as $v){
            $redis->remove($v);
        }
        echo "ok";
    }
}