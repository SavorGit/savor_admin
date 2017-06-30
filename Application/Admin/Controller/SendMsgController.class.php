<?php
namespace Admin\Controller;

use Think\Controller;
use Common\Lib\SavorRedis;
use Common\Lib\Ucpaas;
// use Common\Lib\SavorRedis;
/**
 * @desc 功能测试类
 *
 */
class SendmsgController extends Controller
{

    /*
* 执行脚本文件定时发送短信
*/
    public function sendToSeller(){

        //http://www.a.com/index.php/checkaccount/sendToSeller
        //http://devp.admin.rerdian.com/index.php/sendmsg/sendToSeller
        $redis  =  SavorRedis::getInstance();
        $redis->select(15);
        $rkey = 'savor_account_statement_notice';
        $roll_back_arr = array();
        $suca = array();
        $count = 0;
        $maxcount = 8;
        $max = $redis->lsize($rkey);
        $data = $redis->lgetrange($rkey,0,$max);
        var_dump($data);
        $statedetailModel = new \Admin\Model\AccountStatementDetailModel();
        $statenoticeModel = new \Admin\Model\AccountStatementNoticeModel();
        $me_su_arr = array();
        $me_fail_arr = array();
        if(empty($data)){
            echo '数组为空'."\n";
            die;
        }
        //http://www.a.com/showMesLink/1be79d87c7f8360f
        foreach ($data as $val){
            //获取短信发送最大值数量
            $field = 'count,id noticeid, f_type ftype';
            $dat['detail_id'] = $val;
            $notice_arr = $statenoticeModel->getWhere($dat, $field);
            if($notice_arr){
                $count = $notice_arr['count'];
                $noticeid = $notice_arr['noticeid'];
                $redis->lPop($rkey);
                if ($count >= 8 ) {
                    continue;
                } else {
                    //发送短信
                    $info = $statedetailModel->getWhereSql($val);
                    $m_state = $this->sendMessage($info);
                    var_dump($m_state);
                    if($m_state){
                        $me_su_arr[] = $noticeid;
                        continue;
                    }else{
                        $roll_back_arr[] = $val;
                        $me_fail_arr[] = $noticeid;
                        continue;
                    }

                }
            }else{
                echo '出错ID:'.$val.'<br/>';
                $redis->lPop($rkey);
            }

        }
        if($roll_back_arr){
            //更新count字段
            foreach($roll_back_arr as $k){
                $redis->rPush($rkey, $k);
            }
        }
        if($me_su_arr){
            $me_su_str = 'values';
            $where = 'status = 1';
            foreach($me_su_arr as $ma){
                $me_su_str .=  ' ('.$ma.')'.',';
            }
            $me_su_str = substr($me_su_str,0,-1);
            $statenoticeModel->insertDup($me_su_str, $where);
        }
        if($me_fail_arr){
            $me_fail_str = 'values';
            $where = '`count` = `count` + 1';
            foreach($me_fail_arr as $ma){
                $me_fail_str .=  ' ('.$ma.')'.',';
            }
            $me_fail_str = substr($me_fail_str,0,-1);
            $statenoticeModel->insertDup($me_fail_str, $where);
        }
    }



    private function sendMessage($info){
        //$sjson  = '{"resp":{"respCode":"000000","templateSMS":{"createDate":"20170621131304","smsId":"3bcd56624d1d60a6e5830c3886f2f31d"}}}';
        $fe_start = $info['fee_start'];
        $fe_end = $info['fee_end'];
        $tel= $info['tel'];
        $detailid = $info['id'];
        $to = $tel;
        $short = encrypt_data($detailid);
        $shortlink = C('HOST_NAME').'/admin/hotelbill/index?id='.$short;
        $shortlink = shortUrlAPI(1, $shortlink);
        echo $shortlink;
        $param="$shortlink";
        $bool = $this->sendToUcPa($tel,$param);
        return $bool;
    }


    private function sendToUcPa($to,$param,$type=1){
        $bool = true;
        $ucconfig = C('SMS_CONFIG');
        $options['accountsid'] = $ucconfig['accountsid'];
        $options['token'] = $ucconfig['token'];
        //确认付款通知
        if($type == 2){
            $templateId = $ucconfig['payment_templateid'];
        }else{
            $templateId = $ucconfig['bill_templateid'];
        }
        $ucpass= new Ucpaas($options);
        $appId = $ucconfig['appid'];
        $sjson = $ucpass->templateSMS($appId,$to,$templateId,$param);
        $this->addAccountLog($sjson,$param,$to);
        $sjson = json_decode($sjson,true);
        $code = $sjson['resp']['respCode'];

        if($code === '000000') {
        }else{
            $bool = false;
        }
        return $bool;

    }
}