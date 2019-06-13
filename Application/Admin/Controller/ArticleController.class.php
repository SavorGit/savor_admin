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
use Common\Lib\SavorRedis;
class ArticleController extends BaseController {
    
    public  $path = 'content/img';
    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->oss_host = get_oss_host();
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


    public function delpictures() {
        $gid = I('get.id', 0, 'int');
        //查找是否在首页内容中引用
        if($gid) {
            $mbHomeModel = new \Admin\Model\HomeModel();
            $mbpicModel = new \Admin\Model\MbPicturesModel();
            $res = $mbHomeModel->where('content_id='.$gid)->find();
            if($res) {
                $this->error('首页有引用不可删除!');
            }
            $artModel = new ArticleModel();
            $result = $artModel -> delData($gid);
            //删除mb_picutres表
            $mbpicModel->delData();

            if($result) {
                $this->output('删除成功', 'content/getlist',2);
            } else {
                $this->output('删除失败', 'content/getlist',1);
            }
        } else {
            $this->error('删除失败,缺少参数!');
        }
    }


    public function delart() {
        $gid = I('get.id', 0, 'int');
        //查找是否在首页内容中引用
        if($gid) {

            //判断是否在专题组中文章
            $spRelation = new \Admin\Model\SpecialGroupRelationModel();
            $fields = 'sgr.name';
            $map['sgrp.sarticleid'] = $gid;
            $map['_string'] = 'sgr.state=0 or sgr.state=1';
            $res = $spRelation->judgeArtRelation($fields, $map);
            if($res) {
                $this->error('已用于'.$res['name'].'专题组，解除关联后才可删除');
            }
            $mbHomeModel = new \Admin\Model\HomeModel();
            $res = $mbHomeModel->where('content_id='.$gid)->find();
            if($res) {
                $this->error('首页有引用不可删除!');
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


    
    /*
     * 显示并生成H5页面地址
     */
    public function showcontent($id){
        $artModel = new ArticleModel();
        $vinfo = $artModel->where('id='.$id)->find();
        $title = $vinfo['title'];
        $content = html_entity_decode($vinfo['content']);
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
        $replacment = '<img src='.__ROOT__ .'${1}>';
        $content =  preg_replace($pattern, $replacment, $content);
        $this->assign('contenttitle', $title);
        $this->assign('content',$content);
        ob_start();
        $this->display('showcontent');
        $content = ob_get_contents();//取得php页面输出的全部内容
        echo $content;
        $path = SITE_TP_PATH.'/Public/html/article';
        if ( !(is_dir($path)) ) {
            mkdir ( $path, 0777, true );
        }
        $fp = fopen($path."/".$id.".html", "w");
        fwrite($fp, $content);
        fclose($fp);
        ob_end_clean();
    }



    /*
    * 显示并生成H5页面地址视频地址
    */
    public function showvideocontent($id, $url){
        $artModel = new ArticleModel();
        $vinfo = $artModel->where('id='.$id)->find();
        $title = $vinfo['title'];
        $content = html_entity_decode($vinfo['content']);
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
        $replacment = '<img src='.__ROOT__ .'${1}>';
        $url_arr = explode('?id=', $url);
        $url_id = $url_arr['1'];
        $this->assign('contenttitle', $title);
        $this->assign('videoaaa', $url_id);
        $this->assign('videobbb', $url_id);
        $content =  preg_replace($pattern, $replacment, $content);
        $this->assign('content',$content);
        ob_start();
        $this->display('showvideocontent');
        $content = ob_get_contents();//取得php页面输出的全部内容
        $content = str_replace('videobbbqqqqqqqqqq',$url_id,$content);
        $path = SITE_TP_PATH.'/Public/html/video';
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
        
        $tmp_hotel_arr = getVsmallHotelList();
        if($type == 1){
            //判断表中是否有
            $res = $mbHomeModel->where('content_id='.$artid)->find();
            if( $res ){
                $this->output('失败文章已经存在', 'content/getlist',3,0);
            } else {
                $artModel = new  \Admin\Model\ArticleModel();
                $arr = $artModel->find($artid);
                $state = $arr['state'];
                if ($state != 2) {
                    $this->output('失败审核状态不允许', 'content/getlist',3,0);

                }
                //期刊
                $mbperModel = new \Admin\Model\MbPeriodModel();
                $num = $mbperModel->count();
                $time = time();
                $dat['period'] = date("YmdHis",$time);
                $dat['update_time'] = date("Y-m-d H:i:s",$time);
                if($num>0){
                    $sql = "update savor_mb_period set period=".$dat['period'].",update_time='".$dat['update_time']."'";
                    $rest = $mbperModel->execute($sql);
                }else{
                    $mbperModel->add($dat);
                }

                $userInfo = session('sysUserInfo');
                $save[] = array();
                $md5 = $arr['vod_md5'];
                if ($md5) {
                    $save['is_demand'] = 1;
                }
                $save['content_id'] = $artid;
                $max_nu = $mbHomeModel->max('sort_num');
                $save['sort_num'] = $max_nu+1;
                $save['creator_id'] = $userInfo['id'];
                $save['create_time'] = date("Y-m-d H:i:s", time());
                $save['update_time'] = $save['create_time'];
                $res = $mbHomeModel->add($save);
                if($res){
                    
                    //新虚拟小平台接口
                    $redis = SavorRedis::getInstance();
                    $redis->select(10);
                    $v_hotel_list_key = C('VSMALL_HOTELLIST');
                    $redis_result = $redis->get($v_hotel_list_key);
                    $v_hotel_list = json_decode($redis_result,true);
                    $v_hotel_arr = array_column($v_hotel_list, 'hotel_id');  //虚拟小平台酒楼id
                    $v_vod_key = C('VSMALL_VOD');
                    foreach($v_hotel_arr as $v){
                        $keys_arr = $redis->keys($v_vod_key.$v."*");
                        foreach($keys_arr as $vv){
                            $redis->del($vv);
                        }
                    }
                    sendTopicMessage($tmp_hotel_arr, 11);
                    $this->output('操作成功!', 'article/homemanager',2);
                }else{
                    $this->output('操作失败!', 'content/getlist');
                }
            }
        } else {
            $max_nu = $mbHomeModel->max('sort_num');
            $save['sort_num'] = $max_nu+1;
            $res_save = $mbHomeModel->where('id='.$id)->save($save);
            if($res_save){
                sendTopicMessage($tmp_hotel_arr, 11);
                $this->output('操作成功!', 'content/getlist',3);
                die;
            }else{
                $this->output('操作失败!', 'article/homemanager');
            }

        }
    }


    public function homemanager(){
        $mbHomeModel = new \Admin\Model\HomeModel();
        $artModel = new  \Admin\Model\ArticleModel();
        $catModel = new \Admin\Model\HotCategoModel;
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
        $name = I('name');
        $con_id_arr = $mbHomeModel->field('content_id')->select();
        if($name){
            //去content表找
            $map['title'] = array('like','%'.$name.'%');
            $ar_id_arr = $artModel->field('id')->where($map)->select();
            if ($ar_id_arr) {
                $ar_arr = array_column($ar_id_arr, 'id');
                $cr_arr = array_column($con_id_arr, 'content_id');
                $inc_arr = array_intersect($ar_arr,$cr_arr);
                if($inc_arr){
                    $inc_str = implode(',', $inc_arr);
                    $where .= " AND content_id in (".$inc_str.")";
                } else {
                    $where .= " AND id<0";
                }
            } else {
                $where .= " AND id<0";
            }
            $this->assign('name',$name);
        }
        $result = $mbHomeModel->getList($where,$orders,$start,$size);
        /*array_map(function($ar,$cr){
            var_dump($ar);
            var_dump($cr);
        }, $ar_id_arr, $con_id_arr);*/

        $t_size = $artModel->getTotalSize($con_id_arr);
        $datalist = $artModel->changeIdjName($result['list'], $cat_arr);

        $this->assign('list', $datalist);
        $this->assign('tsize', $t_size);
        $this->assign('page',  $result['page']);
        $this->display('homearticle');
    }


    /*
     * @desc 分类内容排序
     */
    public function hotsortmanager(){
        $artModel = new  \Admin\Model\ArticleModel();
        $m_hot_category = new \Admin\Model\HotCategoryModel();
        $cat_arr = $m_hot_category->select();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','sort_num');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = "1=1";
        $hot_category_id = I('hot_catgory_id',101,'intval');
        $this->assign('hot_category_id',$hot_category_id);
        if($hot_category_id) $where .=" and hot_category_id='$hot_category_id' and state=2";
        $result = $artModel->getList($where,$orders,$start,$size);
        $result['list'] = $artModel->changeCatname($result['list']);
        $where = " state=1";
        $field = 'id,name';
        $category_list = $m_hot_category->getWhere($where, $field);
        $this->assign('vcainfo',$category_list);
        $ind = $start;
        foreach($result['list'] as &$val){
            $ind++;
            $val['indnum'] = $ind;
        }
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('hotarticle');
    }

    /*
     * 修改状态
     */
    public function changestatus(){
        $mbHomeModel = new \Admin\Model\HomeModel();
        $id = I('request.id');
        $flag = I('request.flag');
        $save['state'] = $flag;
        if($flag ==1){
            $homeInfo =  $mbHomeModel->field('content_id')->where('id='.$id)->find();
            $artModel = new \Admin\Model\ArticleModel();
            $info = $artModel->field('state')->where('id='.$homeInfo['content_id'])->find();
            if($info['state'] !=2){
            
                $this->error('该文章未审核通过，不能上线!');
            }    
        }
        
        if($mbHomeModel->where('id='.$id)->save($save)){
            //期刊
            $mbperModel = new \Admin\Model\MbPeriodModel();
            $num = $mbperModel->count();
            $time = time();
            $dat['period'] = date("YmdHis",$time);
            $dat['update_time'] = date("Y-m-d H:i:s",$time);
            if($num>0){
                $sql = "update savor_mb_period set period=".$dat['period'].",update_time='".$dat['update_time']."'";
                $rest = $mbperModel->execute($sql);
            }else{
                $mbperModel->add($dat);
            }
            $tmp_hotel_arr = getVsmallHotelList();
            sendTopicMessage($tmp_hotel_arr, 11);
            
            //新虚拟小平台接口
            $redis = SavorRedis::getInstance();
            $redis->select(10);
            $v_hotel_list_key = C('VSMALL_HOTELLIST');
            $redis_result = $redis->get($v_hotel_list_key);
            $v_hotel_list = json_decode($redis_result,true);
            $v_hotel_arr = array_column($v_hotel_list, 'hotel_id');  //虚拟小平台酒楼id
            $v_vod_key = C('VSMALL_VOD');
            foreach($v_hotel_arr as $v){
                $keys_arr = $redis->keys($v_vod_key.$v."*");
                foreach($keys_arr as $vv){
                    $redis->del($vv);
                }
            }
            
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
            $oss_host = $this->oss_host;
            $vainfo = $artModel->where('id='.$id)->find();
            $caid = $vainfo['hot_category_id'];
            if($caid==0){

            }else{
                $max_nu = $artModel->where('hot_category_id='.$caid.' and state=2')->max('sort_num');
            }
            $ar_sort_num = $vainfo['sort_num'];
            if ($ar_sort_num == $max_nu) {
                echo '<script>$.pdialog.closeCurrent();  alertMsg.error("当前内容已经置顶，不允许被修改");</script>';
            };



            if ($vainfo['bespeak_time'] == '1970-01-01 00:00:00' || $vinfo['bespeak_time'] == '0000-00-00 00:00:00') {
                $vainfo['bespeak_time'] = '';
            }
            $media_id = $vainfo['media_id'];
            $vainfo['oss_addr'] = $oss_host.$vainfo['img_url'];
            $vainfo['vid_type'] = 2;
            if($media_id){
                $mediaModel = new \Admin\Model\MediaModel();
                $mediainfo = $mediaModel->getMediaInfoById($vainfo['media_id']);
                $vainfo['videooss_addr'] = $mediainfo['oss_addr'];
                if($vainfo['index_img_url']){
                    $vainfo['index_oss_addr'] = $oss_host.$vainfo['index_img_url'];
                }   
                $vainfo['vid_type'] = 1;
                $vainfo['videoname'] = $mediainfo['name'];
            }
            //转换成html实体
            $vainfo['title'] = htmlspecialchars($vainfo['title']);
            //获取文章id本身有的标签
            $resp = $this->getTagInfoByArId($id);
            $this->assign('tagaddart',$resp);

            $this->assign('vainfo',$vainfo);

            $new = json_encode($resp);
            $new = preg_replace('/\"/', "'", $new);
            $this->assign('taginfod',$new);
        }else{
            $vainfo['duration'] = 0;
            $this->assign('vainfo',$vainfo);
        }
        $where = "1=1 and state=1";
        $field = 'id,name';
        $m_hot_category = new \Admin\Model\HotCategoModel();
        $vinfo = $m_hot_category->getWhere($where, $field);
        unset($vinfo[2]);
        $this->assign('vcainfo',$vinfo);
        //老分类
        $m_old_category = new \Admin\Model\CategoModel();
        $voldinfo = $m_old_category->getWhere($where, $field);
        $this->assign('voldinfo',$voldinfo);

        $pagearr = $this->getPageTag();
        $this->assign('pageinfo',$pagearr['list']);
        $this->assign('pagecount',$pagearr['page']);
        //添加来源
        $m_article_source = new \Admin\Model\ArticleSourceModel();
        $article_list = $m_article_source->order('convert(`name` using gbk) asc')->getAll();
        $this->assign('sourcelist',$article_list);
        $this->display('addvideo');
    }

    public function delhome(){
        $id = I('get.id');
        $mbHomeModel = new \Admin\Model\HomeModel();
        
        if($id){
            $info = $mbHomeModel->where('id='.$id)->find();
            if($info['state'] == 1){
                $this->error('添加到首页内容不可删除！');
            }
            //             $vinfo = $mbHomeModel->where(array('id'=>$id))->find();
            //             $this->assign('vinfo',$vinfo);
            $res_save=$mbHomeModel->where('id='.$id)->delete();
            if($res_save){
                //期刊
                $mbperModel = new \Admin\Model\MbPeriodModel();
                $num = $mbperModel->count();
                $time = time();
                $dat['period'] = date("YmdHis",$time);
                $dat['update_time'] = date("Y-m-d H:i:s",$time);
                if($num>0){
                    $sql = "update savor_mb_period set period=".$dat['period'].",update_time='".$dat['update_time']."'";
                    $rest = $mbperModel->execute($sql);
                }else{
                    $mbperModel->add($dat);
                }
                
                $this->output('操作成功!', 'article/homemanager',2);
            }else{
                $this->output('操作失败!', 'article/homemanager');
            }

        }
        //         return $this->display('addhome');
    }

    public function doSort(){

        $sort_str= I('post.soar');
        $sort_arr = explode(',', $sort_str);
        $sql = 'update savor_mb_home  SET sort_num = CASE id
';
        foreach($sort_arr as $k=>$v){
            $k = $k+1;
            $sql .= ' WHEN '.$v.' THEN '.$k;
        }
        $sql .= ' END WHERE id IN ('.$sort_str.')';
        $mbHome = new \Admin\Model\HomeModel();
        $bool = $mbHome->execute($sql);
        if($bool){
            //期刊
            $mbperModel = new \Admin\Model\MbPeriodModel();
            $num = $mbperModel->count();
            $time = time();
            $dat['period'] = date("YmdHis",$time);
            $dat['update_time'] = date("Y-m-d H:i:s",$time);
            if($num>0){
                $sql = "update savor_mb_period set period=".$dat['period'].",update_time='".$dat['update_time']."'";
                $rest = $mbperModel->execute($sql);
            }else{
                $mbperModel->add($dat);
            }
            $tmp_hotel_arr = getVsmallHotelList();
            sendTopicMessage($tmp_hotel_arr, 11);
            //新虚拟小平台接口
            $redis = SavorRedis::getInstance();
            $redis->select(10);
            $v_hotel_list_key = C('VSMALL_HOTELLIST');
            $redis_result = $redis->get($v_hotel_list_key);
            $v_hotel_list = json_decode($redis_result,true);
            $v_hotel_arr = array_column($v_hotel_list, 'hotel_id');  //虚拟小平台酒楼id
            $v_vod_key = C('VSMALL_VOD');
            foreach($v_hotel_arr as $v){
                $keys_arr = $redis->keys($v_vod_key.$v."*");
                foreach($keys_arr as $vv){
                    $redis->del($vv);
                }
            }
            
            $this->output('操作成功','article/homemanager');

        } else{
            $this->output('未改顺序','content/getlist',1,0);
        }

        /*    SET display_order = CASE id
        WHEN 1 THEN 3
        WHEN 2 THEN 4
        WHEN 3 THEN 5
    END
WHERE id IN (1,2,3)*/

    }


    public function addSort(){
        $mbHomeModel = new \Admin\Model\HomeModel();
        $artModel = new  \Admin\Model\ArticleModel();
        $catModel = new \Admin\Model\CategoModel;
        $cat_arr = $catModel->select();
        $order = I('_order','sort_num');
        $this->assign('_order',$order);
        $sort = I('_sort','asc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $where = "1=1";
        $result = $mbHomeModel->where($where)->order($order)->select();


        $con_id_arr = $mbHomeModel->field('content_id')->select();
        $t_size = $artModel->getTotalSize($con_id_arr);
        $datalist = $artModel->changeIdjName($result, $cat_arr);
        $name = I('name');
        if($name){
            //根据id取
            $this->assign('name',$name);
            $where .= "	AND name LIKE '%{$name}%'";
        }
        $this->assign('list', $datalist);
        $this->assign('tsize', $t_size);
        $this->display('homesort');
    }

    public function doAddvideo(){
        $mediaModel = new \Admin\Model\MediaModel();
        $artModel = new ArticleModel();
        $id                  = I('post.id');
        $save                = [];
        $save['title']        = I('post.title','','trim');
        $save['hot_category_id']        = I('post.hot_category_id',0,'intval');
        //老分类
        $save['category_id']        = I('post.old_category_id',0,'intval');
        $media_id = I('post.media_id','0','intval');//视频id
        $save['source_id']    = I('post.source_id',0,'intval');
        $contents = I('post.content','','strip_tags');
        if(empty($contents)){
            $save['content_word_num'] = 0;
        }else{
            $contents = str_replace('&nbsp;', '', $contents);
            $contents = myTrim($contents);
            $save['content_word_num'] = mb_strlen($contents,'UTF8');
        }
        
        $save['content']    = I('post.content','','htmlspecialchars');
        $save['type']    = 3;
        $save['state']    = 0;
        $save['tx_url'] = I('post.yunhref','');
        $save['update_time'] = date('Y-m-d H:i:s');
        $addtype = I('post.r1','0',intval);
        $save['bespeak_time'] = I('post.logtime','');

        $minu = I('post.minu','0','intval');
        $seco = I('post.seco','0','intval');
        if(empty($minu) && empty($seco)){
            $this->error('请输入有效的时长');
        }
        //处理标签
        $_POST['taginfo'] = preg_replace("/\'/", '"', $_POST['taginfo']);
        $tagr = json_decode ($_POST['taginfo'],true);
        $ar = array();
        //var_dump($tagr);
        foreach ($tagr as $t=>$v) {
            if(in_array($v['tagid'], $ar)){
                $this->error('标签不可有重复');
            }
            $ar[]=$v['tagid'];
        }
        $save['sort_tag'] = implode(',',$ar);
        sort($ar);
        $save['order_tag'] = implode(',',$ar);
        if(count($tagr)<1){
            $this->error('标签数应大于0');
        }


        $save['duration'] = $minu*60+$seco;
        $v_type    = I('post.r1','0','intval');
        //$image_host = 'http://'.C('OSS_BUCKET').'.'.C('OSS_HOST').'/';
        if($save['bespeak_time'] == '' || $save['bespeak_time']=='0000-00-00 00:00:00'){
            $save['bespeak'] = 0;
            $save['bespeak_time'] ='1970-01-01 00:00:00';

        }else{
            $save['bespeak'] = 1;
        }
        $covermedia_id = I('post.covervideo_id');//视频封面id
        if(empty($covermedia_id)){
            /*$this->output('失败封面必填!', 'content/getlist',3,0);
            die;*/
        }else{
            if($covermedia_id>0){//首页封面图
                $oss_addr = $mediaModel->find($covermedia_id);
                $oss_addr = $oss_addr['oss_addr'];
                $save['img_url'] = $oss_addr;
            }
        }
        $index_media_id = I('post.index_media_id');
        if(empty($index_media_id)){
            $save['index_img_url'] = '';
        }else{
            if($index_media_id>0){//首页封面图
                $oss_addr = $mediaModel->find($index_media_id);
                $oss_addr = $oss_addr['oss_addr'];
                $save['index_img_url'] = $oss_addr;
            }
        }
        if($media_id) {
            $oss_arr = $mediaModel->find($media_id);
            $oss_path = $oss_arr['oss_addr'];
            $save['size'] = $artModel->getOssSize($oss_path);
           // file_put_contents(APP_PATH . '../public/abcf.txt', $save['size']);
            $save['vod_md5'] = $oss_arr['md5'];
            $save['media_id'] = $media_id;
        }

        if($id){
            $this->changeTag($tagr, $id);
            if($addtype == 2){
                //mediaid去除，md5,
                $save['vod_md5'] = '';
                $save['media_id'] = 0;
            }else{
                if($addtype == 1 && $media_id==0){
                    $m_arr = $artModel->where('id='.$id)->find();
                    $meid = $m_arr['media_id'];
                    if ($meid){

                    }else{
                        $this->output('失败点播不能为空!', 'article/addvideo', 3,0);
                        die;
                    }

                }

            }
            if($artModel->where('id='.$id)->save($save)){
                //判断是否在首页点播中
                $homeModel = new \Admin\Model\HomeModel();
                $hinfo = $homeModel->where(array('content_id'=>$id))->find();
                if ($hinfo) {
                    $hid = $hinfo['id'];
                    $shome['state'] = 0;
                    $homeModel->where('id='.$hid)->save($shome);
                    
                    //新虚拟小平台接口
                    $redis = SavorRedis::getInstance();
                    $redis->select(10);
                    $v_hotel_list_key = C('VSMALL_HOTELLIST');
                    $redis_result = $redis->get($v_hotel_list_key);
                    $v_hotel_list = json_decode($redis_result,true);
                    $v_hotel_arr = array_column($v_hotel_list, 'hotel_id');  //虚拟小平台酒楼id
                    $v_vod_key = C('VSMALL_VOD');
                    foreach($v_hotel_arr as $v){
                        $keys_arr = $redis->keys($v_vod_key.$v."*");
                        foreach($keys_arr as $vv){
                            $redis->del($vv);
                        }
                    }
                }
                //$this->showvideocontent($id, $save['tx_url']);
                $this->output('修改成功!', 'content/getlist');
            }else{
                $this->output('操作失败!', 'content/getlist');
            }

        }else{
            if(!$covermedia_id) {
                $this->output('失败封面必填!', 'article/addvideo', 3,0);
            }
            //点播
            if($addtype == 1 && $media_id==0){
                $this->output('失败点播不能为空!', 'article/addvideo', 3,0);
            }
            $ret = $artModel->where(array('title'=>$save['title']))->find();
            if($ret){
                $this->output('失败文章标题存在!', 'content/getlist',3,0);
            }

            $save['type'] = 3;
            $save['create_time'] = date('Y-m-d H:i:s');
            $userInfo = session('sysUserInfo');
            $uname = $userInfo['remark'];
            $save['operators']    = $uname;
            if($artModel->add($save)){
                $id = $artModel->getLastInsID();
                $this->changeTag($tagr, $id);
               // $this->showvideocontent($id, $save['tx_url']);
                $dat['content_url'] = 'content/'.$id.'.html';
                $dat['sort_num'] = $id;
                $artModel->where('id='.$id)->save($dat);
                $this->output('操作成功!', 'content/getlist',1);
            }else{
                $this->output('操作失败!', 'content/getlist');
            }
        }
    }

    public function getTagInfoByArId($id){
        $tagModel = new \Admin\Model\TagModel();
        $res = $tagModel->where('article_id='.$id)->field('tagid,tagname')->select();
        return $res;
    }

    public function getPageTag(){
        $tagModel = new \Admin\Model\TagListModel();
        $size   = 20;//显示每页记录数
        $start = 1;
        $order = I('_order','convert(tagname using gbk)');
        $sort = I('_sort','asc');
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = "1=1";
        $tagname = I('tagname','','trim');
        if($tagname){
            $where .= "	AND tagname LIKE '%{$tagname}%'";
        }
        $where .= " AND flag = 1";
        $field = 'id,tagname';
        $result = $tagModel->getList($where,$orders,$start,$size,$field);
        $result['page'] = $tagModel->getPageCount($where);
        $result['page'] = ceil($result['page']/$size);
        return $result;
    }




    /**
     * 添加文章
     */
    public function addArticle(){

        $artModel = new ArticleModel();
        $userInfo = session('sysUserInfo');
        $uname = $userInfo['username'];
        $this->assign('uname',$uname);
        $id = I('get.id');
        $acctype = I('get.acttype');


        if ($acctype && $id){

            //获取最大sort_num
            $vinfo['state'] = 0;
            $vinfo = $artModel->where('id='.$id)->find();
            $ar_sort_num = $vinfo['sort_num'];
            $caid = $vinfo['hot_category_id'];
            if($caid==0){

            }else{
                $max_nu = $artModel->where('hot_category_id='.$caid.' and state=2')->max('sort_num');
            }
            if ($ar_sort_num == $max_nu) {
                echo '<script>$.pdialog.closeCurrent();  alertMsg.error("当前内容已经置顶，不允许被修改");</script>';
            };
            //转换成html实体
            $vinfo['title'] = htmlspecialchars($vinfo['title']);
            if ($vinfo['bespeak_time'] == '1970-01-01 00:00:00' ||  $vinfo['bespeak_time'] == '0000-00-00 00:00:00') {
                $vinfo['bespeak_time'] = '';
            }
            $oss_host = $this->oss_host;
            $vinfo['oss_addr'] = $oss_host.$vinfo['img_url'];
            if($vinfo['index_img_url']){
                $vinfo['index_oss_addr'] = $oss_host.$vinfo['index_img_url'];
            }
            $this->assign('vinfo',$vinfo);

            //获取文章id本身有的标签
            $resp = $this->getTagInfoByArId($id);
            if($resp){
                $this->assign('tagaddart',$resp);
                $new = json_encode($resp);
                $new = preg_replace('/\"/', "'", $new);
                $this->assign('taginfod',$new);
            }
            //[{"tagid":"34","tagname":"安卓"},{"tagid":"32","tagname":"ajax"},{"tagid":"33","tagname":"ios"},{"tagid":"57","tagname":"1   1"},{"tagid":"58","tagname":"1 1"},{"tagid":"45","tagname":"123"}]

        } else {
            $vinfo['img_style'] = 1;
            $this->assign('vinfo',$vinfo);
        }
        $where = "1=1 and state=1";
        $field = 'id,name';
        $m_hot_category = new \Admin\Model\HotCategoModel();
        $vinfo = $m_hot_category->getWhere($where, $field);
        //unset($vinfo[2]);
        $this->assign('vcainfo',$vinfo);
        //老分类
        $m_old_category = new \Admin\Model\CategoModel();
        $voldinfo = $m_old_category->getWhere($where, $field);

        $this->assign('voldinfo',$voldinfo);
        //添加标签
        $pagearr = $this->getPageTag();
        //添加来源
        $m_article_source = new \Admin\Model\ArticleSourceModel();
        $article_list = $m_article_source->order('convert(`name` using gbk) asc')->getAll();
       
        $this->assign('sourcelist',$article_list);
        $this->assign('pageinfo',$pagearr['list']);
        $this->assign('pagecount',$pagearr['page']);
        $this->display('addart');

    }

    public function changeTag($dat,$id){
        foreach ($dat as $k=>$v) {
            $dat[$k]['article_id'] = $id;
        }

        $tagModel = new \Admin\Model\TagModel();
        $where = 'article_id='.$id;
        $tagModel->delWhereData($where);
        $tagModel->addAll($dat);
    }

    public function doAddarticle(){


        $artModel = new ArticleModel();
        $id                  = I('post.id');
        $save                = [];
        $save['title']        = I('post.title','','trim');
        $save['hot_category_id']        = I('post.hot_category_id',0,'intval');
        $save['img_style']        = I('post.img_style',0,'intval');
        //$save['source']    = I('post.source','');
        $save['source_id']   = I('post.source_id');
        //老分类
        $save['category_id']        = I('post.old_category_id',0,'intval');
        $contents = I('post.content','','strip_tags');
        if(empty($contents)){
            $save['content_word_num'] = 0;
        }else{
            $contents = str_replace('&nbsp;', '', $contents);
            $contents = myTrim($contents);
            $save['content_word_num'] = mb_strlen($contents,'UTF8');
        }
        $save['content']    = I('post.content','','htmlspecialchars');
        $save['type']    = I('post.ctype','','intval');
        $save['state']    = 0;
        $save['update_time'] = date('Y-m-d H:i:s');
        $save['bespeak_time'] = I('post.logtime','');
        if($save['bespeak_time'] == '' || $save['bespeak_time']=='0000-00-00 00:00:00'){
            $save['bespeak'] = 0;
            $save['bespeak_time'] = '1970-01-01 00:00:00';
        }else{
            $save['bespeak'] = 1;
        }

        $mediaid = I('post.media_id','0','intval');
        $mediaModel = new \Admin\Model\MediaModel();
        if($mediaid){
            $oss_addr = $mediaModel->find($mediaid);
            $oss_addr = $oss_addr['oss_addr'];
            $save['img_url'] = $oss_addr;
            $save['media_id'] = $mediaid;
        }else{
            /*$this->output('失败封面必填!', 'content/getlist',3,0);
            die;*/
        }
        $index_media_id = I('post.index_media_id');
        if(empty($index_media_id)){
            $save['index_img_url'] = '';
        }else{
            if($index_media_id>0){//首页封面图
                $oss_addr = $mediaModel->find($index_media_id);
                $oss_addr = $oss_addr['oss_addr'];
                $save['index_img_url'] = $oss_addr;
            }
        }
        //处理标签
        $_POST['taginfo'] = preg_replace("/\'/", '"', $_POST['taginfo']);
        $tagr = json_decode ($_POST['taginfo'],true);
        $ar = array();
        foreach ($tagr as $t=>$v) {
            if(in_array($v['tagid'], $ar)){
                $this->error('标签不可有重复');
            }
            $ar[]=$v['tagid'];
        }
        $save['sort_tag'] = implode(',',$ar);
        sort($ar);
        $save['order_tag'] = implode(',',$ar);
        if(count($tagr)<1){
            $this->error('标签数应大于0');
        }
        if($id){
            //修改标签
            $this->changeTag($tagr, $id);
            if($artModel->where('id='.$id)->save($save)){
                //判断是否在首页点播中
                $homeModel = new \Admin\Model\HomeModel();
                $hinfo = $homeModel->where(array('content_id'=>$id))->find();
                if ($hinfo) {
                    $hid = $hinfo['id'];
                    $shome['state'] = 0;
                    $homeModel->where('id='.$hid)->save($shome);
                }
            
                $this->output('操作成功!', 'content/getlist');
            }else{
                $this->output('操作失败!', 'content/getlist');
            }
        }else{
            if(!$mediaid){
                $this->output('失败封面必填!', 'content/getlist',3,0);
                die;
            }
            $ret = $artModel->where(array('title'=>$save['title']))->find();
            if($ret){
                $this->output('失败文章标题存在!', 'content/getlist',3,0);
            }
            $save['create_time'] = date('Y-m-d H:i:s');
            $userInfo = session('sysUserInfo');
            $uname = $userInfo['remark'];
            $save['operators']    = $uname;
            $save['creator_id']   = $userInfo['id'];
            if($artModel->add($save)){
                $arid = $artModel->getLastInsID();
                //修改标签
                $this->changeTag($tagr, $arid);
                //$this->showcontent($arid);
                $dat['content_url'] = 'content/'.$arid.'.html';
                $dat['sort_num'] = $arid;
                $artModel->where('id='.$arid)->save($dat);
                $this->output('操作成功!', 'content/getlist');
            }else{
                $this->output('操作失败!', 'content/getlist');
            }
        }
    }




    public function doAddPictures(){
        $artModel = new ArticleModel();
        $mbpictModel = new \Admin\Model\MbPicturesModel();
        $id                  = I('post.id');
        $save                = [];
        $save['title']        = I('post.title','','trim');
        $save['hot_category_id']        = I('post.cate','','trim');
        //$save['source']    = I('post.source','');
        $save['source_id']   = I('post.source_id');
        $save['type']    = I('post.ctype','','intval');
        $save['state']    = 0;
        $save['update_time'] = date('Y-m-d H:i:s');
        $save['bespeak_time'] = I('post.logtime','');
        $picdat =  $_POST['pictuji'];
        $picdat_arr = json_decode($_POST['pictuji'], true);
        foreach ( $picdat_arr as $pv){
            if(empty($pv['atext'])) {
                $this->error('图集描述不可为空');
            }
            if(mb_strlen($pv['atext'])>60) {
                $this->error('内容最多为60字');
            }

        }
        if(count($picdat_arr)<5){
            $this->error('图集最少5个');
        }
        if($save['bespeak_time'] == '' || $save['bespeak_time']=='0000-00-00 00:00:00'
        ){
            $save['bespeak'] = 0;
            $save['bespeak_time'] = '1970-01-01 00:00:00';
        }else{
            $save['bespeak'] = 1;
        }

        $mediaid = I('post.media_id');
        $mediaModel = new \Admin\Model\MediaModel();
        $index_media_id = I('post.index_media_id');
        if(empty($mediaid)){
            $this->output('失败封面必填!', 'content/getlist',3,0);
            die;
        }else{
            if($mediaid>0){//首页封面图
                $oss_addr = $mediaModel->find($mediaid);
                $oss_addr = $oss_addr['oss_addr'];
                $save['img_url'] = $oss_addr;
            }
        }
        if(empty($index_media_id)){
            $save['index_img_url'] = '';
        }else{
            if($index_media_id>0){//首页封面图
                $oss_addr = $mediaModel->find($index_media_id);
                $oss_addr = $oss_addr['oss_addr'];
                $save['index_img_url'] = $oss_addr;
            }
        }
        //处理标签
        $_POST['taginfo'] = preg_replace("/\'/", '"', $_POST['taginfo']);
        $tagr = json_decode ($_POST['taginfo'],true);
        $ar = array();
        foreach ($tagr as $t=>$v) {
            if(in_array($v['tagid'], $ar)){
                $this->error('标签不可有重复');
            }
            $ar[]=$v['tagid'];
        }
        $save['sort_tag'] = implode(',',$ar);
        sort($ar);
        $save['order_tag'] = implode(',',$ar);
        if(count($tagr)<1){
            $this->error('标签数应大于0');
        }

        if($id){
            //修改标签
            $this->changeTag($tagr, $id);

            if($artModel->where('id='.$id)->save($save)){
                //修改图集
                $tuji = array();
                $tuji['detail'] = $picdat;
                $mbpictModel->where('contentid='.$id)->save($tuji);
                //判断是否在首页点播中
                $homeModel = new \Admin\Model\HomeModel();
                $hinfo = $homeModel->where(array('content_id'=>$id))->find();
                if ($hinfo) {
                    $hid = $hinfo['id'];
                    $shome['state'] = 0;
                    $homeModel->where('id='.$hid)->save($shome);
                }

                $this->output('操作成功!', 'content/getlist');
            }else{
                $this->output('操作失败!', 'content/getlist');
            }
        }else{
            if(!$mediaid){
                $this->output('失败封面必填!', 'content/getlist',3,0);
                die;
            }
            $ret = $artModel->where(array('title'=>$save['title']))->find();
            if($ret){
                $this->output('失败文章标题存在!', 'content/getlist',3,0);
            }
            $save['create_time'] = date('Y-m-d H:i:s');
            $userInfo = session('sysUserInfo');
            $uname = $userInfo['remark'];
            $save['operators']    = $uname;
            if($artModel->add($save)){
                $arid = $artModel->getLastInsID();
                //修改标签
                $this->changeTag($tagr, $arid);
                $dat['content_url'] = 'content/'.$arid.'.html';
                $dat['sort_num'] = $arid;
                $artModel->where('id='.$arid)->save($dat);
                //添加图集
                $tuji = array();
                $tuji['contentid'] = $arid;
                $tuji['detail'] = $picdat;
                $mbpictModel->addData($tuji);
                $this->output('操作成功!', 'content/getlist');
            }else{
                $this->output('操作失败!', 'content/getlist');
            }
        }
    }

    /**
     * @desc 发布图集
     */
    public function addpictures(){


        //添加标签
        $pagearr = $this->getPageTag();
        $this->assign('pageinfo',$pagearr['list']);
        $this->assign('pagecount',$pagearr['page']);

        //添加来源
        $m_article_source = new \Admin\Model\ArticleSourceModel();
        $article_list = $m_article_source->order('convert(`name` using gbk) asc')->getAll();
        $this->assign('sourcelist',$article_list);


        $hotcatModel = new \Admin\Model\HotCategoModel();
        $where = "1=1 and state=1";
        $field = 'id,name';
        $vinfo = $hotcatModel->getWhere($where, $field);
        unset($vinfo[2]);
        $this->assign('vcainfo',$vinfo);
        $this->display('addpics');
    }
    /**
     * @desc 编辑图集
     */
    public function editpictures(){

        $catModel = new \Admin\Model\HotCategoryModel();
        $artModel = new \Admin\Model\ArticleModel();
        $mbpicModel = new \Admin\Model\MbPicturesModel();
        $mediaModel = new \Admin\Model\MediaModel();
        $userInfo = session('sysUserInfo');
        $uname = $userInfo['username'];
        $this->assign('uname',$uname);
        $id = I('get.id');
        $vinfo['state'] = 0;
        $vinfo['id'] = $id;
        if ($id){
            $vinfo = $artModel->where('id='.$id)->find();
            //转换成html实体
            $caid = $vinfo['hot_category_id'];
            if($caid==0){

            }else{
                $max_nu = $artModel->where('hot_category_id='.$caid.' and state=2')->max('sort_num');
            }
            $ar_sort_num = $vinfo['sort_num'];

            if ($ar_sort_num == $max_nu) {
                echo '<script>$.pdialog.closeCurrent();  alertMsg.error("当前内容已经置顶，不允许被修改");</script>';
            };



            $vinfo['title'] = htmlspecialchars($vinfo['title']);
            if ($vinfo['bespeak_time'] == '1970-01-01 00:00:00' ||  $vinfo['bespeak_time'] == '0000-00-00 00:00:00') {
                $vinfo['bespeak_time'] = '';
            }

            $oss_host = $this->oss_host;
            $vinfo['oss_addr'] = $oss_host.$vinfo['img_url'];
            if($vinfo['index_img_url']){
                $vinfo['index_oss_addr'] = $oss_host.$vinfo['index_img_url'];
            }
            $this->assign('vinfo',$vinfo);

            //获取文章id本身有的标签
            $resp = $this->getTagInfoByArId($id);
            if($resp){
                $this->assign('tagaddart',$resp);
                $new = json_encode($resp);
                $new = preg_replace('/\"/', "'", $new);
                $this->assign('taginfod',$new);
            }
            //[{"tagid":"34","tagname":"安卓"},{"tagid":"32","tagname":"ajax"},{"tagid":"33","tagname":"ios"},{"tagid":"57","tagname":"1   1"},{"tagid":"58","tagname":"1 1"},{"tagid":"45","tagname":"123"}]
            //获取图集详细信息
            $pic_arr = $mbpicModel->getOne('contentid='.$id);
            $detail_info = json_decode($pic_arr['detail'],true);
            $count = 1;
            foreach($detail_info as $dkey=>$dval){
                $detail_info[$dkey]['num'] = $count;
                $detail_info[$dkey]['pic_num']= 'pics_map_'.$count;
                $detail_info[$dkey]['tex_num']= 'texta_pic_'.$count;
                $oss_arr = $mediaModel->find($dval['aid']);
                $oss_host = $this->oss_host;
                $oss_path = $oss_host.$oss_arr['oss_addr'];
                $detail_info[$dkey]['opath']= $oss_path;
                $count++;
            }
            $this->assign('detailinfo',$detail_info);

        }
        $where = "1=1 and state=1";
        $field = 'id,name';
        $vinfo = $catModel->getWhere($where, $field);
        $this->assign('vcainfo',$vinfo);

        //添加标签
        $pagearr = $this->getPageTag();
        //添加来源
        $m_article_source = new \Admin\Model\ArticleSourceModel();
        $article_list = $m_article_source->getAll();

        $this->assign('sourcelist',$article_list);
        $this->assign('pageinfo',$pagearr['list']);
        $this->assign('pagecount',$pagearr['page']);
        $this->display('editpics');

    }
    /**
     * 添加专题
     */
    public function addSpecial(){
    
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
            //转换成html实体
            $caid = $vinfo['hot_category_id'];
            if($caid==0){

            }else{
                $max_nu = $artModel->where('hot_category_id='.$caid.' and state=2')->max('sort_num');
            }
            $ar_sort_num = $vinfo['sort_num'];

            if ($ar_sort_num == $max_nu) {
                echo '<script>$.pdialog.closeCurrent();  alertMsg.error("当前内容已经置顶，不允许被修改");</script>';
            };


            $vinfo['title'] = htmlspecialchars($vinfo['title']);
            if ($vinfo['bespeak_time'] == '1970-01-01 00:00:00' ||  $vinfo['bespeak_time'] == '0000-00-00 00:00:00') {
                $vinfo['bespeak_time'] = '';
            }
    
            $oss_host = $this->oss_host;
            $vinfo['oss_addr'] = $oss_host.$vinfo['img_url'];
            $vinfo['index_oss_addr'] = $oss_host.$vinfo['index_img_url'];
            if($vinfo['index_img_url']){
                $vinfo['index_oss_addr'] = $oss_host.$vinfo['index_img_url'];
            }
            $this->assign('vinfo',$vinfo);
    
            //获取文章id本身有的标签
            $resp = $this->getTagInfoByArId($id);
            if($resp){
                $this->assign('tagaddart',$resp);
                $new = json_encode($resp);
                $new = preg_replace('/\"/', "'", $new);
                $this->assign('taginfod',$new);
            }
           
        }
        
        //添加标签
        $pagearr = $this->getPageTag();
        //添加来源
        $m_article_source = new \Admin\Model\ArticleSourceModel();
        $article_list = $m_article_source->getAll();
         
        $this->assign('sourcelist',$article_list);
        $this->assign('pageinfo',$pagearr['list']);
        $this->assign('pagecount',$pagearr['page']);
        $this->display('addspecial');
    
    }
    public function doAddSpecial(){
    
        $artModel = new ArticleModel();
        $id                  = I('post.id');
        $save                = [];
        $save['title']        = I('post.title','','trim');
        $save['share_title']    = I('post.share_title','','trim');
       
        $contents = I('post.content','','strip_tags');
        if(empty($contents)){
            $save['content_word_num'] = 0;
        }else{
            $contents = str_replace('&nbsp;', '', $contents);
            $contents = myTrim($contents);
            $save['content_word_num'] = mb_strlen($contents,'UTF8');
        }
        $save['content']    = I('post.content','','htmlspecialchars');
        $save['type']    = I('post.ctype','','intval');
        $save['state']    = 0;
        $save['update_time'] = date('Y-m-d H:i:s');
        $save['bespeak_time'] = I('post.logtime','');
        if($save['bespeak_time'] == '' || $save['bespeak_time']=='0000-00-00 00:00:00'){
            $save['bespeak'] = 0;
            $save['bespeak_time'] = '1970-01-01 00:00:00';
        }else{
            $save['bespeak'] = 1;
        }
    
        $mediaid = I('post.media_id');
        $mediaModel = new \Admin\Model\MediaModel();
        $mediaModel = new \Admin\Model\MediaModel();
        if($mediaid){
            $oss_addr = $mediaModel->find($mediaid);
            $oss_addr = $oss_addr['oss_addr'];
            $save['img_url'] = $oss_addr;
            $save['media_id'] = $mediaid;
        }else{
            $this->output('失败封面必填!', 'content/getlist',3,0);
            die;
        }
        $index_media_id = I('post.index_media_id');
        if(empty($index_media_id)){
            $save['index_img_url'] = '';
        }else{
            if($index_media_id>0){//首页封面图
                $oss_addr = $mediaModel->find($index_media_id);
                $oss_addr = $oss_addr['oss_addr'];
                $save['index_img_url'] = $oss_addr;
            }
        }
        //处理标签
        $_POST['taginfo'] = preg_replace("/\'/", '"', $_POST['taginfo']);
        $tagr = json_decode ($_POST['taginfo'],true);
        $ar = array();
        foreach ($tagr as $t=>$v) {
            if(in_array($v['tagid'], $ar)){
                $this->error('标签不可有重复');
            }
            $ar[]=$v['tagid'];
        }
        $save['sort_tag'] = implode(',',$ar);
        sort($ar);
        $save['order_tag'] = implode(',',$ar);
        if(count($tagr)<1){
            $this->error('标签数应大于0');
        }
        if($id){
            //修改标签
            $this->changeTag($tagr, $id);
            if($artModel->where('id='.$id)->save($save)){
                //判断是否在首页点播中
                $homeModel = new \Admin\Model\HomeModel();
                $hinfo = $homeModel->where(array('content_id'=>$id))->find();
                if ($hinfo) {
                    $hid = $hinfo['id'];
                    $shome['state'] = 0;
                    $homeModel->where('id='.$hid)->save($shome);
                }
    
                $this->output('操作成功!', 'content/getlist');
            }else{
                $this->output('操作失败!', 'content/getlist');
            }
        }else{
            if(!$mediaid){
                $this->output('失败封面必填!', 'content/getlist',3,0);
                die;
            }
            $ret = $artModel->where(array('title'=>$save['title']))->find();
            if($ret){
                $this->output('失败文章标题存在!', 'content/getlist',3,0);
            }
            $save['create_time'] = date('Y-m-d H:i:s');
            $userInfo = session('sysUserInfo');
            $uname = $userInfo['remark'];
            $save['operators']    = $uname;
            $save['creator_id']   = $userInfo['id'];
            $save['hot_category_id'] = 103;
            if($artModel->add($save)){
                $arid = $artModel->getLastInsID();
                //修改标签
                $this->changeTag($tagr, $arid);
                //$this->showcontent($arid);
                $dat['content_url'] = 'special/'.$arid.'.html';
                $dat['sort_num']    = $arid;
                $artModel->where('id='.$arid)->save($dat);
                $this->output('操作成功!', 'content/getlist');
            }else{
                $this->output('操作失败!', 'content/getlist');
            }
        }
    }


    public function addHotSort(){
        $artModel = new  \Admin\Model\ArticleModel();

        if($_POST['paixu'] == 'tijiao'){
            //获取原始序号
            $sort_str = I('post.sort_str','');
            $sort_arr = explode(',',$sort_str);
            //获取排序文章id
            $artid_arr = json_decode($_POST['artid'],true);
            $f_artid = $artid_arr[0];
            $f_sortid = $sort_arr[0];
            //判断第一条文章是否有封面
            $art_info = $artModel->find($f_artid);
            $hotcatid = I('post.hotcatid','0');
            $index_img = $art_info['index_img_url'];
            if($hotcatid != 103){
                if(empty($index_img) ){
                    $this->error('首条内容必须上传首页封面图');
                }
            }
            //获取最新一条的审核时间的artid
            $field = 'id,sort_num';
            $where = "1=1";
            $where .=" AND state ='2' AND hot_category_id = ".$hotcatid;
            $order = 'update_time desc';
            $artinfo = $artModel->getOneRow($where, $field,$order);
            if($artinfo){
                //等于情况
                $check_apply_id = $artinfo['id'];
                $check_apply_sort_id = $artinfo['sort_num'];
                if(!in_array($check_apply_id, $artid_arr)){
                    if($check_apply_sort_id > $f_sortid){
                        array_unshift($sort_arr, $check_apply_sort_id);
                        //文章id
                        unset($artid_arr[0]);
                        array_unshift($artid_arr,$check_apply_id);
                        array_unshift($artid_arr,$f_artid);
                    }else if($check_apply_sort_id < $f_sortid){
                        //该放哪放哪
                        //如果获取最新审核文章id在文章表
                            array_push($sort_arr, $check_apply_sort_id);
                            rsort($sort_arr);
                            $index = array_search($check_apply_sort_id,$sort_arr); //返回2
                            //放到指定位置上
                            array_splice($artid_arr,$index,0,$check_apply_id);

                    }
                }

            }
            $org_arr = explode(',',I('post.org_str',''));
            if($org_arr == $artid_arr){
                //保持顺序没变
                $this->outputNew('保存排序成功','article/hotsortmanager');
            }else{
                //
                $bool = $artModel->updateSortNum($artid_arr, $sort_arr);
                if($bool){
                    $this->output('保存排序成功','article/hotsortmanager');
                }else{
                    $this->error('保存排序失败');
                }

            }
            die;
        }
        $catModel = new \Admin\Model\CategoModel;
        $cat_arr = $catModel->select();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','sort_num');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $where = "1=1";
        $hot_category_id = I('hotcatid',1,'intval');
        if($hot_category_id){
            $where .= "	and hot_category_id='$hot_category_id' and state=2";
        }
        $result = $artModel->getdiaList($where,$orders,$start,$size);
        $ind = $start;
        $sort = array();
        foreach ($result['list'] as &$val) {
            $val['indnum'] = ++$ind;
            $sort[] = $val['sort_num'];
            $orign[] = $val['id'];
        }
        $sort = implode(',', $sort);
        $orign = implode(',', $orign);
        $this->assign('hotcatid', $hot_category_id);
        $this->assign('sort_ord', $sort);
        $this->assign('org_artid', $orign);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('hotsort');
    }
    /**
     * @desc 设置专题
     */
    public function setSpecial(){
        $m_sysconfig =  new \Admin\Model\SysConfigModel();
        if(IS_POST){
            $special_name = I('post.special_title','','trim');
            if(!empty($special_name)){
                $length = mb_strlen($special_name);
                if($length<2 || $length>12){
                    $this->error('标题限制2-12个字');
                }
            }
            $data['system_special_title'] = $special_name;
            $ret = $m_sysconfig->updateInfo($data);  
            if($ret){
                $this->output('保存成功', 'content/getlist', 1);
            }else {
                $this->error('保存失败');
            }
        }else{
            $info = $m_sysconfig->getOne('system_special_title');
           
            $this->assign('info',$info);
            $this->display('setspecial');
        } 
    }
}
