<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 助力播放列表
 *
 */
class HelpplayController extends BaseController {

    public function helplist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $openid = I('openid','','trim');
        $is_recommend = I('is_recommend',99,'intval');
        $res_type = I('res_type',0,'intval');
        $status = I('status',99,'intval');
        $playstatus = I('playstatus',0,'intval');

        $m_forscreenhelp = new \Admin\Model\Smallapp\ForscreenhelpModel();
        $m_forscreenhelpuser = new \Admin\Model\Smallapp\ForscreenhelpuserModel();
        $where = array();
        if($status==99){
            $where['p.status'] = array('in','1,2');
        }else{
            $where['p.status'] = $status;
        }
        if($is_recommend!=99){
            $where['p.is_recommend'] = $is_recommend;
        }
        if($res_type){
            $where['p.res_type'] = $res_type;
        }
        if($openid){
            $where['a.openid'] = $openid;
        }
        if($playstatus){
            $where['a.status'] = $playstatus;
        }
        $start = ($pageNum-1)*$size;
        $fields = 'a.id,a.forscreen_record_id,a.openid,p.res_type,p.is_recommend,p.status as status,a.status as play_status,a.add_time';
        $orderby = 'a.id desc';
        $group = 'f.id';
        $res_list = $m_forscreenhelp->getList($fields,$where,$orderby,$group,$start,$size);
        $data_list = array();
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $allplay_status = array(1=>'未播放',2=>'待播放',3=>'播放中',4=>'播放完');
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $res_user = $m_user->getOne('nickName',array('openid'=>$v['openid']));
                $v['nickname'] = $res_user['nickname'];
                if($v['res_type']==1){
                    $v['res_typestr'] = '图片';
                }else{
                    $v['res_typestr'] = '视频';
                }
                if($v['is_recommend']==1){
                    $v['is_recommendstr'] = '已推荐';
                }else{
                    $v['is_recommendstr'] = '未推荐';
                }
                if($v['status']==1){
                    $v['statusstr'] = '待审核';
                }elseif($v['status']==2){
                    $v['statusstr'] = '审核通过';
                }else{
                    $v['statusstr'] = '未审核';
                }
                $v['play_statusstr'] = $allplay_status[$v['play_status']];

                $field = 'count(*) as num';
                $where = array('help_id'=>$v['id']);
                $res_helpnum = $m_forscreenhelpuser->getAll($field,$where,0,1,'id desc');
                if(empty($res_helpnum)){
                    $v['helpnum'] = 0;
                }else{
                    $v['helpnum'] = $res_helpnum[0]['num'];
                }
                $data_list[] = $v;
            }
        }
        $this->assign('allplay_status',$allplay_status);
        $this->assign('openid',$openid);
        $this->assign('playstatus',$playstatus);
        $this->assign('status',$status);
        $this->assign('res_type',$res_type);
        $this->assign('is_recommend',$is_recommend);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function helpdetail(){
        $id = I('get.id',0,'intval');
        $forscreen_record_id = I('get.forscreen_record_id',0,'intval');
        $m_forscreenhelp = new \Admin\Model\Smallapp\ForscreenhelpModel();
        $where = array('a.id'=>$id);
        $fields = 'a.id as help_id,a.forscreen_record_id,a.status as play_status,p.id as pub_id,p.res_type,p.is_recommend,p.forscreen_id,p.status';
        $info = $m_forscreenhelp->getHelpdetail($fields,$where);

        $forscreen_id = $info['forscreen_id'];

        $m_pubdetail = new \Admin\Model\Smallapp\PubdetailModel();
        $oss_host = 'http://'. C('OSS_HOST_NEW').'/';
        $fields = "concat('".$oss_host."',`res_url`) res_url";
        $where = array();
        $where['forscreen_id'] = $forscreen_id;
        $list = $m_pubdetail->getWhere($fields,$where);
        $this->assign('forscreen_record_id',$forscreen_record_id);
        $this->assign('list',$list);
        $this->assign('info',$info);
        $this->display();
    }

    public function setplaytime(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        if(IS_POST){
            $content_play_time = I('post.content_play_time',0,'intval');
            if($content_play_time>120){
                $this->error('播放时间不能大于5天');
            }
            $data = array('config_value'=>$content_play_time);
            $rts = $m_sys_config->editData($data, 'content_play_time');
            if($rts){
                $sys_list = $m_sys_config->getList(array('status'=>1));
                $redis  =  \Common\Lib\SavorRedis::getInstance();
                $redis->select(12);
                $cache_key = C('SYSTEM_CONFIG');
                $redis->set($cache_key, json_encode($sys_list));
                $this->output('操作成功','helpplay/helplist');
            }else {
                $this->error('操作失败');
            }
        }else{
            $where = " config_key in('content_play_time')";
            $volume_arr = $m_sys_config->getList($where);
            $info = array();
            foreach($volume_arr as $v){
                $info[$v['config_key']] = $v['config_value'];
            }
            $this->assign('info',$info);
            $this->display();
        }
    }

    public function operateStatus(){
        $id = I('post.id',0,'intval');
        $type = I('post.type',0,'intval');//1推荐 2审核 3播放
        $is_recommend = I('post.is_recommend',0,'intval');
        $status = I('post.status',0,'intval');
        $play_status = I('post.play_status',0,'intval');
        $m_public = new \Admin\Model\Smallapp\PublicModel();
        switch ($type){
            case 1:
                $where = array('id'=>$id);
                $ret = $m_public->updateInfo($where, array('is_recommend'=>$is_recommend));
                break;
            case 2:
                $where = array('id'=>$id);
                $ret = $m_public->updateInfo($where, array('status'=>$status));
                break;
            case 3:
                $pub_id = I('post.pub_id',0,'intval');
                $forscreen_record_id = I('post.forscreen_record_id',0,'intval');
                $res_public = $m_public->getOne('*',array('id'=>$pub_id));
                if($res_public['is_recommend']!=1 || $res_public['status']!=2){
                    $this->outputNew('请先推荐和审核', 'helpplay/helpdetail',3);
                }
                $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
                $fields = 'id,forscreen_id,resource_id,openid,imgs,duration,md5_file';
                $res_forscreen = $m_forscreen->getOne($fields,array('id'=>$forscreen_record_id));
                if(empty($res_forscreen)){
                    $this->outputNew('投屏内容不存在', 'helpplay/helpdetail',3);
                }
                $res_forscreen['help_id'] = $id;
                $content_key = C('SAPP_SELECTCONTENT_CONTENT');
                $redis  =  \Common\Lib\SavorRedis::getInstance();
                $redis->select(5);
                $res_cache = $redis->get($content_key);
                if(!empty($res_cache)){
                    $content_data = json_decode($res_cache,true);
                }else{
                    $content_data = array();
                }
                if($play_status==2){
                    $content_data[$id] = $res_forscreen;
                }elseif($play_status==1){
                    if(isset($content_data[$id])){
                        unset($content_data[$id]);

                        $redis->select(5);
                        $allkeys  = $redis->keys('smallapp:selectcontent:program:*');
                        foreach ($allkeys as $program_key){
                            $period = getMillisecond();
                            $redis->set($program_key,$period);
                        }
                    }
                }
                $redis->set($content_key,json_encode($content_data));

                $where = array('id'=>$id);
                $m_forscreenhelp = new \Admin\Model\Smallapp\ForscreenhelpModel();
                $ret = $m_forscreenhelp->updateData($where, array('status'=>$play_status));
                break;
            default:
                $ret = false;
        }
        if($ret){
            echo 1;
        }else {
            echo 0;
        }
    }

}