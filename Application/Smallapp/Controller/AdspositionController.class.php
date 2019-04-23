<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 广告位管理
 *
 */
class AdspositionController extends BaseController {

    public $clicktypes = array(1=>'链接',2=>'事件');


    public function adspositionlist(){
        $position = I('position',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $name = I('name','','trim');

        $m_adsposition = new \Admin\Model\Smallapp\AdspositionModel();
        $where = array();
        if($position){
            $where['position'] = $position;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_adsposition->getDataList('*',$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        foreach ($data_list as $k=>$v){
            if($v['status'] == 1){
                $data_list[$k]['status_str'] = '可用';
            }else{
                $data_list[$k]['status_str'] = '不可用';
            }
        }
        $this->assign('position',$position);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function adspositionadd(){
        if(IS_POST){
            $id = I('post.id',0,'intval');
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $linkcontent = I('post.linkcontent','','trim');
            $bindtap = I('post.bindtap','','trim');
            $appid = I('post.appid','','trim');
            $clicktype = I('post.clicktype',1,'intval');
            $position = I('post.position',0,'intval');
            $status = I('post.status',0,'intval');
            $sort = I('post.sort',1,'intval');
            $data = array('name'=>$name,'media_id'=>$media_id,'linkcontent'=>$linkcontent,'bindtap'=>$bindtap,'appid'=>$appid,
                'clicktype'=>$clicktype,'position'=>$position,'sort'=>$sort,'status'=>$status);

            $m_adsposition = new \Admin\Model\Smallapp\AdspositionModel();
            if($id){
                $result = $m_adsposition->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_adsposition->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'adsposition/adspositionlist');
            }else{
                $this->output('操作失败', 'adsposition/adspositionadd',2,0);
            }
        }else{
            $vinfo = array('status'=>1,'clicktype'=>1,'sort'=>1);
            $this->assign('vinfo',$vinfo);
            $this->assign('clicktypes',$this->clicktypes);
            $this->display();
        }
    }

    public function adspositionedit(){
        $id = I('id',0,'intval');
        $m_adsposition = new \Admin\Model\Smallapp\AdspositionModel();
        $vinfo = $m_adsposition->getInfo(array('id'=>$id));
        $oss_addr = '';
        if(!empty($vinfo['media_id'])){
            $m_media = new \Admin\Model\MediaModel();
            $res_addr = $m_media->getMediaInfoById($vinfo['media_id']);
            $oss_addr = $res_addr['oss_addr'];
        }
        $vinfo['oss_addr'] = $oss_addr;
        $this->assign('vinfo',$vinfo);
        $this->assign('clicktypes',$this->clicktypes);
        $this->display('adspositionadd');
    }

    public function adspositiondel(){
        $id = I('get.id',0,'intval');
        $m_adsposition = new \Admin\Model\Smallapp\AdspositionModel();
        $result = $m_adsposition->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'adsposition/adspositionlist',2);
        }else{
            $this->output('操作失败', 'adsposition/adspositionlist',2,0);
        }
    }

}