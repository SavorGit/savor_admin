<?php
namespace Smallapp\Controller;
use Common\Lib\Page;
use Admin\Controller\BaseController;
/**
 * @desc 小程序销售用户
 *
 */
class SaleuserController extends BaseController {

    public function __construct() {
        parent::__construct();
    }

    public function userlist(){
        $openid = I('openid','','trim');
        $hotel_name = I('hotel_name','','trim');
        $small_app_id = I('small_app_id',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $where = array('a.status'=>1,'merchant.status'=>1);
        if(!empty($openid)){
            $where['a.openid'] = $openid;
        }
        if(!empty($hotel_name)){
            $where['hotel.name'] = array('like',"%$hotel_name%");
        }
        $start = ($pageNum-1)*$size;
        $m_staffuser = new \Admin\Model\Integral\StaffModel();
        $fields = "u.id,u.openid,u.mobile,u.avatarUrl,u.nickName,hotel.name as hotel_name,u.create_time,i.integral";
        $res_list = $m_staffuser->getUserIntegralList($fields,$where,'i.integral desc',$start,$size);

        $data_list = $res_list['list'];
        foreach ($data_list as $k=>$v){
            $data_list[$k]['integral'] = intval($v['integral']);
        }

        $this->assign('hotel_name',$hotel_name);
        $this->assign('openid',$openid);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('small_app_id',$small_app_id);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function integrallist(){
        $start_date = I('post.start_date','');
        $end_date = I('post.end_date','');
        $hotel_name = I('hotel_name','','trim');
        $openid = I('openid','','trim');
        $type = I('type',0,'intval');
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码

        $integral_types = array('1'=>'开机','2'=>'互动','3'=>'销售','4'=>'兑换');
        $where = array('openid'=>$openid);
        if($hotel_name){
            $where['hotel_name'] = array('like',"%$hotel_name%");
        }
        if($type){
            $where['type'] = $type;
        }
        if($start_date && $end_date){
            $stime = strtotime($start_date);
            $etime = strtotime($end_date);
            if($stime>$etime){
                $this->output('开始时间不能大于结束时间', 'goods/goodsadd', 2, 0);
            }
            $start_time = date('Y-m-d 00:00:00',$stime);
            $end_time = date('Y-m-d 23:59:59',$etime);
            $where['add_time'] = array(array('egt',$start_time),array('elt',$end_time), 'and');
        }

        $start = ($pageNum-1)*$size;
        $m_integral_record = new \Admin\Model\Smallapp\UserIntegralrecordModel();
        $res_list = $m_integral_record->getDataList('*',$where,'id desc',$start,$size);
        $data_list = $res_list['list'];
        $m_goods = new \Admin\Model\Smallapp\GoodsModel();
        foreach ($data_list as $k=>$v){
            $info = '';
            switch ($v['type']){
                case 1:
                    $info = $integral_types[$v['type']].$v['content'].'小时';
                    if($v['fj_type']==1){
                        $info.='--午饭';
                    }elseif($v['fj_type']==2){
                        $info.='--晚饭';
                    }
                    break;
                case 2:
                    $info = $integral_types[$v['type']].$v['content'].'人数';
                    break;
                case 3:
                case 4:
                    $goods_info = $m_goods->getInfo(array('id'=>$v['goods_id']));
                    $info = $integral_types[$v['type']].'商品：'.$goods_info['name'].' 数量：'.$v['content'];
                    break;
            }
            $data_list[$k]['info'] = $info;
            $status_str = '';
            if($v['type']==4){
                $status_str = '已使用';
            }else{
                if($v['status']==1){
                    $status_str = '可用';
                }elseif($v['status']==2){
                    $status_str = '未结算不可用';
                }
            }
            $data_list[$k]['status_str']  = $status_str;
            $data_list[$k]['type_str']  = $integral_types[$v['type']];
        }
        $integral = 0;
        if($openid){
            $m_uintegral = new \Admin\Model\Smallapp\UserIntegralModel();
            $res_integral = $m_uintegral->getInfo(array('openid'=>$openid));
            $integral = intval($res_integral['integral']);
        }
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('type',$type);
        $this->assign('openid',$openid);
        $this->assign('integral',$integral);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('integral_types',$integral_types);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display('integrallist');
    }


}