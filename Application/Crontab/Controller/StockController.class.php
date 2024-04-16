<?php
namespace Crontab\Controller;
use Think\Controller;

class StockController extends Controller{

    public function expirerecycle(){
        $diff_time = time() - 86400*60;
        $where = array('type'=>7,'wo_status'=>2,'recycle_status'=>array('in','1,4'));
        $where['UNIX_TIMESTAMP(add_time)'] = array('elt',$diff_time);
        $m_stock_record = new \Admin\Model\FinanceStockRecordModel();
        $m_stock_record->updateData($where,array('recycle_status'=>7));

        $now_time = date('Y-m-d H:i:s');
        echo "expirerecycle time:$now_time \r\n";
    }
}