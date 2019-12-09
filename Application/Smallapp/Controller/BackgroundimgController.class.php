<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 欢迎词背景图
 *
 */
class BackgroundimgController extends BaseController {

    public function backgroundimglist(){
        $category_id = I('category_id',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_category = new \Admin\Model\CategoryModel();
        $category = $m_category->getCategory($category_id,1,6);

        $m_backgroundimg = new \Admin\Model\Smallapp\BackgroundimgModel();
        $where = array();
        if($category_id){
            $where['category_id'] = $category_id;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_backgroundimg->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_media = new \Admin\Model\MediaModel();
            foreach ($res_list['list'] as $v){
                $res_media = $m_media->getMediaInfoById($v['media_id']);
                $v['img'] = $res_media['oss_addr'];
                $v['category'] = $category[$v['category_id']]['name'];
                if($v['status']==1){
                    $v['status_str'] = '启用';
                }else{
                    $v['status_str'] = '禁用';
                }
                $data_list[] = $v;
            }
        }

        $this->assign('category',$category);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display('backgroundimglist');
    }

    public function backgroundimgadd(){
        $id = I('id',0,'intval');
        $m_backgroundimg = new \Admin\Model\Smallapp\BackgroundimgModel();
        if(IS_POST){
            $category_id = I('post.category_id',0,'intval');
            $media_id = I('post.media_id',0,'intval');
            $status = I('post.status',0,'intval');
            $data = array('media_id'=>$media_id,'category_id'=>$category_id,'status'=>$status);
            if($id){
                $result = $m_backgroundimg->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_backgroundimg->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'backgroundimg/backgroundimglist');
            }else{
                $this->output('操作失败', 'backgroundimg/backgroundimglist',2,0);
            }
        }else{
            $category_id = 0;
            if($id){
                $vinfo = $m_backgroundimg->getInfo(array('id'=>$id));
                $category_id = $vinfo['category_id'];
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getMediaInfoById($vinfo['media_id']);
                $vinfo['oss_addr'] = $res_media['oss_addr'];

            }else{
                $vinfo = array('status'=>1);
            }
            $m_category = new \Admin\Model\CategoryModel();
            $category = $m_category->getCategory($category_id,1,6);

            $this->assign('category',$category);
            $this->assign('vinfo',$vinfo);
            $this->display('backgroundimgadd');
        }
    }


    public function backgroundimgdel(){
        $id = I('get.id',0,'intval');
        $m_background = new \Admin\Model\Smallapp\BackgroundimgModel();
        $result = $m_background->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'backgroundimg/backgroundimglist',2);
        }else{
            $this->output('操作失败', 'backgroundimg/backgroundimglist',2,0);
        }
    }

}