<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 热播内容管理
 *
 */
class HotplayController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',8,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_playlog = new \Admin\Model\Smallapp\PlaylogModel();
        $where = array('type'=>4);
        $start = ($pageNum-1)*$size;
        $orderby = 'nums desc';
        $res_list = $m_playlog->getDataList('*',$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $oss_host = 'http://'.C('OSS_HOST_NEW').'/';
        foreach ($data_list as $k=>$v){
            $res_forscreen = $m_forscreen->getInfo(array('id'=>$v['res_id']));
            $data_list[$k]['res_type'] = $res_forscreen['resource_type'];
            $where = array('openid'=>$res_forscreen['openid']);
            $fields = 'id user_id,avatarUrl,nickName';
            $res_user = $m_user->getOne($fields, $where);
            $data_list[$k]['nickname'] = $res_user['nickname'];
            $data_list[$k]['avatarurl'] = $res_user['avatarurl'];
            $fields_forscreen = 'imgs,duration,resource_size,resource_id';
            $all_forscreen = $m_forscreen->getDataList($fields_forscreen,array('forscreen_id'=>$res_forscreen['forscreen_id']),'id asc');
            $imgs_info = json_decode($all_forscreen[0]['imgs'],true);
            $forscreen_url = $oss_host.$imgs_info[0];
            if($res_forscreen['resource_type']==1){
                $resource_type_str = '图片';
                $img_url = $forscreen_url."?x-oss-process=image/quality,Q_50";
            }else{
                $resource_type_str = '视频';
                $img_url = $forscreen_url.'?x-oss-process=video/snapshot,t_10000,f_jpg,w_450,m_fast';
            }
            $data_list[$k]['resource_type_str'] = $resource_type_str;
            $data_list[$k]['img'] = $img_url;
        }
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function edit(){
        $id = I('id',0,'intval');
        $m_playlog = new \Admin\Model\Smallapp\PlaylogModel();
        if(IS_POST){
            $pub_id = I('post.pub_id',0,'intval');
            if($pub_id){
                $m_public = new \Admin\Model\Smallapp\PublicModel();
                $res_public = $m_public->getOne('forscreen_id',array('id'=>$pub_id),'id desc');
                $forscreen_id = intval($res_public['forscreen_id']);
                $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
                $fields_forscreen = 'id';
                $all_forscreen = $m_forscreen->getDataList($fields_forscreen,array('forscreen_id'=>$forscreen_id),'id asc');
                if(empty($all_forscreen)){
                    $this->output('请重新选择内容审核序号ID', 'hotplay/datalist',2,0);
                }
                $res_id = $all_forscreen[0]['id'];
                $res = $m_playlog->updateData(array('id'=>$id),array('res_id'=>$res_id));
                if($res){
                    $redis = \Common\Lib\SavorRedis::getInstance();
                    $redis->select(5);
                    $key_demand = C('SAPP_HOTPLAYDEMAND');
                    $res_demand = $redis->get($key_demand);
                    if(!empty($res_demand)){
                        $demand = json_decode($res_demand,true);
                    }else{
                        $demand = array();
                    }
                    $demand['period'] = getMillisecond();
                    $redis->set($key_demand,json_encode($demand));

                    $this->output('操作成功!', 'hotplay/datalist');
                }else{
                    $this->output('操作失败', 'hotplay/datalist',2,0);
                }
            }else{
                $this->output('操作成功!', 'hotplay/datalist');
            }
        }else{
            $res_info = $m_playlog->getInfo(array('id'=>$id));
            $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
            $res_forscreen = $m_forscreen->getInfo(array('id'=>$res_info['res_id']));
            $fields_forscreen = 'imgs,duration,resource_size,resource_id';
            $all_forscreen = $m_forscreen->getDataList($fields_forscreen,array('forscreen_id'=>$res_forscreen['forscreen_id']),'id asc');
            $list = array();
            $oss_host = 'http://'.C('OSS_HOST_NEW').'/';
            foreach ($all_forscreen as $dv){
                $imgs_info = json_decode($dv['imgs'],true);
                $forscreen_url = $imgs_info[0];
                $img_url = $oss_host.$forscreen_url;
                $list[]=array('res_url'=>$img_url);
            }
            $this->assign('res_type',$res_forscreen['resource_type']);
            $this->assign('list',$list);
            $this->assign('id',$id);
            $this->display();
        }
    }



}