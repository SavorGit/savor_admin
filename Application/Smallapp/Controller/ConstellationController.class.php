<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 星座管理
 *
 */
class ConstellationController extends BaseController {

    public function constellationlist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $constellation_name = I('constellation_name','','trim');

        $m_constell = new \Admin\Model\Smallapp\ConstellationModel();
        $where = array();
        if($constellation_name){
            $where['name'] = array('like',"%$constellation_name%");
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'end_month asc,end_day asc';
        $res_list = $m_constell->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['date_str'] = $v['start_month'].'.'.$v['start_day'].'-'.$v['end_month'].'.'.$v['end_day'];
                if($v['status']){
                    $v['status_str'] = '启用';
                }else{
                    $v['status_str'] = '关闭';
                }
                $data_list[] = $v;
            }
        }
        $this->assign('constellation_name',$constellation_name);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function constellationadd(){
        if(IS_POST){
            $id = I('post.id',0,'intval');
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $start_month = I('post.start_month',0,'intval');
            $start_day = I('post.start_day',0,'intval');
            $end_month = I('post.end_month',0,'intval');
            $end_day = I('post.end_day',0,'intval');
            $intro = I('post.intro','','trim');
            $desc = I('post.desc','','trim');
            $content = I('post.content','','trim');
            $keywords = I('post.keywords','','trim');
            $symbol = I('post.symbol','','trim');
            $quad = I('post.quad','','trim');
            $house = I('post.house','','trim');
            $yinyang = I('post.yinyang','','trim');
            $feature = I('post.feature','','trim');
            $star = I('post.star','','trim');
            $color = I('post.color','','trim');
            $trikona = I('post.trikona','','trim');
            $body = I('post.body','','trim');
            $gems = I('post.gems','','trim');
            $lucknum = I('post.lucknum',0,'intval');
            $status = I('post.status',0,'intval');

            $data = array('name'=>$name,'media_id'=>$media_id,'start_month'=>$start_month,'start_day'=>$start_day,'end_month'=>$end_month,'end_day'=>$end_day,
                'intro'=>$intro,'desc'=>$desc,'content'=>$content,'keywords'=>$keywords,'symbol'=>$symbol,'quad'=>$quad,'house'=>$house,
                'yinyang'=>$yinyang,'feature'=>$feature,'star'=>$star,'color'=>$color,'trikona'=>$trikona,'body'=>$body,'gems'=>$gems,
                'lucknum'=>$lucknum,'status'=>$status);

            $m_constell = new \Admin\Model\Smallapp\ConstellationModel();
            if($id){
                $result = $m_constell->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_constell->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'constellation/constellationlist');
            }else{
                $this->output('操作失败', 'constellation/constellationlist',2,0);
            }
        }else{
            $vinfo = array('status'=>1);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function constellationedit(){
        $id = I('id',0,'intval');
        $m_constell = new \Admin\Model\Smallapp\ConstellationModel();
        $vinfo = $m_constell->getInfo(array('id'=>$id));
        $oss_addr = '';
        if(!empty($vinfo['media_id'])){
            $m_media = new \Admin\Model\MediaModel();
            $res_addr = $m_media->getMediaInfoById($vinfo['media_id']);
            $oss_addr = $res_addr['oss_addr'];
        }
        $vinfo['oss_addr'] = $oss_addr;
        $this->assign('vinfo',$vinfo);
        $this->display('constellationadd');
    }

    public function constellationdel(){
        $id = I('get.id',0,'intval');
        $m_constell = new \Admin\Model\Smallapp\ConstellationModel();
        $result = $m_constell->updateData(array('id'=>$id),array('status'=>0));
        if($result){
            $this->output('操作成功!', 'constellation/constellationlist',2);
        }else{
            $this->output('操作失败', 'constellation/constellationlist',2,0);
        }
    }

    public function relatevideo(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $constellation_id = I('constellation_id',0,'intval');

        $m_constellvideo = new \Admin\Model\Smallapp\ConstellationvideoModel();
        $where = array('constellation_id'=>$constellation_id);
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_constellvideo->getDataList('*',$where,$orderby,$start,$size);
        $m_media = new \Admin\Model\MediaModel();
        foreach ($res_list['list'] as $k=>$v){
            $oss_addr = '';
            $video_img = '';
            if(!empty($v['media_id'])) {
                $res_addr = $m_media->getMediaInfoById($v['media_id']);
                $oss_addr = $res_addr['oss_addr'];
                $video_img = $oss_addr.'?x-oss-process=video/snapshot,t_3000,f_jpg,w_450,m_fast';
            }
            $res_list['list'][$k]['oss_addr'] = $oss_addr;
            $res_list['list'][$k]['video_img'] = $video_img;
        }

        $m_constell = new \Admin\Model\Smallapp\ConstellationModel();
        $constellation = $m_constell->getInfo(array('id'=>$constellation_id));

        $this->assign('data',$res_list['list']);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->assign('constellation',$constellation);
        $this->display();
    }

    public function videoadd(){
        $constellation_id = I('constellation_id',0,'intval');
        $id = I('id',0,'intval');
        $m_constellvideo = new \Admin\Model\Smallapp\ConstellationvideoModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $sort = I('post.sort',1,'intval');
            $status = I('post.status',0,'intval');
            $data = array('name'=>$name,'media_id'=>$media_id,'sort'=>$sort,'status'=>$status,'constellation_id'=>$constellation_id);
            if($id){
                $result = $m_constellvideo->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_constellvideo->addData($data);
            }
            if($result){

                $current_id = $this->getCurrentConstellation();
                if($current_id==$constellation_id){
                    $redis  =  \Common\Lib\SavorRedis::getInstance();
                    $redis->select(5);
                    $key_demand = C('SAPP_BIRTHDAYDEMAND');
                    $res_demand = $redis->get($key_demand);
                    if(!empty($res_demand)){
                        $demand = json_decode($res_demand,true);
                    }else{
                        $demand = array();
                    }
                    $demand['period'] = getMillisecond();
                    $demand['constellation_id'] = $constellation_id;
                    $redis->set($key_demand,json_encode($demand));
                }

                $this->output('操作成功!', 'constellation/relatevideo');
            }else{
                $this->output('操作失败', 'constellation/relatevideo',2,0);
            }
        }else{
            $vinfo = array('sort'=>1,'status'=>1);
            if($id){
                $vinfo = $m_constellvideo->getInfo(array('id'=>$id));
                $oss_addr = '';
                if(!empty($vinfo['media_id'])){
                    $m_media = new \Admin\Model\MediaModel();
                    $res_addr = $m_media->getMediaInfoById($vinfo['media_id']);
                    $oss_addr = $res_addr['oss_addr'];
                }
                $vinfo['oss_addr'] = $oss_addr;
            }
            $this->assign('constellation_id',$constellation_id);
            $this->assign('vinfo',$vinfo);
        }
        $this->display();
    }

    public function videoedit(){
        $id = I('get.id',0,'intval');
        $m_constellvideo = new \Admin\Model\Smallapp\ConstellationvideoModel();
        $vinfo = $m_constellvideo->getInfo(array('id'=>$id));
        $oss_addr = '';
        if(!empty($vinfo['media_id'])){
            $m_media = new \Admin\Model\MediaModel();
            $res_addr = $m_media->getMediaInfoById($vinfo['media_id']);
            $oss_addr = $res_addr['oss_addr'];
        }
        $vinfo['oss_addr'] = $oss_addr;
        $constellation_id = $vinfo['constellation_id'];
        $this->assign('constellation_id',$constellation_id);
        $this->assign('vinfo',$vinfo);
        $this->display('videoadd');
    }
    public function videodel(){
        $id = I('get.id',0,'intval');
        $m_constellvideo = new \Admin\Model\Smallapp\ConstellationvideoModel();
        $res_constellvideo = $m_constellvideo->getInfo(array('id'=>$id));
        $result = $m_constellvideo->delData(array('id'=>$id));
        if($result){

            $current_id = $this->getCurrentConstellation();
            if($current_id==$res_constellvideo['constellation_id']){
                $redis  =  \Common\Lib\SavorRedis::getInstance();
                $redis->select(5);
                $key_demand = C('SAPP_BIRTHDAYDEMAND');
                $res_demand = $redis->get($key_demand);
                if(!empty($res_demand)){
                    $demand = json_decode($res_demand,true);
                }else{
                    $demand = array();
                }
                $demand['period'] = getMillisecond();
                $demand['constellation_id'] = $res_constellvideo['constellation_id'];
                $redis->set($key_demand,json_encode($demand));
            }

            $this->output('操作成功!', 'constellation/relatevideo',2);
        }else{
            $this->output('操作失败', 'constellation/relatevideo',2,0);
        }
    }

    private function getCurrentConstellation(){
        $fields = 'id,name,media_id,start_month,start_day,end_month,end_day,intro';
        $where = array('status'=>1);
        $orderby = 'end_month asc,end_day asc';
        $m_constellation = new \Admin\Model\Smallapp\ConstellationModel();
        $res = $m_constellation->getDataList($fields,$where,$orderby);
        $month = date('n');
        $day = date('j');
        $constellation_id = 1;
        foreach ($res as $k=>$v){
            if($month==$v['end_month'] && $day<=$v['end_day']){
                $constellation_id = $v['id'];
                break;
            }elseif($month==$v['start_month'] && $day>=$v['start_day']){
                $constellation_id = $v['id'];
                break;
            }
        }
        return $constellation_id;
    }

}