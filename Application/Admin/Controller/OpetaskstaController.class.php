<?php
/**
 * @AUTHOR: baiyutao.
 * @PROJECT: PhpStorm
 * @FILE: OpetaskstatementController.class.php
 * @CREATE ON: 2018/3/6 9:33
 * @VERSION: X.X
 */
namespace Admin\Controller;

use Admin\Controller\BaseController as BaseController;
use Common\Lib\Page;

class OpetaskstaController extends BaseController {
    public function __construct( ) {
        parent::__construct();
    }


    public function emptyData($size){
        $result['list'] = array();
        $count = 0;
        $objPage = new Page($count,$size);
        $show = $objPage->admin_page();
        $result['page'] = $show;
        return $result;
    }

    public function getUserRoleByCity() {
        //判断该城市是否有执行者
        $area_v = I('cityid',0);
        $opUserModel = new \Admin\Model\OpuserroleModel();
        $op_field = 'a.user_id id,user.remark';
        if($area_v){
            $op_where = '1 and manage_city='.$area_v.
            ' and a.state=1 and user.status=1 and a.role_id=3';
        }else {
            $op_where = '1 and a.state=1 and user.status=1 and a.role_id=3';
        }
        
        $user_arr = $opUserModel->getAllRole($op_field, $op_where);
        $tmp = array('id'=>0,'remark'=>'全部');
        array_unshift($user_arr, $tmp);
        if($user_arr) {
            $result = array(
                'code'=>1,
                'list'=>$user_arr,
            );
        } else {
            $result = array(
                'code'=>0,
                'list'=>array(),
            );
        }
        echo json_encode($result);
    }

    /*
     * @Purpose:显示列表
     * @Access:public
     * @Method:getList
     * @http: Post
     * @param
     */
    public function getList(){

        $starttime = I('adsstarttime','');
        $endtime = I('adsendtime','');
        $size   = I('numPerPage',50);//显示每页记录数
        $this->assign('numPerPage',$size);
        $start = I('pageNum',1);
        $this->assign('pageNum',$start);
        $where = "1=1";
        $yesday =  date("Y-m-d",strtotime("-1 day"));
        $start  = ( $start-1 ) * $size;
        //得到所有城市
        $areaModel  = new \Admin\Model\AreaModel();
        $area_arr = $areaModel->getAllArea();
        $this->assign('area', $area_arr);
        $area_v = I('area_v', 0);
        if( empty($starttime) && empty($endtime)) {
            $starttime = date("Y-m-d",strtotime("-7 days"));
            $endtime = $yesday;
            //$area_v = $area_arr[0]['id'];
            $st_time = $starttime.' 00:00:00 ';
            $en_time = $endtime.' 23:59:59 ';
            $this->assign('s_time',$starttime);
            $this->assign('e_time',$endtime);
        } else {
            if ( empty($starttime) ) {
                $starttime = $yesday;
                $st_time = $yesday.' 00:00:00 ';
            } else {
                $st_time = $starttime.' 00:00:00 ';
            }

            if ( empty($endtime) ) {
                $endtime = $yesday;
                $en_time = $yesday.' 23:59:59 ';
            } else {
                $en_time = $endtime.' 23:59:59 ';
            }

            if($st_time < $en_time) {
                $this->assign('s_time',$starttime);
                $this->assign('e_time',$endtime);

            }else{
                $this->error('开始时间必须小于等于结束时间');
            }
            $exe_user_id = I('user_v', 0);
            /* if( empty($area_v) ) {
                $area_v = $area_arr[0]['id'];
            } */
        }
        
        //判断该城市是否有执行者
        $opUserModel = new \Admin\Model\OpuserroleModel();
        $op_field = 'a.user_id id,user.remark';
        if($area_v){
            $op_where = '1 and manage_city='.$area_v.
            ' and a.state=1 and user.status=1 and a.role_id=3';
        }else {
            $op_where = '1 and a.state=1 and user.status=1 and a.role_id=3';
        }
        
        $user_arr = $opUserModel->getAllRole($op_field, $op_where);
        if($user_arr) {
            $uer_id_a = array_column($user_arr, 'id');
            $uer_remark_a = array_column($user_arr, 'remark');
            $user_remark_arr = array_combine(
                $uer_id_a, $uer_remark_a
            );
            if($exe_user_id == -999) {
                $result = $this->emptyData($size);
                $this->assign('user_k', $exe_user_id);
                $len = count($user_arr);
                $user_arr = array();
                $user_arr[] = array(
                    'id'=>'-999',
                    'remark'=>'无人员',
                );
            } else {
                /* if( empty($exe_user_id) ) {
                    $exe_user_id = $user_arr[0]['id'];
                } */
                //$t_user_remark = $user_remark_arr[$exe_user_id];
                $task_type = C('OPTION_USER_SKILL_ARR');
                $optaskModel = new \Admin\Model\OptiontaskModel();
                $field = ' state, hotel_id, tv_nums, task_type ';
                $tap = array();
                foreach($task_type as $tk=>$tv) {
                    $wherea = $where;
                    if(empty($exe_user_id)){
                        $wherea .= ' and task_type= '.$tk.' and flag=0  and ( ( state=2 and palan_finish_time > "'.$st_time.'"
                    and  palan_finish_time <= "'.$en_time.'"  )
                    or (state=4 and  complete_time > "'.$st_time.'"
                    and  complete_time <= "'.$en_time.'" ) )';
                    }else {
                        $wherea .= ' and task_type= '.$tk.' and flag=0 and exe_user_id = '.$exe_user_id.
                        ' and ( ( state=2 and palan_finish_time > "'.$st_time.'"
                    and  palan_finish_time <= "'.$en_time.'"  )
                    or (state=4 and  complete_time > "'.$st_time.'"
                    and  complete_time <= "'.$en_time.'" ) )';
                    }
                    
