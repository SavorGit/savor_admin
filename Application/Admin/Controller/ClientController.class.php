<?php
namespace Admin\Controller;
use Think\Controller;
/**
 * @desc 客户端页面
 *
 */
class ClientController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function showcontentererr(){
        $id = I('get.id',0,'intval');
        if($id){
            $articleModel = new \Admin\Model\ArticleModel();
            $vinfo = $articleModel->where('id='.$id)->find();
            $vinfo['content'] = html_entity_decode($vinfo['content']);
                $tx_url = $vinfo['tx_url'];
                $url_arr = explode('?id=', $tx_url);
                $url_id = $url_arr['1'];
                $play_js = "(function(){ var option ={'auto_play':'0','file_id':'$url_id','app_id':'1252891964','width':1280,'height':720,'https':1};new qcVideo.Player('id_video_container_$url_id', option ); })()";
                $this->assign('videourl_id', $url_id);
                $this->assign('play_js', $play_js);
                $display_html = 'showvideocontent';

        }else{
            $vinfo = array();
            $display_html = 'showcontent';
        }
        $this->assign('vinfo',$vinfo);
        $this->display($display_html);
    }


    public function showcontent(){
        $id = I('get.id',0,'intval');
        if($id){
            $articleModel = new \Admin\Model\ArticleModel();
            $vinfo = $articleModel->where('id='.$id)->find();
            $vinfo['content'] = html_entity_decode($vinfo['content']);
            if($vinfo['type']==1){//图文
                $display_html = 'showcontent';
            }elseif($vinfo['type']==3){
                $tx_url = $vinfo['tx_url'];
                $url_arr = explode('?id=', $tx_url);
                $url_id = $url_arr['1'];
                $play_js = "(function(){ var option ={'auto_play':'0','file_id':'$url_id','app_id':'1252891964','width':1280,'height':720,'https':1};new qcVideo.Player('id_video_container_$url_id', option ); })()";
                $this->assign('videourl_id', $url_id);
                $this->assign('play_js', $play_js);
                $display_html = 'showvideocontent';
            }else{
                $display_html = 'showcontent';
            }
        }else{
            $vinfo = array();
            $display_html = 'showcontent';
        }
        $this->assign('vinfo',$vinfo);
        $this->display($display_html);
    }
}