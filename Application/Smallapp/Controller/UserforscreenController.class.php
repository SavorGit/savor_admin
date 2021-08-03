<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;

class UserforscreenController extends BaseController {

    public function datalist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);//显示每页记录数
        $openid = I('openid','','trim');
        $morehotel_user = I('morehotel_user',99,'intval');
        $heavy_user = I('heavy_user',99,'intval');
        $sale_user = I('sale_user',99,'intval');
        $content_user = I('content_user',99,'intval');
        $common_user = I('common_user',99,'intval');
        $label_num = I('label_num',0,'intval');
        $is_upscore = I('is_upscore',99,'intval');

        $where = array();
        if(!empty($openid)){
            $where['user.openid'] = $openid;
        }
        $orderby = '';
        if($morehotel_user==1 || $morehotel_user==0){
            $where['a.morehotel_user'] = $morehotel_user;
            if($morehotel_user==1){
                $orderby.='a.hotel_num desc,';
            }
        }
        if($heavy_user==1 || $heavy_user==0){
            $where['a.heavy_user'] = $heavy_user;
            if($heavy_user==1){
                $orderby.='a.forscreen_num desc,';
            }
        }
        if($sale_user==1 || $sale_user==0){
            $where['a.sale_user'] = $sale_user;
            if($sale_user==1){
                $orderby.='a.file_num desc,';
            }
        }
        if($content_user==1 || $content_user==0){
            $where['a.content_user'] = $content_user;
            if($content_user==1){
                $orderby.='a.public_num desc';
            }
        }
        if($common_user==1 || $common_user==0){
            if($common_user==1){
                $where['a.morehotel_user+a.heavy_user+a.sale_user+a.content_user'] = 0;
            }else{
                $where['a.morehotel_user+a.heavy_user+a.sale_user+a.content_user'] = array('gt',0);
            }
            $orderby.='a.forscreen_num desc';
        }
        if($label_num){
            $where['a.morehotel_user+a.heavy_user+a.sale_user+a.content_user'] = $label_num;
        }
        if($is_upscore!=99){
            $where['a.is_upscore'] = $is_upscore;
        }
        if(empty($orderby))     $orderby ='a.forscreen_num desc';
        $orderby =  rtrim($orderby,",");
        $fields = 'a.*,user.openid,user.avatarUrl,user.nickName';
        $count_fields = 'count(a.id) as tp_count';
        $start  = ($page-1) * $size;
        $m_userforscreen = new \Admin\Model\Smallapp\UserForscreenModel();
        $res_data = $m_userforscreen->getCustomeList($fields,$count_fields,$where,$orderby,$start,$size);
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
        $this->assign('label_num',$label_num);
        $this->assign('is_upscore',$is_upscore);
        $this->assign('page',  $res_data['page']);
        $this->display();
    }

    public function edit(){
        $id = I('id',0,'intval');
        $m_userforscreen = new \Admin\Model\Smallapp\UserForscreenModel();
        if(IS_POST){
            $morehotel_user = I('post.morehotel_user',0,'intval');
            $morehotel_score = I('post.morehotel_score',0,'intval');
            $heavy_user = I('post.heavy_user',0,'intval');
            $heavy_score = I('post.heavy_score',0,'intval');
            $sale_user = I('post.sale_user',0,'intval');
            $sale_score = I('post.sale_score',0,'intval');
            $content_user = I('post.content_user',0,'intval');
            $content_score = I('post.content_score',0,'intval');

            if($morehotel_user==1 && $morehotel_score==0){
                $this->output('请输入多餐厅投屏分数', 'userforscreen/datalist', 2, 0);
            }
            if($heavy_user==1 && $heavy_score==0){
                $this->output('请输入重度分数', 'userforscreen/datalist', 2, 0);
            }
            if($sale_user==1 && $sale_score==0){
                $this->output('请输入销售分数', 'userforscreen/datalist', 2, 0);
            }
            if($content_user==1 && $content_score==0){
                $this->output('请输入内容屏分数', 'userforscreen/datalist', 2, 0);
            }
            if($morehotel_score+$heavy_score+$sale_score+$content_score>10){
                $this->output('总分为10分', 'userforscreen/datalist', 2, 0);
            }

            $data = array('morehotel_user'=>$morehotel_user,'morehotel_score'=>$morehotel_score,
                'heavy_user'=>$heavy_user,'heavy_score'=>$heavy_score,
                'sale_user'=>$sale_user,'sale_score'=>$sale_score,
                'content_user'=>$content_user,'content_score'=>$content_score,
            );
            $info = $m_userforscreen->getInfo(array('id'=>$id));
            $is_upscore = 0;
            foreach($data as $k=>$v){
                if($info[$k]!=$v){
                    $is_upscore = 1;
                }
            }
            if($is_upscore==1){
                $data['is_upscore'] = 0;
                $data['upscore_time'] = date('Y-m-d H:i:s');
            }
            $data['update_time'] = date('Y-m-d H:i:s');
            $m_userforscreen->updateData(array('id'=>$id),$data);
            $msg = '修改成功';
            $this->output($msg, 'userforscreen/datalist');
        }else{
            $fields = 'a.*,user.avatarUrl,user.nickName';
            $where = array('a.id'=>$id);
            $res_userinfo = $m_userforscreen->getUserForscreenInfo($fields,$where);
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
            $start_time = '2021-01-01';
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
        $m_forscreen = new \Admin\Model\Smallapp\ForscreenRecordModel();
        foreach ($datalist as $k=>$v){
            $fwhere = array('openid'=>$v['openid']);
            $fwhere['create_time'] = array(array('EGT',date('Y-m-d 00:00:00',strtotime($v['add_date']))),array('ELT',date('Y-m-d 23:59:59',strtotime($v['add_date']))));
            $fwhere['hotel_id'] = array('gt',0);
            $res_forscreen = $m_forscreen->getAll('hotel_name,box_name,box_mac',$fwhere,0,1,'id asc');
            $hotel_name = $box_name = $box_mac = '';
            if(!empty($res_forscreen)){
                $hotel_name = $res_forscreen[0]['hotel_name'];
                $box_name = $res_forscreen[0]['box_name'];
                $box_mac = $res_forscreen[0]['box_mac'];
            }
            $datalist[$k]['hotel_name'] = $hotel_name;
            $datalist[$k]['box_name'] = $box_name;
            $datalist[$k]['box_mac'] = $box_mac;
        }

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
        $fields = 'id,api,params,add_time';
        $where = array('openid'=>$openid);
        $where['add_time'] = array(array('EGT',date('Y-m-d 00:00:00',strtotime($tdate))),array('ELT',date('Y-m-d 23:59:59',strtotime($tdate))));
        $res_data = $m_acceslog->getDataList($fields,$where,'id asc',$start,$size);
        $datalist = $res_data['list'];
        $all_apis = C('ALL_API');
        $all_actions = C('all_forscreen_actions');
        foreach ($datalist as $k=>$v){
            $api = trim($v['api'],'/');
            $api_name = '';
            if(isset($all_apis[$api])){
                $api_name = $all_apis[$api];
            }
            if($api=='index/recordforscreenpics'){
                $params_info = json_decode($v['params'],true);
                if($params_info['action']==2){
                    $action = $params_info['action'].'-'.$params_info['resource_type'];
                }else{
                    $action = $params_info['action'];
                }
                $api_name = $api_name."({$all_actions[$action]})";
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