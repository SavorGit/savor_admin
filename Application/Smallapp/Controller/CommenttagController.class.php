<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 评论标签管理
 *
 */
class CommenttagController extends BaseController {

    public function taglist(){
        $status = I('status',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码


        $m_commenttag = new \Admin\Model\Smallapp\CommenttagModel();
        $where = array();
        if($status){
            $where['status'] = $status;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'a.id desc';
        $res_list = $m_commenttag->getTagList('a.id,a.name,a.type,a.status,hotel.name as hotel_name',$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        foreach ($data_list as $k=>$v){
            if($v['type']==1){
                $type_str = '公共标签';
            }elseif($v['type']==2){
                $type_str = '酒楼自定义标签';
            }else{
                $type_str = '';
            }
            $data_list[$k]['type_str'] = $type_str;
            if($v['status']==1){
                $data_list[$k]['statusstr'] = '可用';
            }else{
                $data_list[$k]['statusstr'] = '不可用';
            }
        }
        $this->assign('status',$status);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function tagadd(){
        $id = I('post.id',0,'intval');
        $m_commenttag = new \Admin\Model\Smallapp\CommenttagModel();
        if(IS_POST){
            $hotel_id = I('hotel_id',0,'intval');
            $status = I('post.status',0,'intval');
            $name = I('post.name','','trim');
            $data = array('name'=>$name,'status'=>$status,'hotel_id'=>$hotel_id);
            if($hotel_id){
                $data['type'] = 2;
            }else{
                $data['type'] = 1;
            }
            if($id){
                $result = $m_commenttag->updateData($data);
            }else{
                $result = $m_commenttag->add($data);
            }
            if($result){
                $this->output('操作成功!', 'commenttag/taglist');
            }else{
                $this->output('操作失败', 'commenttag/taglist',2,0);
            }
        }else{
            $vinfo = array();
            $hotel_id = 0;
            if($id){
                $vinfo = $m_commenttag->getInfo(array('id'=>$id));
                $hotel_id = $vinfo['hotel_id'];
            }
            $m_hotel = new \Admin\Model\HotelModel();
            $where = array('state'=>1,'flag'=>0);
            $field = 'id,name';
            $hotels = $m_hotel->getWhereorderData($where,$field,'area_id asc');
            foreach ($hotels as $k=>$v){
                if($hotel_id && $v['id']==$hotel_id){
                    $hotels[$k]['is_select'] = 'selected';
                }else{
                    $hotels[$k]['is_select'] = '';
                }
            }
            $this->assign('hotels',$hotels);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function tagdel(){
        $id = I('get.id',0,'intval');
        $m_commenttag = new \Admin\Model\Smallapp\CommenttagModel();
        $result = $m_commenttag->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'commenttag/taglist',2);
        }else{
            $this->output('操作失败', 'commenttag/taglist',2,0);
        }
    }

}