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
        $where = array('status'=>1);
        if($constellation_name){
            $where['name'] = array('like',"%$constellation_name%");
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_constell->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['date_str'] = $v['start_month'].'.'.$v['start_day'].'-'.$v['end_month'].'.'.$v['end_day'];
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
}