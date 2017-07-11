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
            $catid = $vinfo['hot_category_id'];
            $vinfo['content'] = html_entity_decode($vinfo['content']);
            if ($catid == 3) {
                $oss_host = get_oss_host();
                $vinfo['img_url'] = $oss_host.$vinfo['img_url'];
                if($vinfo['index_img_url']){
                    $vinfo['index_img_url'] = $oss_host.$vinfo['index_img_url'];
                }
                $display_html = 'special';
            }else{

                if($vinfo['type']==1){//图文
                    $display_html = 'newshowcontent';
                }elseif($vinfo['type']==3){
                    $tx_url = $vinfo['tx_url'];
                    $this->assign('tx_url', $tx_url);
                    $display_html = 'newshowvideocontent';
                }else{
                    // $display_html = 'showcontent';
                    $display_html = 'newshowcontent';
                }
            }
        }else{
            $vinfo = array();
            $display_html = 'newshowcontent';
        }
        $this->assign('vinfo',$vinfo);
        $this->display($display_html);
    }
}