<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class MessageModel extends BaseModel{
	protected $tableName='smallapp_message';

    /*
     * $type 类型1赞(喜欢内容),2内容审核,3优质内容,4领取红包,5购买订单,6发货订单
     */
    public function recordMessage($openid,$content_id,$type,$status=0,$hotel_nums=0){
        switch ($type){
            case 1:
                $where = array('openid'=>$openid,'content_id'=>$content_id,'type'=>$type);
                $res_data = $this->getDataList('*',$where,'id desc');
                if(!empty($res_data)){
                    if($status==0){
                        $this->delData(array('id'=>$res_data[0]['id']));
                    }
                }else{
                    if($status){
                        $data = $where;
                        $data['read_status'] = 1;
                        $this->add($data);
                    }
                }
                break;
            case 2:
                $where = array('openid'=>$openid,'content_id'=>$content_id,'type'=>$type);
                $res_data = $this->getDataList('*',$where,'id desc');
                if(!empty($res_data)){
                    $data = $where;
                    $data['audit_status'] = $status;
                    $data['read_status'] = 1;
                    $this->updateData(array('id'=>$res_data[0]['id']),$data);
                }else{
                    $data = $where;
                    $data['audit_status'] = $status;
                    $data['read_status'] = 1;
                    $this->add($data);
                }
                break;
            case 3:
                $where = array('openid'=>$openid,'content_id'=>$content_id,'type'=>$type);
                $res_data = $this->getDataList('*',$where,'id desc');
                if(!empty($res_data)){
                    $data = $where;
                    $data['good_status'] = $status;
                    $data['hotel_num'] = $hotel_nums;
                    $data['read_status'] = 1;
                    $this->updateData(array('id'=>$res_data[0]['id']),$data);
                }else{
                    $data = $where;
                    $data['good_status'] = $status;
                    $data['hotel_num'] = $hotel_nums;
                    $data['read_status'] = 1;
                    $this->add($data);
                }
                break;
            case 4:
            case 5:
            case 6:
                $data = array('openid'=>$openid,'content_id'=>$content_id,'type'=>$type,'read_status'=>1);
                $this->add($data);
                break;
        }

        return true;
    }

    public function stockCheckErrorStat(){
        $sql = "select a.ops_staff_id,GROUP_CONCAT( DISTINCT sc.hotel_id) as hotel_ids from savor_smallapp_message as a left join savor_smallapp_stockcheck as sc on a.content_id=sc.id  where a.type=13
            and sc.is_handle_stock_check=0 and sc.stock_check_success_status in (22,23,24) group by a.ops_staff_id";
        $res_data = $this->query($sql);
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(22);
        $cache_key = C('SAPP_OPS').'msgschotels';
        foreach ($res_data as $v){
            if(!empty($v['hotel_ids'])){
                $redis->set($cache_key.":{$v['ops_staff_id']}",$v['hotel_ids'],86000);
                echo "ops_staff_id:{$v['ops_staff_id']} ok \r\n";
            }
        }
    }
}