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
    
    public function video(){
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
        //查找是否在首页内容中引用

        if($gid) {
            $mbHomeModel = new \Admin\Model\HomeModel();
            $res = $mbHomeModel->where('content_id='.$gid)->find();
            if($res) {
                $this->output('首页有引用不可删除', 'content/getlist',1);
                die;
            }
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

    public function showpic() {
        $img = I('get.pic');
        $this->assign('imgd', $img);
        $this->display('showpic');
        echo $img;
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

        $vinfo['state'] = 0;
        $this->assign('vinfo',$vinfo);
        if ($acctype && $id){
            $vinfo = $artModel->where('id='.$id)->find();
            $vinfo['oss_addr'] = $vinfo['img_url'];
            $this->assign('vinfo',$vinfo);
        }
        $where = "1=1";
        $field = 'id,name';
        $vinfo = $catModel->getWhere($where, $field);
        $this->assign('vcainfo',$vinfo);
        $this->display('addart');

    }
    /*
     * 显示并生成H5页面地址
     */
    public function showcontent($id){
        $artModel = new ArticleModel();
        $vinfo = $artModel->where('id='.$id)->find();
        $content = html_entity_decode($vinfo['content']);
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
        $replacment = '<img src='.__ROOT__ .'${1}>';
        $content =  preg_replace($pattern, $replacment, $content);
        $this->assign('content',$content);
        ob_start();
        $this->display('showcontent');
        $content = ob_get_contents();//取得php页面输出的全部内容
        echo $content;
        $path = SITE_TP_PATH.'/Public/html';
        if ( !(is_dir($path)) ) {
            mkdir ( $path, 0777, true );
        }
        $fp = fopen($path."/".$id.".html", "w");
        fwrite($fp, $content);
        fclose($fp);
        ob_end_clean();
    }

    public function addhome(){
        $id = I('get.id');
        $mbHomeModel = new \Admin\Model\HomeModel();
        if($id){
            $vinfo = $mbHomeModel->where(array('id'=>$id))->find();

            $this->assign('vinfo',$vinfo);

        }
        return $this->display('addhome');
    }

    public function doaddhome(){

        //文章id
        $mbHomeModel = new \Admin\Model\HomeModel();
        $artid = $_REQUEST['artid'];
        $type = $_REQUEST['acttype'];
        $id = I('post.id');
      //  var_dump($artid,$type);
        if($type == 1){
            //判断表中是否有

            $res = $mbHomeModel->where('content_id='.$artid)->find();

            if( $res ){
                $this->output('文章已经存在', 'content/getlist',2);
            } else {

                $artModel = new  \Admin\Model\ArticleModel();
                $arr = $artModel->find($artid);
                $state = $arr['state'];
                if ($state != 2) {
                    $this->output('审核状态不允许', 'content/getlist',2);
                }
                $mbHomeModel = new \Admin\Model\HomeModel();
                $userInfo = session('sysUserInfo');
                $save[] = array();
                $md5 = $arr['vod_md5'];
                if ($md5) {
                    $save['is_demand'] = 1;
                }
                $save['content_id'] = $artid;
                $save['sort_num'] = 2;
                $save['creator_id'] = $userInfo['id'];
                $save['create_time'] = date("Y-m-d H:i:s", time());
                $save['update_time'] = $save['create_time'];

                $res = $mbHomeModel->add($save);
                if($res){
                    $this->output('操作成功!', 'article/homemanager',2);
                }else{
                    $this->output('操作失败!', 'content/getlist');
                }
            }
        } else {
            $save['sort_num'] = I('sort');
            $res_save = $mbHomeModel->where('id='.$id)->save($save);
            if($res_save){
                $this->output('操作成功!', 'article/homemanager',1);
            }else{
                $this->output('操作失败!', 'article/homemanager');
            }

        }







    }

    public function homemanager(){


        $mbHomeModel = new \Admin\Model\HomeModel();
        $artModel = new  \Admin\Model\ArticleModel();
        $catModel = new \Admin\Model\CategoModel;
        $cat_arr = $catModel->select();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','sort_num');
        $this->assign('_order',$order);
        $sort = I('_sort','asc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = "1=1";

        $result = $mbHomeModel->getList($where,$orders,$start,$size);

        $datalist = $artModel->changeIdjName($result['list'], $cat_arr);

        $name = I('name');
        if($name){
            //根据id取
            $this->assign('name',$name);
            $where .= "	AND name LIKE '%{$name}%'";
        }
        $this->assign('list', $datalist);
        $this->assign('page',  $result['page']);

        $this->display('homearticle');

    }

    /*
     * 修改状态
     */
    public function changestatus(){
        $mbHomeModel = new \Admin\Model\HomeModel();
        $id = I('request.id');
        $flag = I('request.flag');

        $save['state'] = $flag;
        if($mbHomeModel->where('id='.$id)->save($save)){
            $message = '更新成功!';
            $url = 'article/homemanager';
        }else{
            $message = '更新失败!';
            $url = 'article/homemanager';
        }
        $this->output($message, $url,2);


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
        $vinfo['state'] = 0;
        $this->assign('vinfo',$vinfo);
        $id = I('get.id');
        $acctype = I('get.acttype');
        if ($acctype && $id){
            $oss_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
            $vainfo = $artModel->where('id='.$id)->find();
            $media_id = $vainfo['media_key_id'];
            $vainfo['oss_addr'] = $oss_host.$vainfo['oss_addr'];
            $vainfo['vid_type'] = 2;
            if($media_id){
                $mediaModel = new \Admin\Model\MediaModel();
                $mediainfo = $mediaModel->getMediaInfoById($vainfo['media_id']);
                $vainfo['videooss_addr'] = $oss_host.
                $vainfo['vid_type'] = 1;
            }
            $this->assign('vainfo',$vainfo);
        }
        $where = "1=1";
        $field = 'id,name';
        $vinfo = $catModel->getWhere($where, $field);
        $this->assign('vcainfo',$vinfo);
        $this->display('addvideo');
    }

    public function doAddvideo(){
        $mediaModel = new \Admin\Model\MediaModel();
        $artModel = new ArticleModel();
        $id                  = I('post.id');
        $save                = [];
        $save['title']        = I('post.title','','trim');
        $save['category_id']        = I('post.cate','','trim');
        $covermedia_id = I('post.covervideo_id','0','intval');//视频封面id
        $media_id = I('post.media_id','0','intval');//视频id


        $save['source']    = I('post.source','');
        $save['content']    = I('post.content','','htmlspecialchars');
        $save['type']    = I('post.ctype','','intval');
        $save['state']    = I('post.state','0','intval');
        $save['tx_url'] = I('post.yunhref','');
        $save['update_time'] = date('Y-m-d H:i:s');
        $addtype = I('post.r1','0',intval);
        $save['bespeak_time'] = I('post.logtime','');
        $save['bespeak'] = 0;
        $image_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
        if($covermedia_id){
            $oss_arr = $mediaModel->find($covermedia_id);
            $oss_addr = $oss_arr['oss_addr'];
            $save['oss_addr'] = $oss_addr;
            $save['img_url'] = $image_host.$oss_addr;
            $save['type'] = 1;
        }else{
            $this->output('封面必填!', 'article/addvideo');
        }
        if($media_id){
            $oss_arr = $mediaModel->find($media_id);
            $save['duration'] = $oss_arr['duration'];
            $save['vod_md5'] = $oss_arr['md5'];
            $save['media_key_id']    = $media_id;

        }
        if($id){
            if($artModel->where('id='.$id)->save($save)){
                $this->showcontent($id);
                $this->output('操作成功!', 'content/getlist');
            }else{
                $this->output('操作失败!', 'content/getlist');
            }
        }else{
            $save['type'] = 3;
            $save['create_time'] = date('Y-m-d H:i:s');
            $userInfo = session('sysUserInfo');
            $uname = $userInfo['username'];
            $save['operators']    = $uname;
            if($artModel->add($save)){
                $id = $artModel->getLastInsID();
                $this->showcontent($id);
                $dat['content_url'] = 'html/'.$id.'.html';
                $artModel->where('id='.$id)->save($dat);
                $this->output('操作成功!', 'content/getlist');
            }else{
                $this->output('操作失败!', 'content/getlist');
            }
        }
    }

    public function doAddarticle(){
        $artModel = new ArticleModel();
        $id                  = I('post.id');
        $save                = [];
        $save['title']        = I('post.title','','trim');
        $save['category_id']        = I('post.cate','','trim');
        $save['source']    = I('post.source','');

        $save['content']    = I('post.content','htmlspecialchars');
        $save['type']    = I('post.ctype','','intval');
        $save['state']    = I('post.state','0','intval');
        $save['update_time'] = date('Y-m-d H:i:s');
        $save['bespeak_time'] = I('post.logtime','');
        $save['bespeak'] = 0;
        $mediaid = I('post.media_id');

        $mediaModel = new \Admin\Model\MediaModel();
        $oss_addr = $mediaModel->find($mediaid);
        $oss_addr = $oss_addr['oss_addr'];
        $save['oss_addr'] = $oss_addr;
        $image_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
        $oss_addr = $image_host.$oss_addr;
        $save['img_url'] = $oss_addr;
        if($id){
            if($artModel->where('id='.$id)->save($save)){
                $this->showcontent($id);
                $this->output('操作成功!', 'content/getlist');
            }else{
                $this->output('操作失败!', 'content/getlist');
            }
        }else{
            if(!$mediaid){
                $this->output('封面必填!', 'article/doAddarticle',3);
                die;
            }
            $save['create_time'] = date('Y-m-d H:i:s');
            $userInfo = session('sysUserInfo');
            $uname = $userInfo['username'];
            $save['operators']    = $uname;
            if($artModel->add($save)){
                $arid = $artModel->getLastInsID();
                $this->showcontent($arid);
                $dat['content_url'] = 'html/'.$arid.'.html';
                $artModel->where('id='.$arid)->save($dat);
                $this->output('操作成功!', 'content/getlist');
            }else{
                $this->output('操作失败!', 'content/getlist');
            }
        }
    }
}
