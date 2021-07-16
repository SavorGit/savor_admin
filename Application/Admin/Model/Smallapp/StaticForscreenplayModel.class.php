<?php

namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class StaticForscreenplayModel extends BaseModel{

	protected $tableName='smallapp_static_forscreenplay';

    public function getForscreenplayDataList($fields,$where,$orderby,$group,$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->field($fields)->where($where)->order($orderby)->group($group)->limit($start,$size)->select();
            $count = $this->where($where)->group($group)->count();
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->field($fields)->where($where)->order($orderby)->group($group)->select();
        }
        return $data;
    }
	public function handle_forscreenplay_data(){
        $start_time = date('Y-m-d 00:00:00',strtotime('-1day'));
        $end_time = date('Y-m-d 23:59:59',strtotime('-1day'));

        $time_condition = array(array('EGT',$start_time),array('ELT',$end_time));
        $static_date = date('Y-m-d',strtotime('-1day'));
        $common_where = array('create_time'=>$time_condition,'small_app_id'=>array('in',array(1,2)),'mobile_brand'=>array('neq','devtools'));
        //视频：(视频投屏、切片视频投屏,重投)
        $where = $common_where;
        $where['action'] = array('in',array(2,3,8));
        $where['resource_type'] = 2;
        $order = 'id desc';
        $group = 'hotel_id';
        $field = 'area_id,area_name,hotel_id,hotel_name,hotel_box_type,hotel_is_4g,count(id) as num,sum(duration) as total_duration';
        $m_forscreen_record = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $res_record = $m_forscreen_record->getAll($field,$where,0,10000,$order,$group);
        foreach ($res_record as $v){
            $hotel_id = $v['hotel_id'];
            $where['hotel_id'] = $hotel_id;
            $where['box_play_stime'] = array('gt',0);
            $where['box_play_etime'] = array('gt',0);
            $res_hotel_record = $m_forscreen_record->getAll('*',$where,0,10000,$order);
            $play_num = count($res_hotel_record);
            $full_play_num = 0;
            $play_duration = 0;
            foreach ($res_hotel_record as $rv){
                $play_time = $rv['box_play_etime']-$rv['box_play_stime'];
                if($play_time>0){
                    $play_time_sec = round($play_time/1000, 2);
                    if($play_time_sec>=$rv['duration']){
                        $full_play_num++;
                        $full_duration = $rv['duration']*1000;
                        $play_duration+=$full_duration;
                    }else{
                        $play_duration+=$play_time;
                    }

                }
            }
            $play_duration = round($play_duration/1000, 2);
            $one_duration = round($play_duration/$play_num, 2);
            $add_data = array('area_id'=>$v['area_id'],'area_name'=>$v['area_name'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                'hotel_box_type'=>$v['hotel_box_type'],'hotel_is_4g'=>$v['hotel_is_4g'],'forscreen_num'=>$v['num'],'total_duration'=>$v['total_duration'],
                'play_duration'=>$play_duration,'one_duration'=>$one_duration,'play_num'=>$play_num,'full_play_num'=>$full_play_num,
                'type'=>1,'static_date'=>$static_date
                );
            $this->add($add_data);
        }

        //图片：(滑动、多图投屏、重投)
        $where = $common_where;
        $where['action'] = array('in',array(2,4,8));
        $where['resource_type'] = 1;
        $order = 'id desc';
        $group = 'hotel_id';
        $field = 'area_id,area_name,hotel_id,hotel_name,hotel_box_type,hotel_is_4g,count(id) as num';
        $m_forscreen_record = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $res_record = $m_forscreen_record->getAll($field,$where,0,10000,$order,$group);
        foreach ($res_record as $v){
            $hotel_id = $v['hotel_id'];
            $where['hotel_id'] = $hotel_id;
            $where['box_play_stime'] = array('gt',0);
            $where['box_play_etime'] = array('gt',0);
            $res_hotel_record = $m_forscreen_record->getAll('*',$where,0,10000,$order);
            $play_num = count($res_hotel_record);
            $play_duration = 0;
            foreach ($res_hotel_record as $rv){
                $play_time = $rv['box_play_etime']-$rv['box_play_stime'];
                if($play_time>0){
                    $play_duration+=$play_time;
                }
            }
            $play_duration = round($play_duration/1000, 2);
            $one_duration = round($play_duration/$play_num, 2);
            $add_data = array('area_id'=>$v['area_id'],'area_name'=>$v['area_name'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                'hotel_box_type'=>$v['hotel_box_type'],'hotel_is_4g'=>$v['hotel_is_4g'],'forscreen_num'=>$v['num'],
                'play_duration'=>$play_duration,'one_duration'=>$one_duration,'play_num'=>$play_num,'type'=>2,'static_date'=>$static_date
            );
            $this->add($add_data);
        }

        //文件：（投屏文件）
        $where = $common_where;
        $where['action'] = 30;
        $order = 'id desc';
        $group = 'hotel_id';
        $field = 'area_id,area_name,hotel_id,hotel_name,hotel_box_type,hotel_is_4g,count(id) as num';
        $m_forscreen_record = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $res_record = $m_forscreen_record->getAll($field,$where,0,10000,$order,$group);
        foreach ($res_record as $v){
            $hotel_id = $v['hotel_id'];
            $where['hotel_id'] = $hotel_id;
            $res_hotel_record = $m_forscreen_record->getAll('*',$where,0,10000,$order);
            $play_duration = 0;
            foreach ($res_hotel_record as $rv){
                $file_where = array('forscreen_id'=>$rv['id']);
                $file_where['box_play_stime'] = array('gt',0);
                $file_where['box_play_etime'] = array('gt',0);
                $res_file_record = $m_forscreen_record->getAll('*',$file_where,0,10000,$order);
                foreach ($res_file_record as $fv){
                    $play_time = $fv['box_play_etime']-$fv['box_play_stime'];
                    if($play_time>0){
                        $play_duration+=$play_time;
                    }
                }
            }
            $play_duration = round($play_duration/1000, 2);
            $one_duration = round($play_duration/$v['num'], 2);
            $add_data = array('area_id'=>$v['area_id'],'area_name'=>$v['area_name'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                'hotel_box_type'=>$v['hotel_box_type'],'hotel_is_4g'=>$v['hotel_is_4g'],'forscreen_num'=>$v['num'],
                'play_duration'=>$play_duration,'one_duration'=>$one_duration,'type'=>3,'static_date'=>$static_date
            );
            $this->add($add_data);
        }

        //点播图片：（发现点播图片、热播内容点播图片）
        $where = $common_where;
        $where['action'] = array('in',array(11,16));
        $order = 'id desc';
        $group = 'hotel_id';
        $field = 'area_id,area_name,hotel_id,hotel_name,hotel_box_type,hotel_is_4g,count(id) as num';
        $m_forscreen_record = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $res_record = $m_forscreen_record->getAll($field,$where,0,10000,$order,$group);
        foreach ($res_record as $v){
            $hotel_id = $v['hotel_id'];
            $where['hotel_id'] = $hotel_id;
            $where['box_play_stime'] = array('gt',0);
            $where['box_play_etime'] = array('gt',0);
            $res_hotel_record = $m_forscreen_record->getAll('*',$where,0,10000,$order);
            $play_num = count($res_hotel_record);
            $play_duration = 0;
            foreach ($res_hotel_record as $rv){
                $play_time = $rv['box_play_etime']-$rv['box_play_stime'];
                if($play_time>0){
                    $play_duration+=$play_time;
                }
            }
            $play_duration = round($play_duration/1000, 2);
            $one_duration = round($play_duration/$play_num, 2);
            $add_data = array('area_id'=>$v['area_id'],'area_name'=>$v['area_name'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                'hotel_box_type'=>$v['hotel_box_type'],'hotel_is_4g'=>$v['hotel_is_4g'],'forscreen_num'=>$v['num'],
                'play_duration'=>$play_duration,'one_duration'=>$one_duration,'play_num'=>$play_num,'type'=>4,'static_date'=>$static_date
            );
            $this->add($add_data);
        }

        //点播视频：（视频点播、发现点播视频、点播商城商品、点播本地生活店铺视频、热播内容点播视频）
        $where = $common_where;
        $where['action'] = array('in',array(5,12,13,15,17));
        $order = 'id desc';
        $group = 'hotel_id';
        $field = 'area_id,area_name,hotel_id,hotel_name,hotel_box_type,hotel_is_4g,count(id) as num,sum(duration) as total_duration';
        $m_forscreen_record = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $res_record = $m_forscreen_record->getAll($field,$where,0,10000,$order,$group);
        foreach ($res_record as $v){
            $hotel_id = $v['hotel_id'];
            $where['hotel_id'] = $hotel_id;
            $where['box_play_stime'] = array('gt',0);
            $where['box_play_etime'] = array('gt',0);
            $res_hotel_record = $m_forscreen_record->getAll('*',$where,0,10000,$order);
            $play_num = count($res_hotel_record);
            $full_play_num = 0;
            $play_duration = 0;
            foreach ($res_hotel_record as $rv){
                $play_time = $rv['box_play_etime']-$rv['box_play_stime'];
                if($play_time>0){
                    $play_time_sec = round($play_time/1000, 2);
                    if($play_time_sec>=$rv['duration']){
                        $full_play_num++;
                        $full_duration = $rv['duration']*1000;
                        $play_duration+=$full_duration;
                    }else{
                        $play_duration+=$play_time;
                    }
                }
            }
            $play_duration = round($play_duration/1000, 2);
            $one_duration = round($play_duration/$play_num, 2);
            $add_data = array('area_id'=>$v['area_id'],'area_name'=>$v['area_name'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                'hotel_box_type'=>$v['hotel_box_type'],'hotel_is_4g'=>$v['hotel_is_4g'],'forscreen_num'=>$v['num'],'total_duration'=>$v['total_duration'],
                'play_duration'=>$play_duration,'one_duration'=>$one_duration,'play_num'=>$play_num,'full_play_num'=>$full_play_num,
                'type'=>5,'static_date'=>$static_date
            );
            $this->add($add_data);
        }
    }

}