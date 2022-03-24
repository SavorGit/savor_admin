<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class PrizepoolprizeModel extends BaseModel{
	protected $tableName='smallapp_prizepool_prize';

    public function getHotelpoolprizeList($fields,$where,$order){
        $datas = $this->alias('a')
            ->join('savor_smallapp_prizepool p on a.prizepool_id=p.id', 'left')
            ->join('savor_smallapp_hotel_prizepool hp on a.prizepool_id=hp.prizepool_id', 'left')
            ->join('savor_hotel h on hp.hotel_id=h.id', 'left')
            ->field($fields)
            ->where($where)
            ->order($order)
            ->select();
        return $datas;
    }

    public function handle_notclaime_prize(){
        $res_data = $this->getDataList('*',array('status'=>1),'id asc');
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(1);
        $key_pool = C('SAPP_PRIZEPOOL');
        foreach ($res_data as $v){
            $prizepool_prize_id = $v['id'];
            $lucky_pool_key = $key_pool.$prizepool_prize_id;
            $res_pool = $redis->get($lucky_pool_key);
            if(!empty($res_pool)){
                $now_pools = array();
                $pools = json_decode($res_pool,true);
                foreach ($pools as $pk=>$pv){
                    if($pv==2){
                        $now_pools[$pk]=$pv;
                    }
                }
                $now_num = count($now_pools);
                if($v['send_amount']!=$now_num){
                    $this->updateData(array('id'=>$prizepool_prize_id),array('send_amount'=>$now_num));
                }
                $redis->set($lucky_pool_key,json_encode($now_pools));
                echo "prizepool_prize_id:$prizepool_prize_id now_amount:$now_num,send_amount:{$v['send_amount']} \r\n";
            }
        }

    }
}