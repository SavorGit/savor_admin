<?php
namespace Admin\Controller;

class HoteldrinksController extends BaseController {

    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->oss_host = get_oss_host();
    }

    public function datalist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $hotel_id = I('hotel_id',0,'intval');

        $where = array('hotel_id'=>$hotel_id,'type'=>2);

        $orders = 'id desc';
        $start  = ($page-1) * $size;
        $m_hoteldrinks = new \Admin\Model\HoteldrinksModel();

        $result = $m_hoteldrinks->getDataList('*',$where,$orders,$start,$size);
        $datalist = $result['list'];
        foreach ($datalist as $k=>$v){
            $content = '当前餐厅无在售酒水';
            if(!empty($v['image'])){
                $v['image'] = $this->oss_host.$v['image'];
                $content = '';
            }
            $v['content'] = $content;
            $datalist[$k] = $v;
        }

        $this->assign('datalist', $datalist);
        $this->assign('hotel_id',$hotel_id);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function drinksadd(){
        $id = I('id',0,'intval');
        $hotel_id = I('hotel_id',0,'intval');
        $m_hoteldrinks = new \Admin\Model\HoteldrinksModel();
        if(IS_POST){
            $media_id = I('post.media_id',0,'intval');
            $is_nosell = I('post.is_nosell',0,'intval');

            if($is_nosell==0 && $media_id==0){
                $this->output('请上传酒水图片', 'hoteldrinks/drinksadd',2,0);
            }
            $image = '';
            if($media_id){
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getMediaInfoById($media_id);
                $image = $res_media['oss_path'];
            }

            $userInfo = session('sysUserInfo');
            $data = array('hotel_id'=>$hotel_id,'image'=>$image,'type'=>2,'sysuser_id'=>$userInfo['id']);
            if($id){
                $result = $m_hoteldrinks->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_hoteldrinks->add($data);
            }
            if($result){
                $this->output('操作成功!', 'hoteldrinks/datalist');
            }else{
                $this->output('操作失败', 'hoteldrinks/drinksadd',2,0);
            }
        }else{
            $vinfo = array('is_nosell'=>0);
            if($id){
                $vinfo = $m_hoteldrinks->getInfo(array('id'=>$id));
            }
            $this->assign('hotel_id',$hotel_id);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }
}
