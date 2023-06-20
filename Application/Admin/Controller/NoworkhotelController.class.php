<?php
namespace Admin\Controller;

class NoworkhotelController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keywords = I('keywords','','trim');
        $no_work_type = I('no_work_type',0,'intval');
        $area_id = I('area_id',0,'intval');

        $m_area  = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        $area_arr = array();
        foreach ($res_area as $v){
            $area_arr[$v['id']]=$v;
        }

        $where = array('a.htype'=>20);
        if($area_id){
            $where['a.area_id'] = $area_id;
        }
        if($keywords){
            $where['a.name'] = array('like',"%$keywords%");
        }
        if($no_work_type){
            $where['a.no_work_type'] = $no_work_type;
        }
        $start = ($pageNum-1)*$size;
        $m_hotel = new \Admin\Model\HotelModel();
        $fields = 'a.id,a.name,a.addr,a.area_id,area.region_name as area_name,a.no_work_type,a.contractor,a.mobile,a.create_time,
        signhotel.sign_progress_id,signhotel.start_time,signhotel.end_time';
        $res_list = $m_hotel->getNoworkList($fields,$where,'a.id desc',$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $all_types = C('NO_WORK_TYPES');
            $all_process = C('SIGN_PROCESS');
            foreach ($res_list['list'] as $v){
                $process = '';
                if(!empty($v['sign_progress_id']) && $v['sign_progress_id']>=1){
                    $process = $all_process[$v['sign_progress_id']]['percent'].'%';
                }
                $v['no_work_type_str'] = $all_types[$v['no_work_type']];
                $v['process'] = $process;
                $data_list[] = $v;
            }
        }

        $this->assign('area_id', $area_id);
        $this->assign('area', $area_arr);
        $this->assign('no_work_type',$no_work_type);
        $this->assign('keywords',$keywords);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function hoteladd(){
        $hotel_id = I('id',0,'intval');
        $m_hotel = new \Admin\Model\HotelModel();
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $hotel_cover_media_id = I('post.hotel_cover_media_id',0,'intval');
            $addr = I('post.addr','','trim');
            $area_id = I('post.area_id',0,'intval');
            $county_id = I('post.county_id',0,'intval');
            $business_circle_id = I('post.business_circle_id',0,'intval');
            $contractor = I('post.contractor','','trim');
            $mobile = I('post.mobile','','trim');
            $tel = I('post.tel','','trim');
            $food_style_id = I('post.food_style_id',0,'intval');
            $avg_expense = I('post.avg_expense',0,'intval');
            $gps = I('post.gps','','trim');
            $dp_comment_num = I('post.dp_comment_num',0,'intval');
            $htype = I('post.htype',0,'intval');

            $add_hotel_data = array('name'=>$name,'addr'=>$addr,'area_id'=>$area_id,'county_id'=>$county_id,'gps'=>$gps,
                'business_circle_id'=>$business_circle_id,'contractor'=>$contractor,'mobile'=>$mobile,'tel'=>$tel,'htype'=>$htype);
            $m_hotel->updateData(array('id'=>$hotel_id),$add_hotel_data);
            $add_ext_data = array('hotel_cover_media_id'=>$hotel_cover_media_id,'food_style_id'=>$food_style_id,'avg_expense'=>$avg_expense,
                'dp_comment_num'=>$dp_comment_num);
            $m_hotel_ext->updateData(array('hotel_id'=>$hotel_id),$add_ext_data);

            $this->output('操作成功!', 'noworkhotel/datalist');
        }else{
            $vinfo = $m_hotel->where(array('id'=>$hotel_id))->find();
            $ext_info = $m_hotel_ext->where(array('hotel_id'=>$hotel_id))->find();
            $m_media = new \Admin\Model\MediaModel();
            if(!empty($ext_info['hotel_cover_media_id'])){
                $media_info = $m_media->getMediaInfoById($ext_info['hotel_cover_media_id']);
                $vinfo['hotel_cover_url'] = $media_info['oss_addr'];
                $vinfo['hotel_cover_media_id'] = $ext_info['hotel_cover_media_id'];
            }
            $business_circles = array();
            if($vinfo['area_id'] && $vinfo['county_id']){
                $m_circles  = new \Admin\Model\BusinessCircleModel();
                $where = array('area_id'=>$vinfo['area_id'],'county_id'=>$vinfo['county_id'],'status'=>1);
                $business_circles = $m_circles->getDataList('id,name',$where,'id desc');
                if(!empty($business_circles)){
                    $tmp_data = array(array('id'=>0,'name'=>'无'));
                    $business_circles = array_merge($tmp_data,$business_circles);
                }
            }
            $vinfo['food_style_id'] = $ext_info['food_style_id'];
            $vinfo['avg_expense']   = $ext_info['avg_expense'];
            $vinfo['dp_comment_num']   = $ext_info['dp_comment_num'];

            //获取区/县id
            $area_id = $vinfo['area_id'];
            $m_area_info = new \Admin\Model\AreaModel();
            $parent_id = $this->getParentAreaid($area_id);
            $county_list = $m_area_info->getWhere('id,region_name',array('parent_id'=>$parent_id));
            $area = $m_area_info->getAllArea();
            $m_food_style = new \Admin\Model\FoodStyleModel();
            $food_style_list = $m_food_style->getWhere('id,name', array('status'=>1));
            $this->assign('area',$area);
            $this->assign('county_list',$county_list);
            $this->assign('circle_list',$business_circles);
            $this->assign('food_style_list',$food_style_list);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

}