<?php
namespace Smallapp\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController ;
/**
 * @desc 餐厅端小程序
 *
 */
class SmallapprestController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    public function index(){
        $size       = I('numPerPage',50);       //显示每页记录数
        $start      = I('pageNum',1) ;           //当前页码
        $start      = $start ? $start :1;
        $order      = I('_order','sort'); //排序字段
        $sort       = I('_sort','desc');        //排序类型
        $orders     = $order.' '.$sort;
        $start = ($start-1)* $size;
        
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$start);
        $this->assign('_order',$order);
        $this->assign('_sort',$sort);
        
        $m_rest_download = new \Admin\Model\Smallapp\RestDownloadModel();
        $fields = "a.id,a.name,a.update_time,media.oss_addr";
        $where = array();
        $where['a.status'] = 1;
        $res_list = $m_rest_download->getList($fields,$where, $orders, $start,$size);
        
        $this->assign('list',$res_list['list']);
        $this->assign('page',$res_list['page']);
        $this->assign('oss_host','http://'.C('OSS_HOST_NEW').'/');
        $this->display('download_index');
        
        
    }
    public function adddownload(){
        
        $this->display('adddownload');
    }
    public function doadddownload(){
        $media_id = I('covervideo_id');
        $name     = I('adsname');
        $m_smallapp_rest_content = new \Admin\Model\Smallapp\RestDownloadModel();
        $data = array();
        $data['media_id']    = $media_id;
        $data['name']        = $name;
        $data['create_time'] = date('Y-m-d H:i:s');
        $ret = $m_smallapp_rest_content->addData($data);
        if($ret){
            $this->output('操作成功!', 'smallapprest/index');
        }else {
            $this->output('操作失败', 'smallapprest/index',2,0);
        }
    }
    public function editdownload(){
        $id = I('get.id');
        
        $m_smallapp_rest_content = new \Admin\Model\Smallapp\RestDownloadModel();
        $where['a.id'] = $id;
        $where['a.status'] = 1;
        $fields = "a.id,a.name,a.create_time,a.update_time,media.oss_addr,a.media_id";
        $info = $m_smallapp_rest_content->getInfo($fields,$where);
        $info['oss_addr'] = 'http://'. C('OSS_HOST_NEW').'/'.$info['oss_addr'];
        $this->assign('vainfo',$info);
        $this->display('editdownload');
        
    }
    public function doeditdownload(){
        $id = I('id');
        $m_smallapp_rest_content = new \Admin\Model\Smallapp\RestDownloadModel();
        $where['id'] = $id;
        $data = array();
        $data['media_id'] = I('covervideo_id');
        $data['name']     = I('adsname');
        $data['update_time'] = date('Y-m-d H:i:s');
        $ret = $m_smallapp_rest_content->updateData($where,$data);
        if($ret){
            $this->output('操作成功!', 'smallapprest/index');
        }else {
            $this->output('操作失败', 'smallapprest/index',2,0);
        }
    }
    public function deldownload(){
        $id = I('get.id',0,'intval');
        $m_smallapp_rest_content = new \Admin\Model\Smallapp\RestDownloadModel();
        $where['id'] = $id;
        
        $data = array();
        $data['status'] = 0;
        $ret = $m_smallapp_rest_content->updateData($where,$data);
        
        if($ret){
            $this->output('操作成功!', 'smallapprest/index',2);
        }else {
            $this->output('操作失败', 'smallapprest/index',2,0);
        }
    }
    
}