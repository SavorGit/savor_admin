<?php
namespace Admin\Controller;

class HotelqrcodeController extends BaseController {

    private $oss_host = '';
    public function __construct() {
        parent::__construct();
        $this->oss_host = get_oss_host();
    }

    public function datalist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $hotel_name = I('hotel_name','','trim');
        $status = I('status',0,'intval');

        $where = array();
        if (!empty($hotel_name)) {
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        if($status){
            $where['a.status'] = $status;
        }
        $field = 'a.*,hotel.name as hotel_name';
        $orders = 'a.id desc';
        $start  = ($page-1) * $size;

        $m_hotelqrcode_jump = new \Admin\Model\HotelQrcodeJumpModel();
        $result = $m_hotelqrcode_jump->getList($field,$where,$orders,$start,$size);
        $datalist = $result['list'];
        $all_page = C('HOTELQRCODE_JUMP_PAGE');
        foreach ($datalist as $k=>$v){
            $v['time_str'] = $v['start_time'].'至'.$v['end_time'];
            $statusstr = '正常';
            if($v['status']==2){
                $statusstr = '禁用';
            }
            $v['statusstr'] = $statusstr;
            $v['page'] = $all_page[$v['open_page']]['name'];
            $datalist[$k] = $v;
        }

        $this->assign('datalist', $datalist);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('status',$status);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display();
    }

    public function addjump(){
        $id = I('id',0,'intval');
        $m_hotelqrcode_jump = new \Admin\Model\HotelQrcodeJumpModel();
        if(IS_POST){
            $hotel_id = I('post.hotel_id',0,'intval');
            $start_hour = I('post.start_hour','');
            $start_minute = I('post.start_minute','');
            $end_hour = I('post.end_hour','');
            $end_minute = I('post.end_minute','');
            $open_page = I('post.open_page',0,'intval');
            $status = I('post.status',0,'intval');

            if(empty($start_hour) || empty($start_minute)){
                $this->output('发送时间不能为空', 'hotelqrcode/addjump',2,0);
            }
            if(empty($end_hour) || empty($end_minute)){
                $this->output('结束时间不能为空', 'hotelqrcode/addjump',2,0);
            }
            $start_time = intval($start_hour.$start_minute);
            $end_time = intval($end_hour.$end_minute);
            if($start_time>=$end_time){
                $this->output('请选择正确的开始和结束时间', 'hotelqrcode/addjump',2,0);
            }
            $userInfo = session('sysUserInfo');
            $data = array('hotel_id'=>$hotel_id,'open_page'=>$open_page,'sysuser_id'=>$userInfo['id'],'status'=>$status,
                'start_time'=>$start_hour.':'.$start_minute,'end_time'=>$end_hour.':'.$end_minute);
            if($id){
                $result = $m_hotelqrcode_jump->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_hotelqrcode_jump->add($data);
            }
            if($result){
                $this->output('操作成功!', 'hotelqrcode/datalist');
            }else{
                $this->output('操作失败', 'hotelqrcode/addjump',2,0);
            }
        }else{
            $vinfo = array('status'=>1);
            $start_hour = $start_minute = $end_hour = $end_minute = 0;
            if($id){
                $vinfo = $m_hotelqrcode_jump->getInfo(array('id'=>$id));
                $start_time_arr = explode(':',$vinfo['start_time']);
                $end_time_arr = explode(':',$vinfo['end_time']);
                $start_hour = $start_time_arr[0];
                $start_minute = $start_time_arr[1];
                $end_hour = $end_time_arr[0];
                $end_minute = $end_time_arr[1];
            }
            $res = $this->handle_publicinfo();
            $hours = $res['hours'];
            $minutes = $res['minutes'];
            $hlist = $res['hotels'];
            $areas = $res['areas'];

            $this->assign('hlist', $hlist);
            $this->assign('areas', $areas);
            $this->assign('vinfo',$vinfo);
            $this->assign('hours',$hours);
            $this->assign('minutes',$minutes);
            $this->assign('start_hour',$start_hour);
            $this->assign('start_minute',$start_minute);
            $this->assign('end_hour',$end_hour);
            $this->assign('end_minute',$end_minute);
            $this->display();
        }
    }

    public function createqrcode(){
        $hotel_id = I('hotel_id',0,'intval');
        if(IS_POST){
            $type = I('post.type',0,'intval');
            $num = I('post.num',0,'intval');
            if($type==2 && $num==0){
                $this->output('请输入生成二维码的数量', 'hotel/manager',2,0);
            }
            $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php h5/printer/hotelqrcode/hotel_id/$hotel_id/type/$type/num/$num > /tmp/null &";
            system($shell);
            $this->output('正在生成二维码,请稍微打开点击下载', 'hotel/manager');
        }else{
            $host_url = get_host_name();
            $url = "$host_url/Public/uploads/qrcode/hotel/$hotel_id/";

            $box_url = '';
            $num_url = '';
            $redis  =  \Common\Lib\SavorRedis::getInstance();
            $redis->select(1);
            $cache_key = 'cronscript:hotelqrcode:'.$hotel_id;
            $res_box_url = $redis->get($cache_key.':room');
            if(!empty($res_box_url)){
                $box_url = $url.$res_box_url;
            }
            $res_num_url = $redis->get($cache_key.':number');
            if(!empty($res_num_url)){
                $num_url = $url.$res_num_url;
            }

            $this->assign('hotel_id',$hotel_id);
            $this->assign('box_url',$box_url);
            $this->assign('num_url',$num_url);
            $this->display();
        }
    }

    private function handle_publicinfo(){
        $hours = array();
        for($i=0;$i<24;$i++){
            $hours[]=str_pad($i,2,'0',STR_PAD_LEFT);
        }
        $minutes = array();
        for($i=0;$i<6;$i++){
            $minutes[]=str_pad($i*10,2,'0',STR_PAD_LEFT);
        }
        $m_area = new \Admin\Model\AreaModel();
        $res_area = $m_area->getHotelAreaList();
        $area_info = array();
        foreach ($res_area as $v){
            $area_info[$v['id']] = $v;
        }
        $where = array('flag'=>0,'state'=>1);
        $m_hotel = new \Admin\Model\HotelModel();
        $hlist = $m_hotel->getInfo('id,name,area_id',$where);
        foreach ($hlist as $k=>$v){
            if(isset($area_info[$v['area_id']])){
                $hlist[$k]['name'] = $area_info[$v['area_id']]['region_name'].'-'.$v['name'];
            }
        }
        $res = array('hours'=>$hours,'minutes'=>$minutes,'hotels'=>$hlist,'areas'=>$area_info);
        return $res;
    }
}
