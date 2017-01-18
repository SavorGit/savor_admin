<?php
namespace Admin\Controller;
// use Common\Lib\SavorRedis;
/**
 * @desc 功能测试类
 *
 */
use Admin\Controller\BaseController;
use Admin\Model\ArticleModel;
use Admin\Model\CategoModel;
class ArticleController extends BaseController {
    
    public  $path = 'content/img';
    public function __construct() {
        parent::__construct();
    }
    
    public function manager() {
        //实例化redis
//         $redis = SavorRedis::getInstance();
//         $redis->set($cache_key, json_encode(array()));
        $this->display('index');
    }

    public function delart() {
        $gid = I('get.id', 0, 'int');
        //查找是否在节目单中引用
        if($gid) {
            $artModel = new ArticleModel();

            $result = $artModel -> delData($gid);
            if($result) {
                $this->output('删除成功', 'content/getlist',1);
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