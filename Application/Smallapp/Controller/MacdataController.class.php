<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序数据统计-版位数据
 *
 */
class MacdataController extends BaseController {

    public function index(){
        $box_mac = I('box_mac','','trim');
        $day = I('day',7,'intval');
        $type = I('type',7,'intval');//2传播力,4网络质量,5互动饭局数,7互动次数,8评分,9心跳
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        $days = $m_statistics->getDays($day,$start_date,$end_date);
        $box_info = $m_statistics->getOne();

        $where = array('box_mac'=>$box_mac);
        $fields = "hotel_name,room_name";
        $ret = $m_statistics->getOne($fields, $where,'id desc');
        $box_name = $ret['hotel_name'].'-'.$ret['room_name'].'-'.$box_mac;

        $chart_list = array();
        if($box_mac){
            foreach ($days as $k=>$v){
                $charts = $this->ratioChart($box_mac,$type,$v);
                $chart_list[] = $charts;
            }
        }
        //详细数据
        $detail_list = array();
        /*
        if($box_mac){
            $hotellevel_c = A('Hotellevel');
            $detail_breaknum = 4;
            foreach ($days as $k=>$v){
                $ratenums = $m_statistics->getRatenum($v,0,0,0,$box_mac);
                $detail = array('fjnum'=>$ratenums['fjnum'],'hdnum'=>$ratenums['hdnum']);
                $detail['transmissibility'] = $m_statistics->getRate($ratenums,2);
                $detail['screens'] = $m_statistics->getRate($ratenums,3);
                $detail['network'] = $m_statistics->getRate($ratenums,4);
                $nums = $hotellevel_c->getMacscore($v,$box_mac);
                $detail['score'] = $nums['score'];
                $detail_list[$v] = $detail;
                if($k==$detail_breaknum){
                    break;
                }
            }
        }
        */

        $this->assign('chart',json_encode($chart_list));
        $this->assign('detail_list',$detail_list);
        $this->assign('alldays',json_encode($days));
        $this->assign('day',$day);
        $this->assign('box_mac',$box_mac);
        $this->assign('box_name',$box_name);
        $this->assign('type',$type);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->display();
    }

