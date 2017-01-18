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
class ContentController extends BaseController {
    

    public function __construct() {
        parent::__construct();
    }
    
    public function manager() {
        //实例化redis
//         $redis = SavorRedis::getInstance();
//         $redis->set($cache_key, json_encode(array()));
        $this->display('index');
    }

    public function getlist(){
        $artModel = new ArticleModel();
        $size   = I('numPerPage',1);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','id');
        $this->assign('_order',$order);
        $sort = I('_sort','asc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
        $starttime = I('starttime',date("Y-m-d H:i", time()-3600));
        $endtime = I('endtime', date("Y-m-d H:i"));
        $starttime = $starttime.':00';
        $endtime = $endtime.':00';
        $where = "1=1";
        $name = I('titlename');
        if ($starttime > $endtime) {
            $this->display('content');
        } else {
            if($name)
            {
                $this->assign('name',$name);
                $where .= "	AND title LIKE '%{$name}%'";
                $where .= "	AND (`create_time`) > '{$starttime}' AND (`create_time`) < '{$endtime}' ";
            }
            $result = $artModel->getList($where,$orders,$start,$size);
            $this->assign('list', $result['list']);
            $this->assign('page',  $result['page']);

            $this->display('content');
        }

       /* print_r($result);
        var_dump($artModel->getLastSql());
        var_dump($artModel->getDbError());*/

    }
    
    /*public function addArticle(){


        if(IS_POST){
            $res_param = json_encode($_POST);
            $this->output('操作成功!', 'test/testList');
        }else{
            $catModel = new CategoModel;
            $where = "state=0";
            $field = 'id,name';
            $vinfo = $catModel->getWhere($where, $field);
            $userInfo = session('sysUserInfo');
            $uname = $userInfo['username'];
            $this->assign('uname',$uname);
            $this->assign('vinfo',$vinfo);

            $this->display('addart');
        }
    }


    public function doAddarticle()
    {

        var_dump($_POST);
        $artModel = new ArticleModel();
        $id                  = I('post.id');
        $save                = [];
        $save['title']        = I('post.title','','trim');
        $save['category_id']        = I('post.cate','','trim');

        $save['img_url']    = I('post.shwimage','');


        $save['source']    = I('post.source','');
        $save['operators']    = I('post.operators','');
        $save['content']    = I('post.content','','intval');
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
            var_dump($result);
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


    public function changestatus(){
        var_dump($_POST);
    }*/




}