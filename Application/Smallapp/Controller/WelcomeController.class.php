<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 欢迎词管理
 *
 */
class WelcomeController extends BaseController {

    public $welcome_resource_type = array(
        1=>'字号',
        2=>'颜色',
        3=>'音乐',
        4=>'背景图'
    );

    public function resourcelist(){
        $type = I('type',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_welcomeresource = new \Admin\Model\Smallapp\WelcomeresourceModel();
        $where = array();
        if($type){
            $where['type'] = $type;
        }else{
            unset($this->welcome_resource_type[4]);
            $where['type'] = array('in',array_keys($this->welcome_resource_type));
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_welcomeresource->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            $m_media = new \Admin\Model\MediaModel();
            foreach ($res_list['list'] as $v){
                $res_media = $m_media->getMediaInfoById($v['media_id']);
                $v['music'] = $res_media['oss_addr'];
                if($v['status']==1){
                    $v['status_str'] = '启用';
                }else{
                    $v['status_str'] = '禁用';
                }
                $v['type_str'] = $this->welcome_resource_type[$v['type']];
                switch ($v['type']){
                    case 1:
                        $url = 'welcome/wordsizeadd';
                        break;
                    case 2:
                        $url = 'welcome/coloradd';
                        break;
                    case 3:
                        $url = 'welcome/musicadd';
                        break;
                    default:
                        $url = '';
                }
                $v['url'] = $url;
                $data_list[] = $v;
            }
        }

        $this->assign('types',$this->welcome_resource_type);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display('resourcelist');
    }

    public function wordsizeadd(){
        $this->resource_add('wordsizeadd');
    }

    public function coloradd(){
        $this->resource_add('coloradd');
    }

    public function musicadd(){
        $this->resource_add('musicadd');
    }

    public function backgroundimglist(){
        $category_id = I('category_id',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $m_category = new \Admin\Model\CategoryModel();
        $category = $m_category->getCategory($category_id,1,6);

        $m_welcomeresource = new \Admin\Model\Smallapp\WelcomeresourceModel();
        $where = array('type'=>4);
        if($category_id){
            $where['category_id'] = $category_id;
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_welcomeresource->getDataList('*',$where,$orderby,$start,$size);
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
        $m_welcomeresource = new \Admin\Model\Smallapp\WelcomeresourceModel();
        if(IS_POST){
            $category_id = I('post.category_id',0,'intval');
            $media_id = I('post.media_id',0,'intval');
            $status = I('post.status',0,'intval');
            $data = array('media_id'=>$media_id,'category_id'=>$category_id,'type'=>4,'status'=>$status);
            if($id){
                $result = $m_welcomeresource->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_welcomeresource->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'welcome/backgroundimglist');
            }else{
                $this->output('操作失败', 'welcome/backgroundimglist',2,0);
            }
        }else{
            $category_id = 0;
            if($id){
                $vinfo = $m_welcomeresource->getInfo(array('id'=>$id));
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
        $m_welcomeresource = new \Admin\Model\Smallapp\WelcomeresourceModel();
        $result = $m_welcomeresource->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'welcome/backgroundimglist',2);
        }else{
            $this->output('操作失败', 'welcome/backgroundimglist',2,0);
        }
    }


    public function configdata(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $where = " config_key in('welcome_playtime','welcome_musicvolume')";
        $volume_arr = $m_sys_config->getList($where);
        $info = array();
        foreach($volume_arr as $v){
            $info[$v['config_key']] = $v['config_value'];
        }
        $this->assign('info',$info);
        $this->display('welcomeconfig');
    }

    public function editconfig(){
        $welcome_playtime = I('post.welcome_playtime',0,'intval');
        $welcome_musicvolume = I('post.welcome_musicvolume',0,'intval');

        $m_sys_config = new \Admin\Model\SysConfigModel();
        $data_playtime = array('config_value'=>$welcome_playtime);
        $rts = $m_sys_config->editData($data_playtime, 'welcome_playtime');

        $data_musicvolume = array('config_value'=>$welcome_musicvolume);
        $rts = $m_sys_config->editData($data_musicvolume, 'welcome_musicvolume');

        $sys_list = $m_sys_config->getList(array('status'=>1));
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(12);
        $cache_key = C('SYSTEM_CONFIG');
        $redis->set($cache_key, json_encode($sys_list));
        $this->output('操作成功','welcome/configdata');
    }

    private function resource_add($html){
        $id = I('id',0,'intval');
        $m_welcomeresource = new \Admin\Model\Smallapp\WelcomeresourceModel();
        if(IS_POST){
            $type = I('post.type',0,'intval');
            $name = I('post.name','','trim');
            $media_id = I('post.media_id',0,'intval');
            $color = I('post.color','');
            $small_wordsize = I('post.small_wordsize',0,'intval');
            $tv_wordsize = I('post.tv_wordsize',0,'intval');
            $status = I('post.status',0,'intval');

            $data = array('name'=>$name,'small_wordsize'=>$small_wordsize,'tv_wordsize'=>$tv_wordsize,
                'media_id'=>$media_id,'color'=>$color,'type'=>$type,'status'=>$status);
            if($id){
                $result = $m_welcomeresource->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_welcomeresource->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'welcomeresource/resourcelist');
            }else{
                $this->output('操作失败', "welcomeresource/$html",2,0);
            }
        }else{
            if($id){
                $vinfo = $m_welcomeresource->getInfo(array('id'=>$id));
            }else{
                $vinfo = array();
            }
            $this->assign('vinfo',$vinfo);
            $this->display($html);
        }
    }


    public function resourcedel(){
        $id = I('get.id',0,'intval');
        $m_welcomeresource = new \Admin\Model\Smallapp\WelcomeresourceModel();
        $result = $m_welcomeresource->delData(array('id'=>$id));
        if($result){
            $this->output('操作成功!', 'welcomeresource/resourcelist',2);
        }else{
            $this->output('操作失败', 'welcomeresource/resourcelist',2,0);
        }
    }

}