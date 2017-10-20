<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
/**
 * @desc 系统日志记录类
 *
 */
class DailyconfigController extends BaseController {
    
     private $oss_host = '';
	 public function __construct(){
	     parent::__construct();
	     $this->oss_host = get_oss_host();
	 }
    /**
     * @desc 电视设置页面
     */
    public function configdata(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        //$volume_info = $m_sys_config->getOne('system_default_volume');
        //缓存设置
        $switch_cache_info = $m_sys_config->getOne('daily_cache_config');
        $this->assign('info',$switch_cache_info);
        $this->display('Dailyconfig/configdata');
    }

    /**
     * @desc 修改电视设置配置状态
     */
    public function editStatus(){
        $status = I('get.status',0,'intval');
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $map['status'] = $status;
        $rts = $m_sys_config->editData($map, 'daily_cache_config');
        if($rts){
            if($status == 0) {
                $message = '成功关闭!';
            } else {
                $message = '成功开启!';
            }
            $this->output($message, 'Dailyconfig/configData',2);
        }else {
            $this->error('操作失败!');
        }
        
    }


}