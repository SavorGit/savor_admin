<?php
namespace Admin\Controller;

use Admin\Controller\BaseController;
use Think\Model;
/**
 * @desc 客户端页面
 *
 */
class ClientconfigController extends BaseController {

    public function __construct() {
        parent::__construct();
    }

    public function configdata(){
        $where = '1=1';
        $clientCModel = new \Admin\Model\ClientConfigModel();
        $result = $clientCModel->getdat($where);
        $datalist = $result['list'];
        $mediaModel = new \Admin\Model\MediaModel();
        $oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
        foreach ($datalist as $k=>$v){
            $media_id = $v['media_id'];
            $imgid = $v['img_id'];
            if($media_id){
                $mediainfo = $mediaModel->getMediaInfoById($media_id);
                $datalist[$k]['media_url'] = $mediainfo['oss_addr'];
            }
            if($imgid){
                $mediainfo = $mediaModel->getMediaInfoById($imgid);
                $datalist[$k]['img_url'] = $mediainfo['oss_addr'];
            }
        }
        $this->assign('list', $datalist);
        $this->display('clientlist');
    }


    /*
	 * 添加宣传片
	 */
    public function addclientconfig(){
        $clid = I('get.clid',0,'intval');
        if($clid){
            $oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
            $clientCModel = new \Admin\Model\ClientConfigModel();
            $vainfo = $clientCModel->find($clid);
            $media_id = $vainfo['media_id'];
            $imgid = $vainfo['img_id'];
            if($vainfo['media_id']){
                $mediaModel = new \Admin\Model\MediaModel();
                $mediainfo = $mediaModel->getMediaInfoById($vainfo['media_id']);
                $vainfo['videooss_addr'] = $mediainfo['oss_addr'];
            }
            if($vainfo['img_id']){
                $mediaModel = new \Admin\Model\MediaModel();
                $mediainfo = $mediaModel->getMediaInfoById($vainfo['img_id']);
                $vainfo['oss_addr'] = $mediainfo['oss_addr'];
            }
            $this->assign('vainfo',$vainfo);

        }
        $this->display('addclientconfig');
    }


    /*
	 * 对宣传片添加或者修改
	 */
    public function doAddclient(){
        $save = [];
        $clientCModel = new \Admin\Model\ClientConfigModel();
        $clid = I('post.clid');
        $save['ctype'] = I('post.clienttype','0','intval');
        //判断是否有记录该类型
        $covermedia_id = I('post.covervideo_id','0','intval');//视频封面id
        $media_id = I('post.media_id','0','intval');//视频i
        $save['duration'] = I('post.duration','0','intval');
        $save['name'] = I('post.adsname');

        if($covermedia_id){
            $save['img_id'] = $covermedia_id;
        }
        if($media_id){
            $save['media_id']    = $media_id;
        }
        if($clid){
            $save['update_time'] = date("Y-m-d H:i:s");
            $res_save = $clientCModel->where('id='.$clid)->save($save);
            if($res_save){
                $this->output('操作成功!', 'clientconfig/addclientconfig',1);
            }else{
                $this->error('操作失败!');
            }
        }else{
            $count = $clientCModel->where(array('ctype'=>$save['ctype']))->count();
            if ($count >=1 ){
                $this->error('该类型已经存在不可添加');
            }
            $save['status'] = 1;
            $save['online'] = 1;
            $save['create_time'] = date('Y-m-d H:i:s');
            $dat['update_time'] = date("Y-m-d H:i:s");
            $res_save = $clientCModel->add($save);
            if($res_save){
                $this->output('添加成功!', 'clientconfig/configdata',1);
            }else{
                $this->error('操作失败!');
            }
        }
    }

    public function changestatus(){

        $clientCModel = new \Admin\Model\ClientConfigModel();
        $cid = I('request.id','0','intval');
        $status = I('request.cid','0','intval');
        $data['status'] = $status;
        $res = $clientCModel->where("id='$cid'")->save($data);
        if($res){
           echo json_encode(array('status'=>1));
        } else {
           echo json_encode(array('status'=>0));
        }
    }

    /*
	 * 修改状态
	 */
    public function operateStatus(){

        $clientCModel = new \Admin\Model\ClientConfigModel();
        $adsid = I('request.adsid','0','intval');
        $message = '';
        $flag = I('request.flag');
        $data = array('online'=>$flag);

        $res = $clientCModel->where("id='$adsid'")->save($data);
        if($res){
            $message = '更新状态成功';
        }
        if($message){
            $this->output($message, 'clientconfig/configdata',2);
        }else{
            $this->error('操作失败');
        }


    }



}