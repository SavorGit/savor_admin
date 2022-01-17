<?php
/**
 *资源管理控制器
 *
 */
namespace Admin\Controller;
class ProxysaleController extends BaseController{
    
    private $oss_host = '';
    public function __construct(){
        parent::__construct();
        $this->oss_host = get_oss_host();
    }
    public function index(){
        
        $this->display('index');
    }
    public function add(){
        $this->display('add');
    }
    
    
}