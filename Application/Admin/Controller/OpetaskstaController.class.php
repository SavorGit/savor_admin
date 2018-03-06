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
        $starttime = '2017-03-02';
        if( empty($starttime) && empty($endtime)) {
            $this->emptyData($size);
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

            //得到所有城市
            $areaModel  = new \Admin\Model\AreaModel();
            $area_arr = $areaModel->getAllArea();
            $this->assign('area', $area_arr);
            $area_v = I('area_v', 0);
            $exe_user_id = I('user_v', 0);
            if( empty($area_v) ) {
                $area_v = $area_arr[0]['id'];
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
                if( empty($exe_user_id) ) {
                    $exe_user_id = $user_arr[0]['id'];
                }
                //获取该执行都的所有任务
                $where .= ' and flag=0 and exe_user_id = '.$exe_user_id.
                ' and ( ( state=2 and palan_finish_time > "'.$st_time.'"
                and  palan_finish_time < "'.$en_time.'"  )
                or (state=4 and  complete_time > "'.$st_time.'"
                and  complete_time < "'.$en_time.'" ) )';
                $optaskModel = new \Admin\Model\OptiontaskModel();
                $order = '';
                $limit = '';
                $field = ' state, task_type, COUNT(*) tasknum';
                $group = 'task_type';
                $op_task_arr = $optaskModel->getListByGroup(
                    $field,$where, $order, $group,
                    $limit
                );
                $t_user_remark = $user_remark_arr[$exe_user_id];
                $task_type = C('OPTION_USER_SKILL_ARR');
                if($op_task_arr) {
                    $task_state_arr = array();
                    $t_ar_num = array();
                    foreach($op_task_arr as $ok=>$ov) {
                        $tatype = $ov['task_type'];
                        $tastate = $ov['state'];
                        $task_state_arr[$tatype][$tastate] = $ov['tasknum'];
                        $t_ar_num[$tatype] = 1;
                    }
                    $tap = array();
                    foreach($t_ar_num as $tk=>$tv) {
                        $tap[] = array(
                            'type'=>$task_type[$tk],
                            'remark'=>$t_user_remark,
                            'finish'=>empty($task_state_arr[$tk][4])?0:$task_state_arr[$tk][4],
                            'coni'=>empty($task_state_arr[$tk][2])?0:$task_state_arr[$tk][2]
                        );
                    }
                    $result['list'] = $tap;
                    $count = count($t_ar_num);
                    $objPage = new Page($count, $size);
                    $show = $objPage->admin_page();
                    $result['page'] = $show;

                } else {
                    $result = $this->emptyData($size);
                }


            } else {
                $user_arr = array();
                $result = $this->emptyData($size);
            }


            $this->assign('user_k', $exe_user_id);
            $this->assign('area_k',$area_v);
            $this->assign('usera', $user_arr);

        }
        $this->assign('list', $result['list']);
        $this->assign('page',  $result['page']);
        $this->display('showlist');

    }
}
