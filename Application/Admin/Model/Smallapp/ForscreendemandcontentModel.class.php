<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class ForscreendemandcontentModel extends BaseModel{
    protected $tableName='smallapp_forscreen_demandcontent';
    public function getList($field,$where,$group, $order='id desc', $start=0,$size=5){
        $list = $this->field($field)
        ->where($where)
        ->order($order)
        ->limit($start,$size)
        ->group($group)
        ->select();
        $rt = $this->where($where)
        ->group($group)
        ->select();
        $count = count($rt);
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $data = array('list'=>$list,'page'=>$show);
        return $data;
    }

    public function statDemandDataFor(){
        set_time_limit(0);
        ini_set("memory_limit", "256M");
        $yesterday = date('Y-m-d',strtotime('-1 day'));

        //echo $yesterday;exit;
        $start_date = $yesterday.' 00:00:00';
        $end_date   = $yesterday.' 23:59:59';

        $meal_time  = C('MEAL_TIME');
        $sql ="select id area_id,region_name from savor_area_info where is_in_hotel= 1";
        $area_info = M()->query($sql);
        //$config ['FORSCREEN_RECOURCE_CATE'] = array('1'=>'节目',"2"=>'热播内容节目','3'=>'点播商品视频','4'=>'点播banner商品视频','5'=>'点播生日歌','6'=>'点播星座视频','7'=>'热播内容-用户');
        //热播内容节目
        $sql = "SELECT resource_name,ads_id as resource_id ,media_id,oss_addr
                FROM savor_smallapp_datadisplay
                WHERE `type`=1  AND add_date='".$yesterday."' GROUP BY media_id";
        $hot_program_list = M()->query($sql);
        foreach($hot_program_list as $key=>$v){
            foreach($area_info as $vv){
                $hot_program_list[$key]['demand_nums_'.$vv['area_id']] =0;
                $hot_program_list[$key]['demand_fj_'.$vv['area_id']] =0;
                $sql = "select sum(display_num) as display_num from savor_smallapp_datadisplay
                        where media_id =".$v['media_id']." and area_id=".$vv['area_id']." and type=1 AND add_date='".$yesterday."'";
                $rts = M()->query($sql);
                $hot_program_list[$key]['display_num_'.$vv['area_id']] = intval($rts[0]['display_num']);
            }
            $sql = "select r.id ,r.area_id,r.create_time,box_id
                from savor_smallapp_forscreen_record r
                where resource_id=".$v['resource_id']." and r.action =17 and small_app_id =1 and r.create_time>='".$start_date.
                "' and r.create_time<='".$end_date."' and r.mobile_brand!='devtools'";

            $rt = M()->query($sql);
            $lunch_temp = [];
            $dinner_temp = [];

            foreach($rt as $vv){
                $hot_program_list[$key]['demand_nums_'.$vv['area_id']] +=1;
                $f_time = date('H:i',strtotime($vv['create_time']));
                if($f_time>=$meal_time['lunch'][0] && $f_time<=$meal_time['lunch'][1]){
                    $lunch_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];
                }
                if($f_time>=$meal_time['dinner'][0] && $f_time<=$meal_time['dinner'][1]){
                    $dinner_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];
                }
            }

            foreach($lunch_temp as $kk=>$vv){
                $hot_program_list[$key]['demand_fj_'.$kk] += count($lunch_temp[$kk]);
            }

            foreach($dinner_temp as $kk=>$vv){
                $hot_program_list[$key]['demand_fj_'.$kk] += count($dinner_temp[$kk]);
            }
            $hot_program_list[$key]['resource_cate'] = 1;  //热播节目
            $hot_program_list[$key]['sta_date'] = $yesterday;
            //unset($hot_program_list[$key]['media_id']);
        }
        //print_r($hot_program_list);exit;
        //热播内容-用户
        $sql = "SELECT resource_name, resource_id ,oss_addr
                FROM savor_smallapp_datadisplay
                WHERE `type`= 2  AND add_date='".$yesterday."' GROUP BY resource_id";
        $hot_user_list = M()->query($sql);
        foreach($hot_user_list as $key=>$v){
            foreach($area_info as $vv){
                $hot_user_list[$key]['demand_nums_'.$vv['area_id']] =0;
                $hot_user_list[$key]['demand_fj_'.$vv['area_id']] =0;

                $sql = "select sum(display_num) as display_num from savor_smallapp_datadisplay
                        where resource_id =".$v['resource_id']." and area_id=".$vv['area_id']." and type=2 AND add_date='".$yesterday."'";
                $rts = M()->query($sql);
                $hot_user_list[$key]['display_num_'.$vv['area_id']] = intval($rts[0]['display_num']);

            }
            $sql = "select r.id ,r.area_id,r.create_time,box_id
                from savor_smallapp_forscreen_record r
                where resource_id=".$v['resource_id']." and r.action in(16,17) and small_app_id =1 and r.create_time>='".$start_date.
                "' and r.create_time<='".$end_date."' and r.mobile_brand!='devtools'";

            $rt = M()->query($sql);
            $lunch_temp = [];
            $dinner_temp = [];
            foreach($rt as $vv){
                $hot_user_list[$key]['demand_nums_'.$vv['area_id']] +=1;
                $f_time = date('H:i',strtotime($vv['create_time']));
                if($f_time>=$meal_time['lunch'][0] && $f_time<=$meal_time['lunch'][1]){
                    $lunch_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];
                }
                if($f_time>=$meal_time['dinner'][0] && $f_time<=$meal_time['dinner'][1]){
                    $dinner_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];
                }
            }
            foreach($lunch_temp as $kk=>$vv){
                $hot_user_list[$key]['demand_fj_'.$kk] += count($lunch_temp[$kk]);
            }

            foreach($dinner_temp as $kk=>$vv){
                $hot_user_list[$key]['demand_fj_'.$kk] += count($dinner_temp[$kk]);
            }
            $hot_user_list[$key]['resource_cate'] = 2;  //热播用户内容
            $hot_user_list[$key]['sta_date'] = $yesterday;
            $hot_user_list[$key]['resource_name'] = '热播用户内容';
        }
        //节目    首先获取展示的节目
        $sql = "SELECT resource_name,ads_id as resource_id ,media_id,oss_addr
                FROM savor_smallapp_datadisplay 
                WHERE `type`=3  AND add_date='".$yesterday."' GROUP BY media_id";

        $program_list = M()->query($sql);
        foreach($program_list as $key=>$v){
            foreach($area_info as $vv){
                $program_list[$key]['demand_nums_'.$vv['area_id']] =0;
                $program_list[$key]['demand_fj_'.$vv['area_id']] =0;
                $sql = "select sum(display_num) as display_num from savor_smallapp_datadisplay
                        where media_id =".$v['media_id']." and area_id=".$vv['area_id']." and type=3 AND add_date='".$yesterday."'";
                $rts = M()->query($sql);
                $program_list[$key]['display_num_'.$vv['area_id']] = intval($rts[0]['display_num']);
            }
            $sql = "select r.id ,r.area_id,r.create_time,box_id
                from savor_smallapp_forscreen_record r
                where resource_id=".$v['resource_id']." and r.action =5 and small_app_id =1 and r.create_time>='".$start_date.
                "' and r.create_time<='".$end_date."' and r.mobile_brand!='devtools'";

            $rt = M()->query($sql);
            $lunch_temp = [];
            $dinner_temp = [];
            foreach($rt as $vv){
                $program_list[$key]['demand_nums_'.$vv['area_id']] +=1;
                $f_time = date('H:i',strtotime($vv['create_time']));
                if($f_time>=$meal_time['lunch'][0] && $f_time<=$meal_time['lunch'][1]){
                    $lunch_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];

                }
                if($f_time>=$meal_time['dinner'][0] && $f_time<=$meal_time['dinner'][1]){
                    $dinner_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];
                }

            }
            foreach($lunch_temp as $kk=>$vv){
                $program_list[$key]['demand_fj_'.$kk] += count($lunch_temp[$kk]);
            }

            foreach($dinner_temp as $kk=>$vv){
                $program_list[$key]['demand_fj_'.$kk] += count($dinner_temp[$kk]);
            }
            $program_list[$key]['resource_cate'] = 3;
            $program_list[$key]['sta_date'] = $yesterday;
            //unset($program_list[$key]['media_id']);
        }

        //商品
        $sql =" select id from savor_smallapp_dishgoods where status=1 and flag=2 and type=22 and gtype in(1,2)";
        $rt = M()->query($sql);
        $goods_id_str = '';
        foreach($rt as $v){
            $goods_id_str .= $spacei.$v['id'];
            $spacei = ',';
        }
        $sql ="select d.id resource_id,m.oss_addr,d.name resource_name,m.id media_id from savor_smallapp_dishgoods d
        left join savor_media m on d.video_intromedia_id = m.id
        where  d.id in($goods_id_str) and m.id>0";

        $goods_list = M()->query($sql);
        //print_r($goods_list);exit;
        foreach($goods_list as $key=>$v){

            foreach($area_info as $vv){
                $goods_list[$key]['demand_nums_'.$vv['area_id']] =0;
                $goods_list[$key]['demand_fj_'.$vv['area_id']] =0;
                $goods_list[$key]['display_num_'.$vv['area_id']] = 0;
            }
            $sql = "select r.id ,r.area_id,r.create_time,box_id
                from savor_smallapp_forscreen_record r
                where resource_id=".$v['resource_id']." and r.action =13 and small_app_id =1 and r.create_time>='".$start_date.
                "' and r.create_time<='".$end_date."' and r.mobile_brand!='devtools'";

            $rt = M()->query($sql);
            $lunch_temp = [];
            $dinner_temp = [];
            foreach($rt as $vv){

                $goods_list[$key]['demand_nums_'.$vv['area_id']] +=1;
                $f_time = date('H:i',strtotime($vv['create_time']));
                if($f_time>=$meal_time['lunch'][0] && $f_time<=$meal_time['lunch'][1]){
                    $lunch_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];

                }
                if($f_time>=$meal_time['dinner'][0] && $f_time<=$meal_time['dinner'][1]){
                    $dinner_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];

                }

            }
            foreach($lunch_temp as $kk=>$vv){
                $goods_list[$key]['demand_fj_'.$kk] += count($lunch_temp[$kk]);
            }

            foreach($dinner_temp as $kk=>$vv){
                $goods_list[$key]['demand_fj_'.$kk] += count($dinner_temp[$kk]);
            }
            $goods_list[$key]['resource_cate'] = 4;
            $goods_list[$key]['sta_date'] = $yesterday;
        }
        //banner商品
        $sql ="SELECT linkcontent FROM savor_smallapp_adsposition WHERE linkcontent LIKE '/pages/hotel/goods/detail?goods_id%' AND `status`=1 ";
        $rt = M()->query($sql);

        $goods_id_str = '';
        foreach($rt as $v){
            $url_info = explode('=', $v['linkcontent']);
            $goods_id = $url_info[1];

            $goods_id_str .= $space.$goods_id;
            $space = ',';
        }
        if(!empty($goods_id_str)){
            $sql ="select d.id resource_id,m.oss_addr,d.name resource_name,m.id media_id from savor_smallapp_dishgoods d
                   left join savor_media m on d.video_intromedia_id = m.id
                   where  d.id in($goods_id_str)";

            $banner_goods_list = M()->query($sql);

            foreach($banner_goods_list as $key=>$v){

                foreach($area_info as $vv){
                    $banner_goods_list[$key]['demand_nums_'.$vv['area_id']] =0;
                    $banner_goods_list[$key]['demand_fj_'.$vv['area_id']] =0;
                    $banner_goods_list[$key]['display_num_'.$vv['area_id']] = 0;
                }
                $sql = "select r.id ,r.area_id,r.create_time,box_id
                    from savor_smallapp_forscreen_record r
                    where resource_id=".$v['resource_id']." and r.action =14 and small_app_id =1 and r.create_time>='".$start_date.
                    "' and r.create_time<='".$end_date."' and r.mobile_brand!='devtools'";

                $rt = M()->query($sql);

                $lunch_temp = [];
                $dinner_temp = [];
                foreach($rt as $vv){

                    $banner_goods_list[$key]['demand_nums_'.$vv['area_id']] +=1;
                    $f_time = date('H:i',strtotime($vv['create_time']));
                    if($f_time>=$meal_time['lunch'][0] && $f_time<=$meal_time['lunch'][1]){
                        $lunch_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];

                    }
                    if($f_time>=$meal_time['dinner'][0] && $f_time<=$meal_time['dinner'][1]){
                        $dinner_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];

                    }

                }
                foreach($lunch_temp as $kk=>$vv){
                    $banner_goods_list[$key]['demand_fj_'.$kk] += count($lunch_temp[$kk]);
                }

                foreach($dinner_temp as $kk=>$vv){
                    $banner_goods_list[$key]['demand_fj_'.$kk] += count($dinner_temp[$kk]);
                }
                $banner_goods_list[$key]['resource_cate'] = 5;
                $banner_goods_list[$key]['sta_date'] = $yesterday;
            }
        }
        //生日歌
        $sql ="select b.media_id resource_id,b.name resource_name,oss_addr,m.id media_id from savor_smallapp_birthday b 
               left join savor_media m on b.media_id=m.id ";

        $happy_list = M()->query($sql);

        foreach($happy_list as $key=>$v){

            foreach($area_info as $vv){
                $happy_list[$key]['demand_nums_'.$vv['area_id']] =0;
                $happy_list[$key]['demand_fj_'.$vv['area_id']] =0;
                $happy_list[$key]['display_num_'.$vv['area_id']] = 0;
            }
            //["forscreen/resource/1625401544424.png"]
            $sql = "select r.id ,r.area_id,r.create_time,box_id
                from savor_smallapp_forscreen_record r
                where imgs='[\"".$v['oss_addr']."\"]' and r.action =56 and small_app_id =1 and r.create_time>='".$start_date.
                "' and r.create_time<='".$end_date."' and r.mobile_brand!='devtools'";


            $rt = M()->query($sql);

            $lunch_temp = [];
            $dinner_temp = [];
            foreach($rt as $vv){

                $happy_list[$key]['demand_nums_'.$vv['area_id']] +=1;
                $f_time = date('H:i',strtotime($vv['create_time']));
                if($f_time>=$meal_time['lunch'][0] && $f_time<=$meal_time['lunch'][1]){
                    $lunch_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];

                }
                if($f_time>=$meal_time['dinner'][0] && $f_time<=$meal_time['dinner'][1]){
                    $dinner_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];

                }

            }
            foreach($lunch_temp as $kk=>$vv){
                $happy_list[$key]['demand_fj_'.$kk] += count($lunch_temp[$kk]);
            }

            foreach($dinner_temp as $kk=>$vv){
                $happy_list[$key]['demand_fj_'.$kk] += count($dinner_temp[$kk]);
            }
            $happy_list[$key]['resource_cate'] = 6;
            $happy_list[$key]['sta_date'] = $yesterday;
        }
        //星座
        $sql ="select m.name resource_name,v.media_id resource_id,c.start_month,c.start_day,
               c.end_month,c.end_day,m.oss_addr,m.id media_id
               from savor_smallapp_constellation c
               left join savor_smallapp_constellation_video v on c.id=v.constellation_id
               left join savor_media m  on v.media_id = m.id
               where c.status=1  order by end_month asc,end_day asc";

        $res = M()->query($sql);

        $month = date('n',strtotime($start_date));
        $day = date('j',strtotime($start_date));
        $now_constellation = 0;
        foreach ($res as $k=>$v){
            if($month==$v['end_month'] && $day<=$v['end_day']){
                $now_constellation = $k;
                break;
            }elseif($month==$v['start_month'] && $day>=$v['start_day']){
                $now_constellation = $k;
                break;
            }
        }
        $total = count($res) ;
        $next_constellation = $now_constellation+1;

        if($next_constellation>=$total){
            $next_constellation = 0;
        }
        $n_next_constellation  = $next_constellation+1;
        if($n_next_constellation>=$total){
            $n_next_constellation = 0;
        }
        $nn_next_constellation = $n_next_constellation +1;
        $constellations = array($res[$now_constellation],$res[$next_constellation],$res[$n_next_constellation],$res[$nn_next_constellation]);
        //print_r($constellations);exit;
        foreach($constellations as $key=>$v){

            foreach($area_info as $vv){
                $constellations[$key]['demand_nums_'.$vv['area_id']] =0;
                $constellations[$key]['demand_fj_'.$vv['area_id']] =0;
                $constellations[$key]['display_num_'.$vv['area_id']] = 0;
            }
            $sql = "select r.id ,r.area_id,r.create_time,box_id
                from savor_smallapp_forscreen_record r
                where imgs='[\"".$v['oss_addr']."\"]' and r.action =57 and small_app_id =1 and r.create_time>='".$start_date.
                "' and r.create_time<='".$end_date."' and r.mobile_brand!='devtools'";
            $rt = M()->query($sql);

            $lunch_temp = [];
            $dinner_temp = [];
            foreach($rt as $vv){
                $constellations[$key]['demand_nums_'.$vv['area_id']] +=1;
                $f_time = date('H:i',strtotime($vv['create_time']));
                if($f_time>=$meal_time['lunch'][0] && $f_time<=$meal_time['lunch'][1]){
                    $lunch_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];

                }
                if($f_time>=$meal_time['dinner'][0] && $f_time<=$meal_time['dinner'][1]){
                    $dinner_temp[$vv['area_id']][$vv['box_id']] = $vv['box_id'];
                }
            }
            foreach($lunch_temp as $kk=>$vv){
                $constellations[$key]['demand_fj_'.$kk] += count($lunch_temp[$kk]);
            }
            foreach($dinner_temp as $kk=>$vv){
                $constellations[$key]['demand_fj_'.$kk] += count($dinner_temp[$kk]);
            }
            $constellations[$key]['resource_cate'] = 7;
            $constellations[$key]['sta_date'] = $yesterday;
        }
        foreach($constellations as $key=>$v){
            if(empty($v['oss_addr'])){
                $constellations[$key]['oss_addr'] = 'media/resource/';
            }
            if(empty($v['resource_name'])){
                $constellations[$key]['resource_name'] = '星座';
            }
            unset($constellations[$key]['start_month']);
            unset($constellations[$key]['start_day']);
            unset($constellations[$key]['end_month']);
            unset($constellations[$key]['end_day']);

        }
        //$data = array_merge($hot_program_list,$hot_user_list,$program_list,$goods_list,$banner_goods_list,$happy_list,$constellations);
        $data = array_merge($hot_program_list,$hot_user_list,$program_list,$goods_list,$happy_list,$constellations);
        foreach($data as $key=>$v){
            $this->addData($v);
        }
        echo date('Y-m-d H:i:s');
    }
}