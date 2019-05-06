<?php
/**
 * @desc   聚屏广告管理
 * @author zhang.yingtao
 * @since  2018-04-09
 */
namespace Admin\Controller;
use Common\Lib\Aliyun;
class PolyscreenController extends BaseController{
    public function __construct(){
        parent::__construct();
    }
    /**
     * @desc 列表
     */
    public function index(){
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $order = I('_order','a.create_time');
        $this->assign('_order',$order);
        $sort = I('_sort','desc');
        $this->assign('_sort',$sort);
        $orders = $order.' '.$sort;
        $start  = ( $start-1 ) * $size;
       
        $where = array();
        //$where = "1=1"; 
        $serachads = I('serachads','','trim');
        if($serachads){
            $where['ads.name'] = array('like','%'.$serachads.'%');
        }
        $m_pub_poly_ads = new \Admin\Model\PubPolyAdsModel();
        
        $fields = 'ads.name,media.duration,media.oss_addr,a.id,a.tpmedia_id,a.create_time,a.media_md5,a.type,a.state,user.remark';
        
        $data = $m_pub_poly_ads->getList($fields,$where,$orders,$start,$size);
        
        $third_media_list =C('POLY_SCREEN_MEDIA_LIST');
        foreach($data['list'] as $key=>$v){
            if($v['type'] == 1){
                $data['list'][$key]['typestr'] = '视频';
            }elseif($v['type'] == 2){
                $data['list'][$key]['typestr'] = '图片';
            }else{
                $data['list'][$key]['typestr'] = '';
            }
            $data['list'][$key]['oss_addr'] =  'http://'.C('OSS_HOST_NEW').'/'.$v['oss_addr'];
            $data['list'][$key]['third_media_name'] = $third_media_list[$v['tpmedia_id']];
        }
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);
        
        $this->display('index');
    }
    /**
     * @desc 新增广告
     */
    public function add(){
        $type = I('type',1,'intval');
        $poly_screen_media_list = C('POLY_SCREEN_MEDIA_LIST');

        $this->assign('type',$type);
        $this->assign('poly_screen_media_list',$poly_screen_media_list);
        $this->display('add');
    }
    /**
     * @desc 提交新增
     */
    public function doAdd(){
        if(IS_POST){
            $media_id   = I('post.media_id',0,'intval');//媒体资源id
            if(empty($media_id)){
                $this->error('请上传视频内容');
            }
            $tpmedia_id = I('post.tpmedia_id',0,'intval');//第三方媒体平台id
            if(empty($tpmedia_id)){
                $this->error('请选择视频来源');
            }
            $media_md5  = I('post.media_md5','','trim');//文件md5
            if($tpmedia_id==4 && empty($media_md5)){
                $m_ads = new \Admin\Model\AdsModel();
                $ads_info = $m_ads->getInfo(array('id'=>$media_id));
                $m_media = new \Admin\Model\MediaModel();
                $res_media = $m_media->getInfo(array('id'=>$ads_info['media_id']));
                $oss_addr = $res_media['oss_addr'];
                $accessKeyId = C('OSS_ACCESS_ID');
                $accessKeySecret = C('OSS_ACCESS_KEY');
                $endpoint = C('OSS_HOST');
                $bucket = C('OSS_BUCKET');
                $aliyun = new Aliyun($accessKeyId, $accessKeySecret, $endpoint);
                $aliyun->setBucket($bucket);
                $fileinfo = $aliyun->getObject($oss_addr,'');
                if($fileinfo){
                    $media_md5 = md5($fileinfo);
                }
            }

            if(empty($media_md5)){
                $this->error('请填写文件md5值');
            }

            $type = I('post.type',0,'intval');
            $userInfo = session('sysUserInfo');
            $now_date = date('Y-m-d H:i:s');
            $save = array();
            $save['ads_id']     = $media_id;
            $save['tpmedia_id'] = $tpmedia_id;
            $save['media_md5']  = $media_md5;
            $save['create_time']= $now_date;
            $save['update_time']= $now_date;
            $save['creator_id'] = $userInfo['id'];
            $save['type']      = $type;
            $save['state']      = 0;
            $save['flag']       = 0;
            $m_pub_poly_ads = new \Admin\Model\PubPolyAdsModel();
            $rets = $m_pub_poly_ads->addInfo($save,1);
            if($rets){
                $this->output('添加成功','polyscreen/index');
            }else {
                
                $this->error('添加失败');
            }
        }
    }
    /**
     * @desc 修改状态
     */
    public function editState(){
        $state = I('get.state');
        $id    = I('get.id');
        if(!is_numeric($state)){
            $this->error('参数错误');
        }
        $m_pub_poly_ads = new \Admin\Model\PubPolyAdsModel();
        $fields = 'a.state,a.tpmedia_id';
        $where = array();
        $where['a.id'] = $id;
        $info = $m_pub_poly_ads->getInfo($fields, $where, '', '',1);
        $poly_info = $info;
        if(empty($info)){
            $this->error('该广告不存在');
        }
        if($state == $info['state']){
            if($state ==0){
                $this->error('该广告已为下线状态');
            }else if($state==1){
                $this->error('该广告已为上线状态');
            }
        }
        $map = array();
        $map['id'] = $id;
        $data['state'] = $state;
        $data['update_time'] = date('Y-m-d H:i:s');
        $ret = $m_pub_poly_ads->updateInfo($map,$data);
        if($ret){
            if($state==0){
                $msg = '下线成功';
                $info = $m_pub_poly_ads->getInfo('a.id', ' a.state=1', 'a.update_time desc,a.id desc', '',1);
                $where = $data =  array();
                $where['id'] = $info['id'];
                $data['update_time'] = date('Y-m-d H:i:s');
                $m_pub_poly_ads->updateInfo($where, $data);
            }else if($state==1){
                $msg = '上线成功';
            }
            //获取当前配置该第三方广告盒子的酒楼
            $m_box = new \Admin\Model\BoxModel();
            $sql = "select hotel.id hotel_id from savor_box box
                    left join savor_room room on box.room_id=room.id
                    left join savor_hotel hotel on room.hotel_id=hotel.id
                    where box.state=1 and box.flag=0 and hotel.state=1 and hotel.flag =0 
                    and FIND_IN_SET('".$poly_info['tpmedia_id']."',box.`tpmedia_id`) 
                    group by hotel.id";
            $data = $m_box->query($sql); 
            if(!empty($data)){
                //获取虚拟小平台配置的酒楼id 如果该酒楼在虚拟小平台 通知更新虚拟小平台该酒楼的宣传片
                $tmp_hotel_arr = getVsmallHotelList();
                $ht_arr = array();
                foreach($data as $key=>$v){
                    if(in_array($v['hotel_id'], $tmp_hotel_arr)){
                        $ht_arr[] = $v['hotel_id'];
                        //sendTopicMessage($v['hotel_id'], 10);
                    }
                }
                sendTopicMessage($ht_arr, 10);
                
            }
            
            $this->output($msg, 'polyscreen/index', 2);
        }else {
            if($state==0){
                $msg = '下线失败';
            }else if($state==1){
                $msg = '上线失败';
            }
            $this->error($msg);
        }
    }
} 