    public function grade(){
        $box_mac = I('box_mac','','trim');
        $day = I('day',7,'intval');
        $forscreen_type = I('forscreen_type',1,'intval');
        $type = I('type',99,'intval');//99总评分,1netty重连,2投屏成功分数,3心跳分数,4上传网速分数,5下载网速分数
        $start_date = I('start_date','');
        $end_date = I('end_date','');

        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        if(!empty($start_date) && !empty($end_date)){
            $vstart_date = $start_date;
            $vend_date = $end_date;
            $day = 0;
        }else{
            $end_date = date('Y-m-d',strtotime('-1 day'));
            $start_date = date('Y-m-d',strtotime("-$day day"));
            $vstart_date = '';
            $vend_date = '';
        }
        $days = $m_statistics->getDates($start_date,$end_date,2);
        $m_box = new \Admin\Model\BoxModel();
        $ret = $m_box->getHotelInfoByBoxMac($box_mac);
        $message = '当前酒楼：'.$ret['hotel_name'].'-'.$ret['room_name'].'-'.$box_mac;

        $cache_key = 'cronscript:macgrade';
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $res_cache = $redis->get($cache_key);
        $is_refresh = 0;
        if(!empty($res_cache)){
            $cache_data = json_decode($res_cache,true);
            if($cache_data['status']==1){
                $is_refresh = 1;
            }else{
                $box_mac = '';
                $message = '数据正在重新计算中,请稍后查看';
            }
        }

        $chart_list = array();

        $m_boxstatic = new \Admin\Model\BoxStaticgradeconfigModel();
        $config = $m_boxstatic->config();
        if($forscreen_type==1){
            $sample_nums = $config[12];
        }else{
            $sample_nums = $config[22];
        }

        $grades = array('total'=>0,'netty'=>0,'forscreen'=>0,'heart'=>0,'upspeed'=>0,'downspeed'=>0);
        $samples = array('forscreen'=>1,'upspeed'=>1,'downspeed'=>1);

        $mac_x_axis = 9999;
        if($box_mac){
            $m_box = new \Admin\Model\BoxModel();
            $bwhere = array('mac'=>$box_mac,'state'=>1,'flag'=>0);
            $res_box = $m_box->getInfo('*',$bwhere,'id desc','0,1');
            if(!empty($res_box)){
                $mac_update_date = date('Ymd',strtotime($res_box[0]['forscreen_uptime']));
                $mac_update_x_axis = array_search($mac_update_date,$days);
                if($mac_update_x_axis!=false){
                    $mac_x_axis = $mac_update_x_axis;
                }
            }
        }

        if($box_mac){
            $all_score = array();

            $m_boxgrade = new \Admin\Model\BoxGradeModel();
            foreach ($days as $k=>$v){
                $condition = array('date'=>$v,'mac'=>$box_mac);
                $res_boxgrade = $m_boxgrade->getInfo($condition);
                $total_score =$netty_score=$forscreen_score=$heart_score=$upspeed_score=$downspeed_score=0;
                if(!empty($res_boxgrade)){
                    if($forscreen_type==1){
                        $total_score = $res_boxgrade['total_score'];
                        $forscreen_score = $res_boxgrade['standard_forscreen_score'];
                        $downspeed_score = $res_boxgrade['standard_downspeed_score'];
                    }else{
                        $total_score = $res_boxgrade['mini_total_score'];
                        $forscreen_score = $res_boxgrade['mini_forscreen_score'];
                        $downspeed_score = $res_boxgrade['mini_downspeed_score'];
                    }
                    $netty_score = $res_boxgrade['netty_score'];
                    $heart_score=$res_boxgrade['heart_score'];
                    $upspeed_score=$res_boxgrade['upspeed_score'];
                }
                $all_score['99'][]=$total_score;
                $all_score['1'][]=$netty_score;
                $all_score['2'][]=$forscreen_score;
                $all_score['3'][]=$heart_score;
                $all_score['4'][]=$upspeed_score;
                $all_score['5'][]=$downspeed_score;
            }
            $chart_list = $all_score[$type];

            $standard_forscreen_num = $mini_forscreen_num = $standard_download_num = 0;
            $res_grades = $m_boxgrade->getAvgGrade($box_mac,$start_date,$end_date);
            if(!empty($res_grades)){
                $res_grades = $res_grades[0];
                $standard_forscreen_num = intval($res_grades['standard_forscreen_num']);
                $mini_forscreen_num = intval($res_grades['mini_forscreen_num']);
                $standard_download_num = intval($res_grades['standard_download_num']);

                if($forscreen_type==1){
                    $total_score = $res_grades['total_score'];
                    $forscreen_score = $res_grades['standard_forscreen_score'];
                    $downspeed_score = $res_grades['standard_downspeed_score'];
                }else{
                    $total_score = $res_grades['mini_total_score'];
                    $forscreen_score = $res_grades['mini_forscreen_score'];
                    $downspeed_score = $res_grades['mini_downspeed_score'];
                }
                $netty_score = $res_grades['netty_score'];
                $heart_score=$res_grades['heart_score'];
                $upspeed_score=$res_grades['upspeed_score'];

                if($total_score)        $grades['total'] = sprintf("%.1f",$total_score);
                if($netty_score)        $grades['netty'] = sprintf("%.1f",$netty_score);
                if($forscreen_score)    $grades['forscreen'] = sprintf("%.1f",$forscreen_score);
                if($heart_score)        $grades['heart'] = sprintf("%.1f",$heart_score);
                if($upspeed_score)      $grades['upspeed'] = sprintf("%.1f",$upspeed_score);
                if($downspeed_score)    $grades['downspeed'] = sprintf("%.1f",$downspeed_score);
            }

            if($forscreen_type==1){
                if($standard_forscreen_num && $standard_forscreen_num<$sample_nums[2])  $samples['forscreen'] = 0;
                if($standard_forscreen_num && $standard_forscreen_num<$sample_nums[4])  $samples['upspeed'] = 0;
                if($standard_download_num && $standard_download_num<$sample_nums[5])    $samples['downspeed'] = 0;
            }else{
                if($mini_forscreen_num && $mini_forscreen_num<$sample_nums[2])          $samples['forscreen'] = 0;
                if($standard_download_num && $standard_download_num<$sample_nums[5])    $samples['downspeed'] = 0;
            }
        }
        //详细数据
        $detail_list = array();


        $this->assign('mac_x_axis',$mac_x_axis);
        $this->assign('samples',$samples);
        $this->assign('grades',$grades);
        $this->assign('chart',json_encode($chart_list));
        $this->assign('detail_list',$detail_list);
        $this->assign('alldays',json_encode($days));
        $this->assign('day',$day);
        $this->assign('box_mac',$box_mac);
        $this->assign('message',$message);
        $this->assign('forscreen_type',$forscreen_type);
        $this->assign('type',$type);
        $this->assign('start_date',$vstart_date);
        $this->assign('end_date',$vend_date);
        $this->assign('is_refresh',$is_refresh);
        $this->display();
    }

