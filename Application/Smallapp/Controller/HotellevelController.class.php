<?php
namespace Smallapp\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController ;
/**
 * @desc 小程序数据统计-酒楼评级
 *
 */
class HotellevelController extends BaseController {
    var $column_name;
    public function __construct() {
        parent::__construct();
        $this->column_name = array(array('name'=>'id'),array('name'=>'区域id'),array('name'=>'区域名称'),
                                   array('name'=>'酒楼id'),array('name'=>'酒楼名称'),array('name'=>'盒子类型'),
                                   array('name'=>'合作维护人id'),array('name'=>'运维负责人'),array('name'=>'包间id'),
                                   array('name'=>'包间名称'),array('name'=>'机顶盒id'),array('name'=>'机顶盒mac'),
                                   array('name'=>'包间类型'),array('name'=>'是否为广告机'),array('name'=>'故障次数'),
                                   array('name'=>'日志上传次数'),array('name'=>'饭点心跳次数'),array('name'=>'心跳次数'),
                                   array('name'=>'平均下载速度'),array('name'=>'最大下载速度'),array('name'=>'最小下载速度'),
                                   array('name'=>'扫小码次数'),array('name'=>'扫大码次数'),array('name'=>'扫呼码次数'),
                                   array('name'=>'呼码次数'),array('name'=>'投照片次数'),array('name'=>'切换图片投屏次数'),
                                   array('name'=>'投视频次数'),array('name'=>'发现页投照片次数'),array('name'=>'发现页投视频次数'),
                                   array('name'=>'首页点播次数'),array('name'=>'互动游戏次数'),array('name'=>'点播生日歌次数'),
                                   array('name'=>'重投次数'),array('name'=>'点播总次数'),array('name'=>'点播成功次数'),
                                   array('name'=>'投屏总次数'),array('name'=>'投屏成功次数'),array('name'=>'总互动次数'),
                                   array('name'=>'饭局'),array('name'=>'创建日期'),array('name'=>'统计日期')
        );
    }
    public function index(){
        
        $size       = I('numPerPage',50);       //显示每页记录数
        $start      = I('pageNum',1) ;           //当前页码
        $start      = $start ? $start :1;
        $order      = I('_order','hotel_id'); //排序字段
        $sort       = I('_sort','asc');        //排序类型
        $orders     = $order.' '.$sort;
        $start = ($start-1)* $size;
        
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$start);
        $this->assign('_order',$order);
        $this->assign('_sort',$sort);
        $where = array();
        $yesterday = date('Ymd',strtotime('-1 day'));
        $yesterday_str = date('Y-m-d',strtotime('-1 day'));
        $start_str = I('start_date') ? I('start_date') :$yesterday_str ;
        $end_str   = I('end_date') ? I('end_date') : $yesterday_str ;
        
        $area_id = I('area_id',0,'intval');
        if($area_id){
            $where['area_id'] = $area_id;
            $this->assign('area_id',$area_id);
        }
        $static_fj = I('fj',0,'intval');
        
        if($static_fj){
            $where['static_fj'] = $static_fj;
            $this->assign('fj',$static_fj);
        }
        $maintainer_id = I('maintainer_id',0,'intval');
        if($maintainer_id){
            $where['maintainer_id'] = $maintainer_id;
            $this->assign('maintainer_id',$maintainer_id);
        }
        $start_date = I('start_date') ? date('Ymd',strtotime(I('start_date'))): $yesterday;
        $end_date   = I('end_date')   ? date('Ymd',strtotime(I('end_date')))  : $yesterday;
        
        $m_statics = new \Admin\Model\Smallapp\StatisticsModel();
        $fields = 'hotel_id,hotel_name';
        
