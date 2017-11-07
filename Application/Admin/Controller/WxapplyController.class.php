<?php
namespace Admin\Controller;
use Think\Controller;
/**
 * @desc 微信应用
 *
 */
class WxapplyController extends Controller {
    
    private $dyh_config ;
    private $url_oauth = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    public function __construct() {
        $this->dyh_config = C('WX_FWH_CONFIG');
    }
    /**
     * @desc 用户同意授权获取code
     */
    public function index(){
        $appid = $this->dyh_config['appid'];
        $uri = I('redirect_url','','urldecode');
        $uri = urlencode($uri);
        $scope = I('scope','','intval');
        $state = 'wxsq001';
        if($scope){
            $wx_url = $this->url_oauth."?appid=$appid&redirect_uri=$uri&response_type=code&scope=snsapi_userinfo&state=$state#wechat_redirect";
        }else {
            $wx_url = $this->url_oauth."?appid=$appid&redirect_uri=$uri&response_type=code&scope=snsapi_base&state=$state#wechat_redirect";
        }
        header("Location: $wx_url");
        exit;
    }
    
}