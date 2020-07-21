<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;

class UploadtimesModel extends BaseModel{
	protected $tableName='smallapp_uploadtimes';

    public function addUploadtimes($data){
        if($data['res_sup_time'] && $data['res_eup_time']){
            /*
            $m_invalid = new \Admin\Model\ForscreenInvalidlistModel();
            $orderby = 'id desc';
            $res_list = $m_invalid->getDataList('*',array('type'=>array('in',array(1,2,3))),$orderby);
            $all_invalidlist = array();
            foreach ($res_list as $v){
                $all_invalidlist[$v['type']][] = $v['invalidid'];
            }
            $boxs = array();
            if(isset($all_invalidlist[1]) && !empty($all_invalidlist[1])){
                $hotel_ids = $all_invalidlist[1];
                $fields = "box.mac as box_mac";
                $where = array();
                $where['hotel.id'] = array('in',$hotel_ids);
                $m_box = new \Admin\Model\BoxModel();
                $hotel_boxs = $m_box->getBoxByCondition($fields,$where);
                foreach ($hotel_boxs as $v){
                    $boxs[]=$v['box_mac'];
                }
            }
            if(isset($all_invalidlist[3]) && !empty($all_invalidlist[3])){
                $boxs = array_merge($boxs,$all_invalidlist[3]);
                $boxs = array_unique($boxs);
            }
            $openids = array();
            if(isset($all_invalidlist[2]) && !empty($all_invalidlist[2])){
                $openids = $all_invalidlist[2];
            }
            if(in_array($data['openid'],$openids) || in_array($data['box_mac'],$boxs)){
                return true;
            }
            */
            if(empty($data['create_time'])){
                $data['create_time'] = date('Y-m-d H:i:s',$data['res_eup_time']);
            }
            $data_time = array('openid'=>$data['openid'],'box_mac'=>$data['box_mac'],'resource_size'=>$data['resource_size'],
                'res_sup_time'=>$data['res_sup_time'],'res_eup_time'=>$data['res_eup_time'],'up_time'=>$data['create_time'],
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
}