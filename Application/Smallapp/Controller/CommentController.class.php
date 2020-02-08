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
        $start  = ($page-1) * $size;
        $m_comment  = new \Admin\Model\Smallapp\CommentModel();
        $fields = 'a.staff_id,a.user_id,a.score,a.content,a.status,user.nickName as staff_name,user.avatarUrl as staff_url,
        staff.hotel_id,staff.room_id,hotel.name as hotel_name,area.region_name as area_name';
        $result = $m_comment->getCommentList($fields,$where, 'a.id desc', $start, $size);
        $datalist = $result['list'];
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_commenttag = new \Admin\Model\Smallapp\CommenttagModel();
        $m_commenttagids = new \Admin\Model\Smallapp\CommenttagidsModel();
        $redis = new \Common\Lib\SavorRedis();
        $redis->select(15);
        foreach ($datalist as $k=>$v){
            $res_user = $m_user->getOne('openid',array('id'=>$v['user_id']),'id desc');
            $datalist[$k]['user_openid'] = $res_user['openid'];
            if($v['status']==1){
                $datalist[$k]['status_str'] = '正常显示';
            }else{
                $datalist[$k]['status_str'] = '禁止显示';
            }
            $cache_key = 'savor_room_'.$v['room_id'];
            $redis_room_info = $redis->get($cache_key);
            $room_info = json_decode($redis_room_info, true);
            $datalist[$k]['room_name'] = $room_info['name'];

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