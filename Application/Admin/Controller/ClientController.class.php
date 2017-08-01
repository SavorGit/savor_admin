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


    /**
     *@desc 获取阿里云资源全路径
     */
    public function getOssAddr($url){
        $oss_host = get_oss_host();
        return  $oss_host.$url;
    }
    /**
     * @desc 获取内容完整URL
     */
    public function getContentUrl($url){
        $content_host = C('CONTENT_HOST');
        return $content_host.$url;
    }

    public function combination($a, $m) {
        $r = array();

        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }

        for ($i=0; $i<$n; $i++) {
            $t = array($a[$i]);
            if ($m == 1) {
                $r[] = $t;
            } else {
                $b = array_slice($a, $i+1);
                $c = $this->combination($b, $m-1);
                foreach ($c as $v) {
                    $r[] = array_merge($t, $v);
                }
            }
        }

        return $r;
    }

    public function judgeRecommendInfo($vinfo){
        //推荐数
        //var_dump($vinfo);
        $mend_len = 5;
        $articleModel = new \Admin\Model\ArticleModel();
        //获取推荐列表
        $order_tag = $vinfo['order_tag'];
        // var_dump($order_tag);
        $order_tag_arr = explode(',', $order_tag);
        $tag_len = count($order_tag_arr);
        if($tag_len == 0 || empty($order_tag)){
            $dap = array();
        }else{
            $where = "1=1 and state = 2  and hot_category_id = ".$vinfo['hot_category_id']." and type = ".$vinfo['type'];
            $field = 'id,title,order_tag';
            $dat = array();
            $dap = array();
            $data = array();
            for($i=$tag_len;$i>=1;$i--){
                $art = $this->combination($order_tag_arr, $i);
                foreach($art as $v){
                    $dat[] = $v;
                }

            }

            foreach($dat as $dk=>$dv) {
                $info = $articleModel->getRecommend($where, $field, $dv);
                foreach($info as $v){
                    if($v['id'] == $vinfo['id']){
                        continue;
                    }
                    if(!array_key_exists($v['id'], $dap)){
                        $dap[$v['id']] = $v;
                        $mend_len--;
                    }

                }
                if($mend_len <=0 ){
                    break;
                }
            }
            if($mend_len <=0 ){
                $dap = array_slice($dap, 0, 5);
            }
        }

        return $dap;
    }

    public function changRecList($result){

        $rs = array();
        $mbpictModel = new \Admin\Model\MbPicturesModel();
        $mediaModel  = new \Admin\Model\MediaModel();
        //判断结果
        foreach($result as $key=>$v){
            foreach($v as $kk=> $vv){
                if(empty($vv)){
                    unset($result[$key][$kk]);
                }
            }
            $result[$key]['imageURL'] = $this->getOssAddr($v['imgurl']) ;
            if(!empty($v['index_img_url'])){
                $result[$key]['indexImgUrl'] = $this->getOssAddr($v['index_img_url']) ;
            }

            $result[$key]['contentURL'] = $this->getContentUrl($v['contenturl']).'?app=inner';
            if(!empty($v['videourl'])) $result[$key]['videoURL']   = substr($v['videourl'],0,strpos($v['videourl'], '.f')) ;
            if($v['type'] ==3){
                if(empty($v['name'])){
                    unset($result[$key]['name']);
                }else{
                    $ttp = explode('/', $v['name']);
                    $result[$key]['name'] = $ttp[2];
                }
            }
            if($v['type'] ==3 && empty($v['content'])){
                $result[$key]['type'] = 4;
            }
            $result[$key]['updatetime'] = date("Y-m-d",strtotime($result[$key]['updatetime']));
        }
        return $result;
    }


    public function showcontentyouyuan(){

        $id = I('get.id',0,'intval');
        $app_version = I('get.app','');
        if($app_version == 'inner'){

        }
        $sourcename = I('get.location','');
        $this->assign('sourc', $sourcename);
        $articleModel = new \Admin\Model\ArticleModel();
        $mbpictModel = new \Admin\Model\MbPicturesModel();
        $mediaModel  = new \Admin\Model\MediaModel();
        $vinfo = $articleModel->where('id='.$id.' and state =2')->find();
        if(empty($vinfo)){
            $this->display('null');
            exit;
        }
        if($id && $vinfo){
            $catid = $vinfo['hot_category_id'];
            $vinfo['content'] = html_entity_decode($vinfo['content']);
            $vinfo['minu_time'] = round($vinfo['content_word_num']/600);
            if($vinfo['minu_time']<1){
                $vinfo['minu_time'] = 1;
            }

            $vinfo['update_time'] = date("Y-m-d",strtotime($vinfo['update_time']));

            $m_article_source = new \Admin\Model\ArticleSourceModel();
            $loginfo = $m_article_source->find($vinfo['source_id']);
            $media_info = $mediaModel->getMediaInfoById($loginfo['logo']);
            $loginfo['oss_addr'] = $media_info['oss_addr'];

            $this->assign('linfo', $loginfo);
            if ($catid == 103) {
                $oss_host = get_oss_host();
                $vinfo['img_url'] = $oss_host.$vinfo['img_url'];
                if($vinfo['index_img_url']){
                    $vinfo['index_img_url'] = $oss_host.$vinfo['index_img_url'];
                }
                $display_html = 'special';

            }else{
                $arinfo = $this->judgeRecommendInfo($vinfo);
                if($arinfo){
                    foreach($arinfo as $dv){
                        $where = 'AND mc.id = '. $dv['id'];
                        $dap = $articleModel->getArtinfoById($where);
                        $res[] = $dap;
                    }
                    $data = $this->changRecList($res);
                }else{
                    $data = array();
                }

                $this->assign('list', $data);
                if($vinfo['type']==1){//图文
                    $display_html = 'newshowcontent';
                }elseif($vinfo['type']==3){
                    $tx_url = $vinfo['tx_url'];
                    $this->assign('tx_url', $tx_url);
                    $display_html = 'newshowvideocontent';
                }else{
                    // 图集


                    $info =  $mbpictModel->where('contentid='.$id)->find();
                    $detail_arr = json_decode($info['detail'], true);

                    foreach($detail_arr as $dk=> $dr){
                        $media_info = $mediaModel->getMediaInfoById($dr['aid']);
                        $detail_arr[$dk]['pic_url'] =$media_info['oss_addr'];

                    }
                    $this->assign('detaillist', $detail_arr);
                    $display_html = 'newstuji';
                }
            }
        }else{
            $vinfo = array();
            $display_html = 'newshowcontent';
        }
        $this->assign('vinfo',$vinfo);
        $this->display($display_html);
    }


    public function showcontent(){
        $host_name = C('HTTPS_HOST_NAME').'/admin';
        $this->assign('hostnamed',$host_name);
        $id = I('get.id',0,'intval');
        $app_version = I('get.app','');
        if($app_version == 'inner'){
            //newread证明在客户端
            $sourcename = I('get.location','');
            $this->assign('sourc', $sourcename);
            $articleModel = new \Admin\Model\ArticleModel();
            $mbpictModel = new \Admin\Model\MbPicturesModel();
            $mediaModel  = new \Admin\Model\MediaModel();
            $vinfo = $articleModel->where('id='.$id.' and state =2')->find();
            if(empty($vinfo)){
                $this->display('null');
                exit;
            }
            if($id && $vinfo){
                $catid = $vinfo['hot_category_id'];
                $vinfo['content'] = html_entity_decode($vinfo['content']);
                $vinfo['minu_time'] = round($vinfo['content_word_num']/600);
                if($vinfo['minu_time']<1){
                    $vinfo['minu_time'] = 1;
                }

                $vinfo['update_time'] = date("Y-m-d",strtotime($vinfo['update_time']));

                $m_article_source = new \Admin\Model\ArticleSourceModel();
                $loginfo = $m_article_source->find($vinfo['source_id']);
                $media_info = $mediaModel->getMediaInfoById($loginfo['logo']);
                $loginfo['oss_addr'] = $media_info['oss_addr'];

                $this->assign('linfo', $loginfo);
                if ($catid == 103) {
                    $oss_host = get_oss_host();
                    $vinfo['img_url'] = $oss_host.$vinfo['img_url'];
                    if($vinfo['index_img_url']){
                        $vinfo['index_img_url'] = $oss_host.$vinfo['index_img_url'];
                    }
                    $display_html = 'special';

                }else{
                    $arinfo = $this->judgeRecommendInfo($vinfo);
                    if($arinfo){
                        foreach($arinfo as $dv){
                            $where = 'AND mc.id = '. $dv['id'];
                            $dap = $articleModel->getArtinfoById($where);
                            $res[] = $dap;
                        }
                        $data = $this->changRecList($res);
                    }else{
                        $data = array();
                    }

                    $this->assign('list', $data);
                    if($vinfo['type']==1){//图文
                        $display_html = 'newshowcontent';
                    }elseif($vinfo['type']==3){
                        $oss_host = get_oss_host();
                        $img_url = $oss_host.$vinfo['img_url'];
                        $this->assign('img_url',$img_url);
                        $tx_url = $vinfo['tx_url'];
                        $this->assign('tx_url', $tx_url);
                        $display_html = 'newshowvideocontent';
                    }else{
                        // 图集


                        $info =  $mbpictModel->where('contentid='.$id)->find();
                        $detail_arr = json_decode($info['detail'], true);

                        foreach($detail_arr as $dk=> $dr){
                            $media_info = $mediaModel->getMediaInfoById($dr['aid']);
                            $detail_arr[$dk]['pic_url'] =$media_info['oss_addr'];

                        }
                        $this->assign('detaillist', $detail_arr);
                        $display_html = 'newstuji';
                    }
                }
            }else{
                $vinfo = array();
                $display_html = 'newshowcontent';
            }
        }else{
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
                    $oss_host = get_oss_host();
                    $img_url = $oss_host.$vinfo['img_url'];
                    $this->assign('img_url',$img_url);
                    $this->assign('tx_url',$tx_url);
                    
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
        }
        $this->assign('vinfo',$vinfo);
        $this->display($display_html);
    }
}