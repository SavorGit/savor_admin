<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

/**
 * @desc 数据报表
 *
 */
class ForscreendemandconController extends BaseController {
    public function index(){
        
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','resource_cate,resource_id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        
        
        //资源类型
        $forscreen_resource_cate = C('FORSCREEN_CONTENT_RECOURCE_CATE');
        //$forscreen_resource_cate = array('1'=>'节目',"2"=>'热播内容节目','3'=>'点播商品视频','4'=>'点播banner商品视频','5'=>'点播生日歌','点播星座视频');
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        $this->assign('forscreen_resource_cate',$forscreen_resource_cate);
        $this->assign('area', $area_arr);
        $yesday =  date("Y-m-d",strtotime("-1 day"));
        $m_forscreen_demandcontent = new \Admin\Model\Smallapp\ForscreendemandcontentModel();
        
        if(IS_POST){
            $area_id = I('post.area_id',0,'intval');
            $resource_cate = I('post.resource_cate',0,'intval');
            $resource_name = I('post.resource_name','trim');
            $start_date = I('post.start_date');
            $end_date   = I('post.end_date');
            if($start_date>$end_date){
                $this->error('开始时间不能大于结束时间');
            }
            
            /* if($resource_name==''){
             $this->error('请输入资源名称');
             } */
            $this->assign('resource_cate',$resource_cate);
            $this->assign('resource_name',$resource_name);
            $start_date_str = str_replace('-', '', $start_date);
            $end_date_str   = str_replace('-', '', $end_date);
            
            $where =  ' 1 ';
            /* if($area_id){
                $where .=" and area_id =$area_id";
            } */
            if($resource_cate){
                $where .=" and resource_cate=$resource_cate";
            }
            
            if($resource_name!=''){
                $where .=' and resource_name ="'.$resource_name.'"';
            }
            
            $fields = ' resource_id,resource_name,resource_cate,oss_addr,media_id';
            
            $where .=" and  sta_date>='".$start_date."' and sta_date<='".$end_date."'";
            $group = 'resource_id,resource_cate';
            
            
            
            $result = $m_forscreen_demandcontent->getList($fields,$where,$group, $order, $start,$size);
            
            
            
        }else {
            $start_date = $yesday;
            $end_date   = $yesday;
            $start_date_str = str_replace('-', '', $start_date);
            $end_date_str   = str_replace('-', '', $end_date);
            $where =  ' 1 ';
            /* if($resource_name!=''){
             $where .=' and resource_name like "%'.$resource_name.'%"';
             } */
            $fields = ' resource_id,resource_name,resource_cate,oss_addr,media_id';
            $where .=" and  sta_date>='".$start_date."' and sta_date<='".$end_date."'";
            $group = 'resource_id,resource_cate';
            $result = $m_forscreen_demandcontent->getList($fields,$where,$group, $order, $start,$size);
            
        }
        $m_sta = new \Admin\Model\MediaStaModel();
        $play_where = ' 1';
        
        
        $datalist = $result['list'];
        foreach($datalist as $key=>$v){
            $datalist[$key]['resource_cate_name'] = $forscreen_resource_cate[$v['resource_cate']];
            
            if($v['resource_cate'] ==1 || $v['resource_cate']==3){ //热播节目/节目
                //播放次数
                $play_where = ' 1 ';
                $play_where .= ' and media_id='.$v['media_id'];
                if($area_id){
                    $paly_where .=" and area_id=$area_id";
                }
                $play_where .= " and play_date>=".$start_date_str." and play_date<=".$end_date_str;
                
                $fields = " sum(play_count) as pc";
                $rt = $m_sta->field($fields)->where($play_where)->select();
                if(empty($rt[0]['pc'])){
                    $datalist[$key]['play_nums'] = 0;
                }else {
                    $datalist[$key]['play_nums'] = $rt[0]['pc'];
                }
            }else {
                $datalist[$key]['play_nums'] = 0;
            }
            
            
            //点播次数
            if(empty($area_id)){
                $fields = " sum(`demand_nums_1` + `demand_nums_9`+ `demand_nums_236`+`demand_nums_246`)  dumand_nums ,
                            sum(`demand_fj_1`+`demand_fj_9`+`demand_fj_236`+`demand_fj_246`) demand_fj,
                            sum(`display_num_1`+`display_num_9`+`display_num_236`+`display_num_246`) display_num"; 
                
            }else {
                foreach($area_arr as $vv){
                    if($area_id == $vv['id']){
                        $fields = " sum(demand_nums_".$vv['id'].")  dumand_nums ,sum(demand_fj_".$vv['id'].") demand_fj,
                            sum(display_num_".$vv['id'].") display_num"; 
                        break;
                    }
                }
            }
            
            
            
            $dm_where = '1';
            
            $dm_where .= ' and resource_id='.$v['resource_id'];
            $dm_where .= ' and resource_cate='.$v['resource_cate'];
            
            
            $dm_where .= " and sta_date>='".$start_date."' and sta_date<='".$end_date."'";
            $rt = $m_forscreen_demandcontent->field($fields)->where($dm_where)->find();
            
            $datalist[$key]['demand_nums'] = $rt['dumand_nums'];
            $datalist[$key]['demand_fj'] = $rt['demand_fj'];
            $datalist[$key]['display_num'] = $rt['display_num'];
            
            
        }
        $this->assign('area_id',$area_id);
        $this->assign('resource_cate',$resource_cate);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('oss_host',C('OSS_HOST_NEW'));
        
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('sta_start_date','2021-08-04');
        $this->assign('sta_end_date',$yesday);
        $this->display();
    }
    
}