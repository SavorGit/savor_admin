<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class UploadtimesModel extends BaseModel{
	protected $tableName='smallapp_uploadtimes';

    public function addUploadtimes($data){
        if($data['res_sup_time'] && $data['res_eup_time']){
            if(!empty($data['create_time'])){
                $up_time = $data['create_time'];
            }else{
                $up_time = date('Y-m-d H:i:s',intval($data['res_eup_time']/1000));
            }
            $data_time = array('openid'=>$data['openid'],'box_mac'=>$data['box_mac'],'resource_size'=>$data['resource_size'],
                'res_sup_time'=>$data['res_sup_time'],'res_eup_time'=>$data['res_eup_time'],'up_time'=>$up_time,
            );
            if(!empty($data['box_mac'])){
                $m_box = new \Admin\Model\BoxModel();
                $res_box = $m_box->getHotelInfoByBoxMac($data['box_mac']);
                if(!empty($res_box)){
                    $data_time['hotel_id'] = $res_box['hotel_id'];
                    $data_time['room_id'] = $res_box['room_id'];
                    $data_time['room_name'] = $res_box['room_name'];
                    $data_time['box_id'] = $res_box['box_id'];
                }
            }
            $this->add($data_time);
        }

        return true;
    }

    public function handel_smallapp_upload(){
        $daytime = strtotime("-1 day");
        $start_time = date('Y-m-d 00:00:00',$daytime);
        $end_time   = date('Y-m-d 23:59:59',$daytime);
        $where = "create_time>='{$start_time}' and create_time<='{$end_time}' and small_app_id=1 and mobile_brand!='devtools' and res_sup_time>0 and res_sup_time>0 and resource_size>0";
        $sql = "select openid,box_mac,resource_size,res_sup_time,res_eup_time,create_time from savor_smallapp_forscreen_record where {$where} order by id asc";
        $res = $this->query($sql);
        if(!empty($res)){
            foreach ($res as $v){
                $this->addUploadtimes($v);
                echo "openid:{$v['openid']}-box_mac:{$v['box_mac']}-time:{$v['create_time']}-status:ok \r\n";
            }
        }
    }
}