                    $order = '';
                    $limit = '';
                    $group = '';
                    $op_task_arr = $optaskModel->getListByGroup(
                        $field,$wherea, $order, $group,
                        $limit
                    );

                    if($op_task_arr) {
                        $ho_id_ar = array();
                        $ban = array();
                        foreach($op_task_arr as $ok=>$ov) {
                            $tastate = $ov['state'];
                            $ho_id_ar[$tastate][] = empty($ov['hotel_id'])?0:$ov['hotel_id'];
                            $ban[$tastate][] = empty($ov['tv_nums'])?0:$ov['tv_nums'];
                        }


                        $fi_h = count($ho_id_ar[4]);
                        $co_h = count($ho_id_ar[2]);
                        $fi_ban = array_sum($ban[4]);
                        $co_ban = array_sum($ban[2]);
                        if(empty($fi_h)) {
                            $fi_h = 0;
                        }
                        if(empty($co_h)) {
                            $co_h = 0;
                        }
                        if(empty($fi_ban)) {
                            $fi_ban = 0;
                        }
                        if(empty($co_ban)) {
                            $co_ban = 0;
                        }
                        if( ($tk == 2) || ($tk == 4) ) {
                            if($tk == 4) {
                                //维修需要算版位
                                $repUserModel = new \Admin\Model\RepairBoxUserModel();
                                $repa = array();
                                $repa['state'] = 1;
                                $repa['flag'] = 0;
                                $repa['create_time'] = array(array("GT", $st_time),array("ELT", $en_time));
                                if($exe_user_id > 0) {
                                    $repa['userid'] = $exe_user_id;
                                }
                                $rep_field = 'COUNT(*) bnum,hotel_id';
                                $rep_group = 'hotel_id';
                                $rep_task_box = $repUserModel->getTaskRepair($rep_field,$repa,$rep_group);
                                if ($rep_task_box) {
                                   $hotel_len  = count($rep_task_box);
                                    $hotel_box_arr = array_column($rep_task_box, 'bnum');
                                    $fi_ban = $fi_ban + array_sum($hotel_box_arr);
                                    $fi_h = $fi_h + $hotel_len;
                                }
                                $tap[] = array(
                                    'type'=>$tv,
                                    'remark'=>$t_user_remark,
                                    'finish'=>'酒楼'.$fi_h.'个,版位'.$fi_ban.'个',
                                    'coni'=>'酒楼'.$co_h.'个,版位'.$co_ban.'个',
                                );
                            } else {
                                $tap[] = array(
                                    'type'=>$tv,
                                    'remark'=>$t_user_remark,
                                    'finish'=>'酒楼'.$fi_h.'个,版位'.$fi_ban.'个',
                                    'coni'=>'酒楼'.$co_h.'个,版位'.$co_ban.'个',
                                );
                            }
                        } else {
                            $tap[] = array(
                                'type'=>$tv,
                                'remark'=>$t_user_remark,
                                'finish'=>'酒楼'.$fi_h.'个',
                                'coni'=>'酒楼'.$co_h.'个',
                            );
                        }
                    } else {
                        if( ($tk == 2) || ($tk == 4) ) {
                            $tap[] = array(
                                'type'=>$tv,
                                'remark'=>$t_user_remark,
                                'finish'=>'酒楼0个,版位0个',
                                'coni'=>'酒楼0个,版位0个',
                            );
                        } else {
                            $tap[] = array(
                                'type'=>$tv,
                                'remark'=>$t_user_remark,
                                'finish'=>'酒楼0个',
                                'coni'=>'酒楼0个',
                            );
                        }

                    }
                }
                $result['list'] = $tap;
                $count = 4;
                $objPage = new Page($count, $size);
                $show = $objPage->admin_page();
                $result['page'] = $show;

            }


        } else {
            $user_arr = array();
            $result = $this->emptyData($size);
        }
        //维修任务平均时长
        $where =" 1 ";
        $where .=" and create_time>='".$st_time."'";
        $where .=" and create_time<='".$en_time."'";
        $where .=" and state=4 and task_type=4 and flag=0";
        if(!empty($area_v)){
            $where .=" and task_area=$area_v";
        }
        if($exe_user_id){
            $where .=" and exe_user_id={$exe_user_id}";
        }
        $sql = ' select create_time,complete_time from savor_option_task where '.$where;
        $data = M()->query($sql);
        $all_times = 0;
        $all_nums = count($data);
        
        foreach($data as $key=>$v){
            $diff_time =  strtotime($v['complete_time']) - strtotime($v['create_time']);
            $all_times += $diff_time;
        }
        $avg_time = floor($all_times / $all_nums);
        $avg_time = secsToStr($avg_time);
        
        $this->assign('avg_time',$avg_time);
        
        $this->assign('user_k', $exe_user_id);
        $this->assign('area_k',$area_v);
        $this->assign('usera', $user_arr);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('showlist');

    }
}
