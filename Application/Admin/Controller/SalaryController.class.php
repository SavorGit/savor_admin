<?php
namespace Admin\Controller;

class SalaryController extends BaseController {

    public function __construct() {
        parent::__construct();
    }
    
    public function filelist() {
        $static_month = date('Ym',strtotime('-1 month'));
        $cache_salary_acbd_self_key = 'cronscript:salary_acbd_self'.$static_month;
        $cache_salary_acbd_team_key = 'cronscript:salary_acbd_team'.$static_month;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $acbd_self_data = $redis->get($cache_salary_acbd_self_key);
        $acbd_self_down_time = '';
        $acbd_self_download_url = '';
        $host_name = get_host_name();
        if(!empty($acbd_self_data)){
            if(is_numeric($acbd_self_data)){
                $acbd_self_down_time = time()-$acbd_self_data;
            }else{
                $acbd_self_file_name = $acbd_self_data;
                $acbd_self_download_url = $host_name.$acbd_self_file_name;
            }
        }

        $acbd_team_data = $redis->get($cache_salary_acbd_team_key);
        $acbd_team_down_time = '';
        $acbd_team_download_url = '';
        if(!empty($acbd_team_data)){
            if(is_numeric($acbd_team_data)){
                $acbd_team_down_time = time()-$acbd_team_data;
            }else{
                $acbd_team_file_name = $acbd_team_data;
                $acbd_team_download_url = $host_name.$acbd_team_file_name;
            }
        }
        $datalist = array();
        if(!empty($acbd_self_down_time) || !empty($acbd_team_down_time) || !empty($acbd_self_download_url) || !empty($acbd_team_download_url)){
            $datalist = array(
                array('name'=>'AC和BD个人绩效','down_time'=>$acbd_self_down_time,'download_url'=>$acbd_self_download_url),
                array('name'=>'BD小组绩效','down_time'=>$acbd_team_down_time,'download_url'=>$acbd_team_download_url),
            );
        }
        $is_up = 1;
        $m_staff_saletask = new \Admin\Model\StaffPerformanceSaletaskModel();
        $res_saletask = $m_staff_saletask->getAll('id',array('add_month'=>$static_month),0,1,'id desc');
        if(!empty($res_saletask[0]['id'])){
            $is_up = 0;
        }
        $this->assign('is_up',$is_up);
        $this->assign('datalist', $datalist);
        $this->display('filelist');
    }

    public function addexcel(){
        $last_month = date('Ym',strtotime('-1 month'));
        if(IS_POST){
            $cache_key = 'cronscript:salaryexcel';
            $redis  =  \Common\Lib\SavorRedis::getInstance();
            $redis->select(1);
            $res_data = $redis->get($cache_key);
            if(!empty($res_data)){
                $now_time = time();
                $diff_time = $now_time - $res_data;
                $errMsg = "你上传的文件正在处理中，处理时间{$diff_time}秒，请稍后。";
                $this->output($errMsg, 'salary/filelist', 2,0);
            }

            $upload = new \Think\Upload();
            $upload->exts = array('xls','xlsx','csv');
            $upload->maxSize = 2097152;
            $upload->rootPath = $this->imgup_path();
            $upload->savePath = '';
            $upload->saveName = time().mt_rand();
            $info = $upload->upload();
            if(!$info){
                $errMsg = $upload->getError();
                $this->output($errMsg, 'salary/addexcel', 2,0);
            }else{
                $userinfo = session('sysUserInfo');
                $sysuser_id = $userinfo['id'];
                $file_name = urlencode($info['fileup']['savepath'].$info['fileup']['savename']);
                $shell = "/opt/install/php/bin/php /application_data/web/php/savor_admin/cli.php dataexport/salary/calculateBdac/filename/$file_name/filemonth/$last_month > /tmp/null &";
                system($shell);
                $now_time = time();
                $redis->set($cache_key,$now_time,86400);
                $this->output('导入成功,开始处理数据', 'salary/filelist');
            }
        }else{
            $this->display();
        }

    }
}