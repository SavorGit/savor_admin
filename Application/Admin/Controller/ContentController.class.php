<?php
namespace Admin\Controller;
/**
 * @desc 功能测试类
 *
 */
use Admin\Controller\BaseController;
use Admin\Model\ArticleModel;
use Admin\Model\HomeModel;
class ContentController extends BaseController {
    var $content_type_arr;
    public function __construct() {
        parent::__construct();
        $this->content_type_arr = array(1=>'图文',2=>'图集',3=>'视频（非点播）',4=>'视频（点播）');
    }

    public function getlist(){
        $artModel = new ArticleModel();
        $homeModel = new HomeModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','update_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;

        $where = "1=1";
        $name = I('titlename');
        $type = I('type',10,'intval');//10为全部

        //$where .= " AND state=2 ";
        /* $category_id = I('category_id',0,'intval');
        if($category_id) $where .=" AND category_id='$category_id'"; */
        $hot_category_id = I('hot_catgory_id',0,'intval');
        if($hot_category_id) $where .=" and hot_category_id='$hot_category_id'";
        $content_type = I('content_type','10','intval');
        
        if(is_numeric($content_type)){
            switch ($content_type){
                case 0:
                    $where .= " AND type=0";
                    break;
                case 1:
                    $where .=" AND type=1";
                    break;
                case 2:
                    $where .=" AND type=2";
                    break;
                case 3:
                    $where .=" AND type=3 and media_id=0";
                    break;
                case 4:
                    $where .=" AND type=3 and media_id>0"; 
                    break;
            }
        }
        $this->assign('content_type',$content_type);
        $beg_time = I('begin_time','');
        $end_time = I('end_time','');
        if($beg_time)   $where.=" AND create_time>='$beg_time'";
        if($end_time)   $where.=" AND create_time<='$end_time 23:59:59'";
        if($name){
            $this->assign('name',$name);
            $where .= "	AND title LIKE '%{$name}%'";
        }
        if($type!=10){
            $where .= "	AND type='$type'";
        }
        $result = $artModel->getList($where,$orders,$start,$size);
	    $result['list'] = $artModel->changeCatname($result['list']);
	    $result['list'] = $homeModel->ishomeContent($result['list']);
	    
	    $m_media = new \Admin\Model\MediaModel();
	    $oss_host_new = C('OSS_HOST_NEW');
	    $content_host = C('CONTENT_HOST');
	    foreach($result['list'] as $key=>$v){
	        
	        $pushdata = array();
	        $pushdata['id'] = $v['id'];
	        $pushdata['category'] = $v['cat_name'];
	        $pushdata['title'] = $v['title'];
	        //获取oss name?????????
	        if(!empty($v['media_id'])){
	            $m_info = $m_media->field('oss_addr')->where(array('id'=>$v['media_id']))->find();
	            $pushdata['mediaId'] = $v['media_id'];
	        }
	        if(!empty($v['duration'])){
	            $pushdata['duration'] = $v['duration'];
	        }
	        if(!empty($v['vod_md5'])){
	            $pushdata['canPlay'] = 1;
	        }
	        
	        if($v['type'] ==3){
	            if(!empty($m_info['oss_addr'])){
	                
	                $ttp = explode('/', $m_info['oss_addr']);
	                $pushdata['name'] = $ttp[2];
	            }
	        }
	        $pushdata['type'] = $v['type'];
	        if($v['type'] ==3 && empty($v['content'])){
	            $pushdata['type'] = 4;
	        }
	        if($v['img_url']){
	            $pushdata['imageURL'] = 'http://'.$oss_host_new.'/'.$v['img_url'];
	        }
	        if($v['content_url']){
	            $pushdata['contentURL'] = $content_host.$v['content_url'];
	        }
	        if(!empty($v['tx_url'])) $pushdata['videoURL']   = substr($v['tx_url'],0,strpos($v['tx_url'], '.f')) ;
	        $result['list'][$key]['pushdata'] = json_encode($pushdata,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); 
	        
	    }
	    $m_hot_category = new \Admin\Model\HotCategoryModel();
	    $where = " state=1";
    	$field = 'id,name';
    	$category_list = $m_hot_category->getWhere($where, $field);
    	$this->assign('vcainfo',$category_list);
	    $time_info = array('now_time'=>date('Y-m-d H:i:s'),'begin_time'=>$beg_time,'end_time'=>$end_time);
        $this->assign('content_type_arr',$this->content_type_arr);
	    $this->assign('hot_category_id',$hot_category_id);
	    $this->assign('timeinfo',$time_info);
        $this->assign('ctype', $type);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('content');
    }

    /*
	 * 修改状态
	 */
    public function operateStatus(){


        $adsid = I('request.adsid','0','intval');
        $flag = I('request.flag');
        $message = '';
        if(flag == 2){
            $state = 3;
        } else {
            $state = 2;
        }
        $artModel = new \Admin\Model\ArticleModel();
        $arinfo =$artModel->find($adsid);
        $sort_num = $arinfo['sort_num'];
        $catid = $arinfo['hot_category_id'];
        $now_time = date('Y-m-d H:i:s');
        $where = "1=1 and hot_category_id=$catid and state=2";
        $max_info = $artModel->getMaxSort($where);
        //内空最大值
        if($max_info){
            $max_id = $max_info['id'];
            $max_num = $max_info['sort_num'];
        }

        if($max_num < $sort_num){
            //两个互换
            $ainfo = array(
                'sort_num'=>$max_num,
                'update_time'=>$now_time,
                'state'=>$state,
            );
            $binfo = array(
                'sort_num'=>$sort_num,
                'update_time'=>$now_time,
                'state'=>$state,
            );
            $artModel->where('id = '.$adsid)->save($ainfo);
            $artModel->where('id = '.$max_id)->save($binfo);
        }else{
            $data = array('state'=>$state,'update_time'=>$now_time);
            $res = $artModel->where("id='$adsid'")->save($data);
        }
        $message = '更新审核状态成功';
        if($message){
            $this->output($message, 'content/getlist',2);
        }else{
            $this->output('更新操作失败', 'content/getlist');
        }


    }


    public function check(){
        $artModel = new ArticleModel();
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','update_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;

        $where = "1=1";
        $name = I('titlename');
        $type = I('type',10,'intval');//10为全部

        $beg_time = I('begin_time','');
        $end_time = I('end_time','');
        if($beg_time)   $where.=" AND create_time>='$beg_time'";
        if($end_time)   $where.=" AND create_time<='$end_time 23:59:59'";
        if($name){
            $this->assign('name',$name);
            $where .= "	AND title LIKE '%{$name}%'";
        }
        if($type!=10){
            $where .= "	AND type='$type'";
        }
        $where .= " AND state in (0,1,3)";
        $result = $artModel->getList($where,$orders,$start,$size);
        $result['list'] = $artModel->changeCatname($result['list']);
        $time_info = array('now_time'=>date('Y-m-d H:i:s'),'begin_time'=>$beg_time,'end_time'=>$end_time);
        $this->assign('timeinfo',$time_info);
        $this->assign('ctype', $type);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('contentcheck');
    }

}