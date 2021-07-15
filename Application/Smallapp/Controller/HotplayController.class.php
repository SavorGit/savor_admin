<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 热播内容管理
 *
 */
class HotplayController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $status = I('status',0,'intval');
        $type = I('type',0,'intval');

        $m_hotplay = new \Admin\Model\Smallapp\HotplayModel();
        $where = array();
        if($type)   $where['type']=$type;
        if($status)   $where['status']=$status;

        $start = ($pageNum-1)*$size;
        $orderby = 'sort desc';
        $res_list = $m_hotplay->getDataList('*',$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_ads = new \Admin\Model\AdsModel();
        $m_media = new \Admin\Model\MediaModel();
        $oss_host = 'http://'.C('OSS_HOST_NEW').'/';
        $all_types = C('hot_play_types');
        foreach ($data_list as $k=>$v){
            $nickname = $avatarurl = '';
            if($v['type']==1){
                $res_forscreen = $m_forscreen->getInfo(array('id'=>$v['forscreen_record_id']));
                $res_type = $res_forscreen['resource_type'];
                $where = array('openid'=>$res_forscreen['openid']);
                $fields = 'id user_id,avatarUrl,nickName';
                $res_user = $m_user->getOne($fields, $where);
                $nickname = $res_user['nickname'];
                $avatarurl = $res_user['avatarurl'];

                $fields_forscreen = 'imgs,duration,resource_size,resource_id,md5_file';
                $all_forscreen = $m_forscreen->getDataList($fields_forscreen,array('forscreen_id'=>$res_forscreen['forscreen_id']),'id asc');
                $imgs_info = json_decode($all_forscreen[0]['imgs'],true);
                $forscreen_url = $oss_host.$imgs_info[0];
                $md5_str = '正常';
                if($res_forscreen['resource_type']==1){
                    $resource_type_str = '图片';
                    $img_url = $forscreen_url."?x-oss-process=image/quality,Q_50";
                    foreach ($all_forscreen as $av){
                        if(empty($av['md5_file'])){
                            $md5_str = '异常';
                        }
                    }
                }else{
                    if(empty($all_forscreen[0]['md5_file'])){
                        $md5_str = '异常';
                    }
                    $resource_type_str = '视频';
                    $img_url = $forscreen_url.'?x-oss-process=video/snapshot,t_3000,f_jpg,w_450,m_fast';
                }
            }else{
                $condition = array('id'=>$v['data_id']);
                $res_ads = $m_ads->getInfo($condition);
                $res_media = $m_media->getInfo(array('id'=>$res_ads['media_id']));
                if($res_ads['resource_type']==1){
                    $res_type = 2;
                    $resource_type_str = '视频';
                    $img_url = $oss_host.$res_media['oss_addr'].'?x-oss-process=video/snapshot,t_3000,f_jpg,w_450,m_fast';
                }else{
                    $resource_type_str = '图片';
                    $res_type = 1;
                    $img_url = $oss_host.$res_media['oss_addr']."?x-oss-process=image/quality,Q_50";
                }
                if(!empty($res_media['md5'])){
                    $md5_str = '正常';
                }else{
                    $md5_str = '异常';
                }
            }
            if($v['media_id']){
                $res_media = $m_media->getMediaInfoById($v['media_id']);
                $img_url = $res_media['oss_addr']."?x-oss-process=image/quality,Q_50";
            }
            $data_list[$k]['nickname'] = $nickname;
            $data_list[$k]['avatarurl'] = $avatarurl;
            $data_list[$k]['res_type'] = $res_type;
            $data_list[$k]['type_str'] = $all_types[$v['type']];
            $data_list[$k]['md5_str'] = $md5_str;
            $data_list[$k]['resource_type_str'] = $resource_type_str;
            $data_list[$k]['img'] = $img_url;
        }
        $this->assign('type',$type);
        $this->assign('status',$status);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function addhotplay(){
        if(IS_POST){
            $type = I('post.type',1,'intval');//1用户内容,2广告,3节目
            $media_id = I('post.media_id',0,'intval');
            $data_id = I('post.data_id',0,'intval');
            $init_playnum = I('post.init_playnum',0,'intval');
            $sort = I('post.sort',0,'intval');
            $status = I('post.status',0,'intval');

            $forscreen_record_id = 0;
            if($type==1){
                $m_public = new \Admin\Model\Smallapp\PublicModel();
                $res_public = $m_public->getOne('forscreen_id',array('id'=>$data_id),'id desc');
                $forscreen_id = intval($res_public['forscreen_id']);
                $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
                $fields_forscreen = 'id';
                $all_forscreen = $m_forscreen->getDataList($fields_forscreen,array('forscreen_id'=>$forscreen_id),'id asc');
                if(empty($all_forscreen)){
                    $this->output('请重新输入内容审核ID', 'hotplay/datalist',2,0);
                }
                $forscreen_record_id = $all_forscreen[0]['id'];
            }else{
                $m_ads = new \Admin\Model\AdsModel();
                $condition = array('id'=>$data_id,'state'=>1);
                $res_ads = $m_ads->getInfo($condition);
                if(empty($res_ads)){
                    $this->output('请重新输入广告节目ID', 'hotplay/datalist',2,0);
                }
            }
            $add_data = array('data_id'=>$data_id,'forscreen_record_id'=>$forscreen_record_id,'media_id'=>$media_id,'init_playnum'=>$init_playnum,
            'sort'=>$sort,'type'=>$type,'status'=>$status);
            $m_hoteplay = new \Admin\Model\Smallapp\HotplayModel();
            $res = $m_hoteplay->add($add_data);
            if($res && $status==1){
                $redis = \Common\Lib\SavorRedis::getInstance();
                $redis->select(5);
                $key_demand = C('SAPP_HOTPLAYDEMAND');
                $period = getMillisecond();
                $redis->set($key_demand,$period);
            }
            $this->output('操作成功', 'hotplay/datalist');
        }else{
            $this->display();
        }
    }

    public function edit(){
        $id = I('id',0,'intval');
        $m_hotplay = new \Admin\Model\Smallapp\HotplayModel();
        $res_info = $m_hotplay->getInfo(array('id'=>$id));

        if(IS_POST){
            $type = I('post.type',1,'intval');//1用户内容,2广告,3节目
            $media_id = I('post.media_id',0,'intval');
            $data_id = I('post.data_id',0,'intval');
            $init_playnum = I('post.init_playnum',0,'intval');
            $sort = I('post.sort',0,'intval');
            $status = I('post.status',0,'intval');

            $up_data = array('media_id'=>$media_id,'init_playnum'=>$init_playnum,'sort'=>$sort);

            $forscreen_record_id = 0;
            if($res_info['type']!=$type || $res_info['data_id']!=$data_id || $res_info['status']!=$status){
                if($type==1){
                    $m_public = new \Admin\Model\Smallapp\PublicModel();
                    $res_public = $m_public->getOne('forscreen_id',array('id'=>$data_id),'id desc');
                    $forscreen_id = intval($res_public['forscreen_id']);
                    $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
                    $fields_forscreen = 'id';
                    $all_forscreen = $m_forscreen->getDataList($fields_forscreen,array('forscreen_id'=>$forscreen_id),'id asc');
                    if(empty($all_forscreen)){
                        $this->output('请重新输入内容审核ID', 'hotplay/datalist',2,0);
                    }
                    $forscreen_record_id = $all_forscreen[0]['id'];
                }else{
                    $m_ads = new \Admin\Model\AdsModel();
                    $condition = array('id'=>$data_id,'state'=>1);
                    $res_ads = $m_ads->getInfo($condition);
                    if(empty($res_ads)){
                        $this->output('请重新输入广告节目ID', 'hotplay/datalist',2,0);
                    }
                }
                $up_data['data_id'] = $data_id;
                $up_data['forscreen_record_id'] = $forscreen_record_id;
                $up_data['type'] = $type;
                $up_data['status'] = $status;
                $up_data['update_time'] = date('Y-m-d H:i:s');
            }
            $res = $m_hotplay->updateData(array('id'=>$id),$up_data);
            if($res){
                $redis = \Common\Lib\SavorRedis::getInstance();
                $redis->select(5);
                $key_demand = C('SAPP_HOTPLAYDEMAND');
                $period = getMillisecond();
                $redis->set($key_demand,$period);

                $this->output('操作成功!', 'hotplay/datalist');
            }else{
                $this->output('操作失败', 'hotplay/datalist',2,0);
            }
        }else{
            $oss_host = 'http://'.C('OSS_HOST_NEW').'/';
            if($res_info['media_id']){
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getMediaInfoById($res_info['media_id']);
                $res_info['oss_addr'] = $res_media['oss_addr'];
            }
            if($res_info['type']==1){
                $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
                $res_forscreen = $m_forscreen->getInfo(array('id'=>$res_info['forscreen_record_id']));
                $res_type = $res_forscreen['resource_type'];
                $fields_forscreen = 'imgs,duration,resource_size,resource_id';
                $all_forscreen = $m_forscreen->getDataList($fields_forscreen,array('forscreen_id'=>$res_forscreen['forscreen_id']),'id asc');
                $list = array();
                foreach ($all_forscreen as $dv){
                    $imgs_info = json_decode($dv['imgs'],true);
                    $forscreen_url = $imgs_info[0];
                    $img_url = $oss_host.$forscreen_url;
                    $list[]=array('res_url'=>$img_url);
                }
            }else{
                $m_ads = new \Admin\Model\AdsModel();
                $condition = array('id'=>$res_info['data_id'],'state'=>1);
                $res_ads = $m_ads->getInfo($condition);
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getMediaInfoById($res_ads['media_id']);
                $img_url = $res_media['oss_addr'];
                if($res_ads['resource_type']==1){
                    $res_type = 2;
                }else{
                    $res_type = 1;
                }
                $list = array();
                $list[]=array('res_url'=>$img_url);
            }
            $this->assign('vinfo',$res_info);
            $this->assign('res_type',$res_type);
            $this->assign('list',$list);
            $this->display();
        }
    }



}