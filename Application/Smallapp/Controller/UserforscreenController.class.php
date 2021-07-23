<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class UserforscreenController extends BaseController {

    public function datalist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $openid = I('openid','','trim');
        $morehotel_user = I('morehotel_user',0,'intval');
        $heavy_user = I('heavy_user',0,'intval');
        $sale_user = I('sale_user',0,'intval');
        $content_user = I('content_user',0,'intval');
        $common_user = I('common_user',0,'intval');

        $where = array();
        if(!empty($openid)){
            $where['user.openid'] = $openid;
        }
        $orderby = '';
        if($morehotel_user==1)  $orderby.='a.hotel_num desc,';
        if($heavy_user==1)      $orderby.='a.forscreen_num desc,';
        if($sale_user==1)       $orderby.='a.file_num desc,';
        if($content_user==1)    $orderby.='a.public_num desc';
        if(empty($orderby))     $orderby ='a.forscreen_num desc';
        $orderby =  rtrim($orderby,",");
        $fields = 'a.*,user.avatarUrl,user.nickName';
        $start  = ($page-1) * $size;
        $m_userforscreen = new \Admin\Model\Smallapp\UserForscreenModel();
        $res_data = $m_userforscreen->getCustomeList($fields,$where,$orderby,$start,$size);
        $datalist = $res_data['list'];
        foreach ($datalist as $k=>$v){
            $update_time = $v['update_time'];
            if($update_time=='0000-00-00 00:00:00'){
                $update_time = '';
            }
            $datalist[$k]['update_time'] = $update_time;
            $datalist[$k]['label'] = $this->user_label($v);
        }

        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('datalist', $datalist);
        $this->assign('openid',$openid);
        $this->assign('morehotel_user',$morehotel_user);
        $this->assign('heavy_user',$heavy_user);
        $this->assign('sale_user',$sale_user);
        $this->assign('content_user',$content_user);
        $this->assign('common_user',$common_user);
        $this->assign('page',  $res_data['page']);

        $this->display();
    }

    public function edit(){
        $id = I('id',0,'intval');
        $m_user = new \Admin\Model\Smallapp\UserForscreenModel();
        if(IS_POST){
            $morehotel_user = I('post.morehotel_user',0,'intval');
            $morehotel_score = I('post.morehotel_score',0,'intval');
            $heavy_user = I('post.heavy_user',0,'intval');
            $heavy_score = I('post.heavy_score',0,'intval');
            $sale_user = I('post.sale_user',0,'intval');
            $sale_score = I('post.sale_score',0,'intval');
            $content_user = I('post.content_user',0,'intval');
            $content_score = I('post.content_score',0,'intval');
            $common_user = I('post.common_user',0,'intval');
            $common_score = I('post.common_score',0,'intval');

            $data = array('morehotel_user'=>$morehotel_user,'morehotel_score'=>$morehotel_score,
                'heavy_user'=>$heavy_user,'heavy_score'=>$heavy_score,
                'sale_user'=>$sale_user,'sale_score'=>$sale_score,
                'content_user'=>$content_user,'content_score'=>$content_score,
                'common_user'=>$common_user,'common_score'=>$common_score,'update_time'=>date('Y-m-d H:i:s')
                );
            $m_user->updateData(array('id'=>$id),$data);
            $msg = '修改成功';
            $this->output($msg, 'userforscreen/datalist');
        }else{
            $fields = 'a.*,user.avatarUrl,user.nickName';
            $where = array('a.id'=>$id);
            $res_userinfo = $m_user->getUserForscreenInfo($fields,$where);
            $userinfo = $res_userinfo[0];
            $this->assign('userinfo',$userinfo);
            $this->display();
        }
    }

    public function track(){
        $openid = I('openid','');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $start_time = I('start_time','');
        $end_time = I('end_time','');

        if(empty($start_time)){
            $start_time = date('Y-m-d');
        }
        if(empty($end_time)){
            $end_time = date('Y-m-d');
        }
        $m_acceslog = new \Admin\Model\Smallapp\AccesslogModel();
        $start  = ($page-1) * $size;
        $fields = 'openid,DATE(add_time) as add_date';
        $group = 'add_date';
        $count_field = 'count(DISTINCT(DATE(add_time))) as tp_count';
        $where = array('openid'=>$openid);
        $where['add_time'] = array(array('EGT',date('Y-m-d 00:00:00',strtotime($start_time))),array('ELT',date('Y-m-d 23:59:59',strtotime($end_time))));
        $res_data = $m_acceslog->getCustomeList($fields,$count_field,$where,$group,'',$start,$size);
        $datalist = $res_data['list'];

        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('datalist', $datalist);
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('openid',$openid);
        $this->assign('page',  $res_data['page']);
        $this->display();
    }

    public function ondaytrack(){
        $openid = I('openid','');
        $tdate = I('tdate','');
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数

        $m_acceslog = new \Admin\Model\Smallapp\AccesslogModel();
        $start  = ($page-1) * $size;
        $fields = 'id,api,add_time';
        $where = array('openid'=>$openid);
        $where['add_time'] = array(array('EGT',date('Y-m-d 00:00:00',strtotime($tdate))),array('ELT',date('Y-m-d 23:59:59',strtotime($tdate))));
        $res_data = $m_acceslog->getDataList($fields,$where,'id asc',$start,$size);
        $datalist = $res_data['list'];
        $all_apis = C('ALL_API');
        foreach ($datalist as $k=>$v){
            $api = trim($v['api'],'/');
            $api_name = '';
            if(isset($all_apis[$api])){
                $api_name = $all_apis[$api];
            }
            $datalist[$k]['api_name'] = $api_name;
        }
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->assign('datalist', $datalist);
        $this->assign('tdate',$tdate);
        $this->assign('openid',$openid);
        $this->assign('page',  $res_data['page']);
        $this->display('onedaytrack');
    }

    private function user_label($info){
        $label = '';
        if($info['morehotel_user']==1){
            $morehotel_label = '多餐厅用户';
            if($info['morehotel_score']>0){
                $morehotel_label.="({$info['morehotel_score']})";
            }
            $label.=$morehotel_label.'、';
        }
        if($info['heavy_user']==1){
            $heavy_label = '重度用户';
            if($info['heavy_score']>0){
                $heavy_label.="({$info['heavy_score']})";
            }
            $label.=$heavy_label.'、';
        }
        if($info['sale_user']==1){
            $sale_label = '销售人员';
            if($info['sale_score']>0){
                $sale_label.="({$info['sale_score']})";
            }
            $label.=$sale_label.'、';
        }
        if($info['content_user']==1){
            $content_label = '内容贡献者';
            if($info['content_score']>0){
                $content_label.="({$info['content_score']})";
            }
            $label.=$content_label.'、';
        }
        if($info['common_user']==1){
            $common_label = '普通用户';
            if($info['common_score']>0){
                $common_label.="({$info['common_score']})";
            }
            $label.=$common_label.'、';
        }
        return rtrim($label,'、');
    }


}