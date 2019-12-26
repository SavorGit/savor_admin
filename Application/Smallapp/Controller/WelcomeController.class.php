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

    public function welcomelist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        $area_id = I('area_id',0,'intval');
        $play_type = I('play_type',0,'intval');
        $type = I('type',0,'intval');
        $status = I('status',0,'intval');
        $hotel_name = I('hotel_name','','trim');

        $fields = 'a.id,a.type,a.user_id,user.nickName user_name,a.backgroundimg_id,a.image,a.content,a.play_type,a.play_date,a.timing,a.box_mac,a.add_time,a.status,hotel.name as hotel_name,area.region_name as city';
        $where = array();
        if($area_id)    $where['area.id']=$area_id;
        if($status)     $where['a.status']=$status;
        if($play_type)  $where['a.play_type']=$play_type;
        if($type)       $where['type']=$type;
        if(!empty($hotel_name)) $where['hotel.name'] = array('like',"%$hotel_name%");

        $m_welcome = new \Admin\Model\Smallapp\WelcomeModel();
        $m_welcome->updateExpiredData();

        $start  = ($page-1) * $size;
        $result = $m_welcome->getWelcomeList($fields,$where,'a.id desc',$start,$size);
        $datalist = $result['list'];
        $all_status = C('WELCOME_STATUS');
        $m_box = new \Admin\Model\BoxModel();
        $m_welcomeresource = new \Admin\Model\Smallapp\WelcomeresourceModel();
        $m_media = new \Admin\Model\MediaModel();
        $oss_host = get_oss_host();
        foreach ($datalist as $k=>$v){
            if($v['play_type']==1){
                $datalist[$k]['play_str'] = '立即播放';
                $play_time = $v['add_time'];
            }else{
                $datalist[$k]['play_str'] = '定时播放';
                $play_time = $v['play_date'].' '.$v['timing'];
            }
            $box_mac = $v['box_mac'];
            if($datalist[$k]['type']==1){
                $box_where = array('box.mac'=>$box_mac,'box.flag'=>0,'box.state'=>1,'hotel.flag'=>0,'hotel.state'=>1);
                $res_box = $m_box->getBoxByCondition('box.name',$box_where);
                $room = $res_box[0]['name'];
                $datalist[$k]['type_str']='单个包间播放';
            }else{
                $box_mac = '';
                $room = '全部包间';
                $datalist[$k]['type_str']='所有包间播放';
            }
            if($v['backgroundimg_id']){
                $res_welcome = $m_welcomeresource->getInfo(array('id'=>$v['backgroundimg_id']));
                $res_media = $m_media->getMediaInfoById($res_welcome['media_id']);
                $img = $res_media['oss_addr'];
            }else{
                $img = $oss_host.$v['image'];
            }

            $datalist[$k]['img'] = $img;
            $datalist[$k]['room'] = $room;
            $datalist[$k]['box_mac'] = $box_mac;
            $datalist[$k]['play_time'] = $play_time;
            $datalist[$k]['status_str'] = $all_status[$v['status']];
        }

        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();

        $this->assign('type',$type);
        $this->assign('play_type',$play_type);
        $this->assign('area_id',$area_id);
        $this->assign('status',$status);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('area', $area_arr);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function stopplay(){
        $id = I('get.id',0,'intval');
        $status = I('get.status',0,'intval');

        $m_media = new \Admin\Model\MediaModel();
        $m_welcome = new \Admin\Model\Smallapp\WelcomeModel();
        $m_welcomeresource = new \Admin\Model\Smallapp\WelcomeresourceModel();
        if($status==1 || $status==2){
            $m_welcome->updateData(array('id'=>$id),array('status'=>3));
        }
        $res_welcome = $m_welcome->getInfo(array('id'=>$id));

        $wordsize_id = $res_welcome['wordsize_id'];
        $color_id = $res_welcome['color_id'];
        $backgroundimg_id = $res_welcome['backgroundimg_id'];
        $music_id = $res_welcome['music_id'];

        $ids = array($wordsize_id,$color_id);
        if($music_id){
            $ids[]=$music_id;
        }
        if($backgroundimg_id){
            $ids[]=$backgroundimg_id;
        }
        $where = array('id'=>array('in',$ids));
        $res_resource = $m_welcomeresource->getDataList('*',$where,'id asc');
        $resource_info = array();
        foreach ($res_resource as $v){
            $resource_info[$v['id']]=$v;
        }
        $message = array('action'=>131,'id'=>$id,'forscreen_char'=>$res_welcome['content'],'rotation'=>intval($res_welcome['rotate']),
            'wordsize'=>$resource_info[$wordsize_id]['tv_wordsize'],'color'=>$resource_info[$color_id]['color'],
            'finish_time'=>$res_welcome['finish_time']);
        if(isset($resource_info[$backgroundimg_id])){
            $res_media = $m_media->getMediaInfoById($resource_info[$backgroundimg_id]['media_id']);
            $message['img_id'] = intval($backgroundimg_id);
            $message['img_oss_addr'] = $res_media['oss_addr'];
        }else{
            $message['img_id'] = 0;
            $img_oss_addr = $res_welcome['image'];
            $message['img_oss_addr'] = $img_oss_addr;
        }
        $name_info = pathinfo($message['img_oss_addr']);
        $message['filename'] = $name_info['basename'];

        if(isset($resource_info[$music_id])){
            $res_media = $m_media->getMediaInfoById($resource_info[$music_id]['media_id']);
            $message['music_id'] = intval($music_id);
            $message['music_oss_addr'] = $res_media['oss_addr'];
        }else{
            $message['music_id'] = 0;
            $message['music_oss_addr'] = '';
        }
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $sys_info = $m_sys_config->getAllconfig();
        $playtime = $sys_info['welcome_playtime'];
        $playtime = intval($playtime*60);
        $message['play_times'] = $playtime;

        $m_netty = new \Admin\Model\Smallapp\NettyModel();
        $res_netty = $m_netty->pushBox($res_welcome['box_mac'],json_encode($message));
        if(isset($res_netty['error_code']) && $res_netty['error_code']==90109){
            $m_netty->pushBox($res_welcome['box_mac'],json_encode($message));
        }
        $this->output('操作成功!', 'welcome/welcomelist',2);
    }

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
                $redis = \Common\Lib\SavorRedis::getInstance();
                $redis->select(14);
                $program_key = C('SAPP_SALE_WELCOME_RESOURCE');
                $period = getMillisecond();
                $redis->set($program_key,$period);
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
            $redis = \Common\Lib\SavorRedis::getInstance();
            $redis->select(14);
            $program_key = C('SAPP_SALE_WELCOME_RESOURCE');
            $period = getMillisecond();
            $redis->set($program_key,$period);
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
                $redis = \Common\Lib\SavorRedis::getInstance();
                $redis->select(14);
                $program_key = C('SAPP_SALE_WELCOME_RESOURCE');
                $period = getMillisecond();
                $redis->set($program_key,$period);

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
            $redis = \Common\Lib\SavorRedis::getInstance();
            $redis->select(14);
            $program_key = C('SAPP_SALE_WELCOME_RESOURCE');
            $period = getMillisecond();
            $redis->set($program_key,$period);
            $this->output('操作成功!', 'welcome/resourcelist',2);
        }else{
            $this->output('操作失败', 'welcome/resourcelist',2,0);
        }
    }

}