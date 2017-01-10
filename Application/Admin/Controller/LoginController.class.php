<?php
namespace Admin\Controller;
use Think\Cache;
/**
 * @desc 登陆类
 *
 */
class LoginController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    //用户登陆
    public function index() {
        if(IS_POST) {
            $lock_max = 5;
            $lock_time = 1200;
            $error_msg = '你的账户输入错误已达上限，请联系管理员。';
            $cache_key = 'login_'.getClientIP();
            $cache_db = Cache::getInstance('db');
            $cache_locknum = $cache_db->get($cache_key);
            if(!empty($cache_locknum) && $cache_locknum==$lock_max){
                $this->assign('errormsg', $error_msg);
                $this->display('Login/index');
                exit;
            }
            $userName = I('post.username', '', "trim");
            $userpwd  = I('post.password', '', "trim");
            if($userName && $userpwd) {
                $user = new \Admin\Model\UserModel();
                $where = " and username='".$userName."'";
                $result = $user->getUser($where);
                if(empty($result)){
                    $this->assign('errormsg', '用户名或密码错误，请重新输入。');
                    $this->display('Login/index');
                    exit;
                }
                $userinfo = $result[0];
                $pwdpre = C('PWDPRE');
                if(strlen($userpwd) != strlen($userinfo['password'])){
                    $userpwd = md5(md5($userpwd.$pwdpre));
                }
                //判断用户名和密码是否相等
                if($userpwd == md5($userinfo['password'])){
                    $mind = array();
                    $mind['username'] = $userinfo['username'];
                    $mind['userpwd'] = $userpwd;
                    cookie('login_upwd',$mind,86400*7);
                    unset($result[0]['password']);
                    session('sysUserInfo',$result[0]);
                    $this->sysLog('登录操作', '登录操作', '当前栏目','login');
                    $url = $this->host_name();
                    header("location: $url");
                }else{
                    if($cache_locknum){
                        $cache_locknum = $cache_locknum+1;
                    }else{
                        $cache_locknum = 1;
                    }
                    $cache_db->set($cache_key,$cache_locknum,$lock_time);
                    $lock_num = $lock_max-$cache_locknum;
                    $errormsg=$lock_num==0?$error_msg:'用户名或密码错误，请重新输入。';
                    $this->assign('errormsg',$errormsg);
                    $this->display('Login/index');
                }
            }else{
                $this->assign('errormsg', '用户名和密码不能为空!');
                $this->display('Login/index');
            }
        } else {
            $cookie_upwd = cookie('login_upwd');
            if($cookie_upwd)$this->assign('cookie_upwd',$cookie_upwd);
            $this->display('Login/index');
        }
    }
    
    //用户退出操作
    public function logout(){
        $this->sysLog('退出操作', '退出操作', '当前栏目','logout');
        session('sysUserInfo',null);
        session('licenseinfo',null);
        $url = $this->host_name();
        header('Location:'.$url);
    }
    
}