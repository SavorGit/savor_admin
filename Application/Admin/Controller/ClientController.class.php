<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Weixin_api;
/**
 * @desc 客户端页面
 *
 */
class ClientController extends Controller {
    private $picRecommondNums;      //图集推荐条数
    private $imgTextRecommondNums;  //图文推荐条数
    private $videoRecommondNums;     //视频推荐条数
    public function __construct() {
        parent::__construct();
        $this->picRecommondNums    = 3;
        $this->imgTextRecommondNums= 3;
        $this->videoRecommondNums  = 3;
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
        if($vinfo['type']==0 || $vinfo['type'] ==1){//纯文本、图文
            $mend_len = $this->imgTextRecommondNums;
        }else if($vinfo['type']==2){//图集
            $mend_len = $this->picRecommondNums;
        }else if($vinfo['type']==3){//视频
            $mend_len = $this->videoRecommondNums;
        }
        $articleModel = new \Admin\Model\ArticleModel();
        //获取推荐列表
        $order_tag = $vinfo['order_tag'];
        $order_tag_arr = explode(',', $order_tag);
        $tag_len = count($order_tag_arr);
        //根据相同的文章类型的标签获取推荐 开始
        if($tag_len == 0 || empty($order_tag)){
            $dap = array();
        }else{
            $where = "1=1 and state = 2 and (hot_category_id = 101 or hot_category_id = 102 )  and type = ".$vinfo['type'];
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
            $nums = 0;
            foreach($dat as $dk=>$dv) {
                $info = $articleModel->getRecommend($where, $field, $dv);
               // var_dump($articleModel->getLastSql());
                foreach($info as $v){
                    if($v['id'] == $vinfo['id']){
                        continue;
                    }
                    if(!array_key_exists($v['id'], $dap)){
                        $dap[$v['id']] = $v;
                    }
                }
                $nums = count($dap);
                if($nums>=$mend_len){
                    break;
                }
            }
            
        }
        //根据相同的文章类型的标签获取推荐 结束
        //其他全分类查找推荐 开始
        if($nums<$mend_len){
            if($tag_len){//如果该文章有标签
                $where = "1=1 and state = 2  and hot_category_id in(101,102)";
                $field = 'id,title,order_tag';
        
                foreach($dat as $dk=>$dv) {
                    $info = $articleModel->getRecommend($where, $field, $dv);
                    foreach($info as $v){
                        if($v['id'] == $vinfo['id']){
                            continue;
                        }
                        if(!array_key_exists($v['id'], $dap)){
                            $dap[$v['id']] = $v;
                        }
                    }
                    $nums = count($dap);
                    if($nums>=$mend_len){
                        break;
                    }
                }
            }
        }
        //其他全分类查找推荐 结束
        //获取最新最新内容开始
        if($nums<$mend_len){
            $now_date = date('Y-m-d H:i:s',time());
            $info = $articleModel->getWhere("hot_category_id != 103 and state =2 and bespeak_time<='".$now_date."'",'id,title,order_tag',' create_time desc','limit 0,10');            foreach($info as $v){
                if($v['id'] == $vinfo['id']){
                    continue;
                }
                if(!array_key_exists($v['id'], $dap)){
                    $dap[$v['id']] = $v;
                }
            }
        }
        //获取最新最新内容 结束
        $dap = array_slice($dap, 0, $mend_len);
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

        $host_name = 'https://'.$_SERVER['HTTP_HOST'].'/admin';
        $this->assign('hostnamed',$host_name);
        $id = I('get.id',0,'intval');
        $app_version = I('get.app','');
        $oss_host = get_oss_host();
        $this->skipShop($id);
        if($app_version == 'inner'){
            //newread证明在客户端
            $sourcename = I('get.location','');
            $this->assign('sourc', $sourcename);
            $articleModel = new \Admin\Model\ArticleModel();
            $mbpictModel = new \Admin\Model\MbPicturesModel();
            $mediaModel  = new \Admin\Model\MediaModel();
            $preview = I('get.preview','0','intval');
            if($preview ==1){
                $where = 'id='.$id;
            }else {
                $where = 'id='.$id.' and state =2';
            }
            $vinfo = $articleModel->where($where)->find();
            if(empty($vinfo)){
                $vinfo = $articleModel->where('id='.$id)->find();
                if($vinfo['hot_category_id']!=103){
                    if(empty($vinfo)){
                        //获取最新最新内容开始
                        //推荐数
                        if($vinfo['type']==0 || $vinfo['type'] ==1){//纯文本、图文
                            $mend_len = $this->imgTextRecommondNums;
                        }else if($vinfo['type']==2){//图集
                            $mend_len = $this->picRecommondNums;
                        }else if($vinfo['type']==3){//视频
                            $mend_len = $this->videoRecommondNums;
                        }
                        $articleModel = new \Admin\Model\ArticleModel();
                        $info = $articleModel->getWhere(' state =2','id,title,order_tag',' create_time desc','limit 0,'.$mend_len);
                        $dap = array();
                        foreach($info as $v){
                            if($v['id'] == $vinfo['id']){
                                continue;
                            }
                            if(!array_key_exists($v['id'], $dap)){
                                $dap[$v['id']] = $v;
                            }
                        }
                        $arinfo = $dap;
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
                        $this->assign('list',$data);
                    }else {
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
                        $this->assign('list',$data);
                    } 
                } else {

                }
                $this->display('null');
                exit;
            }
            if($id && $vinfo){
                $is_wx = checkWxbrowser();
                if($is_wx){
                    $issq =  I('issq',0,'intval');
                    
                    $url = $this->getContentUrl($vinfo['content_url']).'?app=inner';
                    if(!empty($issq)){
                        $url .='&issq=1';
                        $this->wxAuthorLog($url,$vinfo['id']);
                    }
                    
                    
                }
                $catid = $vinfo['hot_category_id'];
                $vinfo['content'] = html_entity_decode($vinfo['content']);
                $vinfo['minu_time'] = round($vinfo['content_word_num']/600);
                if($vinfo['minu_time']<1){
                    $vinfo['minu_time'] = 1;
                }

                $vinfo['update_time'] = date("Y-m-d",strtotime($vinfo['create_time']));

                $m_article_source = new \Admin\Model\ArticleSourceModel();
                $loginfo = $m_article_source->find($vinfo['source_id']);
                $media_info = $mediaModel->getMediaInfoById($loginfo['logo']);
                $loginfo['oss_addr'] = $media_info['oss_addr'];

                $this->assign('linfo', $loginfo);
                if ($catid == 103) {

                    $vinfo['img_url'] = $oss_host.$vinfo['img_url'];
                    if($vinfo['index_img_url']){
                        $vinfo['index_img_url'] = $oss_host.$vinfo['index_img_url'];
                    }
                    $display_html = 'newshowcontent';

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
                }else if($vinfo['type']==2){
                    $mediaModel  = new \Admin\Model\MediaModel();
                    $mbpictModel = new \Admin\Model\MbPicturesModel();
                    $m_article_source = new \Admin\Model\ArticleSourceModel();
                    $loginfo = $m_article_source->find($vinfo['source_id']);
                    $media_info = $mediaModel->getMediaInfoById($loginfo['logo']);
                    $loginfo['oss_addr'] = $media_info['oss_addr'];
                    
                    $this->assign('linfo', $loginfo);
                    
                    $info =  $mbpictModel->where('contentid='.$id)->find();
                    $detail_arr = json_decode($info['detail'], true);
                    
                    foreach($detail_arr as $dk=> $dr){
                        $media_info = $mediaModel->getMediaInfoById($dr['aid']);
                        $detail_arr[$dk]['pic_url'] =$media_info['oss_addr'];
                    
                    }
                    $this->assign('detaillist', $detail_arr);
                    $display_html = 'newstuji';
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
        $wpi = new Weixin_api();
        $share_url ='http://' .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];



        $shareimg = 'http://'.$_SERVER['HTTP_HOST'].'/Public/admin/assets/img/logo_100_100.jpg';

        $share_title = $vinfo['title'];
        if($vinfo['type'] == 1) {
            if(empty($vinfo['content'])){
                $share_desc = '小热点，陪伴你创造财富，享受生活。';
            }else{
                $cot = html_entity_decode($vinfo['content']);
                $cot = strip_tags($cot);
                $share_desc = mb_substr($cot,0,50);
            }
        } elseif($vinfo['type'] == 2 || $vinfo['type'] == 3) {
            $share_desc = '热点聚焦，投其所好';
        }


        $share_config = $wpi->showShareConfig($share_url, $share_title,$share_desc,$share_url,$share_url);
        extract($share_config);
        $appid = $share_config['appid'];
        $noncestr = $share_config['noncestr'];
        $signature = $share_config['signature'];
         $this->assign('noncestr', $noncestr);
         $this->assign('signature', $signature);
        $this->assign('appid', $appid);
        $this->assign('share_title', $share_title);
        $this->assign('share_desc', $share_desc);
        $this->assign('shareimg', $shareimg);
        $this->assign('share_link', $share_url);
        $this->assign('vinfo',$vinfo);
        $this->display($display_html);
    }
    /**
     * @desc 微信授权
     */
    public function wxAuthorLog($url,$contentid){
        
        //$url = 'http://devp.admin.littlehotspot.com/content/2785.html?app=inner';
        $m_weixin_api = new \Common\Lib\Weixin_api();
        //微信授权登录开始
        $state = I('state','wxsq001','trim') ;
        $code = I('code');
        //$issq = I('issq',1,'intval');
        $iswx = checkWxbrowser();
        if($iswx==1){ 
            $redirect_url = urlencode($url);
            
            $host_name = C('CONTENT_HOST');
            $jumpUrl = $host_name.'admin/wxapply/index?scope=1&redirect_url='.$redirect_url;
            if (!$code || $state!='wxsq001') {
                header("Location:".$jumpUrl);
                exit;
            }
            $result = $m_weixin_api->getWxOpenid($code,$url);
            $openid = $result['openid'];
            $wxUserinfo = $m_weixin_api->getWxUserInfo($result['access_token'],$openid);
            
            $wxUserinfo['nickname'] = base64_encode($wxUserinfo['nickname']);
            $map =  array();
            $map['openid'] = $wxUserinfo['openid'];
            $map['nickname'] = $wxUserinfo['nickname'];
            $map['sex']      = $wxUserinfo['sex'];
            $map['country']  = $wxUserinfo['country'];
            $map['province'] = $wxUserinfo['province'];
            $map['city']     = $wxUserinfo['city'];
            $map['contentid']= $contentid;
            $map['create_time'] = date('Y-m-d H:i:s'); 
            
            $ip = get_client_ip(); 
            $map['ip_addr'] = $ip;
            $geoArr = getgeoByip($ip);
            $map['long'] = $geoArr['x'];
            $map['lat'] = $geoArr['y'];
            $m_content_wx_auth =  new \Admin\Model\ContentWxAuthModel(); 
            $m_content_wx_auth->addInfo($map);
        }
    }
    private function skipShop($id){
        
        if($id==-1){
            $url = 'https://detail.youzan.com/show/goods?alias=278x9zar5q2uy&reft=1518140890930&spm=f47880926&sf=wx_sm';
            header('Location:'.$url);
            exit;
        }else if($id==-2){
            $url = 'https://mp.weixin.qq.com/s/ZqIUAvY8jIY2EXC1fI1pUg';
            header('Location:'.$url);
            exit;
        }else if($id==-4){
            $url ="https://a.appcpa.net/easyLinkPage/index.html?easy_url=ELwRJhIp&ios_url=https%3A%2F%2Flnk0.com%2F8Mxdck&android_url=https%3A%2F%2Flnk0.com%2FgU9gM1";
            header('Location:'.$url);
            exit;
        }else if($id==-6){
            $url = "https://item.m.jd.com/product/14351822683.html?utm_source=iosapp&utm_medium=appshare&utm_campaign=t_335139774&utm_term=Wxfriends&ShareTm=kjGW4RMtAZfLRQ25qUACDWsWw2o7rXla+fgM2d0Ed/J6vAh3ZVpc2TigaSAKSDE/kEhsRBVeyGMjhtAmDlOe3Y4hVdSdEFMzZK/OlaPdlHaoT4bVUCMsl0OPZ6HfThTdsSjmthgFytMsxu8izRBAiRKQWPhMi+OGGQW7GeuCgfg=&from=singlemessage";
             header('Location:'.$url);
            exit;
        }else if($id==-7){
            $url = "https://mp.weixin.qq.com/s/T-NZ0BDHfs58rfeS1BhQfA";
            header('Location:'.$url);
            exit;
        }else if($id==-8){
            $url = "https://mp.weixin.qq.com/s/2fyRGwmtx6gEww6OlhL17g";
            header('Location:'.$url);
            exit;
        }else if($id==-9){
            $url ="https://mp.weixin.qq.com/s/KXI3E2PIGl6ESG9tBu01Kw";
            header('Location:'.$url);
            exit;
        }
    }
}