<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 生日歌管理
 *
 */
class BirthdayController extends BaseController {

    public function birthdaylist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_birthday = new \Admin\Model\Smallapp\BirthdayModel();
        $where = array();
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_birthday->getDataList('*',$where,$orderby,$start,$size);
        $data_list = $res_list['list'];
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function birthdayadd(){
        if(IS_POST){
            $id = I('post.id',0,'intval');
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');


            $data = array('name'=>$name,'media_id'=>$media_id);

            $m_birthday = new \Admin\Model\Smallapp\BirthdayModel();
            if($id){
                $result = $m_birthday->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_birthday->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'birthday/birthdaylist');
            }else{
                $this->output('操作失败', 'birthday/birthdaylist',2,0);
            }
        }else{
            $vinfo = array('status'=>1);
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }

    public function birthdayedit(){
        $id = I('id',0,'intval');
        $m_birthday = new \Admin\Model\Smallapp\BirthdayModel();
        $vinfo = $m_birthday->getInfo(array('id'=>$id));
        $oss_addr = '';
        if(!empty($vinfo['media_id'])){
            $m_media = new \Admin\Model\MediaModel();
            $res_addr = $m_media->getMediaInfoById($vinfo['media_id']);
            $oss_addr = $res_addr['oss_addr'];
        }
        $vinfo['oss_addr'] = $oss_addr;
        $this->assign('vinfo',$vinfo);
        $this->display('birthdayadd');
    }

    public function birthdaydel(){
        $id = I('get.id',0,'intval');
        $m_birthday = new \Admin\Model\Smallapp\BirthdayModel();
        $result = $m_birthday->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'birthday/birthdaylist',2);
        }else{
            $this->output('操作失败', 'birthday/birthdaylist',2,0);
        }
    }

}