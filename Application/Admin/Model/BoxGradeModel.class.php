<?php
namespace Admin\Model;

class BoxGradeModel extends BaseModel{
	protected $tableName='box_grade';

    public function getDatas($field='*',$filter='',$order='',$group=''){
        $res = $this->field($field)
            ->where($filter)
            ->order($order)
            ->group($group)
            ->select();
        return $res;
    }

    public function getAvgGrade($mac,$start_date,$end_date){
        $start_date = date('Ymd',strtotime($start_date));
        $end_date = date('Ymd',strtotime($end_date));
        $fields = "avg(NULLIF(total_score,0)) as total_score,avg(NULLIF(mini_total_score,0)) as mini_total_score,avg(NULLIF(netty_score,0)) as netty_score,
        avg(NULLIF(heart_score,0)) as heart_score,avg(NULLIF(upspeed_score,0)) as upspeed_score,avg(NULLIF(standard_downspeed_score,0)) as standard_downspeed_score,
        avg(NULLIF(standard_forscreen_score,0)) as standard_forscreen_score,avg(NULLIF(mini_downspeed_score,0)) as mini_downspeed_score,avg(NULLIF(mini_forscreen_score,0)) as mini_forscreen_score,
        sum(standard_forscreen_num) as standard_forscreen_num,sum(mini_forscreen_num) as mini_forscreen_num,sum(standard_download_num) as standard_download_num";
        $sql = "select $fields from savor_box_grade where mac='{$mac}' and date>={$start_date} and date<={$end_date}";
        $res = $this->query($sql);
        return $res;
    }

    public function getGrades($mac,$start_date,$end_date){
        $fields = "grade.mac,grade.date,grade.total_score,grade.mini_total_score,grade.netty_score,grade.heart_score,grade.upspeed_score,
        grade.standard_downspeed_score,grade.standard_forscreen_score,grade.mini_downspeed_score,grade.mini_forscreen_score,detail.netty_reconn_num,
        detail.heart_num,detail.standard_forscreen_success_num,detail.standard_forscreen_num,detail.standard_forscreen_success_rate,detail.mini_forscreen_success_num,
        detail.mini_forscreen_num,detail.mini_forscreen_success_rate,detail.standard_upload_speed,detail.standard_download_speed,detail.mini_download_speed";

        $sql = "select {$fields} from savor_box_grade as grade left join savor_box_grade_details as detail on (grade.mac=detail.mac and grade.date=detail.date)
        where grade.mac='{$mac}' and grade.date>={$start_date} and grade.date<={$end_date}";
        $res = $this->query($sql);
        return $res;
    }


	public function handle_boxgrade_date(){
        $m_boxstatic = new \Admin\Model\BoxStaticgradeconfigModel();
        $config = $m_boxstatic->config();
        $date = date("Ymd", strtotime("-1 day"));
        echo "box_grade_date:$date start \r\n";
        $this->addGrade($config,$date);
        $this->addHotelGrade($date);
        echo "box_grade_date:$date end \r\n";
    }

    public function handle_boxgrade_range(){
        $m_boxstatic = new \Admin\Model\BoxStaticgradeconfigModel();
        $config = $m_boxstatic->config();

        $m_boxgrade_detail = new \Admin\Model\BoxGradedetailsModel();
        $res_dates = $m_boxgrade_detail->getDatas('date','','','date');
        if(!empty($res_dates)){
            $sql_boxgrade = 'TRUNCATE TABLE savor_box_grade';
            $this->execute($sql_boxgrade);
            $sql_hotelgrade = 'TRUNCATE TABLE savor_hotel_grade';
            $this->execute($sql_hotelgrade);

            foreach ($res_dates as $v){
                $now_date = $v['date'];
                echo "box_grade_date:$now_date start \r\n";
                $this->addGrade($config,$now_date);
                $this->addHotelGrade($now_date);
                echo "box_grade_date:$now_date end \r\n";
            }
            $redis  =  \Common\Lib\SavorRedis::getInstance();
            $redis->select(1);
            $cache_key = 'cronscript:macgrade';
            $redis->remove($cache_key);
        }
    }

