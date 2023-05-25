<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class HotelinvitationconfigController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function datalist(){
        $keywords = I('keywords','','trim');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $where = array();
        if(!empty($keywords)){
            $where['hotel_name'] = array('like',"%$keywords%");
        }
        $start  = ($page-1) * $size;
        $m_hotelinvitation  = new \Admin\Model\Smallapp\HotelInvitationConfigModel();
        $result = $m_hotelinvitation->getDataList('*',$where, 'id desc', $start, $size);
        $oss_host = get_oss_host();
        foreach ($result['list'] as $k=>$v){
            $is_open_sellplatform_str = '否';
            if($v['is_open_sellplatform']==1){
                $is_open_sellplatform_str = '是';
            }
            $bg_img = '';
            if(!empty($v['bg_img'])){
                $bg_img = $oss_host.$v['bg_img'];
            }
            $result['list'][$k]['bg_img'] = $bg_img;
            $result['list'][$k]['is_open_sellplatform_str'] = $is_open_sellplatform_str;
        }

        $this->assign('datalist', $result['list']);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('keywords',$keywords);
        $this->display('');
    }

    public function configadd(){
        $id = I('id',0,'intval');

        $m_hotelinvitation  = new \Admin\Model\Smallapp\HotelInvitationConfigModel();
        $m_hotel = new \Admin\Model\HotelModel();
        if(IS_POST){
            $hotel_id = I('post.hotel_id',0,'intval');
            $media_id = I('post.media_id',0,'intval');
            $theme_color = I('post.theme_color','','trim');
            $theme_contrast_color = I('post.theme_contrast_color','','trim');
            $pain_color = I('post.pain_color','','trim');
            $weak_color = I('post.weak_color','','trim');
            $is_open_sellplatform = I('post.is_open_sellplatform',0,'intval');
            $is_view_wine_switch  = I('post.is_view_wine_switch',0,'intval');

            if(empty($hotel_id)){
                $this->output('请选择酒楼', 'hotelinvitationconfig/configadd', 2, 0);
            }
            $rwhere = array('hotel_id'=>$hotel_id);
            if($id){
                $rwhere['id'] = array('neq',$id);
            }
            $res_rdata = $m_hotelinvitation->getInfo($rwhere);
            if(!empty($res_rdata)){
                $this->output('当前酒楼已存在', 'hotelinvitationconfig/configadd', 2, 0);
            }

            $res_hotel = $m_hotel->getOne($hotel_id);
            $hotel_name = $res_hotel['name'];
            $data = array('hotel_id'=>$hotel_id,'hotel_name'=>$hotel_name,'theme_color'=>$theme_color,
                'theme_contrast_color'=>$theme_contrast_color,'pain_color'=>$pain_color,'weak_color'=>$weak_color,
                'is_open_sellplatform'=>$is_open_sellplatform,'is_view_wine_switch'=>$is_view_wine_switch
            );
            if($media_id){
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getMediaInfoById($media_id);
                $data['bg_img'] = $res_media['oss_path'];
            }
            if($id){
                $m_hotelinvitation->updateData(array('id'=>$id),$data);
            }else{
                $m_hotelinvitation->add($data);
            }
            $this->output('操作成功', 'hotelinvitationconfig/datalist');
        }else{
            $vinfo = C('INVITATION_HOTEL_CONFIG');
            $vinfo['hotel_id']=0;
            if($id){
                $vinfo = $m_hotelinvitation->getInfo(array('id'=>$id));
            }
            $oss_host = get_oss_host();
            $vinfo['bg_img'] = $oss_host.$vinfo['bg_img'];

            $where = array('flag'=>0,'state'=>1);
            $hlist = $m_hotel->getWhereorderData($where,'id,name','id desc');
            foreach ($hlist as $k=>$v){
                $is_select = '';
                if($vinfo['hotel_id']==$v['id']){
                    $is_select = 'selected';
                }
                $hlist[$k]['is_select'] = $is_select;
            }
            $this->assign('hlist',$hlist);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function configdel(){
    	$id = I('get.id', 0, 'intval');
        $m_hotelinvitation  = new \Admin\Model\Smallapp\HotelInvitationConfigModel();
    	$condition = array('id'=>$id);
    	$result = $m_hotelinvitation->delData($condition);
    	if($result){
    		$this->output('删除成功', '',2);
    	}else{
    		$this->output('删除失败', '',2);
    	}
    }
}