<?php
namespace Admin\Controller;

/**
 * @desc 文章发布类
 *
 */
use Admin\Controller\BaseController;
use Admin\Model\ArticleModel;
use Admin\Model\CategoModel;
use Admin\Model\MediaModel;
class ArticleController extends BaseController {
    
    public  $path = 'content/img';
    public function __construct() {
        parent::__construct();
    }
    
    public function manager() {
        $this->display('index');
    }


    public function video()
    {

        $mediaModel = new MediaModel;
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $fields = "id, name, oss_addr";

        $where = "1=1";
        $result = $mediaModel->getWhere($where,$fields);
       return $result;
    }

    public function delart() {
        $gid = I('get.id', 0, 'int');
        //查找是否在节目单中引用function()若引用则无法删除
        if($gid) {
            $artModel = new ArticleModel();

            $result = $artModel -> delData($gid);
            if($result) {
                $this->output('删除成功', 'content/getlist',2);
            } else {
                $this->output('删除失败', 'content/getlist',1);
            }
        } else {
            $this->error('删除失败,缺少参数!');
        }
    }



    /**
     * 添加文章
     */
    public function addArticle(){

        $catModel = new CategoModel();
        $artModel = new ArticleModel();
        $userInfo = session('sysUserInfo');
        $uname = $userInfo['username'];
        $this->assign('uname',$uname);

        $id = I('get.id');
        $acctype = I('get.acttype');

        if ($acctype && $id)
        {
            $vinfo = $artModel->where('id='.$id)->find();
            $this->assign('vinfo',$vinfo);

        } else {

        }
        $where = "state=0";
        $field = 'id,name';
        $vinfo = $catModel->getWhere($where, $field);

        $this->assign('vcainfo',$vinfo);
        $this->display('addart');

    }



    /**
     * 添加视频
     */
    public function addVideo(){


        $catModel = new CategoModel();
        $artModel = new ArticleModel();
        $mediaModel = new MediaModel;

        $userInfo = session('sysUserInfo');
        $uname = $userInfo['username'];
        $this->assign('uname',$uname);

        $id = I('get.id');
        $acctype = I('get.acttype');

        if ($acctype && $id)
        {
            $vinfo = $artModel->where('id='.$id)->find();

            $media_id = $vinfo['media_key_id'];
            $oss_addr = $vinfo['oss_addr'];
            if(!empty($oss_addr)) {
                $where = "id=$media_id";
                $fields = 'id, name';

                $result = $mediaModel->getWhere($where,$fields);


                $vinfo['media_name'] = $result[0]['name'];
                //$vinfo['media_name'] = 'vvvv';
                $vinfo['vid_type'] = 1;
            } else {
                $vinfo['vid_type'] = 2;
            }


            $this->assign('vinfo',$vinfo);

        } else {

        }
        $where = "state=0";
        $field = 'id,name';
        $vinfo = $catModel->getWhere($where, $field);

        $media = $this->video();



        $this->assign('videosource', $media);
        $this->assign('vcainfo',$vinfo);
        $this->display('addvideo');

    }

    public function doAddvideo(){



        $artModel = new ArticleModel();
        $id                  = I('post.id');
        $save                = [];
        $save['title']        = I('post.title','','trim');
        $save['category_id']        = I('post.cate','','trim');

        $save['img_url']    = I('post.shwimage','');


        $save['source']    = I('post.source','');
        $save['operators']    = I('post.operators','');
        $save['content']    = I('post.content','','htmlspecialchars');
        $save['type']    = I('post.ctype','','intval');
        $save['state']    = I('post.state','0','intval');

        $save['tx_url'] = I('post.yunhref','');
        $save['update_time'] = date('Y-m-d H:i:s');
        $addtype = I('post.r1','0',intval);
        $save['bespeak_time'] = I('post.logtime','');
        $save['bespeak'] = 0;
        if ($addtype == 1) {
            $save['oss_addr'] = I('post.videoaddr', '');
            $save['media_key_id'] = I('post.videomedia', '');
        } else {
            $save['oss_addr'] = '';
        }
        $old_img = I('post.shwimage','');
        $path = SITE_TP_PATH.'/Public/'.$this->path;
        if ( !(is_dir($path)) ) {
            mkdir ( $path, 0777, true );
        }
        if ( $old_img == '') {

        } else {
            $result = $artModel->getImgRes($path, $old_img);

            if ($result['res'] == 1) {
                $save['img_url']  = $this->path.'/'.$result['pic'];
            } else {
                $this->output('添加图片失败!', 'article/addarticle');
            }
        }
        if($id)
        {
            if($artModel->where('id='.$id)->save($save))
            {
                $this->output('操作成功!', 'article/addvideo');
            }
            else
            {
                $this->output('操作失败!', 'article/addvideo');
            }
        }
        else
        {
            $save['create_time'] = date('Y-m-d H:i:s');
            if($artModel->add($save))
            {
                $this->output('操作成功!', 'article/addvideo');
            }
            else
            {
                $this->output('操作失败!', 'article/addvideo');
            }
        }
    }


    public function doAddarticle()
    {
        $artModel = new ArticleModel();
        $id                  = I('post.id');
        $save                = [];
        $save['title']        = I('post.title','','trim');
        $save['category_id']        = I('post.cate','','trim');
        $save['img_url']    = I('post.shwimage','');
        $save['source']    = I('post.source','');
        $save['operators']    = I('post.operators','');
        $save['content']    = I('post.content','htmlspecialchars');
        $save['type']    = I('post.ctype','','intval');
        $save['state']    = I('post.state','0','intval');
        $save['update_time'] = date('Y-m-d H:i:s');
        $save['bespeak_time'] = I('post.logtime','');
        $save['bespeak'] = 0;
        $old_img = I('post.shwimage','');
        $path = SITE_TP_PATH.'/Public/'.$this->path;
        if ( !(is_dir($path)) ) {
            mkdir ( $path, 0777, true );
        }
        if ( $old_img == '') {

        } else {
            $result = $artModel->getImgRes($path, $old_img);

            if ($result['res'] == 1) {
                $save['img_url']  = $this->path.'/'.$result['pic'];
            } else {
                $this->output('添加图片失败!', 'article/addarticle');
            }
        }
        if($id)
        {
            if($artModel->where('id='.$id)->save($save))
            {
                $this->output('操作成功!', 'release/addCate');
            }
            else
            {
                $this->output('操作失败!', 'release/doAddCat');
            }
        }
        else
        {
            $save['create_time'] = date('Y-m-d H:i:s');
            if($artModel->add($save))
            {
                $this->output('操作成功!', 'release/addCate');
            }
            else
            {
                $this->output('操作失败!', 'release/doAddCat');
            }
        }


    }//End Function











}