        $where['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date)); 
        $group = 'hotel_id';
        $maps = $where;
        //$list = $m_statics->getPageList($fields, $where, $order, $group, $start, $size);
        
          
        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        $conf_list = $m_static_config->getWhere('conf_data,type',array('status'=>1));
        
        $conf_arr = array();
        foreach($conf_list as $key=>$v){
            $conf_arr[$v['type']] = json_decode($v['conf_data'],true);
        }
        
        //个数
        $hotel_list = $m_statics->getWhere('hotel_id,hotel_name', $maps, '','', 'hotel_id');
        $count = count($hotel_list);
        
        $a_level = $b_level = $c_level = 0 ;
        foreach($hotel_list as $key=>$v){
            //break;
            //综合评分-心跳分数
            $where = array();
            $where['hotel_id'] = $v['hotel_id'];
            $where['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
            $where['heart_log_nums'] = array('GT',0);
            $fields = "sum(`heart_log_nums`) as heart_log_nums";
            $ret = $m_statics->getOne($fields, $where);
            $heart_log_nums = intval($ret['heart_log_nums']);
            
            $fields =" count(id) as nums";
            $ret = $m_statics->getOne($fields, $where);
            $heart_box_nums = $ret['nums'];
            $avg_heart_log_nums =round( $heart_log_nums / $heart_box_nums);
            
            $score = $this->getScore($avg_heart_log_nums,$conf_arr[3]);
            $multy_heart_score = $conf_arr[1]['heart'] * $score;
            
            //综合评分-网速分数
            $where = array();
            $where['hotel_id'] = $v['hotel_id'];
            $where['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
            $where['avg_down_speed'] = array('GT',0);
            $fields = "sum(`avg_down_speed`) as avg_down_speed";
            $ret    = $m_statics->getOne($fields, $where);
            $avg_down_speed = $ret['avg_down_speed'];
            
            $fields =" count(id) as nums";
            $ret = $m_statics->getOne($fields, $where);
            $speed_box_nums = $ret['nums'];
            $avg_down_speed = round($avg_down_speed / ($speed_box_nums*1024));
            
            $score = $this->getScore($avg_down_speed, $conf_arr[4]);
            
            $multy_net_score = $conf_arr[1]['net'] * $score;
            
            //综合评分-互动分数
            $where = array();
            $where['hotel_id'] = $v['hotel_id'];
            $where['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
            $where['all_interact_nums'] = array('GT',0);
            $fields = "sum(`all_interact_nums`) as all_interact_nums";
            $ret    = $m_statics->getOne($fields, $where);
            $all_interact_nums = intval($ret['all_interact_nums']);
            
            $fields =" count(id) as nums";
            $ret = $m_statics->getOne($fields, $where);
            $hd_box_nums = $ret['nums'];
            
            $avg_interact_nums = round($all_interact_nums / $hd_box_nums);
            
            $score = $this->getScore($avg_interact_nums, $conf_arr[5]);
            $multy_hd_score = $conf_arr[1]['hd'] * $score;
            
            //综合评分 - 互动覆盖率分数
            $where = array();
            $where['hotel_id'] = $v['hotel_id'];
            $where['static_date'] = array(array('EGT',$start_date),array('ELT',$end_date));
            $fields =" count(id) as nums";
            $ret = $m_statics->getOne($fields, $where);
            $all_box_nums = $ret['nums'];
            $cover_rate = round($hd_box_nums / $all_box_nums * 100);
            
            $score = $this->getScore($cover_rate, $conf_arr[6]);
            $multy_cover_score = $conf_arr[1]['cover'] * $score;
            
            $multy_score = $multy_heart_score + $multy_net_score + $multy_hd_score + $multy_cover_score;
            $multy_score = round($multy_score);
            $multy_level = $this->getLevel($multy_score, $conf_arr[2]);   //综合评分
            
            if($multy_level=='A'){
                $mult_level_type = 1;
                $a_level ++;
            }else if($multy_level=='B'){
                $mult_level_type = 2;
                $b_level ++;
            }else if($multy_level == 'C'){
                $mult_level_type = 3;
                $c_level ++;
            }
            
            
            //开机评分-心跳分数
            
            $score = $this->getScore($avg_heart_log_nums,$conf_arr[13]);
            $wake_heart_score = $conf_arr[11]['heart'] * $score;
            
            //开机评分-网速分数
            $score = $this->getScore($avg_down_speed, $conf_arr[14]);
            $wake_net_score = $conf_arr[11]['net'] * $score;
            
            //开机评分-互动分数
            $score = $this->getScore($avg_interact_nums, $conf_arr[15]);
            $wake_hd_score = $conf_arr[11]['hd'] * $score;
            
            //开机评分 - 互动覆盖率分数
            $score = $this->getScore($cover_rate, $conf_arr[16]);
            $wake_cover_score = $conf_arr[11]['cover'] * $score;
            
            $wake_score = $wake_heart_score + $wake_net_score + $wake_hd_score + $wake_cover_score;
            $wake_score = round($wake_score);
            //网络评分-心跳分数
            $score = $this->getScore($avg_heart_log_nums,$conf_arr[23]);
            $net_heart_score = $conf_arr[21]['heart'] * $score;
            
            //网络评分-网速分数
            $score = $this->getScore($avg_down_speed, $conf_arr[24]);
            $net_net_score = $conf_arr[21]['net'] * $score;
            
            //网络评分-互动分数
            $score = $this->getScore($avg_interact_nums, $conf_arr[25]);
            $net_hd_score = $conf_arr[21]['hd'] * $score;
            
            //网络评分 - 互动覆盖率分数
            $score = $this->getScore($cover_rate, $conf_arr[26]);
            $net_cover_score = $conf_arr[21]['cover'] * $score;
            
            $net_score = $net_heart_score + $net_net_score + $net_hd_score + $net_cover_score;
            $net_score = round($net_score);
            //互动评分-心跳分数
            $score = $this->getScore($avg_heart_log_nums,$conf_arr[33]);
            $hd_heart_score = $conf_arr[31]['heart'] * $score;
            //互动评分-网速分数
            $score = $this->getScore($avg_down_speed, $conf_arr[34]);
            $hd_net_score = $conf_arr[31]['net'] * $score;
            //互动评分-互动分数
            $score = $this->getScore($avg_interact_nums, $conf_arr[35]);
            $hd_hd_score = $conf_arr[31]['hd'] * $score;
            
            //互动评分-互动覆盖率分数
            
            $score = $this->getScore($cover_rate, $conf_arr[36]);
            $hd_cover_score = $conf_arr[31]['cover'] * $score;
            $hd_score = $hd_heart_score + $hd_net_score + $hd_hd_score + $hd_cover_score;
            $hd_score = round($hd_score);
            
            $hotel_list[$key]['mult_level_type'] = $mult_level_type;
            $hotel_list[$key]['multy_level']       = $multy_level;
            $hotel_list[$key]['mylty_score']       = $multy_score;
            $hotel_list[$key]['net_score']         = $net_score;
            $hotel_list[$key]['wake_score']        = $wake_score;
            $hotel_list[$key]['hd_score']          = $hd_score;
            $hotel_list[$key]['heart_log_nums']    = $heart_log_nums;
            $hotel_list[$key]['avg_down_speed']    = $avg_down_speed ? $avg_down_speed.'kb/s' : '';
            $hotel_list[$key]['all_interact_nums'] = $all_interact_nums;
            $hotel_list[$key]['all_box_nums'] = $all_box_nums;
            $hotel_list[$key]['hd_box_nums']       = $hd_box_nums;
            
            
        }
        sortArrByOneField($hotel_list,'mylty_score',true);
        
        $hotel_list = array_slice($hotel_list, $start,$size);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        
        
        //地区
        $m_area_info = new \Admin\Model\AreaModel();
        $area_list = $m_area_info->getAllArea();
        //合作维护人
        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $fields = 'a.user_id main_id,user.remark ';
        $map['state']   = 1;
        $map['role_id']   = 1;
        $user_info = $m_opuser_role->getAllRole($fields,$map,'' );
        
        
        $this->assign('pub_info',$user_info);
        $this->assign('area_list',$area_list);
        //$page = $list['page'];
        $this->assign('a_level',$a_level);
        $this->assign('b_level',$b_level);
        $this->assign('c_level',$c_level);
        $this->assign('page',$show);
        $this->assign('start_date',$start_str);
        $this->assign('end_date',$end_str);
        $this->assign('list',$hotel_list);
        $this->display('hotellevel');
    }

    public function getHotellevel($static_date,$hotel_id=0,$isdetail=0){
        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        $conf_list = $m_static_config->getWhere('conf_data,type',array('status'=>1));
        $conf_arr = array();
        foreach($conf_list as $key=>$v){
            $conf_arr[$v['type']] = json_decode($v['conf_data'],true);
        }
        $m_statics = new \Admin\Model\Smallapp\StatisticsModel();
        $fields = 'hotel_id,hotel_name';
        $where['static_date'] = $static_date;
        $group = 'hotel_id';
        $maps = $where;

        //个数
        if($hotel_id){
            $hotel_list = array(array('hotel_id'=>$hotel_id));
        }else{
            $hotel_list = $m_statics->getWhere('hotel_id,hotel_name', $maps, '','', 'hotel_id');
        }

        $a_level = $b_level = $c_level = 0 ;
        foreach($hotel_list as $key=>$v){
            //综合评分-心跳分数
            $where = array();
            $where['hotel_id'] = $v['hotel_id'];
            $where['static_date'] = $static_date;
            $where['heart_log_nums'] = array('GT',0);
            $fields = "sum(`heart_log_nums`) as heart_log_nums";
            $ret = $m_statics->getOne($fields, $where);
            $heart_log_nums = intval($ret['heart_log_nums']);

            $fields =" count(id) as nums";
            $ret = $m_statics->getOne($fields, $where);
            $heart_box_nums = $ret['nums'];
            $avg_heart_log_nums =round( $heart_log_nums / $heart_box_nums);

            $score = $this->getScore($avg_heart_log_nums,$conf_arr[3]);
            $multy_heart_score = $conf_arr[1]['heart'] * $score;

            //综合评分-网速分数
            $where = array();
            $where['hotel_id'] = $v['hotel_id'];
            $where['static_date'] = $static_date;
            $where['avg_down_speed'] = array('GT',0);
            $fields = "sum(`avg_down_speed`) as avg_down_speed";
            $ret    = $m_statics->getOne($fields, $where);
            $avg_down_speed = $ret['avg_down_speed'];

            $fields =" count(id) as nums";
            $ret = $m_statics->getOne($fields, $where);
            $speed_box_nums = $ret['nums'];
            $avg_down_speed = round($avg_down_speed / ($speed_box_nums*1024));

            $score = $this->getScore($avg_down_speed, $conf_arr[4]);

            $multy_net_score = $conf_arr[1]['net'] * $score;

            //综合评分-互动分数
            $where = array();
            $where['hotel_id'] = $v['hotel_id'];
            $where['static_date'] = $static_date;
            $where['all_interact_nums'] = array('GT',0);
            $fields = "sum(`all_interact_nums`) as all_interact_nums";
            $ret    = $m_statics->getOne($fields, $where);
            $all_interact_nums = intval($ret['all_interact_nums']);

            $fields =" count(id) as nums";
            $ret = $m_statics->getOne($fields, $where);
            $hd_box_nums = $ret['nums'];

            $avg_interact_nums = round($all_interact_nums / $hd_box_nums);

            $score = $this->getScore($avg_interact_nums, $conf_arr[5]);
            $multy_hd_score = $conf_arr[1]['hd'] * $score;

            //综合评分 - 互动覆盖率分数
            $where = array();
            $where['hotel_id'] = $v['hotel_id'];
            $where['static_date'] = $static_date;
            $fields =" count(id) as nums";
            $ret = $m_statics->getOne($fields, $where);
            $all_box_nums = $ret['nums'];
            $cover_rate = round($hd_box_nums / $all_box_nums * 100);

            $score = $this->getScore($cover_rate, $conf_arr[6]);
            $multy_cover_score = $conf_arr[1]['cover'] * $score;

            $multy_score = $multy_heart_score + $multy_net_score + $multy_hd_score + $multy_cover_score;
            $multy_score = round($multy_score);
            $multy_level = $this->getLevel($multy_score, $conf_arr[2]);   //综合评分

            if($multy_level=='A'){
                $a_level ++;
            }else if($multy_level=='B'){
                $b_level ++;
            }else if($multy_level == 'C'){
                $c_level ++;
            }

            if($isdetail){
                //开机评分-心跳分数

                $score = $this->getScore($avg_heart_log_nums,$conf_arr[13]);
                $wake_heart_score = $conf_arr[11]['heart'] * $score;

                //开机评分-网速分数
                $score = $this->getScore($avg_down_speed, $conf_arr[14]);
                $wake_net_score = $conf_arr[11]['net'] * $score;

                //开机评分-互动分数
                $score = $this->getScore($avg_interact_nums, $conf_arr[15]);
                $wake_hd_score = $conf_arr[11]['hd'] * $score;

                //开机评分 - 互动覆盖率分数
                $score = $this->getScore($cover_rate, $conf_arr[16]);
                $wake_cover_score = $conf_arr[11]['cover'] * $score;

                $wake_score = $wake_heart_score + $wake_net_score + $wake_hd_score + $wake_cover_score;
                $wake_score = round($wake_score);
                //网络评分-心跳分数
                $score = $this->getScore($avg_heart_log_nums,$conf_arr[23]);
                $net_heart_score = $conf_arr[21]['heart'] * $score;

                //网络评分-网速分数
                $score = $this->getScore($avg_down_speed, $conf_arr[24]);
                $net_net_score = $conf_arr[21]['net'] * $score;

                //网络评分-互动分数
                $score = $this->getScore($avg_interact_nums, $conf_arr[25]);
                $net_hd_score = $conf_arr[21]['hd'] * $score;

                //网络评分 - 互动覆盖率分数
                $score = $this->getScore($cover_rate, $conf_arr[26]);
                $net_cover_score = $conf_arr[21]['cover'] * $score;

                $net_score = $net_heart_score + $net_net_score + $net_hd_score + $net_cover_score;
                $net_score = round($net_score);
                //互动评分-心跳分数
                $score = $this->getScore($avg_heart_log_nums,$conf_arr[33]);
                $hd_heart_score = $conf_arr[31]['heart'] * $score;
                //互动评分-网速分数
                $score = $this->getScore($avg_down_speed, $conf_arr[34]);
                $hd_net_score = $conf_arr[31]['net'] * $score;
                //互动评分-互动分数
                $score = $this->getScore($avg_interact_nums, $conf_arr[35]);
                $hd_hd_score = $conf_arr[31]['hd'] * $score;

                //互动评分-互动覆盖率分数

                $score = $this->getScore($cover_rate, $conf_arr[36]);
                $hd_cover_score = $conf_arr[31]['cover'] * $score;
                $hd_score = $hd_heart_score + $hd_net_score + $hd_hd_score + $hd_cover_score;
                $hd_score = round($hd_score);
            }
        }
        $hotel_level = array('a'=>$a_level,'b'=>$b_level,'c'=>$c_level);
        if($hotel_id){
            $hotel_level['level'] = $multy_level;
            $hotel_level['score'] = $multy_score;
            if($isdetail){
                $hotel_level['net_score'] = $net_score;
                $hotel_level['wake_score'] = $wake_score;
                $hotel_level['hd_score'] = $hd_score;

            }
        }
        return $hotel_level;
    }


    public function getMacscore($static_date,$box_mac){
        $m_static_config = new \Admin\Model\Smallapp\StaticConfigModel();
        $conf_list = $m_static_config->getWhere('conf_data,type',array('status'=>1));
        $conf_arr = array();
        foreach($conf_list as $key=>$v){
            $conf_arr[$v['type']] = json_decode($v['conf_data'],true);
        }
        $m_statics = new \Admin\Model\Smallapp\StatisticsModel();

        //综合评分-心跳分数
        $where = array();
        $where['box_mac'] = $box_mac;
        $where['static_date'] = $static_date;
        $where['heart_log_nums'] = array('GT',0);
        $fields = "sum(`heart_log_nums`) as heart_log_nums";
        $ret = $m_statics->getOne($fields, $where);
        $heart_log_nums = intval($ret['heart_log_nums']);

        $fields =" count(id) as nums";
        $ret = $m_statics->getOne($fields, $where);
        $heart_box_nums = $ret['nums'];
        $avg_heart_log_nums =round( $heart_log_nums / $heart_box_nums);

        $score = $this->getScore($avg_heart_log_nums,$conf_arr[3]);
        $multy_heart_score = $conf_arr[1]['heart'] * $score;

        //综合评分-网速分数
        $where = array();
        $where['box_mac'] = $box_mac;
        $where['static_date'] = $static_date;
        $where['avg_down_speed'] = array('GT',0);
        $fields = "sum(`avg_down_speed`) as avg_down_speed";
        $ret    = $m_statics->getOne($fields, $where);
        $avg_down_speed = $ret['avg_down_speed'];

        $fields =" count(id) as nums";
        $ret = $m_statics->getOne($fields, $where);
        $speed_box_nums = $ret['nums'];
        $avg_down_speed = round($avg_down_speed / ($speed_box_nums*1024));

        $score = $this->getScore($avg_down_speed, $conf_arr[4]);

        $multy_net_score = $conf_arr[1]['net'] * $score;

        //综合评分-互动分数
        $where = array();
        $where['box_mac'] = $box_mac;
        $where['static_date'] = $static_date;
        $where['all_interact_nums'] = array('GT',0);
        $fields = "sum(`all_interact_nums`) as all_interact_nums";
        $ret    = $m_statics->getOne($fields, $where);
        $all_interact_nums = intval($ret['all_interact_nums']);

        $fields =" count(id) as nums";
        $ret = $m_statics->getOne($fields, $where);
        $hd_box_nums = $ret['nums'];

        $avg_interact_nums = round($all_interact_nums / $hd_box_nums);

        $score = $this->getScore($avg_interact_nums, $conf_arr[5]);
        $multy_hd_score = $conf_arr[1]['hd'] * $score;

        //综合评分 - 互动覆盖率分数
        $where = array();
        $where['box_mac'] = $box_mac;
        $where['static_date'] = $static_date;
        $fields =" count(id) as nums";
        $ret = $m_statics->getOne($fields, $where);
        $all_box_nums = $ret['nums'];
        $cover_rate = round($hd_box_nums / $all_box_nums * 100);

        $score = $this->getScore($cover_rate, $conf_arr[6]);
        $multy_cover_score = $conf_arr[1]['cover'] * $score;

        $multy_score = $multy_heart_score + $multy_net_score + $multy_hd_score + $multy_cover_score;
        $multy_score = round($multy_score);
        $res = array('score'=>$multy_score);
        return $res;
    }


    public function datalist(){
        ini_set("memory_limit","1024M");
        $size       = I('numPerPage',50);       //显示每页记录数
        $start      = I('pageNum',1) ;           //当前页码
        $start      = $start ? $start :1;
        $order      = I('_order','a.id'); //排序字段
        $sort       = I('_sort','desc');        //排序类型
        $orders     = $order.' '.$sort;
        $start = ($start-1)* $size;
        
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$start);
        $this->assign('_order',$order);
        $this->assign('_sort',$sort);
        
        //字段列表
        $table_name = 'savor_smallapp_statistics';
        $columns_arr = M()->query("select COLUMN_NAME from information_schema.COLUMNS where table_name ='".$table_name."'");
        
        
        $start_date = I('start_date') ? I('start_date') : date('Y-m-d',strtotime('-1 day'));
        $end_date   = I('end_date')   ? I('end_date')   : date('Y-m-d',strtotime('-1 day'));
        
        $start_date_str = date('Ymd',strtotime($start_date));
        $end_date_str   = date('Ymd',strtotime($end_date));
        
        $where = array();
        $where['a.static_date'] = array(array('egt',$start_date_str),array('elt',$end_date_str));
        
        $fields = I('fields');
        $fields_str = '';
        $fields_ids= '';
        if(empty($fields)){
            $fields_str = "a.*";
            foreach($this->column_name as $key=>$v){
                $view_column[] = $v['name'];
            }
        }else {
            foreach($fields as $v){
                $fields_str .=$space . 'a.'. $columns_arr[$v]['column_name'];
                $fields_ids .=$space .$v;
                $space = ',';
                $view_column[] = $this->column_name[$v]['name'];
            }
        }
        if(in_array(6, $fields)){
            $view_column[] = '维护人';
            
            $fields_str .=',user.remark as maintainer';
        }
        
        
        $m_statics = new \Admin\Model\Smallapp\StatisticsModel();
        $list = $m_statics->getPageList($fields_str, $where, $orders, '', $start, $size);
        
        
        $all_columns = $this->column_name;
        
        $this->assign('fields_ids',$fields_ids);
        $this->assign('fields',$fields);
        $this->assign('all_columns',$all_columns);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('start_date_str',$start_date_str);
        $this->assign('end_date_str',$end_date_str);
        $this->assign('view_column',$view_column);
        $this->assign('list',$list['list']);
        
        $this->assign('page',$list['page']);
        $this->display('datalist');
    }
    
    private function getScore($data,$conf_arr){
        $score = 0;
        foreach ($conf_arr as $key=>$v){
            if($data>=$v['min'] && $data<=$v['max']){
                $score =  $v['score'];
                break;
            }
        }
        return $score/100;
    }
    private function getLevel($data,$conf_arr){
        if($data>=$conf_arr['mina'] && $data<=$conf_arr['maxa']){
            $level = 'A';
        }else if($data>=$conf_arr['minb'] && $data<=$conf_arr['maxb']){
            $level = 'B';
        }else if($data>=$conf_arr['minc'] && $data<=$conf_arr['maxc']){
            $level = 'C';
        }
        return $level;
    }
}