    public function addGrade($config,$date){
        //type 1netty重连,2投屏成功分数,3心跳分数,4上传网速分数,5下载网速分数
        //score_type 分数类型 10标准投屏,20极简投屏,11总分数-标准投屏,21总分数-极简投屏,12样本-标准投屏,22样本-极简投屏
        $m_boxgrade_detail = new \Admin\Model\BoxGradedetailsModel();
        $res_detail = $m_boxgrade_detail->getDataList('*',array('date'=>$date),'id desc');
        if(empty($res_detail)){
            return true;
        }
        foreach ($res_detail as $v){
            $heart_num = intval($v['heart_num']);
            $standard_forscreen_success_num = intval($v['standard_forscreen_success_num']);
            $mini_forscreen_success_num = intval($v['mini_forscreen_success_num']);
            $forscreen_success_num = $standard_forscreen_success_num + $mini_forscreen_success_num;

            $netty_score = 0;
            if(empty($v['netty_reconn_num'])){
                $v['netty_reconn_num'] = -1;
                if($heart_num>0 && $forscreen_success_num>1){
                    $v['netty_reconn_num'] = 0;
                }
            }
            if($v['netty_reconn_num']>=0){
                $netty_reconn_num = intval($v['netty_reconn_num']);
                $condition = $config[10][1];
                foreach ($condition as $cv){
                    if($netty_reconn_num>=$cv['min'] && $netty_reconn_num<=$cv['max']){
                        $netty_score = $cv['grade'];
                        break;
                    }
                }
            }

            $heart_score = 0;
            if($v['heart_num']){
                $condition = $config[10][3];
                foreach ($condition as $cv){
                    if($heart_num>=$cv['min'] && $heart_num<=$cv['max']){
                        $heart_score = $cv['grade'];
                        break;
                    }
                }
            }
            $upspeed_score = 0;
            if($v['standard_upload_speed'] && !empty($v['standard_upload_speed'])){
                $condition = $config[10][4];
                foreach ($condition as $cv){
                    if($v['standard_upload_speed']>=$cv['min'] && $v['standard_upload_speed']<=$cv['max']){
                        $upspeed_score = $cv['grade'];
                        break;
                    }
                }
            }
            $standard_downspeed_score = 0;
            if($v['standard_download_speed'] && !empty($v['standard_download_speed'])){
                $condition = $config[10][5];
                foreach ($condition as $cv){
                    if($v['standard_download_speed']>=$cv['min'] && $v['standard_download_speed']<=$cv['max']){
                        $standard_downspeed_score = $cv['grade'];
                        break;
                    }
                }
            }
            $standard_forscreen_score = 0;
            if($v['standard_forscreen_success_rate'] && !empty($v['standard_forscreen_success_rate'])){
                $standard_forscreen_success_rate = $v['standard_forscreen_success_rate']*100;
                $condition = $config[10][2];
                foreach ($condition as $cv){
                    if($standard_forscreen_success_rate>=$cv['min'] && $standard_forscreen_success_rate<=$cv['max']){
                        $standard_forscreen_score = $cv['grade'];
                        break;
                    }
                }
            }

            $mini_downspeed_score = 0;
            if($v['mini_download_speed'] && !empty($v['mini_download_speed'])){
                $condition = $config[20][5];
                foreach ($condition as $cv){
                    if($v['mini_download_speed']>=$cv['min'] && $v['mini_download_speed']<=$cv['max']){
                        $mini_downspeed_score = $cv['grade'];
                        break;
                    }
                }
            }
            $mini_forscreen_score = 0;
            if($v['mini_forscreen_success_rate'] && !empty($v['mini_forscreen_success_rate'])){
                $mini_forscreen_success_rate = $v['mini_forscreen_success_rate']*100;
                $condition = $config[20][2];
                foreach ($condition as $cv){
                    if($mini_forscreen_success_rate>=$cv['min'] && $mini_forscreen_success_rate<=$cv['max']){
                        $mini_forscreen_score = $cv['grade'];
                        break;
                    }
                }
            }
            $total_score = $config[11][1]*$netty_score + $config[11][2]*$standard_forscreen_score +
                $config[11][3]*$heart_score + $config[11][4]*$upspeed_score + $config[11][5]*$standard_downspeed_score;
            $mini_total_score = $config[21][2]*$mini_forscreen_score + $config[21][3]*$heart_score + $config[21][5]*$mini_downspeed_score;
            if($total_score>0)  $total_score = sprintf("%.1f",$total_score);
            if($mini_total_score>0)  $mini_total_score = sprintf("%.1f",$mini_total_score);
            $data = array('area_id'=>$v['area_id'],'area_name'=>$v['area_name'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                'room_id'=>$v['room_id'],'room_name'=>$v['room_name'],'box_id'=>$v['box_id'],'box_name'=>$v['box_name'],'mac'=>$v['mac'],
                'is_4g'=>$v['is_4g'],'box_type'=>$v['box_type'],'date'=>$v['date'],'total_score'=>$total_score,
                'mini_total_score'=>$mini_total_score,'netty_score'=>$netty_score,'heart_score'=>$heart_score,'upspeed_score'=>$upspeed_score,
                'standard_downspeed_score'=>$standard_downspeed_score,'standard_forscreen_score'=>$standard_forscreen_score,'mini_downspeed_score'=>$mini_downspeed_score,
                'mini_forscreen_score'=>$mini_forscreen_score,'standard_forscreen_num'=>intval($v['standard_forscreen_num']),'mini_forscreen_num'=>intval($v['mini_forscreen_num']),
                'standard_download_num'=>intval($v['standard_download_num'])
            );
            $this->add($data);
        }
        return true;
    }

    public function addHotelGrade($date){
        $where = array('date'=>$date);
        $group = 'hotel_id';
        $fields = 'area_id,area_name,hotel_id,hotel_name';
        $hotel_ids = $this->getDatas($fields,$where,'',$group);
        if(!empty($hotel_ids)){
            $m_hotel = new \Admin\Model\HotelModel();
            $m_hotelgrade = new \Admin\Model\HotelGradeModel();
            foreach ($hotel_ids as $v){
                $hotel_id = $v['hotel_id'];
                $where = "where date={$date} and hotel_id={$hotel_id} ";
                $sql_boxgrade = "select mac,avg(total_score) as avg_total_score,avg(mini_total_score) as avg_mini_total_score from savor_box_grade {$where} group by mac";
                $res_boxgrades = $this->query($sql_boxgrade);
                $total_score = 0;
                if(!empty($res_boxgrades)){
                    $avg_num = 0;
                    $tmp_total_score = 0;
                    foreach ($res_boxgrades as $gv){
                        if($gv['avg_total_score']>0 || $gv['avg_mini_total_score']>0){
                            $avg_num++;
                        }
                        if($gv['avg_total_score']>=$gv['avg_mini_total_score']){
                            $tmp_total_score+=$gv['avg_total_score'];
                        }elseif($gv['avg_total_score']<=$gv['avg_mini_total_score']){
                            $tmp_total_score+=$gv['avg_mini_total_score'];
                        }
                    }
                    $total_score = sprintf("%.1f",$tmp_total_score/$avg_num);
                }
                $hotel_info = $m_hotel->getOne($hotel_id);

                $data = array('area_id'=>$v['area_id'],'area_name'=>$v['area_name'],'hotel_id'=>$v['hotel_id'],'hotel_name'=>$v['hotel_name'],
                    'is_4g'=>$hotel_info['is_4g'],'hotel_box_type'=>$hotel_info['hotel_box_type'],'total_score'=>$total_score,'date'=>$date);
                $m_hotelgrade->add($data);

            }
        }
    }
}