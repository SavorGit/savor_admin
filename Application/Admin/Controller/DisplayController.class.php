<?php
/**
 * Created by PhpStorm.
 * User: baiyutao
 * Date: 2017/7/11
 * Time: 10:23
 */

namespace Admin\Controller;

use Think\Controller;

class DisplayController extends Controller
{
    public function geteggAwardRecord(){
        $m_award_log = new \Admin\Model\AwardLogModel();
        $deviceid = $_GET['deviceid'];
        $where = "1=1 and  a.prizeid >0 and a.deviceid = '".$deviceid."'";
        $orders = 'a.time  desc';
        $result = $m_award_log->getAwardList($where,$orders);
        $this->assign('list', $result);
        $this->display('record_of_winning_a_prize');
    }
}