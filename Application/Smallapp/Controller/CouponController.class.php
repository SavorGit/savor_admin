<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class CouponController extends BaseController {

    public function datalist(){
        $size = I('numPerPage',50,'intval');//显示每页记录数
        $pageNum = I('pageNum',1,'intval');//当前页码
        $keywords = I('keywords','','trim');

        $m_coupon = new \Admin\Model\Smallapp\CouponModel();
        $where = array();
        if($keywords){
            $where['name'] = array('like',"%$keywords%");
        }
        $start = ($pageNum-1)*$size;
        $orderby = 'id desc';
        $res_list = $m_coupon->getDataList('*',$where,$orderby,$start,$size);
        $data_list = array();
        if(!empty($res_list['list'])){
            foreach ($res_list['list'] as $v){
                $v['min_price'] = '满'.$v['min_price'].'可用';
                $v['date_str'] = $v['start_time'].'到'.$v['end_time'];
                if($v['status']==1){
                    $v['status_str'] = '正常';
                }else{
                    $v['status_str'] = '禁用';
                }
                $data_list[] = $v;
            }
        }
        $this->assign('keywords',$keywords);
        $this->assign('data',$data_list);
        $this->assign('page',$res_list['page']);
        $this->assign('numPerPage',$size);
        $this->assign('pageNum',$pageNum);
        $this->display();
    }

    public function couponadd(){
        $id = I('id',0,'intval');
        $m_coupon = new \Admin\Model\Smallapp\CouponModel();
        if(IS_POST){
            $name = I('post.name','','trim');
            $money = I('post.money',0,'intval');
            $min_price = I('post.min_price',0,'intval');
            $remark = I('post.remark','','trim');
            $start_time = I('post.start_time');
            $end_time = I('post.end_time');
            $status = I('post.status',0,'intval');

            $data = array('name'=>$name,'money'=>$money,'min_price'=>$min_price,'remark'=>$remark,
                'start_time'=>$start_time,'end_time'=>$end_time,'status'=>$status);
            if($id){
                $result = $m_coupon->updateData(array('id'=>$id),$data);
            }else{
                $result = $m_coupon->addData($data);
            }
            if($result){
                $this->output('操作成功!', 'coupon/datalist');
            }else{
                $this->output('操作失败', 'coupon/datalist',2,0);
            }
        }else{
            $vinfo = array('status'=>1);
            if($id){
                $vinfo = $m_coupon->getInfo(array('id'=>$id));
            }
            $this->assign('vinfo',$vinfo);
            $this->display();
        }
    }


}