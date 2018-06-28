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
}