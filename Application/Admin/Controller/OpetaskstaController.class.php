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
        $op_where = '1 and manage_city='.$area_v.
            ' and a.state=1 and user.status=1 and a.role_id=3';
        $user_arr = $opUserModel->getAllRole($op_field, $op_where);
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
            $area_v = $area_arr[0]['id'];
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
            if( empty($area_v) ) {
                $area_v = $area_arr[0]['id'];
            }
        }
        //判断该城市是否有执行者
        $opUserModel = new \Admin\Model\OpuserroleModel();
        $op_field = 'a.user_id id,user.remark';
        $op_where = '1 and manage_city='.$area_v.
            ' and a.state=1 and user.status=1 and a.role_id=3';
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
                if( empty($exe_user_id) ) {
                    $exe_user_id = $user_arr[0]['id'];
                }
                $t_user_remark = $user_remark_arr[$exe_user_id];
                $task_type = C('OPTION_USER_SKILL_ARR');
                $optaskModel = new \Admin\Model\OptiontaskModel();
                $field = ' state, hotel_id, tv_nums, task_type ';
                $tap = array();
                foreach($task_type as $tk=>$tv) {
                    $wherea = $where;
                    $wherea .= ' and task_type= '.$tk.' and flag=0 and exe_user_id = '.$exe_user_id.
                        ' and ( ( state=2 and palan_finish_time > "'.$st_time.'"
                and  palan_finish_time <= "'.$en_time.'"  )
                or (state=4 and  complete_time > "'.$st_time.'"
                and  complete_time <= "'.$en_time.'" ) )';
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
                        $fi_ban = count($ban[4]);
                        $co_ban = count($ban[2]);
                        if( ($tk == 2) || ($tk == 4) ) {
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


        $this->assign('user_k', $exe_user_id);
        $this->assign('area_k',$area_v);
        $this->assign('usera', $user_arr);
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('showlist');

    }
}