    public function calculation(){
        $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php crontab/boxgradebyrange > /tmp/null &";
        system($shell);
        $cache_key = 'cronscript:macgrade';
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $cache_data = json_encode(array('status'=>2,'time'=>date('Y-m-d H:i:s')));
        $redis->set($cache_key,$cache_data);
        $this->output('正在重新计算中,请稍后', '/',2);
    }

    public function changeforscreentype(){
        $mac = I('mac','');
        $condition = array('mac'=>$mac,'state'=>1,'flag'=>0);
        $m_box = new \Admin\Model\BoxModel();
        $res_box = $m_box->where($condition)->find();
        if(IS_GET){
            $this->assign('vinfo',$res_box);
            $this->display();
        }else{
            $is_sapp_forscreen = I('post.is_sapp_forscreen',1,'intval');
            $is_open_simple = I('post.is_open_simple',0,'intval');
            if($res_box['is_sapp_forscreen']!=$is_sapp_forscreen || $res_box['is_open_simple']!=$is_open_simple){
                $forscreen_uptime = date('Y-m-d H:i:s');
                $m_boxchange = new \Admin\Model\BoxChangeforscreenModel();
                $old_data = array('is_sapp_forscreen'=>$res_box['is_sapp_forscreen'],'is_open_simple'=>$res_box['is_open_simple'],
                    'is_4g'=>$res_box['is_4g'],'box_type'=>$res_box['box_type']);
                $data = array('mac'=>$mac,'old_data'=>json_encode($old_data),'is_sapp_forscreen'=>$is_sapp_forscreen,
                    'is_open_simple'=>$is_open_simple,'is_4g'=>$res_box['is_4g'],'box_type'=>$res_box['box_type'],
                    'forscreen_uptime'=>$forscreen_uptime);
                $res = $m_boxchange->add($data);

                $box_data = array('is_sapp_forscreen'=>$is_sapp_forscreen,'is_open_simple'=>$is_open_simple,'forscreen_uptime'=>$forscreen_uptime);
                $m_box->where('id='.$res_box['id'])->save($box_data);

                $m_box->checkForscreenTypeByMac($mac);
            }else{
                $res = false;
            }
            if($res){
                $this->output('操作成功!', 'hoteldata/grade');
            }else{
                $this->output('操作失败', 'macdata/changeforscreentype',2,0);
            }

        }

    }

    /*
     * 比率图表
     * type 1转换率,2传播力,3屏幕在线率,4网络质量,5互动饭局数,6在线屏幕数,7互动次数,8评分,9心跳
     */
    public function ratioChart($box_mac,$type,$date){
        $m_statistics = new \Admin\Model\Smallapp\StatisticsModel();
        switch ($type){
            case 1:
            case 3:
            case 4:
                $nums = $m_statistics->getRatenum($date,0,$type,0,$box_mac);
                $chart = $m_statistics->getRate($nums,$type)/100;
                break;
            case 2:
                $nums = $m_statistics->getRatenum($date,0,$type,0,$box_mac);
                $chart = $m_statistics->getRate($nums,$type);
                break;
            case 5:
                $nums = $m_statistics->getRatenum($date,0,$type,0,$box_mac);
                $chart = $nums['fjnum'];
                break;
            case 6:
                $nums = $m_statistics->getRatenum($date,0,$type,0,$box_mac);
                $chart = $nums['zxnum'];
                break;
            case 7:
                $nums = $m_statistics->getRatenum($date,0,$type,0,$box_mac);
                $chart = $nums['hdnum'];
                break;
            case 8:
                $hotellevel_c = A('Hotellevel');
                $nums = $hotellevel_c->getMacscore($date,$box_mac);
                $chart = $nums['score'];
                break;
            case 9:
                $nums = $m_statistics->getRatenum($date,0,$type,0,$box_mac);
                $chart = $nums['xtnum'];
                break;
        }
        return $chart;
    }
}