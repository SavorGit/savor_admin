<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController;

/**
 * @desc 评论管理
 *
 */
class CommentController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }

    public function commentlist() {
        $area_id = I('area_id',0,'intval');
        $start_date = I('post.start_date','');
        $end_date = I('post.end_date','');
        $page = I('pageNum',1);
        $size = I('numPerPage',50);
        $status = I('status',0,'intval');
        $hotel_name = I('hotel_name','','trim');

        $where = array();
        if($area_id)    $where['area.id']=$area_id;
        if($status)     $where['a.status']=$status;
        if(!empty($hotel_name)) $where['hotel.name'] = array('like',"%$hotel_name%");

        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'goods/goodsadd', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['a.add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }
        $where['box.state'] = 1;
        $where['box.flag'] = 0;
        $start  = ($page-1) * $size;
        $m_comment  = new \Admin\Model\Smallapp\CommentModel();
        $fields = 'a.staff_id,a.id,a.user_id,a.score,a.content,a.status,a.add_time,a.box_mac,a.label,a.satisfaction,
        hotel.id as hotel_id,room.name as room_name,hotel.name as hotel_name,area.region_name as area_name';
        $result = $m_comment->getCommentList($fields,$where, 'a.id desc', $start, $size);
        $datalist = $result['list'];
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_commenttag = new \Admin\Model\Smallapp\TagsModel();
        $m_commenttagids = new \Admin\Model\Smallapp\CommenttagidsModel();
        $m_staff = new \Admin\Model\Integral\StaffModel();
        $comment_cacsi = array(
            '1'=>'很糟糕',
            '2'=>'一般般',
            '3'=>'太赞了',
        );
        foreach ($datalist as $k=>$v){
            $staff_name = $staff_url = '';
            if($v['staff_id']>0){
                $res_staff = $m_staff->getInfo(array('id'=>$v['staff_id']));
                if(!empty($res_staff)){
                    $res_user = $m_user->getOne('avatarUrl,nickName',array('openid'=>$res_staff['openid']));
                    $staff_name = $res_user['nickname'];
                    $staff_url = $res_user['avatarurl'];
                }
            }
            $datalist[$k]['satisfaction_str'] = $comment_cacsi[$v['satisfaction']];
            $datalist[$k]['staff_name'] = $staff_name;
            $datalist[$k]['staff_url'] = $staff_url;
            $res_user = $m_user->getOne('openid',array('id'=>$v['user_id']),'id desc');
            $datalist[$k]['user_openid'] = $res_user['openid'];
            if($v['status']==1){
                $datalist[$k]['status_str'] = '正常显示';
            }else{
                $datalist[$k]['status_str'] = '禁止显示';
            }
            $res_tagids = $m_commenttagids->getDataList('tag_id',array('comment_id'=>$v['id']),'id asc');
            $tag_str = '';
            if(!empty($res_tagids)){
                $tags = array();
                foreach ($res_tagids as $tv){
                    $tags[]=$tv['tag_id'];
                }
                $tag_where = array('id'=>array('in',$tags));
                $res_tags = $m_commenttag->getDataList('name',$tag_where,'');
                $tags = array();
                foreach ($res_tags as $tagv){
                    $tags[]=$tagv['name'];
                }
                $tag_str = join(',',$tags);
            }
            $datalist[$k]['tag_str']=$tag_str;
        }

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $this->assign('area_id',$area_id);
        $this->assign('area',$area_arr);
        $this->assign('status',$status);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('commentlist');
    }


    public function changestatus(){
        $id = I('get.id',0,'intval');
        $status = I('get.status',0,'intval');

        $m_comment = new \Admin\Model\Smallapp\CommentModel();
        $result = $m_comment->updateData(array('id'=>$id),array('status'=>$status));
        if($result){
            $this->output('操作成功!', 'comment/commentlist',2);
        }else{
            $this->output('操作失败', 'comment/commentlist',2,0);
        }
    }

}